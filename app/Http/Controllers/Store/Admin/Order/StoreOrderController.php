<?php

namespace App\Http\Controllers\Store\Admin\Order;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Customer;
use App\Models\DeliveryCharge;
use App\Models\InventoryBatch;
use App\Models\Order;
use App\Models\OrderPayment;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreOrderController extends Controller
{
    public function orders(Request $request)
    {
        $tenantId = tenantId();

        $query = Order::where('tenant_id', $tenantId)
            ->with(['customer', 'products', 'payments'])
            ->latest('placed_at');

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('shipping_name', 'like', "%{$search}%")
                  ->orWhere('shipping_phone', 'like', "%{$search}%")
                  ->orWhere('shipping_email', 'like', "%{$search}%")
                  ->orWhere('public_id', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_status') && $request->payment_status !== 'all') {
            $query->where('payment_status', $request->payment_status);
        }

        $orders = $query->paginate(20)->withQueryString();

        $statusCounts = [
            'all' => Order::where('tenant_id', $tenantId)->count(),
            'pending' => Order::where('tenant_id', $tenantId)->where('status', 'pending')->count(),
            'processing' => Order::where('tenant_id', $tenantId)->whereIn('status', ['confirmed', 'processing'])->count(),
            'shipped' => Order::where('tenant_id', $tenantId)->where('status', 'shipped')->count(),
            'delivered' => Order::where('tenant_id', $tenantId)->where('status', 'delivered')->count(),
            'cancelled' => Order::where('tenant_id', $tenantId)->where('status', 'cancelled')->count(),
        ];

        return view('store_admin.pages.orders.index', compact('orders', 'statusCounts'));
    }

    public function createOrder()
    {
        $tenantId = tenantId();

        $products = Product::where('tenant_id', $tenantId)
            ->where('status', '!=', 'archived')
            ->whereHas('variants', function ($q) {
                $q->where('status', 'active');
            })
            ->with([
                'activeVariants.batches',
                'media',
            ])
            ->orderBy('name')
            ->get();

        $deliveryCharges = DeliveryCharge::where('tenant_id', $tenantId)
            ->where('status', true)
            ->orderBy('sort_order')
            ->get();

        $coupons = Coupon::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>=', now());
            })
            ->orderBy('code')
            ->get();

        $customers = Customer::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->with('defaultAddress')
            ->orderBy('first_name')
            ->get();

        return view('store_admin.pages.orders.form', compact('products', 'deliveryCharges', 'coupons', 'customers'));
    }

    public function storeOrder(Request $request)
    {
        $tenantId = tenantId();

        $validated = $request->validate([
            'customer_id' => ['nullable', 'integer', Rule::exists('customers', 'id')->where('tenant_id', $tenantId)],
            'shipping_name' => ['required', 'string', 'max:150'],
            'shipping_phone' => ['required', 'string', 'max:50'],
            'shipping_email' => ['nullable', 'email', 'max:150'],
            'shipping_address' => ['required', 'string', 'max:500'],
            'shipping_area' => ['nullable', 'string', 'max:100'],
            'shipping_city' => ['required', 'string', 'max:100'],
            'shipping_postcode' => ['nullable', 'string', 'max:30'],
            'shipping_country' => ['nullable', 'string', 'max:100'],
            'delivery_charge_id' => ['nullable', 'integer', Rule::exists('delivery_charges', 'id')->where('tenant_id', $tenantId)],
            'custom_shipping_charge' => ['nullable', 'numeric', 'min:0'],
            'coupon_id' => ['nullable', 'integer', Rule::exists('coupons', 'id')->where('tenant_id', $tenantId)],
            'payment_method' => ['required', Rule::in(['cod', 'card', 'bkash', 'nagad', 'rocket', 'bank_transfer', 'cash'])],
            'payment_status' => ['required', Rule::in(['unpaid', 'paid', 'partially_paid'])],
            'status' => ['required', Rule::in(['pending', 'confirmed', 'processing', 'shipped', 'delivered'])],
            'prescription_status' => ['nullable', Rule::in(['not_required', 'pending_review', 'approved', 'rejected'])],
            'order_note' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.variant_id' => ['required', 'integer', Rule::exists('product_variants', 'id')->where('tenant_id', $tenantId)],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['nullable', 'numeric', 'min:0'],
            'items.*.discount_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        $order = DB::transaction(function () use ($tenantId, $validated, $request) {
            // 1. Process line items and compute subtotal
            $subtotal = 0.0;
            $processedItems = [];
            $hasPrescription = false;

            foreach ($validated['items'] as $itemData) {
                $variant = ProductVariant::where('tenant_id', $tenantId)
                    ->with('product')
                    ->findOrFail($itemData['variant_id']);

                $unitPrice = isset($itemData['unit_price']) && $itemData['unit_price'] !== ''
                    ? (float)$itemData['unit_price']
                    : (float)$variant->price;

                $quantity = (int)$itemData['quantity'];
                $itemDiscount = isset($itemData['discount_amount']) ? (float)$itemData['discount_amount'] : 0.0;
                $lineTotal = max(0, ($unitPrice * $quantity) - $itemDiscount);

                $subtotal += $lineTotal;

                if ($variant->product?->requires_prescription) {
                    $hasPrescription = true;
                }

                $processedItems[] = [
                    'variant' => $variant,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'discount_amount' => $itemDiscount,
                    'line_total' => $lineTotal,
                ];
            }

            // 2. Resolve Shipping
            $shippingCharge = 0.0;
            $shippingMethod = 'Standard Delivery';
            $deliveryCharge = null;

            if (!empty($validated['delivery_charge_id'])) {
                $deliveryCharge = DeliveryCharge::where('tenant_id', $tenantId)->find($validated['delivery_charge_id']);
                if ($deliveryCharge) {
                    $shippingMethod = $deliveryCharge->title;
                    if ($deliveryCharge->min_order_amount && $subtotal >= (float)$deliveryCharge->min_order_amount) {
                        $shippingCharge = 0.0;
                    } else {
                        $shippingCharge = (float)$deliveryCharge->charge_amount;
                    }
                }
            } elseif (isset($validated['custom_shipping_charge']) && $validated['custom_shipping_charge'] !== '') {
                $shippingCharge = (float)$validated['custom_shipping_charge'];
                $shippingMethod = 'Custom Delivery';
            }

            // 3. Resolve Coupon Discount
            $couponDiscount = 0.0;
            $couponCode = null;
            $coupon = null;

            if (!empty($validated['coupon_id'])) {
                $coupon = Coupon::where('tenant_id', $tenantId)->find($validated['coupon_id']);
                if ($coupon && $coupon->isValidForAmount($subtotal)) {
                    $couponDiscount = $coupon->calculateDiscount($subtotal);
                    $couponCode = $coupon->code;
                    $coupon->increment('used_count');
                }
            }

            // 4. Calculate Grand Total
            $grandTotal = max(0, $subtotal - $couponDiscount + $shippingCharge);

            // 5. Generate Order Numbers
            $orderNumber = 'ORD-' . strtoupper(Str::random(8));
            while (Order::where('tenant_id', $tenantId)->where('order_number', $orderNumber)->exists()) {
                $orderNumber = 'ORD-' . strtoupper(Str::random(8));
            }

            $order = Order::create([
                'tenant_id' => $tenantId,
                'public_id' => (string) Str::uuid(),
                'order_number' => $orderNumber,
                'customer_id' => $validated['customer_id'] ?? null,
                'status' => $validated['status'],
                'payment_method' => $validated['payment_method'],
                'payment_status' => $validated['payment_status'],
                'prescription_status' => $hasPrescription ? ($validated['prescription_status'] ?? 'pending_review') : null,
                'currency' => 'USD',
                'subtotal' => $subtotal,
                'discount_amount' => $couponDiscount,
                'shipping_charge' => $shippingCharge,
                'tax_amount' => 0.00,
                'total' => $grandTotal,
                'coupon_id' => $coupon?->id,
                'coupon_code' => $couponCode,
                'delivery_charge_id' => $deliveryCharge?->id,
                'shipping_method' => $shippingMethod,
                'shipping_name' => $validated['shipping_name'],
                'shipping_phone' => $validated['shipping_phone'],
                'shipping_email' => $validated['shipping_email'] ?? null,
                'shipping_address' => $validated['shipping_address'],
                'shipping_area' => $validated['shipping_area'] ?? null,
                'shipping_city' => $validated['shipping_city'],
                'shipping_postcode' => $validated['shipping_postcode'] ?? null,
                'shipping_country' => $validated['shipping_country'] ?? 'Bangladesh',
                'order_note' => $validated['order_note'] ?? null,
                'processed_by_user_id' => auth()->id(),
                'placed_at' => now(),
                'shipped_at' => in_array($validated['status'], ['shipped', 'delivered']) ? now() : null,
                'delivered_at' => $validated['status'] === 'delivered' ? now() : null,
            ]);

            // 6. Create Order Products & Inventory Ledger
            foreach ($processedItems as $item) {
                $variant = $item['variant'];
                $product = $variant->product;

                OrderProduct::create([
                    'tenant_id' => $tenantId,
                    'order_id' => $order->id,
                    'variant_id' => $variant->id,
                    'product_name' => $product?->name ?? 'Custom Item',
                    'variant_title' => $variant->title,
                    'sku' => $variant->sku,
                    'variant_options' => [],
                    'requires_prescription' => (bool)$product?->requires_prescription,
                    'mrp' => $variant->mrp,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount_amount' => $item['discount_amount'],
                    'line_total' => $item['line_total'],
                ]);

                // Inventory allocation
                if ($variant->track_inventory) {
                    $batch = InventoryBatch::where('tenant_id', $tenantId)
                        ->where('variant_id', $variant->id)
                        ->where('status', 'active')
                        ->orderBy('expiry_date')
                        ->first();

                    if ($batch) {
                        if ($order->status === 'delivered') {
                            $batch->decrement('quantity', min($batch->quantity, $item['quantity']));
                            StockMovement::create([
                                'tenant_id' => $tenantId,
                                'variant_id' => $variant->id,
                                'batch_id' => $batch->id,
                                'type' => 'sale',
                                'qty_delta' => -$item['quantity'],
                                'reserved_delta' => 0,
                                'reference_type' => 'Order',
                                'reference_id' => $order->id,
                                'user_id' => auth()->id(),
                                'note' => "Order #{$order->order_number} manual sale",
                                'created_at' => now(),
                            ]);
                        } else {
                            $batch->increment('reserved', $item['quantity']);
                            StockMovement::create([
                                'tenant_id' => $tenantId,
                                'variant_id' => $variant->id,
                                'batch_id' => $batch->id,
                                'type' => 'reserve',
                                'qty_delta' => 0,
                                'reserved_delta' => $item['quantity'],
                                'reference_type' => 'Order',
                                'reference_id' => $order->id,
                                'user_id' => auth()->id(),
                                'note' => "Order #{$order->order_number} reservation",
                                'created_at' => now(),
                            ]);
                        }
                    }
                }
            }

            // 7. Payment Transaction Record
            if ($validated['payment_status'] === 'paid' || $validated['payment_method'] !== 'cod') {
                OrderPayment::create([
                    'tenant_id' => $tenantId,
                    'order_id' => $order->id,
                    'type' => 'charge',
                    'payment_method' => $validated['payment_method'],
                    'transaction_id' => 'MANUAL-' . strtoupper(Str::random(8)),
                    'amount' => $grandTotal,
                    'currency' => 'USD',
                    'status' => $validated['payment_status'] === 'paid' ? 'successful' : 'pending',
                    'gateway_response' => ['source' => 'store_admin_manual'],
                    'paid_at' => $validated['payment_status'] === 'paid' ? now() : null,
                ]);
            }

            return $order;
        });

        return redirect()->route('store.admin.orders.show', $order)->with('success', "Order #{$order->order_number} created successfully.");
    }

    public function showOrder(int $id)
    {
        $order = Order::where('tenant_id', tenantId())
            ->with([
                'customer',
                'products.variant.product.media',
                'payments',
                'coupon',
                'deliveryCharge',
                'processedBy',
            ])
            ->findOrFail($id);

        return view('store_admin.pages.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, int $id)
    {
        $tenantId = tenantId();
        $order = Order::where('tenant_id', $tenantId)->findOrFail($id);

        $validated = $request->validate([
            'status' => ['nullable', Rule::in(['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled', 'returned'])],
            'payment_status' => ['nullable', Rule::in(['unpaid', 'paid', 'partially_paid', 'refunded', 'failed'])],
            'prescription_status' => ['nullable', Rule::in(['not_required', 'pending_review', 'approved', 'rejected'])],
            'order_note' => ['nullable', 'string', 'max:1000'],
            'cancel_reason' => ['nullable', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($tenantId, $order, $validated) {
            $newStatus = $validated['status'] ?? $order->status;
            $newPaymentStatus = $validated['payment_status'] ?? $order->payment_status;

            $updates = [
                'status' => $newStatus,
                'payment_status' => $newPaymentStatus,
                'prescription_status' => $validated['prescription_status'] ?? $order->prescription_status,
                'order_note' => $validated['order_note'] ?? $order->order_note,
                'processed_by_user_id' => auth()->id(),
            ];

            if ($newStatus === 'shipped' && !$order->shipped_at) {
                $updates['shipped_at'] = now();
            }

            if ($newStatus === 'delivered' && !$order->delivered_at) {
                $updates['delivered_at'] = now();
                if ($order->payment_method === 'cod' && $newPaymentStatus === 'unpaid') {
                    $updates['payment_status'] = 'paid';
                    $newPaymentStatus = 'paid';
                }
            }

            // Cancellation & Stock Release
            if ($newStatus === 'cancelled' && $order->status !== 'cancelled') {
                if (!$order->cancelled_at) {
                    $updates['cancelled_at'] = now();
                }
                $updates['cancel_reason'] = $validated['cancel_reason'] ?? $order->cancel_reason;

                foreach ($order->products as $item) {
                    if ($item->variant && $item->variant->track_inventory) {
                        $batch = InventoryBatch::where('tenant_id', $tenantId)
                            ->where('variant_id', $item->variant_id)
                            ->first();

                        if ($batch && $batch->reserved > 0) {
                            $releaseQty = min($batch->reserved, $item->quantity);
                            $batch->decrement('reserved', $releaseQty);

                            StockMovement::create([
                                'tenant_id' => $tenantId,
                                'variant_id' => $item->variant_id,
                                'batch_id' => $batch->id,
                                'type' => 'release',
                                'qty_delta' => 0,
                                'reserved_delta' => -$releaseQty,
                                'note' => "Order #{$order->order_number} cancelled",
                                'reference_type' => 'Order',
                                'reference_id' => $order->id,
                                'created_at' => now(),
                            ]);
                        }
                    }
                }
            }

            // Payment Ledger on 'paid' status
            if ($newPaymentStatus === 'paid' && $order->payment_status !== 'paid') {
                OrderPayment::create([
                    'tenant_id' => $tenantId,
                    'order_id' => $order->id,
                    'type' => 'charge',
                    'payment_method' => $order->payment_method,
                    'transaction_id' => 'MANUAL-' . strtoupper(Str::random(8)),
                    'amount' => $order->total,
                    'currency' => $order->currency,
                    'status' => 'successful',
                    'gateway_response' => ['source' => 'admin_status_update'],
                    'paid_at' => now(),
                ]);
            }

            $order->update($updates);
        });

        return back()->with('success', "Order #{$order->order_number} status updated.");
    }

    public function deleteOrder(int $id)
    {
        $order = Order::where('tenant_id', tenantId())->findOrFail($id);
        $orderNumber = $order->order_number;
        $order->delete();

        return redirect()->route('store.admin.orders.index')->with('success', "Order #{$orderNumber} deleted.");
    }
}
