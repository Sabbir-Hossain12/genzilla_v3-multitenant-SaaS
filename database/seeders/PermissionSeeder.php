<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    /**
     * Define the permission hierarchy with explicit groups for the UI
     */
    public const GROUPS = [
        'Platform Dashboard' => [
            'Platform Dashboard',
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
            'Platform Create Role',
            'Platform Edit Role',
            'Platform Delete Role',
            'Platform Assign Permission',
        ],
        'Platform Permission Management' => [
            'Platform Permission List',
        ],
        'Platform Tenant Management' => [
            'Platform Tenant List',
            'Platform Create Tenant',
            'Platform Edit Tenant',
            'Platform Delete Tenant',
            'Platform Status Tenant',
            'Platform Impersonate Tenant',
        ],
        'Platform Subscription Management' => [
            'Platform Plan List',
            'Platform Create Plan',
            'Platform Edit Plan',
            'Platform Delete Plan',
            'Platform Assign Plan',
            'Platform Manage Domains',
        ],
        'Platform General Setting' => [
            'Platform General Setting',
            'Platform Edit Setting',
        ],
        'Platform Dynamic Page Management' => [
            'Platform Page List',
            'Platform Create Page',
            'Platform Edit Page',
            'Platform Delete Page',
        ],
    ];

    /**
     * Store-level permissions assigned to store merchants & employees
     */
    public const STOREFRONT_GROUPS = [
        'Store Dashboard' => [
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
        'Store Customer Management' => [
            'Store Customer List',
            'Store Customer Create',
            'Store Customer View',
            'Store Customer Edit',
            'Store Customer Delete',
        ],
        'Store Order Management' => [
            'Store Order List',
            'Store Order View',
            'Store Order Create',
            'Store Order Edit',
            'Store Order Delete',
        ],
        'Store Promotions' => [
            'Store Coupon List',
            'Store Coupon Create',
            'Store Coupon Edit',
            'Store Coupon Delete',
            'Store Delivery Charge List',
            'Store Delivery Charge Create',
            'Store Delivery Charge Edit',
            'Store Delivery Charge Delete',
        ],
        'Store Content Management' => [
            'Store Page List', 'Store Page Create', 'Store Page Edit', 'Store Page Delete',
            'Store Slider List', 'Store Slider Create', 'Store Slider Edit', 'Store Slider Delete',
            'Store Banner List', 'Store Banner Create', 'Store Banner Edit', 'Store Banner Delete',
        ],
        'Store Payment Methods' => [
            'Store Payment Method List',
            'Store Payment Method Edit',
        ],
        'Store Domain Management' => [
            'Store Domain List',
            'Store Domain Create',
            'Store Domain Edit',
            'Store Domain Delete',
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
