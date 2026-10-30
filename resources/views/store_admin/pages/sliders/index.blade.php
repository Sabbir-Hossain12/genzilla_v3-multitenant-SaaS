@extends('store_admin.layout.master')

@section('contents')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Hero Sliders</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Manage hero slider carousels featured prominently on your storefront homepage.</p>
        </div>
        @can('Store Slider Create')
            <button type="button" onclick="openCreateModal()"
                    class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-xs font-semibold text-white shadow-sm hover:bg-indigo-700 transition">
                <i class="fa-solid fa-plus text-xs"></i>
                <span>Add Hero Slide</span>
            </button>
        @endcan
    </div>

    {{-- Feedback Alerts --}}
    @if(session('success'))
        <div class="rounded-2xl bg-emerald-50 p-4 text-sm font-medium text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-200 border border-emerald-200 dark:border-emerald-800 flex items-center justify-between gap-3 shadow-sm">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-circle-check text-emerald-600 dark:text-emerald-400"></i>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-800 dark:text-emerald-300">&times;</button>
        </div>
    @endif

    @if ($errors->any())
        <div class="flex items-start gap-3 bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/30 text-red-700 dark:text-red-400 rounded-2xl px-4 py-3.5 text-sm shadow-sm">
            <i class="fa-solid fa-circle-exclamation text-base mt-0.5 shrink-0"></i>
            <ul class="flex-1 space-y-0.5 list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="card p-4 flex items-center gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-950/50 dark:text-indigo-400">
                <i class="fa-solid fa-images text-base"></i>
            </div>
            <div>
                <span class="text-xs text-slate-500 dark:text-slate-400 block">Total Slides</span>
                <span class="text-xl font-bold font-mono text-slate-900 dark:text-white">{{ $stats['total'] ?? 0 }}</span>
            </div>
        </div>

        <div class="card p-4 flex items-center gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-400">
                <i class="fa-solid fa-circle-check text-base"></i>
            </div>
            <div>
                <span class="text-xs text-slate-500 dark:text-slate-400 block">Active Slides</span>
                <span class="text-xl font-bold font-mono text-slate-900 dark:text-white">{{ $stats['active'] ?? 0 }}</span>
            </div>
        </div>

        <div class="card p-4 flex items-center gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400">
                <i class="fa-solid fa-eye-slash text-base"></i>
            </div>
            <div>
                <span class="text-xs text-slate-500 dark:text-slate-400 block">Hidden Slides</span>
                <span class="text-xl font-bold font-mono text-slate-900 dark:text-white">{{ $stats['inactive'] ?? 0 }}</span>
            </div>
        </div>
    </div>

    {{-- Main Sliders Table --}}
    <div class="card overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100 dark:border-slate-800 gap-4 flex-wrap">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-sliders text-slate-400"></i>
                <h2 class="font-bold text-slate-900 dark:text-white">Slides List</h2>
                <span class="rounded-full bg-slate-100 dark:bg-slate-800 px-2.5 py-0.5 text-xs font-semibold text-slate-600 dark:text-slate-400">
                    {{ $sliders->total() }}
                </span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                <tr class="border-b border-slate-100 dark:border-slate-800 bg-slate-50/80 dark:bg-slate-800/40">
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 w-16">Sort</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 w-36">Slide Image</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400">Slide Content</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400">Target Link</th>
                    <th class="text-center px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 w-28">Status</th>
                    <th class="text-right px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 w-28">Actions</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse($sliders as $slider)
                    <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/50 transition-colors">
                        <td class="px-4 py-3.5 text-xs font-mono font-semibold text-slate-500">
                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                {{ $slider->sort_order }}
                            </span>
                        </td>
                        <td class="px-4 py-3.5">
                            <div class="h-16 w-32 rounded-xl overflow-hidden bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 relative group">
                                <img src="{{ $slider->image_url }}" alt="{{ $slider->title ?? 'Slider Image' }}"
                                     class="h-full w-full object-cover group-hover:scale-105 transition-transform duration-300"
                                     onerror="this.src='https://placehold.co/600x300?text=No+Image'"/>
                            </div>
                        </td>
                        <td class="px-4 py-3.5">
                            <div class="font-semibold text-slate-900 dark:text-white">{{ $slider->title ?: 'Untitled Slide' }}</div>
                            @if($slider->subtitle)
                                <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 line-clamp-1">{{ $slider->subtitle }}</div>
                            @endif
                            @if($slider->button_text)
                                <span class="inline-flex items-center gap-1 mt-1 text-[11px] font-medium text-indigo-600 dark:text-indigo-400">
                                    <i class="fa-solid fa-arrow-right text-[9px]"></i> {{ $slider->button_text }}
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3.5 text-xs text-slate-500 font-mono">
                            @if($slider->link)
                                <a href="{{ $slider->link }}" target="_blank" class="hover:text-indigo-600 truncate max-w-xs block">
                                    {{ $slider->link }}
                                </a>
                            @else
                                <span class="text-slate-400">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3.5 text-center">
                            @can('Store Slider Edit')
                                <form action="{{ route('store.admin.sliders.toggle', $slider) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                            title="Click to toggle status"
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium cursor-pointer transition {{ $slider->is_active ? 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100 dark:bg-emerald-950/40 dark:text-emerald-300' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-400' }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $slider->is_active ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                        {{ $slider->is_active ? 'Active' : 'Inactive' }}
                                    </button>
                                </form>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium {{ $slider->is_active ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400' }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $slider->is_active ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                    {{ $slider->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            @endcan
                        </td>
                        <td class="px-4 py-3.5 text-right">
                            <div class="inline-flex items-center gap-1">
                                @can('Store Slider Edit')
                                    <button type="button"
                                            onclick='openEditModal(@json($slider), @json($slider->image_url))'
                                            title="Edit Slide"
                                            class="h-8 w-8 inline-flex items-center justify-center rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-950/50 transition">
                                        <i class="fa-solid fa-pen text-xs"></i>
                                    </button>
                                @endcan

                                @can('Store Slider Delete')
                                    <form action="{{ route('store.admin.sliders.destroy', $slider) }}" method="POST"
                                          onsubmit="return confirm('Are you sure you want to delete this hero slide?')"
                                          class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                title="Delete Slide"
                                                class="h-8 w-8 inline-flex items-center justify-center rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-950/50 transition">
                                            <i class="fa-solid fa-trash text-xs"></i>
                                        </button>
                                    </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                            <div class="flex flex-col items-center justify-center">
                                <div class="w-12 h-12 rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400 mb-3">
                                    <i class="fa-solid fa-images text-xl"></i>
                                </div>
                                <p class="text-sm font-semibold text-slate-700 dark:text-slate-300">No Hero Slides Configured</p>
                                <p class="text-xs text-slate-400 mt-1 max-w-sm">Add dynamic banner sliders to showcase flash sales and top healthcare offers on the homepage.</p>
                                @can('Store Slider Create')
                                    <button type="button" onclick="openCreateModal()"
                                            class="mt-4 inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-indigo-700 transition">
                                        <i class="fa-solid fa-plus text-xs"></i> Add First Slide
                                    </button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if($sliders->hasPages())
            <div class="p-4 border-t border-slate-100 dark:border-slate-800">
                {{ $sliders->links() }}
            </div>
        @endif
    </div>
</div>

{{-- Slide Modal (Create / Edit) --}}
<div id="sliderModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-sm p-4 sm:p-6 md:p-20 flex items-center justify-center">
    <div class="relative w-full max-w-2xl bg-white dark:bg-slate-900 rounded-3xl shadow-2xl border border-slate-100 dark:border-slate-800 overflow-hidden my-8">
        {{-- Modal Header --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-slate-800">
            <div>
                <h3 id="modalTitle" class="text-lg font-bold text-slate-900 dark:text-white">Add Hero Slide</h3>
                <p class="text-xs text-slate-400">Upload or configure a promotional homepage slider</p>
            </div>
            <button type="button" onclick="closeSliderModal()"
                    class="h-8 w-8 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-400 hover:text-slate-700 dark:hover:text-white grid place-items-center transition">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>

        {{-- Modal Form --}}
        <form id="sliderForm" method="POST" action="{{ route('store.admin.sliders.store') }}" enctype="multipart/form-data" class="p-6 space-y-4">
            @csrf
            <input type="hidden" name="_method" id="formMethod" value="POST">

            {{-- Title & Subtitle --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Slide Title</label>
                    <input type="text" name="title" id="slideTitle" placeholder="e.g. Medicine Flash Sale"
                           class="w-full px-3.5 py-2 text-sm border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Subtitle / Tagline</label>
                    <input type="text" name="subtitle" id="slideSubtitle" placeholder="e.g. Up to 40% OFF all essentials"
                           class="w-full px-3.5 py-2 text-sm border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:border-indigo-500">
                </div>
            </div>

            {{-- Link & CTA Text --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Target URL Link</label>
                    <input type="text" name="link" id="slideLink" placeholder="e.g. /category/wellness or https://..."
                           class="w-full px-3.5 py-2 text-sm font-mono border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Button Text (CTA)</label>
                    <input type="text" name="button_text" id="slideButtonText" placeholder="e.g. Shop Now"
                           class="w-full px-3.5 py-2 text-sm border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:border-indigo-500">
                </div>
            </div>

            {{-- Sort Order & Active Switch --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Sort Order</label>
                    <input type="number" name="sort_order" id="slideSortOrder" value="0" min="0"
                           class="w-full px-3.5 py-2 text-sm font-mono border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:border-indigo-500">
                    <p class="text-[11px] text-slate-400 mt-1">Lower numbers display first.</p>
                </div>
                <div class="flex items-center gap-3 pt-6">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="is_active" id="slideActive" value="1" checked class="sr-only peer">
                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600 dark:bg-slate-700"></div>
                    </label>
                    <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">Active Slide</span>
                </div>
            </div>

            {{-- Image Upload & Preview --}}
            <div class="border-t border-slate-100 dark:border-slate-800 pt-3">
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                    Slide Image <span id="imgRequiredNote" class="text-red-500">*</span>
                    <span class="text-[11px] font-normal text-slate-400 ml-1">(AVIF, WebP, PNG, JPG supported up to 4MB)</span>
                </label>
                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                    <div id="imagePreviewBox" class="h-20 w-36 rounded-xl overflow-hidden bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shrink-0">
                        <img id="previewImg" src="https://placehold.co/600x300?text=Preview" alt="Slide Preview" class="h-full w-full object-cover">
                    </div>
                    <div class="flex-1 w-full space-y-2">
                        <input type="file" name="image" id="slideImageInput" accept="image/jpeg,image/png,image/jpg,image/gif,image/svg+xml,image/webp,image/avif"
                               onchange="previewSlideImage(this)"
                               class="block w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 dark:file:bg-indigo-950/50 dark:file:text-indigo-400 hover:file:bg-indigo-100 transition">
                        <input type="text" name="image_url" id="slideImageUrlFallback" placeholder="Or provide an external Image URL (https://...)"
                               oninput="previewSlideUrl(this.value)"
                               class="w-full px-3.5 py-1.5 text-xs font-mono border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:border-indigo-500">
                    </div>
                </div>
            </div>

            {{-- Modal Footer --}}
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                <button type="button" onclick="closeSliderModal()"
                        class="px-4 py-2 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition">
                    Cancel
                </button>
                <button type="submit" id="submitBtn"
                        class="inline-flex items-center gap-2 px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs shadow-sm transition">
                    <i class="fa-solid fa-check"></i>
                    <span id="submitBtnText">Save Slide</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const modal = document.getElementById('sliderModal');
    const form = document.getElementById('sliderForm');
    const formMethod = document.getElementById('formMethod');
    const modalTitle = document.getElementById('modalTitle');
    const submitBtnText = document.getElementById('submitBtnText');
    const previewImg = document.getElementById('previewImg');
    const imgRequiredNote = document.getElementById('imgRequiredNote');

    function openCreateModal() {
        form.reset();
        form.action = "{{ route('store.admin.sliders.store') }}";
        formMethod.value = "POST";
        modalTitle.textContent = "Add Hero Slide";
        submitBtnText.textContent = "Save Slide";
        imgRequiredNote.classList.remove('hidden');
        previewImg.src = "https://placehold.co/600x300?text=Preview";
        document.getElementById('slideActive').checked = true;
        document.getElementById('slideSortOrder').value = 0;
        modal.classList.remove('hidden');
    }

    function openEditModal(slider, imageUrl) {
        form.reset();
        form.action = "{{ url('store/admin/sliders') }}/" + slider.id;
        formMethod.value = "PUT";
        modalTitle.textContent = "Edit Hero Slide";
        submitBtnText.textContent = "Update Slide";
        imgRequiredNote.classList.add('hidden');

        document.getElementById('slideTitle').value = slider.title || '';
        document.getElementById('slideSubtitle').value = slider.subtitle || '';
        document.getElementById('slideLink').value = slider.link || '';
        document.getElementById('slideButtonText').value = slider.button_text || '';
        document.getElementById('slideSortOrder').value = slider.sort_order ?? 0;
        document.getElementById('slideActive').checked = Boolean(slider.is_active);
        document.getElementById('slideImageUrlFallback').value = slider.image_url || '';

        previewImg.src = imageUrl || slider.image_url || 'https://placehold.co/600x300?text=Preview';

        modal.classList.remove('hidden');
    }

    function closeSliderModal() {
        modal.classList.add('hidden');
    }

    function previewSlideImage(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    function previewSlideUrl(val) {
        if (val && val.startsWith('http')) {
            previewImg.src = val;
        }
    }

    // Close on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
            closeSliderModal();
        }
    });
</script>
@endsection
