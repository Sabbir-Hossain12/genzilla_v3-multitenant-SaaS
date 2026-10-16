@extends('platform_admin.layout.master')

@section('contents')
    <div class="space-y-6">
        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-slate-900 dark:text-white tracking-tight">Tenant Resource Usage Monitoring</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Track real-time quotas, storage usage, and subscription limits across tenant stores.</p>
            </div>
        </div>

        <!-- Main Card Container -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-xl shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                    <tr class="bg-slate-50/75 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800 text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400">
                        <th class="py-3.5 px-4 w-16">#</th>
                        <th class="py-3.5 px-4">Tenant Store</th>
                        <th class="py-3.5 px-4">Current Plan</th>
                        <th class="py-3.5 px-4 w-72">Products Count / Limit</th>
                        <th class="py-3.5 px-4">Staff Accounts / Limit</th>
                        <th class="py-3.5 px-4 w-72">Storage Used / Limit</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800 text-sm text-slate-700 dark:text-slate-300">
                    @forelse($usages as $usage)
                        @php
                            $plan = $usage->tenant->activeSubscription->plan ?? null;
                            $maxProducts = $plan->max_products ?? 100;
                            $maxStaff = $plan->max_staff_accounts ?? 5;
                            $maxStorageMB = $plan->max_storage_mb ?? 500;
                            $usedMB = round($usage->storage_used_bytes / (1024 * 1024), 2);
                            $storagePct = min(100, round(($usedMB / max(1, $maxStorageMB)) * 100));
                            $prodPct = min(100, round(($usage->products_count / max(1, $maxProducts)) * 100));
                        @endphp
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition-colors">
                            <td class="py-4 px-4 font-medium text-slate-500 dark:text-slate-400">
                                {{ $loop->iteration }}
                            </td>
                            <td class="py-4 px-4">
                                <div class="font-semibold text-slate-900 dark:text-white">{{ $usage->tenant->name ?? 'N/A' }}</div>
                                <div class="mt-0.5">
                                    <code class="text-xs px-1.5 py-0.5 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 rounded border border-slate-200 dark:border-slate-700">{{ $usage->tenant->subdomain ?? 'N/A' }}</code>
                                </div>
                            </td>
                            <td class="py-4 px-4">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-indigo-50 dark:bg-indigo-950/50 text-indigo-700 dark:text-indigo-400 border border-indigo-200/60 dark:border-indigo-900/50">
                                {{ $plan->name ?? 'Default Plan' }}
                            </span>
                            </td>
                            <!-- Products Progress -->
                            <td class="py-4 px-4">
                                <div class="flex items-center justify-between text-xs font-semibold mb-1.5">
                                    <span class="text-slate-900 dark:text-white">{{ $usage->products_count }} / {{ $maxProducts }}</span>
                                    <span class="{{ $prodPct > 85 ? 'text-rose-600 dark:text-rose-400' : 'text-slate-500 dark:text-slate-400' }}">{{ $prodPct }}%</span>
                                </div>
                                <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-2 overflow-hidden border border-slate-200/60 dark:border-slate-700/60">
                                    <div class="h-full rounded-full transition-all duration-500 {{ $prodPct > 85 ? 'bg-rose-500' : 'bg-emerald-500' }}" style="width: {{ $prodPct }}%;"></div>
                                </div>
                            </td>
                            <td class="py-4 px-4">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                {{ $usage->staff_accounts_count }} / {{ $maxStaff }} Staff
                            </span>
                            </td>
                            <!-- Storage Progress -->
                            <td class="py-4 px-4">
                                <div class="flex items-center justify-between text-xs font-semibold mb-1.5">
                                    <span class="text-slate-900 dark:text-white">{{ $usedMB }} MB / {{ $maxStorageMB }} MB</span>
                                    <span class="{{ $storagePct > 85 ? 'text-rose-600 dark:text-rose-400' : 'text-slate-500 dark:text-slate-400' }}">{{ $storagePct }}%</span>
                                </div>
                                <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-2 overflow-hidden border border-slate-200/60 dark:border-slate-700/60">
                                    <div class="h-full rounded-full transition-all duration-500 {{ $storagePct > 85 ? 'bg-rose-500' : 'bg-sky-500' }}" style="width: {{ $storagePct }}%;"></div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-slate-400 dark:text-slate-500 py-12 text-sm">No tenant resource usages recorded yet.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
