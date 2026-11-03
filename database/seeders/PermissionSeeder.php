<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    /**
     * Define Platform-Level Permissions
     */
    public const GROUPS = [
        'Platform Admin Dashboard' => [
            'Platform View Dashboard',
        ],
        'Tenant Management' => [
            'Tenant List',
            'Tenant Create',
            'Tenant View',
            'Tenant Edit',
            'Tenant Delete',
            'Tenant Impersonate',
        ],
        'Plan & Subscription Management' => [
            'Plan List',
            'Plan Create',
            'Plan Edit',
            'Plan Delete',
            'Subscription List',
            'Subscription Edit',
            'Subscription Cancel',
        ],
        'Domain Management' => [
            'Platform Domain List',
            'Platform Domain Verify',
            'Platform Domain Delete',
        ],
        'Billing & Invoices' => [
            'Platform Invoice List',
            'Platform Invoice View',
            'Platform Payment Gateway Settings',
        ],
        'Content & Support' => [
            'Landing Page CMS',
            'Blog Post Management',
            'Contact Inquiries View',
            'Newsletter Subscribers Export',
        ],
        'Platform Settings & Security' => [
            'Platform General Settings',
            'Admin List',
            'Create Admin',
            'Edit Admin',
            'Delete Admin',
            'Status Admin',
            'Role List',
            'Role Create',
            'Edit Role',
            'Delete Role',
            'Assign Permission',
            'Permission List',
            'Create Permission',
            'Edit Permission',
            'Delete Permission',
        ],
    ];

    /**
     * Define Merchant Store-Level Permissions
     */
    public const STOREFRONT_GROUPS = [
        'Store Dashboard' => [
            'Store Dashboard',
        ],
        'Store Admin Management' => [
            'Store Admin List',
            'Store Create Admin',
            'Store Edit Admin',
            'Store Delete Admin',
            'Store Status Admin',
        ],
        'Store Role Management' => [
            'Store Role List',
            'Store Role Create',
            'Store Edit Role',
            'Store Delete Role',
            'Store Assign Permission',
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
        'Store Abandoned Checkout Management' => [
            'Store Abandoned Checkout List',
            'Store Abandoned Checkout View',
            'Store Abandoned Checkout Contact',
            'Store Abandoned Checkout Delete',
        ],
        'Store Transaction Management' => [
            'Store Transaction List',
            'Store Transaction View',
            'Store Transaction Create',
            'Store Transaction Refund',
            'Store Transaction Export',
        ],
        'Store Prescription Management' => [
            'Store Prescription List',
            'Store Prescription View',
            'Store Prescription Convert',
            'Store Prescription Reject',
            'Store Prescription Delete',
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
        'Store Subscription & Billing' => [
            'Store Subscription View',
            'Store Subscription Change Plan',
            'Store Subscription Cancel',
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
