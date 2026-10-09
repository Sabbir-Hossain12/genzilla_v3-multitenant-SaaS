@extends('platform_admin.layout.master')

@section('contents')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0 font-size-18">Merchant Testimonials</h4>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createTestimonialModal">
                <i class="fa-solid fa-plus me-1"></i> Add Testimonial
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
                        <th>Merchant Image</th>
                        <th>Merchant Name</th>
                        <th>Title / Store</th>
                        <th>Rating</th>
                        <th>Testimonial Text</th>
                        <th width="100">Status</th>
                        <th width="120" class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($testimonials as $testi)
                    <tr>
                        <td><span class="badge bg-secondary">{{ $testi->sort_order }}</span></td>
                        <td class="text-center">
                            @if($testi->merchant_image)
                                <img src="{{ asset('storage/' . $testi->merchant_image) }}" class="rounded-circle" style="height: 40px; width: 40px; object-fit: cover;" alt="Avatar">
                            @else
                                <div class="avatar-sm rounded-circle bg-light d-inline-flex align-items-center justify-content-center fw-bold text-primary">
                                    {{ substr($testi->merchant_name, 0, 1) }}
                                </div>
                            @endif
                        </td>
                        <td class="fw-bold">{{ $testi->merchant_name }}</td>
                        <td>{{ $testi->merchant_title ?? '-' }}</td>
                        <td class="text-warning">
                            @for($i = 1; $i <= 5; $i++)
                                <i class="fa-{{ $i <= $testi->rating ? 'solid' : 'regular' }} fa-star"></i>
                            @endfor
                        </td>
                        <td>{{ Str::limit($testi->text, 90) }}</td>
                        <td>
                            @if($testi->status)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-danger">Inactive</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-info me-1" data-bs-toggle="modal" data-bs-target="#editTestimonialModal{{ $testi->id }}">
                                <i class="fa-solid fa-pen"></i>
                            </button>
                            <form action="{{ route('admin.testimonials.destroy', $testi->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this testimonial?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger"><i class="fa-solid fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>

                    <!-- Edit Modal -->
                    <div class="modal fade" id="editTestimonialModal{{ $testi->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <form method="POST" action="{{ route('admin.testimonials.update', $testi->id) }}" enctype="multipart/form-data" class="modal-content">
                                @csrf
                                @method('PUT')
                                <div class="modal-header">
                                    <h5 class="modal-title">Edit Testimonial</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Merchant Name</label>
                                            <input type="text" name="merchant_name" class="form-control" value="{{ $testi->merchant_name }}" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Merchant Title / Store Name</label>
                                            <input type="text" name="merchant_title" class="form-control" value="{{ $testi->merchant_title }}">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Rating (1-5 Stars)</label>
                                            <select name="rating" class="form-select">
                                                @for($r = 5; $r >= 1; $r--)
                                                    <option value="{{ $r }}" {{ $testi->rating == $r ? 'selected' : '' }}>{{ $r }} Stars</option>
                                                @endfor
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Merchant Photo</label>
                                            <input type="file" name="merchant_image" class="form-control">
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Testimonial Content</label>
                                        <textarea name="text" class="form-control" rows="3" required>{{ $testi->text }}</textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Sort Order</label>
                                        <input type="number" name="sort_order" class="form-control" value="{{ $testi->sort_order }}">
                                    </div>
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" name="status" id="testiStatusEdit{{ $testi->id }}" {{ $testi->status ? 'checked' : '' }}>
                                        <label class="form-check-label" for="testiStatusEdit{{ $testi->id }}">Active Status</label>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-primary">Update Testimonial</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">No testimonials found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Create Modal -->
<div class="modal fade" id="createTestimonialModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('admin.testimonials.store') }}" enctype="multipart/form-data" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Add Testimonial</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Merchant Name</label>
                        <input type="text" name="merchant_name" class="form-control" required placeholder="Jane Doe">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Merchant Title / Store Name</label>
                        <input type="text" name="merchant_title" class="form-control" placeholder="CEO, GlowApparel">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Rating (1-5 Stars)</label>
                        <select name="rating" class="form-select">
                            <option value="5" selected>5 Stars</option>
                            <option value="4">4 Stars</option>
                            <option value="3">3 Stars</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Merchant Photo</label>
                        <input type="file" name="merchant_image" class="form-control">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Testimonial Content</label>
                    <textarea name="text" class="form-control" rows="3" required></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Sort Order</label>
                    <input type="number" name="sort_order" class="form-control" value="0">
                </div>
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" name="status" id="testiStatusCreate" checked>
                    <label class="form-check-label" for="testiStatusCreate">Active Status</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">Save Testimonial</button>
            </div>
        </form>
    </div>
</div>
@endsection
