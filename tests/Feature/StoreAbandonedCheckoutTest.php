<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class StoreAbandonedCheckoutTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Tenant $tenant;
    private User $otherUser;
    private Tenant $otherTenant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->seed(PermissionSeeder::class);

        // 1. Create main user & tenant
        $this->user = User::create([
            'name' => 'Store Support Admin',
            'email' => 'support@example.com',
            'password' => bcrypt('password'),
            'status' => 1,
        ]);

        $this->tenant = Tenant::create([
            'user_id' => $this->user->id,
            'store_name' => 'Care Pharmacy',
            'subdomain' => 'carepharmacy',
            'subscription_status' => 'active',
            'status' => 'active',
        ]);

        // 2. Setup Spatie Roles & Permissions for main tenant
        $registrar = app(PermissionRegistrar::class);
        $registrar->setPermissionsTeamId($this->tenant->id);

        $role = Role::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Store Owner',
            'guard_name' => 'web',
        ]);

        $permissions = [
            'Store Dashboard',
            'Store Abandoned Checkout List',
            'Store Abandoned Checkout View',
            'Store Abandoned Checkout Contact',
            'Store Abandoned Checkout Delete',
        ];

        foreach ($permissions as $p) {
            $role->givePermissionTo($p);
        }

        $this->user->assignRole($role);

        // 3. Create second tenant for multi-tenant isolation tests
        $this->otherUser = User::create([
            'name' => 'Other Merchant',
            'email' => 'other@example.com',
            'password' => bcrypt('password'),
            'status' => 1,
        ]);

        $this->otherTenant = Tenant::create([
            'user_id' => $this->otherUser->id,
            'store_name' => 'Other Meds',
            'subdomain' => 'othermeds',
            'subscription_status' => 'active',
            'status' => 'active',
        ]);

        $otherRole = Role::create([
            'tenant_id' => $this->otherTenant->id,
            'name' => 'Store Owner',
            'guard_name' => 'web',
        ]);

        $otherRole->syncPermissions($permissions);
        $this->otherUser->assignRole($otherRole);
    }

    private function createProductWithVariant(Tenant $tenant, string $name = 'Napa Extra 500mg', float $price = 35.00, bool $requiresRx = false): array
    {
        $product = Product::create([
            'tenant_id' => $tenant->id,
            'name' => $name,
            'slug' => Str::slug($name) . '-' . Str::random(5),
            'status' => 'active',
            'requires_prescription' => $requiresRx,
        ]);

        $variant = ProductVariant::create([
            'tenant_id' => $tenant->id,
            'product_id' => $product->id,
            'title' => 'Default',
            'sku' => 'SKU-' . strtoupper(Str::random(6)),
            'price' => $price,
            'options_hash' => md5('default'),
            'status' => 'active',
        ]);

        return [$product, $variant];
    }

    private function createCustomer(Tenant $tenant, string $name = 'Tanvir Ahmed', string $phone = '01711223344'): Customer
    {
        return Customer::create([
            'tenant_id' => $tenant->id,
            'first_name' => explode(' ', $name)[0],
            'last_name' => explode(' ', $name)[1] ?? 'Customer',
            'email' => Str::slug($name) . '@example.com',
            'phone' => $phone,
            'status' => 'active',
        ]);
    }

    public function test_merchant_can_view_abandoned_checkouts_index_with_kpis()
    {
        [$product, $variant] = $this->createProductWithVariant($this->tenant, 'Napa Extend 665mg', 50.00);
        $customer = $this->createCustomer($this->tenant, 'Tanvir Ahmed', '01711223344');

        $cart = Cart::create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $customer->id,
            'recovery_contacted_at' => null,
            'updated_at' => now()->subHours(3),
            'created_at' => now()->subHours(4),
        ]);

        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'quantity' => 2,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('store.admin.abandoned_checkouts.index'));

        $response->assertStatus(200);
        $response->assertSee('Abandoned Checkouts');
        $response->assertSee('Recoverable Carts');
        $response->assertSee('Tanvir Ahmed');
        $response->assertSee('01711223344');
        $response->assertSee('৳100.00'); // 2 * 50
    }

    public function test_merchant_can_filter_recoverable_carts_by_contact_status()
    {
        [$product, $variant] = $this->createProductWithVariant($this->tenant);
        $customer1 = $this->createCustomer($this->tenant, 'Pending Customer');
        $customer2 = $this->createCustomer($this->tenant, 'Contacted Customer');

        $cart1 = Cart::create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $customer1->id,
            'recovery_contacted_at' => null,
            'updated_at' => now()->subHours(3),
        ]);
        CartItem::create([
            'cart_id' => $cart1->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        $cart2 = Cart::create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $customer2->id,
            'recovery_contacted_at' => now()->subHour(),
            'recovery_note' => 'Spoke to patient',
            'updated_at' => now()->subHours(3),
        ]);
        CartItem::create([
            'cart_id' => $cart2->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        // Filter uncontacted
        $resUncontacted = $this->actingAs($this->user)
            ->get(route('store.admin.abandoned_checkouts.index', ['contact_status' => 'uncontacted']));
        $resUncontacted->assertStatus(200);
        $resUncontacted->assertSee('Pending Customer');
        $resUncontacted->assertDontSee('Contacted Customer');

        // Filter contacted
        $resContacted = $this->actingAs($this->user)
            ->get(route('store.admin.abandoned_checkouts.index', ['contact_status' => 'contacted']));
        $resContacted->assertStatus(200);
        $resContacted->assertSee('Contacted Customer');
        $resContacted->assertDontSee('Pending Customer');
    }

    public function test_merchant_can_filter_by_prescription_required()
    {
        [$rxProd, $rxVar] = $this->createProductWithVariant($this->tenant, 'Antibiotic 500mg', 120.00, true);
        [$otcProd, $otcVar] = $this->createProductWithVariant($this->tenant, 'Vitamin C', 15.00, false);

        $custRx = $this->createCustomer($this->tenant, 'Rx Patient');
        $custOtc = $this->createCustomer($this->tenant, 'Otc Buyer');

        $cartRx = Cart::create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $custRx->id,
            'updated_at' => now()->subHours(3),
        ]);
        CartItem::create([
            'cart_id' => $cartRx->id,
            'product_id' => $rxProd->id,
            'product_variant_id' => $rxVar->id,
            'quantity' => 1,
        ]);

        $cartOtc = Cart::create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $custOtc->id,
            'updated_at' => now()->subHours(3),
        ]);
        CartItem::create([
            'cart_id' => $cartOtc->id,
            'product_id' => $otcProd->id,
            'product_variant_id' => $otcVar->id,
            'quantity' => 1,
        ]);

        $res = $this->actingAs($this->user)
            ->get(route('store.admin.abandoned_checkouts.index', ['rx_status' => 'required']));
        $res->assertStatus(200);
        $res->assertSee('Rx Patient');
        $res->assertDontSee('Otc Buyer');
    }

    public function test_merchant_can_view_cart_details_via_ajax()
    {
        [$product, $variant] = $this->createProductWithVariant($this->tenant, 'Ceevit 250mg', 20.00);
        $customer = $this->createCustomer($this->tenant, 'Farhana Yesmin', '01799887766');

        $cart = Cart::create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $customer->id,
            'updated_at' => now()->subHours(3),
        ]);

        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'quantity' => 3,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson(route('store.admin.abandoned_checkouts.show', $cart->id));

        $response->assertStatus(200);
        $response->assertJson([
            'id' => $cart->id,
            'customer' => [
                'name' => 'Farhana Yesmin',
                'phone' => '01799887766',
            ],
            'subtotal' => 60.00,
            'total_items_count' => 3,
            'items' => [
                [
                    'product_name' => 'Ceevit 250mg',
                    'quantity' => 3,
                    'price' => 20.00,
                    'line_total' => 60.00,
                ]
            ],
        ]);
    }

    public function test_merchant_can_mark_cart_as_contacted()
    {
        [$product, $variant] = $this->createProductWithVariant($this->tenant);
        $customer = $this->createCustomer($this->tenant, 'Sabbir Hossain');

        $cart = Cart::create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $customer->id,
            'recovery_contacted_at' => null,
            'updated_at' => now()->subHours(3),
        ]);

        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson(route('store.admin.abandoned_checkouts.contacted', $cart->id), [
                'recovery_note' => 'Called customer, confirmed prescription is being uploaded.',
            ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $cart->refresh();
        $this->assertNotNull($cart->recovery_contacted_at);
        $this->assertEquals('Called customer, confirmed prescription is being uploaded.', $cart->recovery_note);
    }

    public function test_merchant_can_update_recovery_note()
    {
        [$product, $variant] = $this->createProductWithVariant($this->tenant);
        $customer = $this->createCustomer($this->tenant, 'Mahmudul Hasan');

        $cart = Cart::create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $customer->id,
            'recovery_contacted_at' => null,
            'updated_at' => now()->subHours(4),
        ]);

        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        $response = $this->actingAs($this->user)
            ->patchJson(route('store.admin.abandoned_checkouts.note', $cart->id), [
                'recovery_note' => 'Customer asked for 10% discount coupon',
                'mark_contacted' => true,
            ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $cart->refresh();
        $this->assertEquals('Customer asked for 10% discount coupon', $cart->recovery_note);
        $this->assertNotNull($cart->recovery_contacted_at);
    }

    public function test_top_abandoned_products_tab_aggregates_across_registered_and_guests()
    {
        [$glucometer, $glucVar] = $this->createProductWithVariant($this->tenant, 'Accu-Chek Glucometer', 1500.00);

        // Cart 1: Registered Customer
        $customer = $this->createCustomer($this->tenant, 'Registered Buyer');
        $regCart = Cart::create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $customer->id,
            'updated_at' => now()->subHours(3),
        ]);
        CartItem::create([
            'cart_id' => $regCart->id,
            'product_id' => $glucometer->id,
            'product_variant_id' => $glucVar->id,
            'quantity' => 1,
        ]);

        // Cart 2: Guest Shopper
        $guestCart = Cart::create([
            'tenant_id' => $this->tenant->id,
            'session_token' => 'guest_session_test_xyz',
            'updated_at' => now()->subHours(5),
        ]);
        CartItem::create([
            'cart_id' => $guestCart->id,
            'product_id' => $glucometer->id,
            'product_variant_id' => $glucVar->id,
            'quantity' => 2,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('store.admin.abandoned_checkouts.index', ['tab' => 'products']));

        $response->assertStatus(200);
        $response->assertSee('Accu-Chek Glucometer');
        $response->assertSee('2 Shoppers'); // 2 distinct carts
        $response->assertSee('3'); // 1 + 2 total units
        $response->assertSee('4,500.00'); // 3 * 1500
    }

    public function test_multitenant_isolation_protects_abandoned_carts()
    {
        [$otherProduct, $otherVariant] = $this->createProductWithVariant($this->otherTenant, 'Other Tenant Med', 100.00);
        $otherCustomer = $this->createCustomer($this->otherTenant, 'Other Customer');

        $otherCart = Cart::create([
            'tenant_id' => $this->otherTenant->id,
            'customer_id' => $otherCustomer->id,
            'updated_at' => now()->subHours(3),
        ]);
        CartItem::create([
            'cart_id' => $otherCart->id,
            'product_id' => $otherProduct->id,
            'product_variant_id' => $otherVariant->id,
            'quantity' => 1,
        ]);

        // Main user cannot view other tenant's cart in index
        $resIndex = $this->actingAs($this->user)
            ->get(route('store.admin.abandoned_checkouts.index'));
        $resIndex->assertDontSee('Other Customer');
        $resIndex->assertDontSee('Other Tenant Med');

        // Main user cannot view other tenant's cart details (404)
        $resShow = $this->actingAs($this->user)
            ->get(route('store.admin.abandoned_checkouts.show', $otherCart->id));
        $resShow->assertStatus(404);

        // Main user cannot mark other tenant's cart as contacted (404)
        $resContact = $this->actingAs($this->user)
            ->post(route('store.admin.abandoned_checkouts.contacted', $otherCart->id), ['recovery_note' => 'Hacked']);
        $resContact->assertStatus(404);
    }

    public function test_merchant_can_delete_abandoned_cart()
    {
        [$product, $variant] = $this->createProductWithVariant($this->tenant);
        $customer = $this->createCustomer($this->tenant, 'Customer To Delete');

        $cart = Cart::create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $customer->id,
            'updated_at' => now()->subHours(3),
        ]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        $response = $this->actingAs($this->user)
            ->delete(route('store.admin.abandoned_checkouts.destroy', $cart->id));

        $response->assertRedirect();
        $this->assertDatabaseMissing('carts', ['id' => $cart->id]);
        $this->assertDatabaseMissing('cart_items', ['cart_id' => $cart->id]);
    }
}
