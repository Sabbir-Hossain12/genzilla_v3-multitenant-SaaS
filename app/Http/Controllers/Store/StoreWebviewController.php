<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\Order;
use App\Models\OrderPayment;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Str;
use App\Models\Slider;
use App\Models\Tenant;
use App\Models\TenantPrescription;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
                    'eyebrow' => $banner->eyebrow,
                    'title' => $banner->title,
                    'subtitle' => $banner->subtitle,
                    'cta' => $banner->cta_text,
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

        // 9. Brands for Home Page Showcase
        $brands = Brand::query()
            ->where('tenant_id', $tenantId)
            ->where('status', true)
            ->withCount(['products' => fn($q) => $q->where('status', 'active')])
            ->orderByDesc('products_count')
            ->orderBy('name')
            ->get()
            ->map(function (Brand $b) {
                return [
                    'id' => $b->id,
                    'name' => $b->name,
                    'slug' => $b->slug,
                    'image' => $b->image_url,
                    'products_count' => (int) $b->products_count,
                ];
            })
            ->values()
            ->all();

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
            'brands' => $brands,
        ]);
    }

    public function dashboard(Request $request): Response
    {
        $tenant = tenant() ?? Tenant::first();
        $tenantId = $tenant?->id ?? 1;

        $storeName = $tenant?->store_name ?: ($tenant?->name ?? 'MediMart');
        $storePhone = $tenant?->support_phone ?: ($tenant?->phone ?? null);
        $storeEmail = $tenant?->support_email ?: ($tenant?->email ?? null);

        // Customer determination: authenticated guard customer or tenant customer record
        $customer = auth('customer')->user()
            ?? Customer::where('tenant_id', $tenantId)->with(['addresses'])->first();

        $customerData = [
            'id' => $customer?->id,
            'name' => $customer ? trim("{$customer->first_name} {$customer->last_name}") : '',
            'first_name' => $customer?->first_name ?: '',
            'last_name' => $customer?->last_name ?: '',
            'phone' => $customer?->phone ?: '',
            'email' => $customer?->email ?: '',
            'status' => $customer?->status ?: 'active',
            'membership' => '🏅 Verified Member',
            'avatar' => $customer?->avatar_url,
            'joined_date' => $customer?->created_at ? $customer->created_at->format('M Y') : null,
        ];

        // 1. Orders: Scoped strictly to tenant and customer database records
        $orders = [];
        if ($customer) {
            $orders = Order::where('tenant_id', $tenantId)
                ->where(function ($q) use ($customer) {
                    $q->where('customer_id', $customer->id)
                      ->orWhere('shipping_phone', $customer->phone);
                })
                ->with(['products', 'payments'])
                ->latest('id')
                ->take(25)
                ->get()
                ->map(function (Order $order) {
                    $statusMap = [
                        'pending' => ['label' => 'Pending Review', 'color' => 'text-amber-700 bg-amber-50 border border-amber-200/60'],
                        'processing' => ['label' => 'Processing', 'color' => 'text-blue-700 bg-blue-50 border border-blue-200/60'],
                        'shipped' => ['label' => 'Out for Delivery', 'color' => 'text-indigo-700 bg-indigo-50 border border-indigo-200/60'],
                        'delivered' => ['label' => 'Delivered', 'color' => 'text-emerald-700 bg-emerald-50 border border-emerald-200/60'],
                        'cancelled' => ['label' => 'Cancelled', 'color' => 'text-rose-700 bg-rose-50 border border-rose-200/60'],
                    ];
                    $meta = $statusMap[strtolower($order->status)] ?? ['label' => ucfirst($order->status), 'color' => 'text-gray-700 bg-gray-50 border border-gray-200'];

                    $paymentStatusMap = [
                        'paid' => ['label' => 'Paid', 'color' => 'text-emerald-700 bg-emerald-50 border border-emerald-200'],
                        'unpaid' => ['label' => 'Unpaid', 'color' => 'text-amber-700 bg-amber-50 border border-amber-200'],
                        'partially_paid' => ['label' => 'Partially Paid', 'color' => 'text-blue-700 bg-blue-50 border border-blue-200'],
                        'refunded' => ['label' => 'Refunded', 'color' => 'text-purple-700 bg-purple-50 border border-purple-200'],
                    ];
                    $pMeta = $paymentStatusMap[strtolower($order->payment_status)] ?? ['label' => ucfirst($order->payment_status), 'color' => 'text-gray-700 bg-gray-50 border border-gray-200'];

                    $items = $order->products->map(function (OrderProduct $op) {
                        return [
                            'id' => $op->id,
                            'name' => $op->product_name,
                            'variant' => $op->variant_title,
                            'sku' => $op->sku,
                            'quantity' => (int) $op->quantity,
                            'unit_price' => (float) $op->unit_price,
                            'line_total' => (float) $op->line_total,
                            'requires_prescription' => (bool) $op->requires_prescription,
                            'dosage_instructions' => $op->dosage_instructions,
                        ];
                    })->values()->all();

                    $payments = $order->payments->map(function (OrderPayment $opay) {
                        return [
                            'id' => $opay->id,
                            'transaction_id' => $opay->transaction_id ?: ('TXN-' . $opay->id),
                            'method' => strtoupper($opay->payment_method),
                            'amount' => (float) $opay->amount,
                            'status' => ucfirst($opay->status),
                            'type' => ucfirst($opay->type),
                            'paid_at' => $opay->paid_at ? $opay->paid_at->format('M d, Y h:i A') : $opay->created_at->format('M d, Y h:i A'),
                        ];
                    })->values()->all();

                    $rxImage = null;
                    if (!empty($order->prescription_image)) {
                        $rxImage = str_starts_with($order->prescription_image, 'http')
                            ? $order->prescription_image
                            : ('/storage/' . ltrim($order->prescription_image, '/'));
                    }

                    return [
                        'id' => $order->order_number ?: ('ORD-' . $order->id),
                        'raw_id' => $order->id,
                        'date' => $order->placed_at ? $order->placed_at->format('M d, Y') : $order->created_at->format('M d, Y'),
                        'items_count' => $order->products->sum('quantity') ?: max(1, count($items)),
                        'subtotal' => (float) $order->subtotal,
                        'discount_amount' => (float) $order->discount_amount,
                        'shipping_charge' => (float) $order->shipping_charge,
                        'tax_amount' => (float) $order->tax_amount,
                        'total' => (float) $order->total,
                        'status' => $meta['label'],
                        'raw_status' => $order->status,
                        'color' => $meta['color'],
                        'payment_status' => $pMeta['label'],
                        'payment_color' => $pMeta['color'],
                        'payment_method' => strtoupper($order->payment_method),
                        'prescription_status' => $order->prescription_status,
                        'prescription_image' => $rxImage,
                        'pharmacist_rx_notes' => $order->pharmacist_rx_notes,
                        'shipping' => [
                            'name' => $order->shipping_name,
                            'phone' => $order->shipping_phone,
                            'email' => $order->shipping_email,
                            'address' => $order->shipping_address,
                            'city' => $order->shipping_city,
                            'postcode' => $order->shipping_postcode,
                            'method' => $order->shipping_method,
                            'note' => $order->order_note,
                        ],
                        'items' => $items,
                        'payments' => $payments,
                    ];
                })
                ->values()
                ->all();
        }

        // 2. Prescriptions: Aligned with TenantPrescription database table & Store Admin Rx Module
        $prescriptions = [];
        if ($customer) {
            $prescriptions = TenantPrescription::where('tenant_id', $tenantId)
                ->where(function ($q) use ($customer) {
                    $q->where('customer_id', $customer->id)
                      ->orWhere('customer_phone', $customer->phone);
                })
                ->with('order')
                ->latest('id')
                ->get()
                ->map(function (TenantPrescription $rx) {
                    $statusMap = [
                        'pending_review' => [
                            'label' => 'Under Pharmacist Review',
                            'color' => 'text-amber-700 bg-amber-50 border border-amber-200',
                            'badge' => 'Pending Review',
                        ],
                        'converted_to_order' => [
                            'label' => 'Approved & Converted to Order',
                            'color' => 'text-emerald-700 bg-emerald-50 border border-emerald-200',
                            'badge' => 'Approved',
                        ],
                        'rejected' => [
                            'label' => 'Declined / Invalid Rx',
                            'color' => 'text-rose-700 bg-rose-50 border border-rose-200',
                            'badge' => 'Declined',
                        ],
                    ];
                    $meta = $statusMap[$rx->status] ?? [
                        'label' => ucfirst(str_replace('_', ' ', $rx->status)),
                        'color' => 'text-gray-700 bg-gray-50 border border-gray-200',
                        'badge' => ucfirst($rx->status),
                    ];

                    return [
                        'id' => $rx->id,
                        'prescription_number' => $rx->prescription_number,
                        'status' => $rx->status,
                        'status_label' => $meta['label'],
                        'status_badge' => $meta['badge'],
                        'color' => $meta['color'],
                        'image_url' => $rx->image_url,
                        'customer_name' => $rx->customer_name,
                        'customer_phone' => $rx->customer_phone,
                        'delivery_address' => $rx->delivery_address,
                        'patient_notes' => $rx->patient_notes,
                        'pharmacist_notes' => $rx->pharmacist_notes,
                        'rejection_reason' => $rx->rejection_reason,
                        'order_id' => $rx->order_id,
                        'order_number' => $rx->order?->order_number,
                        'date' => $rx->created_at ? $rx->created_at->format('M d, Y') : null,
                        'reviewed_date' => $rx->reviewed_at ? $rx->reviewed_at->format('M d, Y') : null,
                    ];
                })
                ->values()
                ->all();
        }

        // 3. Saved Addresses: Dynamic from customer_addresses table
        $addresses = [];
        if ($customer) {
            $addresses = CustomerAddress::where('tenant_id', $tenantId)
                ->where('customer_id', $customer->id)
                ->orderByDesc('is_default')
                ->latest('id')
                ->get()
                ->map(function (CustomerAddress $addr) {
                    return [
                        'id' => $addr->id,
                        'title' => $addr->title ?: 'Home',
                        'first_name' => $addr->first_name,
                        'last_name' => $addr->last_name,
                        'name' => $addr->full_name,
                        'phone' => $addr->phone,
                        'address_1' => $addr->address_1,
                        'address_2' => $addr->address_2,
                        'state_district' => $addr->state_district,
                        'zip' => $addr->zip,
                        'country' => $addr->country,
                        'is_default' => (bool) $addr->is_default,
                        'formatted' => $addr->formatted_address,
                    ];
                })
                ->values()
                ->all();
        }

        // 4. Saved Wishlist: Strictly from wishlists database table
        $wishlist = [];
        if ($customer) {
            $wishlist = Wishlist::where('tenant_id', $tenantId)
                ->where('customer_id', $customer->id)
                ->with('product')
                ->latest('id')
                ->get()
                ->filter(fn($w) => $w->product !== null)
                ->map(function (Wishlist $w) {
                    return [
                        'id' => $w->id,
                        'product_id' => $w->product_id,
                        'name' => $w->product->name,
                        'slug' => $w->product->slug,
                        'price' => (float) $w->product->price,
                        'img' => $w->product->thumbnail_url ?? $w->product->image_url ?? null,
                    ];
                })
                ->values()
                ->all();
        }

        // 5. Transactions / Payments: Dynamic from order_payments table & Store Admin Transaction module
        $transactions = [];
        if ($customer) {
            $transactions = OrderPayment::where('tenant_id', $tenantId)
                ->whereHas('order', function ($q) use ($customer) {
                    $q->where('customer_id', $customer->id)
                      ->orWhere('shipping_phone', $customer->phone);
                })
                ->with('order')
                ->latest('id')
                ->take(15)
                ->get()
                ->map(function (OrderPayment $p) {
                    $isRefund = ($p->type === 'refund');
                    return [
                        'id' => $p->id,
                        'transaction_id' => $p->transaction_id ?: ('TXN-' . $p->id),
                        'order_number' => $p->order?->order_number ?: ('ORD-' . $p->order_id),
                        'desc' => ($isRefund ? 'Refund for Order #' : 'Payment for Order #') . ($p->order?->order_number ?: $p->order_id),
                        'method' => strtoupper($p->payment_method),
                        'type' => ucfirst($p->type),
                        'amount' => (float) $p->amount,
                        'positive' => $isRefund,
                        'status' => ucfirst($p->status),
                        'status_color' => match($p->status) {
                            'successful' => 'text-emerald-700 bg-emerald-50 border border-emerald-200',
                            'pending' => 'text-amber-700 bg-amber-50 border border-amber-200',
                            'failed' => 'text-rose-700 bg-rose-50 border border-rose-200',
                            'refunded' => 'text-purple-700 bg-purple-50 border border-purple-200',
                            default => 'text-gray-700 bg-gray-50 border border-gray-200',
                        },
                        'date' => $p->paid_at ? $p->paid_at->format('M d, Y') : $p->created_at->format('M d, Y'),
                    ];
                })
                ->values()
                ->all();
        }

        // 6. Active Store Coupons: Dynamic from coupons database table
        $coupons = Coupon::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>=', now());
            })
            ->latest('id')
            ->take(6)
            ->get()
            ->map(fn(Coupon $c) => [
                'id' => $c->id,
                'code' => $c->code,
                'type' => $c->type,
                'value' => (float) $c->value,
                'min_order_amount' => (float) $c->min_order_amount,
                'max_discount_amount' => (float) $c->max_discount_amount,
                'expires' => $c->expires_at ? $c->expires_at->format('M d, Y') : 'Ongoing',
            ])
            ->values()
            ->all();

        // 7. Computed Customer Stats from real database records
        $activeRxCount = count(array_filter($prescriptions, fn($rx) => $rx['status'] === 'pending_review' || $rx['status'] === 'converted_to_order'));
        $totalSpent = round(array_sum(array_column($orders, 'total')), 2);

        $stats = [
            'total_orders' => count($orders),
            'total_spent' => $totalSpent,
            'active_prescriptions' => $activeRxCount,
            'wishlist_count' => count($wishlist),
            'saved_addresses_count' => count($addresses),
            'coupons_count' => count($coupons),
        ];

        return Inertia::render('store/Dashboard', [
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
                'enable_direct_prescriptions' => (bool) ($tenant?->enable_direct_prescriptions ?? true),
            ],
            'customer' => $customerData,
            'stats' => $stats,
            'orders' => $orders,
            'prescriptions' => $prescriptions,
            'addresses' => $addresses,
            'wishlist' => $wishlist,
            'transactions' => $transactions,
            'coupons' => $coupons,
        ]);
    }

    /**
     * Live search products for storefront header search bar.
     */
    public function searchProducts(Request $request)
    {
        $query = trim((string) $request->input('q', ''));
        if (mb_strlen($query) < 1) {
            return response()->json([
                'products' => [],
                'total' => 0,
                'query' => '',
            ]);
        }

        $tenant = tenant() ?? Tenant::first();
        $tenantId = $tenant?->id ?? 1;

        $products = Product::query()
            ->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('generic_name', 'like', "%{$query}%")
                  ->orWhere('slug', 'like', "%{$query}%")
                  ->orWhereHas('brand', function ($bq) use ($query) {
                      $bq->where('name', 'like', "%{$query}%");
                  })
                  ->orWhereHas('category', function ($cq) use ($query) {
                      $cq->where('name', 'like', "%{$query}%");
                  })
                  ->orWhereHas('variants', function ($vq) use ($query) {
                      $vq->where('sku', 'like', "%{$query}%")
                         ->orWhere('barcode', 'like', "%{$query}%");
                  });
            })
            ->with([
                'category.parent',
                'brand',
                'defaultVariant',
                'activeVariants',
                'media',
            ])
            ->take(8)
            ->get();

        $formatted = $products->map(function (Product $p) {
            $variant = $p->defaultVariant ?? $p->activeVariants->first() ?? $p->variants->first();
            $price = (float) ($variant?->price ?? 0);
            $original = (float) ($variant?->compare_at_price ?? $variant?->mrp ?? $price);

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
                'brand' => $p->brand?->name ?? null,
                'generic_name' => $p->generic_name ?? null,
                'category' => $p->category?->name ?? 'General',
                'price' => $price,
                'original' => $original > $price ? $original : $price,
                'badge' => $badge,
                'image' => $p->thumbnail_url,
                'requires_prescription' => (bool) $p->requires_prescription,
                'is_in_stock' => $variant ? $variant->is_in_stock : true,
            ];
        })->values()->all();

        return response()->json([
            'products' => $formatted,
            'total' => count($formatted),
            'query' => $query,
        ]);
    }

    /**
     * Dedicated search results page for storefront.
     */
    public function searchResults(Request $request): Response
    {
        $tenant = tenant() ?? Tenant::first();
        $tenantId = $tenant?->id ?? 1;

        $query = trim((string) $request->input('q', ''));
        $selectedCategories = array_values(array_filter(explode(',', (string) $request->input('categories', ''))));
        $selectedBrands = array_values(array_filter(explode(',', (string) $request->input('brands', ''))));
        $minPrice = $request->filled('min_price') ? (float) $request->input('min_price') : null;
        $maxPrice = $request->filled('max_price') ? (float) $request->input('max_price') : null;
        $sort = (string) $request->input('sort', 'relevance');

        // Query active products for this tenant
        $productQuery = Product::query()
            ->where('tenant_id', $tenantId)
            ->where('status', 'active');

        if ($query !== '') {
            $productQuery->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('generic_name', 'like', "%{$query}%")
                  ->orWhere('slug', 'like', "%{$query}%")
                  ->orWhereHas('brand', function ($bq) use ($query) {
                      $bq->where('name', 'like', "%{$query}%");
                  })
                  ->orWhereHas('category', function ($cq) use ($query) {
                      $cq->where('name', 'like', "%{$query}%");
                  })
                  ->orWhereHas('variants', function ($vq) use ($query) {
                      $vq->where('sku', 'like', "%{$query}%")
                         ->orWhere('barcode', 'like', "%{$query}%");
                  });
            });
        }

        if (!empty($selectedCategories)) {
            $productQuery->whereIn('category_id', $selectedCategories);
        }

        if (!empty($selectedBrands)) {
            $productQuery->whereIn('brand_id', $selectedBrands);
        }

        $products = $productQuery
            ->with([
                'category.parent',
                'brand',
                'defaultVariant',
                'activeVariants',
                'media',
            ])
            ->get();

        $formattedProducts = $products->map(function (Product $p) {
            $variant = $p->defaultVariant ?? $p->activeVariants->first() ?? $p->variants->first();
            $price = (float) ($variant?->price ?? 0);
            $original = (float) ($variant?->compare_at_price ?? $variant?->mrp ?? $price);

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
                'brand_id' => (string) $p->brand_id,
                'category' => $p->category?->name ?? 'General',
                'category_id' => (string) $p->category_id,
                'generic_name' => $p->generic_name,
                'price' => $price,
                'original' => $original > $price ? $original : $price,
                'rating' => (int) ($p->specs['rating'] ?? 5),
                'reviews' => (int) ($p->specs['reviews'] ?? $p->specs['reviews_count'] ?? 0),
                'badge' => $badge,
                'image' => $p->thumbnail_url,
                'requires_prescription' => (bool) $p->requires_prescription,
                'is_in_stock' => $variant ? $variant->is_in_stock : true,
            ];
        });

        if ($minPrice !== null) {
            $formattedProducts = $formattedProducts->filter(fn($p) => $p['price'] >= $minPrice);
        }
        if ($maxPrice !== null) {
            $formattedProducts = $formattedProducts->filter(fn($p) => $p['price'] <= $maxPrice);
        }

        // Sorting
        if ($sort === 'price_low_high') {
            $formattedProducts = $formattedProducts->sortBy('price')->values();
        } elseif ($sort === 'price_high_low') {
            $formattedProducts = $formattedProducts->sortByDesc('price')->values();
        } elseif ($sort === 'newest') {
            $formattedProducts = $formattedProducts->sortByDesc('id')->values();
        } else {
            $formattedProducts = $formattedProducts->values();
        }

        // Compute Facets from the whole tenant catalog so filter counts reflect available inventory
        $tenantProducts = Product::where('tenant_id', $tenantId)->where('status', 'active')->get();

        $categoryFacets = Category::where('tenant_id', $tenantId)
            ->where('status', true)
            ->get()
            ->map(function ($cat) use ($tenantProducts) {
                return [
                    'id' => (string) $cat->id,
                    'name' => $cat->name,
                    'count' => $tenantProducts->where('category_id', $cat->id)->count(),
                ];
            })
            ->filter(fn($c) => $c['count'] > 0)
            ->values()
            ->all();

        $brandFacets = Brand::where('tenant_id', $tenantId)
            ->where('status', true)
            ->get()
            ->map(function ($b) use ($tenantProducts) {
                return [
                    'id' => (string) $b->id,
                    'name' => $b->name,
                    'count' => $tenantProducts->where('brand_id', $b->id)->count(),
                ];
            })
            ->filter(fn($b) => $b['count'] > 0)
            ->values()
            ->all();

        // Top navigation categories
        $categories = Category::query()
            ->where('tenant_id', $tenantId)
            ->whereNull('parent_id')
            ->where('status', true)
            ->orderBy('position')
            ->orderBy('name')
            ->limit(8)
            ->get()
            ->map(fn($cat) => [
                'id' => $cat->id,
                'name' => $cat->name,
                'slug' => $cat->slug,
                'icon' => self::CATEGORY_ICONS[$cat->name] ?? '???',
            ])
            ->values()
            ->all();

        return Inertia::render('store/SearchResults', [
            'query' => $query,
            'products' => $formattedProducts->all(),
            'total' => $formattedProducts->count(),
            'categoryFacets' => $categoryFacets,
            'brandFacets' => $brandFacets,
            'categories' => $categories,
            'filters' => [
                'categories' => $selectedCategories,
                'brands' => $selectedBrands,
                'min_price' => $minPrice,
                'max_price' => $maxPrice,
                'sort' => $sort,
            ],
        ]);
    }

    /**
     * Product details view for storefront.
     */
    public function productDetails(Request $request, string $slug): Response
    {
        $tenant = tenant() ?? Tenant::first();
        $tenantId = $tenant?->id ?? 1;

        $product = Product::query()
            ->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->where(function ($query) use ($slug) {
                $query->where('slug', $slug);
                if (is_numeric($slug)) {
                    $query->orWhere('id', (int) $slug);
                }
            })
            ->with([
                'category.parent',
                'brand',
                'variants.batches',
                'activeVariants.batches',
                'defaultVariant.batches',
                'media',
                'approvedReviews.customer',
            ])
            ->first();

        if (!$product) {
            abort(404, 'Product not found');
        }

        // 1. Gallery
        $gallery = $product->media->map(function ($m) use ($product) {
            $url = Str::startsWith($m->path, ['http://', 'https://'])
                ? $m->path
                : '/storage/' . ltrim($m->path, '/');
            return [
                'id' => $m->id,
                'url' => $url,
                'alt' => $m->alt ?: $product->name,
                'collection' => $m->collection,
            ];
        })->values();

        if ($gallery->isEmpty()) {
            $thumb = $product->thumbnail_url ?: 'https://via.placeholder.com/600';
            $gallery = collect([
                [
                    'id' => 'thumb-1',
                    'url' => $thumb,
                    'alt' => $product->name,
                    'collection' => 'cover',
                ],
            ]);
        }

        // 2. Variants
        $rawVariants = $product->activeVariants->isNotEmpty() ? $product->activeVariants : $product->variants;
        $variantsList = $rawVariants->map(function (ProductVariant $v) {
            $price = (float) ($v->price ?? 0);
            $original = (float) ($v->compare_at_price ?? $v->mrp ?? $price);
            $discountPercent = ($original > $price && $original > 0)
                ? (int) round((($original - $price) / $original) * 100)
                : 0;

            return [
                'id' => $v->id,
                'title' => $v->title ?: 'Standard',
                'sku' => $v->sku,
                'price' => $price,
                'original' => $original > $price ? $original : $price,
                'discount_percent' => $discountPercent,
                'is_default' => (bool) $v->is_default,
                'is_in_stock' => $v->is_in_stock,
                'available_stock' => $v->available_stock,
            ];
        })->values();

        if ($variantsList->isEmpty()) {
            $variantsList = collect([
                [
                    'id' => 'default',
                    'title' => 'Standard',
                    'sku' => 'SKU-' . $product->id,
                    'price' => 0,
                    'original' => 0,
                    'discount_percent' => 0,
                    'is_default' => true,
                    'is_in_stock' => true,
                    'available_stock' => 99,
                ],
            ]);
        }

        // 3. Specs and structured details
        $specs = is_array($product->specs) ? $product->specs : [];

        // 4. Reviews & Rating Statistics
        $reviews = $product->approvedReviews->map(function ($r) {
            $name = $r->reviewer_name ?: ($r->customer ? $r->customer->name : 'Verified Customer');
            $initials = collect(explode(' ', $name))
                ->filter()
                ->map(fn($w) => Str::substr($w, 0, 1))
                ->take(2)
                ->implode('');

            return [
                'id' => $r->id,
                'reviewer_name' => $name,
                'initials' => strtoupper($initials) ?: 'VC',
                'rating' => (int) $r->rating,
                'title' => $r->title ?: 'Verified Review',
                'comment' => $r->comment,
                'helpful_count' => (int) ($r->helpful_count ?? 0),
                'is_verified_purchase' => (bool) $r->is_verified_purchase,
                'date' => $r->created_at ? $r->created_at->format('d M Y') : 'Recent',
            ];
        })->values();

        $avgRating = $reviews->isNotEmpty()
            ? (float) round($reviews->avg('rating'), 1)
            : (float) ($specs['rating'] ?? 5.0);

        $reviewsCount = $reviews->isNotEmpty()
            ? $reviews->count()
            : (int) ($specs['reviews'] ?? $specs['reviews_count'] ?? 0);

        $starCounts = [
            5 => $reviews->where('rating', 5)->count(),
            4 => $reviews->where('rating', 4)->count(),
            3 => $reviews->where('rating', 3)->count(),
            2 => $reviews->where('rating', 2)->count(),
            1 => $reviews->where('rating', 1)->count(),
        ];
        if ($reviews->isEmpty() && $reviewsCount > 0) {
            $starCounts = [
                5 => (int) round($reviewsCount * 0.8),
                4 => (int) round($reviewsCount * 0.15),
                3 => (int) round($reviewsCount * 0.05),
                2 => 0,
                1 => 0,
            ];
        }

        // 5. Related Products (within same tenant, prioritizing same category)
        $rawRelated = Product::query()
            ->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->where('id', '!=', $product->id)
            ->when($product->category_id, fn($q) => $q->where('category_id', $product->category_id))
            ->with(['brand', 'category.parent', 'defaultVariant.batches', 'activeVariants.batches', 'media'])
            ->take(10)
            ->get();

        if ($rawRelated->count() < 6) {
            $more = Product::query()
                ->where('tenant_id', $tenantId)
                ->where('status', 'active')
                ->where('id', '!=', $product->id)
                ->whereNotIn('id', $rawRelated->pluck('id'))
                ->with(['brand', 'category.parent', 'defaultVariant.batches', 'activeVariants.batches', 'media'])
                ->take(10 - $rawRelated->count())
                ->get();
            $rawRelated = $rawRelated->concat($more);
        }

        $relatedProducts = $rawRelated->map(function (Product $p) {
            $variant = $p->defaultVariant ?? $p->activeVariants->first() ?? $p->variants->first();
            $price = (float) ($variant?->price ?? 0);
            $original = (float) ($variant?->compare_at_price ?? $variant?->mrp ?? $price);

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

        // 6. Navigation categories
        $categories = Category::query()
            ->where('tenant_id', $tenantId)
            ->whereNull('parent_id')
            ->where('status', true)
            ->orderBy('position')
            ->orderBy('name')
            ->limit(8)
            ->get()
            ->map(fn($cat) => [
                'id' => $cat->id,
                'name' => $cat->name,
                'slug' => $cat->slug,
                'icon' => self::CATEGORY_ICONS[$cat->name] ?? '🏷️',
            ])
            ->values()
            ->all();

        // 7. SEO
        $seo = [
            'meta_title' => $product->seo_title ?: ($product->name . ' — Buy Online | ' . ($tenant->store_name ?: 'MediMart')),
            'meta_description' => $product->seo_description ?: Str::limit(strip_tags($product->description ?? ''), 160),
            'image' => $gallery->first()['url'] ?? null,
        ];

        return Inertia::render('store/ProductDetails', [
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'description' => $product->description,
                'generic_name' => $product->generic_name,
                'requires_prescription' => (bool) $product->requires_prescription,
                'brand' => $product->brand ? [
                    'id' => $product->brand->id,
                    'name' => $product->brand->name,
                    'slug' => $product->brand->slug,
                ] : null,
                'category' => $product->category ? [
                    'id' => $product->category->id,
                    'name' => $product->category->name,
                    'slug' => $product->category->slug,
                    'parent' => $product->category->parent ? [
                        'id' => $product->category->parent->id,
                        'name' => $product->category->parent->name,
                        'slug' => $product->category->parent->slug,
                    ] : null,
                ] : null,
                'gallery' => $gallery->all(),
                'variants' => $variantsList->all(),
                'specs' => $specs,
                'avg_rating' => $avgRating,
                'reviews_count' => $reviewsCount,
                'star_counts' => $starCounts,
                'reviews' => $reviews->all(),
            ],
            'relatedProducts' => $relatedProducts,
            'categories' => $categories,
            'seo' => $seo,
        ]);
    }

    /**
     * Category & subcategory product listing page.
     */
    public function categoryProducts(Request $request, string $slug, ?string $subSlug = null): Response
    {
        $tenant = tenant() ?? Tenant::first();
        $tenantId = $tenant?->id ?? 1;

        // 1. Resolve Target Category (and parent if subSlug given)
        $targetSlug = $subSlug ?: $slug;

        $category = Category::query()
            ->where('tenant_id', $tenantId)
            ->where('status', true)
            ->where(function ($q) use ($targetSlug) {
                $q->where('slug', $targetSlug);
                if (is_numeric($targetSlug)) {
                    $q->orWhere('id', (int) $targetSlug);
                }
            })
            ->with(['parent', 'children' => fn($q) => $q->where('status', true)->orderBy('position')])
            ->first();

        if (!$category) {
            abort(404, 'Category not found');
        }

        // Determine if target is root category or child category
        $isSubcategory = (bool) $category->parent_id;
        $parentCategory = $category->parent;

        if ($isSubcategory) {
            $rootCategory = $parentCategory;
            $currentSubcategory = $category;
            // Sibling subcategories under same parent
            $subcategories = Category::query()
                ->where('tenant_id', $tenantId)
                ->where('parent_id', $category->parent_id)
                ->where('status', true)
                ->orderBy('position')
                ->get();
        } else {
            $rootCategory = $category;
            $currentSubcategory = null;
            $subcategories = $category->children;
        }

        // 2. Query all products for this root category and its children
        $allCategoryProducts = Product::query()
            ->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->whereIn('category_id', array_merge([$rootCategory->id], $rootCategory->children->pluck('id')->all()))
            ->with([
                'category.parent',
                'brand',
                'defaultVariant.batches',
                'activeVariants.batches',
                'media',
            ])
            ->get();

        // Subcategory counts
        $subcategoryCounts = [];
        foreach ($subcategories as $sub) {
            $subcategoryCounts[$sub->id] = $allCategoryProducts->where('category_id', $sub->id)->count();
        }

        // 3. Filters
        $requestedSubcategories = array_filter(explode(',', (string) $request->query('subcategories', '')));
        if (empty($requestedSubcategories) && $isSubcategory) {
            $selectedSubcategories = [$category->slug];
        } else {
            $selectedSubcategories = $requestedSubcategories;
        }

        $selectedBrands = array_filter(explode(',', (string) $request->query('brands', '')));
        $minPrice = $request->query('min_price') !== null ? (float) $request->query('min_price') : null;
        $maxPrice = $request->query('max_price') !== null ? (float) $request->query('max_price') : null;
        $minRating = $request->query('rating') !== null ? (int) $request->query('rating') : null;
        $onlyDiscounted = (bool) $request->query('discount', false);
        $rxFilter = $request->query('rx', 'all');
        $sort = (string) $request->query('sort', 'popularity');

        // Format products
        $formattedProducts = $allCategoryProducts->map(function (Product $p) {
            $variant = $p->defaultVariant ?? $p->activeVariants->first() ?? $p->variants->first();
            $price = (float) ($variant?->price ?? 0);
            $original = (float) ($variant?->compare_at_price ?? $variant?->mrp ?? $price);

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
                'brand_id' => $p->brand_id ? (string) $p->brand_id : null,
                'brand_slug' => $p->brand?->slug,
                'category' => $p->category?->name ?? 'General',
                'category_id' => (string) $p->category_id,
                'category_slug' => $p->category?->slug,
                'parent_category' => $p->category?->parent?->name ?? null,
                'parent_category_id' => $p->category?->parent_id ? (string) $p->category?->parent_id : null,
                'parent_category_slug' => $p->category?->parent?->slug,
                'price' => $price,
                'original' => $original > $price ? $original : $price,
                'original_price' => $original > $price ? $original : $price,
                'rating' => (int) ($p->specs['rating'] ?? 5),
                'reviews' => (int) ($p->specs['reviews'] ?? $p->specs['reviews_count'] ?? 0),
                'badge' => $badge,
                'image' => $p->thumbnail_url ?? 'https://via.placeholder.com/300',
                'requires_prescription' => (bool) $p->requires_prescription,
                'is_in_stock' => $variant ? $variant->is_in_stock : true,
            ];
        })->values();

        // Brand facets (from all products in this category)
        $brandFacets = Brand::query()
            ->where('tenant_id', $tenantId)
            ->where('status', true)
            ->get()
            ->map(function ($b) use ($allCategoryProducts) {
                return [
                    'id' => (string) $b->id,
                    'name' => $b->name,
                    'slug' => $b->slug,
                    'count' => $allCategoryProducts->where('brand_id', $b->id)->count(),
                ];
            })
            ->filter(fn($b) => $b['count'] > 0)
            ->values()
            ->all();

        // Subcategory facets
        $formattedSubcategories = $subcategories->map(fn($sub) => [
            'id' => (string) $sub->id,
            'name' => $sub->name,
            'slug' => $sub->slug,
            'image' => $sub->image_url,
            'count' => $subcategoryCounts[$sub->id] ?? 0,
        ])->values()->all();

        // Navigation categories (top bar)
        $categories = Category::query()
            ->where('tenant_id', $tenantId)
            ->whereNull('parent_id')
            ->where('status', true)
            ->orderBy('position')
            ->orderBy('name')
            ->limit(10)
            ->get()
            ->map(fn($cat) => [
                'id' => $cat->id,
                'name' => $cat->name,
                'slug' => $cat->slug,
                'icon' => self::CATEGORY_ICONS[$cat->name] ?? '🏷️',
            ])
            ->values()
            ->all();

        // SEO
        $seo = [
            'meta_title' => $category->meta_title ?: ($category->name . ' — Online Pharmacy | ' . ($tenant->store_name ?: 'MediMart')),
            'meta_description' => $category->meta_description ?: ('Shop ' . $category->name . ' products at best prices with express doorstep delivery.'),
            'image' => $category->image_url,
        ];

        return Inertia::render('store/CategoryProducts', [
            'category' => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'image' => $category->image_url,
                'h1_heading' => $category->h1_heading ?: $category->name,
                'meta_description' => $category->meta_description,
                'is_subcategory' => $isSubcategory,
            ],
            'parentCategory' => $parentCategory ? [
                'id' => $parentCategory->id,
                'name' => $parentCategory->name,
                'slug' => $parentCategory->slug,
            ] : null,
            'currentSubcategory' => $currentSubcategory ? [
                'id' => $currentSubcategory->id,
                'name' => $currentSubcategory->name,
                'slug' => $currentSubcategory->slug,
            ] : null,
            'subcategories' => $formattedSubcategories,
            'products' => $formattedProducts->all(),
            'totalProducts' => $formattedProducts->count(),
            'allCategoryProductsCount' => $allCategoryProducts->count(),
            'brandFacets' => $brandFacets,
            'categories' => $categories,
            'filters' => [
                'subcategories' => $selectedSubcategories,
                'brands' => $selectedBrands,
                'min_price' => $minPrice,
                'max_price' => $maxPrice,
                'sort' => $sort,
                'rating' => $minRating,
                'discount' => $onlyDiscounted,
                'rx' => $rxFilter,
            ],
            'seo' => $seo,
        ]);
    }

    /**
     * Brand product listing page.
     */
    public function brandProducts(Request $request, string $slug): Response
    {
        $tenant = tenant() ?? Tenant::first();
        $tenantId = $tenant?->id ?? 1;

        $brand = Brand::query()
            ->where('tenant_id', $tenantId)
            ->where('status', true)
            ->where(function ($q) use ($slug) {
                $q->where('slug', $slug);
                if (is_numeric($slug)) {
                    $q->orWhere('id', (int) $slug);
                }
            })
            ->first();

        if (!$brand) {
            abort(404, 'Brand not found');
        }

        // 1. Query all active products for this brand
        $allBrandProducts = Product::query()
            ->where('tenant_id', $tenantId)
            ->where('brand_id', $brand->id)
            ->where('status', 'active')
            ->with([
                'category.parent',
                'brand',
                'defaultVariant.batches',
                'activeVariants.batches',
                'media',
            ])
            ->get();

        // 2. Category facets from the brand's products
        $catIds = $allBrandProducts->pluck('category_id')->filter()->unique();
        $categoryFacets = [];
        if ($catIds->isNotEmpty()) {
            $categoryFacets = Category::query()
                ->where('tenant_id', $tenantId)
                ->whereIn('id', $catIds)
                ->where('status', true)
                ->orderBy('position')
                ->orderBy('name')
                ->get()
                ->map(fn($c) => [
                    'id' => (string) $c->id,
                    'name' => $c->name,
                    'slug' => $c->slug,
                    'count' => $allBrandProducts->where('category_id', $c->id)->count(),
                ])
                ->values()
                ->all();
        }

        // 3. Filters
        $selectedCategories = array_filter(explode(',', (string) $request->query('categories', '')));
        $minPrice = $request->query('min_price') !== null ? (float) $request->query('min_price') : null;
        $maxPrice = $request->query('max_price') !== null ? (float) $request->query('max_price') : null;
        $minRating = $request->query('rating') !== null ? (int) $request->query('rating') : null;
        $onlyDiscounted = (bool) $request->query('discount', false);
        $rxFilter = $request->query('rx', 'all');
        $sort = (string) $request->query('sort', 'popularity');

        // 4. Format products
        $formattedProducts = $allBrandProducts->map(function (Product $p) {
            $variant = $p->defaultVariant ?? $p->activeVariants->first() ?? $p->variants->first();
            $price = (float) ($variant?->price ?? 0);
            $original = (float) ($variant?->compare_at_price ?? $variant?->mrp ?? $price);

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
                'brand_id' => $p->brand_id ? (string) $p->brand_id : null,
                'brand_slug' => $p->brand?->slug,
                'category' => $p->category?->name ?? 'General',
                'category_id' => (string) $p->category_id,
                'category_slug' => $p->category?->slug,
                'parent_category' => $p->category?->parent?->name ?? null,
                'parent_category_id' => $p->category?->parent_id ? (string) $p->category?->parent_id : null,
                'parent_category_slug' => $p->category?->parent?->slug,
                'price' => $price,
                'original' => $original > $price ? $original : $price,
                'original_price' => $original > $price ? $original : $price,
                'rating' => (int) ($p->specs['rating'] ?? 5),
                'reviews' => (int) ($p->specs['reviews'] ?? $p->specs['reviews_count'] ?? 0),
                'badge' => $badge,
                'image' => $p->thumbnail_url ?? 'https://via.placeholder.com/300',
                'requires_prescription' => (bool) $p->requires_prescription,
                'is_in_stock' => $variant ? $variant->is_in_stock : true,
            ];
        })->values();

        // 5. Navigation categories (top bar)
        $categories = Category::query()
            ->where('tenant_id', $tenantId)
            ->whereNull('parent_id')
            ->where('status', true)
            ->orderBy('position')
            ->orderBy('name')
            ->limit(10)
            ->get()
            ->map(fn($cat) => [
                'id' => $cat->id,
                'name' => $cat->name,
                'slug' => $cat->slug,
                'icon' => self::CATEGORY_ICONS[$cat->name] ?? '🏷️',
            ])
            ->values()
            ->all();

        // 6. SEO
        $seo = [
            'meta_title' => $brand->meta_title ?: ($brand->name . ' Products — Online Pharmacy | ' . ($tenant->store_name ?: 'MediMart')),
            'meta_description' => $brand->meta_description ?: ('Shop authentic ' . $brand->name . ' products at best prices with express doorstep delivery.'),
            'image' => $brand->image_url,
        ];

        return Inertia::render('store/BrandProducts', [
            'brand' => [
                'id' => $brand->id,
                'name' => $brand->name,
                'slug' => $brand->slug,
                'image' => $brand->image_url,
                'h1_heading' => $brand->h1_heading ?: $brand->name,
                'meta_description' => $brand->meta_description,
            ],
            'products' => $formattedProducts->all(),
            'totalProducts' => $formattedProducts->count(),
            'categoryFacets' => $categoryFacets,
            'categories' => $categories,
            'filters' => [
                'categories' => $selectedCategories,
                'min_price' => $minPrice,
                'max_price' => $maxPrice,
                'sort' => $sort,
                'rating' => $minRating,
                'discount' => $onlyDiscounted,
                'rx' => $rxFilter,
            ],
            'seo' => $seo,
        ]);
    }

    /**
     * All Products listing page with collection filters (Flash Sale, Best Picks, All).
     */
    public function productList(Request $request): Response
    {
        $tenant = tenant() ?? Tenant::first();
        $tenantId = $tenant?->id ?? 1;

        $collection = (string) $request->query('collection', 'all');

        // 1. Query all active products
        $allProductsQuery = Product::query()
            ->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->with([
                'category.parent',
                'brand',
                'defaultVariant.batches',
                'activeVariants.batches',
                'media',
            ]);

        $rawProducts = $allProductsQuery->latest('id')->get();

        // 2. Format products
        $formattedProducts = $rawProducts->map(function (Product $p) {
            $variant = $p->defaultVariant ?? $p->activeVariants->first() ?? $p->variants->first();
            $price = (float) ($variant?->price ?? 0);
            $original = (float) ($variant?->compare_at_price ?? $variant?->mrp ?? $price);

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
                'brand_id' => $p->brand_id ? (string) $p->brand_id : null,
                'brand_slug' => $p->brand?->slug,
                'category' => $p->category?->name ?? 'General',
                'category_id' => (string) $p->category_id,
                'category_slug' => $p->category?->slug,
                'parent_category' => $p->category?->parent?->name ?? null,
                'parent_category_id' => $p->category?->parent_id ? (string) $p->category?->parent_id : null,
                'parent_category_slug' => $p->category?->parent?->slug,
                'price' => $price,
                'original' => $original > $price ? $original : $price,
                'original_price' => $original > $price ? $original : $price,
                'rating' => (int) ($p->specs['rating'] ?? 5),
                'reviews' => (int) ($p->specs['reviews'] ?? $p->specs['reviews_count'] ?? 0),
                'badge' => $badge,
                'image' => $p->thumbnail_url ?? 'https://via.placeholder.com/300',
                'requires_prescription' => (bool) $p->requires_prescription,
                'is_in_stock' => $variant ? $variant->is_in_stock : true,
            ];
        })->values();

        // 3. Category facets
        $categoryFacets = Category::query()
            ->where('tenant_id', $tenantId)
            ->whereNull('parent_id')
            ->where('status', true)
            ->with('children')
            ->orderBy('position')
            ->orderBy('name')
            ->get()
            ->map(function ($cat) use ($formattedProducts) {
                $familyIds = array_merge([(string) $cat->id], $cat->children->pluck('id')->map(fn($id) => (string) $id)->all());
                return [
                    'id' => (string) $cat->id,
                    'name' => $cat->name,
                    'slug' => $cat->slug,
                    'count' => $formattedProducts->whereIn('category_id', $familyIds)->count(),
                ];
            })
            ->filter(fn($c) => $c['count'] > 0)
            ->values()
            ->all();

        // 4. Brand facets
        $brandFacets = Brand::query()
            ->where('tenant_id', $tenantId)
            ->where('status', true)
            ->get()
            ->map(function ($b) use ($formattedProducts) {
                return [
                    'id' => (string) $b->id,
                    'name' => $b->name,
                    'slug' => $b->slug,
                    'count' => $formattedProducts->where('brand_id', (string) $b->id)->count(),
                ];
            })
            ->filter(fn($b) => $b['count'] > 0)
            ->values()
            ->all();

        // 5. Initial filters
        $selectedCategories = array_filter(explode(',', (string) $request->query('categories', '')));
        $selectedBrands = array_filter(explode(',', (string) $request->query('brands', '')));
        $minPrice = $request->query('min_price') !== null ? (float) $request->query('min_price') : null;
        $maxPrice = $request->query('max_price') !== null ? (float) $request->query('max_price') : null;
        $minRating = $request->query('rating') !== null ? (int) $request->query('rating') : null;
        $onlyDiscounted = $collection === 'flash-sale' ? true : (bool) $request->query('discount', false);
        $rxFilter = $request->query('rx', 'all');
        $sort = (string) $request->query('sort', 'popularity');

        // 6. Navigation categories (top bar)
        $categories = Category::query()
            ->where('tenant_id', $tenantId)
            ->whereNull('parent_id')
            ->where('status', true)
            ->orderBy('position')
            ->orderBy('name')
            ->limit(10)
            ->get()
            ->map(fn($cat) => [
                'id' => $cat->id,
                'name' => $cat->name,
                'slug' => $cat->slug,
                'icon' => self::CATEGORY_ICONS[$cat->name] ?? '🏷️',
            ])
            ->values()
            ->all();

        // 7. Hero Info & SEO
        $hero = match ($collection) {
            'flash-sale' => [
                'title' => 'Flash Sale & Special Offers',
                'subtitle' => 'Limited-time incredible discounts on authentic medicines, daily essentials, and personal care.',
                'badge' => '⚡ Up to 50% OFF',
                'color' => 'from-amber-600 to-rose-700',
            ],
            'best-picks' => [
                'title' => 'Best Picks & Top Rated Products',
                'subtitle' => 'Customer favorites, top-rated medicines, and doctor-recommended healthcare products.',
                'badge' => '⭐ Customer Favorites',
                'color' => 'from-teal-800 to-teal-950',
            ],
            default => [
                'title' => 'All Store Products',
                'subtitle' => 'Explore our full catalog of 100% genuine medicines, wellness products, and healthcare equipment.',
                'badge' => '📦 Complete Inventory',
                'color' => 'from-teal-800 to-teal-950',
            ],
        };

        $storeName = $tenant?->store_name ?: ($tenant?->name ?? 'MediMart');
        $seo = [
            'meta_title' => $hero['title'] . ' — ' . $storeName,
            'meta_description' => $hero['subtitle'],
        ];

        return Inertia::render('store/ProductList', [
            'collection' => $collection,
            'hero' => $hero,
            'products' => $formattedProducts->all(),
            'totalProducts' => $formattedProducts->count(),
            'categoryFacets' => $categoryFacets,
            'brandFacets' => $brandFacets,
            'categories' => $categories,
            'filters' => [
                'collection' => $collection,
                'categories' => $selectedCategories,
                'brands' => $selectedBrands,
                'min_price' => $minPrice,
                'max_price' => $maxPrice,
                'sort' => $sort,
                'rating' => $minRating,
                'discount' => $onlyDiscounted,
                'rx' => $rxFilter,
            ],
            'seo' => $seo,
        ]);
    }
}
