@extends('platform_admin.layout.master')

@section('contents')
    <div class="space-y-6">
        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-slate-900 tracking-tight dark:text-white">Platform Use Cases</h1>
                <p class="text-sm text-slate-500 mt-0.5">Manage and organize platform solution use cases and display entries.</p>
            </div>
            <button type="button" onclick="openModal('createUseCaseModal')"
                    class="inline-flex items-center justify-center px-4 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-500 active:bg-indigo-700 rounded-lg shadow-sm transition-all gap-2 cursor-pointer">
                <i class="fa-solid fa-plus text-xs"></i>
                <span>Add Use Case</span>
            </button>
        </div>

        <!-- Main Card Container -->
        <div class="card overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100 dark:border-slate-800 gap-4 flex-wrap">
                <div>
                    <h2 class="font-bold text-ink dark:text-white">Use Cases</h2>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm table-fixed">
                    <thead>
                    <tr class="border-b border-slate-100 dark:border-slate-800 bg-slate-50/80 dark:bg-slate-800/40">
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 w-16">Sort</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 w-48">Title</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 w-32">Badge</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400">Description</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 w-32">BG Color</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 w-28">Status</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 w-28">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($useCases as $uc)
                        <tr class="trow border-b border-slate-100 dark:border-slate-800 hover:bg-slate-50/50 transition-colors">
                            <td class="px-4 py-3.5 text-xs font-bold text-primary w-16">{{ $uc->sort_order }}</td>
                            <td class="px-4 py-3.5 w-48 truncate font-semibold text-slate-900 dark:text-white" title="{{ $uc->title }}">{{ $uc->title }}</td>
                            <td class="px-4 py-3.5 w-32 truncate">
                                @if($uc->badge_label)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-indigo-50 text-indigo-700 border border-indigo-100">
                                        {{ $uc->badge_label }}
                                    </span>
                                @else
                                    <span class="text-xs text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-slate-500 dark:text-slate-400 truncate" title="{{ $uc->description }}">
                                {{ Str::limit($uc->description, 80) }}
                            </td>
                            <td class="px-4 py-3.5 w-32 truncate">
                                @if($uc->bg_color)
                                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-xs font-mono border border-slate-200" style="background-color: {{ $uc->bg_color }}; color: #1e293b;">
                                        <span class="w-2 h-2 rounded-full border border-slate-400 shrink-0" style="background-color: {{ $uc->bg_color }};"></span>
                                        {{ $uc->bg_color }}
                                    </span>
                                @else
                                    <span class="text-xs text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-center w-28">
                                @if($uc->status)
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
                                    <button type="button" onclick="openModal('editUseCaseModal{{ $uc->id }}')"
                                            class="p-2 text-sky-600 hover:text-sky-700 hover:bg-sky-50 rounded-lg transition-colors cursor-pointer"
                                            title="Edit Use Case">
                                        <i class="fa-solid fa-pen text-xs"></i>
                                    </button>
                                    <form action="{{ route('admin.use-cases.destroy', $uc->id) }}" method="POST"
                                          class="inline" onsubmit="return confirm('Delete this use case?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="p-2 text-rose-600 hover:text-rose-700 hover:bg-rose-50 rounded-lg transition-colors cursor-pointer"
                                                title="Delete Use Case">
                                            <i class="fa-solid fa-trash text-xs"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>

                        <!-- Edit Modal -->
                        <div id="editUseCaseModal{{ $uc->id }}"
                             class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
                            <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl overflow-hidden border border-slate-100 transform transition-all">
                                <form method="POST" action="{{ route('admin.use-cases.update', $uc->id) }}">
                                    @csrf
                                    @method('PUT')
                                    <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                                        <h3 class="text-base font-bold text-slate-900">Edit Use Case</h3>
                                        <button type="button" onclick="closeModal('editUseCaseModal{{ $uc->id }}')"
                                                class="text-slate-400 hover:text-slate-600 p-1 rounded-lg transition-colors cursor-pointer">
                                            <i class="fa-solid fa-xmark text-lg"></i>
                                        </button>
                                    </div>
                                    <div class="p-6 space-y-4">
                                        <div>
                                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Title</label>
                                            <input type="text" name="title" value="{{ $uc->title }}" required
                                                   class="w-full px-3.5 py-2 text-sm dark:text-black bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                                        </div>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Badge Label</label>
                                                <input type="text" name="badge_label" value="{{ $uc->badge_label }}" placeholder="Popular"
                                                       class="w-full px-3.5 py-2 text-sm dark:text-black bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Badge Style</label>
                                                <input type="text" name="badge_type" value="{{ $uc->badge_type }}" placeholder="success"
                                                       class="w-full px-3.5 py-2 text-sm dark:text-black bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Description</label>
                                            <textarea name="description" rows="3"
                                                      class="w-full px-3.5 py-2 text-sm dark:text-black bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">{{ $uc->description }}</textarea>
                                        </div>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Background Color Code</label>
                                                <input type="text" name="bg_color" value="{{ $uc->bg_color }}" placeholder="#eff6ff"
                                                       class="w-full px-3.5 py-2 text-sm dark:text-black bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Sort Order</label>
                                                <input type="number" name="sort_order" value="{{ $uc->sort_order }}" min="1"
                                                       class="w-full px-3.5 py-2 text-sm dark:text-black bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-3 pt-2">
                                            <label class="relative inline-flex items-center cursor-pointer">
                                                <input type="checkbox" name="status" id="ucStatusEdit{{ $uc->id }}" value="1"
                                                       class="sr-only peer" {{ $uc->status ? 'checked' : '' }}>
                                                <div class="w-9 h-5 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-indigo-600"></div>
                                            </label>
                                            <label for="ucStatusEdit{{ $uc->id }}" class="text-sm font-medium text-slate-700 cursor-pointer select-none">Active Status</label>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-end gap-3 px-6 py-4 bg-slate-50 border-t border-slate-100">
                                        <button type="button" onclick="closeModal('editUseCaseModal{{ $uc->id }}')"
                                                class="px-4 py-2 text-sm font-semibold text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 rounded-lg transition-colors cursor-pointer">
                                            Close
                                        </button>
                                        <button type="submit"
                                                class="px-4 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-500 rounded-lg shadow-sm transition-colors cursor-pointer">
                                            Update Use Case
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-slate-400 py-12 text-sm">No use cases found.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Create Modal -->
    <div id="createUseCaseModal"
         class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl overflow-hidden border border-slate-100 transform transition-all">
            <form method="POST" action="{{ route('admin.use-cases.store') }}">
                @csrf
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                    <h3 class="text-base font-bold text-slate-900">Add New Use Case</h3>
                    <button type="button" onclick="closeModal('createUseCaseModal')"
                            class="text-slate-400 hover:text-slate-600 p-1 rounded-lg transition-colors cursor-pointer">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Title</label>
                        <input type="text" name="title" required placeholder="Digital Products & Downloads"
                               class="w-full px-3.5 py-2 text-sm dark:text-black bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Badge Label</label>
                            <input type="text" name="badge_label" placeholder="Popular"
                                   class="w-full px-3.5 py-2 text-sm dark:text-black bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Badge Style</label>
                            <input type="text" name="badge_type" placeholder="success"
                                   class="w-full px-3.5 py-2 text-sm dark:text-black bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Description</label>
                        <textarea name="description" rows="3" placeholder="Enter use case description..."
                                  class="w-full px-3.5 py-2 text-sm dark:text-black bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all"></textarea>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Background Color Code</label>
                            <input type="text" name="bg_color" placeholder="#eff6ff"
                                   class="w-full px-3.5 py-2 text-sm dark:text-black bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Sort Order</label>
                            <input type="number" name="sort_order" value="1" min="1"
                                   class="w-full px-3.5 py-2 text-sm dark:text-black bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                        </div>
                    </div>
                    <div class="flex items-center gap-3 pt-2">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="status" id="ucStatusCreate" value="1" checked
                                   class="sr-only peer">
                            <div class="w-9 h-5 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-indigo-600"></div>
                        </label>
                        <label for="ucStatusCreate" class="text-sm font-medium text-slate-700 cursor-pointer select-none">Active Status</label>
                    </div>
                </div>
                <div class="flex items-center justify-end gap-3 px-6 py-4 bg-slate-50 border-t border-slate-100">
                    <button type="button" onclick="closeModal('createUseCaseModal')"
                            class="px-4 py-2 text-sm font-semibold text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 rounded-lg transition-colors cursor-pointer">
                        Close
                    </button>
                    <button type="submit"
                            class="px-4 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-500 rounded-lg shadow-sm transition-colors cursor-pointer">
                        Save Use Case
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
