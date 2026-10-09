@extends('platform_admin.layout.master')

@push('backendCss')
    <link href="{{ asset('backend') }}/assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css" rel="stylesheet" type="text/css">
    <link href="{{ asset('backend') }}/assets/libs/datatables.net-buttons-bs4/css/buttons.bootstrap4.min.css" rel="stylesheet" type="text/css">
@endpush

@section('contents')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Permissions</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">System Administration</a></li>
                        <li class="breadcrumb-item active">Permissions</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title">Permission List</h4>
                        @can('Create Permission')
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createPermissionModal">
                                <i class="fa-solid fa-plus me-1"></i> Create Permission
                            </button>
                        @endcan
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table mb-0 nowrap w-100" id="permissionTable">
                            <thead>
                            <tr>
                                <th>SL</th>
                                <th>Name</th>
                                <th>Group</th>
                                <th>Guard</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @can('Create Permission')
        <div class="modal fade" id="createPermissionModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Create Permission</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="createPermission">
                            @csrf
                            <div class="mb-3">
                                <label class="col-form-label">Name</label>
                                <input type="text" class="form-control" name="name" required>
                            </div>
                            <div class="mb-3">
                                <label class="col-form-label">Group</label>
                                <input type="text" class="form-control" name="group_name" list="permissionGroups" required>
                                <datalist id="permissionGroups">
                                    @foreach($groups as $group)
                                        <option value="{{ $group }}"></option>
                                    @endforeach
                                </datalist>
                            </div>
                            <div class="modal-footer px-0 pb-0">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary">Submit</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endcan

    @can('Edit Permission')
        <div class="modal fade" id="editPermissionModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Permission</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="editPermission">
                            @csrf
                            @method('PUT')
                            <div class="mb-3">
                                <label class="col-form-label">Name</label>
                                <input type="text" id="eName" class="form-control" name="name" required>
                            </div>
                            <div class="mb-3">
                                <label class="col-form-label">Group</label>
                                <input type="text" id="eGroup" class="form-control" name="group_name" list="permissionGroups" required>
                            </div>
                            <input id="id" type="hidden">
                            <div class="modal-footer px-0 pb-0">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary">Submit</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endcan
@endsection

@push('backendJs')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('backend') }}/assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="{{ asset('backend') }}/assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>
    <script>
        $(document).ready(function () {
            var token = $("meta[name='csrf-token']").attr('content');

            var permissionTable = $('#permissionTable').DataTable({
                order: [[0, 'asc']],
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.permission.data') }}",
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'name' },
                    { data: 'group', orderable: false, searchable: false },
                    { data: 'guard_name', render: function (data) {
                        return '<span class="badge bg-primary p-1">' + data + '</span>';
                    }},
                    { data: 'action', name: 'Actions', orderable: false, searchable: false }
                ]
            });

            function notify(title, icon, text) {
                Swal.fire({ title: title, text: text, icon: icon });
            }

            $('#createPermission').submit(function (e) {
                e.preventDefault();
                $.ajax({
                    type: 'POST',
                    headers: { 'X-CSRF-TOKEN': token },
                    url: "{{ route('admin.permission.store') }}",
                    data: new FormData(this),
                    processData: false,
                    contentType: false,
                    success: function (res) {
                        if (res.message === 'success') {
                            $('#createPermissionModal').modal('hide');
                            $('#createPermission')[0].reset();
                            permissionTable.ajax.reload();
                            notify('Success', 'success', 'Permission created.');
                        }
                    },
                    error: function (err) {
                        notify('Failed', 'error', err.responseJSON?.message || 'Something went wrong.');
                    }
                });
            });

            $(document).on('click', '.editButton', function () {
                var id = $(this).data('id');
                $('#id').val(id);

                $.ajax({
                    type: 'GET',
                    headers: { 'X-CSRF-TOKEN': token },
                    url: "{{ url('admin/permissions') }}/" + id + "/edit",
                    success: function (res) {
                        $('#eName').val(res.data.name);
                        $('#eGroup').val(res.data.group_name);
                    },
                    error: function () {
                        notify('Failed', 'error', 'Could not load permission.');
                    }
                });
            });

            $('#editPermission').submit(function (e) {
                e.preventDefault();
                var id = $('#id').val();
                $.ajax({
                    type: 'POST',
                    headers: { 'X-CSRF-TOKEN': token },
                    url: "{{ url('admin/permissions') }}/" + id,
                    data: new FormData(this),
                    processData: false,
                    contentType: false,
                    success: function (res) {
                        if (res.message === 'success') {
                            $('#editPermissionModal').modal('hide');
                            $('#editPermission')[0].reset();
                            permissionTable.ajax.reload();
                            notify('Success', 'success', 'Permission updated.');
                        }
                    },
                    error: function (err) {
                        notify('Failed', 'error', err.responseJSON?.message || 'Something went wrong.');
                    }
                });
            });

            $(document).on('click', '#deletePermissionBtn', function () {
                var id = $(this).data('id');
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!'
                }).then(function (result) {
                    if (result.isConfirmed) {
                        $.ajax({
                            type: 'DELETE',
                            url: "{{ url('admin/permissions') }}/" + id,
                            headers: { 'X-CSRF-TOKEN': token },
                            success: function () {
                                Swal.fire({ title: 'Deleted!', text: 'Permission has been deleted.', icon: 'success' });
                                permissionTable.ajax.reload();
                            },
                            error: function (err) {
                                notify('Failed', 'error', err.responseJSON?.message || 'Something went wrong.');
                            }
                        });
                    }
                });
            });
        });
    </script>
@endpush
