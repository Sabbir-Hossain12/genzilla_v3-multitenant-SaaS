<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles=['Platform SuperAdmin','Platform Coadmin','Platform Staff'];

        foreach($roles as $role){
            Role::firstOrCreate([
                'name'=>$role,
                'guard_name'=>'web'
            ]);
        }

    }
}
