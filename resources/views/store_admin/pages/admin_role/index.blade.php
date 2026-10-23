@extends('store_admin.layout.master')

@section('contents')
    <div class="space-y-6">
        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-slate-900 tracking-tight dark:text-white">Roles</h1>
                <p class="text-sm text-slate-500 mt-0.5">Define roles and control what each group of admins can access.</p>
            </div>
            @can('Store Role Create')
                <button type="button" onclick="openModal('createRoleModal')"
                        class="inline-flex items-center justify-center px-4 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-500 active:bg-indigo-700 rounded-lg shadow-sm transition-all gap-2 cursor-pointer">
                    <i class="fa-solid fa-plus text-xs"></i>
                    <span>Create Role</span>
                </button>
            @endcan
        </div>

        <!-- Main Card Container -->
        <div class="card overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100 dark:border-slate-800 gap-4 flex-wrap">
                <div>
                    <h2 class="font-bold text-ink dark:text-white">Roles</h2>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm table-auto">
                    <thead>
                    <tr class="border-b border-slate-100 dark:border-slate-800 bg-slate-50/80 dark:bg-slate-800/40">
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 w-16">#</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 w-56">Name</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 w-28">Guard</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400">Permissions</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 w-32">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($roles as $role)
                        @php($isProtected = in_array($role->name, $protected, true))
                        <tr class="trow border-b border-slate-100 dark:border-slate-800 hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition-colors">
                            <td class="px-4 py-3.5 w-16 text-slate-500 dark:text-slate-400">{{ $loop->iteration }}</td>
                            <td class="px-4 py-3.5 w-56">
                                <div class="flex items-center gap-2 font-semibold text-slate-900 dark:text-white">
                                    <span class="truncate">{{ $role->name }}</span>
                                    @if($isProtected)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium bg-amber-50 text-amber-700 border border-amber-200/60 dark:bg-amber-500/15 dark:text-amber-300 dark:border-amber-400/20" title="Protected role">
                                            <i class="fa-solid fa-lock text-[10px]"></i> Locked
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3.5 w-28">
                                <span class="px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-600 border border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700">{{ $role->guard_name }}</span>
                            </td>
                            <td class="px-4 py-3.5">
                                @if($role->permissions_count === 0)
                                    <span class="text-xs text-slate-400">No permissions</span>
                                @else
                                    <div class="flex items-center gap-1 flex-wrap">
                                        <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-sky-50 text-sky-700 border border-sky-200 dark:bg-sky-500/15 dark:text-sky-300 dark:border-sky-400/20">{{ $role->permissions_count }}</span>
                                        @foreach($role->permissions->take(3) as $permission)
                                            <span class="px-2 py-0.5 rounded text-xs bg-slate-100 text-slate-600 border border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700">{{ $permission->name }}</span>
                                        @endforeach
                                        @if($role->permissions_count > 3)
                                            <span class="text-xs text-slate-400">+{{ $role->permissions_count - 3 }} more</span>
                                        @endif
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-center w-32">
                                <div class="inline-flex items-center gap-1.5">
                                    @can('Store Assign Permission')
                                        <a href="{{ route('store.admin.role.permission.edit', $role->id) }}"
                                           class="p-2 text-indigo-600 hover:text-indigo-700 hover:bg-indigo-50 rounded-lg transition-colors cursor-pointer"
                                           title="Assign Permissions">
                                            <i class="fa-solid fa-user-shield text-xs"></i>
                                        </a>
                                    @endcan

                                    @can('Store Edit Role')
                                        @unless($isProtected)
                                            <button type="button" onclick="openModal('editRoleModal{{ $role->id }}')"
                                                    class="p-2 text-sky-600 hover:text-sky-700 hover:bg-sky-50 rounded-lg transition-colors cursor-pointer"
                                                    title="Manage Store Roles">
                                                <i class="fa-solid fa-pen text-xs"></i>
                                            </button>
                                        @endunless
                                    @endcan

                                    @can('Store Delete Role')
                                        @unless($isProtected)
                                            <form action="{{ route('store.admin.role.destroy', $role->id) }}" method="POST"
                                                  class="inline" onsubmit="return confirm('Delete this role?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="p-2 text-rose-600 hover:text-rose-700 hover:bg-rose-50 rounded-lg transition-colors cursor-pointer"
                                                        title="Manage Store Roles">
                                                    <i class="fa-solid fa-trash text-xs"></i>
                                                </button>
                                            </form>
                                        @endunless
                                    @endcan
                                </div>
                            </td>
                        </tr>

                        <!-- Edit Modal -->
                        @can('Store Edit Role')
                            @unless($isProtected)
                                <div id="editRoleModal{{ $role->id }}"
                                     class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
                                    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden border border-slate-100 transform transition-all max-h-[90vh] flex flex-col dark:bg-slate-900 dark:border-slate-800">
                                        <form method="POST" action="{{ route('store.admin.role.update', $role->id) }}" class="flex flex-col h-full overflow-hidden">
                                            @csrf
                                            @method('PUT')
                                            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 shrink-0 dark:border-slate-800">
                                                <h3 class="text-base font-bold text-slate-900 dark:text-white">Edit Role</h3>
                                                <button type="button" onclick="closeModal('editRoleModal{{ $role->id }}')"
                                                        class="text-slate-400 hover:text-slate-600 p-1 rounded-lg transition-colors cursor-pointer">
                                                    <i class="fa-solid fa-xmark text-lg"></i>
                                                </button>
                                            </div>
                                            <div class="p-6 space-y-4 overflow-y-auto flex-1">
                                                <div>
                                                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5 dark:text-slate-300">Role Name</label>
                                                    <input type="text" name="name" value="{{ old('name', $role->name) }}" required
                                                           class="w-full px-3.5 py-2 text-sm bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all dark:bg-slate-900 dark:border-slate-700">
                                                </div>
                                            </div>
                                            <div class="flex items-center justify-end gap-3 px-6 py-4 bg-slate-50 border-t border-slate-100 shrink-0 dark:bg-slate-800/50 dark:border-slate-800">
                                                <button type="button" onclick="closeModal('editRoleModal{{ $role->id }}')"
                                                        class="px-4 py-2 text-sm font-semibold text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 rounded-lg transition-colors cursor-pointer dark:bg-slate-900 dark:text-slate-200 dark:border-slate-700">
                                                    Close
                                                </button>
                                                <button type="submit"
                                                        class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-500 rounded-lg shadow-sm transition-colors cursor-pointer">
                                                    <i class="fa-solid fa-floppy-disk text-xs"></i>
                                                    <span>Update Role</span>
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            @endunless
                        @endcan
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-slate-400 py-12 text-sm">No roles found.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Create Modal -->
    @can('Store Role Create')
        <div id="createRoleModal"
             class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden border border-slate-100 transform transition-all max-h-[90vh] flex flex-col dark:bg-slate-900 dark:border-slate-800">
                <form method="POST" action="{{ route('store.admin.role.store') }}" class="flex flex-col h-full overflow-hidden">
                    @csrf
                    <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 shrink-0 dark:border-slate-800">
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Create New Role</h3>
                        <button type="button" onclick="closeModal('createRoleModal')"
                                class="text-slate-400 hover:text-slate-600 p-1 rounded-lg transition-colors cursor-pointer">
                            <i class="fa-solid fa-xmark text-lg"></i>
                        </button>
                    </div>
                    <div class="p-6 space-y-4 overflow-y-auto flex-1">
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5 dark:text-slate-300">Role Name</label>
                            <input type="text" name="name" value="{{ old('name') }}" required placeholder="Marketing Manager"
                                   class="w-full px-3.5 py-2 text-sm bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all dark:bg-slate-900 dark:border-slate-700">
                        </div>
                    </div>
                    <div class="flex items-center justify-end gap-3 px-6 py-4 bg-slate-50 border-t border-slate-100 shrink-0 dark:bg-slate-800/50 dark:border-slate-800">
                        <button type="button" onclick="closeModal('createRoleModal')"
                                class="px-4 py-2 text-sm font-semibold text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 rounded-lg transition-colors cursor-pointer dark:bg-slate-900 dark:text-slate-200 dark:border-slate-700">
                            Close
                        </button>
                        <button type="submit"
                                class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-500 rounded-lg shadow-sm transition-colors cursor-pointer">
                            <i class="fa-solid fa-floppy-disk text-xs"></i>
                            <span>Save Role</span>
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
    </script>
@endsection

