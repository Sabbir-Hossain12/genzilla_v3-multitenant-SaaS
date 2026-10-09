@extends('platform_admin.layout.master')

@section('contents')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0 font-size-18">Tenant Invoices</h4>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Invoice Number</th>
                        <th>Tenant</th>
                        <th>Plan</th>
                        <th>Total Amount</th>
                        <th>Status</th>
                        <th>Paid Date</th>
                        <th width="120" class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $inv)
                    <tr>
                        <td class="fw-bold"><code>{{ $inv->invoice_number }}</code></td>
                        <td>{{ $inv->tenant->name ?? 'N/A' }}</td>
                        <td><span class="badge bg-light text-dark">{{ $inv->subscription->plan->name ?? 'Subscription' }}</span></td>
                        <td class="fw-bold text-success">{{ $inv->currency }} ${{ number_format($inv->total, 2) }}</td>
                        <td>
                            @switch($inv->status)
                                @case('paid')
                                    <span class="badge bg-success">Paid</span>
                                    @break
                                @case('open')
                                    <span class="badge bg-warning text-dark">Open</span>
                                    @break
                                @case('draft')
                                    <span class="badge bg-secondary">Draft</span>
                                    @break
                                @default
                                    <span class="badge bg-danger text-capitalize">{{ $inv->status }}</span>
                            @endswitch
                        </td>
                        <td>{{ $inv->paid_at ? $inv->paid_at->format('M d, Y H:i') : '-' }}</td>
                        <td class="text-center">
                            <a href="{{ route('admin.invoices.show', $inv->id) }}" class="btn btn-sm btn-outline-primary me-1"><i class="fa-solid fa-eye"></i></a>
                            <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#editInvModal{{ $inv->id }}"><i class="fa-solid fa-pen"></i></button>
                        </td>
                    </tr>

                    <!-- Edit Status Modal -->
                    <div class="modal fade" id="editInvModal{{ $inv->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <form method="POST" action="{{ route('admin.invoices.update-status', $inv->id) }}" class="modal-content">
                                @csrf
                                @method('PATCH')
                                <div class="modal-header">
                                    <h5 class="modal-title">Update Invoice Status</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label">Invoice Status</label>
                                        <select name="status" class="form-select" required>
                                            @foreach(['draft', 'open', 'paid', 'uncollectible', 'void'] as $st)
                                                <option value="{{ $st }}" {{ $inv->status == $st ? 'selected' : '' }}>{{ ucfirst($st) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-primary">Update Status</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">No tenant invoices found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
