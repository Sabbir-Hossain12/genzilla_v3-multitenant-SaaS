@extends('platform_admin.layout.master')

@section('contents')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Assign Permissions</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.role.index') }}">Roles</a></li>
                        <li class="breadcrumb-item active">Permissions</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">
                            Permissions for <span class="fw-bolder text-primary">{{ $role->name }}</span>
                        </h4>
                        <a href="{{ route('admin.role.index') }}" class="btn btn-sm btn-light">
                            <i class="fa-solid fa-arrow-left me-1"></i> Back
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.role.permission.update', $role->id) }}" method="post">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            @foreach($permissionGroups as $groupName => $permissions)
                                <div class="col-md-6 mb-4">
                                    <div class="border rounded p-3 h-100">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h6 class="mb-0 text-uppercase text-muted">{{ $groupName ?: 'General' }}</h6>
                                            <div class="form-check">
                                                <input class="form-check-input group-toggle" type="checkbox"
                                                       data-group="group-{{ $loop->index }}" id="all-{{ $loop->index }}">
                                                <label class="form-check-label small" for="all-{{ $loop->index }}">Select all</label>
                                            </div>
                                        </div>
                                        <hr class="mt-0">
                                        @foreach($permissions as $permission)
                                            <div class="form-check mb-2">
                                                <input class="form-check-input group-{{ $loop->parent->index }}"
                                                       @checked($role->hasPermissionTo($permission->name))
                                                       name="permissions[]" type="checkbox"
                                                       id="perm-{{ $permission->id }}" value="{{ $permission->name }}">
                                                <label class="form-check-label" for="perm-{{ $permission->id }}">
                                                    {{ $permission->name }}
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="d-flex justify-content-end">
                            <button class="btn btn-primary" type="submit">
                                <i class="fa-solid fa-floppy-disk me-1"></i> Update Permissions
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('backendJs')
    <script>
        $(document).ready(function () {
            function syncGroup(group) {
                var boxes = $('.' + group);
                var checked = boxes.filter(':checked').length;
                $('[data-group="' + group + '"]').prop('checked', boxes.length > 0 && checked === boxes.length);
            }

            $('.group-toggle').on('change', function () {
                var group = $(this).data('group');
                $('.' + group).prop('checked', $(this).is(':checked'));
            });

            $('.group-0, .group-1, .group-2, .group-3, .group-4, .group-5, .group-6, .group-7, .group-8').on('change', function () {
                syncGroup($(this).prop('class').split(' ').find(function (c) {
                    return c.indexOf('group-') === 0 && c !== 'group-toggle';
                }));
            });

            $('.group-toggle').each(function () {
                syncGroup($(this).data('group'));
            });
        });
    </script>
@endpush
