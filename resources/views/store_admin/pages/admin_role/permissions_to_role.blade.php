@extends('store_admin.layout.master')

@section('contents')
    <div class="space-y-6">
        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-slate-900 tracking-tight dark:text-white">Assign Permissions</h1>
                <p class="text-sm text-slate-500 mt-0.5">Permissions for <span class="font-semibold text-indigo-600">{{ $role->name }}</span></p>
            </div>
            <a href="{{ route('store.admin.role.index') }}"
               class="inline-flex items-center justify-center px-4 py-2 text-sm font-semibold text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 rounded-lg transition-colors gap-2 cursor-pointer">
                <i class="fa-solid fa-arrow-left text-xs"></i>
                <span>Back to Roles</span>
            </a>
        </div>

        <!-- Main Card Container -->
        <form action="{{ route('store.admin.role.permission.update', $role->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @forelse($permissionGroups as $groupName => $permissions)
                    @php($groupKey = 'group-'.$loop->index)
                    <div class="card p-5">
                        <div class="flex items-center justify-between mb-3">
                            <h6 class="text-xs font-bold uppercase tracking-wider text-slate-600">{{ $groupName ?: 'General' }}</h6>
                            <label class="flex items-center gap-2 cursor-pointer text-xs font-medium text-slate-500">
                                <input type="checkbox" class="group-toggle rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                                       data-group="{{ $groupKey }}">
                                <span>Select all</span>
                            </label>
                        </div>
                        <hr class="border-slate-100 mb-3">
                        <div class="space-y-2.5">
                            @foreach($permissions as $permission)
                                <label class="flex items-center gap-2 cursor-pointer text-sm text-slate-700">
                                    <input type="checkbox" name="permissions[]" value="{{ $permission->name }}"
                                           data-group-member="{{ $groupKey }}"
                                           @checked($role->hasPermissionTo($permission->name))
                                           class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                    <span>{{ $permission->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <div class="md:col-span-2 card p-12 text-center text-slate-400 text-sm">No permissions defined yet.</div>
                @endforelse
            </div>

            <div class="flex items-center justify-end">
                <button type="submit"
                        class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-500 rounded-lg shadow-sm transition-colors cursor-pointer">
                    <i class="fa-solid fa-floppy-disk text-xs"></i>
                    <span>Update Permissions</span>
                </button>
            </div>
        </form>
    </div>

    <script>
        function syncGroupToggle(group) {
            const members = Array.from(document.querySelectorAll('input[data-group-member="' + group + '"]'));
            const toggle = document.querySelector('.group-toggle[data-group="' + group + '"]');
            if (toggle) {
                toggle.checked = members.length > 0 && members.every(m => m.checked);
            }
        }

        document.querySelectorAll('.group-toggle').forEach(function (toggle) {
            toggle.addEventListener('change', function () {
                const group = this.dataset.group;
                document.querySelectorAll('input[data-group-member="' + group + '"]').forEach(function (cb) {
                    cb.checked = toggle.checked;
                });
            });
        });

        document.querySelectorAll('input[data-group-member]').forEach(function (cb) {
            cb.addEventListener('change', function () {
                syncGroupToggle(this.dataset.groupMember);
            });
        });

        document.querySelectorAll('.group-toggle').forEach(function (toggle) {
            syncGroupToggle(toggle.dataset.group);
        });
    </script>
@endsection

