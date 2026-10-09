@extends('platform_admin.layout.master')

@section('contents')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0 font-size-18">Platform Features</h4>
            <div class="page-title-right">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createFeatureModal">
                    <i class="fa-solid fa-plus me-1"></i> Add Feature
                </button>
            </div>
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
                        <th>Icon / Image</th>
                        <th>Title</th>
                        <th>Description</th>
                        <th width="100">Sort</th>
                        <th width="100">Status</th>
                        <th width="120" class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($features as $feature)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td class="text-center">
                            @if($feature->image)
                                <img src="{{ asset('storage/' . $feature->image) }}" class="rounded" style="max-height: 40px;" alt="Image">
                            @elseif($feature->icon)
                                <i class="{{ $feature->icon }} fs-3 text-primary"></i>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="fw-bold">{{ $feature->title }}</td>
                        <td>{{ Str::limit($feature->short_desc, 80) }}</td>
                        <td><span class="badge bg-secondary">{{ $feature->sort_order }}</span></td>
                        <td>
                            @if($feature->status)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-danger">Inactive</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-info me-1" data-bs-toggle="modal" data-bs-target="#editFeatureModal{{ $feature->id }}">
                                <i class="fa-solid fa-pen"></i>
                            </button>
                            <form action="{{ route('admin.features.destroy', $feature->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this feature?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger"><i class="fa-solid fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">No platform features found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Edit Modals -->
@foreach($features as $feature)
<div class="modal fade" id="editFeatureModal{{ $feature->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('admin.features.update', $feature->id) }}" enctype="multipart/form-data" class="modal-content">
            @csrf
            @method('PUT')
            <div class="modal-header">
                <h5 class="modal-title">Edit Feature</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Title</label>
                    <input type="text" name="title" class="form-control" value="{{ $feature->title }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">FontAwesome Icon Class</label>
                    <input type="text" name="icon" class="form-control" value="{{ $feature->icon }}" placeholder="fa-solid fa-bolt">
                </div>
                <div class="mb-3">
                    <label class="form-label">Feature Image (Optional)</label>
                    <input type="file" name="image" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label">Short Description</label>
                    <textarea name="short_desc" class="form-control" rows="3">{{ $feature->short_desc }}</textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Sort Order</label>
                    <input type="number" name="sort_order" class="form-control" value="{{ $feature->sort_order }}">
                </div>
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" name="status" id="statusEdit{{ $feature->id }}" {{ $feature->status ? 'checked' : '' }}>
                    <label class="form-check-label" for="statusEdit{{ $feature->id }}">Active Status</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">Update Feature</button>
            </div>
        </form>
    </div>
</div>
@endforeach

<!-- Create Modal -->
<div class="modal fade" id="createFeatureModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('admin.features.store') }}" enctype="multipart/form-data" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Add New Feature</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Title</label>
                    <input type="text" name="title" class="form-control" required placeholder="Instant Subdomains">
                </div>
                <div class="mb-3">
                    <label class="form-label">FontAwesome Icon Class</label>
                    <input type="text" name="icon" class="form-control" placeholder="fa-solid fa-globe">
                </div>
                <div class="mb-3">
                    <label class="form-label">Feature Image (Optional)</label>
                    <input type="file" name="image" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label">Short Description</label>
                    <textarea name="short_desc" class="form-control" rows="3"></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Sort Order</label>
                    <input type="number" name="sort_order" class="form-control" value="0">
                </div>
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" name="status" id="statusCreate" checked>
                    <label class="form-check-label" for="statusCreate">Active Status</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">Save Feature</button>
            </div>
        </form>
    </div>
</div>
@endsection
