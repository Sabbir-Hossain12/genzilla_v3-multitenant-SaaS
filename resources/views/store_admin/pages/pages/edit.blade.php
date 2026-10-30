@extends('store_admin.layout.master')

@section('contents')
<div class="space-y-6 max-w-5xl">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Edit Custom Page</h1>
            <nav class="flex items-center gap-1.5 mt-1 text-xs text-slate-400">
                <a href="{{ route('store.admin.dashboard') }}" class="hover:text-indigo-600 transition-colors">Dashboard</a>
                <i class="fa-solid fa-chevron-right text-[10px]"></i>
                <a href="{{ route('store.admin.pages.index') }}" class="hover:text-indigo-600 transition-colors">Pages</a>
                <i class="fa-solid fa-chevron-right text-[10px]"></i>
                <span class="text-slate-600 dark:text-slate-300 font-medium">Edit: {{ $page->title }}</span>
            </nav>
        </div>
    </div>

    {{-- Validation Errors --}}
    @if ($errors->any())
        <div class="flex items-start gap-3 bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/30 text-red-700 dark:text-red-400 rounded-2xl px-4 py-3.5 text-sm shadow-sm">
            <i class="fa-solid fa-circle-exclamation text-base mt-0.5 shrink-0"></i>
            <ul class="flex-1 space-y-0.5 list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('store.admin.pages.update', $page) }}">
        @csrf
        @method('PUT')
        @include('store_admin.pages.pages._fields')
    </form>
</div>
@endsection
