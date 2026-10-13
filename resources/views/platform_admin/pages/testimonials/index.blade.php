@extends('platform_admin.layout.master')

@section('contents')
    <div class="space-y-6">
        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-slate-900 tracking-tight dark:text-white">Merchant Testimonials</h1>
                <p class="text-sm text-slate-500 mt-0.5">Manage and organize merchant review and testimonial
                    entries.</p>
            </div>
            <button type="button" onclick="openModal('createTestimonialModal')"
                    class="inline-flex items-center justify-center px-4 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-500 active:bg-indigo-700 rounded-lg shadow-sm transition-all gap-2 cursor-pointer">
                <i class="fa-solid fa-plus text-xs"></i>
                <span>Add Testimonial</span>
            </button>
        </div>

        <!-- Main Card Container -->
        <div class="card overflow-hidden">
            <div
                class="flex items-center justify-between px-5 py-4 border-b border-slate-100 dark:border-slate-800 gap-4 flex-wrap">
                <div>
                    <h2 class="font-bold text-ink dark:text-white">Testimonials</h2>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm table-fixed">
                    <thead>
                    <tr class="border-b border-slate-100 dark:border-slate-800 bg-slate-50/80 dark:bg-slate-800/40">
                        <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 w-16">
                            SN
                        </th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 w-20">
                            Sort
                        </th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 w-40">
                            Merchant
                        </th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 w-32">
                            Rating
                        </th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 w-40">
                            Testimonial Content
                        </th>
                        <th class="text-center px-5 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 w-28">
                            Status
                        </th>
                        <th class="text-center px-5 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 w-32">
                            Actions
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($testimonials as $testi)
                        <tr class="trow border-b border-slate-100 dark:border-slate-800 hover:bg-slate-50/50 transition-colors">
                            <td class="px-5 py-3.5 text-xs font-bold text-primary">{{ $loop->iteration }}</td>
                            <td class="px-5 py-3.5 text-xs font-bold text-primary">{{ $testi->sort_order }}</td>
                            <td class="px-4 py-3.5">
                                <div class="flex items-center gap-3">
                                    @if($testi->merchant_image)
                                        <img src="{{ asset('storage/' . $testi->merchant_image) }}"
                                             class="rounded-full object-cover w-9 h-9 border border-slate-200"
                                             alt="Avatar">
                                    @else
                                        <div
                                            class="w-9 h-9 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-xs border border-indigo-100">
                                            {{ substr($testi->merchant_name, 0, 1) }}
                                        </div>
                                    @endif
                                    <div>
                                        <div
                                            class="font-semibold text-slate-900 dark:text-white">{{ $testi->merchant_name }}</div>
                                        <div class="text-xs text-slate-400">{{ $testi->merchant_title ?? '-' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3.5 text-amber-500 text-xs">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="fa-{{ $i <= $testi->rating ? 'solid' : 'regular' }} fa-star"></i>
                                @endfor
                            </td>
                            <td class="px-4 py-3.5 text-slate-500 dark:text-slate-400 max-w-xs truncate">{{ Str::limit($testi->text, 80) }}</td>
                            <td class="px-5 py-3.5 text-center">
                                @if($testi->status)
                                    <span
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Active
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-rose-50 text-rose-700 border border-rose-200/60">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                        Inactive
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-center font-bold text-ink dark:text-slate-200 tabular-nums">
                                <div class="inline-flex items-center gap-1.5">
                                    <button type="button" onclick="openModal('editTestimonialModal{{ $testi->id }}')"
                                            class="p-2 text-sky-600 hover:text-sky-700 hover:bg-sky-50 rounded-lg transition-colors cursor-pointer"
                                            title="Edit Testimonial">
                                        <i class="fa-solid fa-pen text-xs"></i>
                                    </button>
                                    <form action="{{ route('admin.testimonials.destroy', $testi->id) }}" method="POST"
                                          class="inline" onsubmit="return confirm('Delete this testimonial?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="p-2 text-rose-600 hover:text-rose-700 hover:bg-rose-50 rounded-lg transition-colors cursor-pointer"
                                                title="Delete Testimonial">
                                            <i class="fa-solid fa-trash text-xs"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>

                        <!-- Edit Modal -->
                        <div id="editTestimonialModal{{ $testi->id }}"
                             class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
                            <div
                                class="bg-white rounded-2xl shadow-xl w-full max-w-2xl overflow-hidden border border-slate-100 transform transition-all">
                                <form method="POST" action="{{ route('admin.testimonials.update', $testi->id) }}"
                                      enctype="multipart/form-data">
                                    @csrf
                                    @method('PUT')
                                    <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                                        <h3 class="text-base font-bold text-slate-900">Edit Testimonial Entry</h3>
                                        <button type="button"
                                                onclick="closeModal('editTestimonialModal{{ $testi->id }}')"
                                                class="text-slate-400 hover:text-slate-600 p-1 rounded-lg transition-colors cursor-pointer">
                                            <i class="fa-solid fa-xmark text-lg"></i>
                                        </button>
                                    </div>
                                    <div class="p-6 space-y-4">
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <label
                                                    class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Merchant
                                                    Name</label>
                                                <input type="text" name="merchant_name"
                                                       value="{{ $testi->merchant_name }}" required
                                                       class="w-full px-3.5 py-2 text-sm dark:text-black bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                                            </div>
                                            <div>
                                                <label
                                                    class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Title
                                                    / Store Name</label>
                                                <input type="text" name="merchant_title"
                                                       value="{{ $testi->merchant_title }}"
                                                       class="w-full px-3.5 py-2 text-sm dark:text-black bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                                            </div>
                                        </div>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <label
                                                    class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Rating
                                                    (1-5 Stars)</label>
                                                <select name="rating"
                                                        class="w-full px-3.5 py-2 text-sm dark:text-black bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                                                    @for($r = 5; $r >= 1; $r--)
                                                        <option
                                                            value="{{ $r }}" {{ $testi->rating == $r ? 'selected' : '' }}>{{ $r }}
                                                            Stars
                                                        </option>
                                                    @endfor
                                                </select>
                                            </div>
                                            <div>
                                                <label
                                                    class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Merchant
                                                    Photo</label>
                                                <input type="file" name="merchant_image"
                                                       class="w-full px-3.5 py-1.5 text-sm dark:text-black bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all file:mr-4 file:py-1 file:px-2 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                                                @if($testi->merchant_image)
                                                    <img src="{{ asset('storage/'. $testi->merchant_image) }}"  class="p-1 w-30 h-auto" alt="">
                                                @endif
                                            </div>
                                        </div>
                                        <div>
                                            <label
                                                class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Testimonial
                                                Content</label>
                                            <textarea name="text" rows="3" required
                                                      class="w-full px-3.5 py-2 text-sm dark:text-black bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">{{ $testi->text }}</textarea>
                                        </div>
                                        <div>
                                            <label
                                                class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Sort
                                                Order</label>
                                            <input type="number" name="sort_order" value="{{ $testi->sort_order }}"
                                                   min="1"
                                                   class="w-full px-3.5 py-2 text-sm dark:text-black bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                                        </div>
                                        <div class="flex items-center gap-3 pt-2">
                                            <label class="relative inline-flex items-center cursor-pointer">
                                                <input type="checkbox" name="status"
                                                       id="testiStatusEdit{{ $testi->id }}" value="1"
                                                       class="sr-only peer" {{ $testi->status ? 'checked' : '' }}>
                                                <div
                                                    class="w-9 h-5 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-indigo-600"></div>
                                            </label>
                                            <label for="testiStatusEdit{{ $testi->id }}"
                                                   class="text-sm font-medium text-slate-700 cursor-pointer select-none">Active
                                                Status</label>
                                        </div>
                                    </div>
                                    <div
                                        class="flex items-center justify-end gap-3 px-6 py-4 bg-slate-50 border-t border-slate-100">
                                        <button type="button"
                                                onclick="closeModal('editTestimonialModal{{ $testi->id }}')"
                                                class="px-4 py-2 text-sm font-semibold text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 rounded-lg transition-colors cursor-pointer">
                                            Close
                                        </button>
                                        <button type="submit"
                                                class="px-4 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-500 rounded-lg shadow-sm transition-colors cursor-pointer">
                                            Update Testimonial
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-slate-400 py-12 text-sm">No testimonials found.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Create Modal -->
    <div id="createTestimonialModal"
         class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
        <div
            class="bg-white rounded-2xl shadow-xl w-full max-w-2xl overflow-hidden border border-slate-100 transform transition-all">
            <form method="POST" action="{{ route('admin.testimonials.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                    <h3 class="text-base font-bold text-slate-900">Add New Testimonial</h3>
                    <button type="button" onclick="closeModal('createTestimonialModal')"
                            class="text-slate-400 hover:text-slate-600 p-1 rounded-lg transition-colors cursor-pointer">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>
                <div class="p-6 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Merchant
                                Name</label>
                            <input type="text" name="merchant_name" required placeholder="Jane Doe"
                                   class="w-full px-3.5 py-2 text-sm dark:text-black bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Title
                                / Store Name</label>
                            <input type="text" name="merchant_title" placeholder="CEO, GlowApparel"
                                   class="w-full px-3.5 py-2 text-sm dark:text-black bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Rating
                                (1-5 Stars)</label>
                            <select name="rating"
                                    class="w-full px-3.5 py-2 text-sm dark:text-black bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                                <option value="5" selected>5 Stars</option>
                                <option value="4">4 Stars</option>
                                <option value="3">3 Stars</option>
                                <option value="2">2 Stars</option>
                                <option value="1">1 Star</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Merchant
                                Photo</label>
                            <input type="file" name="merchant_image"
                                   class="w-full px-3.5 py-1.5 text-sm dark:text-black bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all file:mr-4 file:py-1 file:px-2 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Testimonial
                            Content</label>
                        <textarea name="text" rows="3" required placeholder="Write merchant feedback here..."
                                  class="w-full px-3.5 py-2 text-sm dark:text-black bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Sort
                            Order</label>
                        <input type="number" name="sort_order" value="1" min="1"
                               class="w-full px-3.5 py-2 text-sm dark:text-black bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                    </div>
                    <div class="flex items-center gap-3 pt-2">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="status" id="testiStatusCreate" value="1" checked
                                   class="sr-only peer">
                            <div
                                class="w-9 h-5 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-indigo-600"></div>
                        </label>
                        <label for="testiStatusCreate"
                               class="text-sm font-medium text-slate-700 cursor-pointer select-none">Active
                            Status</label>
                    </div>
                </div>
                <div class="flex items-center justify-end gap-3 px-6 py-4 bg-slate-50 border-t border-slate-100">
                    <button type="button" onclick="closeModal('createTestimonialModal')"
                            class="px-4 py-2 text-sm font-semibold text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 rounded-lg transition-colors cursor-pointer">
                        Close
                    </button>
                    <button type="submit"
                            class="px-4 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-500 rounded-lg shadow-sm transition-colors cursor-pointer">
                        Save Testimonial
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
