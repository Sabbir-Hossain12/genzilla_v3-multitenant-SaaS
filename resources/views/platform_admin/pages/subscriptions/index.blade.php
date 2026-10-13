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
                <span>Assign Subscription</span>
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
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 w-32">Billing Interval</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 w-28">Status</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 w-32">Period Start</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 w-32">Period End</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 w-28">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($subscriptions as $sub)
                        <tr class="trow border-b border-slate-100 dark:border-slate-800 hover:bg-slate-50/50 transition-colors">
                            <td class="px-4 py-3.5 text-xs font-bold text-primary w-16">{{ $loop->iteration }}</td>
                            <td class="px-4 py-3.5 w-52 truncate">
                                <div class="font-semibold text-slate-900 dark:text-white truncate">{{ $sub->tenant->name ?? 'N/A' }}</div>
                                <div class="text-xs text-slate-400 truncate font-mono"><code>{{ $sub->tenant->subdomain ?? 'N/A' }}</code></div>
                            </td>
                            <td class="px-4 py-3.5 w-36 truncate">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-100">
                                    {{ $sub->plan->name ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 w-32 truncate capitalize text-xs text-slate-600 font-medium">
                                {{ $sub->billing_interval }}
                            </td>
                            <td class="px-4 py-3.5 text-center w-28">
                                @switch($sub->subscription_status)
                                    @case('active')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            Active
                                        </span>
                                        @break
                                    @case('trialing')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-amber-50 text-amber-700 border border-amber-200/60">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                            Trialing
                                        </span>
                                        @break
                                    @case('past_due')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-rose-50 text-rose-700 border border-rose-200/60">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                            Past Due
                                        </span>
                                        @break
                                    @case('cancelled')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-600 border border-slate-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                            Cancelled
                                        </span>
                                        @break
                                    @default
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-700 border border-slate-200 capitalize">
                                            {{ $sub->subscription_status }}
                                        </span>
                                @endswitch
                            </td>
                            <td class="px-4 py-3.5 text-xs text-slate-500 w-32 whitespace-nowrap">
                                {{ $sub->current_period_starts_at ? $sub->current_period_starts_at->format('M d, Y') : '-' }}
                            </td>
                            <td class="px-4 py-3.5 text-xs text-slate-500 w-32 whitespace-nowrap">
                                {{ $sub->current_period_ends_at ? $sub->current_period_ends_at->format('M d, Y') : '-' }}
                            </td>
                            <td class="px-4 py-3.5 text-center font-bold text-ink dark:text-slate-200 tabular-nums w-28">
                                <button type="button" onclick="openModal('editSubModal{{ $sub->id }}')"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-sky-700 bg-sky-50 hover:bg-sky-100 rounded-lg transition-colors cursor-pointer border border-sky-200/60"
                                        title="Edit Subscription">
                                    <i class="fa-solid fa-pen text-[10px]"></i>
                                    <span>Edit</span>
                                </button>
                            </td>
                        </tr>

                        <!-- Edit Modal -->
                        <div id="editSubModal{{ $sub->id }}"
                             class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
                            <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl overflow-hidden border border-slate-100 transform transition-all">
                                <form method="POST" action="{{ route('admin.subscriptions.update', $sub->id) }}">
                                    @csrf
                                    @method('PUT')
                                    <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                                        <h3 class="text-base font-bold text-slate-900">Update Subscription: {{ $sub->tenant->name ?? '' }}</h3>
                                        <button type="button" onclick="closeModal('editSubModal{{ $sub->id }}')"
                                                class="text-slate-400 hover:text-slate-600 p-1 rounded-lg transition-colors cursor-pointer">
                                            <i class="fa-solid fa-xmark text-lg"></i>
                                        </button>
                                    </div>
                                    <div class="p-6 space-y-4">
                                        <div>
                                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Subscription Plan</label>
                                            <select name="plan_id" required
                                                    class="w-full px-3.5 py-2 text-sm dark:text-black bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                                                @foreach($plans as $p)
                                                    <option value="{{ $p->id }}" {{ $sub->plan_id == $p->id ? 'selected' : '' }}>{{ $p->name }} (${{ $p->monthly_price }}/mo)</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Billing Interval</label>
                                            <select name="billing_interval" required
                                                    class="w-full px-3.5 py-2 text-sm dark:text-black bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                                                <option value="monthly" {{ $sub->billing_interval == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                                <option value="yearly" {{ $sub->billing_interval == 'yearly' ? 'selected' : '' }}>Yearly</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Status</label>
                                            <select name="subscription_status" required
                                                    class="w-full px-3.5 py-2 text-sm dark:text-black bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                                                @foreach(['trialing', 'active', 'past_due', 'expired', 'cancelled', 'superseded'] as $st)
                                                    <option value="{{ $st }}" {{ $sub->subscription_status == $st ? 'selected' : '' }}>{{ ucfirst($st) }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Current Period End Date</label>
                                            <input type="date" name="current_period_ends_at" value="{{ $sub->current_period_ends_at ? $sub->current_period_ends_at->format('Y-m-d') : '' }}"
                                                   class="w-full px-3.5 py-2 text-sm dark:text-black bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-end gap-3 px-6 py-4 bg-slate-50 border-t border-slate-100">
                                        <button type="button" onclick="closeModal('editSubModal{{ $sub->id }}')"
                                                class="px-4 py-2 text-sm font-semibold text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 rounded-lg transition-colors cursor-pointer">
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
                            <td colspan="8" class="text-center text-slate-400 py-12 text-sm">No merchant subscriptions found.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Create Modal -->
    <div id="createSubscriptionModal"
         class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl overflow-hidden border border-slate-100 transform transition-all">
            <form method="POST" action="{{ route('admin.subscriptions.store') }}">
                @csrf
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                    <h3 class="text-base font-bold text-slate-900">Assign Merchant Subscription</h3>
                    <button type="button" onclick="closeModal('createSubscriptionModal')"
                            class="text-slate-400 hover:text-slate-600 p-1 rounded-lg transition-colors cursor-pointer">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Select Tenant</label>
                        <select name="tenant_id" required
                                class="w-full px-3.5 py-2 text-sm dark:text-black bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                            <option value="" disabled selected>-- Select Tenant --</option>
                            @foreach($tenants as $t)
                                <option value="{{ $t->id }}">{{ $t->name }} ({{ $t->subdomain }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Select Plan</label>
                        <select name="plan_id" required
                                class="w-full px-3.5 py-2 text-sm dark:text-black bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                            @foreach($plans as $p)
                                <option value="{{ $p->id }}">{{ $p->name }} (${{ $p->monthly_price }}/mo)</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Billing Interval</label>
                        <select name="billing_interval" required
                                class="w-full px-3.5 py-2 text-sm dark:text-black bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                            <option value="monthly">Monthly</option>
                            <option value="yearly">Yearly</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Status</label>
                        <select name="subscription_status" required
                                class="w-full px-3.5 py-2 text-sm dark:text-black bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                            <option value="active" selected>Active</option>
                            <option value="trialing">Trialing</option>
                        </select>
                    </div>
                </div>
                <div class="flex items-center justify-end gap-3 px-6 py-4 bg-slate-50 border-t border-slate-100">
                    <button type="button" onclick="closeModal('createSubscriptionModal')"
                            class="px-4 py-2 text-sm font-semibold text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 rounded-lg transition-colors cursor-pointer">
                        Close
                    </button>
                    <button type="submit"
                            class="px-4 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-500 rounded-lg shadow-sm transition-colors cursor-pointer">
                        Assign Subscription
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
