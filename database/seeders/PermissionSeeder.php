<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{

    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $table_rows = array(
            [
                'group_name' => 'Platform Admin Dashboard',
                'permissions' => [
                    'Platform Admin Dashboard',
                ]
            ],

            [
                'group_name' => 'Role Management',
                'permissions' => [
                    'Role List',
                    'Role Create',
                    'Edit Role',
                    'Delete Role',
                    'Assign Role',
                    'Assign Permission',
                ]
            ],
            [
                'group_name' => 'Settings',
                'permissions' => [
                    'General Setting',
                    'Create Setting',
                    'Edit Setting',
                ]
            ],

        );

        foreach ($table_rows as $iValue) {
            $group_name = $iValue['group_name'];

            foreach ($iValue['permissions'] as $permission_name) {
                Permission::firstOrCreate([
                    'name' => $permission_name,
                    'guard_name' => 'web'
                ], [
                    'group_name' => $group_name
                ]);
            }
        }
    }
}
