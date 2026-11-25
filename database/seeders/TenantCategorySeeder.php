<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Media;
use App\Models\Tenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TenantCategorySeeder extends Seeder
{
    /**
     * Categories taxonomy synced with storefront dummy content (resources/js/data/store/index.js).
     */
    public const CATEGORY_TAXONOMY = [
        [
            'name' => 'Medicine',
            'image' => 'https://www.arogga.com/_next/image?url=https%3A%2F%2Fcdn2.arogga.com%2FeyJidWNrZXQiOiJhcm9nZ2EiLCJrZXkiOiJCbG9jay1iX2NvbmZpZ1wvMVwvMTA1OS1tZWRpLXJ2NTZoMS5wbmciLCJlZGl0cyI6W119&w=1280&q=75',
            'children' => [
                'Prescription Medicine',
                'OTC Medicine',
                'Diabetic Care',
                'Cardiac Care',
                'Dermatological',
                'Insulin',
            ],
        ],
        [
            'name' => 'Beauty',
            'image' => 'https://www.arogga.com/_next/image?url=https%3A%2F%2Fcdn2.arogga.com%2FeyJidWNrZXQiOiJhcm9nZ2EiLCJrZXkiOiJCbG9jay1iX2NvbmZpZ1wvMVwvMTA1OS1CZWF1dHktZHhjbTg3LnBuZyIsImVkaXRzIjpbXX0%3D&w=1280&q=75',
            'children' => [
                'Skincare',
                'Haircare',
                'Makeup',
                'Fragrance',
                "Men's Grooming",
            ],
        ],
        [
            'name' => 'Healthcare',
            'image' => 'https://www.arogga.com/_next/image?url=https%3A%2F%2Fcdn2.arogga.com%2FeyJidWNrZXQiOiJhcm9nZ2EiLCJrZXkiOiJCbG9jay1iX2NvbmZpZ1wvMVwvMTA1OS1BbGwteW91LW5lZWQtbG9nb3MtMy11NmJ3M2UucG5nIiwiZWRpdHMiOltdfQ%3D%3D&w=1280&q=75',
            'children' => [
                'Medical Devices',
                'Diagnostic Kits',
                'Mobility Aids',
                'Personal Protection',
                'Surgical Items',
            ],
        ],
        [
            'name' => 'Lab Test',
            'image' => 'https://www.arogga.com/_next/image?url=https%3A%2F%2Fcdn2.arogga.com%2FeyJidWNrZXQiOiJhcm9nZ2EiLCJrZXkiOiJCbG9jay1iX2NvbmZpZ1wvMVwvMTA1OS1BbGwteW91LW5lZWQtbG9nb3MtMi11NTR3djQucG5nIiwiZWRpdHMiOltdfQ%3D%3D&w=1280&q=75',
            'children' => [
                'Vital Organs',
                'Life Style Packages',
                'Checkup for Women',
                'Checkup for Men',
                'Full Body Checkup',
            ],
        ],
        [
            'name' => 'Baby & Mom',
            'image' => 'https://www.arogga.com/_next/image?url=https%3A%2F%2Fcdn2.arogga.com%2FeyJidWNrZXQiOiJhcm9nZ2EiLCJrZXkiOiJCbG9jay1iX2NvbmZpZ1wvMFwvOTEyLVVudGl0bGVkLWRlc2lnbi0yOS01Y2drcjgucG5nIiwiZWRpdHMiOltdfQ%3D%3D&w=1280&q=75',
            'children' => [
                'Baby Food',
                'Diapers & Wipes',
                'Maternal Care',
                'Baby Skincare',
                'Feeding Essentials',
            ],
        ],
        [
            'name' => 'Herbal',
            'image' => 'https://www.arogga.com/_next/image?url=https%3A%2F%2Fcdn2.arogga.com%2FeyJidWNrZXQiOiJhcm9nZ2EiLCJrZXkiOiJCbG9jay1iX2NvbmZpZ1wvMFwvOTEyLVVudGl0bGVkLWRlc2lnbi0zMS03Z28wMTkucG5nIiwiZWRpdHMiOltdfQ%3D%3D&w=1280&q=75',
            'children' => [
                'Ayurvedic',
                'Unani',
                'Herbal Supplements',
                'Herbal Skincare',
            ],
        ],
        [
            'name' => 'Pet Care',
            'image' => 'https://www.arogga.com/_next/image?url=https%3A%2F%2Fcdn2.arogga.com%2FeyJidWNrZXQiOiJhcm9nZ2EiLCJrZXkiOiJCbG9jay1iX2NvbmZpZ1wvMVwvMTA1OS1wZXQtZmJ3aGp1LnBuZyIsImVkaXRzIjpbXX0%3D&w=1280&q=75',
            'children' => [
                'Dog Care',
                'Cat Care',
                'Pet Food',
                'Pet Grooming',
                'Veterinary',
            ],
        ],
        [
            'name' => 'Supplement',
            'image' => 'https://www.arogga.com/_next/image?url=https%3A%2F%2Fcdn2.arogga.com%2FeyJidWNrZXQiOiJhcm9nZ2EiLCJrZXkiOiJCbG9jay1iX2NvbmZpZ1wvMVwvMTA1OS1zdXBwbC01MzFzNXAucG5nIiwiZWRpdHMiOltdfQ%3D%3D&w=1280&q=75',
            'children' => [
                'Multivitamins',
                'Protein & Fitness',
                'Weight Management',
                'Immunity Boosters',
            ],
        ],
        [
            'name' => 'Home Care',
            'image' => 'https://www.arogga.com/_next/image?url=https%3A%2F%2Fcdn2.arogga.com%2FeyJidWNrZXQiOiJhcm9nZ2EiLCJrZXkiOiJCbG9jay1iX2NvbmZpZ1wvMVwvMTA1OS1Ib21lY2FyZS00d2M0d3MucG5nIiwiZWRpdHMiOltdfQ%3D%3D&w=1280&q=75',
            'children' => [
                'Disinfectants',
                'Sanitizers',
                'Household Wellness',
                'Air Fresheners',
            ],
        ],
        [
            'name' => 'Homeopathy',
            'image' => 'https://www.arogga.com/_next/image?url=https%3A%2F%2Fcdn2.arogga.com%2FeyJidWNrZXQiOiJhcm9nZ2EiLCJrZXkiOiJCbG9jay1iX2NvbmZpZ1wvMVwvMTA1OS1Ib21lby1oOHE3Y2cucG5nIiwiZWRpdHMiOltdfQ%3D%3D&w=1280&q=75',
            'children' => [],
        ],
        [
            'name' => 'Sexual Wellness',
            'image' => 'https://www.arogga.com/_next/image?url=https%3A%2F%2Fcdn2.arogga.com%2FeyJidWNrZXQiOiJhcm9nZ2EiLCJrZXkiOiJCbG9jay1iX2NvbmZpZ1wvMVwvMTA1OS13ZWxsLW5lYm9zMi5wbmciLCJlZGl0cyI6W119&w=1280&q=75',
            'children' => [],
        ],
        [
            'name' => 'Feminine Care',
            'image' => 'https://www.arogga.com/_next/image?url=https%3A%2F%2Fcdn2.arogga.com%2FeyJidWNrZXQiOiJhcm9nZ2EiLCJrZXkiOiJCbG9jay1iX2NvbmZpZ1wvMVwvMTA1OS1GZW1pbmllLUNhcmUtZm5uaXNuLnBuZyIsImVkaXRzIjpbXX0%3D&w=1280&q=75',
            'children' => [],
        ],


    ];

    /**
     * Subcategory specific image overrides matching storefront category tiles.
     */
    public const SUBCATEGORY_IMAGES = [
        'skincare' => 'https://www.arogga.com/_next/image?url=https%3A%2F%2Fcdn2.arogga.com%2FeyJidWNrZXQiOiJhcm9nZ2EiLCJrZXkiOiJCbG9jay1iX2NvbmZpZ1wvMVwvMTA1OS1Ta2luLUNhcmUtbnZiZXoyLnBuZyIsImVkaXRzIjpbXX0%3D&w=1280&q=75',
        'haircare' => 'https://www.arogga.com/_next/image?url=https%3A%2F%2Fcdn2.arogga.com%2FeyJidWNrZXQiOiJhcm9nZ2EiLCJrZXkiOiJCbG9jay1iX2NvbmZpZ1wvMVwvMTA1OS1zdXBwbC01MzFzNXAucG5nIiwiZWRpdHMiOltdfQ%3D%3D&w=1280&q=75',
        'medical-devices' => 'https://www.arogga.com/_next/image?url=https%3A%2F%2Fcdn2.arogga.com%2FeyJidWNrZXQiOiJhcm9nZ2EiLCJrZXkiOiJCbG9jay1iX2NvbmZpZ1wvMVwvMTA1OS1BbGwteW91LW5lZWQtbG9nb3MtbGU4aWt2LnBuZyIsImVkaXRzIjpbXX0%3D&w=1280&q=75',
    ];

    /**
     * Run the tenant categories seeder.
     */
    public function run(): void
    {
        Tenant::query()->orderBy('id')->chunkById(100, function ($tenants): void {
            foreach ($tenants as $tenant) {
                DB::transaction(function () use ($tenant): void {
                    $this->seedCategories($tenant);
                    $tenant->syncUsage();
                });
            }
        });
    }

    /**
     * Seed categories and subcategories for a given tenant.
     * Returns a map of category names to Category models.
     */
    public function seedCategories(Tenant $tenant): array
    {
        $categoryMap = [];
        $rootPos = 0;

        foreach (self::CATEGORY_TAXONOMY as $rootItem) {
            $rootSlug = Str::slug($rootItem['name']);
            $rootCategory = Category::firstOrCreate(
                ['tenant_id' => $tenant->id, 'slug' => $rootSlug],
                [
                    'parent_id' => null,
                    'name' => $rootItem['name'],
                    'image' => $rootItem['image'],
                    'position' => $rootPos++,
                    'status' => true,
                    'meta_title' => $rootItem['name'],
                    'h1_heading' => $rootItem['name'],
                    'meta_description' => sprintf('Explore authentic %s products online.', $rootItem['name']),
                ]
            );

            // Ensure root category has parent_id = null and correct image
            $rootUpdates = [];
            if ($rootCategory->parent_id !== null) {
                $rootUpdates['parent_id'] = null;
            }
            if (empty($rootCategory->image) && !empty($rootItem['image'])) {
                $rootUpdates['image'] = $rootItem['image'];
            }
            if (!empty($rootUpdates)) {
                $rootCategory->update($rootUpdates);
            }

            // Track root category image in media table
            $rootImage = $rootCategory->image ?? $rootItem['image'];
            if (!empty($rootImage)) {
                Media::firstOrCreate(
                    [
                        'tenant_id' => $tenant->id,
                        'model_type' => Category::class,
                        'model_id' => $rootCategory->id,
                        'collection' => 'cover',
                    ],
                    [
                        'path' => $rootImage,
                        'mime' => 'image/webp',
                        'size_bytes' => 0, // Remote dummy CDN asset URL does not count towards local tenant quota
                        'position' => 0,
                        'alt' => $rootCategory->name,
                        'created_at' => now(),
                    ]
                );
            }

            $categoryMap[$rootItem['name']] = $rootCategory;

            $childPos = 0;
            foreach ($rootItem['children'] as $childName) {
                $childSlug = Str::slug($childName);
                $childImage = self::SUBCATEGORY_IMAGES[$childSlug] ?? $rootItem['image'];

                $childCategory = Category::firstOrCreate(
                    ['tenant_id' => $tenant->id, 'slug' => $childSlug],
                    [
                        'parent_id' => $rootCategory->id,
                        'name' => $childName,
                        'image' => $childImage,
                        'position' => $childPos++,
                        'status' => true,
                        'meta_title' => sprintf('%s - %s', $childName, $rootItem['name']),
                        'h1_heading' => $childName,
                        'meta_description' => sprintf('Shop high quality %s under %s.', $childName, $rootItem['name']),
                    ]
                );

                if (empty($childCategory->parent_id)) {
                    $childCategory->update(['parent_id' => $rootCategory->id]);
                }
                if (empty($childCategory->image) && !empty($childImage)) {
                    $childCategory->update(['image' => $childImage]);
                }

                // Track subcategory image in media table
                $childFinalImage = $childCategory->image ?? $childImage;
                if (!empty($childFinalImage)) {
                    Media::firstOrCreate(
                        [
                            'tenant_id' => $tenant->id,
                            'model_type' => Category::class,
                            'model_id' => $childCategory->id,
                            'collection' => 'cover',
                        ],
                        [
                            'path' => $childFinalImage,
                            'mime' => 'image/webp',
                            'size_bytes' => 0, // Remote dummy CDN asset URL does not count towards local tenant quota
                            'position' => 0,
                            'alt' => $childCategory->name,
                            'created_at' => now(),
                        ]
                    );
                }

                $categoryMap[$childName] = $childCategory;
            }
        }

        return $categoryMap;
    }
}
