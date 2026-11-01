<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderPayment;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class StoreTransactionManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Tenant $tenant;
    private User $otherUser;
    private Tenant $otherTenant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);

        // 1. Create main user & tenant
        $this->user = User::create([
            'name' => 'Store Finance Admin',
            'email' => 'finance@example.com',
            'password' => bcrypt('password'),
            'status' => 1,
        ]);

        $this->tenant = Tenant::create([
            'user_id' => $this->user->id,
            'store_name' => 'Lazz Pharma Care',
            'subdomain' => 'lazzpharma',
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
            'Store Order List',
            'Store Order View',
            'Store Order Create',
            'Store Order Edit',
            'Store Transaction List',
            'Store Transaction View',
            'Store Transaction Create',
            'Store Transaction Refund',
            'Store Transaction Export',
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

    private function createOrder(Tenant $tenant, string $orderNumber = 'ORD-TEST-1', float $total = 500.00, string $paymentMethod = 'cod', string $paymentStatus = 'unpaid'): Order
    {
        return Order::create([
            'tenant_id' => $tenant->id,
            'public_id' => (string) Str::uuid(),
            'order_number' => $orderNumber,
            'status' => 'pending',
            'payment_method' => $paymentMethod,
            'payment_status' => $paymentStatus,
            'currency' => 'BDT',
            'subtotal' => $total - 50.00,
            'shipping_charge' => 50.00,
            'discount_amount' => 0.00,
            'tax_amount' => 0.00,
            'total' => $total,
            'shipping_name' => 'Test Customer',
            'shipping_phone' => '+8801700000000',
            'shipping_address' => 'House 1, Road 2, Dhaka',
            'shipping_city' => 'Dhaka',
            'placed_at' => now(),
        ]);
    }

    public function test_merchant_can_view_transactions_index_with_kpi_summary(): void
    {
        $order = $this->createOrder($this->tenant, 'ORD-TX-01', 1000.00, 'bkash', 'paid');

        OrderPayment::create([
            'tenant_id' => $this->tenant->id,
            'order_id' => $order->id,
            'type' => 'charge',
            'payment_method' => 'bkash',
            'transaction_id' => 'BKASH-TEST-001',
            'amount' => 1000.00,
            'currency' => 'BDT',
            'status' => 'successful',
            'paid_at' => now(),
        ]);

        OrderPayment::create([
            'tenant_id' => $this->tenant->id,
            'order_id' => $order->id,
            'type' => 'charge',
            'payment_method' => 'cod',
            'transaction_id' => 'COD-TEST-002',
            'amount' => 350.00,
            'currency' => 'BDT',
            'status' => 'pending',
            'paid_at' => null,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('store.admin.transactions.index'));

        $response->assertOk();
        $response->assertSee('Transactions &amp; Payments', false);
        $response->assertSee('BKASH-TEST-001');
        $response->assertSee('COD-TEST-002');
        $response->assertSee('1,000.00');
        $response->assertSee('350.00');
    }

    public function test_merchant_can_filter_and_search_transactions(): void
    {
        $order = $this->createOrder($this->tenant, 'ORD-TX-FILTER', 1500.00, 'card', 'paid');

        OrderPayment::create([
            'tenant_id' => $this->tenant->id,
            'order_id' => $order->id,
            'type' => 'charge',
            'payment_method' => 'card',
            'transaction_id' => 'CARD-UNIQUE-777',
            'amount' => 1500.00,
            'currency' => 'BDT',
            'status' => 'successful',
            'paid_at' => now(),
        ]);

        OrderPayment::create([
            'tenant_id' => $this->tenant->id,
            'order_id' => $order->id,
            'type' => 'charge',
            'payment_method' => 'rocket',
            'transaction_id' => 'ROCKET-FAIL-888',
            'amount' => 600.00,
            'currency' => 'BDT',
            'status' => 'failed',
            'paid_at' => null,
        ]);

        // Filter by Status: successful
        $resStatus = $this->actingAs($this->user)
            ->get(route('store.admin.transactions.index', ['status' => 'successful']));
        $resStatus->assertOk();
        $resStatus->assertSee('CARD-UNIQUE-777');
        $resStatus->assertDontSee('ROCKET-FAIL-888');

        // Filter by Payment Method: rocket
        $resMethod = $this->actingAs($this->user)
            ->get(route('store.admin.transactions.index', ['payment_method' => 'rocket']));
        $resMethod->assertOk();
        $resMethod->assertSee('ROCKET-FAIL-888');
        $resMethod->assertDontSee('CARD-UNIQUE-777');

        // Search by Transaction ID
        $resSearch = $this->actingAs($this->user)
            ->get(route('store.admin.transactions.index', ['search' => 'UNIQUE-777']));
        $resSearch->assertOk();
        $resSearch->assertSee('CARD-UNIQUE-777');
        $resSearch->assertDontSee('ROCKET-FAIL-888');
    }

    public function test_merchant_can_view_transaction_details_via_ajax_and_html(): void
    {
        $order = $this->createOrder($this->tenant, 'ORD-SHOW-01', 800.00, 'bkash', 'paid');

        $payment = OrderPayment::create([
            'tenant_id' => $this->tenant->id,
            'order_id' => $order->id,
            'type' => 'charge',
            'payment_method' => 'bkash',
            'transaction_id' => 'BKASH-SHOW-123',
            'amount' => 800.00,
            'currency' => 'BDT',
            'status' => 'successful',
            'gateway_response' => ['sender' => '01711223344', 'auth' => 'OK'],
            'paid_at' => now(),
        ]);

        // AJAX JSON response
        $ajaxResponse = $this->actingAs($this->user)
            ->getJson(route('store.admin.transactions.show', $payment->id));

        $ajaxResponse->assertOk();
        $ajaxResponse->assertJsonFragment([
            'transaction_id' => 'BKASH-SHOW-123',
            'amount' => 800.0,
            'status' => 'successful',
            'order_number' => 'ORD-SHOW-01',
        ]);

        // HTML direct response
        $htmlResponse = $this->actingAs($this->user)
            ->get(route('store.admin.transactions.show', $payment->id));

        $htmlResponse->assertOk();
        $htmlResponse->assertSee('BKASH-SHOW-123');
        $htmlResponse->assertSee('ORD-SHOW-01');
    }

    public function test_merchant_can_record_manual_payment_for_order(): void
    {
        $order = $this->createOrder($this->tenant, 'ORD-MANUAL-01', 1200.00, 'cod', 'unpaid');

        $response = $this->actingAs($this->user)
            ->post(route('store.admin.transactions.store'), [
                'order_id' => $order->id,
                'payment_method' => 'cash',
                'amount' => 1200.00,
                'transaction_id' => 'CASH-REC-1001',
                'status' => 'successful',
                'paid_at' => now()->format('Y-m-d H:i:s'),
                'note' => 'Received full cash payment upon store counter collection.',
            ]);

        $response->assertRedirect(route('store.admin.transactions.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('order_payments', [
            'tenant_id' => $this->tenant->id,
            'order_id' => $order->id,
            'payment_method' => 'cash',
            'transaction_id' => 'CASH-REC-1001',
            'amount' => 1200.00,
            'status' => 'successful',
        ]);

        $order->refresh();
        $this->assertSame('paid', $order->payment_status);
    }

    public function test_merchant_can_issue_refund_on_successful_charge(): void
    {
        $order = $this->createOrder($this->tenant, 'ORD-REFUND-01', 2000.00, 'card', 'paid');

        $charge = OrderPayment::create([
            'tenant_id' => $this->tenant->id,
            'order_id' => $order->id,
            'type' => 'charge',
            'payment_method' => 'card',
            'transaction_id' => 'CHARGE-REFUNDABLE',
            'amount' => 2000.00,
            'currency' => 'BDT',
            'status' => 'successful',
            'paid_at' => now()->subDay(),
        ]);

        // Process partial refund of 500.00
        $response = $this->actingAs($this->user)
            ->post(route('store.admin.transactions.refund', $charge->id), [
                'amount' => 500.00,
                'reason' => 'Defective goods returned by customer',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('order_payments', [
            'tenant_id' => $this->tenant->id,
            'order_id' => $order->id,
            'parent_id' => $charge->id,
            'type' => 'refund',
            'amount' => 500.00,
            'status' => 'successful',
        ]);

        $charge->refresh();
        $this->assertEquals(500.00, $charge->refunded_amount);
        $this->assertEquals(1500.00, $charge->remaining_refundable);

        $order->refresh();
        $this->assertSame('partially_paid', $order->payment_status);

        // Validation rejection: Attempt to refund more than remaining 1500.00
        $invalidResponse = $this->actingAs($this->user)
            ->post(route('store.admin.transactions.refund', $charge->id), [
                'amount' => 1600.00,
                'reason' => 'Exceeding remaining balance',
            ]);

        $invalidResponse->assertSessionHasErrors(['amount']);
    }

    public function test_merchant_can_update_transaction_status(): void
    {
        $order = $this->createOrder($this->tenant, 'ORD-STATUS-01', 500.00, 'cod', 'unpaid');

        $payment = OrderPayment::create([
            'tenant_id' => $this->tenant->id,
            'order_id' => $order->id,
            'type' => 'charge',
            'payment_method' => 'cod',
            'transaction_id' => 'COD-PENDING-01',
            'amount' => 500.00,
            'currency' => 'BDT',
            'status' => 'pending',
            'paid_at' => null,
        ]);

        $response = $this->actingAs($this->user)
            ->patch(route('store.admin.transactions.status', $payment->id), [
                'status' => 'successful',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $payment->refresh();
        $this->assertSame('successful', $payment->status);
        $this->assertNotNull($payment->paid_at);

        $order->refresh();
        $this->assertSame('paid', $order->payment_status);
    }

    public function test_merchant_can_export_transactions_csv(): void
    {
        $order = $this->createOrder($this->tenant, 'ORD-EXPORT-01', 750.00, 'bkash', 'paid');

        OrderPayment::create([
            'tenant_id' => $this->tenant->id,
            'order_id' => $order->id,
            'type' => 'charge',
            'payment_method' => 'bkash',
            'transaction_id' => 'BKASH-EXP-999',
            'amount' => 750.00,
            'currency' => 'BDT',
            'status' => 'successful',
            'paid_at' => now(),
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('store.admin.transactions.export'));

        $response->assertOk();
        $this->assertTrue(str_contains($response->headers->get('Content-Type'), 'text/csv'));
        $content = $response->streamedContent();
        $this->assertStringContainsString('BKASH-EXP-999', $content);
        $this->assertStringContainsString('ORD-EXPORT-01', $content);
        $this->assertStringContainsString('750.00', $content);
    }

    public function test_multitenant_isolation_protects_transactions(): void
    {
        $otherOrder = $this->createOrder($this->otherTenant, 'OTHER-ORD-01', 999.00, 'card', 'paid');

        $otherPayment = OrderPayment::create([
            'tenant_id' => $this->otherTenant->id,
            'order_id' => $otherOrder->id,
            'type' => 'charge',
            'payment_method' => 'card',
            'transaction_id' => 'OTHER-TRX-SECRET',
            'amount' => 999.00,
            'currency' => 'USD',
            'status' => 'successful',
            'paid_at' => now(),
        ]);

        // Main user cannot view other tenant's transaction
        $response = $this->actingAs($this->user)
            ->get(route('store.admin.transactions.show', $otherPayment->id));

        $response->assertNotFound();

        // Main user index does not leak other tenant's transaction
        $indexResponse = $this->actingAs($this->user)
            ->get(route('store.admin.transactions.index'));

        $indexResponse->assertOk();
        $indexResponse->assertDontSee('OTHER-TRX-SECRET');

        // Main user cannot refund other tenant's transaction
        $refundResponse = $this->actingAs($this->user)
            ->post(route('store.admin.transactions.refund', $otherPayment->id), [
                'amount' => 100.00,
                'reason' => 'Illicit refund attempt',
            ]);

        $refundResponse->assertNotFound();
    }
}
