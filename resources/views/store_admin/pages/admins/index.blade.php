@extends('store_admin.layout.master')

@section('contents')
    <div class="space-y-6">
        @if($errors->any())
            <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                <ul class="list-disc list-inside space-y-1">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-slate-900 tracking-tight dark:text-white">Store Admins</h1>
                <p class="text-sm text-slate-500 mt-0.5">Manage staff accounts and their access to this store.</p>
            </div>
            @can('Store Create Admin')
                <button type="button" onclick="openModal('createAdminModal')" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-500 rounded-lg shadow-sm">
                    <i class="fa-solid fa-plus text-xs"></i> Create Admin
                </button>
            @endcan
        </div>

        <div class="card overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100 dark:border-slate-800 gap-4 flex-wrap">
                <div><h2 class="font-bold text-ink dark:text-white">Admins</h2><p class="text-xs text-slate-400 mt-0.5">{{ $admins->total() }} staff accounts</p></div>
                <form action="{{ route('store.admin.admins.index') }}" method="GET" class="flex items-center gap-2">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name or email..." class="w-56 px-3.5 py-2 text-sm bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 dark:bg-slate-900 dark:border-slate-700">
                    <button class="px-4 py-2 text-sm font-semibold text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 rounded-lg dark:bg-slate-900 dark:text-slate-200 dark:border-slate-700">Search</button>
                </form>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead><tr class="border-b border-slate-100 dark:border-slate-800 bg-slate-50/80 dark:bg-slate-800/40">
                        <th class="text-left px-4 py-3 text-xs text-slate-500">#</th><th class="text-left px-4 py-3 text-xs text-slate-500">Name</th><th class="text-left px-4 py-3 text-xs text-slate-500">Email</th><th class="text-left px-4 py-3 text-xs text-slate-500">Role</th><th class="text-center px-4 py-3 text-xs text-slate-500">Status</th><th class="text-center px-4 py-3 text-xs text-slate-500">Actions</th>
                    </tr></thead>
                    <tbody>
                    @forelse($admins as $key => $admin)
                        @php($isOwner = (int) $admin->id === (int) auth()->user()->tenant->user_id)
                        <tr class="border-b border-slate-100 dark:border-slate-800 hover:bg-slate-50/50 dark:hover:bg-slate-800/40">
                            <td class="px-4 py-3.5 text-slate-500">{{ $admins->firstItem() + $key }}</td>
                            <td class="px-4 py-3.5 font-semibold text-slate-900 dark:text-white">{{ $admin->name }} @if($isOwner)<span class="ml-1 text-[10px] text-amber-700">Owner</span>@endif</td>
                            <td class="px-4 py-3.5"><a href="mailto:{{ $admin->email }}" class="text-indigo-600 hover:underline">{{ $admin->email }}</a></td>
                            <td class="px-4 py-3.5"><div class="flex flex-wrap gap-1">@forelse($admin->getRoleNames() as $role)<span class="px-2 py-0.5 rounded text-xs bg-indigo-50 text-indigo-700 border border-indigo-200/60 dark:bg-indigo-500/15 dark:text-indigo-300 dark:border-indigo-400/20">{{ $role }}</span>@empty<span class="text-xs text-slate-400">No role</span>@endforelse</div></td>
                            <td class="px-4 py-3.5 text-center">
                                @can('Store Status Admin')
                                    @unless($isOwner || (int) $admin->id === (int) auth()->id())
                                        <form action="{{ route('store.admin.admins.status', $admin->id) }}" method="POST">@csrf @method('PATCH')<button class="px-2.5 py-1 rounded-full text-xs font-medium {{ $admin->status ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">{{ $admin->status ? 'Active' : 'Inactive' }}</button></form>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full text-xs {{ $admin->status ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">{{ $admin->status ? 'Active' : 'Inactive' }}</span>
                                    @endunless
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-xs {{ $admin->status ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">{{ $admin->status ? 'Active' : 'Inactive' }}</span>
                                @endcan
                            </td>
                            <td class="px-4 py-3.5 text-center"><div class="inline-flex items-center gap-1">
                                @can('Store Edit Admin')
                                    @unless($isOwner)
                                        <button type="button" onclick="openModal('editAdminModal{{ $admin->id }}')" class="p-2 text-sky-600 hover:bg-sky-50 rounded-lg" title="Edit"><i class="fa-solid fa-pen text-xs"></i></button>
                                    @endunless
                                @endcan
                                @can('Store Delete Admin')
                                    @unless($isOwner || (int) $admin->id === (int) auth()->id())
                                        <form action="{{ route('store.admin.admins.destroy', $admin->id) }}" method="POST" onsubmit="return confirm('Delete this admin account?')">@csrf @method('DELETE')<button class="p-2 text-rose-600 hover:bg-rose-50 rounded-lg" title="Delete"><i class="fa-solid fa-trash text-xs"></i></button></form>
                                    @endunless
                                @endcan
                            </div></td>
                        </tr>
                        @can('Store Edit Admin')
                            @unless($isOwner)
                                <div id="editAdminModal{{ $admin->id }}" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
                                    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden max-h-[90vh] flex flex-col dark:bg-slate-900">
                                        <form method="POST" action="{{ route('store.admin.admins.update', $admin->id) }}" class="flex flex-col overflow-y-auto">@csrf @method('PUT')
                                            <div class="px-6 py-4 border-b"><h3 class="font-bold">Edit Admin: {{ $admin->name }}</h3></div>
                                            <div class="p-6 space-y-4">
                                                <label class="block text-sm font-medium">Name<input required name="name" value="{{ $admin->name }}" class="mt-1 w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 text-sm"></label>
                                                <label class="block text-sm font-medium">Email<input required type="email" name="email" value="{{ $admin->email }}" class="mt-1 w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 text-sm"></label>
                                                <label class="block text-sm font-medium">New password <span class="text-slate-400">(leave blank to keep current)</span><input type="password" name="password" minlength="8" class="mt-1 w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 text-sm"></label>
                                                <label class="block text-sm font-medium">Role<select required name="role_id" class="mt-1 w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 text-sm">@foreach($roles->where('name', '!=', 'Store Owner') as $role)<option value="{{ $role->id }}" @selected($admin->roles->contains('id', $role->id))>{{ $role->name }}</option>@endforeach</select></label>
                                            </div>
                                            <div class="flex justify-end gap-2 px-6 py-4 border-t"><button type="button" onclick="closeModal('editAdminModal{{ $admin->id }}')" class="px-4 py-2 border rounded-lg text-sm">Cancel</button><button class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm">Save Changes</button></div>
                                        </form>
                                    </div>
                                </div>
                            @endunless
                        @endcan
                    @empty
                        <tr><td colspan="6" class="text-center text-slate-400 py-12">No store admins found.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            @if($admins->hasPages())<div class="px-5 py-4 border-t">{{ $admins->withQueryString()->links() }}</div>@endif
        </div>
    </div>

    @can('Store Create Admin')
        <div id="createAdminModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden dark:bg-slate-900">
                <form method="POST" action="{{ route('store.admin.admins.store') }}">@csrf
                    <div class="px-6 py-4 border-b"><h3 class="font-bold">Create Store Admin</h3></div>
                    <div class="p-6 space-y-4">
                        <label class="block text-sm font-medium">Name<input required name="name" value="{{ old('name') }}" class="mt-1 w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 text-sm"></label>
                        <label class="block text-sm font-medium">Email<input required type="email" name="email" value="{{ old('email') }}" class="mt-1 w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 text-sm"></label>
                        <label class="block text-sm font-medium">Temporary password<input required type="password" name="password" minlength="8" class="mt-1 w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 text-sm"></label>
                        <label class="block text-sm font-medium">Role<select required name="role_id" class="mt-1 w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 text-sm"><option value="">Select role</option>@foreach($roles->where('name', '!=', 'Store Owner') as $role)<option value="{{ $role->id }}">{{ $role->name }}</option>@endforeach</select></label>
                    </div>
                    <div class="flex justify-end gap-2 px-6 py-4 border-t"><button type="button" onclick="closeModal('createAdminModal')" class="px-4 py-2 border rounded-lg text-sm">Cancel</button><button class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm">Create Admin</button></div>
                </form>
            </div>
        </div>
    @endcan
    <script>
        function openModal(id) { const el = document.getElementById(id); if (el) { el.classList.remove('hidden'); el.classList.add('flex'); } }
        function closeModal(id) { const el = document.getElementById(id); if (el) { el.classList.remove('flex'); el.classList.add('hidden'); } }
    </script>
@endsection
