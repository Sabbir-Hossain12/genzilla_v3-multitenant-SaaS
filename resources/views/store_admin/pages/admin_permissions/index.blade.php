@extends('store_admin.layout.master')

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

                    <form method="GET" action="{{ route('store.admin.permission.index') }}"
                          class="flex flex-wrap items-center gap-2">
                        <input type="hidden" name="sort" value="{{ $filters['sort'] }}">
                        <input type="hidden" name="direction" value="{{ $filters['direction'] }}">

                        <div class="relative">
                            <i class="fa-solid fa-magnifying-glass absolute left-2.5 top-1/2 -translate-y-1/2 text-xs text-slate-400 pointer-events-none"></i>
                            <input type="text" name="search" value="{{ $filters['search'] }}" placeholder="Search name or group..."
                                   class="w-full sm:w-56 pl-7 pr-3 py-1.5 text-xs bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all dark:bg-slate-900 dark:border-slate-700">
                        </div>

                        <button type="submit"
                                class="px-3 py-1.5 text-xs font-semibold text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 rounded-lg transition-colors cursor-pointer dark:bg-slate-900 dark:text-slate-200 dark:border-slate-700">
                            Search
                        </button>

                        @if($hasFilters)
                            <a href="{{ route('store.admin.permission.index') }}"
                               class="px-3 py-1.5 text-xs font-semibold text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors cursor-pointer">
                                Reset
                            </a>
                        @endif
                    </form>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm table-auto">
                    <thead>
                    <tr class="border-b border-slate-100 dark:border-slate-800 bg-slate-50/80 dark:bg-slate-800/40">
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
                                Panel
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
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($permissions as $key => $permission)
                        <tr class="trow border-b border-slate-100 dark:border-slate-800 hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition-colors">
                            <td class="px-4 py-3.5 w-16 text-slate-500 dark:text-slate-400">{{ $permissions->firstItem() + $key }}</td>
                            <td class="px-4 py-3.5 font-semibold text-slate-900 dark:text-white">{{ $permission->name }}</td>

                            <td class="px-4 py-3.5 w-56">
                                <span class="px-2 py-0.5 rounded text-xs font-medium bg-indigo-50 text-indigo-700 border border-indigo-200/60 dark:bg-indigo-500/15 dark:text-indigo-300 dark:border-indigo-400/20">{{ $permission->panel ?: '' }}</span>
                            </td>

                            <td class="px-4 py-3.5 w-56">
                                <span class="px-2 py-0.5 rounded text-xs font-medium bg-indigo-50 text-indigo-700 border border-indigo-200/60 dark:bg-indigo-500/15 dark:text-indigo-300 dark:border-indigo-400/20">{{ $permission->group_name ?: 'General' }}</span>
                            </td>
                            <td class="px-4 py-3.5 w-28">
                                <span class="px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-600 border border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700">{{ $permission->guard_name }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-slate-400 py-12 text-sm">
                                No permissions found.
                                @if($hasFilters)
                                    <a href="{{ route('store.admin.permission.index') }}" class="text-indigo-600 hover:underline ml-1">Clear filters</a>
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
