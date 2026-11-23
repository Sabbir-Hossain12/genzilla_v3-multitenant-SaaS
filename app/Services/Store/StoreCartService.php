<?php

namespace App\Services\Store;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StoreCartService
{
    /**
     * Resolve the guest session token from header, input, or session cookie.
     */
    public function resolveSessionToken(Request $request): ?string
    {
        $token = $request->header('X-Cart-Session')
            ?? $request->input('session_token')
            ?? $request->session()->get('storefront_cart_token');

        if (!empty($token) && is_string($token)) {
            return substr(trim($token), 0, 100);
        }

        return null;
    }

    /**
     * Claim or merge guest cart into customer cart upon authentication.
     *
     * Rules:
     * 1. Customer carts are strictly identified by tenant_id + customer_id.
     * 2. Customer carts NEVER retain a guest session_token (session_token is nullified).
     * 3. If guest has no cart -> nothing to merge; return customer's existing cart or create a blank one.
     * 4. If customer has no existing cart in DB -> claim guest cart: set customer_id, clear session_token to null.
     * 5. If customer already has an existing cart in DB -> merge guest items into customer cart,
     *    summing quantities for overlapping product variants, delete the guest cart, and ensure session_token is null.
     */
    public function claimOrMergeGuestCart(int $tenantId, Customer $customer, ?string $sessionToken): ?Cart
    {
        return DB::transaction(function () use ($tenantId, $customer, $sessionToken) {
            // 1. Locate existing customer cart (strictly by tenant_id and customer_id)
            $customerCart = Cart::where('tenant_id', $tenantId)
                ->where('customer_id', $customer->id)
                ->first();

            // Safety cleanup: disassociate any legacy guest session_token from customer cart
            if ($customerCart && $customerCart->session_token !== null) {
                $customerCart->update(['session_token' => null]);
            }

            if (empty($sessionToken)) {
                if (!$customerCart) {
                    $customerCart = Cart::create([
                        'tenant_id' => $tenantId,
                        'customer_id' => $customer->id,
                        'session_token' => null,
                    ]);
                }
                return $customerCart;
            }

            // 2. Locate guest cart (strictly where customer_id IS NULL and session_token matches)
            $guestCart = Cart::where('tenant_id', $tenantId)
                ->where('session_token', $sessionToken)
                ->whereNull('customer_id')
                ->first();

            // Case A: No guest cart exists
            if (!$guestCart) {
                if (!$customerCart) {
                    $customerCart = Cart::create([
                        'tenant_id' => $tenantId,
                        'customer_id' => $customer->id,
                        'session_token' => null,
                    ]);
                }
                return $customerCart;
            }

            // Case B: Customer has NO existing cart -> Direct Claim
            if (!$customerCart) {
                // Claim guest cart: set customer_id and nullify session_token so guest disassociates
                $guestCart->update([
                    'customer_id' => $customer->id,
                    'session_token' => null,
                ]);
                $guestCart->touch();

                Log::info("Cart Claimed: Guest cart #{$guestCart->id} claimed by customer #{$customer->id}");
                return $guestCart;
            }

            // Case C: Customer already has a cart -> Merge guest items into customer cart
            $guestItems = $guestCart->items()->get();

            foreach ($guestItems as $gItem) {
                $existingItem = CartItem::where('cart_id', $customerCart->id)
                    ->where('product_variant_id', $gItem->product_variant_id)
                    ->first();

                if ($existingItem) {
                    $existingItem->update([
                        'quantity' => $existingItem->quantity + $gItem->quantity,
                    ]);
                } else {
                    CartItem::create([
                        'cart_id' => $customerCart->id,
                        'product_id' => $gItem->product_id,
                        'product_variant_id' => $gItem->product_variant_id,
                        'quantity' => $gItem->quantity,
                    ]);
                }
            }

            // Delete obsolete guest cart
            $guestCartId = $guestCart->id;
            $guestCart->items()->delete();
            $guestCart->delete();

            // Ensure customer cart has session_token = null
            $customerCart->update([
                'session_token' => null,
            ]);
            $customerCart->touch();

            Log::info("Cart Merged: Guest cart #{$guestCartId} merged into customer cart #{$customerCart->id} for customer #{$customer->id}");

            return $customerCart;
        });
    }
}
