<?php

namespace Database\Seeders;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Tenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TenantAbandonedCartSeeder extends Seeder
{
    public function run(): void
    {
        $tenants = Tenant::all();

        foreach ($tenants as $tenant) {
            DB::transaction(function () use ($tenant) {
                // Ensure customers exist
                $customer1 = Customer::firstOrCreate(
                    ['tenant_id' => $tenant->id, 'email' => 'tanvir.ahmed@example.com'],
                    [
                        'first_name' => 'Tanvir',
                        'last_name' => 'Ahmed',
                        'phone' => '01711223344',
                        'status' => 'active',
                    ]
                );

                $customer2 = Customer::firstOrCreate(
                    ['tenant_id' => $tenant->id, 'email' => 'nusrat.jahan@example.com'],
                    [
                        'first_name' => 'Nusrat',
                        'last_name' => 'Jahan',
                        'phone' => '01811223344',
                        'status' => 'active',
                    ]
                );

                $customer3 = Customer::firstOrCreate(
                    ['tenant_id' => $tenant->id, 'email' => 'rafiqul.islam@example.com'],
                    [
                        'first_name' => 'Rafiqul',
                        'last_name' => 'Islam',
                        'phone' => '01911223344',
                        'status' => 'active',
                    ]
                );

                // Fetch available products
                $products = Product::where('tenant_id', $tenant->id)->with('variants')->get();
                if ($products->isEmpty()) {
                    return;
                }

                $p1 = $products->first();
                $v1 = $p1->variants->first();

                $p2 = $products->skip(1)->first() ?? $p1;
                $v2 = $p2->variants->first() ?? $v1;

                $p3 = $products->skip(2)->first() ?? $p1;
                $v3 = $p3->variants->first() ?? $v1;

                // ==========================================
                // 1. RECOVERABLE CARTS (Registered Customers)
                // ==========================================

                // Cart 1: Tanvir (Uncontacted, idle 3 hours, has items)
                $cart1 = Cart::updateOrCreate(
                    ['tenant_id' => $tenant->id, 'customer_id' => $customer1->id],
                    [
                        'session_token' => null,
                        'recovery_contacted_at' => null,
                        'recovery_note' => null,
                        'updated_at' => now()->subHours(3),
                        'created_at' => now()->subHours(4),
                    ]
                );
                CartItem::updateOrCreate(
                    ['cart_id' => $cart1->id, 'product_variant_id' => $v1->id],
                    ['product_id' => $p1->id, 'quantity' => 2]
                );
                if ($v2 && $v2->id !== $v1->id) {
                    CartItem::updateOrCreate(
                        ['cart_id' => $cart1->id, 'product_variant_id' => $v2->id],
                        ['product_id' => $p2->id, 'quantity' => 1]
                    );
                }

                // Cart 2: Nusrat (Contacted 1 hour ago with note, idle 5 hours)
                $cart2 = Cart::updateOrCreate(
                    ['tenant_id' => $tenant->id, 'customer_id' => $customer2->id],
                    [
                        'session_token' => null,
                        'recovery_contacted_at' => now()->subHours(1),
                        'recovery_note' => 'Called patient. Customer requested prescription dosage clarification on WhatsApp before proceeding.',
                        'updated_at' => now()->subHours(5),
                        'created_at' => now()->subHours(6),
                    ]
                );
                if ($v2) {
                    CartItem::updateOrCreate(
                        ['cart_id' => $cart2->id, 'product_variant_id' => $v2->id],
                        ['product_id' => $p2->id, 'quantity' => 3]
                    );
                }

                // Cart 3: Rafiqul (Uncontacted, idle 7 hours)
                $cart3 = Cart::updateOrCreate(
                    ['tenant_id' => $tenant->id, 'customer_id' => $customer3->id],
                    [
                        'session_token' => null,
                        'recovery_contacted_at' => null,
                        'recovery_note' => null,
                        'updated_at' => now()->subHours(7),
                        'created_at' => now()->subHours(8),
                    ]
                );
                if ($v3) {
                    CartItem::updateOrCreate(
                        ['cart_id' => $cart3->id, 'product_variant_id' => $v3->id],
                        ['product_id' => $p3->id, 'quantity' => 4]
                    );
                }

                // ==========================================
                // 2. ANONYMOUS GUEST CARTS (Catalog Friction Analytics)
                // ==========================================

                // Guest Cart A: idle 4 hours
                $guestCartA = Cart::updateOrCreate(
                    ['tenant_id' => $tenant->id, 'session_token' => 'guest_sess_tok_4a8b1c2d'],
                    [
                        'customer_id' => null,
                        'recovery_contacted_at' => null,
                        'recovery_note' => null,
                        'updated_at' => now()->subHours(4),
                        'created_at' => now()->subHours(5),
                    ]
                );
                CartItem::updateOrCreate(
                    ['cart_id' => $guestCartA->id, 'product_variant_id' => $v1->id],
                    ['product_id' => $p1->id, 'quantity' => 5]
                );

                // Guest Cart B: idle 9 hours
                $guestCartB = Cart::updateOrCreate(
                    ['tenant_id' => $tenant->id, 'session_token' => 'guest_sess_tok_9e8f7a6b'],
                    [
                        'customer_id' => null,
                        'recovery_contacted_at' => null,
                        'recovery_note' => null,
                        'updated_at' => now()->subHours(9),
                        'created_at' => now()->subHours(10),
                    ]
                );
                CartItem::updateOrCreate(
                    ['cart_id' => $guestCartB->id, 'product_variant_id' => $v1->id],
                    ['product_id' => $p1->id, 'quantity' => 3]
                );
                if ($v2 && $v2->id !== $v1->id) {
                    CartItem::updateOrCreate(
                        ['cart_id' => $guestCartB->id, 'product_variant_id' => $v2->id],
                        ['product_id' => $p2->id, 'quantity' => 2]
                    );
                }

                // Guest Cart C: idle 18 hours
                $guestCartC = Cart::updateOrCreate(
                    ['tenant_id' => $tenant->id, 'session_token' => 'guest_sess_tok_3c4d5e6f'],
                    [
                        'customer_id' => null,
                        'recovery_contacted_at' => null,
                        'recovery_note' => null,
                        'updated_at' => now()->subHours(18),
                        'created_at' => now()->subHours(20),
                    ]
                );
                if ($v3) {
                    CartItem::updateOrCreate(
                        ['cart_id' => $guestCartC->id, 'product_variant_id' => $v3->id],
                        ['product_id' => $p3->id, 'quantity' => 2]
                    );
                }
            });
        }
    }
}
