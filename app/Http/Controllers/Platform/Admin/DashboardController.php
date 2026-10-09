<?php

namespace App\Http\Controllers\Platform\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlatformBlogCategory;
use App\Models\PlatformBlogPost;
use App\Models\PlatformContact;
use App\Models\PlatformFaq;
use App\Models\PlatformFeature;
use App\Models\PlatformNewsletterSubscriber;
use App\Models\PlatformPage;
use App\Models\PlatformPlan;
use App\Models\PlatformSetting;
use App\Models\PlatformStep;
use App\Models\PlatformStoreDemo;
use App\Models\PlatformTestimonial;
use App\Models\PlatformUseCase;
use App\Models\Tenant;
use App\Models\TenantDomain;
use App\Models\TenantInvoice;
use App\Models\TenantSubscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Role;

class DashboardController extends Controller
{
    /**
     * Display the platform admin dashboard overview.
     */
    public function index()
    {
        $monthStarts = Collection::make(range(5, 0))
            ->map(fn (int $i) => Carbon::now()->startOfMonth()->subMonths($i));

        $startOfMonth = Carbon::now()->startOfMonth();

        $stats = [
            'tenants' => Tenant::count(),
            'tenants_month' => Tenant::where('created_at', '>=', $startOfMonth)->count(),
            'active_subscriptions' => TenantSubscription::whereIn('subscription_status', ['active', 'trialing'])->count(),
            'revenue' => (float) TenantInvoice::where('status', 'paid')->sum('total'),
            'revenue_month' => (float) TenantInvoice::where('status', 'paid')->where('paid_at', '>=', $startOfMonth)->sum('total'),
            'outstanding' => (float) TenantInvoice::whereIn('status', ['open', 'draft'])->sum('total'),
            'contacts' => PlatformContact::count(),
            'contacts_month' => PlatformContact::where('created_at', '>=', $startOfMonth)->count(),
            'new_contacts' => PlatformContact::where('status', 'new')->count(),
            'subscribers' => PlatformNewsletterSubscriber::count(),
            'subscribers_month' => PlatformNewsletterSubscriber::where('created_at', '>=', $startOfMonth)->count(),
            'active_subscribers' => PlatformNewsletterSubscriber::where('is_subscribed', true)->count(),
            'admins' => User::count(),
            'active_admins' => User::where('status', 1)->count(),
            'blog_posts' => PlatformBlogPost::count(),
            'published_posts' => PlatformBlogPost::where('status', 1)->count(),
            'draft_posts' => PlatformBlogPost::where('status', 0)->count(),
            'domains' => TenantDomain::count(),
            'custom_domains' => TenantDomain::where('is_custom', true)->count(),
            'roles' => Role::count(),
            'plans' => PlatformPlan::count(),
        ];

        $chartLabels = $monthStarts->map(fn (Carbon $month) => $month->format('M Y'))->values();

        $revenueSeries = $monthStarts->map(fn (Carbon $month) => (float) TenantInvoice::where('status', 'paid')
            ->whereBetween('paid_at', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
            ->sum('total'))->values();

        $contactsSeries = $monthStarts->map(fn (Carbon $month) => PlatformContact::whereBetween('created_at', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])->count())->values();

        $subscriberSeries = $monthStarts->map(fn (Carbon $month) => PlatformNewsletterSubscriber::whereBetween('created_at', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])->count())->values();

        $subscriptionBreakdown = TenantSubscription::query()
            ->selectRaw('subscription_status, count(*) as total')
            ->groupBy('subscription_status')
            ->pluck('total', 'subscription_status');

        $roleBreakdown = Role::query()
            ->orderBy('name')
            ->get()
            ->map(fn (Role $role) => [
                'name' => $role->name,
                'count' => User::role($role->name)->count(),
            ]);

        $recentContacts = PlatformContact::latest()->limit(5)->get();
        $recentInvoices = TenantInvoice::with('tenant')->latest()->limit(5)->get();
        $recentPosts = PlatformBlogPost::with('category')->latest()->limit(5)->get();

        $currency = optional(PlatformSetting::query()->first())->default_currency ?: 'USD';

        $contentStats = [
            ['label' => 'Features', 'icon' => 'fa-list-check', 'count' => PlatformFeature::count(), 'route' => 'admin.features.index', 'permission' => 'Manage Features'],
            ['label' => 'Steps', 'icon' => 'fa-shoe-prints', 'count' => PlatformStep::count(), 'route' => 'admin.steps.index', 'permission' => 'Manage Steps'],
            ['label' => 'Use Cases', 'icon' => 'fa-bullseye', 'count' => PlatformUseCase::count(), 'route' => 'admin.use-cases.index', 'permission' => 'Manage Use Cases'],
            ['label' => 'Store Demos', 'icon' => 'fa-window-restore', 'count' => PlatformStoreDemo::count(), 'route' => 'admin.store-demos.index', 'permission' => 'Manage Store Demos'],
            ['label' => 'Testimonials', 'icon' => 'fa-quote-right', 'count' => PlatformTestimonial::count(), 'route' => 'admin.testimonials.index', 'permission' => 'Manage Testimonials'],
            ['label' => 'FAQs', 'icon' => 'fa-circle-question', 'count' => PlatformFaq::count(), 'route' => 'admin.faqs.index', 'permission' => 'Manage Faqs'],
            ['label' => 'Pages', 'icon' => 'fa-file-lines', 'count' => PlatformPage::count(), 'route' => 'admin.pages.index', 'permission' => 'Manage Pages'],
            ['label' => 'Blog Posts', 'icon' => 'fa-newspaper', 'count' => PlatformBlogPost::count(), 'route' => 'admin.blog.index', 'permission' => 'Manage Blog'],
            ['label' => 'Categories', 'icon' => 'fa-tags', 'count' => PlatformBlogCategory::count(), 'route' => 'admin.blog-categories.index', 'permission' => 'Manage Blog Categories'],
            ['label' => 'Plans', 'icon' => 'fa-layer-group', 'count' => PlatformPlan::count(), 'route' => 'admin.plans.index', 'permission' => 'Manage Plans'],
        ];

        return view('platform_admin.pages.dashboard.dashboard', compact(
            'stats',
            'chartLabels',
            'revenueSeries',
            'contactsSeries',
            'subscriberSeries',
            'subscriptionBreakdown',
            'roleBreakdown',
            'recentContacts',
            'recentInvoices',
            'recentPosts',
            'contentStats',
            'currency',
        ));
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        //
    }

    public function show(string $id)
    {
        //
    }

    public function edit(string $id)
    {
        //
    }

    public function update(Request $request, string $id)
    {
        //
    }

    public function destroy(string $id)
    {
        //
    }
}
