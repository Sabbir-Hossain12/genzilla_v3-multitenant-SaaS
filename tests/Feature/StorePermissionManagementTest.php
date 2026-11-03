<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class StorePermissionManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->seed(PermissionSeeder::class);

        $this->user = User::create([
            'name' => 'Store Admin Tester',
            'email' => 'admin@testpharma.com',
            'password' => bcrypt('password'),
            'status' => 1,
        ]);

        $this->tenant = Tenant::create([
            'user_id' => $this->user->id,
            'store_name' => 'Test Pharma',
            'subdomain' => 'testpharma',
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

        $role->givePermissionTo('Store Permission List');
        $this->user->assignRole($role);
    }

    public function test_merchant_can_access_store_permissions_page(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('store.admin.permission.index'));

        $response->assertStatus(200);
        $response->assertSee('Permissions');
    }
}
