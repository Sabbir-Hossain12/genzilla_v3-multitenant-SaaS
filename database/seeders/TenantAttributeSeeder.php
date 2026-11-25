<?php

namespace Database\Seeders;

use App\Models\Tenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TenantAttributeSeeder extends Seeder
{
    /**
     * Run the tenant attribute defaults seeder.
     */
    public function run(): void
    {
        Tenant::query()->orderBy('id')->chunkById(100, function ($tenants): void {
            foreach ($tenants as $tenant) {
                DB::transaction(function () use ($tenant): void {
                    $tenant->initializeCatalogDefaults();
                });
            }
        });
    }
}
