<?php

namespace Tests\Feature;

use App\Models\PlatformPlan;
use App\Models\Tenant;
use App\Models\TenantDomain;
use App\Models\TenantInvoice;
use App\Models\TenantSubscription;
use App\Models\TenantUsage;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class StoreSubscriptionAndBillingTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Tenant $tenant;
    private PlatformPlan $starterPlan;
    private PlatformPlan $growthPlan;
    private PlatformPlan $enterprisePlan;
    private TenantSubscription $subscription;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Seed plans
        $this->starterPlan = PlatformPlan::create([
            'name' => 'Starter Pharmacy Plan',
            'slug' => 'starter',
            'monthly_price' => 29.00,
            'yearly_price' => 290.00,
            'max_products' => 50,
            'max_staff_accounts' => 1,
            'max_storage_mb' => 100,
            'allow_custom_domain' => false,
            'has_advanced_reporting' => false,
            'has_custom_rbac' => false,
            'has_audit_logs' => false,
            'status' => 1,
        ]);

        $this->growthPlan = PlatformPlan::create([
            'name' => 'Pro Growth Plan',
            'slug' => 'growth',
            'monthly_price' => 79.00,
            'yearly_price' => 790.00,
            'max_products' => 200,
            'max_staff_accounts' => 5,
            'max_storage_mb' => 500,
            'allow_custom_domain' => true,
            'has_advanced_reporting' => true,
            'has_custom_rbac' => false,
            'has_audit_logs' => false,
            'status' => 1,
        ]);

        $this->enterprisePlan = PlatformPlan::create([
            'name' => 'Enterprise Pharmacy Plan',
            'slug' => 'enterprise',
            'monthly_price' => 199.00,
            'yearly_price' => 1990.00,
            'max_products' => 1000,
            'max_staff_accounts' => 20,
            'max_storage_mb' => 2000,
            'allow_custom_domain' => true,
            'has_advanced_reporting' => true,
            'has_custom_rbac' => true,
            'has_audit_logs' => true,
            'status' => 1,
        ]);

        // 2. Create User & Tenant
        $this->user = User::create([
            'name' => 'Dr. Ahmed Khan',
            'email' => 'ahmed@lazzpharma.com',
            'password' => bcrypt('password'),
            'status' => 1,
        ]);

        $this->tenant = Tenant::create([
            'user_id' => $this->user->id,
            'store_name' => 'Lazz Pharma Express',
            'subdomain' => 'lazzpharma',
            'support_email' => 'support@lazzpharma.com',
            'currency' => 'USD',
            'timezone' => 'UTC',
            'subscription_status' => 'active',
            'status' => 'active',
        ]);

        // 3. Domain
        TenantDomain::create([
            'tenant_id' => $this->tenant->id,
            'domain' => 'lazzpharma.genzilla.com',
            'is_custom' => false,
            'is_primary' => true,
            'dns_verified' => true,
            'ssl_status' => 'active',
            'status' => 'active',
        ]);

        // 4. Initial Active Subscription (Starter Plan)
        $this->subscription = TenantSubscription::create([
            'tenant_id' => $this->tenant->id,
            'plan_id' => $this->starterPlan->id,
            'billing_interval' => 'monthly',
            'current_period_starts_at' => now(),
            'current_period_ends_at' => now()->addMonth(),
            'subscription_status' => 'active',
        ]);

        $this->tenant->update([
            'current_subscription_id' => $this->subscription->id,
        ]);

        // 5. Initial Usage
        TenantUsage::create([
            'tenant_id' => $this->tenant->id,
            'storage_used_bytes' => 10 * 1024 * 1024, // 10 MB
            'staff_accounts_count' => 1,
            'products_count' => 15,
        ]);

        // 6. Initial Settled Invoice
        TenantInvoice::create([
            'tenant_id' => $this->tenant->id,
            'platform_subscription_id' => $this->subscription->id,
            'invoice_number' => 'INV-TEST1001',
            'billing_period_start' => now(),
            'billing_period_end' => now()->addMonth(),
            'subtotal' => 29.00,
            'tax' => 0.00,
            'total' => 29.00,
            'currency' => 'USD',
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        // 7. Seed permissions & role
        $registrar = app(PermissionRegistrar::class);
        $registrar->setPermissionsTeamId($this->tenant->id);

        $role = Role::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Store Owner',
            'guard_name' => 'web',
        ]);

        $permissions = [
            'Store Dashboard',
            'Store Subscription View',
            'Store Subscription Change Plan',
            'Store Subscription Cancel',
            'Store Domain List',
            'Store General Setting',
        ];

        foreach ($permissions as $permName) {
            $perm = Permission::firstOrCreate(
                ['name' => $permName, 'guard_name' => 'web'],
                ['group_name' => 'Store Subscription & Billing', 'panel' => 'store']
            );
            $role->givePermissionTo($perm);
        }

        $this->user->assignRole($role);
    }

    private function actingAsMerchant(): self
    {
        app(PermissionRegistrar::class)->setPermissionsTeamId($this->tenant->id);

        return $this->actingAs($this->user)->withSession([
            'tenant_id' => $this->tenant->id,
        ]);
    }

    public function test_subscription_index_page_renders_with_current_plan_and_quotas(): void
    {
        $response = $this->actingAsMerchant()->get(route('store.admin.subscription.index'));

        $response->assertOk();
        $response->assertSee('Store Subscription &amp; Billing', false);
        $response->assertSee('Starter Pharmacy Plan');
        $response->assertSee('Active');
        $response->assertSee('Storage Usage');
        $response->assertSeeText('Staff & Pharmacists', false);
        $response->assertSee('Product Catalog');
        $response->assertSee('lazzpharma.genzilla.com');
        $response->assertSee('Pro Growth Plan');
        $response->assertSee('Enterprise Pharmacy Plan');
        $response->assertSee('INV-TEST1001');
    }

    public function test_subscription_shows_integrated_custom_domain_summary(): void
    {
        // Add verified custom domain
        TenantDomain::create([
            'tenant_id' => $this->tenant->id,
            'domain' => 'lazzpharma.com',
            'is_custom' => true,
            'is_primary' => true,
            'dns_verified' => true,
            'ssl_status' => 'active',
            'status' => 'active',
        ]);

        // Reset system default to secondary
        TenantDomain::where('tenant_id', $this->tenant->id)
            ->where('is_custom', false)
            ->update(['is_primary' => false]);

        $response = $this->actingAsMerchant()->get(route('store.admin.subscription.index'));

        $response->assertOk();
        $response->assertSee('Active Custom Domain');
        $response->assertSee('lazzpharma.com');
        $response->assertSee('SSL Secure');
        $response->assertSee('DNS Verified');
    }

    public function test_merchant_can_upgrade_plan_to_growth(): void
    {
        $response = $this->actingAsMerchant()->post(route('store.admin.subscription.change_plan'), [
            'plan_id' => $this->growthPlan->id,
            'billing_interval' => 'monthly',
        ]);

        $response->assertRedirect(route('store.admin.subscription.index'));
        $response->assertSessionHas('success');

        $this->tenant->refresh();
        $activeSub = $this->tenant->activeSubscription;

        $this->assertNotNull($activeSub);
        $this->assertEquals($this->growthPlan->id, $activeSub->plan_id);
        $this->assertEquals('monthly', $activeSub->billing_interval);
        $this->assertEquals('active', $activeSub->subscription_status);
        $this->assertTrue($this->tenant->allowsCustomDomain());

        // An invoice for the upgrade is generated
        $this->assertDatabaseHas('tenant_invoices', [
            'tenant_id' => $this->tenant->id,
            'platform_subscription_id' => $activeSub->id,
            'subtotal' => 79.00,
            'total' => 79.00,
            'status' => 'paid',
        ]);
    }

    public function test_merchant_can_switch_billing_cycle_to_annual(): void
    {
        $response = $this->actingAsMerchant()->post(route('store.admin.subscription.change_plan'), [
            'plan_id' => $this->enterprisePlan->id,
            'billing_interval' => 'yearly',
        ]);

        $response->assertRedirect(route('store.admin.subscription.index'));

        $this->tenant->refresh();
        $activeSub = $this->tenant->activeSubscription;

        $this->assertEquals($this->enterprisePlan->id, $activeSub->plan_id);
        $this->assertEquals('yearly', $activeSub->billing_interval);

        // An annual invoice is generated
        $this->assertDatabaseHas('tenant_invoices', [
            'tenant_id' => $this->tenant->id,
            'platform_subscription_id' => $activeSub->id,
            'subtotal' => 1990.00,
            'total' => 1990.00,
            'status' => 'paid',
        ]);
    }

    public function test_merchant_can_cancel_and_resume_subscription(): void
    {
        // 1. Cancel
        $cancelResponse = $this->actingAsMerchant()->post(route('store.admin.subscription.cancel'));
        $cancelResponse->assertRedirect(route('store.admin.subscription.index'));
        $cancelResponse->assertSessionHas('success');

        $this->tenant->refresh();
        $this->assertEquals('cancelled', $this->tenant->subscription_status);
        $this->assertNotNull($this->subscription->fresh()->canceled_at);

        // 2. Resume
        $resumeResponse = $this->actingAsMerchant()->post(route('store.admin.subscription.resume'));
        $resumeResponse->assertRedirect(route('store.admin.subscription.index'));
        $resumeResponse->assertSessionHas('success');

        $this->tenant->refresh();
        $this->assertEquals('active', $this->tenant->subscription_status);
        $this->assertNull($this->subscription->fresh()->canceled_at);
    }

    public function test_merchant_can_view_invoice_receipt_via_ajax_and_html(): void
    {
        $invoice = TenantInvoice::where('tenant_id', $this->tenant->id)->first();

        // 1. JSON endpoint for modal
        $jsonResponse = $this->actingAsMerchant()->getJson(route('store.admin.subscription.invoice', $invoice->id));
        $jsonResponse->assertOk();
        $jsonResponse->assertJson([
            'invoice_number' => 'INV-TEST1001',
            'subtotal' => '29.00',
            'status' => 'paid',
            'store_name' => 'Lazz Pharma Express',
        ]);

        // 2. Full HTML endpoint for standalone print
        $htmlResponse = $this->actingAsMerchant()->get(route('store.admin.subscription.invoice', $invoice->id));
        $htmlResponse->assertOk();
        $htmlResponse->assertSee('INV-TEST1001');
        $htmlResponse->assertSee('$29.00');
        $htmlResponse->assertSee('Lazz Pharma Express');
    }

    public function test_multitenant_isolation_protects_invoices(): void
    {
        // Create second tenant
        $otherUser = User::create([
            'name' => 'Other Owner',
            'email' => 'other@pharmacy.com',
            'password' => bcrypt('password'),
            'status' => 1,
        ]);

        $otherTenant = Tenant::create([
            'user_id' => $otherUser->id,
            'store_name' => 'Other Pharmacy',
            'subdomain' => 'otherpharmacy',
            'status' => 'active',
        ]);

        $otherInvoice = TenantInvoice::create([
            'tenant_id' => $otherTenant->id,
            'invoice_number' => 'INV-OTHER-999',
            'subtotal' => 199.00,
            'total' => 199.00,
            'status' => 'paid',
        ]);

        // Tenant 1 cannot access Tenant 2's invoice
        $response = $this->actingAsMerchant()->get(route('store.admin.subscription.invoice', $otherInvoice->id));
        $response->assertNotFound();
    }
}
