@extends('platform_admin.layout.master')

@section('contents')
    <div class="space-y-6">
        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-slate-900 tracking-tight dark:text-white">Create Blog Post</h1>
                <p class="text-sm text-slate-500 mt-0.5">Write and publish a new platform article or news post.</p>
            </div>
            <a href="{{ route('admin.blog.index') }}"
               class="inline-flex items-center justify-center px-4 py-2 text-sm font-semibold text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 rounded-lg shadow-sm transition-all gap-2">
                <i class="fa-solid fa-arrow-left text-xs"></i>
                <span>Back to Blog</span>
            </a>
        </div>

        @if ($errors->any())
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.blog.store') }}" class="space-y-6">
            @csrf
            @include('platform_admin.pages.blog._fields')
        </form>
    </div>
@endsection
