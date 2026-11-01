@extends('store_admin.layout.master')

@section('contents')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Store Operations</span>
                <span class="text-slate-300 dark:text-slate-700">&bull;</span>
                <span class="text-xs text-slate-400">Assisted Ordering</span>
            </div>
            <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white flex items-center gap-2.5 mt-1">
                <i class="fa-solid fa-file-prescription text-indigo-600 dark:text-indigo-400"></i>
                Prescriptions <span class="text-xs font-semibold text-amber-600 bg-amber-50 dark:bg-amber-950/40 dark:text-amber-400 border border-amber-200 dark:border-amber-800 rounded-full px-2.5 py-0.5 ml-1">Order Request Inbox</span>
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                Direct handwritten slips uploaded by customers. Review doctor prescriptions, build line-item orders, and dispatch.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('store.admin.orders.index') }}" class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700 transition">
                <i class="fa-solid fa-cart-shopping"></i> View All Orders
            </a>
        </div>
    </div>

    {{-- Feedback Notifications --}}
    @if(session('success'))
        <div class="rounded-2xl bg-emerald-50 p-4 text-sm font-medium text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-200 border border-emerald-200 dark:border-emerald-800 flex items-center justify-between gap-3 shadow-sm">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-circle-check text-emerald-600 dark:text-emerald-400 text-base"></i>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-800 dark:text-emerald-300">&times;</button>
        </div>
    @endif

    @if($errors->any())
        <div class="rounded-2xl bg-rose-50 p-4 text-sm text-rose-800 dark:bg-rose-950/40 dark:text-rose-200 border border-rose-200 dark:border-rose-800 shadow-sm">
            <div class="font-semibold flex items-center gap-1.5 mb-1">
                <i class="fa-solid fa-circle-exclamation text-rose-600 dark:text-rose-400"></i> Operation failed:
            </div>
            <ul class="list-inside list-disc text-xs space-y-0.5">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Filter Navigation Tabs & Search --}}
    <div class="card overflow-hidden">
        <div class="border-b border-slate-100 p-4 sm:p-5 dark:border-slate-800 flex flex-wrap items-center justify-between gap-4 bg-slate-50/50 dark:bg-slate-800/30">
            {{-- Tabs --}}
            <div class="flex flex-wrap items-center gap-1.5 sm:gap-2">
                @php $currentStatus = request('status', 'pending_review'); @endphp

                <a href="{{ route('store.admin.prescriptions.index', array_merge(request()->query(), ['status' => 'pending_review'])) }}"
                   class="inline-flex items-center gap-1.5 rounded-xl px-3.5 py-1.5 text-xs font-bold transition {{ $currentStatus === 'pending_review' ? 'bg-amber-500 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700' }}">
                    <i class="fa-regular fa-clock"></i>
                    Pending Review
                    <span class="rounded-full px-1.5 py-0.2 text-[10px] {{ $currentStatus === 'pending_review' ? 'bg-white/20 text-white' : 'bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300' }}">
                        {{ $statusCounts['pending_review'] ?? 0 }}
                    </span>
                </a>

                <a href="{{ route('store.admin.prescriptions.index', array_merge(request()->query(), ['status' => 'converted_to_order'])) }}"
                   class="inline-flex items-center gap-1.5 rounded-xl px-3.5 py-1.5 text-xs font-bold transition {{ $currentStatus === 'converted_to_order' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700' }}">
                    <i class="fa-solid fa-check"></i>
                    Converted to Order
                    <span class="rounded-full px-1.5 py-0.2 text-[10px] {{ $currentStatus === 'converted_to_order' ? 'bg-white/20 text-white' : 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300' }}">
                        {{ $statusCounts['converted_to_order'] ?? 0 }}
                    </span>
                </a>

                <a href="{{ route('store.admin.prescriptions.index', array_merge(request()->query(), ['status' => 'rejected'])) }}"
                   class="inline-flex items-center gap-1.5 rounded-xl px-3.5 py-1.5 text-xs font-bold transition {{ $currentStatus === 'rejected' ? 'bg-rose-600 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700' }}">
                    <i class="fa-solid fa-ban"></i>
                    Rejected
                    <span class="rounded-full px-1.5 py-0.2 text-[10px] {{ $currentStatus === 'rejected' ? 'bg-white/20 text-white' : 'bg-rose-100 text-rose-800 dark:bg-rose-900/50 dark:text-rose-300' }}">
                        {{ $statusCounts['rejected'] ?? 0 }}
                    </span>
                </a>

                <a href="{{ route('store.admin.prescriptions.index', array_merge(request()->query(), ['status' => 'all'])) }}"
                   class="inline-flex items-center gap-1.5 rounded-xl px-3.5 py-1.5 text-xs font-bold transition {{ $currentStatus === 'all' ? 'bg-slate-800 text-white dark:bg-slate-200 dark:text-slate-900 shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700' }}">
                    All Records
                    <span class="rounded-full px-1.5 py-0.2 text-[10px] {{ $currentStatus === 'all' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300' }}">
                        {{ $statusCounts['all'] ?? 0 }}
                    </span>
                </a>
            </div>

            {{-- Search Bar --}}
            <form method="GET" action="{{ route('store.admin.prescriptions.index') }}" class="flex items-center gap-2 w-full sm:w-auto">
                <input type="hidden" name="status" value="{{ $currentStatus }}">
                <div class="relative flex-1 sm:w-64">
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Search Rx#, name, phone..."
                           class="w-full rounded-xl border border-slate-200 bg-white py-1.5 pl-8 pr-3 text-xs text-slate-800 placeholder-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:placeholder-slate-500 transition">
                    <i class="fa-solid fa-magnifying-glass absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                </div>
                @if(request('search'))
                    <a href="{{ route('store.admin.prescriptions.index', ['status' => $currentStatus]) }}" class="rounded-xl border border-slate-200 bg-white px-2.5 py-1.5 text-xs text-slate-500 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-400" title="Clear search">
                        <i class="fa-solid fa-xmark"></i>
                    </a>
                @endif
                <button type="submit" class="rounded-xl bg-slate-800 px-3 py-1.5 text-xs font-semibold text-white hover:bg-slate-700 dark:bg-slate-700 dark:hover:bg-slate-600 transition">
                    Filter
                </button>
            </form>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50/80 text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:bg-slate-800/50 dark:text-slate-400 border-b border-slate-100 dark:border-slate-800">
                    <tr>
                        <th class="p-4 sm:px-6">Prescription Ref</th>
                        <th class="p-4">Customer / Patient</th>
                        <th class="p-4">Delivery Details</th>
                        <th class="p-4 text-center">Slip Preview</th>
                        <th class="p-4 text-center">Status</th>
                        <th class="p-4 text-right">Received Date</th>
                        <th class="p-4 sm:px-6 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 font-medium">
                    @forelse($prescriptions as $prescription)
                        <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition">
                            {{-- Prescription Ref --}}
                            <td class="p-4 sm:px-6">
                                <a href="{{ route('store.admin.prescriptions.show', $prescription) }}" class="font-mono font-bold text-indigo-600 hover:underline dark:text-indigo-400 text-sm">
                                    {{ $prescription->prescription_number }}
                                </a>
                                @if($prescription->patient_notes)
                                    <div class="mt-1 flex items-start gap-1 text-[11px] text-slate-500 dark:text-slate-400 line-clamp-1 max-w-xs" title="{{ $prescription->patient_notes }}">
                                        <i class="fa-regular fa-comment-dots mt-0.5 text-slate-400 shrink-0"></i>
                                        <span>{{ Str::limit($prescription->patient_notes, 45) }}</span>
                                    </div>
                                @endif
                            </td>

                            {{-- Customer / Patient --}}
                            <td class="p-4">
                                <div class="font-semibold text-slate-900 dark:text-white">{{ $prescription->customer_name }}</div>
                                <div class="text-[11px] text-slate-500 dark:text-slate-400 flex items-center gap-1.5 mt-0.5 font-mono">
                                    <i class="fa-solid fa-phone text-[10px] text-slate-400"></i>
                                    <a href="tel:{{ $prescription->customer_phone }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">{{ $prescription->customer_phone }}</a>
                                </div>
                                @if($prescription->customer_email)
                                    <div class="text-[11px] text-slate-400 truncate max-w-[180px]">{{ $prescription->customer_email }}</div>
                                @endif
                            </td>

                            {{-- Delivery Details --}}
                            <td class="p-4 max-w-xs">
                                <div class="text-slate-800 dark:text-slate-200 line-clamp-2 text-[11px] leading-relaxed">
                                    {{ $prescription->delivery_address }}
                                </div>
                                @if($prescription->delivery_city)
                                    <div class="mt-1 text-[10px] font-semibold text-slate-500 dark:text-slate-400 inline-flex items-center gap-1 bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded">
                                        <i class="fa-solid fa-location-dot text-[9px] text-slate-400"></i>
                                        {{ $prescription->delivery_city }} @if($prescription->delivery_area)({{ $prescription->delivery_area }})@endif
                                    </div>
                                @endif
                            </td>

                            {{-- Slip Preview --}}
                            <td class="p-4 text-center">
                                @if($prescription->image_url)
                                    <button type="button" onclick="openPreviewModal('{{ $prescription->image_url }}', '{{ $prescription->prescription_number }}')" class="group relative inline-block rounded-xl border border-slate-200 bg-white p-1 hover:border-indigo-400 dark:border-slate-700 dark:bg-slate-800 transition shadow-xs">
                                        <img src="{{ $prescription->image_url }}" alt="Prescription" class="h-12 w-12 rounded-lg object-cover">
                                        <span class="absolute inset-0 grid place-items-center rounded-lg bg-black/40 opacity-0 group-hover:opacity-100 transition text-white text-[11px]">
                                            <i class="fa-solid fa-magnifying-glass-plus"></i>
                                        </span>
                                    </button>
                                @else
                                    <span class="text-slate-400 text-xs italic">No image</span>
                                @endif
                            </td>

                            {{-- Status Badge --}}
                            <td class="p-4 text-center">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold border {{ $prescription->status_badge_class }}">
                                    @if($prescription->status === 'pending_review')
                                        <i class="fa-regular fa-clock mr-1 text-[10px]"></i> Pending Review
                                    @elseif($prescription->status === 'converted_to_order')
                                        <i class="fa-solid fa-check mr-1 text-[10px]"></i> Converted to Order
                                    @else
                                        <i class="fa-solid fa-ban mr-1 text-[10px]"></i> Rejected
                                    @endif
                                </span>
                                @if($prescription->order)
                                    <div class="mt-1 text-[11px]">
                                        <a href="{{ route('store.admin.orders.show', $prescription->order) }}" class="font-mono text-indigo-600 hover:underline dark:text-indigo-400">
                                            #{{ $prescription->order->order_number }}
                                        </a>
                                    </div>
                                @endif
                            </td>

                            {{-- Received Date --}}
                            <td class="p-4 text-right text-slate-500 dark:text-slate-400">
                                <div>{{ $prescription->created_at->format('M j, Y') }}</div>
                                <div class="text-[10px] font-mono text-slate-400">{{ $prescription->created_at->format('h:i A') }}</div>
                            </td>

                            {{-- Actions --}}
                            <td class="p-4 sm:px-6 text-right space-x-1.5 whitespace-nowrap">
                                @if($prescription->status === 'pending_review')
                                    @can('Store Prescription Convert')
                                        <a href="{{ route('store.admin.prescriptions.show', $prescription) }}" class="inline-flex items-center gap-1 rounded-xl bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white shadow-xs hover:bg-indigo-700 transition">
                                            <i class="fa-solid fa-cart-plus"></i> Build Order
                                        </a>
                                    @endcan
                                @elseif($prescription->status === 'converted_to_order' && $prescription->order)
                                    <a href="{{ route('store.admin.orders.show', $prescription->order) }}" class="inline-flex items-center gap-1 rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700 transition">
                                        <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i> View Order
                                    </a>
                                @else
                                    <a href="{{ route('store.admin.prescriptions.show', $prescription) }}" class="inline-flex items-center gap-1 rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700 transition">
                                        <i class="fa-regular fa-eye"></i> View Details
                                    </a>
                                @endif

                                @can('Store Prescription Delete')
                                    <form method="POST" action="{{ route('store.admin.prescriptions.destroy', $prescription) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this prescription record?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-xl p-1.5 text-slate-400 hover:bg-rose-50 hover:text-rose-600 dark:hover:bg-rose-950/40 dark:hover:text-rose-400 transition" title="Delete Prescription">
                                            <i class="fa-regular fa-trash-can"></i>
                                        </button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-12 text-center">
                                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-50 text-amber-600 dark:bg-amber-950/40 dark:text-amber-400 mb-3">
                                    <i class="fa-solid fa-file-prescription text-2xl"></i>
                                </div>
                                <h3 class="text-sm font-bold text-slate-800 dark:text-slate-200">No prescriptions found</h3>
                                <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">
                                    @if(request('status') === 'pending_review' || !request('status'))
                                        Great job! All customer uploaded prescriptions have been processed.
                                    @else
                                        No prescriptions match the selected status or search filter.
                                    @endif
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($prescriptions->hasPages())
            <div class="border-t border-slate-100 p-4 dark:border-slate-800">
                {{ $prescriptions->links() }}
            </div>
        @endif
    </div>
</div>

{{-- Quick Image Modal --}}
<div id="imagePreviewModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/75 p-4 backdrop-blur-xs">
    <div class="relative max-w-3xl w-full bg-slate-900 rounded-2xl overflow-hidden shadow-2xl border border-slate-800">
        <div class="flex items-center justify-between p-4 border-b border-slate-800 text-white">
            <h3 id="modalPrescriptionTitle" class="text-sm font-mono font-bold"></h3>
            <div class="flex items-center gap-2">
                <a id="modalDownloadBtn" href="#" target="_blank" class="rounded-lg bg-slate-800 px-2.5 py-1 text-xs text-slate-300 hover:text-white transition">
                    <i class="fa-solid fa-arrow-up-right-from-square mr-1"></i> Full Size
                </a>
                <button type="button" onclick="closePreviewModal()" class="rounded-lg p-1 text-slate-400 hover:text-white transition">
                    <i class="fa-solid fa-xmark text-base"></i>
                </button>
            </div>
        </div>
        <div class="p-4 flex items-center justify-center max-h-[75vh] overflow-auto">
            <img id="modalPreviewImg" src="" alt="Prescription Full Preview" class="max-h-[70vh] rounded-lg object-contain">
        </div>
    </div>
</div>

<script>
    function openPreviewModal(url, ref) {
        document.getElementById('modalPreviewImg').src = url;
        document.getElementById('modalPrescriptionTitle').innerText = 'Prescription: ' + ref;
        document.getElementById('modalDownloadBtn').href = url;
        const modal = document.getElementById('imagePreviewModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closePreviewModal() {
        const modal = document.getElementById('imagePreviewModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    document.getElementById('imagePreviewModal')?.addEventListener('click', function(e) {
        if (e.target === this) closePreviewModal();
    });
</script>
@endsection
