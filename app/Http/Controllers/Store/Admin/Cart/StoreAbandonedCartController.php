<?php

namespace App\Http\Controllers\Store\Admin\Cart;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StoreAbandonedCartController extends Controller
{
    /**
     * Display the abandoned checkouts dashboard with Recoverable Carts & Top Abandoned Products tabs.
     */
    public function index(Request $request)
    {
        $tenantId = tenantId();
        $tab = $request->query('tab', 'recoverable');

        // ==========================================
        // 1. FINANCIAL & OPERATIONAL KPIS (Tenant-Wide)
        // ==========================================
        $baseRecoverable = Cart::where('tenant_id', $tenantId)
            ->whereNotNull('customer_id')
            ->has('items')
            ->where('updated_at', '<=', now()->subHours(2));

        $totalRecoverableCarts = (clone $baseRecoverable)->count();
        $uncontactedCarts = (clone $baseRecoverable)->whereNull('recovery_contacted_at')->count();
        $contactedCarts = (clone $baseRecoverable)->whereNotNull('recovery_contacted_at')->count();

        // Calculate potential recoverable value (from known customers)
        $recoverableRevenue = (float) DB::table('cart_items')
            ->join('carts', 'cart_items.cart_id', '=', 'carts.id')
            ->join('product_variants', 'cart_items.product_variant_id', '=', 'product_variants.id')
            ->where('carts.tenant_id', $tenantId)
            ->whereNotNull('carts.customer_id')
            ->where('carts.updated_at', '<=', now()->subHours(2))
            ->sum(DB::raw('cart_items.quantity * product_variants.price'));

        // Calculate total stuck catalog value (across all carts including guests)
        $totalLostRevenue = (float) DB::table('cart_items')
            ->join('carts', 'cart_items.cart_id', '=', 'carts.id')
            ->join('product_variants', 'cart_items.product_variant_id', '=', 'product_variants.id')
            ->where('carts.tenant_id', $tenantId)
            ->where('carts.updated_at', '<=', now()->subHours(2))
            ->sum(DB::raw('cart_items.quantity * product_variants.price'));

        $totalAbandonedProductsCount = DB::table('cart_items')
            ->join('carts', 'cart_items.cart_id', '=', 'carts.id')
            ->where('carts.tenant_id', $tenantId)
            ->where('carts.updated_at', '<=', now()->subHours(2))
            ->distinct('cart_items.product_variant_id')
            ->count('cart_items.product_variant_id');

        $kpis = [
            'total_recoverable_carts' => $totalRecoverableCarts,
            'recoverable_revenue' => $recoverableRevenue,
            'uncontacted_carts' => $uncontactedCarts,
            'contacted_carts' => $contactedCarts,
            'total_abandoned_products' => $totalAbandonedProductsCount,
            'total_lost_revenue' => $totalLostRevenue,
        ];

        // ==========================================
        // 2. TAB 1: RECOVERABLE CARTS (Tele-Sales Queue)
        // ==========================================
        $recoverableQuery = Cart::with([
            'customer:id,first_name,last_name,email,phone,status,created_at',
            'coupon:id,code,type,value',
            'items.product:id,name,slug,requires_prescription',
            'items.product.media',
            'items.productVariant:id,sku,title,price,track_inventory',
        ])
        ->where('tenant_id', $tenantId)
        ->whereNotNull('customer_id')
        ->has('items')
        ->where('updated_at', '<=', now()->subHours(2));

        // Filter: Contact Status
        $contactStatus = $request->query('contact_status', 'all');
        if ($contactStatus === 'uncontacted') {
            $recoverableQuery->whereNull('recovery_contacted_at');
        } elseif ($contactStatus === 'contacted') {
            $recoverableQuery->whereNotNull('recovery_contacted_at');
        }

        // Filter: Prescription Required
        $rxStatus = $request->query('rx_status', 'all');
        if ($rxStatus === 'required') {
            $recoverableQuery->whereHas('items.product', fn($q) => $q->where('requires_prescription', true));
        } elseif ($rxStatus === 'not_required') {
            $recoverableQuery->whereDoesntHave('items.product', fn($q) => $q->where('requires_prescription', true));
        }

        // Search: Customer or items
        $search = trim((string) $request->query('search', ''));
        if (!empty($search)) {
            $recoverableQuery->where(function ($q) use ($search) {
                $q->whereHas('customer', function ($cq) use ($search) {
                    $cq->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                })->orWhereHas('items.product', function ($pq) use ($search) {
                    $pq->where('name', 'like', "%{$search}%");
                })->orWhereHas('items.productVariant', function ($vq) use ($search) {
                    $vq->where('sku', 'like', "%{$search}%");
                });
            });
        }

        $recoverableCarts = $recoverableQuery->latest('updated_at')->paginate(15, ['*'], 'recoverable_page');

        // ==========================================
        // 3. TAB 2: TOP ABANDONED PRODUCTS (Catalog Analytics)
        // ==========================================
        $topProductsQuery = CartItem::query()
            ->join('carts', 'cart_items.cart_id', '=', 'carts.id')
            ->join('product_variants', 'cart_items.product_variant_id', '=', 'product_variants.id')
            ->join('products', 'cart_items.product_id', '=', 'products.id')
            ->where('carts.tenant_id', $tenantId)
            ->where('carts.updated_at', '<=', now()->subHours(2))
            ->select(
                'cart_items.product_id',
                'cart_items.product_variant_id',
                DB::raw('MIN(cart_items.id) as id'),
                DB::raw('COUNT(DISTINCT cart_items.cart_id) as abandoned_carts_count'),
                DB::raw('SUM(cart_items.quantity) as total_abandoned_qty'),
                DB::raw('SUM(cart_items.quantity * product_variants.price) as lost_revenue')
            )
            ->with([
                'product:id,name,slug,requires_prescription',
                'product.media',
                'productVariant:id,sku,title,price,track_inventory',
                'productVariant.batches',
            ])
            ->groupBy('cart_items.product_id', 'cart_items.product_variant_id');

        if (!empty($search) && $tab === 'products') {
            $topProductsQuery->where(function ($q) use ($search) {
                $q->where('products.name', 'like', "%{$search}%")
                    ->orWhere('product_variants.sku', 'like', "%{$search}%")
                    ->orWhere('product_variants.title', 'like', "%{$search}%");
            });
        }

        $topAbandonedProducts = $topProductsQuery
            ->orderByDesc('abandoned_carts_count')
            ->orderByDesc('lost_revenue')
            ->paginate(15, ['*'], 'products_page');

        return view('store_admin.pages.abandoned_checkouts.index', compact(
            'kpis',
            'recoverableCarts',
            'topAbandonedProducts',
            'tab',
            'contactStatus',
            'rxStatus',
            'search'
        ));
    }

    /**
     * Show cart snapshot details (for interactive modal or direct view).
     */
    public function show(Request $request, int $id)
    {
        $tenantId = tenantId();

        $cart = Cart::where('tenant_id', $tenantId)
            ->with([
                'customer.addresses',
                'coupon',
                'items.product.media',
                'items.productVariant',
            ])
            ->findOrFail($id);

        if ($request->wantsJson() || $request->ajax()) {
            $items = $cart->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product?->name ?? 'Unknown Product',
                    'variant_title' => $item->productVariant?->title ?? 'Default',
                    'sku' => $item->productVariant?->sku ?? 'N/A',
                    'price' => (float) ($item->productVariant?->price ?? 0),
                    'quantity' => (int) $item->quantity,
                    'line_total' => (float) (($item->productVariant?->price ?? 0) * $item->quantity),
                    'requires_prescription' => (bool) ($item->product?->requires_prescription ?? false),
                    'thumbnail_url' => $item->product?->thumbnail_url,
                ];
            });

            $cleanPhone = preg_replace('/[^0-9]/', '', (string) ($cart->customer?->phone ?? ''));
            $storeName = auth()->user()->tenant->store_name ?? 'our pharmacy';
            $whatsappMessage = rawurlencode(
                "Hello " . ($cart->customer?->first_name ?? 'Customer') . ", we noticed you left some medicines in your cart at " . $storeName . ". Can we help you complete your order?"
            );

            return response()->json([
                'id' => $cart->id,
                'customer' => [
                    'id' => $cart->customer?->id,
                    'name' => $cart->customer?->full_name ?? 'Guest',
                    'email' => $cart->customer?->email,
                    'phone' => $cart->customer?->phone,
                    'clean_phone' => $cleanPhone,
                    'whatsapp_url' => $cleanPhone ? "https://wa.me/{$cleanPhone}?text={$whatsappMessage}" : null,
                    'created_at' => $cart->customer?->created_at?->format('M d, Y'),
                ],
                'coupon' => $cart->coupon ? [
                    'code' => $cart->coupon->code,
                    'type' => $cart->coupon->type,
                    'value' => (float) $cart->coupon->value,
                ] : null,
                'subtotal' => $cart->subtotal,
                'total_items_count' => $cart->total_items_count,
                'has_prescription_items' => $cart->has_prescription_items,
                'idle_time' => $cart->idle_time,
                'updated_at' => $cart->updated_at?->format('M d, Y h:i A'),
                'is_contacted' => $cart->is_contacted,
                'recovery_contacted_at' => $cart->recovery_contacted_at?->format('M d, Y h:i A'),
                'recovery_note' => $cart->recovery_note,
                'items' => $items,
            ]);
        }

        return view('store_admin.pages.abandoned_checkouts.show', compact('cart'));
    }

    /**
     * Mark cart as contacted by tele-sales staff.
     */
    public function markContacted(Request $request, int $id)
    {
        $tenantId = tenantId();

        $cart = Cart::where('tenant_id', $tenantId)->findOrFail($id);

        $validated = $request->validate([
            'recovery_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $cart->recovery_contacted_at = now();
        if (array_key_exists('recovery_note', $validated) && $validated['recovery_note'] !== null) {
            $cart->recovery_note = $validated['recovery_note'];
        }
        $cart->save();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Cart marked as contacted successfully.',
                'contacted_at' => $cart->recovery_contacted_at->format('M d, Y h:i A'),
                'recovery_note' => $cart->recovery_note,
            ]);
        }

        return redirect()->back()->with('success', 'Customer marked as contacted.');
    }

    /**
     * Update tele-sales recovery note for the cart.
     */
    public function updateNote(Request $request, int $id)
    {
        $tenantId = tenantId();

        $cart = Cart::where('tenant_id', $tenantId)->findOrFail($id);

        $validated = $request->validate([
            'recovery_note' => ['nullable', 'string', 'max:1000'],
            'mark_contacted' => ['nullable', 'boolean'],
        ]);

        $cart->recovery_note = $validated['recovery_note'] ?? null;

        if ($request->boolean('mark_contacted') && !$cart->recovery_contacted_at) {
            $cart->recovery_contacted_at = now();
        }

        $cart->save();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Recovery note saved successfully.',
                'recovery_note' => $cart->recovery_note,
                'recovery_contacted_at' => $cart->recovery_contacted_at?->format('M d, Y h:i A'),
            ]);
        }

        return redirect()->back()->with('success', 'Recovery note saved.');
    }

    /**
     * Delete an abandoned cart.
     */
    public function destroy(int $id)
    {
        $tenantId = tenantId();

        $cart = Cart::where('tenant_id', $tenantId)->findOrFail($id);
        $cart->delete();

        return redirect()->back()->with('success', 'Cart removed successfully.');
    }
}
