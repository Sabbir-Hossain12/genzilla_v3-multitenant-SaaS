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
use Illuminate\Support\Str;
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
        $startOfLastMonth = Carbon::now()->subMonth()->startOfMonth();
        $endOfLastMonth = Carbon::now()->subMonth()->endOfMonth();

        $revenue = (float) TenantInvoice::where('status', 'paid')->sum('total');
        $revenueMonth = (float) TenantInvoice::where('status', 'paid')->where('paid_at', '>=', $startOfMonth)->sum('total');
        $revenueLastMonth = (float) TenantInvoice::where('status', 'paid')->whereBetween('paid_at', [$startOfLastMonth, $endOfLastMonth])->sum('total');

        $tenantsCount = Tenant::count();
        $tenantsMonth = Tenant::where('created_at', '>=', $startOfMonth)->count();
        $tenantsLastMonth = Tenant::whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])->count();

        $revenueGrowth = $revenueLastMonth > 0
            ? round((($revenueMonth - $revenueLastMonth) / $revenueLastMonth) * 100, 1)
            : ($revenueMonth > 0 ? 100 : 0);

        $tenantsGrowth = $tenantsLastMonth > 0
            ? round((($tenantsMonth - $tenantsLastMonth) / $tenantsLastMonth) * 100, 1)
            : ($tenantsMonth > 0 ? 100 : 0);

        $stats = [
            'tenants' => $tenantsCount,
            'tenants_month' => $tenantsMonth,
            'tenants_growth' => $tenantsGrowth,
            'active_subscriptions' => TenantSubscription::whereIn('subscription_status', ['active', 'trialing'])->count(),
            'total_subscriptions' => TenantSubscription::count(),
            'revenue' => $revenue,
            'revenue_month' => $revenueMonth,
            'revenue_growth' => $revenueGrowth,
            'outstanding' => (float) TenantInvoice::whereIn('status', ['open', 'draft'])->sum('total'),
            'total_invoices' => TenantInvoice::count(),
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

        // Baseline target series for visual chart comparison
        $averageMonthly = $revenue > 0 ? round($revenue / 6, 2) : 2500.00;
        $targetSeries = $monthStarts->map(fn () => $averageMonthly)->values();

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
//                'count' => User::role($role->name)->count() ?? null,
            ]);

        $recentContacts = PlatformContact::latest()->limit(5)->get();
        $recentInvoices = TenantInvoice::with(['tenant', 'subscription.plan'])->latest()->limit(6)->get();
        $recentPosts = PlatformBlogPost::with('category')->latest()->limit(5)->get();

        $currency = optional(PlatformSetting::query()->first())->default_currency ?: 'USD';

        // Unified recent activities feed
        $activities = collect();

        foreach (Tenant::latest()->limit(4)->get() as $tenant) {
            $activities->push([
                'type' => 'tenant',
                'title' => $tenant->name,
                'desc' => 'joined as a new store',
                'time' => $tenant->created_at,
                'link' => route('admin.domains.index'),
            ]);
        }

        foreach (TenantInvoice::with('tenant')->latest()->limit(4)->get() as $inv) {
            $activities->push([
                'type' => 'invoice',
                'title' => $inv->invoice_number,
                'desc' => $inv->status === 'paid'
                    ? 'Payment of ' . $currency . ' ' . number_format($inv->total, 2) . ' received'
                    : 'Invoice issued for ' . ($inv->tenant->name ?? 'store'),
                'time' => $inv->paid_at ?? $inv->created_at,
                'link' => route('admin.invoices.show', $inv->id),
            ]);
        }

        foreach (PlatformContact::latest()->limit(4)->get() as $contact) {
            $activities->push([
                'type' => 'contact',
                'title' => $contact->name,
                'desc' => 'submitted an inquiry: "' . Str::limit($contact->subject ?: 'General inquiry', 28) . '"',
                'time' => $contact->created_at,
                'link' => route('admin.contacts.index'),
            ]);
        }

        foreach (PlatformBlogPost::latest()->limit(3)->get() as $post) {
            $activities->push([
                'type' => 'blog',
                'title' => Str::limit($post->title, 25),
                'desc' => $post->status == 1 ? 'published in blog' : 'saved as draft',
                'time' => $post->updated_at ?? $post->created_at,
                'link' => route('admin.blog.edit', $post->id),
            ]);
        }

        $activities = $activities->sortByDesc('time')->values()->take(6);

        $contentStats = [
            ['label' => 'Features', 'icon' => 'features', 'count' => PlatformFeature::count(), 'route' => 'admin.features.index', 'permission' => 'Manage Features'],
            ['label' => 'Steps', 'icon' => 'steps', 'count' => PlatformStep::count(), 'route' => 'admin.steps.index', 'permission' => 'Manage Steps'],
            ['label' => 'Use Cases', 'icon' => 'use_cases', 'count' => PlatformUseCase::count(), 'route' => 'admin.use-cases.index', 'permission' => 'Manage Use Cases'],
            ['label' => 'Store Demos', 'icon' => 'store_demos', 'count' => PlatformStoreDemo::count(), 'route' => 'admin.store-demos.index', 'permission' => 'Manage Store Demos'],
            ['label' => 'Testimonials', 'icon' => 'testimonials', 'count' => PlatformTestimonial::count(), 'route' => 'admin.testimonials.index', 'permission' => 'Manage Testimonials'],
            ['label' => 'FAQs', 'icon' => 'faqs', 'count' => PlatformFaq::count(), 'route' => 'admin.faqs.index', 'permission' => 'Manage Faqs'],
            ['label' => 'Pages', 'icon' => 'pages', 'count' => PlatformPage::count(), 'route' => 'admin.pages.index', 'permission' => 'Manage Pages'],
            ['label' => 'Blog Posts', 'icon' => 'blog', 'count' => PlatformBlogPost::count(), 'route' => 'admin.blog.index', 'permission' => 'Manage Blog'],
            ['label' => 'Categories', 'icon' => 'categories', 'count' => PlatformBlogCategory::count(), 'route' => 'admin.blog-categories.index', 'permission' => 'Manage Blog Categories'],
            ['label' => 'Plans', 'icon' => 'plans', 'count' => PlatformPlan::count(), 'route' => 'admin.plans.index', 'permission' => 'Manage Plans'],
        ];

        return view('platform_admin.pages.dashboard.dashboard', compact(
            'stats',
            'chartLabels',
            'revenueSeries',
            'targetSeries',
            'contactsSeries',
            'subscriberSeries',
            'subscriptionBreakdown',
            'roleBreakdown',
            'recentContacts',
            'recentInvoices',
            'recentPosts',
            'contentStats',
            'currency',
            'activities'
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
