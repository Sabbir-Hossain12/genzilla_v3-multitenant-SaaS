<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $all = collect(PermissionSeeder::GROUPS)->flatten()->unique()->values()->all();

        $security = array_merge(
            PermissionSeeder::GROUPS['Admin Management'],
            PermissionSeeder::GROUPS['Role Management'],
            PermissionSeeder::GROUPS['Permission Management'],
        );

        $coadmin = array_values(array_diff($all, $security));

        $staff = array_values(array_unique(array_merge(
            PermissionSeeder::GROUPS['Platform Admin Dashboard'],
            PermissionSeeder::GROUPS['Content & Support'],
        )));

        $matrix = [
            'Platform SuperAdmin' => $all,
            'Platform Coadmin' => $coadmin,
            'Platform Staff' => $staff,
        ];

        foreach ($matrix as $roleName => $permissions) {
            $role = Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web',
            ]);

            $role->syncPermissions($permissions);
        }
    }
}
