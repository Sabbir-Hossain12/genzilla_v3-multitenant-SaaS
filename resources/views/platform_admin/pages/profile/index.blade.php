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

        <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <h1 class="text-xl font-bold text-ink dark:text-white tracking-tight">My Profile</h1>
        <nav class="flex" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3 text-sm text-slate-500 dark:text-slate-400">
                <li class="inline-flex items-center">
                    <a href="{{ route('admin.dashboard.index') }}" class="hover:text-primary transition-colors">Dashboard</a>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="w-4 h-4 text-slate-400 mx-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                        <span class="text-slate-700 dark:text-slate-200 font-medium">Profile</span>
                    </div>
                </li>
            </ol>
        </nav>
    </div>

    <!-- Error Alerts -->
    @if($errors->any())
        <div class="mb-6 p-4 rounded-xl bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 text-red-600 dark:text-red-400 text-sm">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Left Column: User Profile Card -->
        <div class="lg:col-span-4">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm">
                <div class="flex flex-col items-center text-center">
                    <div class="relative mb-4">
                        @if($user->img_url)
                            <img id="avatarPreview" src="{{ $user->img_url }}" alt="{{ $user->name }}"
                                 class="w-24 h-24 rounded-full object-cover ring-4 ring-primary/10">
                        @else
                            <img id="avatarPreview" src="" alt="{{ $user->name }}"
                                 class="w-24 h-24 rounded-full object-cover ring-4 ring-primary/10 hidden">
                            <span id="avatarFallback"
                                  class="flex items-center justify-center w-24 h-24 rounded-full bg-primary/10 text-primary font-bold text-3xl ring-4 ring-primary/10">
                                {{ $initials ?: 'A' }}
                            </span>
                        @endif
                    </div>
                    <h3 class="text-base font-bold text-ink dark:text-white mb-1">{{ $user->name }}</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mb-3">{{ $user->email }}</p>

                    <div class="flex flex-wrap justify-center gap-1.5 mb-6">
                        @forelse($roles as $role)
                            <span class="px-2.5 py-0.5 text-xs font-semibold bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 rounded-full">{{ $role }}</span>
                        @empty
                            <span class="px-2.5 py-0.5 text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 rounded-full">No role</span>
                        @endforelse
                    </div>

                    <div class="w-full border-t border-slate-100 dark:border-slate-800 pt-4 space-y-3 text-left text-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500 dark:text-slate-400">Status</span>
                            <span>
                                {!! $user->status == 1 ? '<span class="px-2.5 py-0.5 text-xs font-semibold bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 rounded-full">Active</span>' : '<span class="px-2.5 py-0.5 text-xs font-semibold bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400 rounded-full">Inactive</span>' !!}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500 dark:text-slate-400">Member since</span>
                            <span class="font-medium text-slate-700 dark:text-slate-300">{{ optional($user->created_at)->format('d M Y') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Edit Forms -->
        <div class="lg:col-span-8 space-y-6">
            <!-- Profile Information Card -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm">
                <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800">
                    <h4 class="text-base font-bold text-ink dark:text-white m-0">Profile Information</h4>
                </div>
                <div class="p-6">
                    <form method="post" action="{{ route('admin.profile.update') }}" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        @method('PUT')
                        <div>
                            <label for="img" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1.5">Profile Image</label>
                            <input type="file" class="w-full text-sm text-slate-500 dark:text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-primarylt file:text-primary hover:file:bg-primarylt/80 cursor-pointer @error('img') border-red-500 @enderror"
                                   id="img" name="img" accept="image/*"
                                   oninput="avatarPreview.src=window.URL.createObjectURL(this.files[0]); avatarPreview.style.display='block'; var f=document.getElementById('avatarFallback'); if(f){f.style.display='none';}">
                            @error('img')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                            <p class="mt-1 text-xs text-slate-400">JPG, PNG, GIF or WEBP. Max 2MB.</p>
                        </div>
                        <div>
                            <label for="name" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1.5">Name</label>
                            <input type="text" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary @error('name') border-red-500 @enderror"
                                   id="name" name="name" value="{{ old('name', $user->name) }}" required>
                            @error('name')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1.5">Email</label>
                            <input type="email" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary @error('email') border-red-500 @enderror"
                                   id="email" name="email" value="{{ old('email', $user->email) }}" required>
                            @error('email')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                        </div>
                        <div class="flex justify-end pt-2">
                            <button type="submit" class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl bg-primary text-white text-sm font-semibold hover:bg-primary/90 transition-colors cursor-pointer">
                                <i class="fa-solid fa-floppy-disk me-2"></i> Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Update Password Card -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm">
                <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800">
                    <h4 class="text-base font-bold text-ink dark:text-white m-0">Update Password</h4>
                </div>
                <div class="p-6">
                    <form method="post" action="{{ route('admin.profile.password') }}" class="space-y-4">
                        @csrf
                        @method('PUT')
                        <div>
                            <label for="current_password" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1.5">Current Password</label>
                            <input type="password" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary @error('current_password') border-red-500 @enderror"
                                   id="current_password" name="current_password" autocomplete="current-password" required>
                            @error('current_password')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1.5">New Password</label>
                            <input type="password" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary @error('password') border-red-500 @enderror"
                                   id="password" name="password" autocomplete="new-password" required>
                            @error('password')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="password_confirmation" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1.5">Confirm New Password</label>
                            <input type="password" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"
                                   id="password_confirmation" name="password_confirmation" autocomplete="new-password" required>
                        </div>
                        <div class="flex justify-end pt-2">
                            <button type="submit" class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl bg-primary text-white text-sm font-semibold hover:bg-primary/90 transition-colors cursor-pointer">
                                <i class="fa-solid fa-key me-2"></i> Update Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
