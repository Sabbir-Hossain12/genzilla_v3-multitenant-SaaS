<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Customer;
use App\Models\DeliveryCharge;
use App\Models\InventoryBatch;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\OrderPayment;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\ProductVariant;
use App\Models\Tenant;
use App\Models\TenantPrescription;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class StoreAdminFullAuditTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->seed(PermissionSeeder::class);

        // 1. Create main user & tenant
        $this->user = User::create([
            'name' => 'Auditor Admin',
            'email' => 'auditor@example.com',
            'password' => Hash::make('password123'),
            'status' => 1,
        ]);

        $this->tenant = Tenant::create([
            'user_id' => $this->user->id,
            'store_name' => 'Care Pharmacy Audit',
            'subdomain' => 'careaudit',
            'subscription_status' => 'active',
            'status' => 'active',
        ]);

        // 2. Grant all store permissions to Store Owner role
        $registrar = app(PermissionRegistrar::class);
        $registrar->setPermissionsTeamId($this->tenant->id);

        $role = Role::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Store Owner',
            'guard_name' => 'web',
        ]);

        $allPermissions = \Spatie\Permission\Models\Permission::where('name', 'like', 'Store %')->get();
        $role->syncPermissions($allPermissions);

        $this->user->assignRole($role);
    }

    private function actingAsTenant(): self
    {
        session(['tenant_id' => $this->tenant->id]);
        return $this->actingAs($this->user);
    }

    public function test_all_sidebar_primary_routes_are_accessible(): void
    {
        $routes = [
            'store.admin.dashboard',
            'store.admin.reports.index',
            'store.admin.prescriptions.index',
            'store.admin.orders.index',
            'store.admin.orders.create',
            'store.admin.abandoned_checkouts.index',
            'store.admin.transactions.index',
            'store.admin.products.index',
            'store.admin.products.create',
            'store.admin.inventory.index',
            'store.admin.categories.index',
            'store.admin.brands.index',
            'store.admin.attributes.index',
            'store.admin.reviews.index',
            'store.admin.customers.index',
            'store.admin.coupons.index',
            'store.admin.delivery_charges.index',
            'store.admin.sliders.index',
            'store.admin.banners.index',
            'store.admin.pages.index',
            'store.admin.admins.index',
            'store.admin.role.index',
            'store.admin.permission.index',
            'store.admin.payments.index',
            'store.admin.domains.index',
            'store.admin.subscription.index',
            'store.admin.settings.index',
            'store.admin.profile.edit',
        ];

        foreach ($routes as $routeName) {
            $response = $this->actingAsTenant()->get(route($routeName));
            $this->assertEquals(200, $response->status(), "Route [{$routeName}] failed to load with status {$response->status()}");
        }
    }

    public function test_dashboard_kpis_and_feeds_sync_with_all_store_features(): void
    {
        // 1. Create a customer
        $customer = Customer::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Karim Rahman',
            'email' => 'karim@test.com',
            'phone' => '01711122233',
        ]);

        // 2. Create an order with items & payments
        $order = Order::create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $customer->id,
            'order_number' => 'ORD-AUDIT-001',
            'subtotal' => 1200.00,
            'discount_amount' => 100.00,
            'shipping_charge' => 60.00,
            'total' => 1160.00,
            'status' => 'processing',
            'payment_status' => 'paid',
            'payment_method' => 'bkash',
            'shipping_name' => 'Karim Rahman',
            'shipping_phone' => '01711122233',
            'shipping_address' => 'House 1, Road 2, Gulshan',
            'shipping_city' => 'Dhaka',
        ]);

        OrderPayment::create([
            'tenant_id' => $this->tenant->id,
            'order_id' => $order->id,
            'amount' => 1160.00,
            'payment_method' => 'bkash',
            'transaction_id' => 'TRX-AUDIT-999',
            'status' => 'successful',
        ]);

        // 3. Create a prescription pending review
        $rx = TenantPrescription::create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $customer->id,
            'prescription_number' => 'RX-AUDIT-555',
            'status' => 'pending',
            'image_path' => 'prescriptions/test.jpg',
            'notes' => 'Patient needs asthma inhaler urgently',
        ]);

        // 4. Create an abandoned cart
        $cart = Cart::create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $customer->id,
            'updated_at' => now()->subHours(3),
        ]);

        $category = Category::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'General Medicine',
            'slug' => 'general-medicine',
        ]);

        $product = Product::create([
            'tenant_id' => $this->tenant->id,
            'category_id' => $category->id,
            'name' => 'Napa Extend 665mg',
            'slug' => 'napa-extend-665mg',
            'status' => 'active',
        ]);

        $variant = ProductVariant::create([
            'tenant_id' => $this->tenant->id,
            'product_id' => $product->id,
            'sku' => 'NAPA-EXT-01',
            'price' => 25.00,
            'track_inventory' => true,
            'status' => 'active',
        ]);

        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'quantity' => 4,
        ]);

        // 5. Create a low stock batch (quantity = 5)
        InventoryBatch::create([
            'tenant_id' => $this->tenant->id,
            'variant_id' => $variant->id,
            'batch_number' => 'BATCH-LOW-1',
            'quantity' => 5,
            'reserved' => 0,
            'status' => 'active',
        ]);

        // 6. Create a pending product review
        $review = ProductReview::create([
            'tenant_id' => $this->tenant->id,
            'product_id' => $product->id,
            'customer_id' => $customer->id,
            'rating' => 5,
            'comment' => 'Very effective for back pain, fast delivery.',
            'status' => 'pending',
        ]);

        // Now hit the Dashboard and verify complete synchronization!
        $response = $this->actingAsTenant()->get(route('store.admin.dashboard'));

        $response->assertOk();
        // Check Revenue & Order KPIs
        $response->assertSee('1,160.00');
        $response->assertSee('ORD-AUDIT-001');
        $response->assertSee('Karim Rahman');

        // Check Prescription Inbox Queue
        $response->assertSee('RX-AUDIT-555');

        // Check Abandoned Cart KPI
        $response->assertSee('Recoverable Carts');

        // Check Low Stock Watchlist
        $response->assertSee('NAPA-EXT-01');
        $response->assertSee('5 units');

        // Check Review Moderation Feed
        $response->assertSee('Very effective for back pain');
    }
}
