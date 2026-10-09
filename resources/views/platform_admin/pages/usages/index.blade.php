@extends('platform_admin.layout.master')

@section('contents')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0 font-size-18">Tenant Resource Usage Monitoring</h4>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Tenant Store</th>
                        <th>Current Plan</th>
                        <th>Products Count / Limit</th>
                        <th>Staff Accounts / Limit</th>
                        <th>Storage Used / Limit</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($usages as $usage)
                    @php
                        $plan = $usage->tenant->activeSubscription->plan ?? null;
                        $maxProducts = $plan->max_products ?? 100;
                        $maxStaff = $plan->max_staff_accounts ?? 5;
                        $maxStorageMB = $plan->max_storage_mb ?? 500;
                        $usedMB = round($usage->storage_used_bytes / (1024 * 1024), 2);
                        $storagePct = min(100, round(($usedMB / max(1, $maxStorageMB)) * 100));
                        $prodPct = min(100, round(($usage->products_count / max(1, $maxProducts)) * 100));
                    @endphp
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td class="fw-bold">
                            {{ $usage->tenant->name ?? 'N/A' }} <br>
                            <small class="text-muted"><code>{{ $usage->tenant->subdomain ?? 'N/A' }}</code></small>
                        </td>
                        <td><span class="badge bg-primary">{{ $plan->name ?? 'Default Plan' }}</span></td>
                        <td>
                            <div class="d-flex justify-content-between mb-1">
                                <span class="fw-bold">{{ $usage->products_count }} / {{ $maxProducts }}</span>
                                <small>{{ $prodPct }}%</small>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar {{ $prodPct > 85 ? 'bg-danger' : 'bg-success' }}" style="width: {{ $prodPct }}%;"></div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark fs-6">{{ $usage->staff_accounts_count }} / {{ $maxStaff }} Staff</span>
                        </td>
                        <td>
                            <div class="d-flex justify-content-between mb-1">
                                <span class="fw-bold">{{ $usedMB }} MB / {{ $maxStorageMB }} MB</span>
                                <small>{{ $storagePct }}%</small>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar {{ $storagePct > 85 ? 'bg-danger' : 'bg-info' }}" style="width: {{ $storagePct }}%;"></div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">No tenant resource usages recorded yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
