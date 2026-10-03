@extends('platform_admin.layout.auth')

@section('contents')
    <div class="auth-page bg-gradient bg-info">
        <div class="container-fluid p-0">
            <div class="row g-0 justify-content-center ">
                <div class="col-xxl-4 col-lg-4 col-md-5 shadow-sm bg-light" style="background:#FFF3C7;">
                    <div class="auth-full-page-content d-flex p-sm-5 p-4">
                        <div class="w-100">
                            <div class="d-flex flex-column h-100">
                                <div class="mb-4 mb-md-3 text-center">
                                    <a href="" class="d-block auth-logo">
                                        <img src=" {{asset('backend')}}/assets/images/logo-sm.svg" alt=""
                                             height="28"> <span class="logo-txt">Eco Bazar</span>
                                    </a>
                                </div>
                                <div class="auth-content my-auto">
                                    <div class="text-center">
                                        <h5 class="mb-0">Welcome Back !</h5>
                                        <p class="text-muted mt-2">Sign in to continue to Eco Bazar.</p>
                                    </div>
                                    <form class="mt-4 pt-2" method="POST" action="{{ route('admin.login.store')}}">
                                        @csrf
                                        <div class="mb-3">
                                            <label for="email" class="form-label">Email</label>
                                            <input type="email" name="email" class="form-control" id="email"
                                                   placeholder="Enter username" value="admin@admin.com">

                                        </div>
                                        <div class="mb-3">
                                            <div class="d-flex align-items-start">
                                                <div class="flex-grow-1">
                                                    <label class="form-label">Password</label>
                                                </div>
                                            </div>

                                            <div class="input-group auth-pass-inputgroup">
                                                <input type="password" name="password" class="form-control"
                                                       placeholder="Enter password"
                                                       aria-label="Password" aria-describedby="password-addon"
                                                       value="password" autocomplete="true">

                                                <button class="btn btn-info shadow-none ms-0" type="button"
                                                        id="password-addon"><i class="mdi mdi-eye-outline"></i>
                                                </button>
                                            </div>
                                            @if ($errors->has('email'))
                                                <div class="mt-2">
                                                    @foreach ($errors->get('email') as $error)
                                                        <span class="text-danger">{{ $error }}</span>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                        <div class="row mb-4">
                                            <div class="col">
                                                <div class="form-check">
                                                    <input class="form-check-input" name="remember" type="checkbox"
                                                           id="remember-check">
                                                    <label class="form-check-label" for="remember-check">
                                                        Remember me
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <button class="btn btn-primary w-100 waves-effect waves-light"
                                                    type="submit">Log
                                                In
                                            </button>
                                        </div>
                                    </form>
                                </div>
                                <div class="mt-4 mt-md-5 text-center">
                                    <p class="mb-0">©
                                        <script>document.write(new Date().getFullYear())</script>
                                        Minia . Crafted with <i class="mdi mdi-heart text-danger"></i> by Themesbrand
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- end auth full page content -->
                </div>
                <!-- end col -->
            </div>
            <!-- end row -->
        </div>
        <!-- end container fluid -->
    </div>
@endsection

@push('backendJs')
    <!-- password addon init -->
    <script src=" {{ asset('backend') }}/assets/js/pages/pass-addon.init.js"></script>
@endpush
