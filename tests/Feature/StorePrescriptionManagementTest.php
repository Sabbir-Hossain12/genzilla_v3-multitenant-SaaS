<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\DeliveryCharge;
use App\Models\InventoryBatch;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Tenant;
use App\Models\TenantPrescription;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class StorePrescriptionManagementTest extends TestCase
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
            'name' => 'Licensed Pharmacist',
            'email' => 'pharmacist@example.com',
            'password' => bcrypt('password'),
            'status' => 1,
        ]);

        $this->tenant = Tenant::create([
            'user_id' => $this->user->id,
            'store_name' => 'Lazz Pharma Care',
            'subdomain' => 'lazzpharma',
            'subscription_status' => 'active',
            'status' => 'active',
            'rx_compliance_mode' => 'flexible',
            'enable_direct_prescriptions' => true,
        ]);

        // 2. Setup Spatie Roles & Permissions for main tenant
        $registrar = app(PermissionRegistrar::class);
        $registrar->setPermissionsTeamId($this->tenant->id);

        $role = Role::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Chief Pharmacist',
            'guard_name' => 'web',
        ]);

        $permissions = [
            'Store Dashboard',
            'Store Order List',
            'Store Order View',
            'Store Order Create',
            'Store Order Edit',
            'Store Order Delete',
            'Store Prescription List',
            'Store Prescription View',
            'Store Prescription Convert',
            'Store Prescription Reject',
            'Store Prescription Delete',
            'Store General Setting',
            'Store Edit Setting',
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
    }

    public function test_merchant_can_view_prescription_inbox_queue_with_status_counts(): void
    {
        TenantPrescription::create([
            'tenant_id' => $this->tenant->id,
            'prescription_number' => 'RX-PENDING1',
            'customer_name' => 'Mr. Rahim',
            'customer_phone' => '+8801700000001',
            'delivery_address' => 'House 12, Road 4, Dhanmondi',
            'delivery_city' => 'Dhaka',
            'patient_notes' => 'Provide medicine for 15 days',
            'prescription_image' => 'prescriptions/rx1.jpg',
            'status' => 'pending_review',
        ]);

        TenantPrescription::create([
            'tenant_id' => $this->tenant->id,
            'prescription_number' => 'RX-CONVERTED',
            'customer_name' => 'Mrs. Salma',
            'customer_phone' => '+8801700000002',
            'delivery_address' => 'Sector 7, Uttara',
            'delivery_city' => 'Dhaka',
            'prescription_image' => 'prescriptions/rx2.jpg',
            'status' => 'converted_to_order',
        ]);

        TenantPrescription::create([
            'tenant_id' => $this->tenant->id,
            'prescription_number' => 'RX-REJECTED',
            'customer_name' => 'Mr. Karim',
            'customer_phone' => '+8801700000003',
            'delivery_address' => 'Chittagong GEC',
            'delivery_city' => 'Chittagong',
            'prescription_image' => 'prescriptions/rx3.jpg',
            'status' => 'rejected',
            'rejection_reason' => 'Image too blurry',
        ]);

        $response = $this->actingAs($this->user)->get(route('store.admin.prescriptions.index'));

        $response->assertOk();
        $response->assertSee('RX-PENDING1');
        $response->assertSee('Mr. Rahim');
        $response->assertSee('Provide medicine for 15 days');
        $response->assertSee('Pending Review');
    }

    public function test_merchant_can_filter_and_search_prescriptions(): void
    {
        $rx1 = TenantPrescription::create([
            'tenant_id' => $this->tenant->id,
            'prescription_number' => 'RX-10001',
            'customer_name' => 'Tariq Islam',
            'customer_phone' => '+8801811111111',
            'delivery_address' => 'Mirpur 10',
            'prescription_image' => 'prescriptions/tariq.jpg',
            'status' => 'pending_review',
        ]);

        $rx2 = TenantPrescription::create([
            'tenant_id' => $this->tenant->id,
            'prescription_number' => 'RX-10002',
            'customer_name' => 'Farhana Akter',
            'customer_phone' => '+8801922222222',
            'delivery_address' => 'Gulshan 2',
            'prescription_image' => 'prescriptions/farhana.jpg',
            'status' => 'converted_to_order',
        ]);

        // Filter by status: converted_to_order
        $response = $this->actingAs($this->user)->get(route('store.admin.prescriptions.index', ['status' => 'converted_to_order']));
        $response->assertOk();
        $response->assertSee('RX-10002');
        $response->assertDontSee('RX-10001');

        // Search by customer name
        $searchResponse = $this->actingAs($this->user)->get(route('store.admin.prescriptions.index', ['status' => 'all', 'search' => 'Tariq']));
        $searchResponse->assertOk();
        $searchResponse->assertSee('RX-10001');
        $searchResponse->assertDontSee('RX-10002');
    }

    public function test_merchant_can_view_prescription_show_workspace(): void
    {
        $rx = TenantPrescription::create([
            'tenant_id' => $this->tenant->id,
            'prescription_number' => 'RX-SHOW1',
            'customer_name' => 'Zakir Hossain',
            'customer_phone' => '+8801733333333',
            'delivery_address' => 'Banani Block C',
            'delivery_city' => 'Dhaka',
            'patient_notes' => 'Please include sugar-free syrups',
            'prescription_image' => 'prescriptions/slip.jpg',
            'status' => 'pending_review',
        ]);

        $response = $this->actingAs($this->user)->get(route('store.admin.prescriptions.show', $rx));

        $response->assertOk();
        $response->assertSee('RX-SHOW1');
        $response->assertSee('Zakir Hossain');
        $response->assertSee('Please include sugar-free syrups');
        $response->assertSee('Prescription Order Builder');
    }

    public function test_merchant_can_convert_direct_prescription_into_an_order_with_dosage_instructions(): void
    {
        $product = Product::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Amoxicillin 500mg Capsule',
            'slug' => 'amoxicillin-500mg',
            'requires_prescription' => true,
            'status' => 'active',
        ]);

        $variant = ProductVariant::create([
            'tenant_id' => $this->tenant->id,
            'product_id' => $product->id,
            'title' => 'Box of 10 strips (100 caps)',
            'sku' => 'AMX-500-100',
            'price' => 12.50,
            'mrp' => 14.00,
            'options_hash' => md5('box-100'),
            'track_inventory' => true,
            'status' => 'active',
        ]);

        $batch = InventoryBatch::create([
            'tenant_id' => $this->tenant->id,
            'variant_id' => $variant->id,
            'batch_number' => 'BATCH-AMX-01',
            'quantity' => 50,
            'reserved' => 0,
            'purchase_price' => 8.00,
            'expiry_date' => now()->addYear(),
            'status' => 'active',
        ]);

        $delivery = DeliveryCharge::create([
            'tenant_id' => $this->tenant->id,
            'title' => 'Inside Dhaka Express',
            'slug' => 'inside-dhaka-express',
            'charge_amount' => 2.50,
            'status' => true,
        ]);

        $rx = TenantPrescription::create([
            'tenant_id' => $this->tenant->id,
            'prescription_number' => 'RX-BUILD1',
            'customer_name' => 'Patient Kamal',
            'customer_phone' => '+8801744444444',
            'customer_email' => 'kamal@example.com',
            'delivery_address' => 'Road 11, Banani',
            'delivery_city' => 'Dhaka',
            'patient_notes' => 'Complete 7 days course',
            'prescription_image' => 'prescriptions/kamal_slip.jpg',
            'status' => 'pending_review',
        ]);

        $payload = [
            'shipping_name' => 'Patient Kamal',
            'shipping_phone' => '+8801744444444',
            'shipping_email' => 'kamal@example.com',
            'shipping_address' => 'Road 11, Banani',
            'shipping_city' => 'Dhaka',
            'delivery_charge_id' => $delivery->id,
            'payment_method' => 'cod',
            'payment_status' => 'unpaid',
            'pharmacist_notes' => 'Verified with patient doctor phone',
            'items' => [
                [
                    'variant_id' => $variant->id,
                    'quantity' => 2,
                    'unit_price' => 12.50,
                    'discount_amount' => 0.00,
                    'dosage_instructions' => '1+0+1 after meal for 7 days',
                ],
            ],
        ];

        $response = $this->actingAs($this->user)->post(route('store.admin.prescriptions.convert', $rx), $payload);

        $rx->refresh();
        $this->assertEquals('converted_to_order', $rx->status);
        $this->assertNotNull($rx->order_id);
        $this->assertEquals($this->user->id, $rx->reviewed_by_user_id);
        $this->assertNotNull($rx->reviewed_at);

        // Check created Order
        $order = Order::findOrFail($rx->order_id);
        $this->assertEquals($this->tenant->id, $order->tenant_id);
        $this->assertEquals('pending', $order->status);
        $this->assertEquals('approved', $order->prescription_status);
        $this->assertTrue($order->is_direct_prescription);
        $this->assertEquals('prescriptions/kamal_slip.jpg', $order->prescription_image);
        $this->assertEquals(25.00, (float)$order->subtotal);
        $this->assertEquals(2.50, (float)$order->shipping_charge);
        $this->assertEquals(27.50, (float)$order->total);

        // Check order product and dosage
        $orderItem = $order->products()->first();
        $this->assertNotNull($orderItem);
        $this->assertEquals($variant->id, $orderItem->variant_id);
        $this->assertEquals(2, $orderItem->quantity);
        $this->assertEquals('1+0+1 after meal for 7 days', $orderItem->dosage_instructions);

        // Check inventory reservation
        $batch->refresh();
        $this->assertEquals(2, $batch->reserved);

        $response->assertRedirect(route('store.admin.orders.show', $order));
    }

    public function test_cannot_convert_an_already_converted_prescription(): void
    {
        $rx = TenantPrescription::create([
            'tenant_id' => $this->tenant->id,
            'prescription_number' => 'RX-ALREADY-CONVERTED',
            'customer_name' => 'Done Patient',
            'customer_phone' => '+8801755555555',
            'delivery_address' => 'Uttara',
            'prescription_image' => 'prescriptions/done.jpg',
            'status' => 'converted_to_order',
        ]);

        $response = $this->actingAs($this->user)->post(route('store.admin.prescriptions.convert', $rx), [
            'shipping_name' => 'Done Patient',
            'shipping_phone' => '+8801755555555',
            'shipping_address' => 'Uttara',
            'shipping_city' => 'Dhaka',
            'payment_method' => 'cod',
            'payment_status' => 'unpaid',
            'items' => [],
        ]);

        $response->assertSessionHasErrors('status');
    }

    public function test_merchant_can_reject_prescription_with_reason(): void
    {
        $rx = TenantPrescription::create([
            'tenant_id' => $this->tenant->id,
            'prescription_number' => 'RX-REJECTME',
            'customer_name' => 'Blurry Patient',
            'customer_phone' => '+8801766666666',
            'delivery_address' => 'Mohakhali',
            'prescription_image' => 'prescriptions/blurry.jpg',
            'status' => 'pending_review',
        ]);

        $response = $this->actingAs($this->user)->post(route('store.admin.prescriptions.reject', $rx), [
            'rejection_reason' => 'The uploaded photo is too blurry to read the dosage or doctor signature.',
        ]);

        $rx->refresh();
        $this->assertEquals('rejected', $rx->status);
        $this->assertEquals('The uploaded photo is too blurry to read the dosage or doctor signature.', $rx->rejection_reason);
        $this->assertEquals($this->user->id, $rx->reviewed_by_user_id);
        $this->assertNotNull($rx->reviewed_at);

        $response->assertRedirect(route('store.admin.prescriptions.index'));
    }

    public function test_pharmacist_can_review_and_approve_prescription_on_order_show_page(): void
    {
        $order = Order::create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => null,
            'status' => 'pending',
            'payment_method' => 'cod',
            'payment_status' => 'unpaid',
            'prescription_status' => 'pending_review',
            'prescription_image' => 'orders/rx_checkout.jpg',
            'subtotal' => 50.00,
            'total' => 50.00,
            'shipping_name' => 'Checkout Customer',
            'shipping_phone' => '+8801777777777',
            'shipping_address' => 'Dhanmondi 27',
            'shipping_city' => 'Dhaka',
        ]);

        // Pharmacist approves prescription
        $response = $this->actingAs($this->user)->patch(route('store.admin.orders.status', $order), [
            'prescription_status' => 'approved',
            'status' => 'processing',
            'pharmacist_rx_notes' => 'Patient verified antibiotic dosage.',
        ]);

        $order->refresh();
        $this->assertEquals('approved', $order->prescription_status);
        $this->assertEquals('processing', $order->status);
        $this->assertEquals('Patient verified antibiotic dosage.', $order->pharmacist_rx_notes);
        $this->assertEquals($this->user->id, $order->rx_reviewed_by_user_id);
        $this->assertNotNull($order->rx_reviewed_at);
    }

    public function test_store_settings_can_toggle_prescription_compliance_mode_and_direct_uploads(): void
    {
        $response = $this->actingAs($this->user)->put(route('store.admin.settings.update'), [
            'store_name' => 'Lazz Pharma Care',
            'support_email' => 'support@lazzpharma.com',
            'support_phone' => '+8801700000000',
            'currency' => 'USD',
            'timezone' => 'Asia/Dhaka',
            'meta_robots' => 'index, follow',
            'rx_compliance_mode' => 'strict',
            'enable_direct_prescriptions' => 1,
        ]);

        $this->tenant->refresh();
        $this->assertEquals('strict', $this->tenant->rx_compliance_mode);
        $this->assertTrue($this->tenant->enable_direct_prescriptions);

        $response->assertRedirect(route('store.admin.settings.index'));
    }

    public function test_multi_tenant_isolation_prevents_viewing_or_converting_other_tenant_prescriptions(): void
    {
        $otherRx = TenantPrescription::create([
            'tenant_id' => $this->otherTenant->id,
            'prescription_number' => 'RX-OTHER-TENANT',
            'customer_name' => 'Foreign Patient',
            'customer_phone' => '+8801799999999',
            'delivery_address' => 'Other city',
            'prescription_image' => 'other.jpg',
            'status' => 'pending_review',
        ]);

        // Main user cannot view other tenant's prescription
        $response = $this->actingAs($this->user)->get(route('store.admin.prescriptions.show', $otherRx));
        $response->assertNotFound();

        // Main user cannot reject other tenant's prescription
        $rejectResponse = $this->actingAs($this->user)->post(route('store.admin.prescriptions.reject', $otherRx), [
            'rejection_reason' => 'Hack attempt',
        ]);
        $rejectResponse->assertNotFound();
    }

    public function test_merchant_can_delete_prescription(): void
    {
        $rx = TenantPrescription::create([
            'tenant_id' => $this->tenant->id,
            'prescription_number' => 'RX-DELETE1',
            'customer_name' => 'Temporary Patient',
            'customer_phone' => '+8801788888888',
            'delivery_address' => 'Gulshan',
            'prescription_image' => 'prescriptions/delete.jpg',
            'status' => 'pending_review',
        ]);

        $response = $this->actingAs($this->user)->delete(route('store.admin.prescriptions.destroy', $rx));

        $this->assertDatabaseMissing('tenant_prescriptions', [
            'id' => $rx->id,
        ]);

        $response->assertRedirect(route('store.admin.prescriptions.index'));
    }
}
