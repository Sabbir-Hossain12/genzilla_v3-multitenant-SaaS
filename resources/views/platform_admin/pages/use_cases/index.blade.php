@extends('platform_admin.layout.master')

@section('contents')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0 font-size-18">Platform Use Cases</h4>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createUseCaseModal">
                <i class="fa-solid fa-plus me-1"></i> Add Use Case
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
                        <th>Title</th>
                        <th>Badge</th>
                        <th>Description</th>
                        <th>BG Color</th>
                        <th width="100">Status</th>
                        <th width="120" class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($useCases as $uc)
                    <tr>
                        <td><span class="badge bg-secondary">{{ $uc->sort_order }}</span></td>
                        <td class="fw-bold">{{ $uc->title }}</td>
                        <td>
                            @if($uc->badge_label)
                                <span class="badge bg-{{ $uc->badge_type ?? 'info' }}">{{ $uc->badge_label }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>{{ Str::limit($uc->description, 80) }}</td>
                        <td>
                            @if($uc->bg_color)
                                <span class="badge border" style="background-color: {{ $uc->bg_color }}; color: #333;">{{ $uc->bg_color }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($uc->status)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-danger">Inactive</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-info me-1" data-bs-toggle="modal" data-bs-target="#editUseCaseModal{{ $uc->id }}">
                                <i class="fa-solid fa-pen"></i>
                            </button>
                            <form action="{{ route('admin.use-cases.destroy', $uc->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this use case?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger"><i class="fa-solid fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>

                    <!-- Edit Modal -->
                    <div class="modal fade" id="editUseCaseModal{{ $uc->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <form method="POST" action="{{ route('admin.use-cases.update', $uc->id) }}" class="modal-content">
                                @csrf
                                @method('PUT')
                                <div class="modal-header">
                                    <h5 class="modal-title">Edit Use Case</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label">Title</label>
                                        <input type="text" name="title" class="form-control" value="{{ $uc->title }}" required>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Badge Label</label>
                                            <input type="text" name="badge_label" class="form-control" value="{{ $uc->badge_label }}" placeholder="Popular">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Badge Style (success, info, warning)</label>
                                            <input type="text" name="badge_type" class="form-control" value="{{ $uc->badge_type }}" placeholder="success">
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Description</label>
                                        <textarea name="description" class="form-control" rows="3">{{ $uc->description }}</textarea>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Background Color Code</label>
                                            <input type="text" name="bg_color" class="form-control" value="{{ $uc->bg_color }}" placeholder="#eff6ff">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Sort Order</label>
                                            <input type="number" name="sort_order" class="form-control" value="{{ $uc->sort_order }}">
                                        </div>
                                    </div>
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" name="status" id="ucStatusEdit{{ $uc->id }}" {{ $uc->status ? 'checked' : '' }}>
                                        <label class="form-check-label" for="ucStatusEdit{{ $uc->id }}">Active Status</label>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-primary">Update Use Case</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">No use cases found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Create Modal -->
<div class="modal fade" id="createUseCaseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('admin.use-cases.store') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Add Use Case</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Title</label>
                    <input type="text" name="title" class="form-control" required placeholder="Digital Products & Downloads">
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Badge Label</label>
                        <input type="text" name="badge_label" class="form-control" placeholder="Popular">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Badge Style</label>
                        <input type="text" name="badge_type" class="form-control" placeholder="success">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3"></textarea>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Background Color Code</label>
                        <input type="text" name="bg_color" class="form-control" placeholder="#eff6ff">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Sort Order</label>
                        <input type="number" name="sort_order" class="form-control" value="0">
                    </div>
                </div>
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" name="status" id="ucStatusCreate" checked>
                    <label class="form-check-label" for="ucStatusCreate">Active Status</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">Save Use Case</button>
            </div>
        </form>
    </div>
</div>
@endsection
