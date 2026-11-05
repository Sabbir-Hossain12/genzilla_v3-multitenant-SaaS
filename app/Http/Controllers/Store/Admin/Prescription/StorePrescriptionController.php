<?php

namespace App\Http\Controllers\Store\Admin\Prescription;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\DeliveryCharge;
use App\Models\InventoryBatch;
use App\Models\Order;
use App\Models\OrderPayment;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use App\Models\TenantPrescription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StorePrescriptionController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = tenantId();

        $query = TenantPrescription::where('tenant_id', $tenantId)
            ->with(['customer', 'order'])
            ->latest('id');

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('prescription_number', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_phone', 'like', "%{$search}%")
                  ->orWhere('customer_email', 'like', "%{$search}%")
                  ->orWhere('delivery_address', 'like', "%{$search}%");
            });
        }

        $prescriptions = $query->paginate(15)->withQueryString();

        $statusCounts = [
            'all' => TenantPrescription::where('tenant_id', $tenantId)->count(),
            'pending_review' => TenantPrescription::where('tenant_id', $tenantId)->where('status', 'pending_review')->count(),
            'converted_to_order' => TenantPrescription::where('tenant_id', $tenantId)->where('status', 'converted_to_order')->count(),
            'rejected' => TenantPrescription::where('tenant_id', $tenantId)->where('status', 'rejected')->count(),
        ];

        return view('store_admin.pages.prescriptions.index', compact('prescriptions', 'statusCounts'));
    }

    public function show(int $id)
    {
        $tenantId = tenantId();

        $prescription = TenantPrescription::where('tenant_id', $tenantId)
            ->with(['customer', 'order.products', 'reviewedBy'])
            ->findOrFail($id);

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

        return view('store_admin.pages.prescriptions.show', compact('prescription', 'products', 'deliveryCharges'));
    }

    public function convertToOrder(Request $request, int $id)
    {
        $tenantId = tenantId();

        $prescription = TenantPrescription::where('tenant_id', $tenantId)->findOrFail($id);

        if ($prescription->status === 'converted_to_order') {
            return back()->withErrors(['status' => 'This prescription has already been converted into an order.']);
        }

        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.variant_id' => ['required', 'integer', Rule::exists('product_variants', 'id')->where('tenant_id', $tenantId)],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.discount_amount' => ['nullable', 'numeric', 'min:0'],
            'items.*.dosage_instructions' => ['nullable', 'string', 'max:255'],
            'delivery_charge_id' => ['nullable', 'integer', Rule::exists('delivery_charges', 'id')->where('tenant_id', $tenantId)],
            'custom_shipping_charge' => ['nullable', 'numeric', 'min:0'],
            'payment_method' => ['required', Rule::in(['cod', 'card', 'bkash', 'nagad', 'rocket', 'bank_transfer', 'cash'])],
            'payment_status' => ['required', Rule::in(['unpaid', 'paid', 'partially_paid'])],
            'shipping_name' => ['required', 'string', 'max:150'],
            'shipping_phone' => ['required', 'string', 'max:50'],
            'shipping_email' => ['nullable', 'email', 'max:150'],
            'shipping_address' => ['required', 'string', 'max:500'],
            'shipping_area' => ['nullable', 'string', 'max:100'],
            'shipping_city' => ['required', 'string', 'max:100'],
            'shipping_postcode' => ['nullable', 'string', 'max:30'],
            'shipping_country' => ['nullable', 'string', 'max:100'],
            'order_note' => ['nullable', 'string', 'max:1000'],
            'pharmacist_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $order = DB::transaction(function () use ($tenantId, $prescription, $validated) {
            // 1. Calculate shipping charge
            $shippingCharge = 0.00;
            $shippingMethod = 'Standard Delivery';
            $deliveryCharge = null;

            if (!empty($validated['delivery_charge_id'])) {
                $deliveryCharge = DeliveryCharge::where('tenant_id', $tenantId)->find($validated['delivery_charge_id']);
                if ($deliveryCharge) {
                    $shippingCharge = (float)$deliveryCharge->charge_amount;
                    $shippingMethod = $deliveryCharge->title;
                }
            }

            if (isset($validated['custom_shipping_charge']) && is_numeric($validated['custom_shipping_charge'])) {
                $shippingCharge = (float)$validated['custom_shipping_charge'];
            }

            // 2. Process Line Items
            $subtotal = 0.00;
            $processedItems = [];

            foreach ($validated['items'] as $itemData) {
                $variant = ProductVariant::where('tenant_id', $tenantId)
                    ->with('product')
                    ->findOrFail($itemData['variant_id']);

                $qty = (int)$itemData['quantity'];
                $price = (float)$itemData['unit_price'];
                $discount = isset($itemData['discount_amount']) ? (float)$itemData['discount_amount'] : 0.00;
                $lineTotal = max(0, ($price * $qty) - $discount);

                $subtotal += $lineTotal;

                $processedItems[] = [
                    'variant' => $variant,
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'discount_amount' => $discount,
                    'line_total' => $lineTotal,
                    'dosage_instructions' => $itemData['dosage_instructions'] ?? null,
                ];
            }

            $grandTotal = max(0, $subtotal + $shippingCharge);

            // 3. Generate Order Number
            $orderNumber = 'ORD-' . strtoupper(Str::random(8));
            while (Order::where('tenant_id', $tenantId)->where('order_number', $orderNumber)->exists()) {
                $orderNumber = 'ORD-' . strtoupper(Str::random(8));
            }

            // 4. Create Order
            $order = Order::create([
                'tenant_id' => $tenantId,
                'public_id' => (string) Str::uuid(),
                'order_number' => $orderNumber,
                'customer_id' => $prescription->customer_id,
                'status' => 'pending',
                'payment_method' => $validated['payment_method'],
                'payment_status' => $validated['payment_status'],
                'prescription_status' => 'approved',
                'prescription_image' => $prescription->prescription_image,
                'pharmacist_rx_notes' => $validated['pharmacist_notes'] ?? null,
                'rx_reviewed_by_user_id' => auth()->id(),
                'rx_reviewed_at' => now(),
                'is_direct_prescription' => true,
                'currency' => 'USD',
                'subtotal' => $subtotal,
                'discount_amount' => 0.00,
                'shipping_charge' => $shippingCharge,
                'tax_amount' => 0.00,
                'total' => $grandTotal,
                'delivery_charge_id' => $deliveryCharge?->id,
                'shipping_method' => $shippingMethod,
                'shipping_name' => $validated['shipping_name'],
                'shipping_phone' => $validated['shipping_phone'],
                'shipping_email' => $validated['shipping_email'] ?? $prescription->customer_email,
                'shipping_address' => $validated['shipping_address'],
                'shipping_area' => $validated['shipping_area'] ?? $prescription->delivery_area,
                'shipping_city' => $validated['shipping_city'],
                'shipping_postcode' => $validated['shipping_postcode'] ?? null,
                'shipping_country' => $validated['shipping_country'] ?? 'Bangladesh',
                'order_note' => $validated['order_note'] ?? $prescription->patient_notes,
                'processed_by_user_id' => auth()->id(),
                'placed_at' => now(),
            ]);

            // 5. Create Order Products & Inventory Ledger
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
                    'dosage_instructions' => $item['dosage_instructions'],
                    'mrp' => $variant->mrp,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount_amount' => $item['discount_amount'],
                    'line_total' => $item['line_total'],
                ]);

                // Inventory Reservation
                if ($variant->track_inventory) {
                    $batch = InventoryBatch::where('tenant_id', $tenantId)
                        ->where('variant_id', $variant->id)
                        ->where('status', 'active')
                        ->orderBy('expiry_date')
                        ->first();

                    if ($batch) {
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
                            'note' => "Rx Order #{$order->order_number} reservation from direct prescription #{$prescription->prescription_number}",
                            'created_at' => now(),
                        ]);
                    }
                }
            }

            // 6. Payment Transaction Record if marked paid or non-COD
            if ($validated['payment_status'] === 'paid' || $validated['payment_method'] !== 'cod') {
                OrderPayment::create([
                    'tenant_id' => $tenantId,
                    'order_id' => $order->id,
                    'type' => 'charge',
                    'payment_method' => $validated['payment_method'],
                    'transaction_id' => 'RX-' . strtoupper(Str::random(8)),
                    'amount' => $grandTotal,
                    'currency' => 'USD',
                    'status' => $validated['payment_status'] === 'paid' ? 'successful' : 'pending',
                    'gateway_response' => ['source' => 'direct_prescription_order_builder'],
                    'paid_at' => $validated['payment_status'] === 'paid' ? now() : null,
                ]);
            }

            // 7. Update Prescription Record
            $prescription->update([
                'status' => 'converted_to_order',
                'order_id' => $order->id,
                'pharmacist_notes' => $validated['pharmacist_notes'] ?? null,
                'reviewed_by_user_id' => auth()->id(),
                'reviewed_at' => now(),
            ]);

            return $order;
        });

        return redirect()->route('store.admin.orders.show', $order)
            ->with('success', "Prescription {$prescription->prescription_number} converted into Order #{$order->order_number} successfully.");
    }

    public function reject(Request $request, int $id)
    {
        $tenantId = tenantId();

        $prescription = TenantPrescription::where('tenant_id', $tenantId)->findOrFail($id);

        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:1000'],
        ]);

        $prescription->update([
            'status' => 'rejected',
            'rejection_reason' => $validated['rejection_reason'],
            'reviewed_by_user_id' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        return redirect()->route('store.admin.prescriptions.index')
            ->with('success', "Prescription #{$prescription->prescription_number} marked as rejected.");
    }

    public function destroy(int $id)
    {
        $tenantId = tenantId();

        $prescription = TenantPrescription::where('tenant_id', $tenantId)->findOrFail($id);

        if ($prescription->prescription_image && !str_starts_with($prescription->prescription_image, 'http')) {
            // Delete file only if no orders are referencing it
            $referencedByOrder = Order::where('tenant_id', $tenantId)
                ->where('prescription_image', $prescription->prescription_image)
                ->exists();

            if (!$referencedByOrder) {
                Storage::disk('public')->delete($prescription->prescription_image);
            }
        }

        $prescription->delete();

        $tenant = tenant() ?? \App\Models\Tenant::find($tenantId);
        $tenant?->syncUsage();

        return redirect()->route('store.admin.prescriptions.index')
            ->with('success', 'Prescription deleted successfully.');
    }
}
