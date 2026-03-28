@extends('layouts.app')
@section('title', 'License Management')
@section('breadcrumb', 'Administration / License Management')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-0" style="color:#1a3c6b">License Management</h2>
        <small class="text-muted">Manage petrotechnical software licenses</small>
    </div>
    @can('create', \App\Models\License::class)
    <a href="{{ route('admin.licenses.create') }}" class="btn" style="background:#1a3c6b;color:#fff">+ Add License</a>
    @endcan
</div>

{{-- Expiry Warnings --}}
@if($expiring->isNotEmpty())
<div class="alert alert-warning mb-4">
    <strong>⚠ Expiring within 30 days:</strong>
    @foreach($expiring as $l)
        <span class="badge bg-warning-lt text-warning ms-1">{{ $l->license_name }} ({{ $l->expiry_date->format('d M Y') }})</span>
    @endforeach
</div>
@endif

{{-- Filters --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" class="d-flex gap-2 align-items-center flex-wrap">
            <input type="text" name="search" class="form-control form-control-sm" style="max-width:220px" placeholder="Search license / app..." value="{{ request('search') }}">
            <select name="status" class="form-select form-select-sm" style="max-width:150px">
                <option value="">All Status</option>
                <option value="enable" {{ request('status')=='enable'?'selected':'' }}>Active</option>
                <option value="disable" {{ request('status')=='disable'?'selected':'' }}>Disabled</option>
            </select>
            <button type="submit" class="btn btn-sm btn-outline-primary">Filter</button>
            <a href="{{ route('admin.licenses.index') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-vcenter card-table">
            <thead style="background:#f4f7fb">
                <tr>
                    <th>Vendor Name</th>
                    <th>Server</th>
                    <th class="text-center">Total Feature</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($vendors as $v)
                <tr>
                    <td>
                        <strong class="text-primary">{{ $v->vendor }}</strong>
                    </td>
                    <td>
                        <code>{{ $v->server?->port }}@ {{ $v->server?->server_name }}</code>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-blue-lt text-blue">{{ $v->features_count }}</span>
                    </td>
                    <td>
                        <span class="badge bg-success-lt text-success">UP</span>
                    </td>
                    <td class="text-end">
                        <a href="{{ route('admin.licenses.vendor', [$v->license_server_id, $v->vendor]) }}" class="btn btn-sm btn-outline-primary">
                            View Detail
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center text-muted py-4">No vendors found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($vendors->hasPages())
    <div class="card-footer">{{ $vendors->withQueryString()->links() }}</div>
    @endif
</div>
@endsection
