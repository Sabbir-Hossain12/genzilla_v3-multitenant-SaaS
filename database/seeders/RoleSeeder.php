<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // ==========================================
        // 1. PLATFORM ROLES (SaaS Level)
        // ==========================================

        // Fetch all permissions where panel = 'platform'
        $platformPermissions = Permission::where('panel', 'platform')->pluck('name')->toArray();

        // Define security permissions for platform
        $platformSecurity = [
            'Admin List', 'Create Admin', 'Edit Admin', 'Delete Admin', 'Status Admin',
            'Role List', 'Role Create', 'Edit Role', 'Delete Role', 'Assign Permission',
            'Permission List', 'Create Permission', 'Edit Permission', 'Delete Permission',
        ];

        // Platform Co-Admin gets everything except high-level security management
        $platformCoadmin = array_values(array_diff($platformPermissions, $platformSecurity));

        // Platform Staff gets limited dashboard and support permissions
        $platformStaff = Permission::where('panel', 'platform')
            ->whereIn('group_name', ['Platform Admin Dashboard', 'Content & Support'])
            ->pluck('name')
            ->toArray();

        $platformMatrix = [
            'Platform SuperAdmin' => $platformPermissions, // Only platform permissions
            'Platform Coadmin'    => $platformCoadmin,    // Only platform permissions
            'Platform Staff'      => $platformStaff,      // Only platform permissions
        ];

        foreach ($platformMatrix as $roleName => $permissions) {
            $role = Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web',
                'tenant_id' => null
            ]);
            $role->syncPermissions($permissions);
        }

        // ==========================================
        // 2. DEFAULT STORE ROLES (Merchant Level)
        // ==========================================

        // Fetch all permissions where panel = 'store'
        $storePermissions = Permission::where('panel', 'store')->pluck('name')->toArray();

        // Default Store Owner gets all store-level permissions
        $storeManagerPermissions = Permission::where('panel', 'store')
            ->whereNotIn('group_name', ['Store Role Management']) // Example: restrict role changes for managers
            ->pluck('name')
            ->toArray();

        $storeMatrix = [
            'Store Owner'   => $storePermissions,      // Only store permissions
            'Store Manager' => $storeManagerPermissions, // Only store permissions
        ];

        foreach ($storeMatrix as $roleName => $permissions) {
            // Note: In multi-tenancy, tenant-specific roles are usually created
            // dynamically when a store is registered, but you can seed default
            // templates here if needed.
            $role = Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web',
                'tenant_id' => 1
            ]);
            $role->syncPermissions($permissions);
        }
    }
}
