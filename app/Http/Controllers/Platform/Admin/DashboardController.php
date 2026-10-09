<?php

namespace App\Http\Controllers\Platform\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlatformBlogPost;
use App\Models\PlatformContact;
use App\Models\PlatformNewsletterSubscriber;
use App\Models\PlatformPlan;
use App\Models\PlatformSetting;
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
