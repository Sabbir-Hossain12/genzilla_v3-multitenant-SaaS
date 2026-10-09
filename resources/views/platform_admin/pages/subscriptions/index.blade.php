@extends('platform_admin.layout.master')

@section('contents')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0 font-size-18">Merchant Subscriptions</h4>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createSubscriptionModal">
                <i class="fa-solid fa-plus me-1"></i> Assign Subscription
            </button>
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
                        <th>Tenant / Store</th>
                        <th>Plan</th>
                        <th>Billing Interval</th>
                        <th>Status</th>
                        <th>Period Start</th>
                        <th>Period End</th>
                        <th width="100" class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($subscriptions as $sub)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td class="fw-bold">
                            {{ $sub->tenant->name ?? 'N/A' }} <br>
                            <small class="text-muted">Subdomain: <code>{{ $sub->tenant->subdomain ?? 'N/A' }}</code></small>
                        </td>
                        <td><span class="badge bg-primary fs-6">{{ $sub->plan->name ?? 'N/A' }}</span></td>
                        <td><span class="badge bg-info text-capitalize">{{ $sub->billing_interval }}</span></td>
                        <td>
                            @switch($sub->subscription_status)
                                @case('active')
                                    <span class="badge bg-success">Active</span>
                                    @break
                                @case('trialing')
                                    <span class="badge bg-warning text-dark">Trialing</span>
                                    @break
                                @case('past_due')
                                    <span class="badge bg-danger">Past Due</span>
                                    @break
                                @case('cancelled')
                                    <span class="badge bg-secondary">Cancelled</span>
                                    @break
                                @default
                                    <span class="badge bg-dark text-capitalize">{{ $sub->subscription_status }}</span>
                            @endswitch
                        </td>
                        <td>{{ $sub->current_period_starts_at ? $sub->current_period_starts_at->format('M d, Y') : '-' }}</td>
                        <td>{{ $sub->current_period_ends_at ? $sub->current_period_ends_at->format('M d, Y') : '-' }}</td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#editSubModal{{ $sub->id }}">
                                <i class="fa-solid fa-pen"></i> Edit
                            </button>
                        </td>
                    </tr>

                    <!-- Edit Modal -->
                    <div class="modal fade" id="editSubModal{{ $sub->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <form method="POST" action="{{ route('admin.subscriptions.update', $sub->id) }}" class="modal-content">
                                @csrf
                                @method('PUT')
                                <div class="modal-header">
                                    <h5 class="modal-title">Update Subscription: {{ $sub->tenant->name ?? '' }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label">Subscription Plan</label>
                                        <select name="plan_id" class="form-select" required>
                                            @foreach($plans as $p)
                                                <option value="{{ $p->id }}" {{ $sub->plan_id == $p->id ? 'selected' : '' }}>{{ $p->name }} (${{ $p->monthly_price }}/mo)</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Billing Interval</label>
                                        <select name="billing_interval" class="form-select" required>
                                            <option value="monthly" {{ $sub->billing_interval == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                            <option value="yearly" {{ $sub->billing_interval == 'yearly' ? 'selected' : '' }}>Yearly</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Status</label>
                                        <select name="subscription_status" class="form-select" required>
                                            @foreach(['trialing', 'active', 'past_due', 'expired', 'cancelled', 'superseded'] as $st)
                                                <option value="{{ $st }}" {{ $sub->subscription_status == $st ? 'selected' : '' }}>{{ ucfirst($st) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Current Period End Date</label>
                                        <input type="date" name="current_period_ends_at" class="form-control" value="{{ $sub->current_period_ends_at ? $sub->current_period_ends_at->format('Y-m-d') : '' }}">
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-primary">Update Subscription</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">No merchant subscriptions found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Create Modal -->
<div class="modal fade" id="createSubscriptionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('admin.subscriptions.store') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Assign Merchant Subscription</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Select Tenant</label>
                    <select name="tenant_id" class="form-select" required>
                        <option value="" disabled selected>-- Select Tenant --</option>
                        @foreach($tenants as $t)
                            <option value="{{ $t->id }}">{{ $t->name }} ({{ $t->subdomain }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Select Plan</label>
                    <select name="plan_id" class="form-select" required>
                        @foreach($plans as $p)
                            <option value="{{ $p->id }}">{{ $p->name }} (${{ $p->monthly_price }}/mo)</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Billing Interval</label>
                    <select name="billing_interval" class="form-select" required>
                        <option value="monthly">Monthly</option>
                        <option value="yearly">Yearly</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select name="subscription_status" class="form-select" required>
                        <option value="active" selected>Active</option>
                        <option value="trialing">Trialing</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">Assign Subscription</button>
            </div>
        </form>
    </div>
</div>
@endsection
