@extends('platform_admin.layout.master')

@push('backendCss')
    <link href="{{asset('backend')}}/assets/libs/admin-resources/jquery.vectormap/jquery-jvectormap-1.2.2.css"
          rel="stylesheet" type="text/css">

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
                <h4 class="mb-sm-0 font-size-18">Overall</h4>

            </div>
        </div>

    </div>

    {{--  Overall Chart --}}

    <div class="row">
        <div class="col-xl-3 col-md-6">
            <!-- card -->
            <div class="card card-h-100 shadow">
                <!-- card body -->
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-4 text-center  rounded">
                            <i class="fas fa-cart-plus h2"></i>
                        </div>

                        <div class="col-8">
                            <span class="text-muted mb-3 lh-1 d-block text-truncate">Orders</span>
                            <h4 class="mb-3">
                                <span class="">0</span>
                            </h4>

                        </div>


                    </div>

                </div><!-- end card body -->
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <!-- card -->
            <div class="card card-h-100 shadow">
                <!-- card body -->
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-4 text-center  rounded">
                            <i class="fas fa-cart-plus h2"></i>
                        </div>

                        <div class="col-8">
                            <span class="text-muted mb-3 lh-1 d-block text-truncate">Products</span>
                            <h4 class="mb-3">
                                <span class="">0</span>
                            </h4>

                        </div>


                    </div>

                </div><!-- end card body -->
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <!-- card -->
            <div class="card card-h-100 shadow">
                <!-- card body -->
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-4 text-center  rounded">
                            <i class="fas fa-cart-plus h2"></i>
                        </div>

                        <div class="col-8">
                            <span class="text-muted mb-3 lh-1 d-block text-truncate">Categories</span>
                            <h4 class="mb-3">
                                <span class="">0</span>
                            </h4>

                        </div>


                    </div>

                </div><!-- end card body -->
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <!-- card -->
            <div class="card card-h-100 shadow">
                <!-- card body -->
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-4 text-center  rounded">
                            <i class="fas fa-cart-plus h2"></i>
                        </div>

                        <div class="col-8">
                            <span class="text-muted mb-3 lh-1 d-block text-truncate">Purchase</span>
                            <h4 class="mb-3">
                                ৳<span class="">0</span>
                            </h4>

                        </div>


                    </div>

                </div><!-- end card body -->
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <!-- card -->
            <div class="card card-h-100 shadow">
                <!-- card body -->
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-4 text-center  rounded">
                            <i class="fas fa-cart-plus h2"></i>
                        </div>

                        <div class="col-8">
                            <span class="text-muted mb-3 lh-1 d-block text-truncate">Sales</span>
                            <h4 class="mb-3">
                                ৳<span class="">0</span>
                            </h4>

                        </div>


                    </div>

                </div><!-- end card body -->
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <!-- card -->
            <div class="card card-h-100 shadow">
                <!-- card body -->
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-4 text-center  rounded">
                            <i class="fas fa-cart-plus h2"></i>
                        </div>

                        <div class="col-8">
                            <span class="text-muted mb-3 lh-1 d-block text-truncate">Profit</span>
                            <h4 class="mb-3">
                                ৳<span class="">0</span>
                            </h4>

                        </div>


                    </div>

                </div><!-- end card body -->
            </div>
        </div>

    </div>
@endsection

@push('backendJs')
    <script src="{{asset('backend')}}/assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="{{asset('backend')}}/assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>
    {{--    <script src=" {{asset('backend')}}/assets/libs/apexcharts/apexcharts.min.js"></script>--}}
    <script
        src=" {{asset('backend')}}/assets/libs/admin-resources/jquery.vectormap/jquery-jvectormap-1.2.2.min.js"></script>
    <script
        src="{{asset('backend')}}/assets/libs/admin-resources/jquery.vectormap/maps/jquery-jvectormap-world-mill-en.js"></script>
    {{--    <script src=" {{asset('backend')}}/assets/js/pages/dashboard.init.js"></script>--}}

    <script>

    </script>
@endpush
