@extends('platform_admin.layout.master')

@section('contents')
    @php
        $sortUrl = function (string $column) use ($filters) {
            $nextDirection = ($filters['sort'] === $column && $filters['direction'] === 'asc') ? 'desc' : 'asc';
            return request()->fullUrlWithQuery([
                'sort' => $column,
                'direction' => $nextDirection,
                'page' => 1,
            ]);
        };
        $hasFilters = $filters['search'] !== '';
    @endphp

    <div class="space-y-6">
        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-slate-900 tracking-tight dark:text-white">Permissions</h1>
                <p class="text-sm text-slate-500 mt-0.5">Manage the individual permissions available to assign to roles.</p>
            </div>
            @can('Create Permission')
                <button type="button" onclick="openModal('createPermissionModal')"
                        class="inline-flex items-center justify-center px-4 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-500 active:bg-indigo-700 rounded-lg shadow-sm transition-all gap-2 cursor-pointer">
                    <i class="fa-solid fa-plus text-xs"></i>
                    <span>Create Permission</span>
                </button>
            @endcan
        </div>

        <!-- Main Card Container -->
        <div class="card overflow-hidden">
            <!-- Toolbar -->
            <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    <div>
                        <h2 class="font-bold text-ink dark:text-white">Permissions</h2>
                        <p class="text-xs text-slate-400 mt-0.5">{{ $permissions->total() }} total</p>
                    </div>

                    <form method="GET" action="{{ route('admin.permission.index') }}"
                          class="flex flex-wrap items-center gap-2">
                        <input type="hidden" name="sort" value="{{ $filters['sort'] }}">
                        <input type="hidden" name="direction" value="{{ $filters['direction'] }}">

                        <div class="relative">
                            <i class="fa-solid fa-magnifying-glass absolute left-2.5 top-1/2 -translate-y-1/2 text-xs text-slate-400 pointer-events-none"></i>
                            <input type="text" name="search" value="{{ $filters['search'] }}" placeholder="Search name or group..."
                                   class="w-full sm:w-56 pl-7 pr-3 py-1.5 text-xs dark:text-black bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                        </div>

                        <button type="submit"
                                class="px-3 py-1.5 text-xs font-semibold text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 rounded-lg transition-colors cursor-pointer">
                            Search
                        </button>

                        @if($hasFilters)
                            <a href="{{ route('admin.permission.index') }}"
                               class="px-3 py-1.5 text-xs font-semibold text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors cursor-pointer">
                                Reset
                            </a>
                        @endif
                    </form>
                </div>
            </div>

            <!-- Bulk action bar -->
            @can('Delete Permission')
                <div class="flex items-center justify-between gap-3 px-5 py-2.5 bg-slate-50/70 dark:bg-slate-800/40 border-b border-slate-100 dark:border-slate-800">
                    <span class="text-xs text-slate-500 dark:text-slate-400"><span id="bulkCount">0</span> selected</span>
                    <button type="submit" form="bulkPermissionsForm" id="bulkDeleteBtn" disabled
                            onclick="return confirm('Delete selected permissions?');"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-rose-600 hover:bg-rose-500 disabled:opacity-40 disabled:cursor-not-allowed rounded-lg transition-all cursor-pointer">
                        <i class="fa-solid fa-trash text-[10px]"></i>
                        <span>Delete selected</span>
                    </button>
                </div>
            @endcan

            <div class="overflow-x-auto">
                <table class="w-full text-sm table-auto">
                    <thead>
                    <tr class="border-b border-slate-100 dark:border-slate-800 bg-slate-50/80 dark:bg-slate-800/40">
                        @can('Delete Permission')
                            <th class="px-4 py-3 w-10">
                                <input type="checkbox" id="permCheckAll"
                                       class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            </th>
                        @endcan
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 w-16">#</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400">
                            <a href="{{ $sortUrl('name') }}" class="inline-flex items-center gap-1.5 hover:text-slate-700 dark:hover:text-slate-200">
                                Name
                                @if($filters['sort'] === 'name')
                                    <i class="fa-solid fa-sort-{{ $filters['direction'] === 'asc' ? 'up' : 'down' }} text-[10px] text-indigo-600"></i>
                                @else
                                    <i class="fa-solid fa-sort text-[10px] opacity-30"></i>
                                @endif
                            </a>
                        </th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 w-56">
                            <a href="{{ $sortUrl('group_name') }}" class="inline-flex items-center gap-1.5 hover:text-slate-700 dark:hover:text-slate-200">
                                Group
                                @if($filters['sort'] === 'group_name')
                                    <i class="fa-solid fa-sort-{{ $filters['direction'] === 'asc' ? 'up' : 'down' }} text-[10px] text-indigo-600"></i>
                                @else
                                    <i class="fa-solid fa-sort text-[10px] opacity-30"></i>
                                @endif
                            </a>
                        </th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 w-28">
                            <a href="{{ $sortUrl('guard_name') }}" class="inline-flex items-center gap-1.5 hover:text-slate-700 dark:hover:text-slate-200">
                                Guard
                                @if($filters['sort'] === 'guard_name')
                                    <i class="fa-solid fa-sort-{{ $filters['direction'] === 'asc' ? 'up' : 'down' }} text-[10px] text-indigo-600"></i>
                                @else
                                    <i class="fa-solid fa-sort text-[10px] opacity-30"></i>
                                @endif
                            </a>
                        </th>
                        <th class="text-center px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 w-32">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($permissions as $key => $permission)
                        <tr class="trow border-b border-slate-100 dark:border-slate-800 hover:bg-slate-50/50 transition-colors">
                            @can('Delete Permission')
                                <td class="px-4 py-3.5 w-10">
                                    <input type="checkbox" name="ids[]" value="{{ $permission->id }}" form="bulkPermissionsForm"
                                           class="perm-check rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                </td>
                            @endcan
                            <td class="px-4 py-3.5 w-16 text-slate-500 dark:text-slate-400">{{ $permissions->firstItem() + $key }}</td>
                            <td class="px-4 py-3.5 font-semibold text-slate-900 dark:text-white">{{ $permission->name }}</td>
                            <td class="px-4 py-3.5 w-56">
                                <span class="px-2 py-0.5 rounded text-xs font-medium bg-indigo-50 text-indigo-700 border border-indigo-200/60">{{ $permission->group_name ?: 'General' }}</span>
                            </td>
                            <td class="px-4 py-3.5 w-28">
                                <span class="px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-600 border border-slate-200">{{ $permission->guard_name }}</span>
                            </td>
                            <td class="px-4 py-3.5 text-center w-32">
                                <div class="inline-flex items-center gap-1.5">
                                    @can('Edit Permission')
                                        <button type="button" onclick="openModal('editPermissionModal{{ $permission->id }}')"
                                                class="p-2 text-sky-600 hover:text-sky-700 hover:bg-sky-50 rounded-lg transition-colors cursor-pointer"
                                                title="Edit Permission">
                                            <i class="fa-solid fa-pen text-xs"></i>
                                        </button>
                                    @endcan

                                    @can('Delete Permission')
                                        <form action="{{ route('admin.permission.destroy', $permission->id) }}" method="POST"
                                              class="inline" onsubmit="return confirm('Delete this permission?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="p-2 text-rose-600 hover:text-rose-700 hover:bg-rose-50 rounded-lg transition-colors cursor-pointer"
                                                    title="Delete Permission">
                                                <i class="fa-solid fa-trash text-xs"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>

                        <!-- Edit Modal -->
                        @can('Edit Permission')
                            <div id="editPermissionModal{{ $permission->id }}"
                                 class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
                                <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden border border-slate-100 transform transition-all max-h-[90vh] flex flex-col">
                                    <form method="POST" action="{{ route('admin.permission.update', $permission->id) }}" class="flex flex-col h-full overflow-hidden">
                                        @csrf
                                        @method('PUT')
                                        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 shrink-0">
                                            <h3 class="text-base font-bold text-slate-900">Edit Permission</h3>
                                            <button type="button" onclick="closeModal('editPermissionModal{{ $permission->id }}')"
                                                    class="text-slate-400 hover:text-slate-600 p-1 rounded-lg transition-colors cursor-pointer">
                                                <i class="fa-solid fa-xmark text-lg"></i>
                                            </button>
                                        </div>
                                        <div class="p-6 space-y-4 overflow-y-auto flex-1">
                                            <div>
                                                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Name</label>
                                                <input type="text" name="name" value="{{ old('name', $permission->name) }}" required
                                                       class="w-full px-3.5 py-2 text-sm dark:text-black bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Group</label>
                                                <input type="text" name="group_name" value="{{ old('group_name', $permission->group_name) }}" list="permissionGroupsEdit{{ $permission->id }}" required
                                                       class="w-full px-3.5 py-2 text-sm dark:text-black bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                                                <datalist id="permissionGroupsEdit{{ $permission->id }}">
                                                    @foreach($groups as $group)
                                                        <option value="{{ $group }}"></option>
                                                    @endforeach
                                                </datalist>
                                            </div>
                                        </div>
                                        <div class="flex items-center justify-end gap-3 px-6 py-4 bg-slate-50 border-t border-slate-100 shrink-0">
                                            <button type="button" onclick="closeModal('editPermissionModal{{ $permission->id }}')"
                                                    class="px-4 py-2 text-sm font-semibold text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 rounded-lg transition-colors cursor-pointer">
                                                Close
                                            </button>
                                            <button type="submit"
                                                    class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-500 rounded-lg shadow-sm transition-colors cursor-pointer">
                                                <i class="fa-solid fa-floppy-disk text-xs"></i>
                                                <span>Update Permission</span>
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        @endcan
                    @empty
                        <tr>
                            <td colspan="{{ auth()->user()->can('Delete Permission') ? 6 : 5 }}" class="text-center text-slate-400 py-12 text-sm">
                                No permissions found.
                                @if($hasFilters)
                                    <a href="{{ route('admin.permission.index') }}" class="text-indigo-600 hover:underline ml-1">Clear filters</a>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            @if($permissions->hasPages())
                <div class="px-5 py-4 border-t border-slate-100 dark:border-slate-800">
                    {{ $permissions->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </div>

    @can('Delete Permission')
        <form id="bulkPermissionsForm" method="POST" action="{{ route('admin.permission.bulk-destroy') }}" class="hidden">
            @csrf
            @method('DELETE')
        </form>
    @endcan

    <!-- Create Modal -->
    @can('Create Permission')
        <div id="createPermissionModal"
             class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden border border-slate-100 transform transition-all max-h-[90vh] flex flex-col">
                <form method="POST" action="{{ route('admin.permission.store') }}" class="flex flex-col h-full overflow-hidden">
                    @csrf
                    <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 shrink-0">
                        <h3 class="text-base font-bold text-slate-900">Create New Permission</h3>
                        <button type="button" onclick="closeModal('createPermissionModal')"
                                class="text-slate-400 hover:text-slate-600 p-1 rounded-lg transition-colors cursor-pointer">
                            <i class="fa-solid fa-xmark text-lg"></i>
                        </button>
                    </div>
                    <div class="p-6 space-y-4 overflow-y-auto flex-1">
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Name</label>
                            <input type="text" name="name" value="{{ old('name') }}" required placeholder="Create Admin"
                                   class="w-full px-3.5 py-2 text-sm dark:text-black bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Group</label>
                            <input type="text" name="group_name" value="{{ old('group_name') }}" list="permissionGroupsCreate" required placeholder="Admins"
                                   class="w-full px-3.5 py-2 text-sm dark:text-black bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                            <datalist id="permissionGroupsCreate">
                                @foreach($groups as $group)
                                    <option value="{{ $group }}"></option>
                                @endforeach
                            </datalist>
                        </div>
                    </div>
                    <div class="flex items-center justify-end gap-3 px-6 py-4 bg-slate-50 border-t border-slate-100 shrink-0">
                        <button type="button" onclick="closeModal('createPermissionModal')"
                                class="px-4 py-2 text-sm font-semibold text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 rounded-lg transition-colors cursor-pointer">
                            Close
                        </button>
                        <button type="submit"
                                class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-500 rounded-lg shadow-sm transition-colors cursor-pointer">
                            <i class="fa-solid fa-floppy-disk text-xs"></i>
                            <span>Save Permission</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endcan

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

        (function initBulkSelection() {
            const checkAll = document.getElementById('permCheckAll');
            const countEl = document.getElementById('bulkCount');
            const deleteBtn = document.getElementById('bulkDeleteBtn');
            if (!checkAll || !countEl || !deleteBtn) return;

            const boxes = () => Array.from(document.querySelectorAll('.perm-check'));

            function refresh() {
                const all = boxes();
                const selected = all.filter(b => b.checked).length;
                countEl.textContent = selected;
                deleteBtn.disabled = selected === 0;
                checkAll.checked = all.length > 0 && selected === all.length;
                checkAll.indeterminate = selected > 0 && selected < all.length;
            }

            checkAll.addEventListener('change', function () {
                boxes().forEach(b => { b.checked = checkAll.checked; });
                refresh();
            });

            boxes().forEach(b => b.addEventListener('change', refresh));
            refresh();
        })();
    </script>
@endsection
