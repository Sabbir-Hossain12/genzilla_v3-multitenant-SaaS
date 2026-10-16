@extends('platform_admin.layout.master')

@section('contents')

    {{-- Page Title --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h4 class="text-xl font-extrabold text-ink dark:text-white">Create Blog Post</h4>
            <nav class="flex items-center gap-1.5 mt-1 text-xs text-slate-400">
                <a href="{{ route('admin.dashboard.index') }}" class="hover:text-primary transition-colors">Admin</a>
                <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                <a href="{{ route('admin.blog.index') }}" class="hover:text-primary transition-colors">Blog Posts</a>
                <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                <span class="text-slate-500 dark:text-slate-300 font-medium">Create</span>
            </nav>
        </div>
    </div>

    {{-- Validation Errors --}}
    @if ($errors->any())
        <div class="flex items-start gap-3 bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/30
                    text-red-700 dark:text-red-400 rounded-2xl px-4 py-3.5 mb-5 text-sm">
            <svg class="w-4 h-4 mt-0.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <ul class="flex-1 space-y-0.5 list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.blog.store') }}" enctype="multipart/form-data">
        @csrf
        @include('platform_admin.pages.blog._fields')
    </form>

@endsection
