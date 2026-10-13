@extends('platform_admin.layout.master')

@section('contents')

    {{-- Page heading --}}
    <div class="flex items-start justify-between gap-4 mb-6">
        <div>
            <h1 class="text-xl md:text-2xl font-extrabold text-ink dark:text-white">Platform Metrics & Social Proof</h1>
            <nav class="flex items-center gap-1.5 mt-1 text-xs text-slate-400">
                <a href="{{ route('admin.dashboard.index') }}" class="hover:text-primary transition-colors">Admin</a>
                <svg class="w-3 h-3 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                <span class="text-slate-500 dark:text-slate-300 font-medium">Metrics</span>
            </nav>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.matrix.update') }}">
        @csrf
        @method('PUT')

        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden">

            {{-- Header --}}
            <div class="flex items-center gap-3 px-6 py-4 border-b border-slate-100 dark:border-slate-800">
                <div class="w-8 h-8 bg-primarylt dark:bg-primary/15 rounded-lg grid place-items-center shrink-0">
                    <svg class="w-4 h-4 text-primary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-ink dark:text-white">Landing Page Counter Stats</p>
                    <p class="text-xs text-slate-400 mt-0.5">Numbers displayed in the public storefront stats section</p>
                </div>
            </div>

            {{-- Body --}}
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-5">

                {{-- Active Merchants --}}
                <div class="space-y-1.5">
                    <label for="active_merchant" class="block text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wide">
                        Active Merchants Count
                    </label>
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
                        <input
                            type="number"
                            id="active_merchant"
                            name="active_merchant"
                            value="{{ old('active_merchant', $matrix->active_merchant) }}"
                            required
                            class="w-full pl-9 pr-3.5 py-2.5 text-sm rounded-xl
                   bg-slate-50 dark:bg-slate-800
                   border border-slate-200 dark:border-slate-700
                   text-ink dark:text-slate-100 placeholder:text-slate-400
                   focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary/20
                   transition-colors tabular-nums"
                        />
                    </div>
                </div>

                {{-- GMV Processed --}}
                <div class="space-y-1.5">
                    <label for="gmv_proccessed" class="block text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wide">
                        Total GMV Processed ($)
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm font-semibold pointer-events-none">$</span>
                        <input
                            type="number"
                            step="0.01"
                            id="gmv_proccessed"
                            name="gmv_proccessed"
                            value="{{ old('gmv_proccessed', $matrix->gmv_proccessed) }}"
                            required
                            class="w-full pl-7 pr-3.5 py-2.5 text-sm rounded-xl
                   bg-slate-50 dark:bg-slate-800
                   border border-slate-200 dark:border-slate-700
                   text-ink dark:text-slate-100 placeholder:text-slate-400
                   focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary/20
                   transition-colors tabular-nums"
                        />
                    </div>
                </div>

                {{-- Countries Served --}}
                <div class="space-y-1.5">
                    <label for="countries_served" class="block text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wide">
                        Countries Served
                    </label>
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 014 10 15.3 15.3 0 01-4 10 15.3 15.3 0 01-4-10 15.3 15.3 0 014-10z"/></svg>
                        <input
                            type="number"
                            id="countries_served"
                            name="countries_served"
                            value="{{ old('countries_served', $matrix->countries_served) }}"
                            required
                            class="w-full pl-9 pr-3.5 py-2.5 text-sm rounded-xl
                   bg-slate-50 dark:bg-slate-800
                   border border-slate-200 dark:border-slate-700
                   text-ink dark:text-slate-100 placeholder:text-slate-400
                   focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary/20
                   transition-colors tabular-nums"
                        />
                    </div>
                </div>

                {{-- Platform Uptime --}}
                <div class="space-y-1.5">
                    <label for="platform_uptime" class="block text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wide">
                        Platform Uptime Percentage
                    </label>
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                        <input
                            type="text"
                            id="platform_uptime"
                            name="platform_uptime"
                            value="{{ old('platform_uptime', $matrix->platform_uptime) }}"
                            required
                            placeholder="99.99%"
                            class="w-full pl-9 pr-3.5 py-2.5 text-sm rounded-xl
                   bg-slate-50 dark:bg-slate-800
                   border border-slate-200 dark:border-slate-700
                   text-ink dark:text-slate-100 placeholder:text-slate-400
                   focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary/20
                   transition-colors font-mono"
                        />
                    </div>
                </div>

            </div>

            {{-- Footer --}}
            <div class="flex items-center justify-between gap-4 px-6 py-4 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30">
                <p class="text-xs text-slate-400 hidden sm:block">These numbers animate on the public landing page.</p>
                <button
                    type="submit"
                    class="flex items-center gap-2 px-5 py-2.5
               bg-primary hover:bg-primarydark text-white
               text-sm font-semibold rounded-xl
               shadow-sm shadow-primary/30
               transition-colors shrink-0"
                >
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/>
                    </svg>
                    Update Metrics
                </button>
            </div>

        </div>
    </form>

@endsection
