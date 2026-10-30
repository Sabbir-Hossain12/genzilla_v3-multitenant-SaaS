<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class StoreCustomerManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Store Merchant',
            'email' => 'merchant_customers@example.com',
            'password' => bcrypt('password'),
            'status' => 1,
        ]);

        $this->tenant = Tenant::create([
            'user_id' => $this->user->id,
            'store_name' => 'Customer Test Pharmacy',
            'subdomain' => 'customertest',
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
            'Store Customer List',
            'Store Customer Create',
            'Store Customer View',
            'Store Customer Edit',
            'Store Customer Delete',
        ];

        foreach ($permissions as $permName) {
            $perm = Permission::firstOrCreate(
                ['name' => $permName, 'guard_name' => 'web'],
                ['group_name' => 'Customer Management', 'panel' => 'store']
            );
            $role->givePermissionTo($perm);
        }

        $this->user->assignRole($role);
    }

    public function test_customer_list_renders_and_filters(): void
    {
        Customer::create([
            'tenant_id' => $this->tenant->id,
            'first_name' => 'Alice',
            'last_name' => 'Smith',
            'email' => 'alice@example.com',
            'phone' => '+15551234567',
            'status' => 'active',
        ]);

        Customer::create([
            'tenant_id' => $this->tenant->id,
            'first_name' => 'Bob',
            'last_name' => 'Jones',
            'email' => 'bob@example.com',
            'phone' => '+15559876543',
            'status' => 'inactive',
        ]);

        // 1. Index page render
        $response = $this->actingAs($this->user)->get($this->storeUrl('/store/customers'));
        $response->assertOk();
        $response->assertSee('Alice Smith');
        $response->assertSee('Bob Jones');

        // 2. Filter by search query
        $searchRes = $this->actingAs($this->user)->get($this->storeUrl('/store/customers?search=Alice'));
        $searchRes->assertOk();
        $searchRes->assertSee('Alice Smith');
        $searchRes->assertDontSee('Bob Jones');

        // 3. Filter by status
        $statusRes = $this->actingAs($this->user)->get($this->storeUrl('/store/customers?status=inactive'));
        $statusRes->assertOk();
        $statusRes->assertSee('Bob Jones');
        $statusRes->assertDontSee('Alice Smith');
    }

    public function test_customer_creation_with_address(): void
    {
        $postData = [
            'first_name' => 'Michael',
            'last_name' => 'Chen',
            'email' => 'michael.chen@example.com',
            'phone' => '+14155550188',
            'password' => 'secret1234',
            'status' => 'active',
            // Address data
            'address_title' => 'Home',
            'address_1' => '742 Evergreen Terrace',
            'address_2' => 'Apt 4B',
            'state_district' => 'Springfield',
            'zip' => '97477',
            'country' => 'United States',
        ];

        $response = $this->actingAs($this->user)->post($this->storeUrl('/store/customers'), $postData);
        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $customer = Customer::where('tenant_id', $this->tenant->id)->where('email', 'michael.chen@example.com')->first();
        $this->assertNotNull($customer);
        $this->assertEquals('Michael', $customer->first_name);
        $this->assertEquals('Chen', $customer->last_name);
        $this->assertEquals('+14155550188', $customer->phone);
        $this->assertEquals('active', $customer->status);

        // Verify Address created & marked default
        $this->assertCount(1, $customer->addresses);
        $address = $customer->addresses->first();
        $this->assertEquals('Home', $address->title);
        $this->assertEquals('742 Evergreen Terrace', $address->address_1);
        $this->assertEquals('Springfield', $address->state_district);
        $this->assertTrue((bool)$address->is_default);
    }

    public function test_customer_edit_and_update(): void
    {
        $customer = Customer::create([
            'tenant_id' => $this->tenant->id,
            'first_name' => 'Sarah',
            'last_name' => 'Connor',
            'email' => 'sarah@skynet.test',
            'phone' => '+15550009999',
            'status' => 'active',
        ]);

        $editRes = $this->actingAs($this->user)->get($this->storeUrl('/store/customers/' . $customer->id . '/edit'));
        $editRes->assertOk();
        $editRes->assertSee('Sarah');
        $editRes->assertSee('Connor');

        $updateRes = $this->actingAs($this->user)->put($this->storeUrl('/store/customers/' . $customer->id), [
            'first_name' => 'Sarah Jane',
            'last_name' => 'Smith',
            'email' => 'sarah.jane@skynet.test',
            'phone' => '+15551112222',
            'status' => 'inactive',
        ]);

        $updateRes->assertSessionHasNoErrors();
        $updateRes->assertRedirect();

        $customer->refresh();
        $this->assertEquals('Sarah Jane', $customer->first_name);
        $this->assertEquals('Smith', $customer->last_name);
        $this->assertEquals('sarah.jane@skynet.test', $customer->email);
        $this->assertEquals('inactive', $customer->status);
    }

    public function test_customer_status_toggle(): void
    {
        $customer = Customer::create([
            'tenant_id' => $this->tenant->id,
            'first_name' => 'Bruce',
            'last_name' => 'Wayne',
            'email' => 'bruce@wayne.test',
            'status' => 'active',
        ]);

        $res = $this->actingAs($this->user)->patchJson($this->storeUrl('/store/customers/' . $customer->id . '/toggle'));
        $res->assertOk();
        $res->assertJson(['success' => true, 'status' => 'suspended']);
        $this->assertEquals('suspended', $customer->fresh()->status);

        $res2 = $this->actingAs($this->user)->patchJson($this->storeUrl('/store/customers/' . $customer->id . '/toggle'));
        $res2->assertOk();
        $res2->assertJson(['success' => true, 'status' => 'active']);
        $this->assertEquals('active', $customer->fresh()->status);
    }

    public function test_customer_address_management_and_default_switch(): void
    {
        $customer = Customer::create([
            'tenant_id' => $this->tenant->id,
            'first_name' => 'Clark',
            'last_name' => 'Kent',
            'email' => 'clark@dailyplanet.test',
            'status' => 'active',
        ]);

        // 1. Add Address 1 (Default)
        $addr1Res = $this->actingAs($this->user)->post($this->storeUrl('/store/customers/' . $customer->id . '/addresses'), [
            'title' => 'Apartment',
            'first_name' => 'Clark',
            'last_name' => 'Kent',
            'address_1' => '344 Clinton St',
            'state_district' => 'Metropolis',
            'zip' => '10001',
            'country' => 'United States',
            'is_default' => 1,
        ]);
        $addr1Res->assertRedirect();

        $addr1 = CustomerAddress::where('customer_id', $customer->id)->where('title', 'Apartment')->first();
        $this->assertNotNull($addr1);
        $this->assertTrue((bool)$addr1->is_default);

        // 2. Add Address 2 (Switch default)
        $addr2Res = $this->actingAs($this->user)->post($this->storeUrl('/store/customers/' . $customer->id . '/addresses'), [
            'title' => 'Daily Planet Office',
            'first_name' => 'Clark',
            'last_name' => 'Kent',
            'address_1' => '1000 Broadway',
            'state_district' => 'Metropolis',
            'zip' => '10002',
            'country' => 'United States',
            'is_default' => 1,
        ]);
        $addr2Res->assertRedirect();

        $addr2 = CustomerAddress::where('customer_id', $customer->id)->where('title', 'Daily Planet Office')->first();
        $this->assertNotNull($addr2);
        $this->assertTrue((bool)$addr2->is_default);
        // Address 1 should no longer be default
        $this->assertFalse((bool)$addr1->fresh()->is_default);

        // 3. Delete Address 2
        $deleteAddrRes = $this->actingAs($this->user)->delete($this->storeUrl('/store/customers/' . $customer->id . '/addresses/' . $addr2->id));
        $deleteAddrRes->assertRedirect();
        $this->assertDatabaseMissing('customer_addresses', ['id' => $addr2->id]);
    }

    public function test_customer_deletion(): void
    {
        $customer = Customer::create([
            'tenant_id' => $this->tenant->id,
            'first_name' => 'Diana',
            'last_name' => 'Prince',
            'email' => 'diana@themyscira.test',
            'status' => 'active',
        ]);

        $address = CustomerAddress::create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $customer->id,
            'title' => 'Museum',
            'first_name' => 'Diana',
            'last_name' => 'Prince',
            'address_1' => '100 Museum Way',
            'state_district' => 'Gateway City',
            'country' => 'United States',
            'is_default' => true,
        ]);

        $deleteRes = $this->actingAs($this->user)->delete($this->storeUrl('/store/customers/' . $customer->id));
        $deleteRes->assertRedirect();

        $this->assertDatabaseMissing('customers', ['id' => $customer->id]);
        $this->assertDatabaseMissing('customer_addresses', ['id' => $address->id]);
    }

    public function test_multitenant_customer_isolation(): void
    {
        // Another tenant
        $otherUser = User::create([
            'name' => 'Other Merchant',
            'email' => 'other_merchant@example.com',
            'password' => bcrypt('password'),
            'status' => 1,
        ]);

        $otherTenant = Tenant::create([
            'user_id' => $otherUser->id,
            'store_name' => 'Other Pharmacy',
            'subdomain' => 'otherpharm',
            'subscription_status' => 'active',
            'status' => 'active',
        ]);

        $otherCustomer = Customer::create([
            'tenant_id' => $otherTenant->id,
            'first_name' => 'Foreign',
            'last_name' => 'Customer',
            'email' => 'foreign@example.com',
            'status' => 'active',
        ]);

        // Attempt to view or modify other tenant's customer under our tenant session -> 404
        $res = $this->actingAs($this->user)->get($this->storeUrl('/store/customers/' . $otherCustomer->id));
        $res->assertNotFound();

        $updateRes = $this->actingAs($this->user)->put($this->storeUrl('/store/customers/' . $otherCustomer->id), [
            'first_name' => 'Hacked',
            'last_name' => 'Name',
            'email' => 'foreign@example.com',
            'status' => 'active',
        ]);
        $updateRes->assertNotFound();
    }

    private function storeUrl(string $path): string
    {
        return 'http://' . config('app.base_domain') . $path;
    }
}
