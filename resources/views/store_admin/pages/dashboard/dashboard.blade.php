@extends('platform_admin.layout.master')

@push('title')
    Dashboard Overview
@endpush

@push('css')
@endpush

@section('contents')
    @php
        $hour = (int) now()->format('H');
        $greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
        $userName = Auth::user() ? (explode(' ', trim(Auth::user()->name))[0] ?? 'Admin') : 'Admin';

        $totalBilled = $stats['revenue'] + $stats['outstanding'];
        $paidRatio = $totalBilled > 0 ? min(100, round(($stats['revenue'] / $totalBilled) * 100)) : ($stats['revenue'] > 0 ? 100 : 0);

        $tenantActiveRatio = $stats['tenants'] > 0 ? min(100, round(($stats['active_subscriptions'] / $stats['tenants']) * 100)) : 0;

        $subTotalCount = max(1, $stats['total_subscriptions']);
        $subActiveRatio = min(100, round(($stats['active_subscriptions'] / $subTotalCount) * 100));

        $resolvedContacts = max(0, $stats['contacts'] - $stats['new_contacts']);
        $contactsResolvedRatio = $stats['contacts'] > 0 ? min(100, round(($resolvedContacts / $stats['contacts']) * 100)) : 100;
    @endphp

    <!-- Heading row -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl md:text-2xl font-extrabold text-ink dark:text-white flex items-center gap-2">
                {{ $greeting }}, {{ $userName }} <span>👋</span>
            </h1>
            <p class="text-xs md:text-sm text-slate-400 mt-1">
                Here's what's happening across your platform today &bull; {{ now()->format('l, j F Y') }}
            </p>
        </div>
        <div class="flex items-center gap-2.5 shrink-0">
            @can('Manage Invoices')
                <a href="{{ route('admin.invoices.index') }}"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold
                          bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200
                          border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-750
                          transition-colors shadow-sm">
                    <svg class="w-3.5 h-3.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/>
                    </svg>
                    Invoices
                </a>
            @endcan
            @can('Manage Plans')
                <a href="{{ route('admin.plans.index') }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-semibold
                          bg-primary hover:bg-primarydark text-white
                          transition-colors shadow-sm shadow-primary/30">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                    </svg>
                    Manage Plans
                </a>
            @endcan
        </div>
    </div>

    <!-- ── STAT CARDS ── -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">

        <!-- Revenue -->
        <div class="card p-5">
            <div class="flex items-start justify-between mb-4">
                <div class="w-10 h-10 bg-primarylt dark:bg-primary/15 rounded-xl grid place-items-center">
                    <svg class="w-5 h-5 text-primary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                    </svg>
                </div>
                @if($stats['revenue_growth'] > 0)
                    <span class="flex items-center gap-1 text-xs font-semibold text-accent">
                        <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <line x1="7" y1="17" x2="17" y2="7"/><polyline points="7 7 17 7 17 17"/>
                        </svg>
                        +{{ $stats['revenue_growth'] }}%
                    </span>
                @elseif($stats['revenue_growth'] < 0)
                    <span class="flex items-center gap-1 text-xs font-semibold text-red-500">
                        <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <line x1="7" y1="7" x2="17" y2="17"/><polyline points="17 7 17 17 7 17"/>
                        </svg>
                        {{ $stats['revenue_growth'] }}%
                    </span>
                @else
                    <span class="text-xs font-semibold text-slate-400">
                        +{{ $currency }} {{ number_format($stats['revenue_month'], 0) }} mo
                    </span>
                @endif
            </div>
            <p class="text-2xl font-extrabold text-ink dark:text-white tabular-nums">
                {{ $currency }} {{ number_format($stats['revenue'], 2) }}
            </p>
            <p class="text-xs text-slate-400 mt-1 font-medium">Total Paid Revenue</p>
            <div class="mt-3.5 h-1.5 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                <div class="h-full bg-primary rounded-full transition-all duration-700" style="width: {{ $paidRatio }}%"></div>
            </div>
            <p class="text-[11px] text-slate-400 mt-1.5 truncate">
                @if($stats['outstanding'] > 0)
                    <span class="text-amber-500 font-medium">{{ $currency }} {{ number_format($stats['outstanding'], 2) }}</span> outstanding
                @else
                    100% collected &bull; No outstanding invoices
                @endif
            </p>
        </div>

        <!-- Tenants / Stores -->
        <div class="card p-5">
            <div class="flex items-start justify-between mb-4">
                <div class="w-10 h-10 bg-accent/10 rounded-xl grid place-items-center">
                    <svg class="w-5 h-5 text-accent" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>
                    </svg>
                </div>
                <span class="flex items-center gap-1 text-xs font-semibold text-accent">
                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <line x1="7" y1="17" x2="17" y2="7"/><polyline points="7 7 17 7 17 17"/>
                    </svg>
                    +{{ $stats['tenants_month'] }} this mo
                </span>
            </div>
            <p class="text-2xl font-extrabold text-ink dark:text-white tabular-nums">
                {{ number_format($stats['tenants']) }}
            </p>
            <p class="text-xs text-slate-400 mt-1 font-medium">Total Stores / Tenants</p>
            <div class="mt-3.5 h-1.5 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                <div class="h-full bg-accent rounded-full transition-all duration-700" style="width: {{ $tenantActiveRatio }}%"></div>
            </div>
            <p class="text-[11px] text-slate-400 mt-1.5 truncate">
                {{ number_format($stats['active_subscriptions']) }} on active subscription ({{ $tenantActiveRatio }}%)
            </p>
        </div>

        <!-- Active Subscriptions -->
        <div class="card p-5">
            <div class="flex items-start justify-between mb-4">
                <div class="w-10 h-10 bg-violet-100 dark:bg-violet-500/10 rounded-xl grid place-items-center">
                    <svg class="w-5 h-5 text-violet-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/>
                    </svg>
                </div>
                <span class="text-xs font-semibold text-violet-600 dark:text-violet-400">
                    {{ $stats['plans'] }} Tiers
                </span>
            </div>
            <p class="text-2xl font-extrabold text-ink dark:text-white tabular-nums">
                {{ number_format($stats['active_subscriptions']) }}
            </p>
            <p class="text-xs text-slate-400 mt-1 font-medium">Active Subscriptions</p>
            <div class="mt-3.5 h-1.5 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                <div class="h-full bg-violet-500 rounded-full transition-all duration-700" style="width: {{ $subActiveRatio }}%"></div>
            </div>
            <p class="text-[11px] text-slate-400 mt-1.5 truncate">
                {{ $stats['total_subscriptions'] }} total subscriptions tracked
            </p>
        </div>

        <!-- Support Inquiries -->
        <div class="card p-5">
            <div class="flex items-start justify-between mb-4">
                <div class="w-10 h-10 bg-amber-100 dark:bg-amber-500/10 rounded-xl grid place-items-center">
                    <svg class="w-5 h-5 text-amber-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                    </svg>
                </div>
                @if($stats['new_contacts'] > 0)
                    <span class="badge-amber font-semibold">
                        {{ $stats['new_contacts'] }} New
                    </span>
                @else
                    <span class="text-xs font-semibold text-accent">
                        All resolved
                    </span>
                @endif
            </div>
            <p class="text-2xl font-extrabold text-ink dark:text-white tabular-nums">
                {{ number_format($stats['contacts']) }}
            </p>
            <p class="text-xs text-slate-400 mt-1 font-medium">Support Inquiries</p>
            <div class="mt-3.5 h-1.5 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                <div class="h-full bg-amber-500 rounded-full transition-all duration-700" style="width: {{ $contactsResolvedRatio }}%"></div>
            </div>
            <p class="text-[11px] text-slate-400 mt-1.5 truncate">
                {{ number_format($stats['subscribers']) }} newsletter subscribers
            </p>
        </div>
    </div>

    <!-- ── CHART + ACTIVITY ── -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

        <!-- Revenue Chart -->
        <div class="card lg:col-span-2 p-5 flex flex-col justify-between">
            <div class="flex items-center justify-between mb-4 gap-4 flex-wrap">
                <div>
                    <h2 class="font-bold text-ink dark:text-white">Revenue Overview</h2>
                    <p class="text-xs text-slate-400 mt-0.5">Monthly platform performance &mdash; {{ now()->year }}</p>
                </div>
                <div class="flex items-center gap-3 shrink-0">
                    <span class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400 font-medium">
                        <span class="w-3 h-1.5 rounded-full bg-primary inline-block"></span>Revenue
                    </span>
                    <span class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400 font-medium">
                        <span class="w-3 h-0.5 rounded-full bg-accent inline-block border-t-2 border-dashed border-accent"></span>Target
                    </span>
                </div>
            </div>

            <div class="w-full flex-1 min-h-[220px]">
                <div id="revenueChart" class="w-full h-full"></div>
            </div>
        </div>

        <!-- Activity -->
        <div class="card p-5 flex flex-col">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-bold text-ink dark:text-white">Recent Activity</h2>
                @can('Manage Contacts')
                    <a href="{{ route('admin.contacts.index') }}" class="text-xs font-semibold text-primary hover:text-primarydark transition-colors">
                        View all
                    </a>
                @endcan
            </div>

            <div class="space-y-3.5 flex-1 overflow-y-auto max-h-[320px]">
                @forelse($activities as $act)
                    <a href="{{ $act['link'] }}" class="flex items-start gap-3 p-2 -mx-2 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-colors group">
                        @if($act['type'] === 'tenant')
                            <div class="w-8 h-8 shrink-0 bg-accent/10 rounded-full grid place-items-center text-accent">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                                </svg>
                            </div>
                        @elseif($act['type'] === 'invoice')
                            <div class="w-8 h-8 shrink-0 bg-primarylt dark:bg-primary/15 rounded-full grid place-items-center text-primary">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/>
                                </svg>
                            </div>
                        @elseif($act['type'] === 'contact')
                            <div class="w-8 h-8 shrink-0 bg-amber-100 dark:bg-amber-500/10 rounded-full grid place-items-center text-amber-500">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                                </svg>
                            </div>
                        @else
                            <div class="w-8 h-8 shrink-0 bg-violet-100 dark:bg-violet-500/10 rounded-full grid place-items-center text-violet-500">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/>
                                </svg>
                            </div>
                        @endif

                        <div class="min-w-0 flex-1">
                            <p class="text-xs md:text-sm text-ink dark:text-slate-200 leading-snug line-clamp-2">
                                <span class="font-semibold text-primary group-hover:underline">{{ $act['title'] }}</span>
                                {{ $act['desc'] }}
                            </p>
                            <p class="text-[11px] text-slate-400 mt-0.5">
                                {{ $act['time'] ? $act['time']->diffForHumans() : 'Recently' }}
                            </p>
                        </div>
                    </a>
                @empty
                    <div class="h-full flex flex-col items-center justify-center py-8 text-center text-slate-400">
                        <svg class="w-8 h-8 text-slate-300 dark:text-slate-700 mb-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                        </svg>
                        <p class="text-xs font-semibold text-slate-500 dark:text-slate-400">No activity yet</p>
                        <p class="text-[11px] text-slate-400 mt-0.5">Recent system events will appear here</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- ── ORDERS / INVOICES TABLE ── -->
    <div class="card overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100 dark:border-slate-800 gap-4 flex-wrap">
            <div>
                <h2 class="font-bold text-ink dark:text-white">Recent Invoices & Orders</h2>
                <p class="text-xs text-slate-400 mt-0.5">Latest subscription transactions from tenant stores</p>
            </div>
            <div class="flex items-center gap-2">
                @can('Manage Invoices')
                    <a href="{{ route('admin.invoices.index') }}"
                       class="inline-flex items-center gap-1.5 text-xs font-semibold text-primary hover:text-primarydark transition-colors px-3 py-1.5 rounded-lg border border-primary/20 hover:bg-primarylt dark:hover:bg-primary/10">
                        View All Invoices
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                            <polyline points="9 18 15 12 9 6"/>
                        </svg>
                    </a>
                @endcan
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                <tr class="border-b border-slate-100 dark:border-slate-800 bg-slate-50/80 dark:bg-slate-800/40">
                    <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400">Invoice ID</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400">Tenant / Store</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 hidden md:table-cell">Plan Tier</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 hidden lg:table-cell">Date</th>
                    <th class="text-right px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400">Amount</th>
                    <th class="text-center px-5 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400">Status</th>
                    <th class="text-right px-5 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400">Action</th>
                </tr>
                </thead>
                <tbody>
                @forelse($recentInvoices as $invoice)
                    <tr class="trow">
                        <td class="px-5 py-3.5 text-xs font-bold text-primary">
                            @can('Manage Invoices')
                                <a href="{{ route('admin.invoices.show', $invoice->id) }}" class="hover:underline">
                                    {{ $invoice->invoice_number }}
                                </a>
                            @else
                                {{ $invoice->invoice_number }}
                            @endcan
                        </td>
                        <td class="px-4 py-3.5">
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-full bg-primary/10 text-primary font-bold text-xs grid place-items-center shrink-0">
                                    {{ strtoupper(substr($invoice->tenant->name ?? 'T', 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="font-semibold text-ink dark:text-slate-200 truncate">
                                        {{ $invoice->tenant->name ?? 'Store #' . $invoice->tenant_id }}
                                    </p>
                                    @if(optional($invoice->tenant)->subdomain)
                                        <p class="text-[11px] text-slate-400 truncate">
                                            {{ $invoice->tenant->subdomain }}.{{ config('app.base_domain') }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3.5 text-slate-500 dark:text-slate-400 text-xs hidden md:table-cell">
                            {{ $invoice->subscription?->plan?->name ?? 'Subscription Plan' }}
                        </td>
                        <td class="px-4 py-3.5 text-slate-400 text-xs hidden lg:table-cell">
                            {{ ($invoice->paid_at ?? $invoice->created_at)->format('M d, Y') }}
                        </td>
                        <td class="px-4 py-3.5 text-right font-bold text-ink dark:text-slate-200 tabular-nums">
                            {{ $invoice->currency ?: $currency }} {{ number_format($invoice->total, 2) }}
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            @if($invoice->status === 'paid')
                                <span class="badge-green">&bull; Paid</span>
                            @elseif($invoice->status === 'open')
                                <span class="badge-amber">&bull; Open</span>
                            @elseif($invoice->status === 'draft')
                                <span class="badge-slate">&bull; Draft</span>
                            @else
                                <span class="badge-red">&bull; {{ ucfirst($invoice->status) }}</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-right">
                            @can('Manage Invoices')
                                <a href="{{ route('admin.invoices.show', $invoice->id) }}"
                                   class="text-xs font-semibold text-primary hover:text-primarydark transition-colors">
                                    View
                                </a>
                            @else
                                <span class="text-xs text-slate-400">&mdash;</span>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-5 py-10 text-center text-slate-400">
                            <div class="max-w-xs mx-auto">
                                <svg class="w-8 h-8 mx-auto text-slate-300 dark:text-slate-700 mb-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                    <rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/>
                                </svg>
                                <p class="text-sm font-semibold text-slate-600 dark:text-slate-400">No invoices yet</p>
                                <p class="text-xs text-slate-400 mt-0.5">Invoices generated by tenant subscriptions will appear here automatically.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <!-- Table Footer -->
        <div class="px-5 py-3.5 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs text-slate-400 gap-4 flex-wrap">
            <span>
                Showing <strong class="text-ink dark:text-slate-300">{{ $recentInvoices->count() }}</strong>
                of <strong class="text-ink dark:text-slate-300">{{ number_format($stats['total_invoices']) }}</strong> recorded invoices
            </span>
            @can('Manage Invoices')
                <a href="{{ route('admin.invoices.index') }}" class="font-medium text-primary hover:underline">
                    Manage all invoices &rarr;
                </a>
            @endcan
        </div>
    </div>

    <!-- ── SUBSCRIPTION HEALTH & PLATFORM CMS OVERVIEW ── -->
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-4">

        <!-- Subscription Health Breakdown -->
        <div class="card p-5 flex flex-col justify-between">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="font-bold text-ink dark:text-white">Subscription Health</h2>
                    <p class="text-xs text-slate-400 mt-0.5">Status breakdown across all tenants</p>
                </div>
                <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-primarylt dark:bg-primary/15 text-primary">
                    {{ number_format($stats['total_subscriptions']) }} Total
                </span>
            </div>

            @php
                $subStatusConfig = [
                    'active' => ['label' => 'Active', 'badge' => 'badge-green', 'bar' => 'bg-accent'],
                    'trialing' => ['label' => 'Trialing', 'badge' => 'badge-slate', 'bar' => 'bg-primary'],
                    'past_due' => ['label' => 'Past Due', 'badge' => 'badge-amber', 'bar' => 'bg-amber-500'],
                    'expired' => ['label' => 'Expired', 'badge' => 'badge-slate', 'bar' => 'bg-slate-400'],
                    'cancelled' => ['label' => 'Cancelled', 'badge' => 'badge-red', 'bar' => 'bg-red-500'],
                ];
                $totalSubs = max(1, (int) $subscriptionBreakdown->sum());
            @endphp

            @if($subscriptionBreakdown->isNotEmpty() && $subscriptionBreakdown->sum() > 0)
                <div class="space-y-3.5 my-auto">
                    @foreach($subStatusConfig as $statusKey => $cfg)
                        @php
                            $statusCount = (int) ($subscriptionBreakdown[$statusKey] ?? 0);
                            $statusPct = round(($statusCount / $totalSubs) * 100);
                        @endphp
                        <div>
                            <div class="flex items-center justify-between text-xs mb-1">
                                <span class="font-medium text-slate-600 dark:text-slate-300">{{ $cfg['label'] }}</span>
                                <span class="font-semibold text-ink dark:text-white tabular-nums">
                                    {{ $statusCount }} <span class="text-slate-400 font-normal">({{ $statusPct }}%)</span>
                                </span>
                            </div>
                            <div class="h-1.5 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                                <div class="h-full {{ $cfg['bar'] }} rounded-full transition-all duration-700" style="width: {{ $statusPct }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="py-8 text-center text-slate-400 my-auto">
                    <svg class="w-7 h-7 mx-auto text-slate-300 dark:text-slate-700 mb-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <circle cx="12" cy="12" r="10"/><line x1="8" y1="12" x2="16" y2="12"/>
                    </svg>
                    <p class="text-xs font-semibold text-slate-500 dark:text-slate-400">No subscriptions recorded</p>
                    <p class="text-[11px] text-slate-400 mt-0.5">Assign subscriptions to tenants in the Subscriptions module</p>
                </div>
            @endif

            <div class="pt-3 border-t border-slate-100 dark:border-slate-800 mt-4">
                @can('Manage Subscriptions')
                    <a href="{{ route('admin.subscriptions.index') }}" class="text-xs font-semibold text-primary hover:text-primarydark transition-colors inline-flex items-center gap-1">
                        Manage Tenant Subscriptions &rarr;
                    </a>
                @endcan
            </div>
        </div>

        <!-- Platform CMS & Management Hub -->
        <div class="card xl:col-span-2 p-5 flex flex-col justify-between">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="font-bold text-ink dark:text-white">Platform Content &amp; CMS Hub</h2>
                    <p class="text-xs text-slate-400 mt-0.5">Quick access to manage marketing and public platform components</p>
                </div>
                @can('Manage Blog')
                    <a href="{{ route('admin.blog.index') }}" class="text-xs font-semibold text-primary hover:text-primarydark transition-colors">
                        Blog Center
                    </a>
                @endcan
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-3 my-auto">
                @foreach($contentStats as $item)
                    @php
                        $isAllowed = Auth::user() && Auth::user()->can($item['permission']);
                    @endphp
                    @if($isAllowed)
                        <a href="{{ route($item['route']) }}"
                           class="group p-3 rounded-xl border border-slate-200/80 dark:border-slate-800
                                  bg-slate-50/50 dark:bg-slate-800/40 hover:bg-primarylt/60 dark:hover:bg-primary/10
                                  hover:border-primary/40 dark:hover:border-primary/30 transition-all duration-150 flex flex-col justify-between">
                    @else
                        <div class="p-3 rounded-xl border border-slate-200/60 dark:border-slate-800 bg-slate-50/30 dark:bg-slate-800/20 opacity-70 flex flex-col justify-between">
                    @endif
                        <div class="flex items-center justify-between mb-2">
                            <span class="w-7 h-7 rounded-lg bg-primary/10 text-primary grid place-items-center">
                                @if($item['icon'] === 'features')
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                                @elseif($item['icon'] === 'steps')
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="13 17 18 12 13 7"/><polyline points="6 17 11 12 6 7"/></svg>
                                @elseif($item['icon'] === 'use_cases')
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/></svg>
                                @elseif($item['icon'] === 'store_demos')
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                                @elseif($item['icon'] === 'testimonials')
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                                @elseif($item['icon'] === 'faqs')
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                                @elseif($item['icon'] === 'pages')
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                @elseif($item['icon'] === 'blog')
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 20H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h10l6 6v10a2 2 0 0 1-2 2z"/><line x1="14" y1="2" x2="14" y2="8"/><line x1="14" y1="8" x2="20" y2="8"/></svg>
                                @elseif($item['icon'] === 'categories')
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
                                @else
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                                @endif
                            </span>
                            <span class="text-xs font-extrabold text-ink dark:text-white tabular-nums group-hover:text-primary transition-colors">
                                {{ number_format($item['count']) }}
                            </span>
                        </div>
                        <span class="text-[11px] font-medium text-slate-500 dark:text-slate-400 truncate">
                            {{ $item['label'] }}
                        </span>
                    @if($isAllowed)
                        </a>
                    @else
                        </div>
                    @endif
                @endforeach
            </div>

            <div class="pt-3 border-t border-slate-100 dark:border-slate-800 mt-4 flex items-center justify-between text-xs text-slate-400">
                <span>Manage landing page blocks, testimonials &amp; SEO pages</span>
                @can('General Setting')
                    <a href="{{ route('admin.settings.index') }}" class="font-medium text-primary hover:underline">
                        Platform Settings &rarr;
                    </a>
                @endcan
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script src="{{ asset('backend/assets/libs/apexcharts/apexcharts.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const chartLabels = @json($chartLabels);
            const revenueSeries = @json($revenueSeries);
            const targetSeries = @json($targetSeries);
            const currency = @json($currency);

            const isDark = document.documentElement.classList.contains('dark');
            const revenueEl = document.querySelector('#revenueChart');

            if (revenueEl && typeof ApexCharts !== 'undefined') {
                const options = {
                    series: [
                        {
                            name: 'Revenue',
                            type: 'area',
                            data: revenueSeries
                        },
                        {
                            name: 'Target',
                            type: 'line',
                            data: targetSeries
                        }
                    ],
                    chart: {
                        height: 220,
                        type: 'line',
                        toolbar: { show: false },
                        fontFamily: 'Inter, sans-serif',
                        zoom: { enabled: false },
                        parentHeightOffset: 0
                    },
                    colors: ['#4f46e5', '#10b981'],
                    stroke: {
                        curve: 'smooth',
                        width: [2.5, 2],
                        dashArray: [0, 5]
                    },
                    fill: {
                        type: ['gradient', 'solid'],
                        gradient: {
                            shadeIntensity: 1,
                            opacityFrom: 0.35,
                            opacityTo: 0.05,
                            stops: [0, 95, 100]
                        }
                    },
                    markers: {
                        size: [4, 0],
                        colors: ['#4f46e5'],
                        strokeColors: '#ffffff',
                        strokeWidth: 2,
                        hover: { size: 6 }
                    },
                    grid: {
                        borderColor: isDark ? 'rgba(148, 163, 184, 0.1)' : 'rgba(226, 232, 240, 0.8)',
                        strokeDashArray: 4,
                        padding: {
                            top: 10,
                            right: 10,
                            bottom: 0,
                            left: 10
                        }
                    },
                    xaxis: {
                        categories: chartLabels,
                        axisBorder: { show: false },
                        axisTicks: { show: false },
                        labels: {
                            style: {
                                colors: isDark ? '#94a3b8' : '#64748b',
                                fontSize: '11px',
                                fontWeight: 500
                            }
                        }
                    },
                    yaxis: {
                        labels: {
                            formatter: function (val) {
                                if (val >= 1000) {
                                    return currency + ' ' + (val / 1000).toFixed(1) + 'k';
                                }
                                return currency + ' ' + Number(val).toFixed(0);
                            },
                            style: {
                                colors: isDark ? '#94a3b8' : '#64748b',
                                fontSize: '11px',
                                fontWeight: 500
                            }
                        }
                    },
                    dataLabels: { enabled: false },
                    tooltip: {
                        theme: isDark ? 'dark' : 'light',
                        y: {
                            formatter: function (val) {
                                return currency + ' ' + Number(val).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                            }
                        }
                    },
                    legend: { show: false },
                    noData: {
                        text: 'No revenue records available',
                        style: {
                            color: isDark ? '#94a3b8' : '#64748b',
                            fontSize: '13px'
                        }
                    }
                };

                const chart = new ApexCharts(revenueEl, options);
                chart.render();

                // Listen for theme toggle to update grid and label colors dynamically
                const observer = new MutationObserver(function () {
                    const darkNow = document.documentElement.classList.contains('dark');
                    chart.updateOptions({
                        grid: {
                            borderColor: darkNow ? 'rgba(148, 163, 184, 0.1)' : 'rgba(226, 232, 240, 0.8)'
                        },
                        xaxis: {
                            labels: {
                                style: { colors: darkNow ? '#94a3b8' : '#64748b' }
                            }
                        },
                        yaxis: {
                            labels: {
                                style: { colors: darkNow ? '#94a3b8' : '#64748b' }
                            }
                        },
                        tooltip: {
                            theme: darkNow ? 'dark' : 'light'
                        }
                    });
                });
                observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
            }
        });
    </script>
@endpush
