<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{

    public function run(): void
    {
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
                    'Assign Permission', //12
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

        foreach ($table_rows as $i => $iValue) {
            $group_name = $iValue['group_name'];

            foreach ($iValue['permissions'] as $j => $jValue) {
                Permission::create([
                    'name' => $iValue['permissions'][$j],
                    'group_name' => $group_name
                ]);
            }
        }
    }
}
