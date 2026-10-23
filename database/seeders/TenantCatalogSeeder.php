<?php

namespace Database\Seeders;

use App\Models\Tenant;
use Illuminate\Database\Seeder;

class TenantCatalogSeeder extends Seeder
{
    public function run(): void
    {
        Tenant::query()->each(fn(Tenant $tenant) => $tenant->initializeCatalogDefaults());
    }
}
