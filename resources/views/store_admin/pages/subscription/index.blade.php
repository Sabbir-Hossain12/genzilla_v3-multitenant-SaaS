@extends('store_admin.layout.master')

@section('title', 'Store Subscription & Billing - ' . ($tenant->store_name ?? 'Store Admin'))

@section('contents')
<div class="space-y-8 pb-10">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-400 mb-1">
                <i class="fa-solid fa-shield-halved"></i>
                <span>Settings & Security &bull; Billing Management</span>
            </div>
            <h1 class="text-2xl font-extrabold tracking-tight text-ink dark:text-white">Store Subscription & Billing</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Manage your pharmacy store tier, monitor resource quotas, view domain status, and track billing invoices.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="#plans-section"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-md shadow-indigo-600/20 transition-all hover:-translate-y-0.5 cursor-pointer">
                <i class="fa-solid fa-crown text-amber-300"></i>
                <span>Upgrade / Change Plan</span>
            </a>
            <a href="{{ route('store.admin.domains.index') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 font-semibold text-xs shadow-sm transition">
                <i class="fa-solid fa-globe text-slate-400"></i>
                <span>Domain Settings</span>
            </a>
        </div>
    </div>

    {{-- Feedback Alerts --}}
    @if(session('success'))
        <div class="rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 p-4 text-sm font-medium text-emerald-800 dark:text-emerald-200 border border-emerald-200 dark:border-emerald-800 flex items-center justify-between gap-3 shadow-sm">
            <div class="flex items-center gap-2.5">
                <i class="fa-solid fa-circle-check text-emerald-600 dark:text-emerald-400 text-base shrink-0"></i>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-800 dark:text-emerald-300 font-bold text-lg">&times;</button>
        </div>
    @endif

    @if(session('error'))
        <div class="rounded-2xl bg-red-50 dark:bg-red-950/40 p-4 text-sm font-medium text-red-800 dark:text-red-200 border border-red-200 dark:border-red-800 flex items-center justify-between gap-3 shadow-sm">
            <div class="flex items-center gap-2.5">
                <i class="fa-solid fa-circle-exclamation text-red-600 dark:text-red-400 text-base shrink-0"></i>
                <span>{{ session('error') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-red-600 hover:text-red-800 dark:text-red-300 font-bold text-lg">&times;</button>
        </div>
    @endif

    @if ($errors->any())
        <div class="flex items-start gap-3 bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/30 text-red-700 dark:text-red-400 rounded-2xl px-4 py-3.5 text-sm shadow-sm">
            <i class="fa-solid fa-circle-exclamation text-base mt-0.5 shrink-0"></i>
            <ul class="flex-1 space-y-0.5 list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Top Section: Current Plan Overview & Integrated Domain Status --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        {{-- 1. Current Plan Overview Card (7 cols) --}}
        <div class="lg:col-span-7 rounded-3xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm overflow-hidden flex flex-col justify-between relative">
            <div class="absolute -right-20 -top-20 w-64 h-64 bg-indigo-50 dark:bg-indigo-950/30 rounded-full blur-3xl pointer-events-none"></div>

            <div class="p-6 md:p-7 relative z-10">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-indigo-50 dark:bg-indigo-950/50 text-indigo-700 dark:text-indigo-400 border border-indigo-200/60 dark:border-indigo-900/50 mb-3">
                            <i class="fa-solid fa-shield-heart text-indigo-500"></i> Current Store Subscription
                        </span>
                        <h2 class="text-2xl font-extrabold text-ink dark:text-white tracking-tight">
                            {{ $currentPlan->name ?? 'Free Trial Plan' }}
                        </h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                            Assigned to <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $tenant->store_name }}</span> &bull; ID: #{{ $tenant->id }}
                        </p>
                    </div>

                    {{-- Dynamic Status Badge --}}
                    <div>
                        @if($statusColor === 'emerald')
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 shadow-xs">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                <span>{{ $statusLabel }}</span>
                            </span>
                        @elseif($statusColor === 'amber')
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300 border border-amber-200 dark:border-amber-800 shadow-xs">
                                <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                <span>{{ $statusLabel }} ({{ $daysRemaining }}d left)</span>
                            </span>
                        @elseif($statusColor === 'blue')
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-blue-50 text-blue-700 dark:bg-blue-500/15 dark:text-blue-300 border border-blue-200 dark:border-blue-800 shadow-xs">
                                <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                                <span>{{ $statusLabel }} @if($daysRemaining !== null)({{ $daysRemaining }}d left)@endif</span>
                            </span>
                        @elseif($statusColor === 'rose' || $statusColor === 'red')
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-rose-50 text-rose-700 dark:bg-rose-500/15 dark:text-rose-300 border border-rose-200 dark:border-rose-800 shadow-xs">
                                <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                                <span>{{ $statusLabel }}</span>
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 border border-slate-200 dark:border-slate-700 shadow-xs">
                                <span>{{ $statusLabel }}</span>
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Pricing & Cycle Meta --}}
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 mt-6 pt-5 border-t border-slate-100 dark:border-slate-800">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Current Cost</p>
                        <p class="text-xl font-extrabold text-ink dark:text-white mt-1">
                            ${{ number_format($currentPrice, 2) }}
                            <span class="text-xs font-medium text-slate-400">/ {{ $billingInterval === 'yearly' ? 'year' : 'month' }}</span>
                        </p>
                    </div>

                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Billing Cycle</p>
                        <div class="mt-1 flex items-center gap-1.5">
                            <span class="font-bold text-sm text-slate-800 dark:text-slate-200 capitalize">
                                {{ $billingInterval }}
                            </span>
                            @if($billingInterval === 'yearly')
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-extrabold bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300">
                                    Saved 15%
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="col-span-2 sm:col-span-1">
                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">
                            {{ $rawStatus === 'trialing' ? 'Trial Expiry' : ($canceledAt ? 'Access Ends' : 'Renews On') }}
                        </p>
                        <p class="text-sm font-bold text-ink dark:text-white mt-1">
                            @if($periodEndsAt)
                                {{ $periodEndsAt->format('F d, Y') }}
                            @elseif($trialEndsAt)
                                {{ $trialEndsAt->format('F d, Y') }}
                            @else
                                Lifetime / Manual
                            @endif
                        </p>
                    </div>
                </div>

                {{-- Plan Feature Inclusions Highlights --}}
                <div class="mt-6 pt-5 border-t border-slate-100 dark:border-slate-800">
                    <p class="text-xs font-bold text-slate-700 dark:text-slate-300 mb-3">Plan Entitlements:</p>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5 text-xs text-slate-600 dark:text-slate-400">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-circle-check text-emerald-500"></i>
                            <span>{{ number_format($maxProducts) }} Products Max</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-circle-check text-emerald-500"></i>
                            <span>{{ $maxStaff }} Staff & Pharmacists</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-circle-check text-emerald-500"></i>
                            <span>{{ $maxStorageMB }} MB Media Storage</span>
                        </div>
                        <div class="flex items-center gap-2">
                            @if($allowsCustomDomain)
                                <i class="fa-solid fa-circle-check text-emerald-500"></i>
                                <span>Custom Domain Enabled</span>
                            @else
                                <i class="fa-solid fa-circle-xmark text-slate-400"></i>
                                <span class="line-through text-slate-400">Custom Domain</span>
                            @endif
                        </div>
                        <div class="flex items-center gap-2">
                            @if($currentPlan?->has_advanced_reporting)
                                <i class="fa-solid fa-circle-check text-emerald-500"></i>
                                <span>Advanced Analytics</span>
                            @else
                                <i class="fa-solid fa-circle-xmark text-slate-400"></i>
                                <span class="line-through text-slate-400">Advanced Analytics</span>
                            @endif
                        </div>
                        <div class="flex items-center gap-2">
                            @if($currentPlan?->has_custom_rbac)
                                <i class="fa-solid fa-circle-check text-emerald-500"></i>
                                <span>Custom RBAC & Roles</span>
                            @else
                                <i class="fa-solid fa-circle-xmark text-slate-400"></i>
                                <span class="line-through text-slate-400">Custom RBAC</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Card Footer Actions --}}
            <div class="px-6 py-4 bg-slate-50/80 dark:bg-slate-800/40 border-t border-slate-100 dark:border-slate-800 flex flex-wrap items-center justify-between gap-3">
                <div class="text-xs text-slate-500 dark:text-slate-400">
                    @if($canceledAt)
                        <span class="text-red-500 font-semibold"><i class="fa-solid fa-ban mr-1"></i> Auto-renew cancelled. Resubscribe before expiry.</span>
                    @else
                        <span><i class="fa-solid fa-repeat text-indigo-500 mr-1"></i> Automatic renewal is active for your pharmacy store.</span>
                    @endif
                </div>

                <div class="flex items-center gap-2.5">
                    @if($canceledAt)
                        <form method="POST" action="{{ route('store.admin.subscription.resume') }}">
                            @csrf
                            <button type="submit"
                                    class="px-3.5 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition shadow-xs">
                                <i class="fa-solid fa-rotate-left mr-1"></i> Resume Auto-Renew
                            </button>
                        </form>
                    @else
                        <button type="button" onclick="confirmCancelModal()"
                                class="px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-400 hover:text-red-600 text-xs font-semibold transition">
                            Cancel Auto-Renew
                        </button>
                    @endif

                    <a href="#plans-section"
                       class="px-4 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-xs transition">
                        Change Plan
                    </a>
                </div>
            </div>
        </div>

        {{-- 2. Integrated Domain Status Summary Widget (5 cols) --}}
        <div class="lg:col-span-5 rounded-3xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm p-6 md:p-7 flex flex-col justify-between relative overflow-hidden">
            <div>
                <div class="flex items-center justify-between gap-2 mb-3">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-400 border border-emerald-200/60 dark:border-emerald-900/50">
                        <i class="fa-solid fa-globe text-emerald-500"></i> Integrated Domain Status
                    </span>
                    <span class="text-[11px] font-bold text-slate-400">Brand Identity</span>
                </div>

                <h3 class="text-lg font-bold text-ink dark:text-white">Active Storefront Domain</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">
                    Your custom domain connects directly with your active plan quota. Customers visit this address to browse and place medicine orders.
                </p>

                {{-- Domain Display Box --}}
                <div class="mt-4 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 bg-slate-50/75 dark:bg-slate-800/40">
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <div class="w-9 h-9 rounded-xl bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-earth-americas text-sm"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">
                                    {{ $primaryDomain->is_custom ? 'Active Custom Domain' : 'Active System Subdomain' }}
                                </p>
                                <p class="text-sm font-extrabold text-ink dark:text-white truncate">
                                    {{ $primaryDomain->domain }}
                                </p>
                            </div>
                        </div>

                        <a href="http://{{ $primaryDomain->domain }}" target="_blank"
                           class="w-7 h-7 rounded-lg bg-white dark:bg-slate-700 text-slate-600 dark:text-slate-300 flex items-center justify-center hover:text-indigo-600 shadow-2xs border border-slate-200 dark:border-slate-600 transition"
                           title="Visit Storefront">
                            <i class="fa-solid fa-arrow-up-right-from-square text-[11px]"></i>
                        </a>
                    </div>

                    {{-- Badges Row --}}
                    <div class="mt-3.5 pt-3 border-t border-slate-200/70 dark:border-slate-700/60 flex flex-wrap items-center gap-2">
                        @if($primaryDomain->is_custom)
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-[11px] font-semibold bg-indigo-50 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300 border border-indigo-200/60 dark:border-indigo-800">
                                <i class="fa-solid fa-award text-[10px]"></i> Custom Branded
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-[11px] font-semibold bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                <i class="fa-solid fa-cube text-[10px]"></i> Platform Subdomain
                            </span>
                        @endif

                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-[11px] font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300 border border-emerald-200/60 dark:border-emerald-800">
                            <i class="fa-solid fa-lock text-[10px]"></i> SSL Secure
                        </span>

                        @if(!empty($primaryDomain->dns_verified))
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-[11px] font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300 border border-emerald-200/60 dark:border-emerald-800">
                                <i class="fa-solid fa-check text-[10px]"></i> DNS Verified
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-[11px] font-semibold bg-amber-50 text-amber-700 dark:bg-amber-950 dark:text-amber-300 border border-amber-200/60 dark:border-amber-800">
                                <i class="fa-solid fa-clock text-[10px]"></i> Pending DNS
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Plan Capability Callout --}}
                <div class="mt-4 text-xs">
                    @if($allowsCustomDomain)
                        <div class="p-3 rounded-xl bg-emerald-50/60 dark:bg-emerald-950/20 border border-emerald-200/50 dark:border-emerald-900/40 text-emerald-800 dark:text-emerald-300 flex items-start gap-2">
                            <i class="fa-solid fa-circle-check text-emerald-600 dark:text-emerald-400 mt-0.5 shrink-0"></i>
                            <span>Custom domain routing is unlocked and fully supported on your <strong>{{ $currentPlan->name ?? 'Pro' }}</strong> tier.</span>
                        </div>
                    @else
                        <div class="p-3 rounded-xl bg-amber-50/60 dark:bg-amber-950/20 border border-amber-200/50 dark:border-amber-900/40 text-amber-800 dark:text-amber-300 flex items-start gap-2">
                            <i class="fa-solid fa-lock text-amber-600 dark:text-amber-400 mt-0.5 shrink-0"></i>
                            <span>Custom domain branding is restricted on the Starter tier. Upgrade your plan to link your own domain (e.g. <code>lazzpharma.com</code>).</span>
                        </div>
                    @endif
                </div>
            </div>

            <div class="mt-5 pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <span class="text-xs text-slate-400">DNS & SSL Status</span>
                <a href="{{ route('store.admin.domains.index') }}"
                   class="inline-flex items-center gap-1.5 text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 transition">
                    <span>Manage Domains & DNS</span>
                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </a>
            </div>
        </div>
    </div>

    {{-- Resource & Limit Meters (Usage Quotas) --}}
    <div>
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-lg font-bold text-ink dark:text-white">Resource & Limit Meters (Usage Quotas)</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Real-time consumption meters tracking your store's storage, staff seats, and catalog size against your plan limits.</p>
            </div>
            <span class="text-xs font-semibold px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400">
                Live Sync
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">

            {{-- 1. Storage Usage Meter --}}
            <div class="card p-5 md:p-6 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-10 h-10 rounded-xl bg-sky-50 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400 flex items-center justify-center text-base">
                            <i class="fa-solid fa-hard-drive"></i>
                        </div>
                        <span class="text-xs font-bold px-2 py-0.5 rounded-full {{ $storagePct > 85 ? 'bg-red-50 text-red-600 dark:bg-red-950 dark:text-red-400' : ($storagePct > 65 ? 'bg-amber-50 text-amber-600 dark:bg-amber-950 dark:text-amber-400' : 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950 dark:text-emerald-400') }}">
                            {{ $storagePct }}% used
                        </span>
                    </div>

                    <h3 class="text-sm font-bold text-ink dark:text-white">Storage Usage</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Media files, banners, & prescription uploads</p>

                    <div class="mt-4 flex items-baseline gap-2">
                        <span class="text-2xl font-extrabold text-ink dark:text-white">{{ $storageUsedMB }} MB</span>
                        <span class="text-xs font-semibold text-slate-400">/ {{ $maxStorageMB }} MB limit</span>
                    </div>

                    {{-- Visual Progress Bar --}}
                    <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-2.5 overflow-hidden mt-3 border border-slate-200/60 dark:border-slate-700/60">
                        <div class="h-full rounded-full transition-all duration-500 {{ $storagePct > 85 ? 'bg-red-500' : ($storagePct > 65 ? 'bg-amber-500' : 'bg-sky-500') }}"
                             style="width: {{ $storagePct }}%;"></div>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 text-[11px] text-slate-500 dark:text-slate-400 flex items-center justify-between">
                    <span>Remaining: {{ max(0, round($maxStorageMB - $storageUsedMB, 1)) }} MB</span>
                    @if($storagePct > 80)
                        <a href="#plans-section" class="text-indigo-600 dark:text-indigo-400 font-bold hover:underline">Upgrade</a>
                    @else
                        <span class="text-emerald-600 dark:text-emerald-400 font-semibold">Healthy</span>
                    @endif
                </div>
            </div>

            {{-- 2. Staff Accounts Meter --}}
            <div class="card p-5 md:p-6 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-10 h-10 rounded-xl bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 flex items-center justify-center text-base">
                            <i class="fa-solid fa-user-doctor"></i>
                        </div>
                        <span class="text-xs font-bold px-2 py-0.5 rounded-full {{ $staffPct > 85 ? 'bg-red-50 text-red-600 dark:bg-red-950 dark:text-red-400' : ($staffPct > 65 ? 'bg-amber-50 text-amber-600 dark:bg-amber-950 dark:text-amber-400' : 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950 dark:text-emerald-400') }}">
                            {{ $staffPct }}% used
                        </span>
                    </div>

                    <h3 class="text-sm font-bold text-ink dark:text-white">Staff & Pharmacists</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Admin and team logins configured</p>

                    <div class="mt-4 flex items-baseline gap-2">
                        <span class="text-2xl font-extrabold text-ink dark:text-white">{{ $staffCount }}</span>
                        <span class="text-xs font-semibold text-slate-400">/ {{ $maxStaff }} allowed accounts</span>
                    </div>

                    {{-- Visual Progress Bar --}}
                    <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-2.5 overflow-hidden mt-3 border border-slate-200/60 dark:border-slate-700/60">
                        <div class="h-full rounded-full transition-all duration-500 {{ $staffPct > 85 ? 'bg-red-500' : ($staffPct > 65 ? 'bg-amber-500' : 'bg-purple-500') }}"
                             style="width: {{ $staffPct }}%;"></div>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 text-[11px] text-slate-500 dark:text-slate-400 flex items-center justify-between">
                    <span>Available seats: {{ max(0, $maxStaff - $staffCount) }}</span>
                    <a href="{{ route('store.admin.admins.index') }}" class="text-indigo-600 dark:text-indigo-400 font-bold hover:underline">Manage Team</a>
                </div>
            </div>

            {{-- 3. Products Catalog Meter --}}
            <div class="card p-5 md:p-6 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-base">
                            <i class="fa-solid fa-pills"></i>
                        </div>
                        <span class="text-xs font-bold px-2 py-0.5 rounded-full {{ $productsPct > 85 ? 'bg-red-50 text-red-600 dark:bg-red-950 dark:text-red-400' : ($productsPct > 65 ? 'bg-amber-50 text-amber-600 dark:bg-amber-950 dark:text-amber-400' : 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950 dark:text-emerald-400') }}">
                            {{ $productsPct }}% used
                        </span>
                    </div>

                    <h3 class="text-sm font-bold text-ink dark:text-white">Product Catalog</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Active pharmaceuticals & medical goods</p>

                    <div class="mt-4 flex items-baseline gap-2">
                        <span class="text-2xl font-extrabold text-ink dark:text-white">{{ $productsCount }}</span>
                        <span class="text-xs font-semibold text-slate-400">/ {{ $maxProducts }} max products</span>
                    </div>

                    {{-- Visual Progress Bar --}}
                    <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-2.5 overflow-hidden mt-3 border border-slate-200/60 dark:border-slate-700/60">
                        <div class="h-full rounded-full transition-all duration-500 {{ $productsPct > 85 ? 'bg-red-500' : ($productsPct > 65 ? 'bg-amber-500' : 'bg-emerald-500') }}"
                             style="width: {{ $productsPct }}%;"></div>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 text-[11px] text-slate-500 dark:text-slate-400 flex items-center justify-between">
                    <span>Can add: {{ max(0, $maxProducts - $productsCount) }} products</span>
                    <a href="{{ route('store.admin.products.index') }}" class="text-indigo-600 dark:text-indigo-400 font-bold hover:underline">View Catalog</a>
                </div>
            </div>

        </div>
    </div>

    {{-- Upgrade / Plan Switching Action Section --}}
    <div id="plans-section" class="scroll-mt-6 rounded-3xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm p-6 md:p-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
            <div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-indigo-50 dark:bg-indigo-950/50 text-indigo-700 dark:text-indigo-400 border border-indigo-200/60 dark:border-indigo-900/50 mb-2">
                    <i class="fa-solid fa-arrow-trend-up"></i> Seamless Plan Switching
                </span>
                <h2 class="text-xl font-extrabold text-ink dark:text-white">Upgrade or Switch Your Subscription Tier</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Scale up your storage, catalog capacity, and staff accounts instantly without platform downtime.</p>
            </div>

            {{-- Billing Cycle Toggle Switch --}}
            <div class="inline-flex items-center p-1 rounded-2xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700">
                <button type="button" onclick="setBillingCycle('monthly')" id="btn-cycle-monthly"
                        class="px-4 py-2 rounded-xl text-xs font-bold transition-all shadow-xs bg-white dark:bg-slate-900 text-ink dark:text-white">
                    Monthly Billing
                </button>
                <button type="button" onclick="setBillingCycle('yearly')" id="btn-cycle-yearly"
                        class="px-4 py-2 rounded-xl text-xs font-bold transition-all text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
                    <span>Annual Billing</span>
                    <span class="px-1.5 py-0.5 rounded text-[10px] font-extrabold bg-emerald-500 text-white">Save ~15%</span>
                </button>
            </div>
        </div>

        {{-- Plan Cards Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach($availablePlans as $plan)
                @php
                    $isCurrentPlan = ($currentPlan && $currentPlan->id === $plan->id);
                @endphp
                <div class="rounded-2xl border {{ $isCurrentPlan ? 'border-2 border-indigo-600 dark:border-indigo-500 bg-indigo-50/10 dark:bg-indigo-950/20' : 'border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900' }} p-6 flex flex-col justify-between relative shadow-sm hover:shadow-md transition-shadow">

                    @if($isCurrentPlan)
                        <div class="absolute -top-3 left-1/2 -translate-x-1/2">
                            <span class="px-3 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-indigo-600 text-white shadow-xs">
                                Current Active Plan
                            </span>
                        </div>
                    @endif

                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-lg font-bold text-ink dark:text-white">{{ $plan->name }}</h3>
                            @if(str_contains(strtolower($plan->name), 'growth') || str_contains(strtolower($plan->name), 'pro'))
                                <span class="px-2 py-0.5 rounded text-[10px] font-extrabold bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300">
                                    POPULAR
                                </span>
                            @endif
                        </div>

                        {{-- Pricing Switchable --}}
                        <div class="my-4">
                            <div class="plan-price-monthly">
                                <span class="text-3xl font-extrabold text-ink dark:text-white">${{ number_format($plan->monthly_price, 2) }}</span>
                                <span class="text-xs text-slate-400 font-semibold">/ month</span>
                            </div>
                            <div class="plan-price-yearly hidden">
                                <span class="text-3xl font-extrabold text-ink dark:text-white">${{ number_format($plan->yearly_price, 2) }}</span>
                                <span class="text-xs text-slate-400 font-semibold">/ year</span>
                                <p class="text-[11px] text-emerald-600 font-bold mt-0.5">Approx. ${{ number_format($plan->yearly_price / 12, 2) }}/month</p>
                            </div>
                        </div>

                        {{-- Core Limits --}}
                        <div class="space-y-2.5 py-4 border-y border-slate-100 dark:border-slate-800 text-xs">
                            <div class="flex items-center justify-between text-slate-700 dark:text-slate-300">
                                <span class="text-slate-400">Products Catalog:</span>
                                <span class="font-bold">{{ number_format($plan->max_products) }} Products</span>
                            </div>
                            <div class="flex items-center justify-between text-slate-700 dark:text-slate-300">
                                <span class="text-slate-400">Staff Accounts:</span>
                                <span class="font-bold">{{ $plan->max_staff_accounts }} Accounts</span>
                            </div>
                            <div class="flex items-center justify-between text-slate-700 dark:text-slate-300">
                                <span class="text-slate-400">Media Storage:</span>
                                <span class="font-bold">{{ $plan->max_storage_mb }} MB</span>
                            </div>
                        </div>

                        {{-- Features Checklist --}}
                        <div class="mt-4 space-y-2 text-xs">
                            <div class="flex items-center gap-2 {{ $plan->allow_custom_domain ? 'text-slate-700 dark:text-slate-300' : 'text-slate-400 line-through' }}">
                                <i class="fa-solid {{ $plan->allow_custom_domain ? 'fa-check text-emerald-500' : 'fa-xmark text-slate-400' }}"></i>
                                <span>Custom Domain Connection</span>
                            </div>
                            <div class="flex items-center gap-2 {{ $plan->has_advanced_reporting ? 'text-slate-700 dark:text-slate-300' : 'text-slate-400 line-through' }}">
                                <i class="fa-solid {{ $plan->has_advanced_reporting ? 'fa-check text-emerald-500' : 'fa-xmark text-slate-400' }}"></i>
                                <span>Advanced Sales & Inventory Reports</span>
                            </div>
                            <div class="flex items-center gap-2 {{ $plan->has_custom_rbac ? 'text-slate-700 dark:text-slate-300' : 'text-slate-400 line-through' }}">
                                <i class="fa-solid {{ $plan->has_custom_rbac ? 'fa-check text-emerald-500' : 'fa-xmark text-slate-400' }}"></i>
                                <span>Custom Roles & Permissions</span>
                            </div>
                            <div class="flex items-center gap-2 {{ $plan->has_audit_logs ? 'text-slate-700 dark:text-slate-300' : 'text-slate-400 line-through' }}">
                                <i class="fa-solid {{ $plan->has_audit_logs ? 'fa-check text-emerald-500' : 'fa-xmark text-slate-400' }}"></i>
                                <span>Audit Logs & Security Tracing</span>
                            </div>
                        </div>
                    </div>

                    {{-- Action Button --}}
                    <div class="mt-6 pt-4 border-t border-slate-100 dark:border-slate-800">
                        @if($isCurrentPlan)
                            <button type="button" disabled
                                    class="w-full py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-500 font-bold text-xs cursor-default">
                                <i class="fa-solid fa-circle-check text-emerald-500 mr-1.5"></i> Current Tier
                            </button>
                        @else
                            <form method="POST" action="{{ route('store.admin.subscription.change_plan') }}" onsubmit="return confirmPlanSwitch('{{ $plan->name }}')">
                                @csrf
                                <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                                <input type="hidden" name="billing_interval" class="selected-billing-interval-input" value="{{ $billingInterval }}">
                                <button type="submit"
                                        class="w-full py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-md shadow-indigo-600/20 transition-all hover:-translate-y-0.5 cursor-pointer">
                                    {{ $plan->monthly_price > ($currentPlan->monthly_price ?? 0) ? 'Upgrade to ' . $plan->name : 'Switch to ' . $plan->name }}
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Billing History & Invoices Section --}}
    <div class="rounded-3xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-lg font-bold text-ink dark:text-white">Billing History & Invoices</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Download previous invoices, review paid subscription settlements, and inspect receipts.</p>
            </div>
            <div class="text-xs font-semibold text-slate-400">
                Total Invoices: {{ $invoices->total() }}
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-slate-50/75 dark:bg-slate-800/50 border-b border-slate-100 dark:border-slate-800 text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        <th class="py-3.5 px-6">Invoice #</th>
                        <th class="py-3.5 px-4">Plan / Description</th>
                        <th class="py-3.5 px-4">Billing Period</th>
                        <th class="py-3.5 px-4">Total Amount</th>
                        <th class="py-3.5 px-4">Payment Status</th>
                        <th class="py-3.5 px-4">Payment Date</th>
                        <th class="py-3.5 px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                    @forelse($invoices as $inv)
                        <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition-colors">
                            <td class="py-4 px-6 font-mono text-xs font-bold text-slate-900 dark:text-white">
                                <code>{{ $inv->invoice_number }}</code>
                            </td>
                            <td class="py-4 px-4 font-semibold text-slate-800 dark:text-slate-200">
                                {{ $inv->plan_title }}
                                <span class="text-xs text-slate-400 block font-normal capitalize">
                                    {{ ucfirst($inv->cycle) }} Cycle
                                </span>
                            </td>
                            <td class="py-4 px-4 text-xs text-slate-500 dark:text-slate-400">
                                @if($inv->billing_period_start && $inv->billing_period_end)
                                    {{ $inv->billing_period_start->format('M d, Y') }} &rarr; {{ $inv->billing_period_end->format('M d, Y') }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="py-4 px-4 font-extrabold text-ink dark:text-white whitespace-nowrap">
                                ${{ number_format($inv->total, 2) }}
                                <span class="text-[11px] font-normal text-slate-400 uppercase">{{ $inv->currency }}</span>
                            </td>
                            <td class="py-4 px-4">
                                @if($inv->status === 'paid')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                        <i class="fa-solid fa-circle-check text-[10px]"></i> Paid
                                    </span>
                                @elseif($inv->status === 'draft')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                        Draft
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 dark:bg-amber-950 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                                        {{ ucfirst($inv->status) }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-4 text-xs text-slate-500 dark:text-slate-400">
                                {{ $inv->paid_at ? $inv->paid_at->format('M d, Y') : ($inv->created_at ? $inv->created_at->format('M d, Y') : '-') }}
                            </td>
                            <td class="py-4 px-6 text-right">
                                <button type="button" onclick="openInvoiceModal({{ $inv->id }})"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:text-indigo-600 hover:border-indigo-300 text-xs font-semibold shadow-2xs transition">
                                    <i class="fa-solid fa-receipt text-slate-400"></i>
                                    <span>View Receipt</span>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400 text-xs">
                                <i class="fa-solid fa-file-invoice text-3xl mb-2 text-slate-300 dark:text-slate-700 block"></i>
                                No billing invoices recorded yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($invoices->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-800">
                {{ $invoices->links() }}
            </div>
        @endif
    </div>

</div>

{{-- Cancel Subscription Confirmation Modal --}}
<div id="cancel-modal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 rounded-3xl max-w-md w-full p-6 md:p-7 border border-slate-200 dark:border-slate-800 shadow-xl space-y-4">
        <div class="w-12 h-12 rounded-2xl bg-red-50 dark:bg-red-950/60 text-red-600 dark:text-red-400 flex items-center justify-center text-xl">
            <i class="fa-solid fa-triangle-exclamation"></i>
        </div>
        <div>
            <h3 class="text-lg font-bold text-ink dark:text-white">Cancel Auto-Renewal?</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">
                Are you sure you want to cancel your automatic renewal? Your pharmacy storefront will remain active until the end of your billing cycle ({{ $periodEndsAt ? $periodEndsAt->format('F d, Y') : 'the end of period' }}), after which custom domains and limits will be paused.
            </p>
        </div>
        <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100 dark:border-slate-800">
            <button type="button" onclick="closeCancelModal()"
                    class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold hover:bg-slate-50 dark:hover:bg-slate-800">
                Keep Subscription
            </button>
            <form method="POST" action="{{ route('store.admin.subscription.cancel') }}">
                @csrf
                <button type="submit"
                        class="px-4 py-2 rounded-xl bg-red-600 hover:bg-red-700 text-white text-xs font-bold shadow-xs">
                    Confirm Cancellation
                </button>
            </form>
        </div>
    </div>
</div>

{{-- Invoice Receipt Modal --}}
<div id="invoice-modal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 rounded-3xl max-w-xl w-full p-6 md:p-8 border border-slate-200 dark:border-slate-800 shadow-2xl space-y-6 relative max-h-[90vh] overflow-y-auto">
        <button type="button" onclick="closeInvoiceModal()" class="absolute top-5 right-5 text-slate-400 hover:text-slate-600 dark:hover:text-white text-xl font-bold">&times;</button>

        <div id="invoice-modal-content">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800">
                <div>
                    <span class="text-[10px] font-extrabold uppercase tracking-widest text-indigo-600 dark:text-indigo-400">Subscription Receipt</span>
                    <h3 class="text-lg font-bold text-ink dark:text-white" id="inv-modal-number">INV-XXXXXX</h3>
                    <p class="text-xs text-slate-400" id="inv-modal-date">-</p>
                </div>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800" id="inv-modal-status">
                    Paid
                </span>
            </div>

            <div class="grid grid-cols-2 gap-4 py-4 border-b border-slate-100 dark:border-slate-800 text-xs">
                <div>
                    <p class="font-bold text-slate-400 uppercase tracking-wider text-[10px]">Billed To:</p>
                    <p class="font-bold text-ink dark:text-white mt-0.5" id="inv-modal-store">-</p>
                    <p class="text-slate-400" id="inv-modal-subdomain">-</p>
                </div>
                <div>
                    <p class="font-bold text-slate-400 uppercase tracking-wider text-[10px]">Billing Period:</p>
                    <p class="font-semibold text-slate-700 dark:text-slate-300 mt-0.5" id="inv-modal-period">-</p>
                </div>
            </div>

            <div class="py-4">
                <table class="w-full text-xs">
                    <thead>
                        <tr class="text-slate-400 uppercase font-semibold text-[10px] border-b border-slate-100 dark:border-slate-800">
                            <th class="py-2 text-left">Description</th>
                            <th class="py-2 text-center">Cycle</th>
                            <th class="py-2 text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        <tr>
                            <td class="py-3 font-semibold text-ink dark:text-white" id="inv-modal-item-name">Plan Tier</td>
                            <td class="py-3 text-center text-slate-500" id="inv-modal-item-cycle">Monthly</td>
                            <td class="py-3 text-right font-bold text-ink dark:text-white" id="inv-modal-subtotal">$0.00</td>
                        </tr>
                    </tbody>
                    <tfoot class="border-t border-slate-200 dark:border-slate-700 font-bold">
                        <tr>
                            <td colspan="2" class="py-2.5 text-right text-slate-500">Tax (0%):</td>
                            <td class="py-2.5 text-right text-slate-700 dark:text-slate-300" id="inv-modal-tax">$0.00</td>
                        </tr>
                        <tr class="text-sm">
                            <td colspan="2" class="py-2 text-right text-ink dark:text-white">Total Settled:</td>
                            <td class="py-2 text-right text-emerald-600 dark:text-emerald-400 font-extrabold" id="inv-modal-total">$0.00</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <p class="text-[11px] text-slate-400">Processed securely via Genzilla SaaS Platform.</p>
                <button type="button" onclick="window.print()"
                        class="px-4 py-2 rounded-xl bg-slate-900 dark:bg-white text-white dark:text-slate-900 font-bold text-xs shadow-xs hover:opacity-90 flex items-center gap-1.5">
                    <i class="fa-solid fa-print"></i>
                    <span>Print Receipt</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    let currentBillingCycle = '{{ $billingInterval }}';

    function setBillingCycle(cycle) {
        currentBillingCycle = cycle;

        const btnMonthly = document.getElementById('btn-cycle-monthly');
        const btnYearly = document.getElementById('btn-cycle-yearly');

        if (cycle === 'yearly') {
            btnYearly.classList.add('bg-white', 'dark:bg-slate-900', 'text-ink', 'dark:text-white', 'shadow-xs');
            btnYearly.classList.remove('text-slate-500', 'dark:text-slate-400');

            btnMonthly.classList.remove('bg-white', 'dark:bg-slate-900', 'text-ink', 'dark:text-white', 'shadow-xs');
            btnMonthly.classList.add('text-slate-500', 'dark:text-slate-400');

            document.querySelectorAll('.plan-price-monthly').forEach(el => el.classList.add('hidden'));
            document.querySelectorAll('.plan-price-yearly').forEach(el => el.classList.remove('hidden'));
        } else {
            btnMonthly.classList.add('bg-white', 'dark:bg-slate-900', 'text-ink', 'dark:text-white', 'shadow-xs');
            btnMonthly.classList.remove('text-slate-500', 'dark:text-slate-400');

            btnYearly.classList.remove('bg-white', 'dark:bg-slate-900', 'text-ink', 'dark:text-white', 'shadow-xs');
            btnYearly.classList.add('text-slate-500', 'dark:text-slate-400');

            document.querySelectorAll('.plan-price-yearly').forEach(el => el.classList.add('hidden'));
            document.querySelectorAll('.plan-price-monthly').forEach(el => el.classList.remove('hidden'));
        }

        // Update hidden inputs in plan forms
        document.querySelectorAll('.selected-billing-interval-input').forEach(input => {
            input.value = cycle;
        });
    }

    // Set initial cycle according to active subscription
    if (currentBillingCycle === 'yearly') {
        setBillingCycle('yearly');
    }

    function confirmPlanSwitch(planName) {
        return confirm(`Confirm plan change to ${planName} (${currentBillingCycle})? Your store quotas and billing cycle will be updated immediately.`);
    }

    function confirmCancelModal() {
        document.getElementById('cancel-modal').classList.remove('hidden');
    }

    function closeCancelModal() {
        document.getElementById('cancel-modal').classList.add('hidden');
    }

    function closeInvoiceModal() {
        document.getElementById('invoice-modal').classList.add('hidden');
    }

    function openInvoiceModal(invoiceId) {
        fetch(`{{ url('/store/subscription/invoices') }}/${invoiceId}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            document.getElementById('inv-modal-number').innerText = data.invoice_number;
            document.getElementById('inv-modal-date').innerText = `Issued on ${data.created_at}`;
            document.getElementById('inv-modal-status').innerText = data.status.toUpperCase();
            document.getElementById('inv-modal-store').innerText = data.store_name;
            document.getElementById('inv-modal-subdomain').innerText = data.subdomain ? `${data.subdomain}.{{ config('app.base_domain') }}` : '';
            document.getElementById('inv-modal-period').innerText = data.period_start ? `${data.period_start} to ${data.period_end}` : 'One-time Settlement';
            document.getElementById('inv-modal-item-name').innerText = data.plan_name;
            document.getElementById('inv-modal-item-cycle').innerText = data.billing_interval;
            document.getElementById('inv-modal-subtotal').innerText = `${data.currency} $${data.subtotal}`;
            document.getElementById('inv-modal-tax').innerText = `${data.currency} $${data.tax}`;
            document.getElementById('inv-modal-total').innerText = `${data.currency} $${data.total}`;

            document.getElementById('invoice-modal').classList.remove('hidden');
        })
        .catch(err => {
            console.error('Failed to load invoice receipt', err);
            window.location.href = `{{ url('/store/subscription/invoices') }}/${invoiceId}`;
        });
    }
</script>
@endsection
