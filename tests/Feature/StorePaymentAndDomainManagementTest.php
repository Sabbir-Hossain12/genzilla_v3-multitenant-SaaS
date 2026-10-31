<?php

namespace Tests\Feature;

use App\Models\PlatformPlan;
use App\Models\Tenant;
use App\Models\TenantDomain;
use App\Models\TenantPaymentMethod;
use App\Models\TenantSubscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class StorePaymentAndDomainManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Tenant $tenant;
    private PlatformPlan $plan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Store Merchant Owner',
            'email' => 'merchant_pay_domain@example.com',
            'password' => bcrypt('password'),
            'status' => 1,
        ]);

        $this->tenant = Tenant::create([
            'user_id' => $this->user->id,
            'store_name' => 'Lazz Pharma Express',
            'subdomain' => 'lazzpharma',
            'subscription_status' => 'active',
            'status' => 'active',
        ]);

        $this->plan = PlatformPlan::create([
            'name' => 'Pro Pharmacy Tier',
            'slug' => 'pro-tier',
            'price' => 49.00,
            'billing_interval' => 'monthly',
            'allow_custom_domain' => true,
            'status' => 'active',
        ]);

        TenantSubscription::create([
            'tenant_id' => $this->tenant->id,
            'plan_id' => $this->plan->id,
            'subscription_status' => 'active',
            'current_period_starts_at' => now(),
            'current_period_ends_at' => now()->addMonth(),
        ]);

        $registrar = app(PermissionRegistrar::class);
        $registrar->setPermissionsTeamId($this->tenant->id);

        $role = Role::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Store Owner',
            'guard_name' => 'web',
        ]);

        $permissions = [
            'Store Dashboard',
            'Store Payment Method List',
            'Store Payment Method Edit',
            'Store Domain List',
            'Store Domain Create',
            'Store Domain Edit',
            'Store Domain Delete',
        ];

        foreach ($permissions as $permName) {
            $perm = Permission::firstOrCreate(
                ['name' => $permName, 'guard_name' => 'web'],
                ['group_name' => 'Store Settings']
            );
            $role->givePermissionTo($perm);
        }

        $this->user->assignRole($role);
    }

    private function actingAsStoreMerchant(): self
    {
        app(PermissionRegistrar::class)->setPermissionsTeamId($this->tenant->id);

        return $this->actingAs($this->user)->withSession([
            'tenant_id' => $this->tenant->id,
        ]);
    }

    public function test_payment_methods_index_screen_renders_and_auto_initializes(): void
    {
        $response = $this->actingAsStoreMerchant()
            ->get(route('store.admin.payments.index'));

        $response->assertStatus(200);
        $response->assertSee('Payment Methods');
        $response->assertSee('Cash on Delivery (COD)');
        $response->assertSee('bKash (Send Money)');
        $response->assertSee('SSLCommerz');

        // Check database initialized records for this tenant
        $this->assertDatabaseHas('tenant_payment_methods', [
            'tenant_id' => $this->tenant->id,
            'provider' => 'cod',
            'is_active' => 1,
        ]);

        $this->assertDatabaseHas('tenant_payment_methods', [
            'tenant_id' => $this->tenant->id,
            'provider' => 'bkash_manual',
        ]);
    }

    public function test_merchant_can_configure_manual_mfs_credentials_and_instructions(): void
    {
        $this->actingAsStoreMerchant()
            ->get(route('store.admin.payments.index'));

        $bkash = TenantPaymentMethod::where('tenant_id', $this->tenant->id)
            ->where('provider', 'bkash_manual')
            ->firstOrFail();

        $response = $this->actingAsStoreMerchant()
            ->put(route('store.admin.payments.update', $bkash), [
                'title' => 'bKash Merchant Payment',
                'instructions' => 'Make payment to merchant number 01700000000 and enter counter 1.',
                'sort_order' => 2,
                'is_active' => '1',
                'credentials' => [
                    'wallet_number' => '01700000000',
                    'account_type' => 'merchant',
                ],
            ]);

        $response->assertRedirect(route('store.admin.payments.index'));
        $response->assertSessionHas('success');

        $bkash->refresh();
        $this->assertEquals('bKash Merchant Payment', $bkash->title);
        $this->assertTrue($bkash->is_active);
        $this->assertEquals('01700000000', $bkash->credentials['wallet_number']);
        $this->assertEquals('merchant', $bkash->credentials['account_type']);
    }

    public function test_merchant_can_configure_automated_gateway_with_encrypted_credentials(): void
    {
        $ssl = TenantPaymentMethod::create([
            'tenant_id' => $this->tenant->id,
            'provider' => 'sslcommerz',
            'type' => 'automated',
            'title' => 'SSLCommerz Hosted Gateway',
            'is_sandbox' => true,
            'is_active' => false,
            'credentials' => [],
        ]);

        $response = $this->actingAsStoreMerchant()
            ->put(route('store.admin.payments.update', $ssl), [
                'title' => 'Pay via Cards / Mobile Banking (SSLCommerz)',
                'is_sandbox' => '0',
                'is_active' => '1',
                'credentials' => [
                    'store_id' => 'lazzpharma_live_01',
                    'store_passwd' => 'SuperSecretLivePassword123',
                ],
            ]);

        $response->assertRedirect(route('store.admin.payments.index'));

        $ssl->refresh();
        $this->assertFalse($ssl->is_sandbox);
        $this->assertTrue($ssl->is_active);
        // Verify decrypted access via Eloquent casting
        $this->assertEquals('lazzpharma_live_01', $ssl->credentials['store_id']);
        $this->assertEquals('SuperSecretLivePassword123', $ssl->credentials['store_passwd']);

        // Verify masked display in UI helper
        $masked = $ssl->masked_credentials;
        $this->assertNotEquals('SuperSecretLivePassword123', $masked['store_passwd']);
        $this->assertStringContainsString('•', $masked['store_passwd']);
    }

    public function test_payment_method_status_can_be_toggled(): void
    {
        $method = TenantPaymentMethod::create([
            'tenant_id' => $this->tenant->id,
            'provider' => 'stripe',
            'type' => 'automated',
            'title' => 'Stripe Checkout',
            'is_active' => false,
        ]);

        $response = $this->actingAsStoreMerchant()
            ->patch(route('store.admin.payments.toggle', $method));

        $response->assertRedirect(route('store.admin.payments.index'));
        $this->assertTrue($method->fresh()->is_active);

        // Toggle back off
        $this->actingAsStoreMerchant()
            ->patch(route('store.admin.payments.toggle', $method));
        $this->assertFalse($method->fresh()->is_active);
    }

    public function test_domains_index_screen_renders_with_default_subdomain(): void
    {
        $response = $this->actingAsStoreMerchant()
            ->get(route('store.admin.domains.index'));

        $response->assertStatus(200);
        $response->assertSee('Custom Domains');

        $baseDomain = config('app.base_domain', 'genzilla.com');
        $expectedSubdomain = "{$this->tenant->subdomain}.{$baseDomain}";

        $response->assertSee($expectedSubdomain);
        $response->assertSee('System Active');

        $this->assertDatabaseHas('tenant_domains', [
            'tenant_id' => $this->tenant->id,
            'domain' => $expectedSubdomain,
            'is_custom' => 0,
            'is_primary' => 1,
        ]);
    }

    public function test_custom_domain_addition_is_gatekept_by_subscription_plan(): void
    {
        // Change plan to basic without custom domain support
        $this->plan->update(['allow_custom_domain' => false]);

        $response = $this->actingAsStoreMerchant()
            ->post(route('store.admin.domains.store'), [
                'domain' => 'mycustompharma.com',
            ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('tenant_domains', [
            'tenant_id' => $this->tenant->id,
            'domain' => 'mycustompharma.com',
        ]);
    }

    public function test_custom_domain_can_be_added_with_pending_dns_status(): void
    {
        $this->plan->update(['allow_custom_domain' => true]);

        $response = $this->actingAsStoreMerchant()
            ->post(route('store.admin.domains.store'), [
                'domain' => 'https://www.lazzpharmaonline.com/',
            ]);

        $response->assertRedirect(route('store.admin.domains.index'));
        $response->assertSessionHas('success');

        // Normalized domain string saved
        $this->assertDatabaseHas('tenant_domains', [
            'tenant_id' => $this->tenant->id,
            'domain' => 'www.lazzpharmaonline.com',
            'is_custom' => 1,
            'status' => 'pending_dns',
            'dns_verified' => 0,
        ]);
    }

    public function test_custom_domain_validation_rejects_invalid_and_platform_domains(): void
    {
        $response = $this->actingAsStoreMerchant()
            ->post(route('store.admin.domains.store'), [
                'domain' => 'invalid-domain-without-tld',
            ]);

        $response->assertSessionHasErrors('domain');

        $reservedResponse = $this->actingAsStoreMerchant()
            ->post(route('store.admin.domains.store'), [
                'domain' => 'shops.genzilla.com',
            ]);

        $reservedResponse->assertSessionHasErrors('domain');
    }

    public function test_dns_verification_updates_domain_to_active(): void
    {
        $domain = TenantDomain::create([
            'tenant_id' => $this->tenant->id,
            'domain' => 'store.lazzpharma.com',
            'is_custom' => true,
            'is_primary' => false,
            'status' => 'pending_dns',
            'dns_verified' => false,
            'ssl_status' => 'pending',
        ]);

        $response = $this->actingAsStoreMerchant()
            ->post(route('store.admin.domains.verify', $domain));

        $response->assertRedirect(route('store.admin.domains.index'));
        $response->assertSessionHas('success');

        $domain->refresh();
        $this->assertTrue($domain->dns_verified);
        $this->assertEquals('active', $domain->status);
        $this->assertEquals('active', $domain->ssl_status);
    }

    public function test_primary_domain_selection_updates_routing_and_tenant_model(): void
    {
        $baseDomain = config('app.base_domain', 'genzilla.com');
        $systemDomain = TenantDomain::create([
            'tenant_id' => $this->tenant->id,
            'domain' => "lazzpharma.{$baseDomain}",
            'is_custom' => false,
            'is_primary' => true,
            'dns_verified' => true,
            'status' => 'active',
        ]);

        $customDomain = TenantDomain::create([
            'tenant_id' => $this->tenant->id,
            'domain' => 'lazzpharma.com',
            'is_custom' => true,
            'is_primary' => false,
            'dns_verified' => true,
            'status' => 'active',
        ]);

        $response = $this->actingAsStoreMerchant()
            ->patch(route('store.admin.domains.primary', $customDomain));

        $response->assertRedirect(route('store.admin.domains.index'));

        $this->assertTrue($customDomain->fresh()->is_primary);
        $this->assertFalse($systemDomain->fresh()->is_primary);
        $this->assertEquals('lazzpharma.com', $this->tenant->fresh()->custom_domain);

        // Revert back to system domain
        $this->actingAsStoreMerchant()
            ->patch(route('store.admin.domains.primary', $systemDomain));

        $this->assertTrue($systemDomain->fresh()->is_primary);
        $this->assertFalse($customDomain->fresh()->is_primary);
        $this->assertNull($this->tenant->fresh()->custom_domain);
    }

    public function test_custom_domain_deletion_falls_back_to_system_subdomain(): void
    {
        $baseDomain = config('app.base_domain', 'genzilla.com');
        $systemDomain = TenantDomain::create([
            'tenant_id' => $this->tenant->id,
            'domain' => "lazzpharma.{$baseDomain}",
            'is_custom' => false,
            'is_primary' => false,
            'dns_verified' => true,
        ]);

        $customDomain = TenantDomain::create([
            'tenant_id' => $this->tenant->id,
            'domain' => 'temp-pharmacy.com',
            'is_custom' => true,
            'is_primary' => true,
            'dns_verified' => true,
        ]);
        $this->tenant->update(['custom_domain' => 'temp-pharmacy.com']);

        $response = $this->actingAsStoreMerchant()
            ->delete(route('store.admin.domains.destroy', $customDomain));

        $response->assertRedirect(route('store.admin.domains.index'));
        $this->assertDatabaseMissing('tenant_domains', ['id' => $customDomain->id]);

        // Fallback primary
        $this->assertTrue($systemDomain->fresh()->is_primary);
        $this->assertNull($this->tenant->fresh()->custom_domain);
    }

    public function test_multitenant_isolation_protects_payments_and_domains(): void
    {
        $otherUser = User::create([
            'name' => 'Other Merchant',
            'email' => 'other_owner@example.com',
            'password' => bcrypt('password'),
            'status' => 1,
        ]);

        $otherTenant = Tenant::create([
            'user_id' => $otherUser->id,
            'store_name' => 'Competitor Pharmacy',
            'subdomain' => 'competitor',
            'subscription_status' => 'active',
            'status' => 'active',
        ]);

        $otherMethod = TenantPaymentMethod::create([
            'tenant_id' => $otherTenant->id,
            'provider' => 'sslcommerz',
            'type' => 'automated',
            'title' => 'Competitor SSL',
            'credentials' => ['store_id' => 'secret_id'],
        ]);

        $otherDomain = TenantDomain::create([
            'tenant_id' => $otherTenant->id,
            'domain' => 'competitor-pharmacy.com',
            'is_custom' => true,
            'status' => 'pending_dns',
        ]);

        // Attempting to update or toggle competitor's payment method
        $this->actingAsStoreMerchant()
            ->put(route('store.admin.payments.update', $otherMethod), [
                'title' => 'Hacked Method',
            ])
            ->assertStatus(404);

        $this->actingAsStoreMerchant()
            ->patch(route('store.admin.payments.toggle', $otherMethod))
            ->assertStatus(404);

        // Attempting to verify, make primary, or delete competitor's domain
        $this->actingAsStoreMerchant()
            ->post(route('store.admin.domains.verify', $otherDomain))
            ->assertStatus(404);

        $this->actingAsStoreMerchant()
            ->patch(route('store.admin.domains.primary', $otherDomain))
            ->assertStatus(404);

        $this->actingAsStoreMerchant()
            ->delete(route('store.admin.domains.destroy', $otherDomain))
            ->assertStatus(404);
    }
}
