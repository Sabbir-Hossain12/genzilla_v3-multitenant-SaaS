@extends('platform_admin.layout.master')

@section('contents')
    <div class="space-y-6">
        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-slate-900 tracking-tight dark:text-white">Frequently Asked Questions
                    (FAQs)</h1>
                <p class="text-sm text-slate-500 mt-0.5">Manage and organize system-wide FAQ entries.</p>
            </div>
            <button type="button" onclick="openModal('createFaqModal')"
                    class="inline-flex items-center justify-center px-4 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-500 active:bg-indigo-700 rounded-lg shadow-sm transition-all gap-2 cursor-pointer">
                <i class="fa-solid fa-plus text-xs"></i>
                <span>Add FAQ Entry</span>
            </button>
        </div>

        <div class="card overflow-hidden">
            <div
                class="flex items-center justify-between px-5 py-4 border-b border-slate-100 dark:border-slate-800 gap-4 flex-wrap">
                <div>
                    <h2 class="font-bold text-ink dark:text-white">FAQs</h2>
                    {{--                    <p class="text-xs text-slate-400 mt-0.5">Last 6 transactions</p>--}}
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
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="8 17 12 21 16 17"></polyline>
                            <line x1="12" y1="12" x2="12" y2="21"></line>
                            <path d="M20.88 18.09A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.29"></path>
                        </svg>
                        Export
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                    <tr class="border-b border-slate-100 dark:border-slate-800 bg-slate-50/80 dark:bg-slate-800/40">
                        <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400">SN</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400">Sort
                        </th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400">
                            Question
                        </th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400  md:table-cell">
                            Answers
                        </th>

                        <th class="text-center px-5 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400">
                            Status
                        </th>
                        <th class="text-center px-5 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400">
                            Actions
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($faqs as $faq)
                        <tr class="trow">
                            <td class="px-5 py-3.5 text-xs font-bold text-primary">{{ $loop->iteration }}</td>
                            <td class="px-5 py-3.5 text-xs font-bold text-primary">{{ $faq->sort_order }}</td>
                            <td class="px-4 py-3.5 text-slate-500 dark:text-slate-400  md:table-cell w-50">{{ $faq->question ?? '' }}</td>
                            <td class="px-4 py-3.5 text-slate-500 dark:text-slate-400  md:table-cell w-100">{{ $faq->answer ?? '' }}</td>
                            <td class="px-5 py-3.5 text-center"><span
                                    class="badge-green">● {{ $faq->status ? 'Active' : 'Inactive' }}</span></td>
                            <td class="px-4 py-3.5 text-center font-bold text-ink dark:text-slate-200 tabular-nums">
                                <div class="inline-flex items-center gap-1.5">
                                    <button type="button" onclick="openModal('editFaqModal{{ $faq->id }}')"
                                            class="p-2 text-sky-600 hover:text-sky-700 hover:bg-sky-50 rounded-lg transition-colors cursor-pointer"
                                            title="Edit FAQ">
                                        <i class="fa-solid fa-pen text-xs"></i>
                                    </button>
                                    <form action="{{ route('admin.faqs.destroy', $faq->id) }}" method="POST"
                                          class="inline" onsubmit="return confirm('Delete this FAQ entry?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="p-2 text-rose-600 hover:text-rose-700 hover:bg-rose-50 rounded-lg transition-colors cursor-pointer"
                                                title="Delete FAQ">
                                            <i class="fa-solid fa-trash text-xs"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>

                        <!-- Edit Modal -->
                        <div id="editFaqModal{{ $faq->id }}"
                             class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
                            <div
                                class="bg-white rounded-2xl shadow-xl w-full max-w-2xl overflow-hidden border border-slate-100 transform transition-all">
                                <form method="POST" action="{{ route('admin.faqs.update', $faq->id) }}">
                                    @csrf
                                    @method('PUT')
                                    <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                                        <h3 class="text-base font-bold text-slate-900">Edit FAQ Entry</h3>
                                        <button type="button" onclick="closeModal('editFaqModal{{ $faq->id }}')"
                                                class="text-slate-400 hover:text-slate-600 p-1 rounded-lg transition-colors cursor-pointer">
                                            <i class="fa-solid fa-xmark text-lg"></i>
                                        </button>
                                    </div>
                                    <div class="p-6 space-y-4">
                                        <div>
                                            <label
                                                class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Question</label>
                                            <input type="text" name="question" value="{{ $faq->question }}" required
                                                   class="w-full px-3.5 py-2 text-sm dark:text-black bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                                        </div>
                                        <div>
                                            <label
                                                class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Answer</label>
                                            <textarea name="answer" rows="4" required
                                                      class="w-full px-3.5 py-2 text-sm dark:text-black bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">{{ $faq->answer }}</textarea>
                                        </div>
                                        <div>
                                            <label
                                                class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Sort
                                                Order</label>
                                            <input type="number" name="sort_order" value="{{ $faq->sort_order }}"
                                                   min="1"
                                                   class="w-full px-3.5 py-2 text-sm dark:text-black bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                                        </div>
                                        <div class="flex items-center gap-3 pt-2">
                                            <label class="relative inline-flex items-center cursor-pointer">
                                                <input type="checkbox" name="status" id="faqStatusEdit{{ $faq->id }}"
                                                       value="1"
                                                       class="sr-only peer" {{ $faq->status ? 'checked' : '' }}>
                                                <div
                                                    class="w-9 h-5 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-indigo-600"></div>
                                            </label>
                                            <label for="faqStatusEdit{{ $faq->id }}"
                                                   class="text-sm font-medium text-slate-700 cursor-pointer select-none">Active
                                                Status</label>
                                        </div>
                                    </div>
                                    <div
                                        class="flex items-center justify-end gap-3 px-6 py-4 bg-slate-50 border-t border-slate-100">
                                        <button type="button" onclick="closeModal('editFaqModal{{ $faq->id }}')"
                                                class="px-4 py-2 text-sm font-semibold text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 rounded-lg transition-colors cursor-pointer">
                                            Close
                                        </button>
                                        <button type="submit"
                                                class="px-4 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-500 rounded-lg shadow-sm transition-colors cursor-pointer">
                                            Update FAQ
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @empty
                    @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="px-5 py-3.5 border-t border-slate-100 dark:border-slate-800
                  flex items-center justify-between text-xs text-slate-400 gap-4">
                <span>Showing <strong class="text-ink dark:text-slate-300">6</strong> of <strong
                        class="text-ink dark:text-slate-300">1,284</strong> orders</span>
                <div class="flex items-center gap-1">
                    <button class="w-7 h-7 grid place-items-center rounded-lg
                         border border-slate-200 dark:border-slate-700
                         hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors opacity-40 cursor-not-allowed">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="15 18 9 12 15 6"></polyline>
                        </svg>
                    </button>
                    <button
                        class="w-7 h-7 grid place-items-center rounded-lg bg-primary border border-primary text-white text-xs font-bold">
                        1
                    </button>
                    <button class="w-7 h-7 grid place-items-center rounded-lg border border-slate-200 dark:border-slate-700
                         text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors text-xs font-medium">
                        2
                    </button>
                    <button class="w-7 h-7 grid place-items-center rounded-lg border border-slate-200 dark:border-slate-700
                         text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors text-xs font-medium">
                        3
                    </button>
                    <button class="w-7 h-7 grid place-items-center rounded-lg
                         border border-slate-200 dark:border-slate-700
                         hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors text-slate-500 dark:text-slate-400">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Modal -->
    <div id="createFaqModal"
         class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
        <div
            class="bg-white rounded-2xl shadow-xl w-full max-w-2xl overflow-hidden border border-slate-100 transform transition-all">
            <form method="POST" action="{{ route('admin.faqs.store') }}">
                @csrf
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                    <h3 class="text-base font-bold text-slate-900">Add New FAQ Entry</h3>
                    <button type="button" onclick="closeModal('createFaqModal')"
                            class="text-slate-400 hover:text-slate-600 p-1 rounded-lg transition-colors cursor-pointer">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Question</label>
                        <input type="text" name="question" required placeholder="How long is the free trial?"
                               class="w-full px-3.5 py-2 text-sm dark:text-black bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                    </div>
                    <div>
                        <label
                            class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Answer</label>
                        <textarea name="answer" rows="4" required
                                  class="w-full px-3.5 py-2 text-sm bg-white border dark:text-black border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Sort
                            Order</label>
                        <input type="number" name="sort_order" value="1" min="1"
                               class="w-full px-3.5 py-2 text-sm dark:text-black bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                    </div>
                    <div class="flex items-center gap-3 pt-2">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="status" id="faqStatusCreate" value="1" checked
                                   class="sr-only peer">
                            <div
                                class="w-9 h-5 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-indigo-600"></div>
                        </label>
                        <label for="faqStatusCreate"
                               class="text-sm font-medium text-slate-700 cursor-pointer select-none">Active
                            Status</label>
                    </div>
                </div>
                <div class="flex items-center justify-end gap-3 px-6 py-4 bg-slate-50 border-t border-slate-100">
                    <button type="button" onclick="closeModal('createFaqModal')"
                            class="px-4 py-2 text-sm font-semibold text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 rounded-lg transition-colors cursor-pointer">
                        Close
                    </button>
                    <button type="submit"
                            class="px-4 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-500 rounded-lg shadow-sm transition-colors cursor-pointer">
                        Save FAQ
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Lightweight Modal Handlers (Add to your layout or script section if not already present) -->
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
