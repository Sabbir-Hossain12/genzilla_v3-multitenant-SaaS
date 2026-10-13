@extends('platform_admin.layout.master')

@section('contents')
    <div class="space-y-6">
        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-slate-900 tracking-tight dark:text-white">Subscription Plans</h1>
                <p class="text-sm text-slate-500 mt-0.5">Manage and organize platform pricing tiers and resource limits.</p>
            </div>
            <button type="button" onclick="openModal('createPlanModal')"
                    class="inline-flex items-center justify-center px-4 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-500 active:bg-indigo-700 rounded-lg shadow-sm transition-all gap-2 cursor-pointer">
                <i class="fa-solid fa-plus text-xs"></i>
                <span>Add New Plan</span>
            </button>
        </div>

        <!-- Main Card Container -->
        <div class="card overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100 dark:border-slate-800 gap-4 flex-wrap">
                <div>
                    <h2 class="font-bold text-ink dark:text-white">Plans</h2>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm table-fixed">
                    <thead>
                    <tr class="border-b border-slate-100 dark:border-slate-800 bg-slate-50/80 dark:bg-slate-800/40">
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 w-44">Plan Name</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 w-28">Monthly Price</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 w-28">Yearly Price</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 w-48">Limits</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 w-28">Custom Domain</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 w-32">Multi-Currency</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 w-20">Subs</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 w-28">Status</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 w-28">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($plans as $plan)
                        <tr class="trow border-b border-slate-100 dark:border-slate-800 hover:bg-slate-50/50 transition-colors">
                            <td class="px-4 py-3.5 w-44 truncate">
                                <div class="font-semibold text-slate-900 dark:text-white truncate">{{ $plan->name }}</div>
                                <div class="text-xs text-slate-400 truncate font-mono"><code>{{ $plan->slug }}</code></div>
                            </td>
                            <td class="px-4 py-3.5 w-28 font-bold text-emerald-600">${{ number_format($plan->monthly_price, 2) }}</td>
                            <td class="px-4 py-3.5 w-28 font-bold text-indigo-600">${{ number_format($plan->yearly_price, 2) }}</td>
                            <td class="px-4 py-3.5 w-48 truncate">
                                <div class="flex items-center gap-1 flex-wrap text-xs">
                                    <span class="px-1.5 py-0.5 rounded bg-slate-100 text-slate-700 border border-slate-200">{{ $plan->max_products }} Prods</span>
                                    <span class="px-1.5 py-0.5 rounded bg-slate-100 text-slate-700 border border-slate-200">{{ $plan->max_staff_accounts }} Staff</span>
                                    <span class="px-1.5 py-0.5 rounded bg-slate-100 text-slate-700 border border-slate-200">{{ $plan->max_storage_mb }} MB</span>
                                </div>
                            </td>
                            <td class="px-4 py-3.5 w-28">
                                @if($plan->allow_custom_domain)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200/60">Yes</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-600 border border-slate-200">No</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 w-32 truncate">
                                @if($plan->has_multi_currency)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200/60">Yes ({{ $plan->max_currencies_supported }})</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-600 border border-slate-200">No</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-center w-20">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-sky-50 text-sky-700 border border-sky-200">{{ $plan->subscriptions_count }}</span>
                            </td>
                            <td class="px-4 py-3.5 text-center w-28">
                                @if($plan->status)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-rose-50 text-rose-700 border border-rose-200/60">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                        Inactive
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-center font-bold text-ink dark:text-slate-200 tabular-nums w-28">
                                <div class="inline-flex items-center gap-1.5">
                                    <button type="button" onclick="openModal('editPlanModal{{ $plan->id }}')"
                                            class="p-2 text-sky-600 hover:text-sky-700 hover:bg-sky-50 rounded-lg transition-colors cursor-pointer"
                                            title="Edit Plan">
                                        <i class="fa-solid fa-pen text-xs"></i>
                                    </button>
                                    <form action="{{ route('admin.plans.destroy', $plan->id) }}" method="POST"
                                          class="inline" onsubmit="return confirm('Delete this plan?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="p-2 text-rose-600 hover:text-rose-700 hover:bg-rose-50 rounded-lg transition-colors cursor-pointer"
                                                title="Delete Plan">
                                            <i class="fa-solid fa-trash text-xs"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>

                        <!-- Edit Modal -->
                        <div id="editPlanModal{{ $plan->id }}"
                             class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
                            <div class="bg-white rounded-2xl shadow-xl w-full max-w-3xl overflow-hidden border border-slate-100 transform transition-all max-h-[90vh] flex flex-col">
                                <form method="POST" action="{{ route('admin.plans.update', $plan->id) }}" class="flex flex-col h-full overflow-hidden">
                                    @csrf
                                    @method('PUT')
                                    <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 shrink-0">
                                        <h3 class="text-base font-bold text-slate-900">Edit Plan: {{ $plan->name }}</h3>
                                        <button type="button" onclick="closeModal('editPlanModal{{ $plan->id }}')"
                                                class="text-slate-400 hover:text-slate-600 p-1 rounded-lg transition-colors cursor-pointer">
                                            <i class="fa-solid fa-xmark text-lg"></i>
                                        </button>
                                    </div>
                                    <div class="p-6 space-y-4 overflow-y-auto flex-1">
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Plan Name</label>
                                                <input type="text" name="name" value="{{ old('name', $plan->name) }}" required
                                                       class="w-full px-3.5 py-2 text-sm dark:text-black bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Slug</label>
                                                <input type="text" name="slug" value="{{ old('slug', $plan->slug) }}"
                                                       class="w-full px-3.5 py-2 text-sm dark:text-black bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                                            </div>
                                        </div>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Monthly Price ($)</label>
                                                <input type="number" step="0.01" name="monthly_price" value="{{ old('monthly_price', $plan->monthly_price) }}" required
                                                       class="w-full px-3.5 py-2 text-sm dark:text-black bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Yearly Price ($)</label>
                                                <input type="number" step="0.01" name="yearly_price" value="{{ old('yearly_price', $plan->yearly_price) }}" required
                                                       class="w-full px-3.5 py-2 text-sm dark:text-black bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                                            </div>
                                        </div>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Stripe Monthly Price ID</label>
                                                <input type="text" name="stripe_monthly_price_id" value="{{ old('stripe_monthly_price_id', $plan->stripe_monthly_price_id) }}"
                                                       class="w-full px-3.5 py-2 text-sm dark:text-black bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Stripe Yearly Price ID</label>
                                                <input type="text" name="stripe_yearly_price_id" value="{{ old('stripe_yearly_price_id', $plan->stripe_yearly_price_id) }}"
                                                       class="w-full px-3.5 py-2 text-sm dark:text-black bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                                            </div>
                                        </div>

                                        <hr class="border-slate-200 my-2">
                                        <h4 class="text-xs font-bold uppercase tracking-wider text-indigo-600 flex items-center gap-1.5">
                                            <i class="fa-solid fa-sliders"></i> Resource & Feature Limits
                                        </h4>

                                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                            <div>
                                                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Max Products</label>
                                                <input type="number" name="max_products" value="{{ old('max_products', $plan->max_products) }}" required
                                                       class="w-full px-3.5 py-2 text-sm dark:text-black bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Max Staff</label>
                                                <input type="number" name="max_staff_accounts" value="{{ old('max_staff_accounts', $plan->max_staff_accounts) }}" required
                                                       class="w-full px-3.5 py-2 text-sm dark:text-black bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Storage (MB)</label>
                                                <input type="number" name="max_storage_mb" value="{{ old('max_storage_mb', $plan->max_storage_mb) }}" required
                                                       class="w-full px-3.5 py-2 text-sm dark:text-black bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">File Size (MB)</label>
                                                <input type="number" name="max_digital_file_size_mb" value="{{ old('max_digital_file_size_mb', $plan->max_digital_file_size_mb) }}" required
                                                       class="w-full px-3.5 py-2 text-sm dark:text-black bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 bg-slate-50 p-4 rounded-xl border border-slate-200">
                                            <label class="flex items-center gap-2 cursor-pointer text-sm font-medium text-slate-700">
                                                <input type="checkbox" name="allow_custom_domain" value="1" {{ $plan->allow_custom_domain ? 'checked' : '' }} class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                                <span>Custom Domain</span>
                                            </label>
                                            <label class="flex items-center gap-2 cursor-pointer text-sm font-medium text-slate-700">
                                                <input type="checkbox" name="has_multi_currency" value="1" {{ $plan->has_multi_currency ? 'checked' : '' }} class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                                <span>Multi-Currency</span>
                                            </label>
                                            <label class="flex items-center gap-2 cursor-pointer text-sm font-medium text-slate-700">
                                                <input type="checkbox" name="has_advanced_reporting" value="1" {{ $plan->has_advanced_reporting ? 'checked' : '' }} class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                                <span>Advanced Reporting</span>
                                            </label>
                                            <label class="flex items-center gap-2 cursor-pointer text-sm font-medium text-slate-700">
                                                <input type="checkbox" name="has_custom_rbac" value="1" {{ $plan->has_custom_rbac ? 'checked' : '' }} class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                                <span>Custom RBAC</span>
                                            </label>
                                            <label class="flex items-center gap-2 cursor-pointer text-sm font-medium text-slate-700">
                                                <input type="checkbox" name="has_audit_logs" value="1" {{ $plan->has_audit_logs ? 'checked' : '' }} class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                                <span>Audit Logs</span>
                                            </label>
                                        </div>

                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Max Currencies Supported</label>
                                                <input type="number" name="max_currencies_supported" value="{{ old('max_currencies_supported', $plan->max_currencies_supported) }}" required
                                                       class="w-full px-3.5 py-2 text-sm dark:text-black bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                                            </div>
                                            <div class="flex items-center gap-3 pt-6">
                                                <label class="relative inline-flex items-center cursor-pointer">
                                                    <input type="checkbox" name="status" id="planStatusEdit{{ $plan->id }}" value="1"
                                                           class="sr-only peer" {{ $plan->status ? 'checked' : '' }}>
                                                    <div class="w-9 h-5 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-indigo-600"></div>
                                                </label>
                                                <label for="planStatusEdit{{ $plan->id }}" class="text-sm font-medium text-slate-700 cursor-pointer select-none">Active Status</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-end gap-3 px-6 py-4 bg-slate-50 border-t border-slate-100 shrink-0">
                                        <button type="button" onclick="closeModal('editPlanModal{{ $plan->id }}')"
                                                class="px-4 py-2 text-sm font-semibold text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 rounded-lg transition-colors cursor-pointer">
                                            Close
                                        </button>
                                        <button type="submit"
                                                class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-500 rounded-lg shadow-sm transition-colors cursor-pointer">
                                            <i class="fa-solid fa-floppy-disk text-xs"></i>
                                            <span>Update Plan</span>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-slate-400 py-12 text-sm">No subscription plans found.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Create Modal -->
    <div id="createPlanModal"
         class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-3xl overflow-hidden border border-slate-100 transform transition-all max-h-[90vh] flex flex-col">
            <form method="POST" action="{{ route('admin.plans.store') }}" class="flex flex-col h-full overflow-hidden">
                @csrf
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 shrink-0">
                    <h3 class="text-base font-bold text-slate-900">Create New Plan</h3>
                    <button type="button" onclick="closeModal('createPlanModal')"
                            class="text-slate-400 hover:text-slate-600 p-1 rounded-lg transition-colors cursor-pointer">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>
                <div class="p-6 space-y-4 overflow-y-auto flex-1">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Plan Name</label>
                            <input type="text" name="name" required placeholder="Growth Plan"
                                   class="w-full px-3.5 py-2 text-sm dark:text-black bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Slug</label>
                            <input type="text" name="slug" placeholder="growth"
                                   class="w-full px-3.5 py-2 text-sm dark:text-black bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Monthly Price ($)</label>
                            <input type="number" step="0.01" name="monthly_price" value="49.00" required
                                   class="w-full px-3.5 py-2 text-sm dark:text-black bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Yearly Price ($)</label>
                            <input type="number" step="0.01" name="yearly_price" value="490.00" required
                                   class="w-full px-3.5 py-2 text-sm dark:text-black bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                        </div>
                    </div>

                    <hr class="border-slate-200 my-2">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-indigo-600 flex items-center gap-1.5">
                        <i class="fa-solid fa-sliders"></i> Resource & Feature Limits
                    </h4>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Max Products</label>
                            <input type="number" name="max_products" value="200" required
                                   class="w-full px-3.5 py-2 text-sm dark:text-black bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Max Staff</label>
                            <input type="number" name="max_staff_accounts" value="5" required
                                   class="w-full px-3.5 py-2 text-sm dark:text-black bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Storage (MB)</label>
                            <input type="number" name="max_storage_mb" value="500" required
                                   class="w-full px-3.5 py-2 text-sm dark:text-black bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">File Size (MB)</label>
                            <input type="number" name="max_digital_file_size_mb" value="50" required
                                   class="w-full px-3.5 py-2 text-sm dark:text-black bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 bg-slate-50 p-4 rounded-xl border border-slate-200">
                        <label class="flex items-center gap-2 cursor-pointer text-sm font-medium text-slate-700">
                            <input type="checkbox" name="allow_custom_domain" value="1" checked class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            <span>Custom Domain</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer text-sm font-medium text-slate-700">
                            <input type="checkbox" name="has_multi_currency" value="1" checked class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            <span>Multi-Currency</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer text-sm font-medium text-slate-700">
                            <input type="checkbox" name="has_advanced_reporting" value="1" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            <span>Advanced Reporting</span>
                        </label>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Max Currencies Supported</label>
                            <input type="number" name="max_currencies_supported" value="3" required
                                   class="w-full px-3.5 py-2 text-sm dark:text-black bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                        </div>
                        <div class="flex items-center gap-3 pt-6">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="status" id="planStatusCreate" value="1" checked
                                       class="sr-only peer">
                                <div class="w-9 h-5 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-indigo-600"></div>
                            </label>
                            <label for="planStatusCreate" class="text-sm font-medium text-slate-700 cursor-pointer select-none">Active Status</label>
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-end gap-3 px-6 py-4 bg-slate-50 border-t border-slate-100 shrink-0">
                    <button type="button" onclick="closeModal('createPlanModal')"
                            class="px-4 py-2 text-sm font-semibold text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 rounded-lg transition-colors cursor-pointer">
                        Close
                    </button>
                    <button type="submit"
                            class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-500 rounded-lg shadow-sm transition-colors cursor-pointer">
                        <i class="fa-solid fa-floppy-disk text-xs"></i>
                        <span>Save Plan</span>
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
    </script>
@endsection
