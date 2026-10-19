@extends('platform_admin.layout.master')

@section('contents')
    <div class="space-y-6">
        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-slate-900 tracking-tight dark:text-white">Merchant Subscriptions</h1>
                <p class="text-sm text-slate-500 mt-0.5">Manage and organize tenant subscription billing and status assignments.</p>
            </div>
            <button type="button" onclick="openModal('createSubscriptionModal')"
                    class="inline-flex items-center justify-center px-4 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-500 active:bg-indigo-700 rounded-lg shadow-sm transition-all gap-2 cursor-pointer">
                <i class="fa-solid fa-plus text-xs"></i>
                <span>Assign / Create Subscription</span>
            </button>
        </div>

        <!-- Main Card Container -->
        <div class="card overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100 dark:border-slate-800 gap-4 flex-wrap">
                <div>
                    <h2 class="font-bold text-ink dark:text-white">Subscriptions</h2>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm table-fixed">
                    <thead>
                    <tr class="border-b border-slate-100 dark:border-slate-800 bg-slate-50/80 dark:bg-slate-800/40">
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 w-16">#</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 w-52">Tenant / Store</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 w-36">Plan</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 w-28">Interval</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 w-28">Status</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 w-32">Period Start</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 w-32">Period End</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 w-24">Invoices</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 w-28">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($subscriptions as $sub)
                        <tr class="trow border-b border-slate-100 dark:border-slate-800 hover:bg-slate-50/50 transition-colors">
                            <td class="px-4 py-3.5 text-xs font-bold text-primary w-16">{{ $loop->iteration }}</td>
                            <td class="px-4 py-3.5 w-52 truncate">
                                <div class="font-semibold text-slate-900 dark:text-white truncate" title="{{ $sub->tenant->store_name ?? $sub->tenant->name ?? 'N/A' }}">
                                    {{ $sub->tenant->store_name ?? $sub->tenant->name ?? 'N/A' }}
                                </div>
                                <div class="text-xs text-slate-400 truncate font-mono">
                                    <code>{{ $sub->tenant->subdomain ?? 'N/A' }}</code>
                                </div>
                            </td>
                            <td class="px-4 py-3.5 w-36 truncate">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-100 dark:bg-indigo-900/30 dark:text-indigo-300 dark:border-indigo-800">
                                    {{ $sub->plan->name ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 w-28 truncate capitalize text-xs text-slate-600 dark:text-slate-300 font-medium">
                                {{ $sub->billing_interval }}
                            </td>
                            <td class="px-4 py-3.5 text-center w-28">
                                @switch($sub->subscription_status)
                                    @case('active')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200/60 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            Active
                                        </span>
                                        @break
                                    @case('trialing')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-amber-50 text-amber-700 border border-amber-200/60 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                            Trialing
                                        </span>
                                        @break
                                    @case('past_due')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-rose-50 text-rose-700 border border-rose-200/60 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                            Past Due
                                        </span>
                                        @break
                                    @case('expired')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-red-50 text-red-700 border border-red-200/60 dark:bg-red-950/40 dark:text-red-300 dark:border-red-800">
                                            <span class="w-1.5 h-1.5 rounded-full bg-red-400"></span>
                                            Expired
                                        </span>
                                        @break
                                    @case('cancelled')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-600 border border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700">
                                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                            Cancelled
                                        </span>
                                        @break
                                    @case('superseded')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-purple-50 text-purple-700 border border-purple-200/60 dark:bg-purple-950/40 dark:text-purple-300 dark:border-purple-800">
                                            <span class="w-1.5 h-1.5 rounded-full bg-purple-400"></span>
                                            Superseded
                                        </span>
                                        @break
                                    @default
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-700 border border-slate-200 capitalize dark:bg-slate-800 dark:text-slate-300">
                                            {{ $sub->subscription_status }}
                                        </span>
                                @endswitch
                            </td>
                            <td class="px-4 py-3.5 text-xs text-slate-500 dark:text-slate-400 w-32 whitespace-nowrap">
                                {{ $sub->current_period_starts_at ? $sub->current_period_starts_at->format('M d, Y') : '-' }}
                            </td>
                            <td class="px-4 py-3.5 text-xs text-slate-500 dark:text-slate-400 w-32 whitespace-nowrap">
                                {{ $sub->current_period_ends_at ? $sub->current_period_ends_at->format('M d, Y') : '-' }}
                            </td>
                            <td class="px-4 py-3.5 text-center text-xs w-24">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                                    {{ $sub->invoices->count() }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-center font-bold text-ink dark:text-slate-200 tabular-nums w-28">
                                <button type="button" onclick="openModal('editSubModal{{ $sub->id }}')"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-sky-700 bg-sky-50 hover:bg-sky-100 rounded-lg transition-colors cursor-pointer border border-sky-200/60 dark:bg-sky-950/40 dark:text-sky-300 dark:border-sky-800"
                                        title="Edit Subscription">
                                    <i class="fa-solid fa-pen text-[10px]"></i>
                                    <span>Edit</span>
                                </button>
                            </td>
                        </tr>

                        <!-- Edit Modal -->
                        <div id="editSubModal{{ $sub->id }}"
                             class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
                            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl w-full max-w-2xl overflow-hidden border border-slate-100 dark:border-slate-800 transform transition-all">
                                <form method="POST" action="{{ route('admin.subscriptions.update', $sub->id) }}">
                                    @csrf
                                    @method('PUT')
                                    <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-slate-800">
                                        <div>
                                            <h3 class="text-base font-bold text-slate-900 dark:text-white">Update Subscription</h3>
                                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ $sub->tenant->store_name ?? $sub->tenant->name ?? 'Store' }} ({{ $sub->tenant->subdomain ?? '' }})</p>
                                        </div>
                                        <button type="button" onclick="closeModal('editSubModal{{ $sub->id }}')"
                                                class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1 rounded-lg transition-colors cursor-pointer">
                                            <i class="fa-solid fa-xmark text-lg"></i>
                                        </button>
                                    </div>
                                    <div class="p-6 space-y-4 max-h-[70vh] overflow-y-auto">
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1.5">Subscription Plan</label>
                                                <select name="plan_id" required
                                                        class="w-full px-3.5 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                                                    @foreach($plans as $p)
                                                        <option value="{{ $p->id }}" {{ $sub->plan_id == $p->id ? 'selected' : '' }}>{{ $p->name }} (${{ $p->monthly_price }}/mo, ${{ $p->yearly_price }}/yr)</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1.5">Billing Interval</label>
                                                <select name="billing_interval" required
                                                        class="w-full px-3.5 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                                                    <option value="monthly" {{ $sub->billing_interval == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                                    <option value="yearly" {{ $sub->billing_interval == 'yearly' ? 'selected' : '' }}>Yearly</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div>
                                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1.5">Status</label>
                                            <select name="subscription_status" required
                                                    class="w-full px-3.5 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                                                @foreach(['trialing', 'active', 'past_due', 'expired', 'cancelled', 'superseded'] as $st)
                                                    <option value="{{ $st }}" {{ $sub->subscription_status == $st ? 'selected' : '' }}>{{ ucfirst($st) }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1.5">Period Starts At</label>
                                                <input type="date" name="current_period_starts_at"
                                                       value="{{ $sub->current_period_starts_at ? $sub->current_period_starts_at->format('Y-m-d') : '' }}"
                                                       class="w-full px-3.5 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1.5">Period Ends At</label>
                                                <input type="date" name="current_period_ends_at"
                                                       value="{{ $sub->current_period_ends_at ? $sub->current_period_ends_at->format('Y-m-d') : '' }}"
                                                       class="w-full px-3.5 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                                            </div>
                                        </div>

                                        <div>
                                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1.5">Trial Ends At</label>
                                            <input type="date" name="trial_ends_at"
                                                   value="{{ $sub->trial_ends_at ? $sub->trial_ends_at->format('Y-m-d') : '' }}"
                                                   class="w-full px-3.5 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                                            <p class="text-xs text-slate-400 mt-1">Leave empty if not currently in trial mode.</p>
                                        </div>

                                        <div class="pt-2 border-t border-slate-100 dark:border-slate-800 space-y-3">
                                            <div class="flex items-center gap-2">
                                                <input type="checkbox" id="edit_create_invoice_{{ $sub->id }}" name="create_invoice" value="1"
                                                       class="w-4 h-4 text-indigo-600 border-slate-300 rounded focus:ring-indigo-500">
                                                <label for="edit_create_invoice_{{ $sub->id }}" class="text-xs font-semibold text-slate-700 dark:text-slate-300">
                                                    Generate settled invoice for this update
                                                </label>
                                            </div>
                                            <div>
                                                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1.5">Custom Invoice Amount (Optional)</label>
                                                <input type="number" step="0.01" min="0" name="invoice_amount" placeholder="Leave empty for plan default or $0.00 for trial"
                                                       class="w-full px-3.5 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-end gap-3 px-6 py-4 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-100 dark:border-slate-800">
                                        <button type="button" onclick="closeModal('editSubModal{{ $sub->id }}')"
                                                class="px-4 py-2 text-sm font-semibold text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 rounded-lg transition-colors cursor-pointer">
                                            Close
                                        </button>
                                        <button type="submit"
                                                class="px-4 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-500 rounded-lg shadow-sm transition-colors cursor-pointer">
                                            Update Subscription
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-slate-400 py-12 text-sm">No merchant subscriptions found.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Create Modal (One-Page Solution: User -> Tenant -> Domain -> Subscription -> Invoice -> Usage) -->
    <div id="createSubscriptionModal"
         class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl w-full max-w-3xl overflow-hidden border border-slate-100 dark:border-slate-800 transform transition-all">
            <form method="POST" action="{{ route('admin.subscriptions.store') }}" id="createSubscriptionForm">
                @csrf
                <input type="hidden" name="tenant_mode" id="tenant_mode_input" value="{{ old('tenant_mode', 'new') }}">

                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-slate-800">
                    <div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Assign / Create Merchant Subscription</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">One-page solution following TenantSubscriptionSeeder: User → Tenant → Domain → Subscription → Invoice → Usage.</p>
                    </div>
                    <button type="button" onclick="closeModal('createSubscriptionModal')"
                            class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1 rounded-lg transition-colors cursor-pointer">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <div class="p-6 space-y-5 max-h-[75vh] overflow-y-auto">
                    @if($errors->any())
                        <div class="p-3.5 rounded-lg bg-rose-50 border border-rose-200 text-xs text-rose-700 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800">
                            <div class="font-bold mb-1 flex items-center gap-1.5">
                                <i class="fa-solid fa-circle-exclamation"></i>
                                <span>Please resolve the following errors:</span>
                            </div>
                            <ul class="list-disc pl-5 space-y-0.5">
                                @foreach($errors->all() as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Tenant Scope Switcher Tabs -->
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-2">Select Scope</label>
                        <div class="grid grid-cols-2 gap-2 p-1 bg-slate-100 dark:bg-slate-800 rounded-xl">
                            <button type="button" id="tab_new_tenant" onclick="setTenantMode('new')"
                                    class="py-2 px-3 text-xs font-bold rounded-lg transition-all flex items-center justify-center gap-2 cursor-pointer shadow-xs bg-white text-indigo-600 dark:bg-slate-700 dark:text-white">
                                <i class="fa-solid fa-sparkles text-amber-500"></i>
                                <span>Create New Store & Merchant</span>
                            </button>
                            <button type="button" id="tab_existing_tenant" onclick="setTenantMode('existing')"
                                    class="py-2 px-3 text-xs font-medium rounded-lg transition-all flex items-center justify-center gap-2 cursor-pointer text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white">
                                <i class="fa-solid fa-store"></i>
                                <span>Select Existing Store ({{ $tenants->count() }})</span>
                            </button>
                        </div>
                    </div>

                    <!-- ========================================== -->
                    <!-- NEW TENANT & MERCHANT USER FIELDS SECTION -->
                    <!-- ========================================== -->
                    <div id="new_tenant_section" class="space-y-4">
                        <!-- Merchant User Card -->
                        <div class="p-4 bg-slate-50/80 dark:bg-slate-800/40 rounded-xl border border-slate-200/70 dark:border-slate-800 space-y-3.5">
                            <div class="flex items-center gap-2 text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider border-b border-slate-200/60 dark:border-slate-800 pb-2">
                                <i class="fa-solid fa-user-circle text-indigo-600 dark:text-indigo-400 text-sm"></i>
                                <span>1. Merchant Account (User Model)</span>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                                <div>
                                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">
                                        Owner Name <span class="text-rose-500">*</span>
                                    </label>
                                    <input type="text" name="user_name" id="user_name" value="{{ old('user_name') }}" placeholder="e.g. Dr. John Doe"
                                           class="w-full px-3 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">
                                        Owner Email <span class="text-rose-500">*</span>
                                    </label>
                                    <input type="email" name="user_email" id="user_email" value="{{ old('user_email') }}" placeholder="e.g. john@medicarepharma.com"
                                           class="w-full px-3 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">
                                        Password (Optional)
                                    </label>
                                    <input type="text" name="password" id="user_password" placeholder="Leave empty for default: password"
                                           class="w-full px-3 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                                    <p class="text-[11px] text-slate-400 mt-0.5">Defaults to <code class="font-mono text-indigo-600 bg-indigo-50 px-1 py-0.5 rounded">password</code> if left blank.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Storefront Details Card -->
                        <div class="p-4 bg-slate-50/80 dark:bg-slate-800/40 rounded-xl border border-slate-200/70 dark:border-slate-800 space-y-3.5">
                            <div class="flex items-center gap-2 text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider border-b border-slate-200/60 dark:border-slate-800 pb-2">
                                <i class="fa-solid fa-store text-indigo-600 dark:text-indigo-400 text-sm"></i>
                                <span>2. Storefront Details (Tenant & TenantDomain Models)</span>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                                <div>
                                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">
                                        Store Name <span class="text-rose-500">*</span>
                                    </label>
                                    <input type="text" name="store_name" id="store_name" value="{{ old('store_name') }}" placeholder="e.g. MediCare Pharmacy"
                                           oninput="autoGenerateSubdomain(this.value)"
                                           class="w-full px-3 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">
                                        Subdomain <span class="text-rose-500">*</span>
                                    </label>
                                    <div class="flex rounded-lg shadow-2xs">
                                        <input type="text" name="subdomain" id="subdomain" value="{{ old('subdomain') }}" placeholder="medicare"
                                               class="w-full px-3 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-l-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                                        <span class="inline-flex items-center px-2.5 rounded-r-lg border border-l-0 border-slate-300 dark:border-slate-700 bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-300 text-xs font-mono">
                                            .{{ config('app.base_domain', 'genzilla.com') }}
                                        </span>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Support Email</label>
                                    <input type="email" name="support_email" value="{{ old('support_email') }}" placeholder="support@medicarepharma.com"
                                           class="w-full px-3 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Support Phone</label>
                                    <input type="text" name="support_phone" value="{{ old('support_phone') }}" placeholder="+1 800 555 0199"
                                           class="w-full px-3 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Store Currency</label>
                                    <select name="currency"
                                            class="w-full px-3 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                                        <option value="USD" {{ old('currency', 'USD') == 'USD' ? 'selected' : '' }}>USD ($)</option>
                                        <option value="EUR" {{ old('currency') == 'EUR' ? 'selected' : '' }}>EUR (€)</option>
                                        <option value="GBP" {{ old('currency') == 'GBP' ? 'selected' : '' }}>GBP (£)</option>
                                        <option value="CAD" {{ old('currency') == 'CAD' ? 'selected' : '' }}>CAD ($)</option>
                                        <option value="AUD" {{ old('currency') == 'AUD' ? 'selected' : '' }}>AUD ($)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Timezone</label>
                                    <select name="timezone"
                                            class="w-full px-3 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                                        <option value="UTC" selected>UTC</option>
                                        <option value="America/New_York">America/New_York</option>
                                        <option value="America/Los_Angeles">America/Los_Angeles</option>
                                        <option value="Europe/London">Europe/London</option>
                                        <option value="Asia/Dhaka">Asia/Dhaka</option>
                                        <option value="Asia/Dubai">Asia/Dubai</option>
                                        <option value="Asia/Singapore">Asia/Singapore</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ============================================== -->
                    <!-- EXISTING STORE SELECTION (WHEN MODE IS EXISTING)-->
                    <!-- ============================================== -->
                    <div id="existing_tenant_section" class="hidden">
                        <div class="p-4 bg-slate-50/80 dark:bg-slate-800/40 rounded-xl border border-slate-200/70 dark:border-slate-800 space-y-2">
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                                Select Existing Tenant / Store <span class="text-rose-500">*</span>
                            </label>
                            <select name="tenant_id" id="tenant_id_select"
                                    class="w-full px-3.5 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                                <option value="" disabled selected>-- Select Tenant Store --</option>
                                @foreach($tenants as $t)
                                    <option value="{{ $t->id }}" {{ old('tenant_id') == $t->id ? 'selected' : '' }}>
                                        {{ $t->store_name ?? $t->name }} (Subdomain: {{ $t->subdomain }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- ============================================== -->
                    <!-- 3. SUBSCRIPTION, INVOICE & USAGE DETAILS       -->
                    <!-- ============================================== -->
                    <div class="p-4 bg-slate-50/80 dark:bg-slate-800/40 rounded-xl border border-slate-200/70 dark:border-slate-800 space-y-3.5">
                        <div class="flex items-center gap-2 text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider border-b border-slate-200/60 dark:border-slate-800 pb-2">
                            <i class="fa-solid fa-receipt text-indigo-600 dark:text-indigo-400 text-sm"></i>
                            <span>3. Subscription & Invoicing (TenantSubscription, TenantInvoice, TenantUsage)</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1.5">Select Plan <span class="text-rose-500">*</span></label>
                                <select name="plan_id" id="create_plan_id" required
                                        class="w-full px-3.5 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                                    @foreach($plans as $p)
                                        <option value="{{ $p->id }}" {{ old('plan_id') == $p->id ? 'selected' : '' }}>
                                            {{ $p->name }} (${{ $p->monthly_price }}/mo, ${{ $p->yearly_price }}/yr)
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1.5">Billing Interval <span class="text-rose-500">*</span></label>
                                <select name="billing_interval" required
                                        class="w-full px-3.5 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                                    <option value="monthly" {{ old('billing_interval', 'monthly') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                    <option value="yearly" {{ old('billing_interval') == 'yearly' ? 'selected' : '' }}>Yearly</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1.5">Initial Subscription Status <span class="text-rose-500">*</span></label>
                            <select name="subscription_status" id="create_subscription_status" required onchange="handleStatusChange(this.value)"
                                    class="w-full px-3.5 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                                <option value="trialing" {{ old('subscription_status', 'trialing') == 'trialing' ? 'selected' : '' }}>Trialing (14-Day Free Manual Trial)</option>
                                <option value="active" {{ old('subscription_status') == 'active' ? 'selected' : '' }}>Active (Full Paid Access)</option>
                                <option value="past_due" {{ old('subscription_status') == 'past_due' ? 'selected' : '' }}>Past Due</option>
                                <option value="expired" {{ old('subscription_status') == 'expired' ? 'selected' : '' }}>Expired</option>
                                <option value="cancelled" {{ old('subscription_status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1.5">Period Starts At</label>
                                <input type="date" name="current_period_starts_at" id="create_starts_at" value="{{ old('current_period_starts_at', date('Y-m-d')) }}"
                                       class="w-full px-3.5 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1.5">Period Ends At</label>
                                <input type="date" name="current_period_ends_at" id="create_ends_at" value="{{ old('current_period_ends_at') }}"
                                       class="w-full px-3.5 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                                <p class="text-[11px] text-slate-400 mt-1">Leave empty to auto-calculate (14 days for trial, 1 mo/yr for active).</p>
                            </div>
                        </div>

                        <div id="trial_ends_wrapper">
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1.5">Trial Ends At</label>
                            <input type="date" name="trial_ends_at" id="create_trial_ends_at" value="{{ old('trial_ends_at') }}"
                                   class="w-full px-3.5 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                            <p class="text-[11px] text-slate-400 mt-1">Defaults to 14 days from start if status is Trialing.</p>
                        </div>

                        <div class="pt-3 border-t border-slate-200/60 dark:border-slate-800 space-y-3">
                            <div class="flex items-center gap-2">
                                <input type="checkbox" id="create_invoice" name="create_invoice" value="1" {{ old('create_invoice', '1') == '1' ? 'checked' : '' }}
                                       class="w-4 h-4 text-indigo-600 border-slate-300 rounded focus:ring-indigo-500">
                                <label for="create_invoice" class="text-xs font-semibold text-slate-700 dark:text-slate-300">
                                    Generate Initial Settled Invoice (INV-XXXX) as per seeder sequence
                                </label>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1.5">Custom Invoice Amount (Optional)</label>
                                <input type="number" step="0.01" min="0" name="invoice_amount" value="{{ old('invoice_amount') }}" placeholder="Leave empty for plan default or $0.00 for trial"
                                       class="w-full px-3.5 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 px-6 py-4 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" onclick="closeModal('createSubscriptionModal')"
                            class="px-4 py-2 text-sm font-semibold text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 rounded-lg transition-colors cursor-pointer">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-4 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-500 rounded-lg shadow-sm transition-colors cursor-pointer flex items-center gap-2">
                        <i class="fa-solid fa-check text-xs"></i>
                        <span>Provision & Assign Subscription</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Handlers Script -->
    <script>
        function openModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }
        }

        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.remove('flex');
                modal.classList.add('hidden');
            }
        }

        let isSubdomainUserModified = false;
        const subdomainInput = document.getElementById('subdomain');
        if (subdomainInput) {
            subdomainInput.addEventListener('input', function() {
                isSubdomainUserModified = true;
            });
        }

        function autoGenerateSubdomain(storeName) {
            if (!isSubdomainUserModified && subdomainInput) {
                const slug = storeName.toLowerCase()
                    .replace(/[^a-z0-9]+/g, '-')
                    .replace(/^-+|-+$/g, '');
                subdomainInput.value = slug;
            }
        }

        function setTenantMode(mode) {
            const modeInput = document.getElementById('tenant_mode_input');
            const newSection = document.getElementById('new_tenant_section');
            const existingSection = document.getElementById('existing_tenant_section');
            const tabNew = document.getElementById('tab_new_tenant');
            const tabExisting = document.getElementById('tab_existing_tenant');

            const userName = document.getElementById('user_name');
            const userEmail = document.getElementById('user_email');
            const storeName = document.getElementById('store_name');
            const subdomain = document.getElementById('subdomain');
            const tenantSelect = document.getElementById('tenant_id_select');

            modeInput.value = mode;

            if (mode === 'new') {
                newSection.classList.remove('hidden');
                existingSection.classList.add('hidden');

                tabNew.className = "py-2 px-3 text-xs font-bold rounded-lg transition-all flex items-center justify-center gap-2 cursor-pointer shadow-xs bg-white text-indigo-600 dark:bg-slate-700 dark:text-white";
                tabExisting.className = "py-2 px-3 text-xs font-medium rounded-lg transition-all flex items-center justify-center gap-2 cursor-pointer text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white";

                if (userName) userName.required = true;
                if (userEmail) userEmail.required = true;
                if (storeName) storeName.required = true;
                if (subdomain) subdomain.required = true;
                if (tenantSelect) tenantSelect.required = false;
            } else {
                newSection.classList.add('hidden');
                existingSection.classList.remove('hidden');

                tabExisting.className = "py-2 px-3 text-xs font-bold rounded-lg transition-all flex items-center justify-center gap-2 cursor-pointer shadow-xs bg-white text-indigo-600 dark:bg-slate-700 dark:text-white";
                tabNew.className = "py-2 px-3 text-xs font-medium rounded-lg transition-all flex items-center justify-center gap-2 cursor-pointer text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white";

                if (userName) userName.required = false;
                if (userEmail) userEmail.required = false;
                if (storeName) storeName.required = false;
                if (subdomain) subdomain.required = false;
                if (tenantSelect) tenantSelect.required = true;
            }
        }

        function handleStatusChange(status) {
            const trialWrapper = document.getElementById('trial_ends_wrapper');
            if (trialWrapper) {
                if (status === 'trialing') {
                    trialWrapper.style.display = 'block';
                } else {
                    trialWrapper.style.display = 'none';
                }
            }
        }

        // Initialize state on page load
        document.addEventListener('DOMContentLoaded', function () {
            const initialMode = document.getElementById('tenant_mode_input')?.value || 'new';
            setTenantMode(initialMode);

            const statusEl = document.getElementById('create_subscription_status');
            if (statusEl) {
                handleStatusChange(statusEl.value);
            }

            @if($errors->any())
                openModal('createSubscriptionModal');
            @endif
        });
    </script>
@endsection
