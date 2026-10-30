@extends('store_admin.layout.master')

@section('contents')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Promo Banners</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Manage promotional cards, app download callouts, and popup campaign banners.</p>
        </div>
        @can('Store Banner Create')
            <button type="button" onclick="openCreateBannerModal()"
                    class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-xs font-semibold text-white shadow-sm hover:bg-indigo-700 transition">
                <i class="fa-solid fa-plus text-xs"></i>
                <span>Add Promo Banner</span>
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
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="card p-4 flex items-center gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-950/50 dark:text-indigo-400">
                <i class="fa-solid fa-bullhorn text-base"></i>
            </div>
            <div>
                <span class="text-xs text-slate-500 dark:text-slate-400 block">Total Banners</span>
                <span class="text-xl font-bold font-mono text-slate-900 dark:text-white">{{ $stats['total'] ?? 0 }}</span>
            </div>
        </div>

        <div class="card p-4 flex items-center gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-400">
                <i class="fa-solid fa-table-cells-large text-base"></i>
            </div>
            <div>
                <span class="text-xs text-slate-500 dark:text-slate-400 block">Promo Grid</span>
                <span class="text-xl font-bold font-mono text-slate-900 dark:text-white">{{ $stats['promo_grid'] ?? 0 }}</span>
            </div>
        </div>

        <div class="card p-4 flex items-center gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-cyan-50 text-cyan-600 dark:bg-cyan-950/50 dark:text-cyan-400">
                <i class="fa-solid fa-mobile-screen-button text-base"></i>
            </div>
            <div>
                <span class="text-xs text-slate-500 dark:text-slate-400 block">App Downloads</span>
                <span class="text-xl font-bold font-mono text-slate-900 dark:text-white">{{ $stats['app_download'] ?? 0 }}</span>
            </div>
        </div>

        <div class="card p-4 flex items-center gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-600 dark:bg-amber-950/50 dark:text-amber-400">
                <i class="fa-solid fa-window-restore text-base"></i>
            </div>
            <div>
                <span class="text-xs text-slate-500 dark:text-slate-400 block">Popups / Modals</span>
                <span class="text-xl font-bold font-mono text-slate-900 dark:text-white">{{ $stats['popup'] ?? 0 }}</span>
            </div>
        </div>
    </div>

    {{-- Main Banners Table --}}
    <div class="card overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100 dark:border-slate-800 gap-4 flex-wrap">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-rectangle-ad text-slate-400"></i>
                <h2 class="font-bold text-slate-900 dark:text-white">Active Promo Banners</h2>
                <span class="rounded-full bg-slate-100 dark:bg-slate-800 px-2.5 py-0.5 text-xs font-semibold text-slate-600 dark:text-slate-400">
                    {{ $banners->total() }}
                </span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                <tr class="border-b border-slate-100 dark:border-slate-800 bg-slate-50/80 dark:bg-slate-800/40">
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 w-32">Banner Preview</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400">Campaign Details</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 w-40">Slot Position</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400">Call to Action</th>
                    <th class="text-center px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 w-28">Status</th>
                    <th class="text-right px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 w-28">Actions</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse($banners as $banner)
                    <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/50 transition-colors">
                        <td class="px-4 py-3.5">
                            <div class="h-16 w-28 rounded-xl overflow-hidden bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 relative group">
                                <img src="{{ $banner->image_url }}" alt="{{ $banner->name }}"
                                     class="h-full w-full object-cover group-hover:scale-105 transition-transform duration-300"
                                     onerror="this.src='https://placehold.co/600x300?text=No+Image'"/>
                            </div>
                        </td>
                        <td class="px-4 py-3.5">
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-slate-900 dark:text-white">{{ $banner->name }}</span>
                                @if($banner->eyebrow)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 uppercase tracking-wider">
                                        {{ $banner->eyebrow }}
                                    </span>
                                @endif
                            </div>
                            <div class="text-xs font-semibold text-slate-700 dark:text-slate-300 mt-1">{!! strip_tags($banner->title, '<br>') !!}</div>
                            @if($banner->subtitle)
                                <div class="text-xs text-slate-400 mt-0.5 line-clamp-1">{{ $banner->subtitle }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3.5">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                @if($banner->position === 'promo_grid')
                                    <i class="fa-solid fa-table-cells-large text-[10px] text-emerald-500"></i>
                                @elseif($banner->position === 'app_download')
                                    <i class="fa-solid fa-mobile-screen-button text-[10px] text-cyan-500"></i>
                                @else
                                    <i class="fa-solid fa-window-restore text-[10px] text-amber-500"></i>
                                @endif
                                {{ $positions[$banner->position] ?? ucfirst(str_replace('_', ' ', $banner->position)) }}
                            </span>
                        </td>
                        <td class="px-4 py-3.5 text-xs">
                            @if($banner->cta_text)
                                <div class="font-semibold text-indigo-600 dark:text-indigo-400 flex items-center gap-1">
                                    <span>{{ $banner->cta_text }}</span>
                                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                                </div>
                            @endif
                            @if($banner->link)
                                <div class="font-mono text-slate-400 text-[11px] truncate max-w-xs mt-0.5">
                                    {{ $banner->link }}
                                </div>
                            @endif
                        </td>
                        <td class="px-4 py-3.5 text-center">
                            @can('Store Banner Edit')
                                <form action="{{ route('store.admin.banners.toggle', $banner) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                            title="Click to toggle status"
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium cursor-pointer transition {{ $banner->is_active ? 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100 dark:bg-emerald-950/40 dark:text-emerald-300' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-400' }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $banner->is_active ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                        {{ $banner->is_active ? 'Active' : 'Inactive' }}
                                    </button>
                                </form>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium {{ $banner->is_active ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400' }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $banner->is_active ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                    {{ $banner->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            @endcan
                        </td>
                        <td class="px-4 py-3.5 text-right">
                            <div class="inline-flex items-center gap-1">
                                @can('Store Banner Edit')
                                    <button type="button"
                                            onclick='openEditBannerModal(@json($banner), @json($banner->image_url))'
                                            title="Edit Banner"
                                            class="h-8 w-8 inline-flex items-center justify-center rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-950/50 transition">
                                        <i class="fa-solid fa-pen text-xs"></i>
                                    </button>
                                @endcan

                                @can('Store Banner Delete')
                                    <form action="{{ route('store.admin.banners.destroy', $banner) }}" method="POST"
                                          onsubmit="return confirm('Are you sure you want to delete this promotional banner?')"
                                          class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                title="Delete Banner"
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
                                    <i class="fa-solid fa-rectangle-ad text-xl"></i>
                                </div>
                                <p class="text-sm font-semibold text-slate-700 dark:text-slate-300">No Promo Banners Configured</p>
                                <p class="text-xs text-slate-400 mt-1 max-w-sm">Create promo cards for the 2-column promo grid, app download callout, or promotional modal popups.</p>
                                @can('Store Banner Create')
                                    <button type="button" onclick="openCreateBannerModal()"
                                            class="mt-4 inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-indigo-700 transition">
                                        <i class="fa-solid fa-plus text-xs"></i> Add First Banner
                                    </button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if($banners->hasPages())
            <div class="p-4 border-t border-slate-100 dark:border-slate-800">
                {{ $banners->links() }}
            </div>
        @endif
    </div>
</div>

{{-- Banner Modal (Create / Edit) --}}
<div id="bannerModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-sm p-4 sm:p-6 md:p-12 flex items-center justify-center">
    <div class="relative w-full max-w-2xl bg-white dark:bg-slate-900 rounded-3xl shadow-2xl border border-slate-100 dark:border-slate-800 overflow-hidden my-8">
        {{-- Modal Header --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-slate-800">
            <div>
                <h3 id="bannerModalTitle" class="text-lg font-bold text-slate-900 dark:text-white">Add Promo Banner</h3>
                <p class="text-xs text-slate-400">Configure promotional marketing banner and layout position</p>
            </div>
            <button type="button" onclick="closeBannerModal()"
                    class="h-8 w-8 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-400 hover:text-slate-700 dark:hover:text-white grid place-items-center transition">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>

        {{-- Modal Form --}}
        <form id="bannerForm" method="POST" action="{{ route('store.admin.banners.store') }}" enctype="multipart/form-data" class="p-6 space-y-4">
            @csrf
            <input type="hidden" name="_method" id="bannerFormMethod" value="POST">

            {{-- Name & Position --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Internal Banner Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="bannerName" required placeholder="e.g. Home Lab Tests Promo"
                           class="w-full px-3.5 py-2 text-sm border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Banner Position <span class="text-red-500">*</span></label>
                    <select name="position" id="bannerPosition" required
                            class="w-full px-3.5 py-2 text-sm border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:border-indigo-500">
                        @foreach($positions as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Eyebrow & Headline Title --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Eyebrow / Badge Text</label>
                    <input type="text" name="eyebrow" id="bannerEyebrow" placeholder="e.g. UP TO 60% OFF or NEW ARRIVAL"
                           class="w-full px-3.5 py-2 text-sm border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Banner Headline Title <span class="text-red-500">*</span></label>
                    <input type="text" name="title" id="bannerHeadlineTitle" required placeholder="e.g. Comprehensive Full Body Checkup"
                           class="w-full px-3.5 py-2 text-sm border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:border-indigo-500">
                </div>
            </div>

            {{-- Subtitle --}}
            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Subtitle / Description</label>
                <textarea name="subtitle" id="bannerSubtitle" rows="2" placeholder="Brief supporting description..."
                          class="w-full px-3.5 py-2 text-sm border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:border-indigo-500 resize-none"></textarea>
            </div>

            {{-- CTA text, Link, & CTA Color --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">CTA Button Text</label>
                    <input type="text" name="cta_text" id="bannerCtaText" placeholder="e.g. Book Lab Test"
                           class="w-full px-3.5 py-2 text-sm border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Destination Link</label>
                    <input type="text" name="link" id="bannerLink" placeholder="e.g. /category/wellness"
                           class="w-full px-3.5 py-2 text-sm font-mono border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">CTA Color Theme</label>
                    <input type="text" name="cta_color" id="bannerCtaColor" placeholder="e.g. teal, cyan, indigo"
                           class="w-full px-3.5 py-2 text-sm border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:border-indigo-500">
                </div>
            </div>

            {{-- Gradient Classes & Status --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Custom Gradient Classes</label>
                    <input type="text" name="gradient" id="bannerGradient" placeholder="e.g. from-teal-900/90 via-slate-900/80 to-slate-900"
                           class="w-full px-3.5 py-2 text-xs font-mono border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:border-indigo-500">
                    <p class="text-[11px] text-slate-400 mt-1">Tailwind CSS background overlay classes used by storefront promo cards.</p>
                </div>
                <div class="flex items-center gap-3 pt-6">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="is_active" id="bannerActive" value="1" checked class="sr-only peer">
                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600 dark:bg-slate-700"></div>
                    </label>
                    <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">Active Banner</span>
                </div>
            </div>

            {{-- Image Upload & Preview --}}
            <div class="border-t border-slate-100 dark:border-slate-800 pt-3">
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                    Banner Background / Foreground Image <span id="bannerImgRequiredNote" class="text-red-500">*</span>
                    <span class="text-[11px] font-normal text-slate-400 ml-1">(AVIF, WebP, PNG, JPG supported up to 4MB)</span>
                </label>
                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                    <div id="bannerImagePreviewBox" class="h-20 w-32 rounded-xl overflow-hidden bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shrink-0">
                        <img id="bannerPreviewImg" src="https://placehold.co/600x300?text=Preview" alt="Banner Preview" class="h-full w-full object-cover">
                    </div>
                    <div class="flex-1 w-full space-y-2">
                        <input type="file" name="image" id="bannerImageInput" accept="image/jpeg,image/png,image/jpg,image/gif,image/svg+xml,image/webp,image/avif"
                               onchange="previewBannerImage(this)"
                               class="block w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 dark:file:bg-indigo-950/50 dark:file:text-indigo-400 hover:file:bg-indigo-100 transition">
                        <input type="text" name="image_url" id="bannerImageUrlFallback" placeholder="Or provide an external Image URL (https://...)"
                               oninput="previewBannerUrl(this.value)"
                               class="w-full px-3.5 py-1.5 text-xs font-mono border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:border-indigo-500">
                    </div>
                </div>
            </div>

            {{-- Modal Footer --}}
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                <button type="button" onclick="closeBannerModal()"
                        class="px-4 py-2 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition">
                    Cancel
                </button>
                <button type="submit" id="bannerSubmitBtn"
                        class="inline-flex items-center gap-2 px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs shadow-sm transition">
                    <i class="fa-solid fa-check"></i>
                    <span id="bannerSubmitBtnText">Save Banner</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const bModal = document.getElementById('bannerModal');
    const bForm = document.getElementById('bannerForm');
    const bFormMethod = document.getElementById('bannerFormMethod');
    const bModalTitle = document.getElementById('bannerModalTitle');
    const bSubmitBtnText = document.getElementById('bannerSubmitBtnText');
    const bPreviewImg = document.getElementById('bannerPreviewImg');
    const bImgRequiredNote = document.getElementById('bannerImgRequiredNote');

    function openCreateBannerModal() {
        bForm.reset();
        bForm.action = "{{ route('store.admin.banners.store') }}";
        bFormMethod.value = "POST";
        bModalTitle.textContent = "Add Promo Banner";
        bSubmitBtnText.textContent = "Save Banner";
        bImgRequiredNote.classList.remove('hidden');
        bPreviewImg.src = "https://placehold.co/600x300?text=Preview";
        document.getElementById('bannerActive').checked = true;
        document.getElementById('bannerPosition').value = 'promo_grid';
        bModal.classList.remove('hidden');
    }

    function openEditBannerModal(banner, imageUrl) {
        bForm.reset();
        bForm.action = "{{ url('store/admin/banners') }}/" + banner.id;
        bFormMethod.value = "PUT";
        bModalTitle.textContent = "Edit Promo Banner";
        bSubmitBtnText.textContent = "Update Banner";
        bImgRequiredNote.classList.add('hidden');

        document.getElementById('bannerName').value = banner.name || '';
        document.getElementById('bannerPosition').value = banner.position || 'promo_grid';
        document.getElementById('bannerEyebrow').value = banner.eyebrow || '';
        document.getElementById('bannerHeadlineTitle').value = banner.title || '';
        document.getElementById('bannerSubtitle').value = banner.subtitle || '';
        document.getElementById('bannerCtaText').value = banner.cta_text || '';
        document.getElementById('bannerLink').value = banner.link || '';
        document.getElementById('bannerCtaColor').value = banner.cta_color || '';
        document.getElementById('bannerGradient').value = banner.gradient || '';
        document.getElementById('bannerActive').checked = Boolean(banner.is_active);
        document.getElementById('bannerImageUrlFallback').value = banner.image_url || '';

        bPreviewImg.src = imageUrl || banner.image_url || 'https://placehold.co/600x300?text=Preview';

        bModal.classList.remove('hidden');
    }

    function closeBannerModal() {
        bModal.classList.add('hidden');
    }

    function previewBannerImage(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                bPreviewImg.src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    function previewBannerUrl(val) {
        if (val && val.startsWith('http')) {
            bPreviewImg.src = val;
        }
    }

    // Close on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && !bModal.classList.contains('hidden')) {
            closeBannerModal();
        }
    });
</script>
@endsection
