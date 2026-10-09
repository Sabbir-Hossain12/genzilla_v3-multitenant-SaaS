@extends('platform_admin.layout.master')

@section('contents')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0 font-size-18">Platform Store Demos</h4>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createDemoModal">
                <i class="fa-solid fa-plus me-1"></i> Add Store Demo
            </button>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="60">Sort</th>
                        <th>Preview Image</th>
                        <th>Title</th>
                        <th>Slug</th>
                        <th>Demo Link</th>
                        <th width="100">Status</th>
                        <th width="120" class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($demos as $demo)
                    <tr>
                        <td><span class="badge bg-secondary">{{ $demo->sort_order }}</span></td>
                        <td class="text-center">
                            @if($demo->image)
                                <img src="{{ asset('storage/' . $demo->image) }}" class="rounded shadow-sm" style="max-height: 45px; max-width: 80px;" alt="Demo">
                            @else
                                <span class="text-muted">No Image</span>
                            @endif
                        </td>
                        <td class="fw-bold">{{ $demo->title }}</td>
                        <td><code>{{ $demo->slug }}</code></td>
                        <td>
                            @if($demo->link)
                                <a href="{{ $demo->link }}" target="_blank" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Visit Demo</a>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($demo->status)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-danger">Inactive</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-info me-1" data-bs-toggle="modal" data-bs-target="#editDemoModal{{ $demo->id }}">
                                <i class="fa-solid fa-pen"></i>
                            </button>
                            <form action="{{ route('admin.store-demos.destroy', $demo->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this store demo?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger"><i class="fa-solid fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>

                    <!-- Edit Modal -->
                    <div class="modal fade" id="editDemoModal{{ $demo->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <form method="POST" action="{{ route('admin.store-demos.update', $demo->id) }}" enctype="multipart/form-data" class="modal-content">
                                @csrf
                                @method('PUT')
                                <div class="modal-header">
                                    <h5 class="modal-title">Edit Store Demo</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label">Title</label>
                                        <input type="text" name="title" class="form-control" value="{{ $demo->title }}" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Slug</label>
                                        <input type="text" name="slug" class="form-control" value="{{ $demo->slug }}">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Demo Link URL</label>
                                        <input type="url" name="link" class="form-control" value="{{ $demo->link }}" placeholder="https://fashion.mysaas.com">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Preview Image</label>
                                        <input type="file" name="image" class="form-control">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Sort Order</label>
                                        <input type="number" name="sort_order" class="form-control" value="{{ $demo->sort_order }}">
                                    </div>
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" name="status" id="demoStatusEdit{{ $demo->id }}" {{ $demo->status ? 'checked' : '' }}>
                                        <label class="form-check-label" for="demoStatusEdit{{ $demo->id }}">Active Status</label>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-primary">Update Demo</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">No store demos found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Create Modal -->
<div class="modal fade" id="createDemoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('admin.store-demos.store') }}" enctype="multipart/form-data" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Add Store Demo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Title</label>
                    <input type="text" name="title" class="form-control" required placeholder="Fashion Boutique Demo">
                </div>
                <div class="mb-3">
                    <label class="form-label">Slug</label>
                    <input type="text" name="slug" class="form-control" placeholder="fashion-boutique">
                </div>
                <div class="mb-3">
                    <label class="form-label">Demo Link URL</label>
                    <input type="url" name="link" class="form-control" placeholder="https://fashion.mysaas.com">
                </div>
                <div class="mb-3">
                    <label class="form-label">Preview Image</label>
                    <input type="file" name="image" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label">Sort Order</label>
                    <input type="number" name="sort_order" class="form-control" value="0">
                </div>
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" name="status" id="demoStatusCreate" checked>
                    <label class="form-check-label" for="demoStatusCreate">Active Status</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">Save Demo</button>
            </div>
        </form>
    </div>
</div>
@endsection
