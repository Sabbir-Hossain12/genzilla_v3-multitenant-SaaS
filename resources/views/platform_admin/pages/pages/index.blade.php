@extends('platform_admin.layout.master')

@section('contents')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0 font-size-18">Custom Pages</h4>
            <a class="btn btn-primary" href="{{ route('admin.pages.create') }}">
                <i class="fa-solid fa-plus me-1"></i> Create New Page
            </a>
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
                        <th>Title</th>
                        <th>Slug</th>
                        <th>Meta Title</th>
                        <th width="100">Status</th>
                        <th width="120" class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pages as $page)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td class="fw-bold">{{ $page->title }}</td>
                        <td><code>/{{ $page->slug }}</code></td>
                        <td>{{ $page->meta_title ?? '-' }}</td>
                        <td>
                            @if($page->status)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Draft</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <a class="btn btn-sm btn-info me-1" href="{{ route('admin.pages.edit', $page->id) }}">
                                <i class="fa-solid fa-pen"></i>
                            </a>
                            <form action="{{ route('admin.pages.destroy', $page->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this page?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger"><i class="fa-solid fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">No custom pages found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
