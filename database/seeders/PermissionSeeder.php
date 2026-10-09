<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    /**
     * Platform permission groups. Tenant-scoped permissions are intentionally
     * out of scope for now (see config/permission.php teams => true).
     *
     * @var array<string, array<int, string>>
     */
    public const GROUPS = [
        'Platform Admin Dashboard' => [
            'Platform Admin Dashboard',
        ],
        'Admin Management' => [
            'Admin List',
            'Create Admin',
            'Edit Admin',
            'Delete Admin',
            'Status Admin',
        ],
        'Role Management' => [
            'Role List',
            'Role Create',
            'Edit Role',
            'Delete Role',
            'Assign Permission',
        ],
        'Permission Management' => [
            'Permission List',
            'Create Permission',
            'Edit Permission',
            'Delete Permission',
        ],
        'Settings' => [
            'General Setting',
            'Edit Setting',
        ],
        'Landing Page CMS' => [
            'Manage Hero',
            'Manage Features',
            'Manage Steps',
            'Manage Use Cases',
            'Manage Store Demos',
            'Manage Testimonials',
            'Manage Faqs',
            'Manage Matrix',
        ],
        'Plans & Billing' => [
            'Manage Plans',
            'Manage Subscriptions',
            'Manage Invoices',
        ],
        'Tenant Operations' => [
            'Manage Domains',
            'Manage Usages',
        ],
        'Content & Support' => [
            'Manage Pages',
            'Manage Blog',
            'Manage Blog Categories',
            'Manage Contacts',
            'Manage Newsletter',
        ],
    ];

    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        foreach (self::GROUPS as $groupName => $permissions) {
            foreach ($permissions as $permissionName) {
                Permission::firstOrCreate(
                    [
                        'name' => $permissionName,
                        'guard_name' => 'web',
                    ],
                    [
                        'group_name' => $groupName,
                    ]
                );
            }
        }
    }
}
