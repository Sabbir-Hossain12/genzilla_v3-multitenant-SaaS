<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Tenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TenantBrandSeeder extends Seeder
{
    /**
     * Brands referenced in storefront dummy items.
     */
    public const BRANDS = [
        ['name' => 'Skin1004', 'slug' => 'skin1004'],
        ['name' => 'Minimalist', 'slug' => 'minimalist'],
        ['name' => 'CeraVe', 'slug' => 'cerave'],
        ['name' => 'Square Pharma', 'slug' => 'square-pharma'],
        ['name' => 'Healath', 'slug' => 'healath'],
        ['name' => "L'Oréal", 'slug' => 'loreal'],
        ['name' => "Nature's Way", 'slug' => 'natures-way'],
        ['name' => 'Renata', 'slug' => 'renata'],
        ['name' => 'Pigeon', 'slug' => 'pigeon'],
        ['name' => 'Omron', 'slug' => 'omron'],
        ['name' => 'Dettol', 'slug' => 'dettol'],
        ['name' => 'Himalaya', 'slug' => 'himalaya'],
    ];

    /**
     * Run the tenant brands seeder.
     */
    public function run(): void
    {
        Tenant::query()->orderBy('id')->chunkById(100, function ($tenants): void {
            foreach ($tenants as $tenant) {
                DB::transaction(function () use ($tenant): void {
                    $this->seedBrands($tenant);
                });
            }
        });
    }

    /**
     * Seed brands for a given tenant.
     * Returns a map of brand names to Brand models.
     */
    public function seedBrands(Tenant $tenant): array
    {
        $brandMap = [];
        foreach (self::BRANDS as $brandData) {
            $brand = Brand::firstOrCreate(
                ['tenant_id' => $tenant->id, 'slug' => $brandData['slug']],
                [
                    'name' => $brandData['name'],
                    'status' => true,
                    'meta_title' => $brandData['name'] . ' Products',
                    'h1_heading' => $brandData['name'],
                    'meta_description' => sprintf('Authentic products manufactured and distributed by %s.', $brandData['name']),
                ]
            );
            $brandMap[$brandData['name']] = $brand;
        }

        return $brandMap;
    }
}
