@extends('platform_admin.layout.master')

@section('contents')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0 font-size-18">Invoice Details</h4>
            <a href="{{ route('admin.invoices.index') }}" class="btn btn-secondary">
                <i class="fa-solid fa-arrow-left me-1"></i> Back to Invoices
            </a>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body p-4">
        <div class="row mb-4">
            <div class="col-sm-6">
                <h5 class="text-primary fw-bold">GENZILLA SAAS PLATFORM</h5>
                <p class="text-muted">Invoice #: <strong>{{ $invoice->invoice_number }}</strong></p>
                <p class="text-muted">Date: {{ $invoice->created_at ? $invoice->created_at->format('F d, Y') : '-' }}</p>
            </div>
            <div class="col-sm-6 text-sm-end">
                <h5>Billed To:</h5>
                <h6 class="fw-bold">{{ $invoice->tenant->name ?? 'Merchant Store' }}</h6>
                <p class="text-muted mb-1">Subdomain: {{ $invoice->tenant->subdomain ?? 'N/A' }}</p>
                <p class="text-muted mb-1">Email: {{ $invoice->tenant->email ?? 'N/A' }}</p>
            </div>
        </div>

        <div class="table-responsive my-4">
            <table class="table table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>Description</th>
                        <th>Billing Period</th>
                        <th class="text-end">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <strong>{{ $invoice->subscription->plan->name ?? 'Subscription Plan' }}</strong>
                            <span class="badge bg-info text-capitalize ms-1">{{ $invoice->subscription->billing_interval ?? 'Monthly' }}</span>
                        </td>
                        <td>
                            {{ $invoice->billing_period_start ? $invoice->billing_period_start->format('M d, Y') : '-' }} to {{ $invoice->billing_period_end ? $invoice->billing_period_end->format('M d, Y') : '-' }}
                        </td>
                        <td class="text-end fw-bold">${{ number_format($invoice->subtotal, 2) }}</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="2" class="text-end">Subtotal:</th>
                        <td class="text-end">${{ number_format($invoice->subtotal, 2) }}</td>
                    </tr>
                    <tr>
                        <th colspan="2" class="text-end">Tax:</th>
                        <td class="text-end">${{ number_format($invoice->tax, 2) }}</td>
                    </tr>
                    <tr>
                        <th colspan="2" class="text-end fs-5">Total Paid / Due:</th>
                        <td class="text-end fs-5 fw-bold text-success">${{ number_format($invoice->total, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
