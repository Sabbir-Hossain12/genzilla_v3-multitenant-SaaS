@extends('platform_admin.layout.master')

@section('contents')
    <div class="space-y-6">
        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-slate-900 tracking-tight dark:text-white">Admin Users</h1>
                <p class="text-sm text-slate-500 mt-0.5">Manage platform administrators, their roles, and account access.</p>
            </div>
            @can('Platform Create Admin')
                <button type="button" onclick="openModal('createAdminModal')"
                        class="inline-flex items-center justify-center px-4 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-500 active:bg-indigo-700 rounded-lg shadow-sm transition-all gap-2 cursor-pointer">
                    <i class="fa-solid fa-plus text-xs"></i>
                    <span>Create Admin</span>
                </button>
            @endcan
        </div>

        <!-- Main Card Container -->
        <div class="card overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100 dark:border-slate-800 gap-4 flex-wrap">
                <div>
                    <h2 class="font-bold text-ink dark:text-white">Admins</h2>
                </div>
                <form action="{{ route('admin.admins.index') }}" method="GET" class="flex items-center gap-2">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name or email..."
                           class="w-56 px-3.5 py-2 text-sm dark:text-black bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                    <button type="submit"
                            class="px-4 py-2 text-sm font-semibold text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 rounded-lg transition-colors cursor-pointer">
                        Search
                    </button>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm table-auto">
                    <thead>
                    <tr class="border-b border-slate-100 dark:border-slate-800 bg-slate-50/80 dark:bg-slate-800/40">
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 w-40">#</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 w-50">Name</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 W-70">Email</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 w-60">Role</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 w-40">Status</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 w-40">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($admins as $key => $admin)
                        <tr class="trow border-b border-slate-100 dark:border-slate-800 hover:bg-slate-50/50 transition-colors">
                            <td class="px-4 py-3.5 w-16 text-slate-500 dark:text-slate-400">{{ $admins->firstItem() + $key }}</td>
                            <td class="px-4 py-3.5 w-48 truncate font-semibold text-slate-900 dark:text-white">{{ $admin->name }}</td>
                            <td class="px-4 py-3.5 truncate">
                                <a href="mailto:{{ $admin->email }}" class="text-indigo-600 hover:underline font-medium">{{ $admin->email }}</a>
                            </td>
                            <td class="px-4 py-3.5 w-44">
                                <div class="flex items-center gap-1 flex-wrap">
                                    @forelse($admin->getRoleNames() as $role)
                                        <span class="px-2 py-0.5 rounded text-xs font-medium bg-indigo-50 text-indigo-700 border border-indigo-200/60">{{ $role }}</span>
                                    @empty
                                        <span class="px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-600 border border-slate-200">No role</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="px-4 py-3.5 text-center w-28">
                                @can('Platform Status Admin')
                                    <form action="{{ route('admin.status', $admin->id) }}" method="POST" class="inline-flex justify-center">
                                        @csrf
                                        <label class="relative inline-flex items-center cursor-pointer" title="Toggle status">
                                            <input type="checkbox" class="sr-only peer" onchange="this.form.submit()" {{ $admin->status == 1 ? 'checked' : '' }}>
                                            <div class="w-9 h-5 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-indigo-600"></div>
                                        </label>
                                    </form>
                                @else
                                    @if($admin->status)
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
                                @endcan
                            </td>
                            <td class="px-4 py-3.5 text-center w-28">
                                <div class="inline-flex items-center gap-1.5">
                                    @can('Platform Edit Admin')
                                        <button type="button" onclick="openModal('editAdminModal{{ $admin->id }}')"
                                                class="p-2 text-sky-600 hover:text-sky-700 hover:bg-sky-50 rounded-lg transition-colors cursor-pointer"
                                                title="Edit Admin">
                                            <i class="fa-solid fa-pen text-xs"></i>
                                        </button>
                                    @endcan

                                    @can('Platform Delete Admin')
                                        <form action="{{ route('admin.admins.destroy', $admin->id) }}" method="POST"
                                              class="inline" onsubmit="return confirm('Delete this admin user?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="p-2 text-rose-600 hover:text-rose-700 hover:bg-rose-50 rounded-lg transition-colors cursor-pointer"
                                                    title="Delete Admin">
                                                <i class="fa-solid fa-trash text-xs"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>

                        <!-- Edit Modal -->
                        @can('Platform Edit Admin')
                            <div id="editAdminModal{{ $admin->id }}"
                                 class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
                                <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl overflow-hidden border border-slate-100 transform transition-all max-h-[90vh] flex flex-col">
                                    <form method="POST" action="{{ route('admin.admins.update', $admin->id) }}" class="flex flex-col h-full overflow-hidden">
                                        @csrf
                                        @method('PUT')
                                        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 shrink-0">
                                            <h3 class="text-base font-bold text-slate-900">Edit Admin: {{ $admin->name }}</h3>
                                            <button type="button" onclick="closeModal('editAdminModal{{ $admin->id }}')"
                                                    class="text-slate-400 hover:text-slate-600 p-1 rounded-lg transition-colors cursor-pointer">
                                                <i class="fa-solid fa-xmark text-lg"></i>
                                            </button>
                                        </div>
                                        <div class="p-6 space-y-4 overflow-y-auto flex-1">
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                <div>
                                                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Name</label>
                                                    <input type="text" name="name" value="{{ old('name', $admin->name) }}" required
                                                           class="w-full px-3.5 py-2 text-sm dark:text-black bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Email</label>
                                                    <input type="email" name="email" value="{{ old('email', $admin->email) }}" required
                                                           class="w-full px-3.5 py-2 text-sm dark:text-black bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                                                </div>
                                            </div>
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                <div>
                                                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Role</label>
                                                    <select name="role" required
                                                            class="w-full px-3.5 py-2 text-sm dark:text-black bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all cursor-pointer">
                                                        <option value="" disabled>Select a role</option>
                                                        @foreach($roles as $role)
                                                            <option value="{{ $role->name }}" {{ $admin->hasRole($role->name) ? 'selected' : '' }}>{{ $role->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Password <span class="text-slate-400 normal-case tracking-normal">(blank to keep)</span></label>
                                                    <input type="password" name="password" minlength="6"
                                                           class="w-full px-3.5 py-2 text-sm dark:text-black bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex items-center justify-end gap-3 px-6 py-4 bg-slate-50 border-t border-slate-100 shrink-0">
                                            <button type="button" onclick="closeModal('editAdminModal{{ $admin->id }}')"
                                                    class="px-4 py-2 text-sm font-semibold text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 rounded-lg transition-colors cursor-pointer">
                                                Close
                                            </button>
                                            <button type="submit"
                                                    class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-500 rounded-lg shadow-sm transition-colors cursor-pointer">
                                                <i class="fa-solid fa-floppy-disk text-xs"></i>
                                                <span>Update Admin</span>
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        @endcan
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-slate-400 py-12 text-sm">No admin users found.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            @if($admins->hasPages())
                <div class="px-5 py-4 border-t border-slate-100 dark:border-slate-800">
                    {{ $admins->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Create Modal -->
    @can('Platform Create Admin')
        <div id="createAdminModal"
             class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl overflow-hidden border border-slate-100 transform transition-all max-h-[90vh] flex flex-col">
                <form method="POST" action="{{ route('admin.admins.store') }}" class="flex flex-col h-full overflow-hidden">
                    @csrf
                    <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 shrink-0">
                        <h3 class="text-base font-bold text-slate-900">Create New Admin</h3>
                        <button type="button" onclick="closeModal('createAdminModal')"
                                class="text-slate-400 hover:text-slate-600 p-1 rounded-lg transition-colors cursor-pointer">
                            <i class="fa-solid fa-xmark text-lg"></i>
                        </button>
                    </div>
                    <div class="p-6 space-y-4 overflow-y-auto flex-1">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Name</label>
                                <input type="text" name="name" value="{{ old('name') }}" required placeholder="John Doe"
                                       class="w-full px-3.5 py-2 text-sm dark:text-black bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Email</label>
                                <input type="email" name="email" value="{{ old('email') }}" required placeholder="john@example.com"
                                       class="w-full px-3.5 py-2 text-sm dark:text-black bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Role</label>
                                <select name="role" required
                                        class="w-full px-3.5 py-2 text-sm dark:text-black bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all cursor-pointer">
                                    <option value="" disabled {{ old('role') ? '' : 'selected' }}>Select a role</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->name }}" {{ old('role') == $role->name ? 'selected' : '' }}>{{ $role->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Password</label>
                                <input type="password" name="password" minlength="6" required
                                       class="w-full px-3.5 py-2 text-sm dark:text-black bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center justify-end gap-3 px-6 py-4 bg-slate-50 border-t border-slate-100 shrink-0">
                        <button type="button" onclick="closeModal('createAdminModal')"
                                class="px-4 py-2 text-sm font-semibold text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 rounded-lg transition-colors cursor-pointer">
                            Close
                        </button>
                        <button type="submit"
                                class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-500 rounded-lg shadow-sm transition-colors cursor-pointer">
                            <i class="fa-solid fa-floppy-disk text-xs"></i>
                            <span>Save Admin</span>
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
