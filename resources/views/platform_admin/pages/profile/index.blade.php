@extends('platform_admin.layout.master')

@section('contents')
    @php
        $initials = collect(explode(' ', $user->name ?? ''))
            ->filter()
            ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
            ->take(2)
            ->implode('');
        $roles = $user->getRoleNames();
    @endphp

    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">My Profile</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard.index') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Profile</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body text-center">
                    <div class="d-flex justify-content-center mb-3">
                        @if($user->img_url)
                            <img id="avatarPreview" src="{{ $user->img_url }}" alt="{{ $user->name }}"
                                 class="rounded-circle object-fit-cover"
                                 style="width: 96px; height: 96px; object-fit: cover;">
                        @else
                            <img id="avatarPreview" src="" alt="{{ $user->name }}" class="rounded-circle"
                                 style="width: 96px; height: 96px; object-fit: cover; display: none;">
                            <span id="avatarFallback"
                                  class="avatar-title rounded-circle bg-primary font-size-36"
                                  style="width: 96px; height: 96px; line-height: 96px;">
                                {{ $initials ?: 'A' }}
                            </span>
                        @endif
                    </div>
                    <h5 class="mb-1">{{ $user->name }}</h5>
                    <p class="text-muted mb-2">{{ $user->email }}</p>

                    @forelse($roles as $role)
                        <span class="badge bg-success me-1">{{ $role }}</span>
                    @empty
                        <span class="badge bg-secondary">No role</span>
                    @endforelse

                    <hr>
                    <div class="text-start small">
                        <p class="mb-1 d-flex justify-content-between">
                            <span class="text-muted">Status</span>
                            <span>{!! $user->status == 1 ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>' !!}</span>
                        </p>
                        <p class="mb-0 d-flex justify-content-between">
                            <span class="text-muted">Member since</span>
                            <span>{{ optional($user->created_at)->format('d M Y') }}</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Profile Information</h4>
                </div>
                <div class="card-body p-4">
                    <form method="post" action="{{ route('admin.profile.update') }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label for="img" class="form-label">Profile Image</label>
                            <input type="file" class="form-control @error('img') is-invalid @enderror"
                                   id="img" name="img" accept="image/*"
                                   oninput="avatarPreview.src=window.URL.createObjectURL(this.files[0]); avatarPreview.style.display='block'; var f=document.getElementById('avatarFallback'); if(f){f.style.display='none';}">
                            @error('img')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <small class="text-muted">JPG, PNG, GIF or WEBP. Max 2MB.</small>
                        </div>
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   id="name" name="name" value="{{ old('name', $user->name) }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                   id="email" name="email" value="{{ old('email', $user->email) }}" required>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa-solid fa-floppy-disk me-1"></i> Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Update Password</h4>
                </div>
                <div class="card-body p-4">
                    <form method="post" action="{{ route('admin.profile.password') }}">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Current Password</label>
                            <input type="password" class="form-control @error('current_password') is-invalid @enderror"
                                   id="current_password" name="current_password" autocomplete="current-password" required>
                            @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">New Password</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror"
                                   id="password" name="password" autocomplete="new-password" required>
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" id="password_confirmation"
                                   name="password_confirmation" autocomplete="new-password" required>
                        </div>
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa-solid fa-key me-1"></i> Update Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
