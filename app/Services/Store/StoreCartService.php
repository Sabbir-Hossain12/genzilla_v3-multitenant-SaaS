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
     * Convention:
     * 1. If guest has no cart -> nothing to merge.
     * 2. If customer has no existing cart in DB -> directly claim guest cart (customer_id = $customer->id).
     * 3. If customer already has an existing cart in DB -> merge guest items into customer cart,
     *    summing quantities for overlapping product variants, and delete the guest cart.
     */
    public function claimOrMergeGuestCart(int $tenantId, Customer $customer, ?string $sessionToken): ?Cart
    {
        if (empty($sessionToken)) {
            return Cart::where('tenant_id', $tenantId)->where('customer_id', $customer->id)->first();
        }

        return DB::transaction(function () use ($tenantId, $customer, $sessionToken) {
            // 1. Locate guest cart
            $guestCart = Cart::where('tenant_id', $tenantId)
                ->where('session_token', $sessionToken)
                ->whereNull('customer_id')
                ->first();

            // 2. Locate existing customer cart
            $customerCart = Cart::where('tenant_id', $tenantId)
                ->where('customer_id', $customer->id)
                ->first();

            // Case A: No guest cart exists
            if (!$guestCart) {
                return $customerCart;
            }

            // Case B: Customer has NO existing cart -> Direct Claim
            if (!$customerCart) {
                $guestCart->update([
                    'customer_id' => $customer->id,
                ]);
                $guestCart->touch();

                Log::info("Cart Claimed: Guest cart #{$guestCart->id} (token {$sessionToken}) claimed by customer #{$customer->id}");
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

            // Delete obsolete guest cart first to release unique ['tenant_id', 'session_token'] constraint
            $guestCartId = $guestCart->id;
            $guestCart->items()->delete();
            $guestCart->delete();

            // Now safely update session token on customer cart so current device stays in sync
            $customerCart->update([
                'session_token' => $sessionToken,
            ]);
            $customerCart->touch();

            Log::info("Cart Merged: Guest cart #{$guestCartId} merged into customer cart #{$customerCart->id} for customer #{$customer->id}");

            return $customerCart;
        });
    }
}
