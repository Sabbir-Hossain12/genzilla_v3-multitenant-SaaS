@php
    $blogPost = $blogPost ?? null;
    $categories = $categories ?? collect();
@endphp

<div class="card">
    <div class="card-body p-4">
        <div class="row">
            <div class="col-md-8 mb-3">
                <label class="form-label font-weight-bold">Title</label>
                <input type="text" name="title" class="form-control" value="{{ old('title', $blogPost->title ?? '') }}" required>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label font-weight-bold">Slug</label>
                <input type="text" name="slug" class="form-control" value="{{ old('slug', $blogPost->slug ?? '') }}" placeholder="auto-generated from title">
            </div>

            <div class="col-md-4 mb-3">
                <label class="form-label font-weight-bold">Category</label>
                <select name="blog_category_id" class="form-select">
                    <option value="">&mdash; None &mdash;</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @selected(old('blog_category_id', $blogPost->blog_category_id ?? '') == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label font-weight-bold">Read Time (minutes)</label>
                <input type="number" name="read_time" class="form-control" min="1" max="120" value="{{ old('read_time', $blogPost->read_time ?? 5) }}">
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label font-weight-bold">Publish Date</label>
                <input type="date" name="published_at" class="form-control" value="{{ old('published_at', optional($blogPost->published_at ?? null)->format('Y-m-d')) }}">
            </div>

            <div class="col-12 mb-3">
                <label class="form-label font-weight-bold">Excerpt</label>
                <textarea name="excerpt" class="form-control" rows="2" required>{{ old('excerpt', $blogPost->excerpt ?? '') }}</textarea>
            </div>

            <div class="col-md-4 mb-3">
                <label class="form-label font-weight-bold">Author Name</label>
                <input type="text" name="author_name" class="form-control" value="{{ old('author_name', $blogPost->author_name ?? '') }}" required>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label font-weight-bold">Author Role</label>
                <input type="text" name="author_role" class="form-control" value="{{ old('author_role', $blogPost->author_role ?? '') }}">
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label font-weight-bold">Author Initials</label>
                <input type="text" name="author_initials" class="form-control" maxlength="5" value="{{ old('author_initials', $blogPost->author_initials ?? '') }}">
            </div>

            <div class="col-md-4 mb-3">
                <label class="form-label font-weight-bold">Cover Gradient Class</label>
                <input type="text" name="cover_class" class="form-control" value="{{ old('cover_class', $blogPost->cover_class ?? '') }}" placeholder="bg-gradient-to-br from-indigo-50 to-indigo-100">
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label font-weight-bold">Emoji</label>
                <input type="text" name="emoji" class="form-control" maxlength="16" value="{{ old('emoji', $blogPost->emoji ?? '') }}" placeholder="&#128640;">
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label font-weight-bold">Sort Order</label>
                <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $blogPost->sort_order ?? 0) }}">
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="status" id="blogStatus" value="1" @checked(old('status', $blogPost->status ?? 1))>
                    <label class="form-check-label" for="blogStatus">Published</label>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="is_featured" id="blogFeatured" value="1" @checked(old('is_featured', $blogPost->is_featured ?? false))>
                    <label class="form-check-label" for="blogFeatured">Featured post</label>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header bg-light">
        <h5 class="card-title mb-0">Article Body</h5>
    </div>
    <div class="card-body p-4">
        <label class="form-label font-weight-bold">Long Description</label>
        <textarea name="long_desc" id="blog-long-desc" class="form-control" rows="12" placeholder="Write the full article here...">{{ old('long_desc', $blogPost->long_desc ?? '') }}</textarea>
        <div class="form-text">Supports headings, bold, lists, and quotes. Stored as HTML and rendered on the public blog.</div>
    </div>
</div>

<div class="card">
    <div class="card-header bg-light">
        <h5 class="card-title mb-0">SEO</h5>
    </div>
    <div class="card-body p-4">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label font-weight-bold">Meta Title</label>
                <input type="text" name="meta_title" class="form-control" value="{{ old('meta_title', $blogPost->meta_title ?? '') }}">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label font-weight-bold">Meta Keywords</label>
                <input type="text" name="meta_keywords" class="form-control" value="{{ old('meta_keywords', $blogPost->meta_keywords ?? '') }}">
            </div>
            <div class="col-12 mb-3">
                <label class="form-label font-weight-bold">Meta Description</label>
                <textarea name="meta_description" class="form-control" rows="2">{{ old('meta_description', $blogPost->meta_description ?? '') }}</textarea>
            </div>
        </div>
    </div>
    <div class="card-footer bg-light text-end p-3">
        <button type="submit" class="btn btn-primary px-4">
            <i class="fa-solid fa-floppy-disk me-1"></i> Save Post
        </button>
    </div>
</div>

@push('backendJs')
    @include('platform_admin.pages.blog._scripts')
@endpush
