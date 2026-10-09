@extends('platform_admin.layout.master')

@section('contents')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0 font-size-18">Subscription Plans</h4>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createPlanModal">
                <i class="fa-solid fa-plus me-1"></i> Add New Plan
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
                        <th>Plan Name</th>
                        <th>Monthly Price</th>
                        <th>Yearly Price</th>
                        <th>Limits (Products / Staff / Storage)</th>
                        <th>Custom Domain</th>
                        <th>Multi-Currency</th>
                        <th>Active Subs</th>
                        <th>Status</th>
                        <th width="120" class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($plans as $plan)
                    <tr>
                        <td class="fw-bold">{{ $plan->name }} <br><small class="text-muted"><code>{{ $plan->slug }}</code></small></td>
                        <td class="fw-bold text-success">${{ number_format($plan->monthly_price, 2) }}</td>
                        <td class="fw-bold text-primary">${{ number_format($plan->yearly_price, 2) }}</td>
                        <td>
                            <span class="badge bg-light text-dark">{{ $plan->max_products }} Prods</span>
                            <span class="badge bg-light text-dark">{{ $plan->max_staff_accounts }} Staff</span>
                            <span class="badge bg-light text-dark">{{ $plan->max_storage_mb }} MB</span>
                        </td>
                        <td>
                            {!! $plan->allow_custom_domain ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-secondary">No</span>' !!}
                        </td>
                        <td>
                            {!! $plan->has_multi_currency ? '<span class="badge bg-success">Yes ('.$plan->max_currencies_supported.')</span>' : '<span class="badge bg-secondary">No</span>' !!}
                        </td>
                        <td><span class="badge bg-info fs-6">{{ $plan->subscriptions_count }}</span></td>
                        <td>
                            {!! $plan->status ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>' !!}
                        </td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-info me-1" data-bs-toggle="modal" data-bs-target="#editPlanModal{{ $plan->id }}">
                                <i class="fa-solid fa-pen"></i>
                            </button>
                            <form action="{{ route('admin.plans.destroy', $plan->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this plan?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger"><i class="fa-solid fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">No subscription plans found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Edit Modals (Rendered outside table to prevent HTML invalidation) -->
@foreach($plans as $plan)
<div class="modal fade" id="editPlanModal{{ $plan->id }}" tabindex="-1" aria-labelledby="editPlanModalLabel{{ $plan->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form method="POST" action="{{ route('admin.plans.update', $plan->id) }}" class="modal-content">
            @csrf
            @method('PUT')
            <div class="modal-header">
                <h5 class="modal-title" id="editPlanModalLabel{{ $plan->id }}">Edit Plan: {{ $plan->name }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label font-weight-bold">Plan Name</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $plan->name) }}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label font-weight-bold">Slug</label>
                        <input type="text" name="slug" class="form-control" value="{{ old('slug', $plan->slug) }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label font-weight-bold">Monthly Price ($)</label>
                        <input type="number" step="0.01" name="monthly_price" class="form-control" value="{{ old('monthly_price', $plan->monthly_price) }}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label font-weight-bold">Yearly Price ($)</label>
                        <input type="number" step="0.01" name="yearly_price" class="form-control" value="{{ old('yearly_price', $plan->yearly_price) }}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Stripe Monthly Price ID</label>
                        <input type="text" name="stripe_monthly_price_id" class="form-control" value="{{ old('stripe_monthly_price_id', $plan->stripe_monthly_price_id) }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Stripe Yearly Price ID</label>
                        <input type="text" name="stripe_yearly_price_id" class="form-control" value="{{ old('stripe_yearly_price_id', $plan->stripe_yearly_price_id) }}">
                    </div>
                </div>

                <hr class="my-3">
                <h6 class="text-primary mb-3"><i class="fa-solid fa-sliders me-1"></i> Resource & Feature Limits</h6>
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label font-weight-bold">Max Products</label>
                        <input type="number" name="max_products" class="form-control" value="{{ old('max_products', $plan->max_products) }}" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label font-weight-bold">Max Staff Accounts</label>
                        <input type="number" name="max_staff_accounts" class="form-control" value="{{ old('max_staff_accounts', $plan->max_staff_accounts) }}" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label font-weight-bold">Max Storage (MB)</label>
                        <input type="number" name="max_storage_mb" class="form-control" value="{{ old('max_storage_mb', $plan->max_storage_mb) }}" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label font-weight-bold">Max File Size (MB)</label>
                        <input type="number" name="max_digital_file_size_mb" class="form-control" value="{{ old('max_digital_file_size_mb', $plan->max_digital_file_size_mb) }}" required>
                    </div>
                </div>

                <div class="row bg-light p-3 rounded mb-3">
                    <div class="col-md-4 mb-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="allow_custom_domain" id="customDomainEdit{{ $plan->id }}" {{ $plan->allow_custom_domain ? 'checked' : '' }}>
                            <label class="form-check-label" for="customDomainEdit{{ $plan->id }}">Allow Custom Domain</label>
                        </div>
                    </div>
                    <div class="col-md-4 mb-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="has_multi_currency" id="multiCurrEdit{{ $plan->id }}" {{ $plan->has_multi_currency ? 'checked' : '' }}>
                            <label class="form-check-label" for="multiCurrEdit{{ $plan->id }}">Multi-Currency</label>
                        </div>
                    </div>
                    <div class="col-md-4 mb-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="has_advanced_reporting" id="reportingEdit{{ $plan->id }}" {{ $plan->has_advanced_reporting ? 'checked' : '' }}>
                            <label class="form-check-label" for="reportingEdit{{ $plan->id }}">Advanced Reporting</label>
                        </div>
                    </div>
                    <div class="col-md-4 mb-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="has_custom_rbac" id="rbacEdit{{ $plan->id }}" {{ $plan->has_custom_rbac ? 'checked' : '' }}>
                            <label class="form-check-label" for="rbacEdit{{ $plan->id }}">Custom RBAC</label>
                        </div>
                    </div>
                    <div class="col-md-4 mb-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="has_audit_logs" id="auditEdit{{ $plan->id }}" {{ $plan->has_audit_logs ? 'checked' : '' }}>
                            <label class="form-check-label" for="auditEdit{{ $plan->id }}">Audit Logs</label>
                        </div>
                    </div>
                    <div class="col-md-4 mb-2">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="status" id="planStatusEdit{{ $plan->id }}" {{ $plan->status ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold text-success" for="planStatusEdit{{ $plan->id }}">Active Plan</label>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label font-weight-bold">Max Currencies Supported</label>
                    <input type="number" name="max_currencies_supported" class="form-control" value="{{ old('max_currencies_supported', $plan->max_currencies_supported) }}" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i> Update Plan</button>
            </div>
        </form>
    </div>
</div>
@endforeach

<!-- Create Modal -->
<div class="modal fade" id="createPlanModal" tabindex="-1" aria-labelledby="createPlanModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form method="POST" action="{{ route('admin.plans.store') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title" id="createPlanModalLabel">Create New Plan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label font-weight-bold">Plan Name</label>
                        <input type="text" name="name" class="form-control" required placeholder="Growth Plan">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label font-weight-bold">Slug</label>
                        <input type="text" name="slug" class="form-control" placeholder="growth">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label font-weight-bold">Monthly Price ($)</label>
                        <input type="number" step="0.01" name="monthly_price" class="form-control" value="49.00" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label font-weight-bold">Yearly Price ($)</label>
                        <input type="number" step="0.01" name="yearly_price" class="form-control" value="490.00" required>
                    </div>
                </div>

                <hr class="my-3">
                <h6 class="text-primary mb-3"><i class="fa-solid fa-sliders me-1"></i> Resource & Feature Limits</h6>
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label font-weight-bold">Max Products</label>
                        <input type="number" name="max_products" class="form-control" value="200" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label font-weight-bold">Max Staff Accounts</label>
                        <input type="number" name="max_staff_accounts" class="form-control" value="5" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label font-weight-bold">Max Storage (MB)</label>
                        <input type="number" name="max_storage_mb" class="form-control" value="500" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label font-weight-bold">Max File Size (MB)</label>
                        <input type="number" name="max_digital_file_size_mb" class="form-control" value="50" required>
                    </div>
                </div>

                <div class="row bg-light p-3 rounded mb-3">
                    <div class="col-md-4 mb-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="allow_custom_domain" id="customDomainCreate" checked>
                            <label class="form-check-label" for="customDomainCreate">Allow Custom Domain</label>
                        </div>
                    </div>
                    <div class="col-md-4 mb-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="has_multi_currency" id="multiCurrCreate" checked>
                            <label class="form-check-label" for="multiCurrCreate">Multi-Currency</label>
                        </div>
                    </div>
                    <div class="col-md-4 mb-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="has_advanced_reporting" id="reportingCreate">
                            <label class="form-check-label" for="reportingCreate">Advanced Reporting</label>
                        </div>
                    </div>
                    <div class="col-md-4 mb-2">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="status" id="planStatusCreate" checked>
                            <label class="form-check-label fw-bold text-success" for="planStatusCreate">Active Plan</label>
                        </div>
                    </div>
                </div>
                <div class="mt-3">
                    <label class="form-label font-weight-bold">Max Currencies Supported</label>
                    <input type="number" name="max_currencies_supported" class="form-control" value="3" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i> Save Plan</button>
            </div>
        </form>
    </div>
</div>
@endsection
