@extends('platform_admin.layout.master')

@push('title')
    Dashboard Overview
@endpush

@push('backendCss')
    <style>
        .dash-hero {
            background: linear-gradient(135deg, #556ee6 0%, #34c38f 100%);
            border-radius: .75rem;
            color: #fff;
        }
        .dash-hero .dash-hero-sub { color: rgba(255, 255, 255, .8); }

        .stat-card { border: 0; border-radius: .75rem; transition: transform .18s ease, box-shadow .18s ease; }
        .stat-card:hover { transform: translateY(-3px); box-shadow: 0 .5rem 1.25rem rgba(0, 0, 0, .1); }
        .stat-icon {
            width: 52px; height: 52px; min-width: 52px;
            display: inline-flex; align-items: center; justify-content: center;
            border-radius: .65rem; font-size: 1.35rem;
        }
        .bg-soft-primary { background: rgba(85, 110, 230, .12); color: #556ee6; }
        .bg-soft-success { background: rgba(52, 195, 143, .12); color: #34c38f; }
        .bg-soft-warning { background: rgba(241, 180, 76, .14); color: #f1b44c; }
        .bg-soft-info    { background: rgba(80, 165, 241, .12); color: #50a5f1; }
        .bg-soft-danger  { background: rgba(244, 106, 106, .12); color: #f46a6a; }

        .mini-tile { border: 0; border-radius: .75rem; }
        .mini-tile .mini-value { font-size: 1.4rem; font-weight: 600; line-height: 1; }

        .content-tile {
            display: flex; align-items: center; gap: .75rem;
            padding: .85rem 1rem; border-radius: .6rem;
            border: 1px solid var(--bs-border-color); height: 100%;
            transition: border-color .15s ease, background .15s ease;
        }
        .content-tile:hover { border-color: #556ee6; background: rgba(85, 110, 230, .04); }
        .content-tile .ti { font-size: 1.1rem; }

        .dash-empty { text-align: center; padding: 1.75rem 0; color: #8b98a9; }
        .dash-empty i { font-size: 1.75rem; display: block; margin-bottom: .5rem; }
    </style>
@endpush

@section('contents')
    @php
        $contactColors = ['new' => 'warning', 'contacted' => 'info', 'resolved' => 'success'];
        $invoiceColors = ['paid' => 'success', 'open' => 'info', 'draft' => 'secondary', 'uncollectible' => 'danger', 'void' => 'dark'];
        $subLabels = [
            'active' => ['Active', 'success'],
            'trialing' => ['Trialing', 'info'],
            'past_due' => ['Past Due', 'warning'],
            'expired' => ['Expired', 'secondary'],
            'cancelled' => ['Cancelled', 'danger'],
            'superseded' => ['Superseded', 'dark'],
        ];
        $subscriptionsTotal = (int) $subscriptionBreakdown->sum();
        $rolesTotal = (int) $roleBreakdown->sum('count');
    @endphp

    {{-- Hero --}}
    <div class="dash-hero mb-4">
        <div class="p-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h4 class="mb-1 text-white">Welcome back, {{ Auth::user()->name ?? 'Admin' }}</h4>
                <p class="dash-hero-sub mb-0">
                    <i class="far fa-calendar-alt me-1"></i> {{ now()->format('l, j F Y') }} &middot;
                    Here's what's happening on your platform today.
                </p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                @can('Create Admin')
                    <a href="{{ route('admin.admins.index') }}" class="btn btn-light btn-sm"><i class="fas fa-user-plus me-1"></i> Add Admin</a>
                @endcan
                @can('Manage Blog')
                    <a href="{{ route('admin.blog.create') }}" class="btn btn-light btn-sm"><i class="fas fa-pen me-1"></i> New Post</a>
                @endcan
                @can('Manage Contacts')
                    <a href="{{ route('admin.contacts.index') }}" class="btn btn-light btn-sm"><i class="fas fa-envelope me-1"></i> Inbox @if($stats['new_contacts'] > 0)<span class="badge bg-danger ms-1">{{ $stats['new_contacts'] }}</span>@endif</a>
                @endcan
                @can('General Setting')
                    <a href="{{ route('admin.settings.index') }}" class="btn btn-light btn-sm"><i class="fas fa-gear me-1"></i> Settings</a>
                @endcan
            </div>
        </div>
    </div>

    {{-- Primary KPI cards --}}
    <div class="row g-3">
        <div class="col-xl-3 col-md-6">
            <div class="card stat-card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <span class="stat-icon bg-soft-primary"><i class="fas fa-store"></i></span>
                        <div class="flex-grow-1">
                            <span class="text-muted d-block text-truncate">Total Tenants</span>
                            <div class="d-flex align-items-end gap-2">
                                <h3 class="mb-0 fw-semibold">{{ number_format($stats['tenants']) }}</h3>
                                @if($stats['tenants_month'] > 0)
                                    <span class="badge bg-soft-success text-success mb-1">+{{ $stats['tenants_month'] }} this month</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card stat-card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <span class="stat-icon bg-soft-success"><i class="fas fa-repeat"></i></span>
                        <div class="flex-grow-1">
                            <span class="text-muted d-block text-truncate">Active Subscriptions</span>
                            <h3 class="mb-0 fw-semibold">{{ number_format($stats['active_subscriptions']) }}</h3>
                            <small class="text-muted">Active &amp; trialing</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card stat-card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <span class="stat-icon bg-soft-warning"><i class="fas fa-sack-dollar"></i></span>
                        <div class="flex-grow-1">
                            <span class="text-muted d-block text-truncate">Revenue (Paid)</span>
                            <div class="d-flex align-items-end gap-2">
                                <h3 class="mb-0 fw-semibold">{{ number_format($stats['revenue'], 2) }}</h3>
                                <small class="text-muted mb-1">{{ $currency }}</small>
                            </div>
                            @if($stats['outstanding'] > 0)
                                <small class="text-warning">{{ number_format($stats['outstanding'], 2) }} outstanding</small>
                            @else
                                <small class="text-muted">No outstanding invoices</small>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card stat-card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <span class="stat-icon bg-soft-info"><i class="fas fa-envelope-open-text"></i></span>
                        <div class="flex-grow-1">
                            <span class="text-muted d-block text-truncate">Contacts</span>
                            <div class="d-flex align-items-end gap-2">
                                <h3 class="mb-0 fw-semibold">{{ number_format($stats['contacts']) }}</h3>
                                @if($stats['new_contacts'] > 0)
                                    <span class="badge bg-soft-danger text-danger mb-1">{{ $stats['new_contacts'] }} new</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts --}}
    <div class="row g-3 mt-1">
        <div class="col-xl-8">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-transparent d-flex align-items-center justify-content-between">
                    <h5 class="card-title mb-0">Revenue Overview</h5>
                    <span class="text-muted small">Last 6 months &middot; {{ $currency }}</span>
                </div>
                <div class="card-body">
                    <div id="revenueChart" style="height: 300px;"></div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-transparent">
                    <h5 class="card-title mb-0">Admins by Role</h5>
                </div>
                <div class="card-body">
                    @if($rolesTotal > 0)
                        <div id="roleChart" style="height: 300px;"></div>
                    @else
                        <div class="dash-empty"><i class="fas fa-user-shield"></i>No admins yet</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Secondary tiles --}}
    <div class="row g-3 mt-1">
        <div class="col-xl-3 col-sm-6">
            <div class="card mini-tile shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="stat-icon bg-soft-primary"><i class="fas fa-user-shield"></i></span>
                    <div>
                        <div class="mini-value">{{ number_format($stats['active_admins']) }}<span class="text-muted fs-6">/{{ number_format($stats['admins']) }}</span></div>
                        <small class="text-muted">Active admins</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card mini-tile shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="stat-icon bg-soft-info"><i class="fas fa-bell"></i></span>
                    <div>
                        <div class="mini-value">{{ number_format($stats['subscribers']) }}</div>
                        <small class="text-muted">Subscribers @if($stats['subscribers_month'] > 0)<span class="text-success">(+{{ $stats['subscribers_month'] }})</span>@endif</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card mini-tile shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="stat-icon bg-soft-success"><i class="fas fa-newspaper"></i></span>
                    <div>
                        <div class="mini-value">{{ number_format($stats['published_posts']) }}<span class="text-muted fs-6">/{{ number_format($stats['blog_posts']) }}</span></div>
                        <small class="text-muted">Published posts</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card mini-tile shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="stat-icon bg-soft-warning"><i class="fas fa-globe"></i></span>
                    <div>
                        <div class="mini-value">{{ number_format($stats['domains']) }}</div>
                        <small class="text-muted">Domains &middot; {{ $stats['custom_domains'] }} custom</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Recent activity --}}
    <div class="row g-3 mt-1">
        <div class="col-lg-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-transparent d-flex align-items-center justify-content-between">
                    <h5 class="card-title mb-0">Recent Contacts</h5>
                    @can('Manage Contacts')<a href="{{ route('admin.contacts.index') }}" class="small">View all</a>@endcan
                </div>
                <div class="card-body p-0">
                    @forelse($recentContacts as $contact)
                        <div class="d-flex align-items-start gap-3 px-3 py-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                            <span class="stat-icon bg-soft-info" style="width:38px;height:38px;min-width:38px;font-size:.95rem;">
                                {{ strtoupper(substr($contact->name ?: $contact->email, 0, 1)) }}
                            </span>
                            <div class="flex-grow-1 overflow-hidden">
                                <div class="d-flex justify-content-between gap-2">
                                    <span class="fw-medium text-truncate">{{ $contact->name ?: 'Unknown' }}</span>
                                    <span class="badge bg-soft-{{ $contactColors[$contact->status] ?? 'secondary' }} text-{{ $contactColors[$contact->status] ?? 'secondary' }} text-capitalize">{{ $contact->status }}</span>
                                </div>
                                <small class="text-muted d-block text-truncate">{{ $contact->email }}</small>
                                <small class="text-muted">{{ optional($contact->created_at)->diffForHumans() }}</small>
                            </div>
                        </div>
                    @empty
                        <div class="dash-empty"><i class="fas fa-inbox"></i>No contact messages yet</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-transparent d-flex align-items-center justify-content-between">
                    <h5 class="card-title mb-0">Recent Invoices</h5>
                    @can('Manage Invoices')<a href="{{ route('admin.invoices.index') }}" class="small">View all</a>@endcan
                </div>
                <div class="card-body p-0">
                    @forelse($recentInvoices as $invoice)
                        <div class="d-flex align-items-center gap-3 px-3 py-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                            <span class="stat-icon bg-soft-success" style="width:38px;height:38px;min-width:38px;font-size:.95rem;"><i class="fas fa-file-invoice-dollar"></i></span>
                            <div class="flex-grow-1 overflow-hidden">
                                <div class="d-flex justify-content-between gap-2">
                                    <span class="fw-medium text-truncate">{{ $invoice->invoice_number ?: 'INV-'.$invoice->id }}</span>
                                    <span class="badge bg-soft-{{ $invoiceColors[$invoice->status] ?? 'secondary' }} text-{{ $invoiceColors[$invoice->status] ?? 'secondary' }} text-capitalize">{{ $invoice->status }}</span>
                                </div>
                                <small class="text-muted d-block text-truncate">{{ optional($invoice->tenant)->name ?? 'N/A' }}</small>
                                <small class="text-muted">{{ number_format((float) $invoice->total, 2) }} {{ $invoice->currency }}</small>
                            </div>
                        </div>
                    @empty
                        <div class="dash-empty"><i class="fas fa-file-invoice"></i>No invoices yet</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-transparent d-flex align-items-center justify-content-between">
                    <h5 class="card-title mb-0">Recent Blog Posts</h5>
                    @can('Manage Blog')<a href="{{ route('admin.blog.index') }}" class="small">View all</a>@endcan
                </div>
                <div class="card-body p-0">
                    @forelse($recentPosts as $post)
                        <div class="d-flex align-items-center gap-3 px-3 py-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                            <span class="stat-icon bg-soft-warning" style="width:38px;height:38px;min-width:38px;font-size:.95rem;"><i class="fas fa-feather"></i></span>
                            <div class="flex-grow-1 overflow-hidden">
                                <div class="d-flex justify-content-between gap-2">
                                    <span class="fw-medium text-truncate">{{ $post->title }}</span>
                                    @if($post->status == 1)
                                        <span class="badge bg-soft-success text-success">Published</span>
                                    @else
                                        <span class="badge bg-soft-secondary text-secondary">Draft</span>
                                    @endif
                                </div>
                                <small class="text-muted d-block text-truncate">{{ optional($post->category)->name ?? 'Uncategorized' }}</small>
                                <small class="text-muted">{{ optional($post->published_at)->format('d M Y') ?? optional($post->created_at)->diffForHumans() }}</small>
                            </div>
                        </div>
                    @empty
                        <div class="dash-empty"><i class="fas fa-newspaper"></i>No blog posts yet</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Content overview + subscription snapshot --}}
    <div class="row g-3 mt-1 mb-4">
        <div class="col-xl-8">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-transparent">
                    <h5 class="card-title mb-0">Content Overview</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @foreach($contentStats as $item)
                            @php $allowed = Auth::user() && Auth::user()->can($item['permission']); @endphp
                            <div class="col-xl-3 col-md-4 col-sm-6">
                                @if($allowed)
                                    <a href="{{ route($item['route']) }}" class="content-tile text-body text-decoration-none">
                                @else
                                    <div class="content-tile">
                                @endif
                                        <span class="ti bg-soft-primary rounded d-inline-flex align-items-center justify-content-center" style="width:36px;height:36px;"><i class="fas {{ $item['icon'] }}"></i></span>
                                        <span class="flex-grow-1">
                                            <span class="d-block fw-semibold fs-5 lh-1">{{ number_format($item['count']) }}</span>
                                            <small class="text-muted">{{ $item['label'] }}</small>
                                        </span>
                                @if($allowed)
                                    </a>
                                @else
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-transparent d-flex align-items-center justify-content-between">
                    <h5 class="card-title mb-0">Subscription Status</h5>
                    <span class="badge bg-soft-primary text-primary">{{ number_format($subscriptionsTotal) }} total</span>
                </div>
                <div class="card-body">
                    @if($subscriptionsTotal > 0)
                        @foreach($subLabels as $key => $meta)
                            @php $count = (int) ($subscriptionBreakdown[$key] ?? 0); $pct = $subscriptionsTotal > 0 ? round($count / $subscriptionsTotal * 100) : 0; @endphp
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-muted">{{ $meta[0] }}</span>
                                    <span class="fw-medium">{{ $count }}</span>
                                </div>
                                <div class="progress" style="height:6px;">
                                    <div class="progress-bar bg-{{ $meta[1] }}" role="progressbar" style="width: {{ $pct }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="dash-empty"><i class="fas fa-credit-card"></i>No subscriptions yet</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('backendJs')
    <script src="{{ asset('backend/assets/libs/apexcharts/apexcharts.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const chartLabels = @json($chartLabels);
            const revenueSeries = @json($revenueSeries);

            const revenueEl = document.querySelector('#revenueChart');
            if (revenueEl && typeof ApexCharts !== 'undefined') {
                new ApexCharts(revenueEl, {
                    chart: { type: 'area', height: 300, toolbar: { show: false }, fontFamily: 'inherit' },
                    series: [{ name: 'Revenue', data: revenueSeries }],
                    xaxis: { categories: chartLabels, axisBorder: { show: false }, axisTicks: { show: false } },
                    yaxis: { labels: { formatter: (v) => Number(v).toLocaleString() } },
                    dataLabels: { enabled: false },
                    stroke: { curve: 'smooth', width: 3 },
                    fill: {
                        type: 'gradient',
                        gradient: { shadeIntensity: 1, opacityFrom: 0.45, opacityTo: 0.05, stops: [0, 90, 100] }
                    },
                    colors: ['#556ee6'],
                    grid: { borderColor: 'rgba(0,0,0,.06)', strokeDashArray: 4 },
                    tooltip: { y: { formatter: (v) => Number(v).toLocaleString() } },
                    noData: { text: 'No revenue recorded yet' }
                }).render();
            }

            const roleEl = document.querySelector('#roleChart');
            if (roleEl && typeof ApexCharts !== 'undefined') {
                new ApexCharts(roleEl, {
                    chart: { type: 'donut', height: 300, fontFamily: 'inherit' },
                    series: @json($roleBreakdown->pluck('count')),
                    labels: @json($roleBreakdown->pluck('name')),
                    colors: ['#556ee6', '#34c38f', '#f1b44c', '#50a5f1', '#f46a6a', '#74788d'],
                    legend: { position: 'bottom' },
                    dataLabels: { enabled: true },
                    plotOptions: { pie: { donut: { size: '68%' } } },
                    noData: { text: 'No admins yet' }
                }).render();
            }
        });
    </script>
@endpush
