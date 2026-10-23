<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    public const GROUPS = [
        'Platform Admin Dashboard' => [
            'Platform Admin Dashboard',
        ],
        'Platform Admin Management' => [
            'Platform Admin List',
            'Platform Create Admin',
            'Platform Edit Admin',
            'Platform Delete Admin',
            'Platform Status Admin',
        ],
        'Platform Role Management' => [
            'Platform Role List',
            'PlatformRole Create',
            'Platform Edit Role',
            'Delete Role',
            'Platform Assign Permission',
        ],
        'Platform Permission Management' => [
            'Platform Permission List',
            'Platform Create Permission',
            'Platform Edit Permission',
            'Platform Delete Permission',
        ],
        'Platform Settings' => [
            'Platform General Setting',
            'Platform Edit Setting',
        ],
        'Platform Landing Page CMS' => [
            'Platform Manage Hero',
            'Platform Manage Features',
            'Platform Manage Steps',
            'Platform Manage Use Cases',
            'Platform Manage Store Demos',
            'Platform Manage Testimonials',
            'Platform Manage Faqs',
            'Platform Manage Matrix',
        ],
        'Platform Plans & Billing' => [
            'Platform Manage Plans',
            'Platform Manage Subscriptions',
            'Platform Manage Invoices',
        ],
        'Platform Tenant Operations' => [
            'Platform Manage Domains',
            'Platform Manage Usages',
        ],
        'PlatformContent & Support' => [
            'Platform Manage Pages',
            'Platform Manage Blog',
            'Platform Manage Blog Categories',
            'Platform Manage Contacts',
            'Platform Manage Newsletter',
        ],
    ];

    /** @var array<string, array<int, string>> */
    public const STOREFRONT_GROUPS = [
        'Store Admin Dashboard' => [
            'Store Dashboard',
        ],
        'Store Role Management' => [
            'Store Role List',
            'Store Role Create',
            'Store Edit Role',
            'Store Delete Role',
            'Store Assign Permission',
        ],
        'Store Admin Management' => [
            'Store Admin List',
            'Store Create Admin',
            'Store Edit Admin',
            'Store Delete Admin',
            'Store Status Admin',
        ],
        'Store Permission Management' => [
            'Store Permission List',
        ],
        'Store Catalog Management' => [
            'Store Category List', 'Store Category Create', 'Store Category Edit', 'Store Category Delete',
            'Store Brand List', 'Store Brand Create', 'Store Brand Edit', 'Store Brand Delete',
            'Store Attribute List', 'Store Attribute Create', 'Store Attribute Edit', 'Store Attribute Delete',
            'Store Product List', 'Store Product Create', 'Store Product Edit', 'Store Product Delete',
            'Store Inventory List', 'Store Inventory Manage',
        ],
        'Store Settings' => [
            'Store General Setting',
            'Store Edit Setting',
        ],
    ];

    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Seed Platform-level permissions
        foreach (self::GROUPS as $groupName => $permissions) {
            foreach ($permissions as $permissionName) {
                Permission::updateOrCreate(
                    [
                        'name' => $permissionName,
                        'guard_name' => 'web',
                    ],
                    [
                        'group_name' => $groupName,
                        'panel' => 'platform', // Assigned to SaaS platform panel
                    ]
                );
            }
        }

        // 2. Seed Store-level permissions
        foreach (self::STOREFRONT_GROUPS as $groupName => $permissions) {
            foreach ($permissions as $permissionName) {
                Permission::updateOrCreate(
                    [
                        'name' => $permissionName,
                        'guard_name' => 'web',
                    ],
                    [
                        'group_name' => $groupName,
                        'panel' => 'store', // Assigned to merchant store panel
                    ]
                );
            }
        }
    }
}
