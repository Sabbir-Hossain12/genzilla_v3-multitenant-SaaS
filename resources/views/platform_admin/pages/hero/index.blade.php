@extends('platform_admin.layout.master')

@section('contents')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0 font-size-18">Platform Hero Section</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard.index') }}">Admin</a></li>
                    <li class="breadcrumb-item active">Hero Section</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<form method="POST" action="{{ route('admin.hero.update') }}">
    @csrf
    @method('PUT')

    <div class="card">
        <div class="card-header bg-transparent border-bottom">
            <h5 class="card-title mb-0"><i class="fa-solid fa-heading me-2 text-primary"></i>Manage Hero Banner Content</h5>
        </div>
        <div class="card-body p-4">
            <div class="mb-3">
                <label for="title" class="form-label font-weight-bold">Hero Title</label>
                <input type="text" class="form-control" id="title" name="title" value="{{ old('title', $hero->title) }}" required placeholder="e.g. Build, Launch, & Scale Your Global Store">
            </div>

            <div class="mb-3">
                <label for="ai_product_desc" class="form-label font-weight-bold">AI Product Badge / Subheading</label>
                <textarea class="form-control" id="ai_product_desc" name="ai_product_desc" rows="2" placeholder="e.g. Generate high-converting storefronts instantly using AI.">{{ old('ai_product_desc', $hero->ai_product_desc) }}</textarea>
            </div>

            <div class="mb-3">
                <label for="short_desc" class="form-label font-weight-bold">Short Description</label>
                <textarea class="form-control" id="short_desc" name="short_desc" rows="4">{{ old('short_desc', $hero->short_desc) }}</textarea>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="btn_1_text" class="form-label font-weight-bold">Primary Button Text</label>
                    <input type="text" class="form-control" id="btn_1_text" name="btn_1_text" value="{{ old('btn_1_text', $hero->btn_1_text) }}" placeholder="Start Free Trial">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="btn_1_link" class="form-label font-weight-bold">Primary Button Link</label>
                    <input type="text" class="form-control" id="btn_1_link" name="btn_1_link" value="{{ old('btn_1_link', $hero->btn_1_link) }}" placeholder="/register">
                </div>

                <div class="col-md-6 mb-3">
                    <label for="btn_2_text" class="form-label font-weight-bold">Secondary Button Text</label>
                    <input type="text" class="form-control" id="btn_2_text" name="btn_2_text" value="{{ old('btn_2_text', $hero->btn_2_text) }}" placeholder="Explore Demos">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="btn_2_link" class="form-label font-weight-bold">Secondary Button Link</label>
                    <input type="text" class="form-control" id="btn_2_link" name="btn_2_link" value="{{ old('btn_2_link', $hero->btn_2_link) }}" placeholder="#demos">
                </div>
            </div>
        </div>

        <div class="card-footer bg-light text-end p-3">
            <button type="submit" class="btn btn-primary px-4"><i class="fa-solid fa-floppy-disk me-1"></i> Save Hero Changes</button>
        </div>
    </div>
</form>
@endsection
