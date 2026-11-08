<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\Order;
use App\Models\OrderPayment;
use App\Models\OrderProduct;
use App\Models\Product;
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

    public function dashboard(Request $request): Response
    {
        $tenant = tenant() ?? Tenant::first();
        $tenantId = $tenant?->id ?? 1;

        $storeName = $tenant?->store_name ?: ($tenant?->name ?? 'MediMart');
        $storePhone = $tenant?->support_phone ?: ($tenant?->phone ?? '09610-016778');
        $storeEmail = $tenant?->support_email ?: ($tenant?->email ?? 'support@medimart.com');

        // Customer determination: authenticated guard customer or tenant primary customer fallback
        $customer = auth('customer')->user()
            ?? Customer::where('tenant_id', $tenantId)->with(['addresses'])->first();

        $customerData = [
            'id' => $customer?->id ?? 1,
            'name' => $customer ? trim("{$customer->first_name} {$customer->last_name}") : 'Sabbir Hossain',
            'first_name' => $customer?->first_name ?: 'Sabbir',
            'last_name' => $customer?->last_name ?: 'Hossain',
            'phone' => $customer?->phone ?: '+880 1926241906',
            'email' => $customer?->email ?: 'sabbir@example.com',
            'status' => $customer?->status ?: 'active',
            'membership' => '🏅 Verified Member',
            'avatar' => $customer?->avatar_url ?: 'https://loremflickr.com/200/200/portrait,man?lock=501',
            'joined_date' => $customer?->created_at ? $customer->created_at->format('M Y') : 'Oct 2026',
        ];

        // 1. Orders: Scoped to tenant and customer
        $ordersQuery = Order::where('tenant_id', $tenantId);
        if ($customer) {
            $ordersQuery->where(function ($q) use ($customer) {
                $q->where('customer_id', $customer->id)
                  ->orWhere('shipping_phone', $customer->phone);
            });
        }
        $orders = $ordersQuery->with(['products', 'payments'])
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
                        : Storage::disk('public')->url($order->prescription_image);
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

        // 2. Prescriptions: Aligned with TenantPrescription database table & Store Admin Rx Module
        $prescriptionsQuery = TenantPrescription::where('tenant_id', $tenantId);
        if ($customer) {
            $prescriptionsQuery->where(function ($q) use ($customer) {
                $q->where('customer_id', $customer->id)
                  ->orWhere('customer_phone', $customer->phone);
            });
        }
        $prescriptions = $prescriptionsQuery->with('order')
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

                $imageUrl = null;
                if (!empty($rx->prescription_image)) {
                    $imageUrl = str_starts_with($rx->prescription_image, 'http')
                        ? $rx->prescription_image
                        : Storage::disk('public')->url($rx->prescription_image);
                }

                return [
                    'id' => $rx->id,
                    'prescription_number' => $rx->prescription_number,
                    'status' => $rx->status,
                    'status_label' => $meta['label'],
                    'status_badge' => $meta['badge'],
                    'color' => $meta['color'],
                    'image_url' => $imageUrl,
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

        // 4. Saved Wishlist: Dynamic from wishlists table & catalog
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
                        'img' => $w->product->image_url ?? $w->product->featured_image ?? 'https://via.placeholder.com/300',
                    ];
                })
                ->values()
                ->all();
        }

        if (empty($wishlist)) {
            $wishlist = Product::where('tenant_id', $tenantId)
                ->where('status', 'active')
                ->take(4)
                ->get()
                ->map(fn($p) => [
                    'id' => $p->id,
                    'product_id' => $p->id,
                    'name' => $p->name,
                    'slug' => $p->slug,
                    'price' => (float) $p->price,
                    'img' => $p->image_url ?? $p->featured_image ?? 'https://via.placeholder.com/300',
                ])
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

        // 6. Active Store Coupons: Dynamic from coupons table
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

        // 7. Computed Customer Stats
        $activeRxCount = count(array_filter($prescriptions, fn($rx) => $rx['status'] === 'pending_review' || $rx['status'] === 'converted_to_order'));
        $totalSpent = round(array_sum(array_column($orders, 'total')), 2);

        $stats = [
            'total_orders' => count($orders),
            'total_spent' => $totalSpent,
            'active_prescriptions' => $activeRxCount,
            'wishlist_count' => count($wishlist),
            'saved_addresses_count' => count($addresses),
            'coupons_count' => count($coupons),
            'wallet_balance' => 320.00,
            'reward_points' => 1240,
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
}
