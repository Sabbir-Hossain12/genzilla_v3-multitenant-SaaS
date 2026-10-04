<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Super Admin',
                'password' => 'password',
            ],
        );

        $admin->assignRole('Platform SuperAdmin');

        $allPermissions = Permission::all();

        // syncPermissions replaces any old permissions with the full list
        $admin->syncPermissions($allPermissions);
    }
}
