@extends('platform_admin.layout.master')

@push('title')
    Dashboard Overview
@endpush

@push('css')

@endpush

@section('contents')
    <!-- Heading row -->
    <div class="flex items-start justify-between gap-4">
        <div>
            <h1 class="text-xl md:text-2xl font-extrabold text-ink dark:text-white">Good morning, Alex 👋</h1>
            <p class="text-sm text-slate-400 mt-1">Here's what's happening across your platform today.</p>
        </div>
        <button class="shrink-0 hidden sm:flex items-center gap-2 px-4 py-2.5
                     bg-primary hover:bg-primarydark text-white text-sm font-semibold
                     rounded-xl transition-colors shadow-sm shadow-primary/30">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            New Report
        </button>
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
                <span class="flex items-center gap-1 text-xs font-semibold text-accent">
            <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="7" y1="17" x2="17" y2="7"/><polyline points="7 7 17 7 17 17"/></svg>
            +12.4%
          </span>
            </div>
            <p class="text-2xl font-extrabold text-ink dark:text-white tabular-nums">$84,254</p>
            <p class="text-xs text-slate-400 mt-1 font-medium">Total Revenue</p>
            <div class="mt-3.5 h-1.5 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                <div class="h-full bg-primary rounded-full transition-all duration-700" style="width:74%"></div>
            </div>
            <p class="text-[11px] text-slate-400 mt-1.5">74% of $114k monthly goal</p>
        </div>

        <!-- Active Users -->
        <div class="card p-5">
            <div class="flex items-start justify-between mb-4">
                <div class="w-10 h-10 bg-accent/10 rounded-xl grid place-items-center">
                    <svg class="w-5 h-5 text-accent" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                </div>
                <span class="flex items-center gap-1 text-xs font-semibold text-accent">
            <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="7" y1="17" x2="17" y2="7"/><polyline points="7 7 17 7 17 17"/></svg>
            +8.1%
          </span>
            </div>
            <p class="text-2xl font-extrabold text-ink dark:text-white tabular-nums">24,731</p>
            <p class="text-xs text-slate-400 mt-1 font-medium">Active Users</p>
            <div class="mt-3.5 h-1.5 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                <div class="h-full bg-accent rounded-full transition-all duration-700" style="width:62%"></div>
            </div>
            <p class="text-[11px] text-slate-400 mt-1.5">+1,847 new this week</p>
        </div>

        <!-- Orders -->
        <div class="card p-5">
            <div class="flex items-start justify-between mb-4">
                <div class="w-10 h-10 bg-violet-100 dark:bg-violet-500/10 rounded-xl grid place-items-center">
                    <svg class="w-5 h-5 text-violet-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>
                        <line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/>
                    </svg>
                </div>
                <span class="flex items-center gap-1 text-xs font-semibold text-red-500">
            <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="7" y1="7" x2="17" y2="17"/><polyline points="17 7 17 17 7 17"/></svg>
            -2.3%
          </span>
            </div>
            <p class="text-2xl font-extrabold text-ink dark:text-white tabular-nums">1,284</p>
            <p class="text-xs text-slate-400 mt-1 font-medium">New Orders</p>
            <div class="mt-3.5 h-1.5 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                <div class="h-full bg-violet-500 rounded-full transition-all duration-700" style="width:53%"></div>
            </div>
            <p class="text-[11px] text-slate-400 mt-1.5">53% fulfilled today</p>
        </div>

        <!-- Conversion -->
        <div class="card p-5">
            <div class="flex items-start justify-between mb-4">
                <div class="w-10 h-10 bg-amber-100 dark:bg-amber-500/10 rounded-xl grid place-items-center">
                    <svg class="w-5 h-5 text-amber-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/>
                        <polyline points="17 6 23 6 23 12"/>
                    </svg>
                </div>
                <span class="flex items-center gap-1 text-xs font-semibold text-accent">
            <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="7" y1="17" x2="17" y2="7"/><polyline points="7 7 17 7 17 17"/></svg>
            +5.7%
          </span>
            </div>
            <p class="text-2xl font-extrabold text-ink dark:text-white tabular-nums">3.61%</p>
            <p class="text-xs text-slate-400 mt-1 font-medium">Conversion Rate</p>
            <div class="mt-3.5 h-1.5 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                <div class="h-full bg-amber-500 rounded-full transition-all duration-700" style="width:36%"></div>
            </div>
            <p class="text-[11px] text-slate-400 mt-1.5">Industry avg: 2.8%</p>
        </div>
    </div>

    <!-- ── CHART + ACTIVITY ── -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

        <!-- Revenue Chart -->
        <div class="card lg:col-span-2 p-5">
            <div class="flex items-center justify-between mb-5 gap-4">
                <div>
                    <h2 class="font-bold text-ink dark:text-white">Revenue Overview</h2>
                    <p class="text-xs text-slate-400 mt-0.5">Monthly performance vs target — 2026</p>
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
            <div class="overflow-x-auto -mx-1">
                <svg viewBox="0 0 600 210" class="w-full min-w-[320px]" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <linearGradient id="gp" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="#4f46e5" stop-opacity="0.2"/>
                            <stop offset="100%" stop-color="#4f46e5" stop-opacity="0"/>
                        </linearGradient>
                    </defs>
                    <!-- Grid -->
                    <line x1="44" y1="15"  x2="588" y2="15"  stroke="currentColor" stroke-width="0.5" class="text-slate-200 dark:text-slate-700/80" stroke-dasharray="4 3"/>
                    <line x1="44" y1="52"  x2="588" y2="52"  stroke="currentColor" stroke-width="0.5" class="text-slate-200 dark:text-slate-700/80" stroke-dasharray="4 3"/>
                    <line x1="44" y1="90"  x2="588" y2="90"  stroke="currentColor" stroke-width="0.5" class="text-slate-200 dark:text-slate-700/80" stroke-dasharray="4 3"/>
                    <line x1="44" y1="127" x2="588" y2="127" stroke="currentColor" stroke-width="0.5" class="text-slate-200 dark:text-slate-700/80" stroke-dasharray="4 3"/>
                    <line x1="44" y1="165" x2="588" y2="165" stroke="currentColor" stroke-width="0.5" class="text-slate-200 dark:text-slate-700/80"/>
                    <!-- Y labels -->
                    <text x="38" y="19"  text-anchor="end" font-size="9.5" fill="currentColor" class="text-slate-400 dark:text-slate-500">100k</text>
                    <text x="38" y="56"  text-anchor="end" font-size="9.5" fill="currentColor" class="text-slate-400 dark:text-slate-500"> 75k</text>
                    <text x="38" y="94"  text-anchor="end" font-size="9.5" fill="currentColor" class="text-slate-400 dark:text-slate-500"> 50k</text>
                    <text x="38" y="131" text-anchor="end" font-size="9.5" fill="currentColor" class="text-slate-400 dark:text-slate-500"> 25k</text>
                    <text x="38" y="169" text-anchor="end" font-size="9.5" fill="currentColor" class="text-slate-400 dark:text-slate-500">  0k</text>
                    <!-- Area fill -->
                    <path d="M68,155 L144,118 L220,138 L296,88 L372,103 L448,58 L524,33 L524,165 L68,165 Z"
                          fill="url(#gp)"/>
                    <!-- Revenue line -->
                    <polyline points="68,155 144,118 220,138 296,88 372,103 448,58 524,33"
                              fill="none" stroke="#4f46e5" stroke-width="2.5"
                              stroke-linecap="round" stroke-linejoin="round"/>
                    <!-- Target line -->
                    <polyline points="68,148 144,136 220,124 296,113 372,100 448,88 524,74"
                              fill="none" stroke="#10b981" stroke-width="1.8"
                              stroke-dasharray="5 4" stroke-linecap="round" stroke-linejoin="round"/>
                    <!-- Dots -->
                    <circle cx="68"  cy="155" r="3.5" fill="#4f46e5" stroke="white" stroke-width="2"/>
                    <circle cx="144" cy="118" r="3.5" fill="#4f46e5" stroke="white" stroke-width="2"/>
                    <circle cx="220" cy="138" r="3.5" fill="#4f46e5" stroke="white" stroke-width="2"/>
                    <circle cx="296" cy="88"  r="3.5" fill="#4f46e5" stroke="white" stroke-width="2"/>
                    <circle cx="372" cy="103" r="3.5" fill="#4f46e5" stroke="white" stroke-width="2"/>
                    <circle cx="448" cy="58"  r="3.5" fill="#4f46e5" stroke="white" stroke-width="2"/>
                    <circle cx="524" cy="33"  r="5"   fill="#4f46e5" stroke="white" stroke-width="2.5"/>
                    <!-- Highlight tooltip -->
                    <rect x="498" y="8" width="52" height="18" rx="5" fill="#4f46e5"/>
                    <text x="524" y="20.5" text-anchor="middle" font-size="9" font-weight="700" fill="white">$90.1k ↑</text>
                    <!-- X labels -->
                    <text x="68"  y="182" text-anchor="middle" font-size="9.5" fill="currentColor" class="text-slate-400 dark:text-slate-500">Jan</text>
                    <text x="144" y="182" text-anchor="middle" font-size="9.5" fill="currentColor" class="text-slate-400 dark:text-slate-500">Feb</text>
                    <text x="220" y="182" text-anchor="middle" font-size="9.5" fill="currentColor" class="text-slate-400 dark:text-slate-500">Mar</text>
                    <text x="296" y="182" text-anchor="middle" font-size="9.5" fill="currentColor" class="text-slate-400 dark:text-slate-500">Apr</text>
                    <text x="372" y="182" text-anchor="middle" font-size="9.5" fill="currentColor" class="text-slate-400 dark:text-slate-500">May</text>
                    <text x="448" y="182" text-anchor="middle" font-size="9.5" fill="currentColor" class="text-slate-400 dark:text-slate-500">Jun</text>
                    <text x="524" y="182" text-anchor="middle" font-size="9.5" fill="currentColor" class="text-slate-400 dark:text-slate-500">Jul</text>
                </svg>
            </div>
        </div>

        <!-- Activity -->
        <div class="card p-5 flex flex-col">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-bold text-ink dark:text-white">Activity</h2>
                <button class="text-xs font-semibold text-primary hover:text-primarydark transition-colors">View all</button>
            </div>
            <div class="space-y-4 flex-1">

                <div class="flex gap-3">
                    <div class="w-8 h-8 shrink-0 bg-primarylt dark:bg-primary/15 rounded-full grid place-items-center">
                        <svg class="w-3.5 h-3.5 text-primary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm text-ink dark:text-slate-200 leading-snug"><span class="font-semibold">Sarah Chen</span> joined as a new user</p>
                        <p class="text-xs text-slate-400 mt-0.5">2 min ago</p>
                    </div>
                </div>

                <div class="flex gap-3">
                    <div class="w-8 h-8 shrink-0 bg-accent/10 rounded-full grid place-items-center">
                        <svg class="w-3.5 h-3.5 text-accent" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm text-ink dark:text-slate-200 leading-snug">Order <span class="font-semibold">#ORD-4821</span> fulfilled</p>
                        <p class="text-xs text-slate-400 mt-0.5">14 min ago</p>
                    </div>
                </div>

                <div class="flex gap-3">
                    <div class="w-8 h-8 shrink-0 bg-amber-100 dark:bg-amber-500/10 rounded-full grid place-items-center">
                        <svg class="w-3.5 h-3.5 text-amber-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm text-ink dark:text-slate-200 leading-snug">Payment of <span class="font-semibold">$3,200</span> received</p>
                        <p class="text-xs text-slate-400 mt-0.5">1 hr ago</p>
                    </div>
                </div>

                <div class="flex gap-3">
                    <div class="w-8 h-8 shrink-0 bg-red-100 dark:bg-red-500/10 rounded-full grid place-items-center">
                        <svg class="w-3.5 h-3.5 text-red-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm text-ink dark:text-slate-200 leading-snug">Server spike on <span class="font-semibold">us-east-1</span></p>
                        <p class="text-xs text-slate-400 mt-0.5">2 hr ago</p>
                    </div>
                </div>

                <div class="flex gap-3">
                    <div class="w-8 h-8 shrink-0 bg-violet-100 dark:bg-violet-500/10 rounded-full grid place-items-center">
                        <svg class="w-3.5 h-3.5 text-violet-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm text-ink dark:text-slate-200 leading-snug">Conversion hit <span class="font-semibold">4.2%</span> — new record</p>
                        <p class="text-xs text-slate-400 mt-0.5">3 hr ago</p>
                    </div>
                </div>

                <div class="flex gap-3">
                    <div class="w-8 h-8 shrink-0 bg-primarylt dark:bg-primary/10 rounded-full grid place-items-center">
                        <svg class="w-3.5 h-3.5 text-primary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm text-ink dark:text-slate-200 leading-snug">Q3 report exported by <span class="font-semibold">Mike R.</span></p>
                        <p class="text-xs text-slate-400 mt-0.5">5 hr ago</p>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- ── ORDERS TABLE ── -->
    <div class="card overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100 dark:border-slate-800 gap-4 flex-wrap">
            <div>
                <h2 class="font-bold text-ink dark:text-white">Recent Orders</h2>
                <p class="text-xs text-slate-400 mt-0.5">Last 6 transactions</p>
            </div>
            <div class="flex items-center gap-2">
                <select class="text-xs border border-slate-200 dark:border-slate-700
                         bg-white dark:bg-slate-800
                         text-slate-600 dark:text-slate-300
                         rounded-lg px-3 py-1.5 focus:outline-none focus:border-primary transition-colors">
                    <option>All Status</option>
                    <option>Completed</option>
                    <option>Pending</option>
                    <option>Refunded</option>
                </select>
                <button class="flex items-center gap-1.5 text-xs border border-slate-200 dark:border-slate-700
                         bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300
                         rounded-lg px-3 py-1.5
                         hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="8 17 12 21 16 17"/><line x1="12" y1="12" x2="12" y2="21"/><path d="M20.88 18.09A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.29"/></svg>
                    Export
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                <tr class="border-b border-slate-100 dark:border-slate-800 bg-slate-50/80 dark:bg-slate-800/40">
                    <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400">Order ID</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400">Customer</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 hidden md:table-cell">Product</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 hidden lg:table-cell">Date</th>
                    <th class="text-right px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400">Amount</th>
                    <th class="text-center px-5 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400">Status</th>
                </tr>
                </thead>
                <tbody>
                <tr class="trow">
                    <td class="px-5 py-3.5 text-xs font-bold text-primary">#ORD-4821</td>
                    <td class="px-4 py-3.5"><div class="flex items-center gap-2.5"><img src="https://i.pravatar.cc/32?img=5"  class="w-7 h-7 rounded-full object-cover hidden sm:block" alt=""/><span class="font-semibold text-ink dark:text-slate-200">Mariana T.</span></div></td>
                    <td class="px-4 py-3.5 text-slate-500 dark:text-slate-400 hidden md:table-cell">Pro Plan — Annual</td>
                    <td class="px-4 py-3.5 text-slate-400 text-xs hidden lg:table-cell">Oct 12, 2026</td>
                    <td class="px-4 py-3.5 text-right font-bold text-ink dark:text-slate-200 tabular-nums">$299.00</td>
                    <td class="px-5 py-3.5 text-center"><span class="badge-green">● Completed</span></td>
                </tr>
                <tr class="trow">
                    <td class="px-5 py-3.5 text-xs font-bold text-primary">#ORD-4820</td>
                    <td class="px-4 py-3.5"><div class="flex items-center gap-2.5"><img src="https://i.pravatar.cc/32?img=11" class="w-7 h-7 rounded-full object-cover hidden sm:block" alt=""/><span class="font-semibold text-ink dark:text-slate-200">Diego R.</span></div></td>
                    <td class="px-4 py-3.5 text-slate-500 dark:text-slate-400 hidden md:table-cell">Starter — Monthly</td>
                    <td class="px-4 py-3.5 text-slate-400 text-xs hidden lg:table-cell">Oct 12, 2026</td>
                    <td class="px-4 py-3.5 text-right font-bold text-ink dark:text-slate-200 tabular-nums">$49.00</td>
                    <td class="px-5 py-3.5 text-center"><span class="badge-amber">● Pending</span></td>
                </tr>
                <tr class="trow">
                    <td class="px-5 py-3.5 text-xs font-bold text-primary">#ORD-4819</td>
                    <td class="px-4 py-3.5"><div class="flex items-center gap-2.5"><img src="https://i.pravatar.cc/32?img=22" class="w-7 h-7 rounded-full object-cover hidden sm:block" alt=""/><span class="font-semibold text-ink dark:text-slate-200">Yuki N.</span></div></td>
                    <td class="px-4 py-3.5 text-slate-500 dark:text-slate-400 hidden md:table-cell">Enterprise — Annual</td>
                    <td class="px-4 py-3.5 text-slate-400 text-xs hidden lg:table-cell">Oct 11, 2026</td>
                    <td class="px-4 py-3.5 text-right font-bold text-ink dark:text-slate-200 tabular-nums">$1,199.00</td>
                    <td class="px-5 py-3.5 text-center"><span class="badge-green">● Completed</span></td>
                </tr>
                <tr class="trow">
                    <td class="px-5 py-3.5 text-xs font-bold text-primary">#ORD-4818</td>
                    <td class="px-4 py-3.5"><div class="flex items-center gap-2.5"><img src="https://i.pravatar.cc/32?img=33" class="w-7 h-7 rounded-full object-cover hidden sm:block" alt=""/><span class="font-semibold text-ink dark:text-slate-200">Kevin P.</span></div></td>
                    <td class="px-4 py-3.5 text-slate-500 dark:text-slate-400 hidden md:table-cell">Pro Plan — Monthly</td>
                    <td class="px-4 py-3.5 text-slate-400 text-xs hidden lg:table-cell">Oct 11, 2026</td>
                    <td class="px-4 py-3.5 text-right font-bold text-ink dark:text-slate-200 tabular-nums">$79.00</td>
                    <td class="px-5 py-3.5 text-center"><span class="badge-red">● Refunded</span></td>
                </tr>
                <tr class="trow">
                    <td class="px-5 py-3.5 text-xs font-bold text-primary">#ORD-4817</td>
                    <td class="px-4 py-3.5"><div class="flex items-center gap-2.5"><img src="https://i.pravatar.cc/32?img=44" class="w-7 h-7 rounded-full object-cover hidden sm:block" alt=""/><span class="font-semibold text-ink dark:text-slate-200">Aisha M.</span></div></td>
                    <td class="px-4 py-3.5 text-slate-500 dark:text-slate-400 hidden md:table-cell">Starter — Annual</td>
                    <td class="px-4 py-3.5 text-slate-400 text-xs hidden lg:table-cell">Oct 10, 2026</td>
                    <td class="px-4 py-3.5 text-right font-bold text-ink dark:text-slate-200 tabular-nums">$399.00</td>
                    <td class="px-5 py-3.5 text-center"><span class="badge-green">● Completed</span></td>
                </tr>
                <tr class="trow !border-b-0">
                    <td class="px-5 py-3.5 text-xs font-bold text-primary">#ORD-4816</td>
                    <td class="px-4 py-3.5"><div class="flex items-center gap-2.5"><img src="https://i.pravatar.cc/32?img=51" class="w-7 h-7 rounded-full object-cover hidden sm:block" alt=""/><span class="font-semibold text-ink dark:text-slate-200">Tom W.</span></div></td>
                    <td class="px-4 py-3.5 text-slate-500 dark:text-slate-400 hidden md:table-cell">Enterprise — Monthly</td>
                    <td class="px-4 py-3.5 text-slate-400 text-xs hidden lg:table-cell">Oct 10, 2026</td>
                    <td class="px-4 py-3.5 text-right font-bold text-ink dark:text-slate-200 tabular-nums">$249.00</td>
                    <td class="px-5 py-3.5 text-center"><span class="badge-slate">● Processing</span></td>
                </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="px-5 py-3.5 border-t border-slate-100 dark:border-slate-800
                  flex items-center justify-between text-xs text-slate-400 gap-4">
            <span>Showing <strong class="text-ink dark:text-slate-300">6</strong> of <strong class="text-ink dark:text-slate-300">1,284</strong> orders</span>
            <div class="flex items-center gap-1">
                <button class="w-7 h-7 grid place-items-center rounded-lg
                         border border-slate-200 dark:border-slate-700
                         hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors opacity-40 cursor-not-allowed">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
                </button>
                <button class="w-7 h-7 grid place-items-center rounded-lg bg-primary border border-primary text-white text-xs font-bold">1</button>
                <button class="w-7 h-7 grid place-items-center rounded-lg border border-slate-200 dark:border-slate-700
                         text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors text-xs font-medium">2</button>
                <button class="w-7 h-7 grid place-items-center rounded-lg border border-slate-200 dark:border-slate-700
                         text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors text-xs font-medium">3</button>
                <button class="w-7 h-7 grid place-items-center rounded-lg
                         border border-slate-200 dark:border-slate-700
                         hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors text-slate-500 dark:text-slate-400">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                </button>
            </div>
        </div>
    </div>
@endsection

@push('js')

@endpush
