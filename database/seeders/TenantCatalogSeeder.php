<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\InventoryBatch;
use App\Models\Media;
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
     * Categories taxonomy synced with storefront dummy content (resources/js/data/store/index.js).
     */
    public const CATEGORY_TAXONOMY = [
        [
            'name' => 'Medicine',
            'image' => 'https://www.arogga.com/_next/image?url=https%3A%2F%2Fcdn2.arogga.com%2FeyJidWNrZXQiOiJhcm9nZ2EiLCJrZXkiOiJCbG9jay1iX2NvbmZpZ1wvMVwvMTA1OS1zdXBwbC01MzFzNXAucG5nIiwiZWRpdHMiOltdfQ%3D%3D&w=1280&q=75',
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
            'image' => 'https://www.arogga.com/_next/image?url=https%3A%2F%2Fcdn2.arogga.com%2FeyJidWNrZXQiOiJhcm9nZ2EiLCJrZXkiOiJCbG9jay1iX2NvbmZpZ1wvMVwvMTA1OS1zdXBwbC01MzFzNXAucG5nIiwiZWRpdHMiOltdfQ%3D%3D&w=1280&q=75',
            'children' => [
                'Skincare',
                'Haircare',
                'Makeup',
                'Feminine Care',
                'Fragrance',
                "Men's Grooming",
            ],
        ],
        [
            'name' => 'Healthcare',
            'image' => 'https://www.arogga.com/_next/image?url=https%3A%2F%2Fcdn2.arogga.com%2FeyJidWNrZXQiOiJhcm9nZ2EiLCJrZXkiOiJCbG9jay1iX2NvbmZpZ1wvMVwvMTA1OS1zdXBwbC01MzFzNXAucG5nIiwiZWRpdHMiOltdfQ%3D%3D&w=1280&q=75',
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
            'image' => 'https://www.arogga.com/_next/image?url=https%3A%2F%2Fcdn2.arogga.com%2FeyJidWNrZXQiOiJhcm9nZ2EiLCJrZXkiOiJCbG9jay1iX2NvbmZpZ1wvMVwvMTA1OS1zdXBwbC01MzFzNXAucG5nIiwiZWRpdHMiOltdfQ%3D%3D&w=1280&q=75',
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
            'image' => 'https://www.arogga.com/_next/image?url=https%3A%2F%2Fcdn2.arogga.com%2FeyJidWNrZXQiOiJhcm9nZ2EiLCJrZXkiOiJCbG9jay1iX2NvbmZpZ1wvMVwvMTA1OS1zdXBwbC01MzFzNXAucG5nIiwiZWRpdHMiOltdfQ%3D%3D&w=1280&q=75',
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
            'image' => 'https://www.arogga.com/_next/image?url=https%3A%2F%2Fcdn2.arogga.com%2FeyJidWNrZXQiOiJhcm9nZ2EiLCJrZXkiOiJCbG9jay1iX2NvbmZpZ1wvMVwvMTA1OS1zdXBwbC01MzFzNXAucG5nIiwiZWRpdHMiOltdfQ%3D%3D&w=1280&q=75',
            'children' => [
                'Ayurvedic',
                'Unani',
                'Herbal Supplements',
                'Herbal Skincare',
            ],
        ],
        [
            'name' => 'Pet Care',
            'image' => 'https://www.arogga.com/_next/image?url=https%3A%2F%2Fcdn2.arogga.com%2FeyJidWNrZXQiOiJhcm9nZ2EiLCJrZXkiOiJCbG9jay1iX2NvbmZpZ1wvMVwvMTA1OS1zdXBwbC01MzFzNXAucG5nIiwiZWRpdHMiOltdfQ%3D%3D&w=1280&q=75',
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
            'image' => 'https://www.arogga.com/_next/image?url=https%3A%2F%2Fcdn2.arogga.com%2FeyJidWNrZXQiOiJhcm9nZ2EiLCJrZXkiOiJCbG9jay1iX2NvbmZpZ1wvMVwvMTA1OS1zdXBwbC01MzFzNXAucG5nIiwiZWRpdHMiOltdfQ%3D%3D&w=1280&q=75',
            'children' => [
                'Disinfectants',
                'Sanitizers',
                'Household Wellness',
                'Air Fresheners',
            ],
        ],
    ];

    /**
     * Subcategory specific image overrides matching storefront category tiles.
     */
    public const SUBCATEGORY_IMAGES = [
        'skincare' => 'https://www.arogga.com/_next/image?url=https%3A%2F%2Fcdn2.arogga.com%2FeyJidWNrZXQiOiJhcm9nZ2EiLCJrZXkiOiJCbG9jay1iX2NvbmZpZ1wvMVwvMTA1OS1Ta2luLUNhcmUtbnZiZXoyLnBuZyIsImVkaXRzIjpbXX0%3D&w=1280&q=75',
        'haircare' => 'https://www.arogga.com/_next/image?url=https%3A%2F%2Fcdn2.arogga.com%2FeyJidWNrZXQiOiJhcm9nZ2EiLCJrZXkiOiJCbG9jay1iX2NvbmZpZ1wvMVwvMTA1OS1zdXBwbC01MzFzNXAucG5nIiwiZWRpdHMiOltdfQ%3D%3D&w=1280&q=75',
        'medical-devices' => 'https://www.arogga.com/_next/image?url=https%3A%2F%2Fcdn2.arogga.com%2FeyJidWNrZXQiOiJhcm9nZ2EiLCJrZXkiOiJCbG9jay1iX2NvbmZpZ1wvMVwvMTA1OS1zdXBwbC01MzFzNXAucG5nIiwiZWRpdHMiOltdfQ%3D%3D&w=1280&q=75',
    ];

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
     * 12 Storefront products with exact images, pricing, ratings, and categories.
     */
    public const STOREFRONT_PRODUCTS = [
        [
            'id' => 1,
            'name' => 'Skin1004 Centella Sun Serum SPF50+ 50ml',
            'slug' => 'skin1004-centella-sun-serum-spf50-50ml',
            'brand' => 'Skin1004',
            'category' => 'Skincare',
            'parent_category' => 'Beauty',
            'kind' => 'general',
            'price' => 1500,
            'original' => 2100,
            'cost' => 1100,
            'stock' => 45,
            'rating' => 5,
            'reviews' => 27,
            'badge' => '29% OFF',
            'requires_prescription' => false,
            'generic_name' => null,
            'image' => 'https://www.arogga.com/_next/image?url=https%3A%2F%2Fcdn2.arogga.com%2FeyJidWNrZXQiOiJhcm9nZ2EiLCJrZXkiOiJQcm9kdWN0LXBfaW1hZ2VzLzU0NzA4LzU0NzA4LXdhdHNvbnMtdGFtYXJpbmQtMTEteWE5bzBvLmpwZWciLCJlZGl0cyI6eyJyZXNpemUiOnsid2lkdGgiOjMwMCwiaGVpZ2h0IjozMDAsImZpdCI6Im91dHNpZGUifX19&w=375&q=75',
            'description' => 'Daily hydrating sun serum enriched with Centella Asiatica extract for soothing protection with SPF50+ PA++++.',
        ],
        [
            'id' => 2,
            'name' => 'Minimalist Niacinamide 10% Face Serum 30ml',
            'slug' => 'minimalist-niacinamide-10-face-serum-30ml',
            'brand' => 'Minimalist',
            'category' => 'Skincare',
            'parent_category' => 'Beauty',
            'kind' => 'general',
            'price' => 700,
            'original' => 950,
            'cost' => 500,
            'stock' => 60,
            'rating' => 4,
            'reviews' => 312,
            'badge' => '26% OFF',
            'requires_prescription' => false,
            'generic_name' => null,
            'image' => 'https://www.arogga.com/_next/image?url=https%3A%2F%2Fcdn2.arogga.com%2FeyJidWNrZXQiOiJhcm9nZ2EiLCJrZXkiOiJQcm9kdWN0VmFyaWFudC1wdl9pbWFnZXMvNTE3MTQvNTE3MTQtMy1hODE4ejcuanBlZyIsImVkaXRzIjp7InJlc2l6ZSI6eyJ3aWR0aCI6MzAwLCJoZWlnaHQiOjMwMCwiZml0Ijoib3V0c2lkZSJ9fX0%3D&w=375&q=75',
            'description' => 'Nourishing and oil-balancing daily face serum formulated with pure 10% Vitamin B3 (Niacinamide) and Zinc.',
        ],
        [
            'id' => 3,
            'name' => 'CeraVe Moisturising Lotion 473ml',
            'slug' => 'cerave-moisturising-lotion-473ml',
            'brand' => 'CeraVe',
            'category' => 'Skincare',
            'parent_category' => 'Beauty',
            'kind' => 'general',
            'price' => 2450,
            'original' => 2800,
            'cost' => 1850,
            'stock' => 30,
            'rating' => 5,
            'reviews' => 154,
            'badge' => '13% OFF',
            'requires_prescription' => false,
            'generic_name' => null,
            'image' => 'https://www.arogga.com/_next/image?url=https%3A%2F%2Fcdn2.arogga.com%2FeyJidWNrZXQiOiJhcm9nZ2EiLCJrZXkiOiJQcm9kdWN0VmFyaWFudC1wdl9pbWFnZXMvNTI1ODgvNTI1ODgtR1VFU1RfYTliNjBlYmItYTVlMS00ZGRhLWJmODEtMzhlOWZjNDQ5ZDAxLWd6c3l5bS53ZWJwIiwiZWRpdHMiOnsicmVzaXplIjp7IndpZHRoIjozMDAsImhlaWdodCI6MzAwLCJmaXQiOiJvdXRzaWRlIn19fQ%3D%3D&w=375&q=75',
            'description' => 'Lightweight moisturizing body and facial lotion with 3 essential ceramides and hyaluronic acid for 24-hour hydration.',
        ],
        [
            'id' => 4,
            'name' => 'Loratadine 10mg Tablet (30 pcs)',
            'slug' => 'loratadine-10mg-tablet-30-pcs',
            'brand' => 'Square Pharma',
            'category' => 'OTC Medicine',
            'parent_category' => 'Medicine',
            'kind' => 'medicine',
            'price' => 32,
            'original' => 38,
            'cost' => 22,
            'stock' => 150,
            'rating' => 4,
            'reviews' => 890,
            'badge' => '16% OFF',
            'requires_prescription' => false,
            'generic_name' => 'Loratadine',
            'image' => 'https://www.arogga.com/_next/image?url=https%3A%2F%2Fcdn2.arogga.com%2FeyJidWNrZXQiOiJhcm9nZ2EiLCJrZXkiOiJQcm9kdWN0LXBfaW1hZ2VzLzEwODI2LzEwODI2LUxvcmF0YWRpbmUtMTBtZy0zN2Y2cHkuanBlZyIsImVkaXRzIjp7InJlc2l6ZSI6eyJ3aWR0aCI6MzAwLCJoZWlnaHQiOjMwMCwiZml0Ijoib3V0c2lkZSJ9fX0%3D&w=375&q=75',
            'description' => 'Non-drowsy 24-hour antihistamine tablet for relief of allergic rhinitis, sneezing, and skin allergy symptoms.',
        ],
        [
            'id' => 5,
            'name' => 'Napa Extra 500mg (10 tablets)',
            'slug' => 'napa-extra-500mg-10-tablets',
            'brand' => 'Healath',
            'category' => 'OTC Medicine',
            'parent_category' => 'Medicine',
            'kind' => 'medicine',
            'price' => 28,
            'original' => 34,
            'cost' => 18,
            'stock' => 200,
            'rating' => 4,
            'reviews' => 1240,
            'badge' => '18% OFF',
            'requires_prescription' => false,
            'generic_name' => 'Paracetamol + Caffeine',
            'image' => 'https://www.arogga.com/_next/image?url=https%3A%2F%2Fcdn2.arogga.com%2FeyJidWNrZXQiOiJhcm9nZ2EiLCJrZXkiOiJQcm9kdWN0LXBfaW1hZ2VzLzEyNDc0LzEyNDc0LU5hcGEtOWpuZDZ0LmpwZWciLCJlZGl0cyI6eyJyZXNpemUiOnsid2lkdGgiOjMwMCwiaGVpZ2h0IjozMDAsImZpdCI6Im91dHNpZGUifX19&w=375&q=75',
            'description' => 'Fast-acting relief for headache, body aches, migraines, toothache, and acute fever.',
        ],
        [
            'id' => 6,
            'name' => "L'Oréal Paris Elvive Color & Shine Shampoo 340ml",
            'slug' => 'loreal-paris-elvive-color-shine-shampoo-340ml',
            'brand' => "L'Oréal",
            'category' => 'Haircare',
            'parent_category' => 'Beauty',
            'kind' => 'general',
            'price' => 890,
            'original' => 1150,
            'cost' => 650,
            'stock' => 40,
            'rating' => 4,
            'reviews' => 76,
            'badge' => '23% OFF',
            'requires_prescription' => false,
            'generic_name' => null,
            'image' => 'https://www.arogga.com/_next/image?url=https%3A%2F%2Fcdn2.arogga.com%2FeyJidWNrZXQiOiJhcm9nZ2EiLCJrZXkiOiJQcm9kdWN0LXBfaW1hZ2VzLzkyNTQ4LzkyNTQ4LTEtZmdpdWczLmpwZWciLCJlZGl0cyI6eyJyZXNpemUiOnsid2lkdGgiOjMwMCwiaGVpZ2h0IjozMDAsImZpdCI6Im91dHNpZGUifX19&w=375&q=75',
            'description' => 'Protective formula with red peony and UV filter to nourish, revive, and prolong vibrant color and shine.',
        ],
        [
            'id' => 7,
            'name' => 'Garlic Ginseng Omega 3 60 Softgels',
            'slug' => 'garlic-ginseng-omega-3-60-softgels',
            'brand' => "Nature's Way",
            'category' => 'Multivitamins',
            'parent_category' => 'Supplement',
            'kind' => 'general',
            'price' => 1250,
            'original' => 1600,
            'cost' => 900,
            'stock' => 50,
            'rating' => 5,
            'reviews' => 208,
            'badge' => '22% OFF',
            'requires_prescription' => false,
            'generic_name' => null,
            'image' => 'https://www.arogga.com/_next/image?url=https%3A%2F%2Fcdn2.arogga.com%2FeyJidWNrZXQiOiJhcm9nZ2EiLCJrZXkiOiJtZWRpY2luZS82MC82MDYwMy1HYXJsaWMtUG93ZGVyLTEwMGdtLXM2M28uanBlZyIsImVkaXRzIjp7InJlc2l6ZSI6eyJ3aWR0aCI6MzAwLCJoZWlnaHQiOjMwMCwiZml0Ijoib3V0c2lkZSJ9fX0%3D&w=375&q=75',
            'description' => 'Triple action blend supporting cardiovascular health, active vitality, stamina, and natural immunity.',
        ],
        [
            'id' => 8,
            'name' => 'Calbo D 60 Tablets (Calcium + Vitamin D)',
            'slug' => 'calbo-d-60-tablets-calcium-vitamin-d',
            'brand' => 'Renata',
            'category' => 'Multivitamins',
            'parent_category' => 'Supplement',
            'kind' => 'general',
            'price' => 340,
            'original' => 410,
            'cost' => 240,
            'stock' => 120,
            'rating' => 4,
            'reviews' => 431,
            'badge' => '17% OFF',
            'requires_prescription' => false,
            'generic_name' => 'Calcium Carbonate + Vitamin D3',
            'image' => 'https://www.arogga.com/_next/image?url=https%3A%2F%2Fcdn2.arogga.com%2FeyJidWNrZXQiOiJhcm9nZ2EiLCJrZXkiOiJQcm9kdWN0LXBfaW1hZ2VzLzMwMTYvMzAxNi1DQWxiby1ELVZpdGEtY29weS15a3dqZnAuanBlZyIsImVkaXRzIjp7InJlc2l6ZSI6eyJ3aWR0aCI6MzAwLCJoZWlnaHQiOjMwMCwiZml0Ijoib3V0c2lkZSJ9fX0%3D&w=375&q=75',
            'description' => 'Daily calcium and vitamin D3 supplement for bone density strength, osteoporosis prevention, and joint support.',
        ],
        [
            'id' => 9,
            'name' => 'Pigeon Baby Softlotion 200ml',
            'slug' => 'pigeon-baby-softlotion-200ml',
            'brand' => 'Pigeon',
            'category' => 'Baby Skincare',
            'parent_category' => 'Baby & Mom',
            'kind' => 'general',
            'price' => 620,
            'original' => 700,
            'cost' => 450,
            'stock' => 35,
            'rating' => 5,
            'reviews' => 96,
            'badge' => '11% OFF',
            'requires_prescription' => false,
            'generic_name' => null,
            'image' => 'https://www.arogga.com/_next/image?url=https%3A%2F%2Fcdn2.arogga.com%2FeyJidWNrZXQiOiJhcm9nZ2EiLCJrZXkiOiJQcm9kdWN0LXBfaW1hZ2VzLzkyMTgxLzkyMTgxLU1pbml0dXR1LVBpZ2Vvbi00dGgtRy1XaWRlLU1vdXRoLUZyb3N0ZWQtTmlwcGxlLU0tQ0I5MTctMy1Nb250aHMtQlBBLUZyZWUtQmFieS1OaXBwbGUtdWFvbmdrLndlYnAiLCJlZGl0cyI6eyJyZXNpemUiOnsid2lkdGgiOjMwMCwiaGVpZ2h0IjozMDAsImZpdCI6Im91dHNpZGUifX19&w=375&q=75',
            'description' => 'Hypoallergenic baby body lotion enriched with Sakura extract to nourish and soothe delicate newborn skin.',
        ],
        [
            'id' => 10,
            'name' => 'Digital Blood Pressure Monitor BPM-320',
            'slug' => 'digital-blood-pressure-monitor-bpm-320',
            'brand' => 'Omron',
            'category' => 'Medical Devices',
            'parent_category' => 'Healthcare',
            'kind' => 'general',
            'price' => 4200,
            'original' => 4900,
            'cost' => 3100,
            'stock' => 20,
            'rating' => 4,
            'reviews' => 58,
            'badge' => '14% OFF',
            'requires_prescription' => false,
            'generic_name' => null,
            'image' => 'https://www.arogga.com/_next/image?url=https%3A%2F%2Fcdn2.arogga.com%2FeyJidWNrZXQiOiJhcm9nZ2EiLCJrZXkiOiJQcm9kdWN0LXBfaW1hZ2VzLzI3MzQxLzI3MzQxLURpZ2l0YWwtQmxvb2QtUHJlc3N1cmUtTWFjaGluZS1MRC01NzktU0NJQU4tY29weS0zZzg3bHMuanBlZyIsImVkaXRzIjp7InJlc2l6ZSI6eyJ3aWR0aCI6MzAwLCJoZWlnaHQiOjMwMCwiZml0Ijoib3V0c2lkZSJ9fX0%3D&w=375&q=75',
            'description' => 'Clinically validated digital arm blood pressure monitor with Intellisense technology and irregular heartbeat detector.',
        ],
        [
            'id' => 11,
            'name' => 'Dettol Antiseptic Liquid 250ml',
            'slug' => 'dettol-antiseptic-liquid-250ml',
            'brand' => 'Dettol',
            'category' => 'Disinfectants',
            'parent_category' => 'Home Care',
            'kind' => 'general',
            'price' => 285,
            'original' => 320,
            'cost' => 200,
            'stock' => 80,
            'rating' => 5,
            'reviews' => 1120,
            'badge' => '11% OFF',
            'requires_prescription' => false,
            'generic_name' => null,
            'image' => 'https://www.arogga.com/_next/image?url=https%3A%2F%2Fcdn2.arogga.com%2FeyJidWNrZXQiOiJhcm9nZ2EiLCJrZXkiOiJQcm9kdWN0LXBfaW1hZ2VzLzI5MjMyLzI5MjMyLTEtZDl5dm1sLmpwZWciLCJlZGl0cyI6eyJyZXNpemUiOnsid2lkdGgiOjMwMCwiaGVpZ2h0IjozMDAsImZpdCI6Im91dHNpZGUifX19&w=375&q=75',
            'description' => 'Trusted multi-purpose antiseptic liquid for first aid wound cleansing, personal hygiene, and surface disinfection.',
        ],
        [
            'id' => 12,
            'name' => 'Himalaya Neem Face Wash 150ml',
            'slug' => 'himalaya-neem-face-wash-150ml',
            'brand' => 'Himalaya',
            'category' => 'Skincare',
            'parent_category' => 'Beauty',
            'kind' => 'general',
            'price' => 260,
            'original' => 310,
            'cost' => 175,
            'stock' => 90,
            'rating' => 4,
            'reviews' => 640,
            'badge' => '16% OFF',
            'requires_prescription' => false,
            'generic_name' => null,
            'image' => 'https://www.arogga.com/_next/image?url=https%3A%2F%2Fcdn2.arogga.com%2FeyJidWNrZXQiOiJhcm9nZ2EiLCJrZXkiOiJQcm9kdWN0VmFyaWFudC1wdl9pbWFnZXMvMzY1MjEvMzY1MjEtTmVlbS1fLVR1cm1lcmljLVNvYXBfMTgwMHgxODAwLXJhN3cyOS53ZWJwIiwiZWRpdHMiOnsicmVzaXplIjp7IndpZHRoIjozMDAsImhlaWdodCI6MzAwLCJmaXQiOiJvdXRzaWRlIn19fQ%3D%3D&w=375&q=75',
            'description' => 'Soap-free herbal formulation that clears impurities and helps prevent acne and pimples using neem and turmeric.',
        ],
    ];

    /**
     * Run the catalog seeder.
     */
    public function run(): void
    {
        Tenant::query()->orderBy('id')->chunkById(100, function ($tenants): void {
            foreach ($tenants as $tenant) {
                DB::transaction(function () use ($tenant): void {
                    $tenant->initializeCatalogDefaults();
                    $this->seedTaxonomyAndProducts($tenant);
                    $tenant->syncUsage();
                });
            }
        });
    }

    /**
     * Seed categories, subcategories, brands, and products for a tenant.
     */
    private function seedTaxonomyAndProducts(Tenant $tenant): void
    {
        // 1. Seed Categories & Subcategories
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

            // Update image if null
            if (empty($rootCategory->image) && !empty($rootItem['image'])) {
                $rootCategory->update(['image' => $rootItem['image']]);
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

                $categoryMap[$childName] = $childCategory;
            }
        }

        // 2. Seed Brands
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

        // 3. Seed Products
        foreach (self::STOREFRONT_PRODUCTS as $item) {
            $targetCategory = $categoryMap[$item['category']]
                ?? $categoryMap[$item['parent_category']]
                ?? Category::where('tenant_id', $tenant->id)->first();

            $targetBrand = $brandMap[$item['brand']]
                ?? Brand::where('tenant_id', $tenant->id)->first();

            $product = Product::firstOrCreate(
                ['tenant_id' => $tenant->id, 'slug' => $item['slug']],
                [
                    'kind' => $item['kind'] ?? 'general',
                    'name' => $item['name'],
                    'description' => $item['description'],
                    'status' => 'active',
                    'category_id' => $targetCategory?->id,
                    'brand_id' => $targetBrand?->id,
                    'generic_name' => $item['generic_name'] ?? null,
                    'requires_prescription' => (bool) ($item['requires_prescription'] ?? false),
                    'specs' => [
                        'rating' => $item['rating'] ?? 5,
                        'reviews' => $item['reviews'] ?? 0,
                        'badge' => $item['badge'] ?? null,
                        'featured' => true,
                        'storefront_synced' => true,
                    ],
                    'seo_title' => $item['name'] . ' | Best Price Online',
                    'seo_description' => $item['description'],
                ]
            );

            // Seed default variant
            $sku = sprintf('P%d-%s', $tenant->id, strtoupper(Str::slug(substr($item['name'], 0, 15))));
            $variant = ProductVariant::firstOrCreate(
                ['tenant_id' => $tenant->id, 'sku' => $sku],
                [
                    'product_id' => $product->id,
                    'title' => 'Standard Unit',
                    'price' => $item['price'],
                    'compare_at_price' => $item['original'] ?? ($item['price'] * 1.2),
                    'mrp' => $item['original'] ?? ($item['price'] * 1.2),
                    'cost_price' => $item['cost'] ?? ($item['price'] * 0.7),
                    'weight_grams' => 250,
                    'track_inventory' => true,
                    'is_default' => true,
                    'position' => 0,
                    'status' => 'active',
                    'options_hash' => md5($sku),
                ]
            );

            // Seed opening stock batch
            $batchNo = sprintf('BATCH-%d-%02d', $tenant->id, $item['id']);
            $batch = InventoryBatch::firstOrCreate(
                ['variant_id' => $variant->id, 'batch_no' => $batchNo],
                [
                    'tenant_id' => $tenant->id,
                    'expiry_date' => now()->addYears(2)->toDateString(),
                    'quantity' => $item['stock'],
                    'reserved' => 0,
                    'cost_price' => $item['cost'] ?? ($item['price'] * 0.7),
                    'received_at' => now(),
                    'status' => 'active',
                ]
            );

            // Seed initial stock movement
            StockMovement::firstOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'variant_id' => $variant->id,
                    'batch_id' => $batch->id,
                    'type' => 'receive',
                ],
                [
                    'note' => 'Storefront catalog initial opening inventory',
                    'qty_delta' => $item['stock'],
                    'reserved_delta' => 0,
                    'reference_type' => 'seeder',
                    'reference_id' => $product->id,
                    'user_id' => $tenant->user_id,
                    'created_at' => now(),
                ]
            );

            // Seed product media thumbnail
            if (!empty($item['image'])) {
                Media::firstOrCreate(
                    [
                        'tenant_id' => $tenant->id,
                        'model_type' => Product::class,
                        'model_id' => $product->id,
                        'collection' => 'thumbnail',
                    ],
                    [
                        'path' => $item['image'],
                        'mime' => 'image/webp',
                        'size_bytes' => 0, // Remote dummy CDN asset URL does not count towards local tenant quota
                        'position' => 0,
                        'alt' => $item['name'],
                        'created_at' => now(),
                    ]
                );
            }
        }
    }
}
