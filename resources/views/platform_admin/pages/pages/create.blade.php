@extends('platform_admin.layout.master')

@section('contents')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0 font-size-18">Create Custom Page</h4>
            <a href="{{ route('admin.pages.index') }}" class="btn btn-secondary"><i class="fa-solid fa-arrow-left me-1"></i> Back to Pages</a>
        </div>
    </div>
</div>

<form method="POST" action="{{ route('admin.pages.store') }}">
    @csrf
    <div class="card">
        <div class="card-body p-4">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label font-weight-bold">Page Title</label>
                    <input type="text" name="title" class="form-control" required placeholder="Terms of Service">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label font-weight-bold">Page Slug</label>
                    <input type="text" name="slug" class="form-control" placeholder="terms-of-service">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label font-weight-bold">Meta Title</label>
                    <input type="text" name="meta_title" class="form-control">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label font-weight-bold">Status</label>
                    <select name="status" class="form-select">
                        <option value="1" selected>Active</option>
                        <option value="0">Draft</option>
                    </select>
                </div>
                <div class="col-12 mb-3">
                    <label class="form-label font-weight-bold">Meta Description</label>
                    <textarea name="meta_description" class="form-control" rows="2"></textarea>
                </div>
                <div class="col-12 mb-3">
                    <label class="form-label font-weight-bold">Page Content (HTML / Text)</label>
                    <textarea name="content" class="form-control" rows="12" required></textarea>
                </div>
            </div>
        </div>
        <div class="card-footer bg-light text-end p-3">
            <button type="submit" class="btn btn-primary px-4"><i class="fa-solid fa-floppy-disk me-1"></i> Save Page</button>
        </div>
    </div>
</form>
@endsection
