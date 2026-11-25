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
        ['name' => 'Incepta', 'slug' => 'incepta', 'image' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQld8lSUT7PhzW8jLC3M0Gm_XRIWTz9BwSmo5yEJcJDUTGJwFYfZ171A1Q&s=10'],
        ['name' => 'Eskayef Pharma', 'slug' => 'eskayef-pharma', 'image' => 'https://thefe-bd.sgp1.cdn.digitaloceanspaces.com/posts/51667/join-eskayef-pharmaceuticals-ltd.jpg'],
        ['name' => 'CeraVe', 'slug' => 'cerave' , 'image' => 'https://cdn.kireibd.com/storage/all/CeraVe-Intensive-Moisturizing-Cream-340gnormal-8GWRJu0RXSpWykyrjCYo0UQ75iburF.jpg'],
        ['name' => 'Square Pharma', 'slug' => 'square-pharma', 'image' => 'https://thebangladeshtoday.com/wp-content/uploads/2025/01/square.png'],
        ['name' => 'Beximco', 'slug' => 'beximco', 'image' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQ8_bxp1KcVfu81jb6L4wzCo3x_NGSfmkMKYF2ajstX_OJfzNa9foHL-ZDK&s=10'],
        ['name' => "L'Oréal", 'slug' => 'loreal', 'image' => 'https://a.storyblok.com/f/182663/2000x1125/78a55f7715/loreal-from-paris-to-global-dominance.png/m/3840x0/filters:quality(85)'],
        ['name' => "Nature's Way", 'slug' => 'natures-way', 'image' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRsKuC4DcPA7avmV7FhkfGlo9RrIctq_RJs01hW5uXgoA&s'],
        ['name' => 'Renata', 'slug' => 'renata', 'image' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRd6FBdzyZ5HZJyLCqkeacV-bLOpvalWqircAwDpX4E1w&s=10'],
        ['name' => 'Pigeon', 'slug' => 'pigeon', 'image' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSXmD1f5AWNLWYZ4lboE_RcR4qv5jY5NMGGEbRskd5zgeKAMS0qTxv11jw&s=10'],
        ['name' => 'Omron', 'slug' => 'omron', 'image' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSb0RBkjDPuhPRpQksrpzBIQbBpNhEGjkU95ZUbOcAH3wi5zht_1_ZIGOU&s=10'],
        ['name' => 'Dettol', 'slug' => 'dettol', 'image' => 'https://mikipetstore.com/cdn/shop/collections/himalaya_logo.jpg?v=1757267191'],
        ['name' => 'Himalaya', 'slug' => 'himalaya', 'image' => 'https://cdn.kireibd.com/storage/all/CeraVe-Intensive-Moisturizing-Cream-340gnormal-8GWRJu0RXSpWykyrjCYo0UQ75iburF.jpg'],
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
                    'image' => $brandData['image'],
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
