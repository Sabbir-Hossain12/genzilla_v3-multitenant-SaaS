@extends('platform_admin.layout.master')

@push('backendCss')
    <link href="{{ asset('backend') }}/assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css" rel="stylesheet" type="text/css">
    <link href="{{ asset('backend') }}/assets/libs/datatables.net-buttons-bs4/css/buttons.bootstrap4.min.css" rel="stylesheet" type="text/css">
@endpush

@section('contents')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Admin Users</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">System Administration</a></li>
                        <li class="breadcrumb-item active">Admins</li>
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
                        <h4 class="card-title">Admins List</h4>
                        @can('Create Admin')
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createAdminModal">
                                <i class="fa-solid fa-plus me-1"></i> Create Admin
                            </button>
                        @endcan
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table mb-0 nowrap w-100" id="adminTable">
                            <thead>
                            <tr>
                                <th>SL</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
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

    @can('Create Admin')
        <div class="modal fade" id="createAdminModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Create Admin</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="createAdmin">
                            @csrf
                            <div class="mb-3">
                                <label class="col-form-label">Name</label>
                                <input type="text" class="form-control" name="name" required>
                            </div>
                            <div class="mb-3">
                                <label class="col-form-label">Email</label>
                                <input type="email" class="form-control" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label class="col-form-label">Role</label>
                                <select class="form-select" name="role" required>
                                    <option value="">Select a role</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->name }}">{{ $role->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="col-form-label">Password</label>
                                <input type="password" class="form-control" name="password" minlength="6" required>
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

    @can('Edit Admin')
        <div class="modal fade" id="editAdminModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Admin</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="editAdmin">
                            @csrf
                            @method('PUT')
                            <div class="mb-3">
                                <label class="col-form-label">Name</label>
                                <input type="text" id="eName" class="form-control" name="name" required>
                            </div>
                            <div class="mb-3">
                                <label class="col-form-label">Email</label>
                                <input type="email" id="eEmail" class="form-control" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label class="col-form-label">Role</label>
                                <select name="role" id="rolesId" class="form-select" required>
                                    <option value="">Select a role</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->name }}">{{ $role->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="col-form-label">Password <small class="text-muted">(leave blank to keep current)</small></label>
                                <input type="password" id="ePassword" class="form-control" name="password">
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

            var adminTable = $('#adminTable').DataTable({
                order: [[0, 'asc']],
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.data') }}",
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'name' },
                    { data: 'email' },
                    { data: 'role' },
                    { data: 'status', name: 'Status', orderable: false, searchable: false },
                    { data: 'action', name: 'Actions', orderable: false, searchable: false }
                ]
            });

            function notify(title, icon) {
                Swal.fire({ title: title, icon: icon });
            }

            $('#createAdmin').submit(function (e) {
                e.preventDefault();
                $.ajax({
                    type: 'POST',
                    headers: { 'X-CSRF-TOKEN': token },
                    url: "{{ route('admin.admins.store') }}",
                    data: new FormData(this),
                    processData: false,
                    contentType: false,
                    success: function (res) {
                        if (res.message === 'success') {
                            $('#createAdminModal').modal('hide');
                            $('#createAdmin')[0].reset();
                            adminTable.ajax.reload();
                            notify('Admin created', 'success');
                        }
                    },
                    error: function (err) {
                        Swal.fire({ title: 'Failed', text: err.responseJSON?.message || 'Something went wrong.', icon: 'error' });
                    }
                });
            });

            $(document).on('click', '.editButton', function () {
                var id = $(this).data('id');
                $('#id').val(id);

                $.ajax({
                    type: 'GET',
                    headers: { 'X-CSRF-TOKEN': token },
                    url: "{{ url('admin/admins') }}/" + id + "/edit",
                    success: function (res) {
                        $('#eName').val(res.data.name);
                        $('#eEmail').val(res.data.email);
                        $('#rolesId').val(res.data.role);
                    },
                    error: function () {
                        notify('Could not load admin', 'error');
                    }
                });
            });

            $('#editAdmin').submit(function (e) {
                e.preventDefault();
                var id = $('#id').val();
                $.ajax({
                    type: 'POST',
                    headers: { 'X-CSRF-TOKEN': token },
                    url: "{{ url('admin/admins') }}/" + id,
                    data: new FormData(this),
                    processData: false,
                    contentType: false,
                    success: function (res) {
                        if (res.message === 'success') {
                            $('#editAdminModal').modal('hide');
                            $('#editAdmin')[0].reset();
                            adminTable.ajax.reload();
                            notify('Admin updated', 'success');
                        }
                    },
                    error: function (err) {
                        Swal.fire({ title: 'Failed', text: err.responseJSON?.message || 'Something went wrong.', icon: 'error' });
                    }
                });
            });

            $(document).on('click', '#deleteAdminBtn', function () {
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
                            url: "{{ url('admin/admins') }}/" + id,
                            headers: { 'X-CSRF-TOKEN': token },
                            success: function () {
                                Swal.fire({ title: 'Deleted!', text: 'Admin has been deleted.', icon: 'success' });
                                adminTable.ajax.reload();
                            },
                            error: function (err) {
                                Swal.fire({ title: 'Failed', text: err.responseJSON?.message || 'Something went wrong.', icon: 'error' });
                            }
                        });
                    }
                });
            });

            $(document).on('click', '#adminStatus', function () {
                var id = $(this).data('id');
                var status = $(this).data('status');
                $.ajax({
                    type: 'POST',
                    url: "{{ route('admin.status') }}",
                    headers: { 'X-CSRF-TOKEN': token },
                    data: { id: id, status: status },
                    success: function (res) {
                        adminTable.ajax.reload();
                        Swal.fire({ title: res.status == 1 ? 'Status changed to Active' : 'Status changed to Inactive', icon: 'success' });
                    },
                    error: function (err) {
                        Swal.fire({ title: 'Failed', text: err.responseJSON?.message || 'Something went wrong.', icon: 'error' });
                    }
                });
            });
        });
    </script>
@endpush
