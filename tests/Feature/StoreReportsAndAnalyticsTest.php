<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderPayment;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\ProductVariant;
use App\Models\Tenant;
use App\Models\TenantPrescription;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class StoreReportsAndAnalyticsTest extends TestCase
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
            'name' => 'Store Pharmacist Admin',
            'email' => 'pharmacist@example.com',
            'password' => bcrypt('password'),
            'status' => 1,
        ]);

        $this->tenant = Tenant::create([
            'user_id' => $this->user->id,
            'store_name' => 'Care Pharmacy BD',
            'subdomain' => 'carepharma',
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
            'Store Report List',
            'Store Report Export',
            'Store Order List',
            'Store Order Create',
            'Store Product List',
            'Store Product Create',
            'Store Product Review List',
            'Store Inventory List',
            'Store Prescription List',
            'Store Transaction List',
            'Store Abandoned Checkout List',
            'Store Customer List',
        ];

        foreach ($permissions as $p) {
            $role->givePermissionTo($p);
        }

        $this->user->assignRole($role);

        // 3. Other tenant to test data isolation
        $this->otherUser = User::create([
            'name' => 'Competitor Pharmacist',
            'email' => 'competitor@example.com',
            'password' => bcrypt('password'),
            'status' => 1,
        ]);

        $this->otherTenant = Tenant::create([
            'user_id' => $this->otherUser->id,
            'store_name' => 'MediStore Competitor',
            'subdomain' => 'medistore',
            'subscription_status' => 'active',
            'status' => 'active',
        ]);

        $otherRole = Role::create([
            'tenant_id' => $this->otherTenant->id,
            'name' => 'Store Owner',
            'guard_name' => 'web',
        ]);

        foreach ($permissions as $p) {
            $otherRole->givePermissionTo($p);
        }
        $this->otherUser->assignRole($otherRole);
    }

    private function actingAsTenant(): self
    {
        session(['tenant_id' => $this->tenant->id]);
        return $this->actingAs($this->user);
    }

    private function actingAsOtherTenant(): self
    {
        session(['tenant_id' => $this->otherTenant->id]);
        return $this->actingAs($this->otherUser);
    }

    private function createOrder(array $attributes = []): Order
    {
        return Order::create(array_merge([
            'tenant_id' => $this->tenant->id,
            'order_number' => 'ORD-' . strtoupper(fake()->bothify('??###')),
            'subtotal' => 500,
            'discount_amount' => 50,
            'shipping_charge' => 60,
            'total' => 510,
            'status' => 'processing',
            'payment_status' => 'paid',
            'payment_method' => 'bkash',
            'shipping_name' => 'Test Customer',
            'shipping_phone' => '01700000001',
            'shipping_address' => 'House 12, Road 4, Dhanmondi',
            'shipping_city' => 'Dhaka',
        ], $attributes));
    }

    public function test_store_admin_can_view_dashboard_with_analytics_and_charts(): void
    {
        $this->createOrder([
            'order_number' => 'ORD-10001',
            'shipping_name' => 'Rahim Ahmed',
        ]);

        $response = $this->actingAsTenant()->get(route('store.admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSeeText('Pharmacy Overview');
        $response->assertSeeText('Gross Sales');
        $response->assertSeeText('Total Orders');
        $response->assertSee('salesTrendsChart');
        $response->assertSee('paymentMethodsChart');
        $response->assertSee('ORD-10001');
    }

    public function test_store_admin_can_view_reports_page(): void
    {
        $response = $this->actingAsTenant()->get(route('store.admin.reports.index'));

        $response->assertStatus(200);
        $response->assertSeeText('Business Analytics');
        $response->assertSeeText('Orders Performance Report');
        $response->assertSeeText('Export Excel (CSV)');
        $response->assertSeeText('Download PDF');
    }

    public function test_store_admin_can_switch_report_types(): void
    {
        // Inventory Report
        $resInv = $this->actingAsTenant()->get(route('store.admin.reports.index', ['type' => 'inventory']));
        $resInv->assertStatus(200);
        $resInv->assertSeeText('Inventory Stock Valuation');

        // Prescriptions Report
        $resRx = $this->actingAsTenant()->get(route('store.admin.reports.index', ['type' => 'prescriptions']));
        $resRx->assertStatus(200);
        $resRx->assertSeeText('Prescriptions Compliance');

        // Transactions Report
        $resTx = $this->actingAsTenant()->get(route('store.admin.reports.index', ['type' => 'transactions']));
        $resTx->assertStatus(200);
        $resTx->assertSeeText('Financial Ledger');

        // Abandoned Checkouts Report
        $resCart = $this->actingAsTenant()->get(route('store.admin.reports.index', ['type' => 'abandoned_carts']));
        $resCart->assertStatus(200);
        $resCart->assertSeeText('Abandoned Checkouts');

        // Reviews Report
        $resRev = $this->actingAsTenant()->get(route('store.admin.reports.index', ['type' => 'reviews']));
        $resRev->assertStatus(200);
        $resRev->assertSeeText('Product Reviews');
    }

    public function test_store_admin_can_export_csv(): void
    {
        $this->createOrder([
            'order_number' => 'ORD-CSV-1',
            'subtotal' => 1200,
            'discount_amount' => 100,
            'shipping_charge' => 60,
            'total' => 1160,
            'status' => 'delivered',
            'payment_status' => 'paid',
            'payment_method' => 'cod',
            'shipping_name' => 'Karim Export',
            'shipping_phone' => '01800000002',
            'shipping_address' => 'Mirpur 10, Dhaka',
            'shipping_city' => 'Dhaka',
        ]);

        $response = $this->actingAsTenant()->get(route('store.admin.reports.export.csv', ['type' => 'sales']));

        $response->assertStatus(200);
        $this->assertStringContainsString('text/csv', $response->headers->get('content-type'));
        $this->assertStringContainsString('report-sales', $response->headers->get('content-disposition'));

        // Check streamed content
        $content = $response->streamedContent();
        $this->assertStringContainsString('ORD-CSV-1', $content);
        $this->assertStringContainsString('Karim Export', $content);
    }

    public function test_store_admin_can_export_pdf(): void
    {
        $this->createOrder([
            'order_number' => 'ORD-PDF-1',
            'subtotal' => 800,
            'discount_amount' => 0,
            'shipping_charge' => 50,
            'total' => 850,
            'status' => 'delivered',
            'payment_status' => 'paid',
            'payment_method' => 'bkash',
            'shipping_name' => 'Sumon PDF',
            'shipping_phone' => '01900000003',
            'shipping_address' => 'Uttara Sector 7, Dhaka',
            'shipping_city' => 'Dhaka',
        ]);

        $response = $this->actingAsTenant()->get(route('store.admin.reports.export.pdf', ['type' => 'sales']));

        $response->assertStatus(200);
        $this->assertStringContainsString('application/pdf', $response->headers->get('content-type'));
        $this->assertStringContainsString('report-sales', $response->headers->get('content-disposition'));
    }

    public function test_unauthorized_user_cannot_view_or_export_reports(): void
    {
        // Create user without report permissions
        $limitedUser = User::create([
            'name' => 'Junior Staff',
            'email' => 'junior@example.com',
            'password' => bcrypt('password'),
            'status' => 1,
        ]);

        $limitedRole = Role::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Staff Non-Reports',
            'guard_name' => 'web',
        ]);
        $limitedRole->givePermissionTo('Store Dashboard');
        $limitedUser->assignRole($limitedRole);

        session(['tenant_id' => $this->tenant->id]);
        $response = $this->actingAs($limitedUser)->get(route('store.admin.reports.index'));
        $response->assertStatus(403);

        $resExport = $this->actingAs($limitedUser)->get(route('store.admin.reports.export.csv'));
        $resExport->assertStatus(403);
    }

    public function test_reports_data_is_strictly_isolated_by_tenant(): void
    {
        // Order for Tenant 1
        $this->createOrder([
            'tenant_id' => $this->tenant->id,
            'order_number' => 'ORD-TENANT-1',
            'subtotal' => 500,
            'discount_amount' => 0,
            'shipping_charge' => 60,
            'total' => 560,
            'status' => 'delivered',
            'payment_status' => 'paid',
            'shipping_name' => 'Secret Customer 1',
            'shipping_phone' => '01711111111',
            'shipping_address' => 'Mohakhali DOHS, Dhaka',
            'shipping_city' => 'Dhaka',
        ]);

        // Order for Other Tenant
        $this->createOrder([
            'tenant_id' => $this->otherTenant->id,
            'order_number' => 'ORD-TENANT-OTHER',
            'subtotal' => 9999,
            'discount_amount' => 0,
            'shipping_charge' => 60,
            'total' => 10059,
            'status' => 'delivered',
            'payment_status' => 'paid',
            'shipping_name' => 'Competitor Secret Buyer',
            'shipping_phone' => '01822222222',
            'shipping_address' => 'Gulshan 2, Dhaka',
            'shipping_city' => 'Dhaka',
        ]);

        // When Tenant 1 views report
        $res1 = $this->actingAsTenant()->get(route('store.admin.reports.index', ['type' => 'sales']));
        $res1->assertSeeText('ORD-TENANT-1');
        $res1->assertDontSeeText('ORD-TENANT-OTHER');
        $res1->assertDontSeeText('Competitor Secret Buyer');

        // When Other Tenant views report
        $resOther = $this->actingAsOtherTenant()->get(route('store.admin.reports.index', ['type' => 'sales']));
        $resOther->assertSeeText('ORD-TENANT-OTHER');
        $resOther->assertDontSeeText('ORD-TENANT-1');
        $resOther->assertDontSeeText('Secret Customer 1');
    }
}
