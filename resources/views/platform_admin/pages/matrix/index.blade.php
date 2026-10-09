@extends('platform_admin.layout.master')

@section('contents')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0 font-size-18">Platform Metrics & Social Proof</h4>
        </div>
    </div>
</div>

<form method="POST" action="{{ route('admin.matrix.update') }}">
    @csrf
    @method('PUT')

    <div class="card">
        <div class="card-header bg-transparent border-bottom">
            <h5 class="card-title mb-0"><i class="fa-solid fa-chart-simple me-2 text-primary"></i>Landing Page Counter Stats</h5>
        </div>
        <div class="card-body p-4">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="active_merchant" class="form-label font-weight-bold">Active Merchants Count</label>
                    <input type="number" class="form-control" id="active_merchant" name="active_merchant" value="{{ old('active_merchant', $matrix->active_merchant) }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="gmv_proccessed" class="form-label font-weight-bold">Total GMV Processed ($)</label>
                    <input type="number" step="0.01" class="form-control" id="gmv_proccessed" name="gmv_proccessed" value="{{ old('gmv_proccessed', $matrix->gmv_proccessed) }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="countries_served" class="form-label font-weight-bold">Countries Served</label>
                    <input type="number" class="form-control" id="countries_served" name="countries_served" value="{{ old('countries_served', $matrix->countries_served) }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="platform_uptime" class="form-label font-weight-bold">Platform Uptime Percentage</label>
                    <input type="text" class="form-control" id="platform_uptime" name="platform_uptime" value="{{ old('platform_uptime', $matrix->platform_uptime) }}" required placeholder="99.99%">
                </div>
            </div>
        </div>
        <div class="card-footer bg-light text-end p-3">
            <button type="submit" class="btn btn-primary px-4"><i class="fa-solid fa-floppy-disk me-1"></i> Update Metrics</button>
        </div>
    </div>
</form>
@endsection
