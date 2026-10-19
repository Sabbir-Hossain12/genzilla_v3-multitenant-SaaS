@extends('platform_admin.layout.master')

@section('contents')
    <div class="space-y-6 max-w-4xl mx-auto">
        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <a href="{{ route('admin.subscriptions.index') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-500 inline-flex items-center gap-1.5 mb-1 transition-colors">
                    <i class="fa-solid fa-arrow-left text-[10px]"></i>
                    Back to Subscriptions
                </a>
                <h1 class="text-xl font-bold text-slate-900 tracking-tight dark:text-white">Edit Subscription</h1>
                <p class="text-sm text-slate-500 mt-0.5">Update subscription status and billing periods for {{ $subscription->tenant->store_name ?? $subscription->tenant->name ?? 'Store' }}.</p>
            </div>
        </div>

        <!-- Main Card Container -->
        <div class="card overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800">
                <h2 class="font-bold text-ink dark:text-white">Subscription Details</h2>
            </div>

            <form method="POST" action="{{ route('admin.subscriptions.update', $subscription->id) }}">
                @csrf
                @method('PUT')
                <div class="p-6 space-y-5">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1.5">Tenant / Store</label>
                        <input type="text" disabled value="{{ $subscription->tenant->store_name ?? $subscription->tenant->name }} ({{ $subscription->tenant->subdomain }})"
                               class="w-full px-3.5 py-2 text-sm bg-slate-100 dark:bg-slate-800/60 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-600 dark:text-slate-400 cursor-not-allowed">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1.5">Subscription Plan</label>
                            <select name="plan_id" required
                                    class="w-full px-3.5 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                                @foreach($plans as $p)
                                    <option value="{{ $p->id }}" {{ $subscription->plan_id == $p->id ? 'selected' : '' }}>{{ $p->name }} (${{ $p->monthly_price }}/mo, ${{ $p->yearly_price }}/yr)</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1.5">Billing Interval</label>
                            <select name="billing_interval" required
                                    class="w-full px-3.5 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                                <option value="monthly" {{ $subscription->billing_interval == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                <option value="yearly" {{ $subscription->billing_interval == 'yearly' ? 'selected' : '' }}>Yearly</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1.5">Status</label>
                        <select name="subscription_status" required
                                class="w-full px-3.5 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                            @foreach(['trialing', 'active', 'past_due', 'expired', 'cancelled', 'superseded'] as $st)
                                <option value="{{ $st }}" {{ $subscription->subscription_status == $st ? 'selected' : '' }}>{{ ucfirst($st) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1.5">Period Starts At</label>
                            <input type="date" name="current_period_starts_at"
                                   value="{{ $subscription->current_period_starts_at ? $subscription->current_period_starts_at->format('Y-m-d') : '' }}"
                                   class="w-full px-3.5 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1.5">Period Ends At</label>
                            <input type="date" name="current_period_ends_at"
                                   value="{{ $subscription->current_period_ends_at ? $subscription->current_period_ends_at->format('Y-m-d') : '' }}"
                                   class="w-full px-3.5 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1.5">Trial Ends At</label>
                        <input type="date" name="trial_ends_at"
                               value="{{ $subscription->trial_ends_at ? $subscription->trial_ends_at->format('Y-m-d') : '' }}"
                               class="w-full px-3.5 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                        <p class="text-xs text-slate-400 mt-1">Leave empty if not in trial mode.</p>
                    </div>

                    <div class="pt-3 border-t border-slate-100 dark:border-slate-800 space-y-3">
                        <div class="flex items-center gap-2">
                            <input type="checkbox" id="page_create_invoice" name="create_invoice" value="1"
                                   class="w-4 h-4 text-indigo-600 border-slate-300 rounded focus:ring-indigo-500">
                            <label for="page_create_invoice" class="text-xs font-semibold text-slate-700 dark:text-slate-300">
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
                    <a href="{{ route('admin.subscriptions.index') }}"
                       class="px-4 py-2 text-sm font-semibold text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 rounded-lg transition-colors cursor-pointer">
                        Cancel
                    </a>
                    <button type="submit"
                            class="px-4 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-500 rounded-lg shadow-sm transition-colors cursor-pointer">
                        Update Subscription
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
