<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\InventoryBatch;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use App\Models\Tenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TenantCatalogSeeder extends Seeder
{
    /**
     * Seed catalog defaults for every tenant. Empty catalogs receive a private,
     * starter product so the merchant can inspect catalog and inventory
     * screens.
     */
    public function run(): void
    {
        Tenant::query()->orderBy('id')->chunkById(100, function ($tenants): void {
            foreach ($tenants as $tenant) {
                DB::transaction(function () use ($tenant): void {
                    $tenant->initializeCatalogDefaults();

                    // Existing product data belongs to the merchant; only add the
                    // example catalog for tenants whose catalog is still empty.
                    if ($tenant->products()->withTrashed()->exists()) {
                        return;
                    }

                    $this->seedStarterCatalog($tenant);
                });
            }
        });
    }

    private function seedStarterCatalog(Tenant $tenant): void
    {
        $rootCategory = Category::firstOrCreate(
            ['tenant_id' => $tenant->id, 'slug' => 'starter-catalog'],
            [
                'parent_id' => null,
                'name' => 'Starter Catalog',
                'position' => 0,
                'status' => true,
                'meta_title' => 'Starter Catalog',
                'h1_heading' => 'Starter Catalog',
                'meta_description' => 'Explore the starter catalog selection.',
            ]
        );

        $category = Category::firstOrCreate(
            ['tenant_id' => $tenant->id, 'slug' => 'everyday-essentials'],
            [
                'parent_id' => $rootCategory->id,
                'name' => 'Everyday Essentials',
                'position' => 0,
                'status' => true,
                'meta_title' => 'Everyday Essentials',
                'h1_heading' => 'Everyday Essentials',
                'meta_description' => 'Essential daily products for wellness.',
            ]
        );

        $brand = Brand::firstOrCreate(
            ['tenant_id' => $tenant->id, 'slug' => 'store-essentials'],
            [
                'name' => 'Store Essentials',
                'status' => true,
                'meta_title' => 'Store Essentials Brand',
                'h1_heading' => 'Store Essentials',
                'meta_description' => 'Trusted products from Store Essentials.',
            ]
        );

        $size = ProductAttribute::firstOrCreate(
            ['tenant_id' => $tenant->id, 'slug' => 'size'],
            ['name' => 'Size', 'position' => 0]
        );

        $product = Product::create([
            'tenant_id' => $tenant->id,
            'kind' => 'general',
            'name' => 'Daily Essentials Kit',
            'slug' => 'daily-essentials-kit',
            'description' => 'A starter product you can edit or replace with your own catalog item.',
            'status' => 'active',
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'requires_prescription' => false,
            'specs' => ['seeded' => true],
            'seo_title' => 'Daily Essentials Kit',
            'seo_description' => 'Starter catalog example. Edit before publishing.',
        ]);

        foreach ([
            ['value' => 'Small', 'position' => 0, 'price' => 19.99, 'cost' => 8.00, 'stock' => 10],
            ['value' => 'Medium', 'position' => 1, 'price' => 24.99, 'cost' => 10.00, 'stock' => 10],
            ['value' => 'Large', 'position' => 2, 'price' => 29.99, 'cost' => 12.00, 'stock' => 10],
        ] as $variantData) {
            $valueSlug = Str::slug($variantData['value']);
            $attributeValue = ProductAttributeValue::firstOrCreate(
                ['tenant_id' => $tenant->id, 'attribute_id' => $size->id, 'slug' => $valueSlug],
                ['value' => $variantData['value'], 'position' => $variantData['position'], 'status' => true]
            );

            $variant = ProductVariant::firstOrCreate(
                ['tenant_id' => $tenant->id, 'sku' => sprintf('STARTER-%d-%s', $tenant->id, strtoupper($valueSlug))],
                [
                    'product_id' => $product->id,
                    'title' => $variantData['value'],
                    'price' => $variantData['price'],
                    'compare_at_price' => null,
                    'mrp' => null,
                    'cost_price' => $variantData['cost'],
                    'weight_grams' => null,
                    'track_inventory' => true,
                    'is_default' => $variantData['position'] === 0,
                    'position' => $variantData['position'],
                    'status' => 'active',
                    'options_hash' => md5((string) $attributeValue->id),
                ]
            );

            DB::table('product_variant_attribute_values')->updateOrInsert(
                ['variant_id' => $variant->id, 'attribute_id' => $size->id],
                ['tenant_id' => $tenant->id, 'attribute_value_id' => $attributeValue->id]
            );

            $batch = InventoryBatch::firstOrCreate(
                ['variant_id' => $variant->id, 'batch_no' => sprintf('SEED-%d-%s', $tenant->id, strtoupper($valueSlug))],
                [
                    'tenant_id' => $tenant->id,
                    'expiry_date' => null,
                    'quantity' => $variantData['stock'],
                    'reserved' => 0,
                    'cost_price' => $variantData['cost'],
                    'received_at' => now(),
                    'status' => 'active',
                ]
            );

            StockMovement::firstOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'variant_id' => $variant->id,
                    'batch_id' => $batch->id,
                    'type' => 'receive',
                    'note' => 'Opening stock from starter catalog seeder',
                ],
                [
                    'qty_delta' => $variantData['stock'],
                    'reserved_delta' => 0,
                    'reference_type' => 'seeder',
                    'reference_id' => $product->id,
                    'user_id' => $tenant->user_id,
                    'created_at' => now(),
                ]
            );
        }
    }
}
