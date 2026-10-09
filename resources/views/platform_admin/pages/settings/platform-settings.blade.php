@extends('platform_admin.layout.master')

@section('contents')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0 font-size-18">Platform Settings</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard.index') }}">Admin</a></li>
                    <li class="breadcrumb-item active">Platform Settings</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-header bg-transparent border-bottom">
            <ul class="nav nav-tabs card-header-tabs nav-tabs-custom" id="settingsTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button" role="tab" aria-controls="general" aria-selected="true">
                        <i class="fa-solid fa-gear me-1"></i> General Settings
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="seo-tab" data-bs-toggle="tab" data-bs-target="#seo" type="button" role="tab" aria-controls="seo" aria-selected="false">
                        <i class="fa-solid fa-magnifying-glass me-1"></i> SEO & Open Graph
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="social-tab" data-bs-toggle="tab" data-bs-target="#social" type="button" role="tab" aria-controls="social" aria-selected="false">
                        <i class="fa-solid fa-share-nodes me-1"></i> Social Links
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="scripts-tab" data-bs-toggle="tab" data-bs-target="#scripts" type="button" role="tab" aria-controls="scripts" aria-selected="false">
                        <i class="fa-solid fa-code me-1"></i> Analytics & Scripts
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="announcement-tab" data-bs-toggle="tab" data-bs-target="#announcement" type="button" role="tab" aria-controls="announcement" aria-selected="false">
                        <i class="fa-solid fa-bullhorn me-1"></i> Announcement Bar
                    </button>
                </li>
            </ul>
        </div>

        <div class="card-body p-4">
            <div class="tab-content" id="settingsTabsContent">

                <!-- 1. GENERAL SETTINGS -->
                <div class="tab-pane fade show active" id="general" role="tabpanel" aria-labelledby="general-tab">
                    <h5 class="mb-3 text-primary"><i class="fa-solid fa-building me-1"></i> Brand & System Information</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="platform_name" class="form-label font-weight-bold">Platform Name</label>
                            <input type="text" class="form-control" id="platform_name" name="platform_name" value="{{ old('platform_name', $setting->platform_name ?? 'MySaaS') }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="tagline" class="form-label font-weight-bold">Tagline</label>
                            <input type="text" class="form-control" id="tagline" name="tagline" value="{{ old('tagline', $setting->tagline) }}" placeholder="e.g. Next-Gen Store Builder">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="short_desc" class="form-label font-weight-bold">Short Description</label>
                        <textarea class="form-control" id="short_desc" name="short_desc" rows="3">{{ old('short_desc', $setting->short_desc) }}</textarea>
                    </div>

                    <hr class="my-4">
                    <h5 class="mb-3 text-primary"><i class="fa-solid fa-image me-1"></i> Media & Logos</h5>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="black_logo" class="form-label font-weight-bold">Dark / Black Logo</label>
                            <input class="form-control" type="file" id="black_logo" name="black_logo" accept="image/*" onchange="previewImg(this, 'blackLogoPrev')">
                            <div class="mt-2">
                                <img id="blackLogoPrev" src="{{ $setting->black_logo ? asset('storage/' . $setting->black_logo) : asset('backend/assets/images/logo-dark.png') }}" class="img-thumbnail" style="max-height: 60px;" alt="Dark Logo">
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="light_logo" class="form-label font-weight-bold">Light Logo</label>
                            <input class="form-control" type="file" id="light_logo" name="light_logo" accept="image/*" onchange="previewImg(this, 'lightLogoPrev')">
                            <div class="mt-2 p-2 bg-dark rounded">
                                <img id="lightLogoPrev" src="{{ $setting->light_logo ? asset('storage/' . $setting->light_logo) : asset('backend/assets/images/logo-light.png') }}" class="img-thumbnail" style="max-height: 60px;" alt="Light Logo">
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="favicon" class="form-label font-weight-bold">Favicon</label>
                            <input class="form-control" type="file" id="favicon" name="favicon" accept="image/*" onchange="previewImg(this, 'faviconPrev')">
                            <div class="mt-2">
                                <img id="faviconPrev" src="{{ $setting->favicon ? asset('storage/' . $setting->favicon) : asset('backend/assets/images/favicon.ico') }}" class="img-thumbnail" style="max-height: 40px;" alt="Favicon">
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">
                    <h5 class="mb-3 text-primary"><i class="fa-solid fa-envelope me-1"></i> Contact & Regional Settings</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label font-weight-bold">Platform Contact Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $setting->email) }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="phone_1" class="form-label font-weight-bold">Phone Number</label>
                            <input type="text" class="form-control" id="phone_1" name="phone_1" value="{{ old('phone_1', $setting->phone_1) }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="working_hours" class="form-label font-weight-bold">Working Hours</label>
                            <input type="text" class="form-control" id="working_hours" name="working_hours" value="{{ old('working_hours', $setting->working_hours) }}" placeholder="Mon - Fri: 9am - 6pm">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="default_currency" class="form-label font-weight-bold">Default Currency</label>
                            <input type="text" class="form-control" id="default_currency" name="default_currency" value="{{ old('default_currency', $setting->default_currency ?? 'USD') }}">
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="company_address" class="form-label font-weight-bold">Company Address</label>
                            <textarea class="form-control" id="company_address" name="company_address" rows="2">{{ old('company_address', $setting->company_address) }}</textarea>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="copyright_text" class="form-label font-weight-bold">Copyright Text</label>
                            <input type="text" class="form-control" id="copyright_text" name="copyright_text" value="{{ old('copyright_text', $setting->copyright_text) }}">
                        </div>
                    </div>
                </div>

                <!-- 2. SEO & OPEN GRAPH -->
                <div class="tab-pane fade" id="seo" role="tabpanel" aria-labelledby="seo-tab">
                    <h5 class="mb-3 text-primary"><i class="fa-solid fa-search me-1"></i> Search Engine Optimization</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="meta_title" class="form-label font-weight-bold">Meta Title</label>
                            <input type="text" class="form-control" id="meta_title" name="meta_title" value="{{ old('meta_title', $setting->meta_title) }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="meta_robots" class="form-label font-weight-bold">Meta Robots</label>
                            <input type="text" class="form-control" id="meta_robots" name="meta_robots" value="{{ old('meta_robots', $setting->meta_robots ?? 'index, follow') }}">
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="meta_description" class="form-label font-weight-bold">Meta Description</label>
                            <textarea class="form-control" id="meta_description" name="meta_description" rows="3">{{ old('meta_description', $setting->meta_description) }}</textarea>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="meta_keywords" class="form-label font-weight-bold">Meta Keywords</label>
                            <textarea class="form-control" id="meta_keywords" name="meta_keywords" rows="2" placeholder="ecommerce, saas, store builder">{{ old('meta_keywords', $setting->meta_keywords) }}</textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="canonical_url" class="form-label font-weight-bold">Canonical URL</label>
                            <input type="url" class="form-control" id="canonical_url" name="canonical_url" value="{{ old('canonical_url', $setting->canonical_url) }}">
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="schema_markup" class="form-label font-weight-bold">Schema Markup (JSON-LD)</label>
                            <textarea class="form-control font-monospace" id="schema_markup" name="schema_markup" rows="4" placeholder="<script type='application/ld+json'>...</script>">{{ old('schema_markup', $setting->schema_markup) }}</textarea>
                        </div>
                    </div>

                    <hr class="my-4">
                    <h5 class="mb-3 text-primary"><i class="fa-solid fa-share-nodes me-1"></i> Open Graph (Social Sharing)</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="og_title" class="form-label font-weight-bold">OG Title</label>
                            <input type="text" class="form-control" id="og_title" name="og_title" value="{{ old('og_title', $setting->og_title) }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="og_image" class="form-label font-weight-bold">OG Image</label>
                            <input class="form-control" type="file" id="og_image" name="og_image" accept="image/*" onchange="previewImg(this, 'ogImgPrev')">
                            <div class="mt-2">
                                <img id="ogImgPrev" src="{{ $setting->og_image ? asset('storage/' . $setting->og_image) : '' }}" class="img-thumbnail" style="max-height: 80px; display: {{ $setting->og_image ? 'inline-block' : 'none' }};" alt="OG Image">
                            </div>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="og_description" class="form-label font-weight-bold">OG Description</label>
                            <textarea class="form-control" id="og_description" name="og_description" rows="3">{{ old('og_description', $setting->og_description) }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- 3. SOCIAL LINKS -->
                <div class="tab-pane fade" id="social" role="tabpanel" aria-labelledby="social-tab">
                    <h5 class="mb-3 text-primary"><i class="fa-solid fa-link me-1"></i> Social Media Profiles</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="fb_link" class="form-label font-weight-bold"><i class="fa-brands fa-facebook text-primary me-1"></i> Facebook Link</label>
                            <input type="url" class="form-control" id="fb_link" name="fb_link" value="{{ old('fb_link', $setting->fb_link) }}" placeholder="https://facebook.com/yourpage">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="x_link" class="form-label font-weight-bold"><i class="fa-brands fa-x-twitter me-1"></i> X (Twitter) Link</label>
                            <input type="url" class="form-control" id="x_link" name="x_link" value="{{ old('x_link', $setting->x_link) }}" placeholder="https://x.com/yourhandle">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="youtube_link" class="form-label font-weight-bold"><i class="fa-brands fa-youtube text-danger me-1"></i> YouTube Link</label>
                            <input type="url" class="form-control" id="youtube_link" name="youtube_link" value="{{ old('youtube_link', $setting->youtube_link) }}" placeholder="https://youtube.com/c/yourchannel">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="insta_link" class="form-label font-weight-bold"><i class="fa-brands fa-instagram text-warning me-1"></i> Instagram Link</label>
                            <input type="url" class="form-control" id="insta_link" name="insta_link" value="{{ old('insta_link', $setting->insta_link) }}" placeholder="https://instagram.com/yourprofile">
                        </div>
                    </div>
                </div>

                <!-- 4. ANALYTICS & SCRIPTS -->
                <div class="tab-pane fade" id="scripts" role="tabpanel" aria-labelledby="scripts-tab">
                    <h5 class="mb-3 text-primary"><i class="fa-solid fa-code me-1"></i> Tracking & Custom Scripts</h5>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="fb_pixel" class="form-label font-weight-bold">Facebook Pixel Code</label>
                            <textarea class="form-control font-monospace" id="fb_pixel" name="fb_pixel" rows="4" placeholder="<!-- Meta Pixel Code -->">{{ old('fb_pixel', $setting->fb_pixel) }}</textarea>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="google_analytics" class="form-label font-weight-bold">Google Analytics Script</label>
                            <textarea class="form-control font-monospace" id="google_analytics" name="google_analytics" rows="4" placeholder="<!-- Global site tag (gtag.js) -->">{{ old('google_analytics', $setting->google_analytics) }}</textarea>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="chatbox_script" class="form-label font-weight-bold">Live Chat / Support Widget Script</label>
                            <textarea class="form-control font-monospace" id="chatbox_script" name="chatbox_script" rows="4" placeholder="<!-- Live Chat Script -->">{{ old('chatbox_script', $setting->chatbox_script) }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- 5. ANNOUNCEMENT BAR -->
                <div class="tab-pane fade" id="announcement" role="tabpanel" aria-labelledby="announcement-tab">
                    <h5 class="mb-3 text-primary"><i class="fa-solid fa-bullhorn me-1"></i> Top Banner Announcement</h5>
                    <div class="mb-3">
                        <label for="announcement_bar_text" class="form-label font-weight-bold">Announcement Bar Text</label>
                        <textarea class="form-control" id="announcement_bar_text" name="announcement_bar_text" rows="3" placeholder="🎉 Special Offer: 20% off all annual plans! Use code GENZ20">{{ old('announcement_bar_text', $setting->announcement_bar_text) }}</textarea>
                        <small class="text-muted">This text will be displayed in the announcement bar across the landing pages.</small>
                    </div>
                </div>

            </div>
        </div>

        <div class="card-footer bg-light text-end p-3">
            <button type="submit" class="btn btn-primary px-4"><i class="fa-solid fa-floppy-disk me-1"></i> Save Platform Settings</button>
        </div>
    </div>
</form>

<script>
function previewImg(input, targetId) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function (e) {
            var el = document.getElementById(targetId);
            el.src = e.target.result;
            el.style.display = 'inline-block';
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endsection
