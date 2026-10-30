<?php

namespace Tests\Feature;

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
use App\Models\Tenant;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class StoreOrdersAndPromotionsTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Store Merchant',
            'email' => 'merchant_orders@example.com',
            'password' => bcrypt('password'),
            'status' => 1,
        ]);

        $this->tenant = Tenant::create([
            'user_id' => $this->user->id,
            'store_name' => 'Orders Test Pharmacy',
            'subdomain' => 'orderstest',
            'subscription_status' => 'active',
            'status' => 'active',
        ]);

        $registrar = app(PermissionRegistrar::class);
        $registrar->setPermissionsTeamId($this->tenant->id);

        $role = Role::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Store Owner',
            'guard_name' => 'web',
        ]);

        $permissions = [
            'Store Order List',
            'Store Order Create',
            'Store Order View',
            'Store Order Edit',
            'Store Order Delete',
            'Store Coupon List',
            'Store Coupon Create',
            'Store Coupon Edit',
            'Store Coupon Delete',
            'Store Delivery Charge List',
            'Store Delivery Charge Create',
            'Store Delivery Charge Edit',
            'Store Delivery Charge Delete',
        ];

        foreach ($permissions as $permName) {
            $perm = Permission::firstOrCreate(
                ['name' => $permName, 'guard_name' => 'web'],
                ['group_name' => 'Orders & Promotions', 'panel' => 'store']
            );
            $role->givePermissionTo($perm);
        }

        $this->user->assignRole($role);
    }

    public function test_coupon_crud_and_discount_calculation(): void
    {
        // 1. Create Coupon
        $response = $this->actingAs($this->user)->post($this->storeUrl('/store/promotions/coupons'), [
            'code' => 'save20',
            'type' => 'percentage',
            'value' => 20,
            'min_order_amount' => 50,
            'max_discount_amount' => 30,
            'usage_limit' => 100,
            'usage_limit_per_customer' => 2,
            'is_active' => 1,
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $coupon = Coupon::where('tenant_id', $this->tenant->id)->where('code', 'SAVE20')->first();
        $this->assertNotNull($coupon);
        $this->assertEquals('percentage', $coupon->type);
        $this->assertEquals(20.00, $coupon->value);
        $this->assertTrue($coupon->is_active);

        // 2. Validate Discount Logic
        // Less than min_order_amount
        $this->assertFalse($coupon->isValidForAmount(40.00));
        $this->assertEquals(0.00, $coupon->calculateDiscount(40.00));

        // Eligible amount ($100 subtotal, 20% = $20)
        $this->assertTrue($coupon->isValidForAmount(100.00));
        $this->assertEquals(20.00, $coupon->calculateDiscount(100.00));

        // Capped by max_discount_amount ($200 subtotal, 20% = $40 -> capped at $30)
        $this->assertTrue($coupon->isValidForAmount(200.00));
        $this->assertEquals(30.00, $coupon->calculateDiscount(200.00));

        // 3. Update Coupon
        $updateRes = $this->actingAs($this->user)->put($this->storeUrl('/store/promotions/coupons/' . $coupon->id), [
            'code' => 'save50flat',
            'type' => 'fixed',
            'value' => 50,
            'min_order_amount' => 100,
            'max_discount_amount' => null,
            'is_active' => 1,
        ]);
        $updateRes->assertSessionHasNoErrors();
        $coupon->refresh();
        $this->assertEquals('SAVE50FLAT', $coupon->code);
        $this->assertEquals('fixed', $coupon->type);
        $this->assertEquals(50.00, $coupon->value);

        // Fixed discount check
        $this->assertEquals(50.00, $coupon->calculateDiscount(150.00));

        // 4. Toggle Status
        $toggleRes = $this->actingAs($this->user)->patch($this->storeUrl('/store/promotions/coupons/' . $coupon->id . '/toggle'));
        $toggleRes->assertRedirect();
        $this->assertFalse($coupon->fresh()->is_active);

        // 5. Delete Coupon
        $deleteRes = $this->actingAs($this->user)->delete($this->storeUrl('/store/promotions/coupons/' . $coupon->id));
        $deleteRes->assertRedirect();
        $this->assertDatabaseMissing('coupons', ['id' => $coupon->id]);
    }

    public function test_delivery_charge_crud(): void
    {
        // 1. Create Delivery Charge
        $response = $this->actingAs($this->user)->post($this->storeUrl('/store/promotions/delivery-charges'), [
            'title' => 'Express Courier',
            'charge_amount' => 15.00,
            'min_order_amount' => 100.00,
            'estimated_days' => '1-2 Days',
            'sort_order' => 1,
            'status' => 1,
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $delivery = DeliveryCharge::where('tenant_id', $this->tenant->id)->first();
        $this->assertNotNull($delivery);
        $this->assertEquals('Express Courier', $delivery->title);
        $this->assertEquals('express-courier', $delivery->slug);
        $this->assertEquals(15.00, $delivery->charge_amount);
        $this->assertEquals(100.00, $delivery->min_order_amount);

        // 2. Update Delivery Charge
        $updateRes = $this->actingAs($this->user)->put($this->storeUrl('/store/promotions/delivery-charges/' . $delivery->id), [
            'title' => 'Express Courier Prime',
            'charge_amount' => 12.00,
            'status' => 1,
        ]);
        $updateRes->assertSessionHasNoErrors();
        $this->assertEquals('Express Courier Prime', $delivery->fresh()->title);
        $this->assertEquals(12.00, $delivery->fresh()->charge_amount);

        // 3. Delete Delivery Charge
        $deleteRes = $this->actingAs($this->user)->delete($this->storeUrl('/store/promotions/delivery-charges/' . $delivery->id));
        $deleteRes->assertRedirect();
        $this->assertDatabaseMissing('delivery_charges', ['id' => $delivery->id]);
    }

    public function test_order_lifecycle_status_and_payment_transitions(): void
    {
        $customer = Customer::create([
            'tenant_id' => $this->tenant->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'phone' => '+8801712345678',
            'status' => 'active',
        ]);

        $product = Product::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Paracetamol 500mg',
            'slug' => 'paracetamol-500mg',
            'kind' => 'medicine',
            'status' => 'active',
        ]);

        $variant = ProductVariant::create([
            'tenant_id' => $this->tenant->id,
            'product_id' => $product->id,
            'title' => 'Strip of 10 Tablets',
            'sku' => 'PARA-500-10',
            'price' => 12.50,
            'cost_price' => 7.00,
            'options_hash' => md5('box-20'),
            'track_inventory' => true,
            'is_default' => true,
            'status' => 'active',
        ]);

        $deliveryCharge = DeliveryCharge::create([
            'tenant_id' => $this->tenant->id,
            'title' => 'Standard Courier Delivery',
            'slug' => 'standard-courier-delivery',
            'charge_amount' => 5.00,
            'status' => true,
        ]);

        $coupon = Coupon::create([
            'tenant_id' => $this->tenant->id,
            'code' => 'WELCOME10',
            'type' => 'percentage',
            'value' => 10,
            'min_order_amount' => 20,
            'is_active' => true,
        ]);

        // Create Order manually
        $order = Order::create([
            'tenant_id' => $this->tenant->id,
            'order_number' => 'ORD-10001',
            'customer_id' => $customer->id,
            'status' => 'pending',
            'payment_method' => 'cod',
            'payment_status' => 'unpaid',
            'currency' => 'USD',
            'subtotal' => 25.00,
            'discount_amount' => 2.50,
            'shipping_charge' => 5.00,
            'tax_amount' => 0.00,
            'total' => 27.50,
            'coupon_id' => $coupon->id,
            'coupon_code' => $coupon->code,
            'delivery_charge_id' => $deliveryCharge->id,
            'shipping_method' => $deliveryCharge->title,
            'shipping_name' => 'John Doe',
            'shipping_phone' => '+8801712345678',
            'shipping_email' => 'john@example.com',
            'shipping_address' => 'House 12, Road 4, Banani',
            'shipping_city' => 'Dhaka',
            'shipping_country' => 'Bangladesh',
            'placed_at' => now(),
        ]);

        // Add line items
        OrderProduct::create([
            'tenant_id' => $this->tenant->id,
            'order_id' => $order->id,
            'variant_id' => $variant->id,
            'product_name' => $product->name,
            'variant_title' => $variant->title,
            'sku' => $variant->sku,
            'quantity' => 2,
            'unit_price' => 12.50,
            'discount_amount' => 0.00,
            'line_total' => 25.00,
        ]);

        $this->assertEquals(27.50, (float)$order->total);
        $this->assertEquals('pending', $order->status);
        $this->assertNull($order->shipped_at);
        $this->assertNull($order->delivered_at);

        // 1. Advance Order Status to Shipped
        $statusRes = $this->actingAs($this->user)->patch($this->storeUrl('/store/orders/' . $order->id . '/status'), [
            'status' => 'shipped',
        ]);
        $statusRes->assertRedirect();
        $order->refresh();
        $this->assertEquals('shipped', $order->status);
        $this->assertNotNull($order->shipped_at);

        // 2. Advance Order Status to Delivered
        $deliverRes = $this->actingAs($this->user)->patch($this->storeUrl('/store/orders/' . $order->id . '/status'), [
            'status' => 'delivered',
        ]);
        $deliverRes->assertRedirect();
        $order->refresh();
        $this->assertEquals('delivered', $order->status);
        $this->assertNotNull($order->delivered_at);

        // 3. Update Payment Status to Paid
        $paymentRes = $this->actingAs($this->user)->patch($this->storeUrl('/store/orders/' . $order->id . '/payment-status'), [
            'payment_status' => 'paid',
        ]);
        $paymentRes->assertRedirect();
        $order->refresh();
        $this->assertEquals('paid', $order->payment_status);

        // OrderPayment ledger entry should have been created
        $paymentRecord = OrderPayment::where('tenant_id', $this->tenant->id)->where('order_id', $order->id)->first();
        $this->assertNotNull($paymentRecord);
        $this->assertEquals(27.50, (float)$paymentRecord->amount);
        $this->assertEquals('successful', $paymentRecord->status);
        $this->assertNotNull($paymentRecord->paid_at);

        // 4. Update Prescription Status
        $prescriptionRes = $this->actingAs($this->user)->patch($this->storeUrl('/store/orders/' . $order->id . '/prescription-status'), [
            'prescription_status' => 'approved',
        ]);
        $prescriptionRes->assertRedirect();
        $order->refresh();
        $this->assertEquals('approved', $order->prescription_status);

        // 5. Test Wishlist model relation
        $wishlist = Wishlist::create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $customer->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
        ]);
        $this->assertDatabaseHas('wishlists', [
            'tenant_id' => $this->tenant->id,
            'product_id' => $product->id,
            'customer_id' => $customer->id,
        ]);
    }

    public function test_store_admin_can_manually_create_order_with_live_inventory(): void
    {
        $customer = Customer::create([
            'tenant_id' => $this->tenant->id,
            'first_name' => 'Sherlock',
            'last_name' => 'Holmes',
            'email' => 'sherlock@example.com',
            'phone' => '+447960111222',
            'status' => 'active',
        ]);

        // 1. Setup Catalog with Inventory Batch
        $product = Product::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Antiseptic Solution 250ml',
            'slug' => 'antiseptic-solution-250ml',
            'kind' => 'medicine',
            'status' => 'active',
        ]);

        $variant = ProductVariant::create([
            'tenant_id' => $this->tenant->id,
            'product_id' => $product->id,
            'title' => 'Bottle 250ml',
            'sku' => 'ANTISEPTIC-250',
            'price' => 15.00,
            'cost_price' => 8.00,
            'options_hash' => md5('vol-250'),
            'track_inventory' => true,
            'is_default' => true,
            'status' => 'active',
        ]);

        $batch = InventoryBatch::create([
            'tenant_id' => $this->tenant->id,
            'variant_id' => $variant->id,
            'batch_number' => 'BATCH-TEST-01',
            'quantity' => 50,
            'reserved' => 0,
            'cost_price' => 8.00,
        ]);

        // 2. Delivery Charge & Coupon
        $deliveryCharge = DeliveryCharge::create([
            'tenant_id' => $this->tenant->id,
            'title' => 'Express Courier',
            'slug' => 'express-courier',
            'charge_amount' => 10.00,
            'status' => true,
        ]);

        $coupon = Coupon::create([
            'tenant_id' => $this->tenant->id,
            'code' => 'SAVE10PERCENT',
            'type' => 'percentage',
            'value' => 10,
            'min_order_amount' => 20,
            'is_active' => true,
        ]);

        // 3. Test Create Form Page Render
        $createRes = $this->actingAs($this->user)->get($this->storeUrl('/store/orders/create'));
        $createRes->assertOk();
        $createRes->assertSee('Create New Order');
        $createRes->assertSee('Antiseptic Solution 250ml');
        $createRes->assertSee('Express Courier');

        // 4. Submit Order Form
        $postData = [
            'customer_id' => $customer->id,
            'shipping_name' => 'Sherlock Holmes',
            'shipping_phone' => '+447960111222',
            'shipping_email' => 'sherlock@example.com',
            'shipping_address' => '221B Baker Street',
            'shipping_area' => 'Marylebone',
            'shipping_city' => 'London',
            'shipping_postcode' => 'NW1 6XE',
            'shipping_country' => 'United Kingdom',
            'delivery_charge_id' => $deliveryCharge->id,
            'coupon_id' => $coupon->id,
            'payment_method' => 'cod',
            'payment_status' => 'unpaid',
            'status' => 'pending',
            'order_note' => 'Leave with Mrs Hudson at reception.',
            'items' => [
                [
                    'variant_id' => $variant->id,
                    'unit_price' => 15.00,
                    'quantity' => 2,
                ],
            ],
        ];

        $storeRes = $this->actingAs($this->user)->post($this->storeUrl('/store/orders'), $postData);
        $storeRes->assertSessionHasNoErrors();
        $storeRes->assertRedirect();

        // 5. Verify Database Records
        $order = Order::where('tenant_id', $this->tenant->id)->where('shipping_name', 'Sherlock Holmes')->first();
        $this->assertNotNull($order);
        $this->assertEquals(30.00, (float)$order->subtotal); // 2 x $15
        $this->assertEquals(3.00, (float)$order->discount_amount); // 10% of $30 = $3
        $this->assertEquals(10.00, (float)$order->shipping_charge);
        $this->assertEquals(37.00, (float)$order->total); // 30 - 3 + 10 = 37
        $this->assertEquals('SAVE10PERCENT', $order->coupon_code);
        $this->assertEquals('Express Courier', $order->shipping_method);
        $this->assertEquals('pending', $order->status);

        // Check line items
        $this->assertEquals(1, $order->products()->count());
        $lineItem = $order->products()->first();
        $this->assertEquals('Antiseptic Solution 250ml', $lineItem->product_name);
        $this->assertEquals(2, $lineItem->quantity);
        $this->assertEquals(30.00, (float)$lineItem->line_total);

        // Check inventory reservation delta
        $movement = StockMovement::where('tenant_id', $this->tenant->id)
            ->where('reference_type', 'Order')
            ->where('reference_id', $order->id)
            ->first();
        $this->assertNotNull($movement);
        $this->assertEquals('reserve', $movement->type);
        $this->assertEquals(2, $movement->reserved_delta);
        $this->assertEquals(2, $batch->fresh()->reserved);

        // Coupon used_count increment
        $this->assertEquals(1, $coupon->fresh()->used_count);
    }

    public function test_order_cancellation_releases_reserved_stock(): void
    {
        $product = Product::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Pain Relieving Balm',
            'slug' => 'pain-relieving-balm',
            'kind' => 'general',
            'status' => 'active',
        ]);

        $variant = ProductVariant::create([
            'tenant_id' => $this->tenant->id,
            'product_id' => $product->id,
            'title' => 'Standard Jar',
            'sku' => 'BALM-50',
            'price' => 10.00,
            'cost_price' => 5.00,
            'options_hash' => md5('jar-50'),
            'track_inventory' => true,
            'is_default' => true,
            'status' => 'active',
        ]);

        $batch = InventoryBatch::create([
            'tenant_id' => $this->tenant->id,
            'variant_id' => $variant->id,
            'batch_number' => 'BALM-BATCH-1',
            'quantity' => 20,
            'reserved' => 5, // Initially reserved
        ]);

        $order = Order::create([
            'tenant_id' => $this->tenant->id,
            'order_number' => 'ORD-CANCEL-001',
            'status' => 'pending',
            'payment_method' => 'cod',
            'payment_status' => 'unpaid',
            'currency' => 'USD',
            'subtotal' => 50.00,
            'discount_amount' => 0.00,
            'shipping_charge' => 0.00,
            'tax_amount' => 0.00,
            'total' => 50.00,
            'shipping_name' => 'John Cancel',
            'shipping_phone' => '+1234567890',
            'shipping_address' => 'Some St',
            'shipping_city' => 'Anytown',
            'shipping_country' => 'USA',
            'placed_at' => now(),
        ]);

        OrderProduct::create([
            'tenant_id' => $this->tenant->id,
            'order_id' => $order->id,
            'variant_id' => $variant->id,
            'product_name' => $product->name,
            'variant_title' => $variant->title,
            'sku' => $variant->sku,
            'quantity' => 5,
            'unit_price' => 10.00,
            'line_total' => 50.00,
        ]);

        // Cancel the order
        $cancelRes = $this->actingAs($this->user)->patch($this->storeUrl('/store/orders/' . $order->id . '/cancel'), [
            'status' => 'cancelled',
            'cancel_reason' => 'Customer requested cancellation before shipment',
        ]);

        $cancelRes->assertRedirect();
        $order->refresh();
        $this->assertEquals('cancelled', $order->status);
        $this->assertEquals('Customer requested cancellation before shipment', $order->cancel_reason);
        $this->assertNotNull($order->cancelled_at);

        // Verify reserved stock released
        $this->assertEquals(0, $batch->fresh()->reserved);

        // Verify stock movement recorded
        $releaseMovement = StockMovement::where('tenant_id', $this->tenant->id)
            ->where('reference_type', 'Order')
            ->where('type', 'release')
            ->first();
        $this->assertNotNull($releaseMovement);
        $this->assertEquals(-5, $releaseMovement->reserved_delta);
    }

    public function test_views_render_successfully(): void
    {
        $customer = Customer::create([
            'tenant_id' => $this->tenant->id,
            'first_name' => 'Emma',
            'last_name' => 'Watson',
            'email' => 'emma@example.com',
            'phone' => '+447900123456',
            'status' => 'active',
        ]);

        $product = Product::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Pain Relief Gel',
            'slug' => 'pain-relief-gel',
            'kind' => 'medicine',
            'status' => 'active',
        ]);

        $variant = ProductVariant::create([
            'tenant_id' => $this->tenant->id,
            'product_id' => $product->id,
            'title' => 'Tube 50g',
            'sku' => 'GEL-50',
            'price' => 9.00,
            'cost_price' => 4.00,
            'options_hash' => md5('tube-50'),
            'track_inventory' => false,
            'is_default' => true,
            'status' => 'active',
        ]);

        $order = Order::create([
            'tenant_id' => $this->tenant->id,
            'order_number' => 'ORD-VIEW-1',
            'customer_id' => $customer->id,
            'status' => 'processing',
            'payment_method' => 'card',
            'payment_status' => 'paid',
            'currency' => 'USD',
            'subtotal' => 18.00,
            'discount_amount' => 0.00,
            'shipping_charge' => 5.00,
            'tax_amount' => 0.00,
            'total' => 23.00,
            'shipping_name' => 'Emma Watson',
            'shipping_phone' => '+447900123456',
            'shipping_address' => '221B Baker St',
            'shipping_city' => 'London',
            'shipping_country' => 'United Kingdom',
            'placed_at' => now(),
        ]);

        OrderProduct::create([
            'tenant_id' => $this->tenant->id,
            'order_id' => $order->id,
            'variant_id' => $variant->id,
            'product_name' => $product->name,
            'variant_title' => $variant->title,
            'sku' => $variant->sku,
            'quantity' => 2,
            'unit_price' => 9.00,
            'discount_amount' => 0.00,
            'line_total' => 18.00,
        ]);

        Coupon::create([
            'tenant_id' => $this->tenant->id,
            'code' => 'TESTCODE',
            'type' => 'percentage',
            'value' => 15,
            'is_active' => true,
        ]);

        DeliveryCharge::create([
            'tenant_id' => $this->tenant->id,
            'title' => 'Standard Courier Delivery',
            'slug' => 'standard-courier-delivery',
            'charge_amount' => 5.00,
            'status' => true,
        ]);

        // 1. Orders index
        $ordersRes = $this->actingAs($this->user)->get($this->storeUrl('/store/orders'));
        $ordersRes->assertOk();
        $ordersRes->assertSee('ORD-VIEW-1');
        $ordersRes->assertSee('Emma Watson');

        // 2. Orders create form
        $createRes = $this->actingAs($this->user)->get($this->storeUrl('/store/orders/create'));
        $createRes->assertOk();
        $createRes->assertSee('Create New Order');
        $createRes->assertSee('Order Items');

        // 3. Orders show
        $showRes = $this->actingAs($this->user)->get($this->storeUrl('/store/orders/' . $order->id));
        $showRes->assertOk();
        $showRes->assertSee('ORD-VIEW-1');
        $showRes->assertSee('Pain Relief Gel');
        $showRes->assertSee('221B Baker St');
        $showRes->assertSee('Fulfillment Progress');

        // 4. Coupons index
        $couponRes = $this->actingAs($this->user)->get($this->storeUrl('/store/promotions/coupons'));
        $couponRes->assertOk();
        $couponRes->assertSee('TESTCODE');
        $couponRes->assertSee('15% OFF');

        // 5. Delivery Charges index
        $deliveryRes = $this->actingAs($this->user)->get($this->storeUrl('/store/promotions/delivery-charges'));
        $deliveryRes->assertOk();
        $deliveryRes->assertSee('Standard Courier Delivery');
        $deliveryRes->assertSee('$5.00');
    }

    private function storeUrl(string $path): string
    {
        return 'http://' . config('app.base_domain') . $path;
    }
}
