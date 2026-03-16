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
                    <th>License Name</th>
                    <th>Application</th>
                    <th>Server</th>
                    <th>Expiry Date</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($licenses as $license)
                <tr>
                    <td>
                        <strong>{{ $license->license_name }}</strong>
                        @if($license->isExpired())
                            <span class="badge bg-danger-lt text-danger ms-1">EXPIRED</span>
                        @endif
                    </td>
                    <td>{{ $license->application_name }}</td>
                    <td>{{ $license->server?->server_name ?? '—' }}</td>
                    <td>
                        <span class="badge bg-{{ $license->expiry_badge }}-lt text-{{ $license->expiry_badge }}">
                            {{ $license->expiry_date->format('d M Y') }}
                        </span>
                        @if(!$license->isExpired())
                            <small class="text-muted">({{ $license->days_until_expiry }}d)</small>
                        @endif
                    </td>
                    <td>
                        <form method="POST" action="{{ route('admin.licenses.toggle', $license) }}" class="d-inline">
                            @csrf
                            <button type="submit" class="badge border-0 bg-{{ $license->status === 'enable' ? 'success' : 'secondary' }}-lt text-{{ $license->status === 'enable' ? 'success' : 'secondary' }}" title="Click to toggle">
                                {{ $license->status === 'enable' ? 'Active' : 'Disabled' }}
                            </button>
                        </form>
                    </td>
                    <td class="text-end">
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('admin.licenses.show', $license) }}" class="btn btn-outline-secondary">View</a>
                            @can('update', $license)
                            <a href="{{ route('admin.licenses.edit', $license) }}" class="btn btn-outline-primary">Edit</a>
                            @endcan
                            @can('delete', $license)
                            <form method="POST" action="{{ route('admin.licenses.destroy', $license) }}" onsubmit="return confirm('Delete this license?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger">Delete</button>
                            </form>
                            @endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No licenses found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($licenses->hasPages())
    <div class="card-footer">{{ $licenses->withQueryString()->links() }}</div>
    @endif
</div>
@endsection
