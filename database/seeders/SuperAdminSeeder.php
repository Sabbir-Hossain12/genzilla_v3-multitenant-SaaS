<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $admin = User::updateOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Super Admin',
                'password' => 'password',
            ],
        );

        $superAdminRole = Role::firstOrCreate([
            'name' => 'Platform SuperAdmin',
            'guard_name' => 'web',
        ]);

        $allPermissions = Permission::all();
        $superAdminRole->syncPermissions($allPermissions);

        $admin->assignRole($superAdminRole);
    }
}
