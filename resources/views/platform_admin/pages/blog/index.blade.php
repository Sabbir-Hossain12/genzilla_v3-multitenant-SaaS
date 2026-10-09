@extends('platform_admin.layout.master')

@section('contents')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0 font-size-18">Blog Posts</h4>
            <div>
                <a href="{{ route('admin.blog-categories.index') }}" class="btn btn-light me-1">
                    <i class="fa-solid fa-tags me-1"></i> Categories
                </a>
                <a href="{{ route('admin.blog.create') }}" class="btn btn-primary">
                    <i class="fa-solid fa-plus me-1"></i> Create Post
                </a>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.blog.index') }}" class="row g-2 align-items-end mb-3">
            <div class="col-auto">
                <label class="form-label mb-1">Status</label>
                <select name="status" class="form-select" onchange="this.form.submit()">
                    <option value="">All</option>
                    <option value="1" @selected(request('status') === '1')>Published</option>
                    <option value="0" @selected(request('status') === '0')>Draft</option>
                </select>
            </div>
            <div class="col-auto">
                <a href="{{ route('admin.blog.index') }}" class="btn btn-light">Reset</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="60">#</th>
                        <th>Title</th>
                        <th width="160">Category</th>
                        <th width="180">Author</th>
                        <th width="130">Published</th>
                        <th width="130">Flags</th>
                        <th width="120" class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($posts as $post)
                    <tr>
                        <td>{{ $posts->firstItem() + $loop->index }}</td>
                        <td class="fw-bold">
                            {{ $post->title }}
                            <div class="text-muted small"><code>/blog/{{ $post->slug }}</code></div>
                        </td>
                        <td>{{ $post->category->name ?? '—' }}</td>
                        <td>
                            {{ $post->author_name }}
                            @if($post->author_role)
                                <div class="text-muted small">{{ $post->author_role }}</div>
                            @endif
                        </td>
                        <td>{{ optional($post->published_at)->format('M j, Y') ?? '—' }}</td>
                        <td>
                            @if($post->is_featured)
                                <span class="badge bg-warning text-dark">Featured</span>
                            @endif
                            @if($post->status)
                                <span class="badge bg-success">Published</span>
                            @else
                                <span class="badge bg-secondary">Draft</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <a href="{{ route('admin.blog.edit', $post) }}" class="btn btn-sm btn-info me-1">
                                <i class="fa-solid fa-pen"></i>
                            </a>
                            <form action="{{ route('admin.blog.destroy', $post) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this post?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger"><i class="fa-solid fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">No blog posts found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $posts->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
@endsection
