<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Product;
use App\Models\Slider;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StoreWebviewController extends Controller
{
    /**
     * Map category names to aesthetic icons.
     */
    protected const CATEGORY_ICONS = [
        'Medicine' => '💊',
        'Beauty' => '✨',
        'Healthcare' => '🏥',
        'Lab Test' => '🧪',
        'Baby & Mom' => '👶',
        'Herbal' => '🌿',
        'Pet Care' => '🐾',
        'Supplement' => '💪',
        'Home Care' => '🏠',
        'Everyday Essentials' => '🛒',
    ];

    public function index(Request $request): Response
    {
        $tenant = tenant() ?? Tenant::first();
        $tenantId = $tenant?->id ?? 1;

        // 1. Categories & Subcategories
        $categories = Category::query()
            ->where('tenant_id', $tenantId)
            ->whereNull('parent_id')
            ->where('status', true)
            ->with(['children' => function ($query) {
                $query->where('status', true)->orderBy('position')->orderBy('name');
            }])
            ->orderBy('position')
            ->orderBy('name')
            ->limit(8)
            ->get()
            ->map(function (Category $cat) {
                return [
                    'id' => $cat->id,
                    'name' => $cat->name,
                    'slug' => $cat->slug,
                    'icon' => self::CATEGORY_ICONS[$cat->name] ?? '🏷️',
                    'image' => $cat->image_url,
                    'children' => $cat->children->map(fn(Category $child) => [
                        'id' => $child->id,
                        'name' => $child->name,
                        'slug' => $child->slug,
                        'image' => $child->image_url,
                    ])->all(),
                ];
            })
            ->values()
            ->all();

        // 2. Category Tiles for "All You Need" section
        $categoryTiles = Category::query()
            ->where('tenant_id', $tenantId)
            ->where('status', true)
            ->whereNotNull('image')
            ->orderBy('position')
            ->take(12)
            ->get()
            ->map(function (Category $cat) {
                return [
                    'id' => $cat->id,
                    'name' => $cat->name,
                    'slug' => $cat->slug,
                    'image' => $cat->image_url,
                ];
            })
            ->values()
            ->all();

        // Fallback tiles if none configured with image
        if (empty($categoryTiles)) {
            $categoryTiles = collect($categories)->take(12)->map(fn($c) => [
                'id' => $c['id'],
                'name' => $c['name'],
                'slug' => $c['slug'],
                'image' => $c['image'] ?? 'https://via.placeholder.com/150',
            ])->all();
        }

        // 3. Hero Sliders
        $slides = Slider::query()
            ->where('tenant_id', $tenantId)
            ->where('status', true)
            ->orderBy('sort_order')
            ->get()
            ->map(function (Slider $slider) {
                return [
                    'id' => $slider->id,
                    'title' => $slider->title,
                    'subtitle' => $slider->subtitle,
                    'src' => $slider->image_url,
                    'alt' => $slider->alt ?? $slider->title,
                    'link' => $slider->link ?? '#',
                    'button_text' => $slider->button_text ?? 'Order Now',
                ];
            })
            ->values()
            ->all();

        // 4. Promotional Banners
        $promos = Banner::query()
            ->where('tenant_id', $tenantId)
            ->where('status', true)
            ->where('position', 'promo_grid')
            ->orderBy('sort_order')
            ->get()
            ->map(function (Banner $banner) {
                return [
                    'id' => $banner->id,
                    'eyebrow' => $banner->eyebrow ?? 'Special Offer',
                    'title' => $banner->title,
                    'subtitle' => $banner->subtitle,
                    'cta' => $banner->cta_text ?? 'Explore →',
                    'ctaColor' => $banner->cta_color ?? 'text-teal-600',
                    'gradient' => $banner->gradient ?? 'from-teal-500 to-cyan-600',
                    'image' => $banner->image_url,
                    'link' => $banner->link ?? '#',
                ];
            })
            ->values()
            ->all();

        // 5. Products Transformation
        $rawProducts = Product::query()
            ->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->with([
                'category.parent',
                'brand',
                'defaultVariant.batches',
                'activeVariants.batches',
                'media',
            ])
            ->latest('id')
            ->get();

        $formattedProducts = $rawProducts->map(function (Product $p) {
            $variant = $p->defaultVariant ?? $p->activeVariants->first() ?? $p->variants->first();
            $price = (float) ($variant?->price ?? 0);
            $original = (float) ($variant?->compare_at_price ?? $variant?->mrp ?? $price);

            // Compute discount badge if not in specs
            $badge = $p->specs['badge'] ?? null;
            if (!$badge && $original > $price && $original > 0) {
                $discountPercent = (int) round((($original - $price) / $original) * 100);
                if ($discountPercent > 0) {
                    $badge = $discountPercent . '% OFF';
                }
            }

            return [
                'id' => $p->id,
                'name' => $p->name,
                'slug' => $p->slug,
                'brand' => $p->brand?->name ?? 'Generic',
                'category' => $p->category?->name ?? 'General',
                'parent_category' => $p->category?->parent?->name ?? null,
                'price' => $price,
                'original' => $original > $price ? $original : $price,
                'rating' => (int) ($p->specs['rating'] ?? 5),
                'reviews' => (int) ($p->specs['reviews'] ?? $p->specs['reviews_count'] ?? 0),
                'badge' => $badge,
                'image' => $p->thumbnail_url ?? 'https://via.placeholder.com/300',
                'requires_prescription' => (bool) $p->requires_prescription,
                'is_in_stock' => $variant ? $variant->is_in_stock : true,
            ];
        })->values()->all();

        // Flash sale products: products with a discount or explicit badge, or top 6 products
        $flashSaleProducts = collect($formattedProducts)
            ->filter(fn($p) => !empty($p['badge']) || ($p['original'] > $p['price']))
            ->take(12)
            ->values()
            ->all();

        if (empty($flashSaleProducts)) {
            $flashSaleProducts = array_slice($formattedProducts, 0, 12);
        }

        // Best picks products
        $bestPicksProducts = $formattedProducts;

        // 6. Quick Action Cards
        $quickActions = [
            ['icon' => '📋', 'title' => 'Upload', 'subtitle' => 'Prescription', 'bg' => 'bg-primarylt', 'hover' => 'group-hover:bg-primary/15', 'link' => '#upload-prescription'],
            ['icon' => '🧪', 'title' => 'Book', 'subtitle' => 'Lab Test', 'bg' => 'bg-blue-50', 'hover' => 'group-hover:bg-blue-100', 'link' => '#lab-tests'],
            ['icon' => '👨‍⚕️', 'title' => 'Consult', 'subtitle' => 'a Doctor', 'bg' => 'bg-green-50', 'hover' => 'group-hover:bg-green-100', 'link' => '#doctor-consult'],
            ['icon' => '📦', 'title' => 'Track', 'subtitle' => 'Your Order', 'bg' => 'bg-orange-50', 'hover' => 'group-hover:bg-orange-100', 'link' => '#track-order'],
        ];

        // 7. Trust Badges
        $trustBadges = [
            ['icon' => '🚚', 'title' => 'Free Delivery', 'subtitle' => 'Orders above ৳500', 'wrap' => 'bg-primarylt border-primary/15', 'titleColor' => 'text-primary'],
            ['icon' => '✅', 'title' => '100% Authentic', 'subtitle' => 'DGDA approved', 'wrap' => 'bg-orange-50 border-orange-100', 'titleColor' => 'text-orange-600'],
            ['icon' => '💊', 'title' => 'Licensed Pharmacist', 'subtitle' => 'Rx verified', 'wrap' => 'bg-blue-50 border-blue-100', 'titleColor' => 'text-blue-600'],
            ['icon' => '🔒', 'title' => 'Secure Payment', 'subtitle' => 'bKash, Nagad, Card', 'wrap' => 'bg-green-50 border-green-100', 'titleColor' => 'text-green-600'],
        ];

        // 8. Stats
        $stats = [
            ['value' => '3M+', 'label' => 'Happy Customers'],
            ['value' => sprintf('%d+', max(count($formattedProducts), 12)), 'label' => 'Products Available'],
            ['value' => '64', 'label' => 'Districts Covered'],
            ['value' => '4 hrs', 'label' => 'Express Delivery'],
        ];

        $storeName = $tenant?->store_name ?: ($tenant?->name ?? 'MediMart');
        $storePhone = $tenant?->support_phone ?: ($tenant?->phone ?? '09610-016778');
        $storeEmail = $tenant?->support_email ?: ($tenant?->email ?? 'support@medimart.com');

        return Inertia::render('store/home', [
            'store' => [
                'id' => $tenant?->id ?? 1,
                'name' => $storeName,
                'subdomain' => $tenant?->subdomain ?? 'medimart',
                'custom_domain' => $tenant?->custom_domain,
                'logo_url' => $tenant?->logo_url,
                'phone' => $storePhone,
                'email' => $storeEmail,
                'currency' => $tenant?->currency ?? 'BDT',
                'timezone' => $tenant?->timezone ?? 'UTC',
            ],
            'seo' => [
                'meta_title' => $tenant?->meta_title ?: ($storeName . ' — Online Medicine, Beauty & Healthcare'),
                'meta_description' => $tenant?->meta_description ?: 'Order authentic medicines, wellness, and healthcare products with express doorstep delivery.',
                'meta_keywords' => $tenant?->meta_keywords ?: 'medicine, pharmacy, healthcare, beauty, healthcare products, order medicine online',
                'meta_robots' => $tenant?->meta_robots ?: 'index, follow',
                'canonical_url' => $tenant?->canonical_url ?: ($tenant?->custom_domain ? 'https://' . $tenant->custom_domain : url()->current()),
                'schema_markup' => $tenant?->schema_markup,
                'fb_pixel_id' => $tenant?->fb_pixel_id,
                'google_analytics_id' => $tenant?->google_analytics_id,
                'gtm_id' => $tenant?->gtm_id,
                'custom_head_script' => $tenant?->custom_head_script,
            ],
            'categories' => $categories,
            'categoryTiles' => $categoryTiles,
            'slides' => $slides,
            'promos' => $promos,
            'flashSaleProducts' => $flashSaleProducts,
            'bestPicksProducts' => $bestPicksProducts,
            'quickActions' => $quickActions,
            'trustBadges' => $trustBadges,
            'stats' => $stats,
        ]);
    }
}
