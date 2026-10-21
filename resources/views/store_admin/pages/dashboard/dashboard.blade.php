@extends('store_admin.layout.master')

@push('title')
    Dashboard Overview
@endpush

@push('css')
@endpush

@section('contents')

    <!-- Heading row -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl md:text-2xl font-extrabold text-ink dark:text-white flex items-center gap-2">
                 Hello, {{ auth()->user()->name }} <span>👋</span>
            </h1>
            <p class="text-xs md:text-sm text-slate-400 mt-1">
                Here's what's happening across your platform today &bull; {{ now()->format('l, j F Y') }}
            </p>
        </div>
        <div class="flex items-center gap-2.5 shrink-0">
            @can('Manage Invoices')
                <a href=""
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold
                          bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200
                          border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-750
                          transition-colors shadow-sm">
                    <svg class="w-3.5 h-3.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/>
                    </svg>
                    Invoices
                </a>
            @endcan
            @can('Manage Plans')
                <a href=""
                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-semibold
                          bg-primary hover:bg-primarydark text-white
                          transition-colors shadow-sm shadow-primary/30">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                    </svg>
                    Manage Plans
                </a>
            @endcan
        </div>
    </div>

@endsection

@push('js')

@endpush
