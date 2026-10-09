@extends('platform_admin.layout.master')

@section('contents')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0 font-size-18">Contact Messages</h4>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="60">#</th>
                        <th>Sender Name</th>
                        <th>Email & Phone</th>
                        <th>Company</th>
                        <th>Message Preview</th>
                        <th>Status</th>
                        <th>Submitted At</th>
                        <th width="120" class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($contacts as $contact)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td class="fw-bold">{{ $contact->name }}</td>
                        <td>
                            <a href="mailto:{{ $contact->email }}">{{ $contact->email }}</a> <br>
                            <small class="text-muted">{{ $contact->phone ?? '-' }}</small>
                        </td>
                        <td>{{ $contact->company_name ?? '-' }}</td>
                        <td>{{ Str::limit($contact->message, 70) }}</td>
                        <td>
                            @switch($contact->status)
                                @case('new')
                                    <span class="badge bg-danger">New</span>
                                    @break
                                @case('contacted')
                                    <span class="badge bg-warning text-dark">Contacted</span>
                                    @break
                                @case('resolved')
                                    <span class="badge bg-success">Resolved</span>
                                    @break
                                @default
                                    <span class="badge bg-secondary">{{ $contact->status }}</span>
                            @endswitch
                        </td>
                        <td>{{ $contact->created_at ? $contact->created_at->format('M d, Y H:i') : '-' }}</td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-info me-1" data-bs-toggle="modal" data-bs-target="#viewContactModal{{ $contact->id }}">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                            <form action="{{ route('admin.contacts.destroy', $contact->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this message?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger"><i class="fa-solid fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>

                    <!-- View & Status Modal -->
                    <div class="modal fade" id="viewContactModal{{ $contact->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Contact Message from {{ $contact->name }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <p class="mb-1"><strong>Email:</strong> <a href="mailto:{{ $contact->email }}">{{ $contact->email }}</a></p>
                                            <p class="mb-1"><strong>Phone:</strong> {{ $contact->phone ?? 'N/A' }}</p>
                                        </div>
                                        <div class="col-md-6">
                                            <p class="mb-1"><strong>Company:</strong> {{ $contact->company_name ?? 'N/A' }}</p>
                                            <p class="mb-1"><strong>Date:</strong> {{ $contact->created_at ? $contact->created_at->format('M d, Y H:i A') : 'N/A' }}</p>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label font-weight-bold">Full Message:</label>
                                        <div class="p-3 bg-light rounded border text-wrap">
                                            {{ $contact->message }}
                                        </div>
                                    </div>

                                    <form method="POST" action="{{ route('admin.contacts.update-status', $contact->id) }}">
                                        @csrf
                                        @method('PATCH')
                                        <div class="row align-items-center">
                                            <div class="col-md-6">
                                                <label class="form-label font-weight-bold">Update Status:</label>
                                                <select name="status" class="form-select">
                                                    <option value="new" {{ $contact->status == 'new' ? 'selected' : '' }}>New</option>
                                                    <option value="contacted" {{ $contact->status == 'contacted' ? 'selected' : '' }}>Contacted</option>
                                                    <option value="resolved" {{ $contact->status == 'resolved' ? 'selected' : '' }}>Resolved</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6 mt-4">
                                                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i> Save Status</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">No contact messages received yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
