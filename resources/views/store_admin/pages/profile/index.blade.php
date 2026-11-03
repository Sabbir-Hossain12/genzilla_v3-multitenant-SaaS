@extends('store_admin.layout.master')

@push('title')
    My Profile & Account Settings
@endpush

@section('contents')
@php
    $initials = collect(explode(' ', $user->name ?? ''))
        ->filter()
        ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
        ->take(2)
        ->implode('');
@endphp

<div class="space-y-6">

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-primary/10 text-primary dark:bg-primary/20">
                    <i class="fa-solid fa-user-gear text-base"></i>
                </span>
                <h1 class="text-xl md:text-2xl font-bold text-ink dark:text-white">Admin Profile & Settings</h1>
            </div>
            <p class="text-xs md:text-sm text-slate-500 dark:text-slate-400 mt-1">
                Manage your personal credentials, pharmacy staff account, and security settings.
            </p>
        </div>

        <nav class="flex" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-2 text-xs text-slate-500 dark:text-slate-400">
                <li class="inline-flex items-center">
                    <a href="{{ route('store.admin.dashboard') }}" class="hover:text-primary transition-colors">Dashboard</a>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="w-3.5 h-3.5 text-slate-400 mx-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                        <span class="text-slate-700 dark:text-slate-200 font-medium">My Profile</span>
                    </div>
                </li>
            </ol>
        </nav>
    </div>

    <!-- Flash Alerts -->
    @if(session('success'))
        <div class="flex items-center gap-3 p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-300 text-xs sm:text-sm shadow-xs">
            <i class="fa-solid fa-circle-check text-base text-emerald-600 dark:text-emerald-400 shrink-0"></i>
            <span class="font-medium">{{ session('success') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-700 dark:text-rose-300 text-xs sm:text-sm shadow-xs">
            <div class="flex items-center gap-2 mb-2 font-semibold">
                <i class="fa-solid fa-triangle-exclamation text-rose-600"></i>
                <span>Please correct the errors below:</span>
            </div>
            <ul class="list-disc list-inside space-y-1 pl-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Left Column: User Profile & Store Meta Card -->
        <div class="lg:col-span-4 space-y-6">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-xs">
                <div class="flex flex-col items-center text-center">

                    <!-- Avatar Preview -->
                    <div class="relative mb-4">
                        @if($user->img_url)
                            <img id="avatarPreview" src="{{ $user->img_url }}" alt="{{ $user->name }}"
                                 class="w-24 h-24 rounded-2xl object-cover ring-4 ring-primary/10 shadow-sm">
                        @else
                            <img id="avatarPreview" src="" alt="{{ $user->name }}"
                                 class="w-24 h-24 rounded-2xl object-cover ring-4 ring-primary/10 shadow-sm hidden">
                            <span id="avatarFallback"
                                  class="flex items-center justify-center w-24 h-24 rounded-2xl bg-gradient-to-tr from-primary to-blue-600 text-white font-black text-2xl ring-4 ring-primary/10 shadow-sm">
                                {{ $initials ?: 'AD' }}
                            </span>
                        @endif
                        <span class="absolute -bottom-1 -right-1 w-5 h-5 rounded-full bg-emerald-500 border-2 border-white dark:border-slate-900" title="Active Account"></span>
                    </div>

                    <h2 class="text-base font-bold text-ink dark:text-white">{{ $user->name }}</h2>
                    <p class="text-xs text-slate-400 mt-0.5">{{ $user->email }}</p>

                    <!-- Role Badges -->
                    <div class="flex flex-wrap justify-center gap-1.5 mt-3 mb-5">
                        @forelse($roles as $role)
                            <span class="px-2.5 py-1 text-[11px] font-bold bg-primary/10 text-primary dark:bg-primary/20 rounded-lg">
                                <i class="fa-solid fa-shield-halved text-[10px] me-1"></i>{{ $role }}
                            </span>
                        @empty
                            <span class="px-2.5 py-1 text-[11px] font-medium bg-slate-100 dark:bg-slate-800 text-slate-500 rounded-lg">Staff Member</span>
                        @endforelse
                    </div>

                    <!-- Detail Metrics List -->
                    <div class="w-full border-t border-slate-100 dark:border-slate-800 pt-4 space-y-2.5 text-xs text-left">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400">Account Status</span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                Active
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400">Permissions Level</span>
                            <span class="font-semibold text-ink dark:text-white">{{ $permissionsCount }} assigned</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400">Pharmacy Store</span>
                            <span class="font-semibold text-primary truncate max-w-[160px]">{{ $tenant->store_name ?? 'Store Admin' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400">Store Domain</span>
                            <span class="font-medium text-slate-600 dark:text-slate-300 truncate max-w-[160px]">{{ $tenant->subdomain ?? 'N/A' }}.{{ config('app.base_domain') }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400">Member Since</span>
                            <span class="font-medium text-slate-600 dark:text-slate-300">{{ optional($user->created_at)->format('d M Y') }}</span>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Quick Security Tip Card -->
            <div class="bg-gradient-to-br from-primary/5 to-blue-500/5 dark:from-primary/10 dark:to-blue-500/10 border border-primary/20 rounded-2xl p-4.5 text-xs">
                <div class="flex items-center gap-2 text-primary font-bold mb-1.5">
                    <i class="fa-solid fa-lock"></i>
                    <span>Account Security Tip</span>
                </div>
                <p class="text-slate-500 dark:text-slate-400 leading-relaxed text-[11px]">
                    Use a strong passphrase with at least 8 characters including numbers and special symbols. Never share your merchant login credentials with external third parties.
                </p>
            </div>
        </div>

        <!-- Right Column: Edit Information & Security -->
        <div class="lg:col-span-8 space-y-6">

            <!-- 1. Profile Information Form -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xs overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-bold text-ink dark:text-white flex items-center gap-2">
                            <i class="fa-solid fa-address-card text-primary text-xs"></i>
                            General Information
                        </h2>
                        <p class="text-xs text-slate-400 mt-0.5">Update your display name, email address, and avatar image.</p>
                    </div>
                </div>

                <div class="p-5 sm:p-6">
                    <form method="POST" action="{{ route('store.admin.profile.update') }}" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        @method('PUT')

                        <!-- Avatar Picker -->
                        <div>
                            <label for="img" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1.5">
                                Change Profile Photo
                            </label>
                            <input type="file"
                                   id="img"
                                   name="img"
                                   accept="image/*"
                                   class="w-full text-xs text-slate-500 dark:text-slate-400 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20 cursor-pointer border border-slate-200 dark:border-slate-700 rounded-xl p-1 bg-white dark:bg-slate-800 @error('img') border-rose-500 @enderror"
                                   onchange="previewUploadedAvatar(this)">
                            @error('img')
                                <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-[11px] text-slate-400">Supported formats: JPG, PNG, GIF, WEBP. Maximum file size: 2MB.</p>
                        </div>

                        <!-- Name & Email Grid -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="name" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1.5">
                                    Full Name <span class="text-rose-500">*</span>
                                </label>
                                <input type="text"
                                       id="name"
                                       name="name"
                                       value="{{ old('name', $user->name) }}"
                                       required
                                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs sm:text-sm text-ink dark:text-white focus:outline-hidden focus:ring-2 focus:ring-primary/20 focus:border-primary transition-colors @error('name') border-rose-500 @enderror">
                                @error('name')
                                    <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1.5">
                                    Email Address <span class="text-rose-500">*</span>
                                </label>
                                <input type="email"
                                       id="email"
                                       name="email"
                                       value="{{ old('email', $user->email) }}"
                                       required
                                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs sm:text-sm text-ink dark:text-white focus:outline-hidden focus:ring-2 focus:ring-primary/20 focus:border-primary transition-colors @error('email') border-rose-500 @enderror">
                                @error('email')
                                    <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="flex justify-end pt-3">
                            <button type="submit"
                                    class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-primary hover:bg-primarydark text-white text-xs font-bold shadow-sm shadow-primary/25 transition-colors cursor-pointer">
                                <i class="fa-solid fa-floppy-disk text-xs"></i>
                                <span>Save Profile</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- 2. Security & Password Update Form -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xs overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-bold text-ink dark:text-white flex items-center gap-2">
                            <i class="fa-solid fa-key text-amber-500 text-xs"></i>
                            Security & Password
                        </h2>
                        <p class="text-xs text-slate-400 mt-0.5">Ensure your account is protected with a secure and unique password.</p>
                    </div>
                </div>

                <div class="p-5 sm:p-6">
                    <form method="POST" action="{{ route('store.admin.profile.password') }}" class="space-y-4">
                        @csrf
                        @method('PUT')

                        <div>
                            <label for="current_password" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1.5">
                                Current Password <span class="text-rose-500">*</span>
                            </label>
                            <input type="password"
                                   id="current_password"
                                   name="current_password"
                                   autocomplete="current-password"
                                   required
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs sm:text-sm text-ink dark:text-white focus:outline-hidden focus:ring-2 focus:ring-primary/20 focus:border-primary transition-colors @error('current_password') border-rose-500 @enderror">
                            @error('current_password')
                                <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1.5">
                                    New Password <span class="text-rose-500">*</span>
                                </label>
                                <input type="password"
                                       id="password"
                                       name="password"
                                       autocomplete="new-password"
                                       required
                                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs sm:text-sm text-ink dark:text-white focus:outline-hidden focus:ring-2 focus:ring-primary/20 focus:border-primary transition-colors @error('password') border-rose-500 @enderror">
                                @error('password')
                                    <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="password_confirmation" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1.5">
                                    Confirm New Password <span class="text-rose-500">*</span>
                                </label>
                                <input type="password"
                                       id="password_confirmation"
                                       name="password_confirmation"
                                       autocomplete="new-password"
                                       required
                                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs sm:text-sm text-ink dark:text-white focus:outline-hidden focus:ring-2 focus:ring-primary/20 focus:border-primary transition-colors">
                            </div>
                        </div>

                        <div class="flex justify-end pt-3">
                            <button type="submit"
                                    class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold shadow-sm shadow-amber-600/25 transition-colors cursor-pointer">
                                <i class="fa-solid fa-lock text-xs"></i>
                                <span>Update Password</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>

    </div>

</div>

@push('js')
<script>
    function previewUploadedAvatar(input) {
        if (input.files && input.files[0]) {
            const preview = document.getElementById('avatarPreview');
            const fallback = document.getElementById('avatarFallback');

            preview.src = window.URL.createObjectURL(input.files[0]);
            preview.classList.remove('hidden');
            if (fallback) {
                fallback.classList.add('hidden');
            }
        }
    }
</script>
@endpush
@endsection
