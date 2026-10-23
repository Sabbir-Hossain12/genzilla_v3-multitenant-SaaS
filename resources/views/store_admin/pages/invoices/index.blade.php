@extends('platform_admin.layout.master')

@section('contents')
    <div class="space-y-6">
        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-slate-900 tracking-tight dark:text-white">Tenant Invoices</h1>
                <p class="text-sm text-slate-500 mt-0.5">Manage and track tenant billing history and payment status.</p>
            </div>
        </div>

        <!-- Main Card Container -->
        <div class="card overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100 dark:border-slate-800 gap-4 flex-wrap">
                <div>
                    <h2 class="font-bold text-ink dark:text-white">Invoices</h2>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm table-fixed">
                    <thead>
                    <tr class="border-b border-slate-100 dark:border-slate-800 bg-slate-50/80 dark:bg-slate-800/40">
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 w-40">Invoice Number</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 w-48">Tenant</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 w-36">Plan</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 w-32">Total Amount</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 w-28">Status</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 w-36">Paid Date</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 w-28">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($invoices as $inv)
                        <tr class="trow border-b border-slate-100 dark:border-slate-800 hover:bg-slate-50/50 transition-colors">
                            <td class="px-4 py-3.5 w-40 font-mono text-xs font-bold text-slate-900 dark:text-white">
                                <code>{{ $inv->invoice_number }}</code>
                            </td>
                            <td class="px-4 py-3.5 w-48 truncate font-medium text-slate-900 dark:text-white" title="{{ $inv->tenant->name ?? 'N/A' }}">
                                {{ $inv->tenant->name ?? 'N/A' }}
                            </td>
                            <td class="px-4 py-3.5 w-36 truncate">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-slate-100 text-slate-700 border border-slate-200 dark:bg-slate-800 dark:text-slate-200 dark:border-slate-700">
                                    {{ $inv->subscription->plan->name ?? 'Subscription' }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 w-32 font-bold text-emerald-600 whitespace-nowrap">
                                {{ $inv->currency }} ${{ number_format($inv->total, 2) }}
                            </td>
                            <td class="px-4 py-3.5 text-center w-28">
                                @switch($inv->status)
                                    @case('paid')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            Paid
                                        </span>
                                        @break
                                    @case('open')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-amber-50 text-amber-700 border border-amber-200/60">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                            Open
                                        </span>
                                        @break
                                    @case('draft')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-600 border border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700">
                                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                            Draft
                                        </span>
                                        @break
                                    @default
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-rose-50 text-rose-700 border border-rose-200/60 capitalize">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                            {{ $inv->status }}
                                        </span>
                                @endswitch
                            </td>
                            <td class="px-4 py-3.5 text-xs text-slate-500 w-36 whitespace-nowrap">
                                {{ $inv->paid_at ? $inv->paid_at->format('M d, Y H:i') : '-' }}
                            </td>
                            <td class="px-4 py-3.5 text-center font-bold text-ink dark:text-slate-200 tabular-nums w-28">
                                <div class="inline-flex items-center gap-1.5">
                                    <a href="{{ route('admin.invoices.show', $inv->id) }}"
                                       class="p-2 text-indigo-600 hover:text-indigo-700 hover:bg-indigo-50 rounded-lg transition-colors"
                                       title="View Invoice">
                                        <i class="fa-solid fa-eye text-xs"></i>
                                    </a>
                                    <button type="button" onclick="openModal('editInvModal{{ $inv->id }}')"
                                            class="p-2 text-sky-600 hover:text-sky-700 hover:bg-sky-50 rounded-lg transition-colors cursor-pointer"
                                            title="Edit Status">
                                        <i class="fa-solid fa-pen text-xs"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <!-- Edit Status Modal -->
                        <div id="editInvModal{{ $inv->id }}"
                             class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
                            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden border border-slate-100 transform transition-all dark:bg-slate-900 dark:border-slate-800">
                                <form method="POST" action="{{ route('admin.invoices.update-status', $inv->id) }}">
                                    @csrf
                                    @method('PATCH')
                                    <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-slate-800">
                                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Update Invoice Status</h3>
                                        <button type="button" onclick="closeModal('editInvModal{{ $inv->id }}')"
                                                class="text-slate-400 hover:text-slate-600 p-1 rounded-lg transition-colors cursor-pointer">
                                            <i class="fa-solid fa-xmark text-lg"></i>
                                        </button>
                                    </div>
                                    <div class="p-6 space-y-4">
                                        <div>
                                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5 dark:text-slate-300">Invoice Status</label>
                                            <select name="status" required
                                                    class="w-full px-3.5 py-2 text-sm bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all dark:bg-slate-900 dark:border-slate-700">
                                                @foreach(['draft', 'open', 'paid', 'uncollectible', 'void'] as $st)
                                                    <option value="{{ $st }}" {{ $inv->status == $st ? 'selected' : '' }}>{{ ucfirst($st) }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-end gap-3 px-6 py-4 bg-slate-50 border-t border-slate-100 dark:bg-slate-800/50 dark:border-slate-800">
                                        <button type="button" onclick="closeModal('editInvModal{{ $inv->id }}')"
                                                class="px-4 py-2 text-sm font-semibold text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 rounded-lg transition-colors cursor-pointer dark:bg-slate-900 dark:text-slate-200 dark:border-slate-700">
                                            Close
                                        </button>
                                        <button type="submit"
                                                class="px-4 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-500 rounded-lg shadow-sm transition-colors cursor-pointer">
                                            Update Status
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-slate-400 py-12 text-sm">No tenant invoices found.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
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
