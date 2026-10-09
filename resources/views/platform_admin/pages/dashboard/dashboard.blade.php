@extends('platform_admin.layout.master')

@push('backendCss')
    <style>
        .card-body thead {
            background: #EEF2F7;
        }
    </style>
@endpush

@section('contents')

    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Dashboard Overview</h4>
            </div>
        </div>
    </div>

    {{-- Overall Metrics --}}
    <div class="row">
        <div class="col-xl-3 col-md-6">
            <div class="card card-h-100 shadow-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-4 text-center rounded text-primary">
                            <i class="fas fa-shopping-cart h2 mb-0"></i>
                        </div>
                        <div class="col-8">
                            <span class="text-muted mb-2 lh-1 d-block text-truncate">Orders</span>
                            <h4 class="mb-0">0</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card card-h-100 shadow-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-4 text-center rounded text-success">
                            <i class="fas fa-box h2 mb-0"></i>
                        </div>
                        <div class="col-8">
                            <span class="text-muted mb-2 lh-1 d-block text-truncate">Products</span>
                            <h4 class="mb-0">0</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card card-h-100 shadow-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-4 text-center rounded text-info">
                            <i class="fas fa-tags h2 mb-0"></i>
                        </div>
                        <div class="col-8">
                            <span class="text-muted mb-2 lh-1 d-block text-truncate">Categories</span>
                            <h4 class="mb-0">0</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card card-h-100 shadow-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-4 text-center rounded text-warning">
                            <i class="fas fa-wallet h2 mb-0"></i>
                        </div>
                        <div class="col-8">
                            <span class="text-muted mb-2 lh-1 d-block text-truncate">Revenue</span>
                            <h4 class="mb-0">$0.00</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
