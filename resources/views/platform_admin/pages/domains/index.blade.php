@extends('platform_admin.layout.master')

@section('contents')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0 font-size-18">Tenant Domains & Caddy SSL Routing</h4>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createDomainModal">
                <i class="fa-solid fa-plus me-1"></i> Attach Domain
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
                        <th>Domain Name</th>
                        <th>Tenant / Store</th>
                        <th>Domain Type</th>
                        <th>Primary</th>
                        <th>DNS Verified</th>
                        <th>Caddy SSL Status</th>
                        <th width="120" class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($domains as $dom)
                    <tr>
                        <td class="fw-bold">
                            <a href="http://{{ $dom->domain }}" target="_blank" class="text-primary">
                                {{ $dom->domain }} <i class="fa-solid fa-external-link ms-1 font-size-12"></i>
                            </a>
                        </td>
                        <td>{{ $dom->tenant->name ?? 'N/A' }}</td>
                        <td>
                            @if($dom->is_custom)
                                <span class="badge bg-purple text-white">Custom Domain</span>
                            @else
                                <span class="badge bg-secondary">Subdomain</span>
                            @endif
                        </td>
                        <td>
                            {!! $dom->is_primary ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-light text-dark">No</span>' !!}
                        </td>
                        <td>
                            {!! $dom->dns_verified ? '<span class="badge bg-success"><i class="fa-solid fa-check me-1"></i> Verified</span>' : '<span class="badge bg-warning text-dark"><i class="fa-solid fa-clock me-1"></i> Pending DNS</span>' !!}
                        </td>
                        <td>
                            @switch($dom->ssl_status)
                                @case('active')
                                    <span class="badge bg-success"><i class="fa-solid fa-lock me-1"></i> SSL Active</span>
                                    @break
                                @case('pending')
                                    <span class="badge bg-warning text-dark"><i class="fa-solid fa-spinner me-1"></i> Provisioning</span>
                                    @break
                                @case('failed')
                                    <span class="badge bg-danger"><i class="fa-solid fa-triangle-exclamation me-1"></i> SSL Failed</span>
                                    @break
                                @default
                                    <span class="badge bg-dark">{{ $dom->ssl_status }}</span>
                            @endswitch
                        </td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-info me-1" data-bs-toggle="modal" data-bs-target="#editDomainModal{{ $dom->id }}">
                                <i class="fa-solid fa-pen"></i>
                            </button>
                            <form action="{{ route('admin.domains.destroy', $dom->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this domain mapping?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger"><i class="fa-solid fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>

                    <!-- Edit Modal -->
                    <div class="modal fade" id="editDomainModal{{ $dom->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <form method="POST" action="{{ route('admin.domains.update', $dom->id) }}" class="modal-content">
                                @csrf
                                @method('PUT')
                                <div class="modal-header">
                                    <h5 class="modal-title">Edit Domain Mapping</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label">Domain Name</label>
                                        <input type="text" name="domain" class="form-control" value="{{ $dom->domain }}" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">SSL Provisioning Status</label>
                                        <select name="ssl_status" class="form-select" required>
                                            <option value="pending" {{ $dom->ssl_status == 'pending' ? 'selected' : '' }}>Pending</option>
                                            <option value="active" {{ $dom->ssl_status == 'active' ? 'selected' : '' }}>Active</option>
                                            <option value="failed" {{ $dom->ssl_status == 'failed' ? 'selected' : '' }}>Failed</option>
                                        </select>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="is_custom" id="customDomEdit{{ $dom->id }}" {{ $dom->is_custom ? 'checked' : '' }}>
                                        <label class="form-check-label" for="customDomEdit{{ $dom->id }}">Custom Domain (CNAME)</label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="is_primary" id="primaryDomEdit{{ $dom->id }}" {{ $dom->is_primary ? 'checked' : '' }}>
                                        <label class="form-check-label" for="primaryDomEdit{{ $dom->id }}">Set as Primary Domain</label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="dns_verified" id="dnsDomEdit{{ $dom->id }}" {{ $dom->dns_verified ? 'checked' : '' }}>
                                        <label class="form-check-label" for="dnsDomEdit{{ $dom->id }}">DNS Verified</label>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-primary">Update Domain</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">No tenant domain mappings found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Create Modal -->
<div class="modal fade" id="createDomainModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('admin.domains.store') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Attach New Domain</h5>
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
                    <label class="form-label">Domain Name</label>
                    <input type="text" name="domain" class="form-control" placeholder="mystore.com" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">SSL Provisioning Status</label>
                    <select name="ssl_status" class="form-select" required>
                        <option value="pending" selected>Pending</option>
                        <option value="active">Active</option>
                    </select>
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" name="is_custom" id="customDomCreate" checked>
                    <label class="form-check-label" for="customDomCreate">Custom Domain</label>
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" name="is_primary" id="primaryDomCreate" checked>
                    <label class="form-check-label" for="primaryDomCreate">Set as Primary Domain</label>
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" name="dns_verified" id="dnsDomCreate" checked>
                    <label class="form-check-label" for="dnsDomCreate">DNS Verified</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">Attach Domain</button>
            </div>
        </form>
    </div>
</div>
@endsection
