@extends('platform_admin.layout.master')

@section('contents')
    <div class="space-y-6">
        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-slate-900 tracking-tight dark:text-white">Platform Store Demos</h1>
                <p class="text-sm text-slate-500 mt-0.5">Manage and organize template demo sites and showcase entries.</p>
            </div>
            <button type="button" onclick="openModal('createDemoModal')"
                    class="inline-flex items-center justify-center px-4 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-500 active:bg-indigo-700 rounded-lg shadow-sm transition-all gap-2 cursor-pointer">
                <i class="fa-solid fa-plus text-xs"></i>
                <span>Add Store Demo</span>
            </button>
        </div>

        <!-- Main Card Container -->
        <div class="card overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100 dark:border-slate-800 gap-4 flex-wrap">
                <div>
                    <h2 class="font-bold text-ink dark:text-white">Store Demos</h2>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm table-fixed">
                    <thead>
                    <tr class="border-b border-slate-100 dark:border-slate-800 bg-slate-50/80 dark:bg-slate-800/40">
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 w-16">SN</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 w-20">Sort</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 w-28">Preview</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 w-64">Title & Slug</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 w-36">Demo Link</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 w-28">Status</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 w-28">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($demos as $demo)
                        <tr class="trow border-b border-slate-100 dark:border-slate-800 hover:bg-slate-50/50 transition-colors">
                            <td class="px-4 py-3.5 text-xs font-bold text-primary w-16">{{ $loop->iteration }}</td>
                            <td class="px-4 py-3.5 text-xs font-bold text-primary w-20">{{ $demo->sort_order }}</td>
                            <td class="px-4 py-3.5 w-28">
                                @if($demo->image)
                                    <img src="{{ asset('storage/' . $demo->image) }}" class="rounded shadow-sm object-cover h-10 w-16 border border-slate-200" alt="Demo">
                                @else
                                    <span class="text-xs text-slate-400 italic">No Image</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 w-64 truncate">
                                <div class="font-semibold text-slate-900 dark:text-white truncate">{{ $demo->title }}</div>
                                <div class="text-xs text-slate-400 truncate font-mono"><code>{{ $demo->slug }}</code></div>
                            </td>
                            <td class="px-4 py-3.5 w-36 truncate">
                                @if($demo->link)
                                    <a href="{{ $demo->link }}" target="_blank" class="inline-flex items-center gap-1 text-xs font-medium text-indigo-600 hover:text-indigo-500 bg-indigo-50 px-2.5 py-1 rounded-md border border-indigo-100 transition-colors">
                                        <span>Visit Demo</span>
                                        <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                                    </a>
                                @else
                                    <span class="text-xs text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-center w-28">
                                @if($demo->status)
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
                                    <button type="button" onclick="openModal('editDemoModal{{ $demo->id }}')"
                                            class="p-2 text-sky-600 hover:text-sky-700 hover:bg-sky-50 rounded-lg transition-colors cursor-pointer"
                                            title="Edit Demo">
                                        <i class="fa-solid fa-pen text-xs"></i>
                                    </button>
                                    <form action="{{ route('admin.store-demos.destroy', $demo->id) }}" method="POST"
                                          class="inline" onsubmit="return confirm('Delete this store demo?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="p-2 text-rose-600 hover:text-rose-700 hover:bg-rose-50 rounded-lg transition-colors cursor-pointer"
                                                title="Delete Demo">
                                            <i class="fa-solid fa-trash text-xs"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>

                        <!-- Edit Modal -->
                        <div id="editDemoModal{{ $demo->id }}"
                             class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
                            <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl overflow-hidden border border-slate-100 transform transition-all">
                                <form method="POST" action="{{ route('admin.store-demos.update', $demo->id) }}" enctype="multipart/form-data">
                                    @csrf
                                    @method('PUT')
                                    <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                                        <h3 class="text-base font-bold text-slate-900">Edit Store Demo</h3>
                                        <button type="button" onclick="closeModal('editDemoModal{{ $demo->id }}')"
                                                class="text-slate-400 hover:text-slate-600 p-1 rounded-lg transition-colors cursor-pointer">
                                            <i class="fa-solid fa-xmark text-lg"></i>
                                        </button>
                                    </div>
                                    <div class="p-6 space-y-4">
                                        <div>
                                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Title</label>
                                            <input type="text" name="title" value="{{ $demo->title }}" required
                                                   class="w-full px-3.5 py-2 text-sm dark:text-black bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Slug</label>
                                            <input type="text" name="slug" value="{{ $demo->slug }}"
                                                   class="w-full px-3.5 py-2 text-sm dark:text-black bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Demo Link URL</label>
                                            <input type="url" name="link" value="{{ $demo->link }}" placeholder="https://fashion.mysaas.com"
                                                   class="w-full px-3.5 py-2 text-sm dark:text-black bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Preview Image</label>
                                            <input type="file" name="image"
                                                   class="w-full px-3.5 py-1.5 text-sm dark:text-black bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all file:mr-4 file:py-1 file:px-2 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Sort Order</label>
                                            <input type="number" name="sort_order" value="{{ $demo->sort_order }}" min="1"
                                                   class="w-full px-3.5 py-2 text-sm dark:text-black bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                                        </div>
                                        <div class="flex items-center gap-3 pt-2">
                                            <label class="relative inline-flex items-center cursor-pointer">
                                                <input type="checkbox" name="status" id="demoStatusEdit{{ $demo->id }}" value="1"
                                                       class="sr-only peer" {{ $demo->status ? 'checked' : '' }}>
                                                <div class="w-9 h-5 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-indigo-600"></div>
                                            </label>
                                            <label for="demoStatusEdit{{ $demo->id }}" class="text-sm font-medium text-slate-700 cursor-pointer select-none">Active Status</label>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-end gap-3 px-6 py-4 bg-slate-50 border-t border-slate-100">
                                        <button type="button" onclick="closeModal('editDemoModal{{ $demo->id }}')"
                                                class="px-4 py-2 text-sm font-semibold text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 rounded-lg transition-colors cursor-pointer">
                                            Close
                                        </button>
                                        <button type="submit"
                                                class="px-4 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-500 rounded-lg shadow-sm transition-colors cursor-pointer">
                                            Update Demo
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-slate-400 py-12 text-sm">No store demos found.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Create Modal -->
    <div id="createDemoModal"
         class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl overflow-hidden border border-slate-100 transform transition-all">
            <form method="POST" action="{{ route('admin.store-demos.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                    <h3 class="text-base font-bold text-slate-900">Add New Store Demo</h3>
                    <button type="button" onclick="closeModal('createDemoModal')"
                            class="text-slate-400 hover:text-slate-600 p-1 rounded-lg transition-colors cursor-pointer">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Title</label>
                        <input type="text" name="title" required placeholder="Fashion Boutique Demo"
                               class="w-full px-3.5 py-2 text-sm dark:text-black bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Slug</label>
                        <input type="text" name="slug" placeholder="fashion-boutique"
                               class="w-full px-3.5 py-2 text-sm dark:text-black bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Demo Link URL</label>
                        <input type="url" name="link" placeholder="https://fashion.mysaas.com"
                               class="w-full px-3.5 py-2 text-sm dark:text-black bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Preview Image</label>
                        <input type="file" name="image"
                               class="w-full px-3.5 py-1.5 text-sm dark:text-black bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all file:mr-4 file:py-1 file:px-2 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Sort Order</label>
                        <input type="number" name="sort_order" value="1" min="1"
                               class="w-full px-3.5 py-2 text-sm dark:text-black bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                    </div>
                    <div class="flex items-center gap-3 pt-2">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="status" id="demoStatusCreate" value="1" checked
                                   class="sr-only peer">
                            <div class="w-9 h-5 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-indigo-600"></div>
                        </label>
                        <label for="demoStatusCreate" class="text-sm font-medium text-slate-700 cursor-pointer select-none">Active Status</label>
                    </div>
                </div>
                <div class="flex items-center justify-end gap-3 px-6 py-4 bg-slate-50 border-t border-slate-100">
                    <button type="button" onclick="closeModal('createDemoModal')"
                            class="px-4 py-2 text-sm font-semibold text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 rounded-lg transition-colors cursor-pointer">
                        Close
                    </button>
                    <button type="submit"
                            class="px-4 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-500 rounded-lg shadow-sm transition-colors cursor-pointer">
                        Save Demo
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
