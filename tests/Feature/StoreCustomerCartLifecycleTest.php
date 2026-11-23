<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Customer;
use App\Models\PlatformPlan;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Tenant;
use App\Models\TenantSubscription;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class StoreCustomerCartLifecycleTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;
    private Tenant $tenant;
    private Customer $customer;
    private Product $product1;
    private Product $product2;
    private Product $product3;
    private Product $product4;
    private ProductVariant $variant1;
    private ProductVariant $variant2;
    private ProductVariant $variant3;
    private ProductVariant $variant4;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->seed(PermissionSeeder::class);

        // 1. Create merchant & tenant with localhost custom_domain for test routing
        $this->adminUser = User::create([
            'name' => 'Care Pharmacist',
            'email' => 'pharmacy@genzilla.test',
            'password' => bcrypt('password'),
            'status' => 1,
        ]);

        $this->tenant = Tenant::create([
            'user_id' => $this->adminUser->id,
            'store_name' => 'Care Pharmacy',
            'subdomain' => 'care',
            'custom_domain' => 'localhost',
            'subscription_status' => 'active',
            'status' => 'active',
        ]);

        $plan = PlatformPlan::create([
            'name' => 'Pro Pharmacy Tier',
            'slug' => 'pro-tier',
            'price' => 49.00,
            'billing_interval' => 'monthly',
            'allow_custom_domain' => true,
            'status' => 'active',
        ]);

        TenantSubscription::create([
            'tenant_id' => $this->tenant->id,
            'plan_id' => $plan->id,
            'subscription_status' => 'active',
            'current_period_starts_at' => now(),
            'current_period_ends_at' => now()->addMonth(),
        ]);

        // 2. Create customer
        $this->customer = Customer::create([
            'tenant_id' => $this->tenant->id,
            'first_name' => 'Rahim',
            'last_name' => 'Uddin',
            'phone' => '01711223344',
            'email' => 'rahim@test.com',
            'password' => 'password123',
            'status' => 'active',
        ]);

        // 3. Create products & variants
        $this->product1 = Product::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Napa Extra 500mg',
            'slug' => 'napa-extra-500mg',
            'status' => 'active',
        ]);
        $this->variant1 = ProductVariant::create([
            'tenant_id' => $this->tenant->id,
            'product_id' => $this->product1->id,
            'title' => 'Default',
            'sku' => 'NAPA-EXT',
            'price' => 35,
            'options_hash' => md5('default-1'),
            'is_default' => true,
            'status' => 'active',
        ]);

        $this->product2 = Product::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Seclo 20mg Capsule',
            'slug' => 'seclo-20mg',
            'status' => 'active',
            'requires_prescription' => true,
        ]);
        $this->variant2 = ProductVariant::create([
            'tenant_id' => $this->tenant->id,
            'product_id' => $this->product2->id,
            'title' => 'Strip of 10',
            'sku' => 'SECLO-20',
            'price' => 70,
            'options_hash' => md5('default-2'),
            'is_default' => true,
            'status' => 'active',
        ]);

        $this->product3 = Product::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Vitamin C 500mg Chewable',
            'slug' => 'vitamin-c-500mg',
            'status' => 'active',
        ]);
        $this->variant3 = ProductVariant::create([
            'tenant_id' => $this->tenant->id,
            'product_id' => $this->product3->id,
            'title' => 'Bottle of 30',
            'sku' => 'VIT-C-500',
            'price' => 25,
            'options_hash' => md5('default-3'),
            'is_default' => true,
            'status' => 'active',
        ]);

        $this->product4 = Product::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'First Aid Bandages 20s',
            'slug' => 'first-aid-bandages',
            'status' => 'active',
        ]);
        $this->variant4 = ProductVariant::create([
            'tenant_id' => $this->tenant->id,
            'product_id' => $this->product4->id,
            'title' => 'Pack of 20',
            'sku' => 'BANDAGE-20',
            'price' => 50,
            'options_hash' => md5('default-4'),
            'is_default' => true,
            'status' => 'active',
        ]);
    }

    private function getTenantHeaders(array $extra = []): array
    {
        return array_merge([
            'Accept' => 'application/json',
        ], $extra);
    }

    public function test_authenticated_customer_cart_is_saved_under_customer_id_with_null_session_token(): void
    {
        $this->actingAs($this->customer, 'customer');

        // Customer adds 3 items
        $response = $this->postJson(route('store.cart.sync'), [
            'items' => [
                ['product_id' => $this->product1->id, 'variant_id' => $this->variant1->id, 'quantity' => 2, 'price' => 35],
                ['product_id' => $this->product2->id, 'variant_id' => $this->variant2->id, 'quantity' => 1, 'price' => 70],
                ['product_id' => $this->product3->id, 'variant_id' => $this->variant3->id, 'quantity' => 3, 'price' => 25],
            ],
        ], $this->getTenantHeaders());

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'is_authenticated' => true,
            'count' => 6, // 2 + 1 + 3
        ]);

        // Verify in database: cart is strictly tied to customer_id, session_token is null
        $savedCart = Cart::where('tenant_id', $this->tenant->id)
            ->where('customer_id', $this->customer->id)
            ->first();

        $this->assertNotNull($savedCart);
        $this->assertNull($savedCart->session_token);
        $this->assertEquals(3, $savedCart->items()->count());

        // Customer fetches cart via GET /api/cart
        $getResponse = $this->getJson(route('store.cart.get'), $this->getTenantHeaders());
        $getResponse->assertOk();
        $getResponse->assertJson([
            'success' => true,
            'is_authenticated' => true,
            'count' => 6,
        ]);
        $this->assertCount(3, $getResponse->json('items'));
    }

    public function test_customer_cart_remains_safely_saved_in_database_after_logout(): void
    {
        // 1. Setup authenticated customer with 3 items saved
        $customerCart = Cart::create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $this->customer->id,
            'session_token' => null,
        ]);
        CartItem::create(['cart_id' => $customerCart->id, 'product_id' => $this->product1->id, 'product_variant_id' => $this->variant1->id, 'quantity' => 2]);
        CartItem::create(['cart_id' => $customerCart->id, 'product_id' => $this->product2->id, 'product_variant_id' => $this->variant2->id, 'quantity' => 1]);
        CartItem::create(['cart_id' => $customerCart->id, 'product_id' => $this->product3->id, 'product_variant_id' => $this->variant3->id, 'quantity' => 3]);

        $this->actingAs($this->customer, 'customer');

        // 2. Perform logout
        $logoutResponse = $this->postJson(route('store.customer.logout'), [], $this->getTenantHeaders());
        $logoutResponse->assertOk();
        $logoutResponse->assertJsonStructure([
            'success',
            'fresh_session_token',
        ]);

        $freshToken = $logoutResponse->json('fresh_session_token');
        $this->assertNotEmpty($freshToken);

        // 3. Database State: Customer's 3 items remain safely in the DB under customer_id
        $dbCart = Cart::where('tenant_id', $this->tenant->id)
            ->where('customer_id', $this->customer->id)
            ->first();

        $this->assertNotNull($dbCart);
        $this->assertNull($dbCart->session_token);
        $this->assertEquals(3, $dbCart->items()->count());
        $this->assertEquals(6, $dbCart->total_items_count);

        // 4. Guest Mode: Guest requests cart with fresh session token -> returns 0 items
        $guestCartResponse = $this->getJson(route('store.cart.get'), $this->getTenantHeaders([
            'X-Cart-Session' => $freshToken,
        ]));

        $guestCartResponse->assertOk();
        $guestCartResponse->assertJson([
            'success' => true,
            'is_authenticated' => false,
            'count' => 0,
            'items' => [],
        ]);
    }

    public function test_guest_actions_cannot_mutate_or_clear_authenticated_customer_saved_cart(): void
    {
        // 1. Setup authenticated customer's saved cart (3 items)
        $customerCart = Cart::create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $this->customer->id,
            'session_token' => null,
        ]);
        CartItem::create(['cart_id' => $customerCart->id, 'product_id' => $this->product1->id, 'product_variant_id' => $this->variant1->id, 'quantity' => 2]);
        CartItem::create(['cart_id' => $customerCart->id, 'product_id' => $this->product2->id, 'product_variant_id' => $this->variant2->id, 'quantity' => 1]);
        CartItem::create(['cart_id' => $customerCart->id, 'product_id' => $this->product3->id, 'product_variant_id' => $this->variant3->id, 'quantity' => 3]);

        $guestToken = 'guest_' . Str::random(32);

        // 2. Guest adds a new product (Product 4) to their guest cart
        $addResponse = $this->postJson(route('store.cart.sync'), [
            'session_token' => $guestToken,
            'items' => [
                ['product_id' => $this->product4->id, 'variant_id' => $this->variant4->id, 'quantity' => 1, 'price' => 50],
            ],
        ], $this->getTenantHeaders(['X-Cart-Session' => $guestToken]));

        $addResponse->assertOk();
        $addResponse->assertJson([
            'success' => true,
            'is_authenticated' => false,
            'count' => 1,
        ]);

        // 3. Verify Customer Cart is untouched
        $customerCart->refresh();
        $this->assertEquals(3, $customerCart->items()->count());
        $this->assertEquals(6, $customerCart->total_items_count);

        // 4. Guest calls clear cart
        $clearResponse = $this->postJson(route('store.cart.clear'), [
            'session_token' => $guestToken,
        ], $this->getTenantHeaders(['X-Cart-Session' => $guestToken]));

        $clearResponse->assertOk();

        // 5. Verify customer's cart still has all 3 items intact!
        $customerCart->refresh();
        $this->assertEquals(3, $customerCart->items()->count());
        $this->assertEquals(6, $customerCart->total_items_count);
    }

    public function test_logging_in_merges_guest_cart_into_authenticated_cart_and_deletes_guest_cart(): void
    {
        // 1. Customer already has 3 saved items in DB: Napa (qty 2), Seclo (qty 1), Vitamin C (qty 3)
        $customerCart = Cart::create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $this->customer->id,
            'session_token' => null,
        ]);
        CartItem::create(['cart_id' => $customerCart->id, 'product_id' => $this->product1->id, 'product_variant_id' => $this->variant1->id, 'quantity' => 2]);
        CartItem::create(['cart_id' => $customerCart->id, 'product_id' => $this->product2->id, 'product_variant_id' => $this->variant2->id, 'quantity' => 1]);
        CartItem::create(['cart_id' => $customerCart->id, 'product_id' => $this->product3->id, 'product_variant_id' => $this->variant3->id, 'quantity' => 3]);

        // 2. Unauthenticated guest adds:
        //    - 1 new item: Product 4 (Bandages, qty 1)
        //    - 1 overlapping item: Product 1 (Napa, qty 1)
        $guestToken = 'guest_' . Str::random(32);
        $guestCart = Cart::create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => null,
            'session_token' => $guestToken,
        ]);
        CartItem::create(['cart_id' => $guestCart->id, 'product_id' => $this->product4->id, 'product_variant_id' => $this->variant4->id, 'quantity' => 1]);
        CartItem::create(['cart_id' => $guestCart->id, 'product_id' => $this->product1->id, 'product_variant_id' => $this->variant1->id, 'quantity' => 1]);

        // 3. Guest logs into customer account
        $loginResponse = $this->postJson(route('store.customer.login'), [
            'phone' => $this->customer->phone,
            'password' => 'password123',
            'session_token' => $guestToken,
        ], $this->getTenantHeaders(['X-Cart-Session' => $guestToken]));

        $loginResponse->assertOk();

        // 4. Verify temporary guest cart was deleted
        $this->assertDatabaseMissing('carts', [
            'id' => $guestCart->id,
        ]);

        // 5. Verify customer's cart now has the merged items:
        // - 4 distinct items: Product 1, Product 2, Product 3, Product 4
        // - Product 1 quantity summed: 2 (saved) + 1 (guest) = 3
        // - Product 4 added: 1
        // - Total items count = 3 (P1) + 1 (P2) + 3 (P3) + 1 (P4) = 8
        $customerCart->refresh();
        $this->assertEquals(4, $customerCart->items()->count());
        $this->assertEquals(8, $customerCart->total_items_count);
        $this->assertNull($customerCart->session_token);

        $p1Item = $customerCart->items()->where('product_variant_id', $this->variant1->id)->first();
        $this->assertEquals(3, $p1Item->quantity);

        $p4Item = $customerCart->items()->where('product_variant_id', $this->variant4->id)->first();
        $this->assertEquals(1, $p4Item->quantity);
    }

    public function test_guest_cannot_access_customer_cart_by_passing_matching_token(): void
    {
        // Customer cart has customer_id = customer->id and session_token = null
        Cart::create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $this->customer->id,
            'session_token' => null,
        ]);

        // Guest calls /api/cart with some token
        $guestToken = 'guest_' . Str::random(32);
        $response = $this->getJson(route('store.cart.get'), $this->getTenantHeaders([
            'X-Cart-Session' => $guestToken,
        ]));

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'is_authenticated' => false,
            'count' => 0,
            'items' => [],
        ]);

        // Verify the cart resolved is strictly a guest cart with customer_id = null
        $resolvedCart = Cart::find($response->json('cart_id'));
        $this->assertNotNull($resolvedCart);
        $this->assertNull($resolvedCart->customer_id);
        $this->assertEquals($guestToken, $resolvedCart->session_token);
    }
}
