<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderPayment;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Models\Tenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TenantTransactionSeeder extends Seeder
{
    public function run(): void
    {
        $tenants = Tenant::all();

        foreach ($tenants as $tenant) {
            DB::transaction(function () use ($tenant) {
                $customer = Customer::where('tenant_id', $tenant->id)->first();
                $product = Product::where('tenant_id', $tenant->id)->with('variants')->first();
                $variant = $product?->variants->first();

                // 1. Ensure Pending COD transaction for ORD-1002 if not present
                $order2 = Order::where('tenant_id', $tenant->id)->where('order_number', 'ORD-1002')->first();
                if ($order2) {
                    OrderPayment::firstOrCreate(
                        ['tenant_id' => $tenant->id, 'payment_method' => 'cod', 'transaction_id' => 'COD-ORD-1002'],
                        [
                            'order_id' => $order2->id,
                            'type' => 'charge',
                            'amount' => $order2->total,
                            'currency' => $order2->currency ?? 'USD',
                            'status' => 'pending',
                            'gateway_response' => ['note' => 'Awaiting cash collection by courier on delivery'],
                            'paid_at' => null,
                        ]
                    );
                }

                // 2. Add bKash payment for ORD-2026-RX1
                $orderRx1 = Order::where('tenant_id', $tenant->id)->where('order_number', 'ORD-2026-RX1')->first();
                if ($orderRx1) {
                    OrderPayment::firstOrCreate(
                        ['tenant_id' => $tenant->id, 'payment_method' => 'bkash', 'transaction_id' => 'BKASH-9X82KL1'],
                        [
                            'order_id' => $orderRx1->id,
                            'type' => 'charge',
                            'amount' => $orderRx1->total,
                            'currency' => 'BDT',
                            'status' => 'successful',
                            'gateway_response' => [
                                'gateway' => 'bKash Merchant Checkout',
                                'sender_mobile' => '01711223344',
                                'trx_id' => '9X82KL1',
                                'merchant_invoice_no' => $orderRx1->order_number,
                            ],
                            'paid_at' => now()->subDays(1),
                        ]
                    );
                    $orderRx1->update(['payment_status' => 'paid', 'payment_method' => 'bkash']);
                }

                // 3. Add Pending Nagad payment for ORD-2026-RX2
                $orderRx2 = Order::where('tenant_id', $tenant->id)->where('order_number', 'ORD-2026-RX2')->first();
                if ($orderRx2) {
                    OrderPayment::firstOrCreate(
                        ['tenant_id' => $tenant->id, 'payment_method' => 'nagad', 'transaction_id' => 'NAGAD-77A1BC9'],
                        [
                            'order_id' => $orderRx2->id,
                            'type' => 'charge',
                            'amount' => $orderRx2->total,
                            'currency' => 'BDT',
                            'status' => 'pending',
                            'gateway_response' => [
                                'gateway' => 'Nagad Online Gateway',
                                'sender_mobile' => '01755123456',
                                'status' => 'Initiated - Verification Pending',
                            ],
                            'paid_at' => null,
                        ]
                    );
                }

                // 4. Create Order ORD-2026-105 with Card charge and partial refund
                $order105 = Order::firstOrCreate(
                    ['tenant_id' => $tenant->id, 'order_number' => 'ORD-2026-105'],
                    [
                        'public_id' => (string) Str::uuid(),
                        'customer_id' => $customer?->id,
                        'status' => 'delivered',
                        'payment_method' => 'card',
                        'payment_status' => 'partially_paid',
                        'currency' => 'BDT',
                        'subtotal' => 1400.00,
                        'shipping_charge' => 50.00,
                        'discount_amount' => 0.00,
                        'tax_amount' => 0.00,
                        'total' => 1450.00,
                        'shipping_name' => 'Haji Mohammad Ali',
                        'shipping_phone' => '+880 1711-234567',
                        'shipping_email' => 'haji.ali@example.com',
                        'shipping_address' => 'House 34, Road 11, Dhanmondi',
                        'shipping_city' => 'Dhaka',
                        'shipping_area' => 'Dhanmondi',
                        'placed_at' => now()->subDays(4),
                        'delivered_at' => now()->subDays(2),
                    ]
                );

                if ($variant) {
                    OrderProduct::firstOrCreate(
                        ['tenant_id' => $tenant->id, 'order_id' => $order105->id, 'variant_id' => $variant->id],
                        [
                            'product_name' => $product->name,
                            'variant_title' => $variant->title,
                            'sku' => $variant->sku,
                            'variant_options' => [],
                            'requires_prescription' => false,
                            'quantity' => 2,
                            'unit_price' => 700.00,
                            'discount_amount' => 0.00,
                            'line_total' => 1400.00,
                        ]
                    );
                }

                $charge105 = OrderPayment::firstOrCreate(
                    ['tenant_id' => $tenant->id, 'payment_method' => 'card', 'transaction_id' => 'TXN-CARD-5501'],
                    [
                        'order_id' => $order105->id,
                        'type' => 'charge',
                        'amount' => 1450.00,
                        'currency' => 'BDT',
                        'status' => 'successful',
                        'gateway_response' => [
                            'gateway' => 'stripe_checkout',
                            'card_brand' => 'Visa',
                            'last4' => '4242',
                            'auth_code' => 'AUTH99201',
                        ],
                        'paid_at' => now()->subDays(4),
                    ]
                );

                // Child Refund on ORD-2026-105
                OrderPayment::firstOrCreate(
                    ['tenant_id' => $tenant->id, 'payment_method' => 'card', 'transaction_id' => 'REF-2026-001'],
                    [
                        'order_id' => $order105->id,
                        'type' => 'refund',
                        'parent_id' => $charge105->id,
                        'amount' => 250.00,
                        'currency' => 'BDT',
                        'status' => 'successful',
                        'gateway_response' => [
                            'refund_reason' => 'Customer returned 1 damaged package of medicine. Pharmacist authorized partial refund.',
                            'processed_by' => 'Store Pharmacist Admin',
                        ],
                        'paid_at' => now()->subDays(2),
                    ]
                );

                // 5. Create Order ORD-2026-106 with Bank Wire Transfer
                $order106 = Order::firstOrCreate(
                    ['tenant_id' => $tenant->id, 'order_number' => 'ORD-2026-106'],
                    [
                        'public_id' => (string) Str::uuid(),
                        'customer_id' => $customer?->id,
                        'status' => 'processing',
                        'payment_method' => 'bank_transfer',
                        'payment_status' => 'paid',
                        'currency' => 'BDT',
                        'subtotal' => 3150.00,
                        'shipping_charge' => 50.00,
                        'discount_amount' => 0.00,
                        'tax_amount' => 0.00,
                        'total' => 3200.00,
                        'shipping_name' => 'Tariqul Islam',
                        'shipping_phone' => '+880 1819-876543',
                        'shipping_email' => 'tariqul.islam@example.com',
                        'shipping_address' => 'Flat 4A, Green Garden Heights, Mirpur DOHS',
                        'shipping_city' => 'Dhaka',
                        'placed_at' => now()->subHours(16),
                    ]
                );

                OrderPayment::firstOrCreate(
                    ['tenant_id' => $tenant->id, 'payment_method' => 'bank_transfer', 'transaction_id' => 'BANK-WIRE-8812'],
                    [
                        'order_id' => $order106->id,
                        'type' => 'charge',
                        'amount' => 3200.00,
                        'currency' => 'BDT',
                        'status' => 'successful',
                        'gateway_response' => [
                            'bank_name' => 'City Bank Ltd',
                            'branch' => 'Mirpur Branch, Dhaka',
                            'account_no' => '******9012',
                            'deposit_slip_ref' => 'DEPOSIT-SLIP-44910',
                            'verified_by' => 'Store Finance Admin',
                        ],
                        'paid_at' => now()->subHours(12),
                    ]
                );

                // 6. Create Order ORD-2026-107 with Failed Rocket attempt
                $order107 = Order::firstOrCreate(
                    ['tenant_id' => $tenant->id, 'order_number' => 'ORD-2026-107'],
                    [
                        'public_id' => (string) Str::uuid(),
                        'customer_id' => $customer?->id,
                        'status' => 'pending',
                        'payment_method' => 'rocket',
                        'payment_status' => 'failed',
                        'currency' => 'BDT',
                        'subtotal' => 900.00,
                        'shipping_charge' => 50.00,
                        'discount_amount' => 0.00,
                        'tax_amount' => 0.00,
                        'total' => 950.00,
                        'shipping_name' => 'Begum Rokeya Sultana',
                        'shipping_phone' => '+880 1912-345678',
                        'shipping_email' => 'rokeya.sultana@example.com',
                        'shipping_address' => 'Holding 18, Road 4, Sector 7, Uttara',
                        'shipping_city' => 'Dhaka',
                        'placed_at' => now()->subHours(8),
                    ]
                );

                OrderPayment::firstOrCreate(
                    ['tenant_id' => $tenant->id, 'payment_method' => 'rocket', 'transaction_id' => 'ROCKET-FAIL-01'],
                    [
                        'order_id' => $order107->id,
                        'type' => 'charge',
                        'amount' => 950.00,
                        'currency' => 'BDT',
                        'status' => 'failed',
                        'gateway_response' => [
                            'gateway' => 'DBBL Rocket MFS',
                            'error_code' => 'MFS_INSUFFICIENT_BALANCE',
                            'error_message' => 'Transaction declined due to insufficient customer wallet balance.',
                        ],
                        'paid_at' => null,
                    ]
                );
            });
        }
    }
}
