@extends('platform_admin.layout.master')

@section('contents')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0 font-size-18">Newsletter Subscribers</h4>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="60">#</th>
                        <th>Email Address</th>
                        <th>Subscription Status</th>
                        <th>Subscribed Date</th>
                        <th width="140" class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($subscribers as $sub)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td class="fw-bold"><a href="mailto:{{ $sub->email }}">{{ $sub->email }}</a></td>
                        <td>
                            @if($sub->is_subscribed)
                                <span class="badge bg-success"><i class="fa-solid fa-check me-1"></i> Subscribed</span>
                            @else
                                <span class="badge bg-secondary"><i class="fa-solid fa-ban me-1"></i> Unsubscribed</span>
                            @endif
                        </td>
                        <td>{{ $sub->subscribed_at ? $sub->subscribed_at->format('M d, Y H:i') : ($sub->created_at ? $sub->created_at->format('M d, Y H:i') : '-') }}</td>
                        <td class="text-center">
                            <form action="{{ route('admin.newsletter.toggle', $sub->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-warning me-1" title="Toggle Subscription Status">
                                    <i class="fa-solid fa-rotate"></i>
                                </button>
                            </form>
                            <form action="{{ route('admin.newsletter.destroy', $sub->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Remove subscriber?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger"><i class="fa-solid fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">No newsletter subscribers found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
