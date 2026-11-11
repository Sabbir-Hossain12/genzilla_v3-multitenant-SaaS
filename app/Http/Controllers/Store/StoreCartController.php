<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class StoreCartController extends Controller
{
    /**
     * Resolve the active tenant ID for storefront.
     */
    protected function getTenantId(): int
    {
        if (function_exists('tenantId') && tenantId()) {
            return (int) tenantId();
        }
        $tenant = tenant() ?? Tenant::first();
        return (int) ($tenant?->id ?? 1);
    }

    /**
     * Resolve the session token for guest identification.
     */
    protected function resolveSessionToken(Request $request): string
    {
        $headerToken = $request->header('X-Cart-Session') ?? $request->input('session_token');
        if (!empty($headerToken) && is_string($headerToken)) {
            return substr(trim($headerToken), 0, 100);
        }

        $sessionToken = $request->session()->get('storefront_cart_token');
        if (!$sessionToken) {
            $sessionToken = 'sess_' . Str::random(40);
            $request->session()->put('storefront_cart_token', $sessionToken);
        }

        return $sessionToken;
    }

    /**
     * Find or create the active database Cart record for the tenant and user/session.
     */
    protected function resolveCart(Request $request, int $tenantId): Cart
    {
        $customer = auth('customer')->user();
        $sessionToken = $this->resolveSessionToken($request);

        if ($customer) {
            // Check if there is an existing customer cart
            $cart = Cart::where('tenant_id', $tenantId)
                ->where('customer_id', $customer->id)
                ->first();

            // If no customer cart exists, check if there was a guest cart with this session to adopt
            if (!$cart) {
                $guestCart = Cart::where('tenant_id', $tenantId)
                    ->where('session_token', $sessionToken)
                    ->whereNull('customer_id')
                    ->first();

                if ($guestCart) {
                    $guestCart->update(['customer_id' => $customer->id]);
                    $cart = $guestCart;
                } else {
                    $cart = Cart::create([
                        'tenant_id' => $tenantId,
                        'customer_id' => $customer->id,
                        'session_token' => $sessionToken,
                    ]);
                }
            }

            return $cart;
        }

        // Guest user cart
        $cart = Cart::where('tenant_id', $tenantId)
            ->where('session_token', $sessionToken)
            ->first();

        if (!$cart) {
            $cart = Cart::create([
                'tenant_id' => $tenantId,
                'session_token' => $sessionToken,
            ]);
        }

        return $cart;
    }

    /**
     * GET /api/cart
     * Retrieve the database cart with formatted items, counts, and subtotal.
     */
    public function getCart(Request $request): JsonResponse
    {
        $tenantId = $this->getTenantId();
        $cart = $this->resolveCart($request, $tenantId);

        $cart->load([
            'items.product:id,name,slug,requires_prescription,status',
            'items.product.media',
            'items.productVariant:id,product_id,title,price,compare_at_price,mrp,track_inventory,is_in_stock',
        ]);

        $formattedItems = $cart->items->map(function (CartItem $item) {
            $variant = $item->productVariant;
            $product = $item->product;

            $price = (float) ($variant?->price ?? 0);
            $orig = (float) ($variant?->compare_at_price ?? $variant?->mrp ?? $price);

            return [
                'id' => $variant ? "{$product->id}-{$variant->id}" : (string) $product->id,
                'product_id' => $product->id,
                'variant_id' => $variant?->id,
                'variant_title' => $variant?->title,
                'name' => $product->name,
                'slug' => $product->slug,
                'price' => $price,
                'original' => $orig > $price ? $orig : $price,
                'qty' => (int) $item->quantity,
                'image' => $product->thumbnail_url ?? 'https://via.placeholder.com/300',
                'requires_prescription' => (bool) $product->requires_prescription,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'cart_id' => $cart->id,
            'session_token' => $cart->session_token,
            'items' => $formattedItems,
            'count' => $cart->total_items_count,
            'subtotal' => $cart->subtotal,
            'has_prescription_items' => $cart->has_prescription_items,
        ]);
    }

    /**
     * POST /api/cart/sync
     * Synchronize the frontend cart items array to the database.
     */
    public function syncCart(Request $request): JsonResponse
    {
        $request->validate([
            'items' => ['present', 'array'],
            'items.*.product_id' => ['required', 'integer'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.variant_id' => ['nullable', 'integer'],
            'session_token' => ['nullable', 'string', 'max:100'],
        ]);

        $tenantId = $this->getTenantId();
        $cart = $this->resolveCart($request, $tenantId);
        $incomingItems = $request->input('items', []);

        DB::beginTransaction();
        try {
            if (empty($incomingItems)) {
                // If cart was emptied on frontend, clear all database items
                $cart->items()->delete();
                $cart->touch();
                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Cart cleared in database.',
                    'count' => 0,
                    'subtotal' => 0,
                    'items' => [],
                ]);
            }

            // Collect product IDs and variant IDs to validate against current tenant
            $productIds = collect($incomingItems)->pluck('product_id')->filter()->unique()->all();
            $products = Product::where('tenant_id', $tenantId)
                ->whereIn('id', $productIds)
                ->with(['defaultVariant', 'variants'])
                ->get()
                ->keyBy('id');

            $validVariantIds = [];

            foreach ($incomingItems as $itemData) {
                $productId = (int) $itemData['product_id'];
                $quantity = max(1, (int) ($itemData['quantity'] ?? 1));

                $product = $products->get($productId);
                if (!$product) {
                    continue; // Skip invalid or cross-tenant product
                }

                // Resolve variant ID: either specified or default
                $variantId = !empty($itemData['variant_id'])
                    ? (int) $itemData['variant_id']
                    : ($product->defaultVariant?->id ?? $product->variants->first()?->id);

                if (!$variantId) {
                    // Safety: find or create default variant if missing
                    $variant = ProductVariant::firstOrCreate(
                        ['tenant_id' => $tenantId, 'product_id' => $product->id, 'title' => 'Default'],
                        ['price' => (float) ($itemData['price'] ?? 0), 'is_default' => true, 'status' => 'active']
                    );
                    $variantId = $variant->id;
                }

                // Upsert cart item
                CartItem::updateOrCreate(
                    [
                        'cart_id' => $cart->id,
                        'product_variant_id' => $variantId,
                    ],
                    [
                        'product_id' => $product->id,
                        'quantity' => $quantity,
                    ]
                );

                $validVariantIds[] = $variantId;
            }

            // Remove any old cart items for this cart that are not in the current sync payload
            if (!empty($validVariantIds)) {
                $cart->items()->whereNotIn('product_variant_id', $validVariantIds)->delete();
            } else {
                $cart->items()->delete();
            }

            $cart->touch();
            DB::commit();

            // Refresh and calculate updated totals
            $cart->load(['items.product', 'items.productVariant']);

            return response()->json([
                'success' => true,
                'cart_id' => $cart->id,
                'session_token' => $cart->session_token,
                'count' => $cart->total_items_count,
                'subtotal' => $cart->subtotal,
                'has_prescription_items' => $cart->has_prescription_items,
            ]);
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('Cart sync error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to synchronize cart with database.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * POST /api/cart/clear
     * Empty all items from the active cart in the database.
     */
    public function clearCart(Request $request): JsonResponse
    {
        $tenantId = $this->getTenantId();
        $cart = $this->resolveCart($request, $tenantId);

        $cart->items()->delete();
        $cart->touch();

        return response()->json([
            'success' => true,
            'message' => 'Cart emptied successfully.',
        ]);
    }
}
