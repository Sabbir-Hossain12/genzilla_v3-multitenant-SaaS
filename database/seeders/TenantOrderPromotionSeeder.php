<?php

namespace Database\Seeders;

use App\Models\Coupon;
use App\Models\DeliveryCharge;
use App\Models\Order;
use App\Models\OrderPayment;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TenantOrderPromotionSeeder extends Seeder
{
    public function run(): void
    {
        $tenants = Tenant::all();

        foreach ($tenants as $tenant) {
            // 1. Delivery Charges
            $standard = DeliveryCharge::firstOrCreate(
                ['tenant_id' => $tenant->id, 'slug' => 'standard-delivery'],
                [
                    'title' => 'Standard Delivery',
                    'description' => 'Delivered to your doorstep within 2-4 business days.',
                    'charge_amount' => 5.00,
                    'min_order_amount' => null,
                    'estimated_days' => '2-4 business days',
                    'sort_order' => 1,
                    'status' => true,
                ]
            );

            $express = DeliveryCharge::firstOrCreate(
                ['tenant_id' => $tenant->id, 'slug' => 'express-delivery'],
                [
                    'title' => 'Express Same-Day Delivery',
                    'description' => 'Fast delivery guaranteed on the same day for orders placed before 2 PM.',
                    'charge_amount' => 15.00,
                    'min_order_amount' => null,
                    'estimated_days' => 'Same day',
                    'sort_order' => 2,
                    'status' => true,
                ]
            );

            $free = DeliveryCharge::firstOrCreate(
                ['tenant_id' => $tenant->id, 'slug' => 'free-shipping-threshold'],
                [
                    'title' => 'Free Delivery on Orders Over $50',
                    'description' => 'Complimentary standard shipping for orders totaling $50 or more.',
                    'charge_amount' => 0.00,
                    'min_order_amount' => 50.00,
                    'estimated_days' => '3-5 business days',
                    'sort_order' => 3,
                    'status' => true,
                ]
            );

            // 2. Coupons
            $couponWelcome = Coupon::firstOrCreate(
                ['tenant_id' => $tenant->id, 'code' => 'WELCOME10'],
                [
                    'type' => 'percentage',
                    'value' => 10.00,
                    'min_order_amount' => 20.00,
                    'max_discount_amount' => 15.00,
                    'usage_limit' => 200,
                    'usage_limit_per_customer' => 1,
                    'used_count' => 12,
                    'starts_at' => now()->subDays(5),
                    'expires_at' => now()->addDays(60),
                    'is_active' => true,
                ]
            );

            $couponFixed = Coupon::firstOrCreate(
                ['tenant_id' => $tenant->id, 'code' => 'SAVE5'],
                [
                    'type' => 'fixed',
                    'value' => 5.00,
                    'min_order_amount' => 30.00,
                    'max_discount_amount' => 5.00,
                    'usage_limit' => 100,
                    'usage_limit_per_customer' => 2,
                    'used_count' => 5,
                    'starts_at' => now()->subDays(10),
                    'expires_at' => now()->addDays(30),
                    'is_active' => true,
                ]
            );

            $couponFlash = Coupon::firstOrCreate(
                ['tenant_id' => $tenant->id, 'code' => 'FLASH20'],
                [
                    'type' => 'percentage',
                    'value' => 20.00,
                    'min_order_amount' => 40.00,
                    'max_discount_amount' => 25.00,
                    'usage_limit' => 50,
                    'usage_limit_per_customer' => 1,
                    'used_count' => 0,
                    'starts_at' => now(),
                    'expires_at' => now()->addDays(3),
                    'is_active' => true,
                ]
            );

            // 3. Sample Orders
            $product = Product::where('tenant_id', $tenant->id)->with('variants')->first();
            $customer = User::where('id', '!=', $tenant->user_id)->first() ?? $tenant->user;

            if ($product && $product->variants->isNotEmpty()) {
                $variant = $product->variants->first();

                // Order 1: Delivered & Paid via Card
                $order1 = Order::firstOrCreate(
                    ['tenant_id' => $tenant->id, 'order_number' => 'ORD-1001'],
                    [
                        'public_id' => (string) Str::uuid(),
                        'customer_id' => $customer?->id,
                        'status' => 'delivered',
                        'payment_method' => 'card',
                        'payment_status' => 'paid',
                        'prescription_status' => $product->requires_prescription ? 'approved' : null,
                        'currency' => 'USD',
                        'subtotal' => (float)$variant->price * 2,
                        'discount_amount' => 5.00,
                        'shipping_charge' => 5.00,
                        'tax_amount' => 0.00,
                        'total' => max(0, ((float)$variant->price * 2) - 5.00 + 5.00),
                        'coupon_id' => $couponFixed->id,
                        'coupon_code' => $couponFixed->code,
                        'delivery_charge_id' => $standard->id,
                        'shipping_method' => $standard->title,
                        'shipping_name' => $customer?->name ?? 'John Doe',
                        'shipping_phone' => '+1 (555) 234-5678',
                        'shipping_email' => $customer?->email ?? 'customer@example.com',
                        'shipping_address' => '742 Evergreen Terrace',
                        'shipping_area' => 'Downtown',
                        'shipping_city' => 'Springfield',
                        'shipping_postcode' => '97477',
                        'shipping_country' => 'United States',
                        'order_note' => 'Please leave package by the front door.',
                        'placed_at' => now()->subDays(4),
                        'shipped_at' => now()->subDays(3),
                        'delivered_at' => now()->subDays(1),
                    ]
                );

                OrderProduct::firstOrCreate(
                    ['tenant_id' => $tenant->id, 'order_id' => $order1->id, 'variant_id' => $variant->id],
                    [
                        'product_name' => $product->name,
                        'variant_title' => $variant->title,
                        'sku' => $variant->sku,
                        'variant_options' => [],
                        'requires_prescription' => (bool)$product->requires_prescription,
                        'mrp' => $variant->mrp,
                        'quantity' => 2,
                        'unit_price' => $variant->price,
                        'discount_amount' => 5.00,
                        'line_total' => ((float)$variant->price * 2) - 5.00,
                    ]
                );

                OrderPayment::firstOrCreate(
                    ['tenant_id' => $tenant->id, 'order_id' => $order1->id, 'transaction_id' => 'TXN-CARD-1001'],
                    [
                        'type' => 'charge',
                        'payment_method' => 'card',
                        'amount' => $order1->total,
                        'currency' => 'USD',
                        'status' => 'successful',
                        'gateway_response' => ['gateway' => 'stripe', 'auth_code' => 'AUTH123456'],
                        'paid_at' => now()->subDays(4),
                    ]
                );

                // Order 2: Processing & Unpaid (Cash on Delivery)
                $order2 = Order::firstOrCreate(
                    ['tenant_id' => $tenant->id, 'order_number' => 'ORD-1002'],
                    [
                        'public_id' => (string) Str::uuid(),
                        'customer_id' => $customer?->id,
                        'status' => 'processing',
                        'payment_method' => 'cod',
                        'payment_status' => 'unpaid',
                        'prescription_status' => $product->requires_prescription ? 'pending_review' : null,
                        'currency' => 'USD',
                        'subtotal' => (float)$variant->price,
                        'discount_amount' => 0.00,
                        'shipping_charge' => 15.00,
                        'tax_amount' => 0.00,
                        'total' => (float)$variant->price + 15.00,
                        'delivery_charge_id' => $express->id,
                        'shipping_method' => $express->title,
                        'shipping_name' => 'Alice Johnson',
                        'shipping_phone' => '+1 (555) 876-5432',
                        'shipping_email' => 'alice@example.com',
                        'shipping_address' => '123 Market Street, Apt 4B',
                        'shipping_area' => 'Financial District',
                        'shipping_city' => 'New York',
                        'shipping_postcode' => '10005',
                        'shipping_country' => 'United States',
                        'order_note' => 'Call upon arrival.',
                        'placed_at' => now()->subHours(6),
                    ]
                );

                OrderProduct::firstOrCreate(
                    ['tenant_id' => $tenant->id, 'order_id' => $order2->id, 'variant_id' => $variant->id],
                    [
                        'product_name' => $product->name,
                        'variant_title' => $variant->title,
                        'sku' => $variant->sku,
                        'variant_options' => [],
                        'requires_prescription' => (bool)$product->requires_prescription,
                        'mrp' => $variant->mrp,
                        'quantity' => 1,
                        'unit_price' => $variant->price,
                        'discount_amount' => 0.00,
                        'line_total' => $variant->price,
                    ]
                );

                // Wishlist item
                if ($customer) {
                    Wishlist::firstOrCreate(
                        [
                            'tenant_id' => $tenant->id,
                            'customer_id' => $customer->id,
                            'product_id' => $product->id,
                            'product_variant_id' => $variant->id,
                        ]
                    );
                }
            }
        }
    }
}
