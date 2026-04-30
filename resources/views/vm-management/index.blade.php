@extends('layouts.app')
@section('title', 'VM Management')
@section('breadcrumb', 'Administration / VM Management')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-0" style="color:#1a3c6b">VM Management</h2>
        <small class="text-muted">Create, edit, and manage virtual machines</small>
    </div>
    <a href="{{ route('admin.vm-management.create') }}" class="btn" style="background:#1a3c6b;color:#fff">+ Add VM</a>
</div>

{{-- Filters --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" class="d-flex gap-2 align-items-center flex-wrap">
            <input type="text" name="search" class="form-control form-control-sm" style="max-width:220px"
                   placeholder="Search VM name, app, IP..." value="{{ request('search') }}">
            <select name="status" class="form-select form-select-sm" style="max-width:140px">
                <option value="">All Status</option>
                <option value="running" {{ request('status')=='running'?'selected':'' }}>Running</option>
                <option value="stopped" {{ request('status')=='stopped'?'selected':'' }}>Stopped</option>
                <option value="paused" {{ request('status')=='paused'?'selected':'' }}>Paused</option>
            </select>
            <select name="region" class="form-select form-select-sm" style="max-width:150px">
                <option value="">All Regions</option>
                @foreach($regions as $region)
                <option value="{{ $region }}" {{ request('region')==$region?'selected':'' }}>{{ $region }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-sm btn-outline-primary">Filter</button>
            <a href="{{ route('admin.vm-management.index') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-vcenter card-table">
            <thead style="background:#f4f7fb">
                <tr>
                    <th>VM Name</th>
                    <th>Application</th>
                    <th>OS</th>
                    <th>Region</th>
                    <th>IP Address</th>
                    <th>Specs</th>
                    <th>Assigned To</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($vms as $vm)
                <tr>
                    <td><strong>{{ $vm->vm_name }}</strong></td>
                    <td>{{ $vm->application_name }}</td>
                    <td><small class="text-muted">{{ $vm->os_type }}</small></td>
                    <td>{{ $vm->region ?? '—' }}</td>
                    <td><code>{{ $vm->ip_address ?? '—' }}</code></td>
                    <td>
                        <small class="text-muted">
                            {{ $vm->cpu_cores ?? '—' }} vCPU · {{ $vm->ram_gb ?? '—' }} GB
                            @if($vm->has_gpu) · GPU @endif
                        </small>
                    </td>
                    <td>{{ $vm->assignedUser?->name ?? '—' }}</td>
                    <td>
                        <span class="badge bg-{{ $vm->status_badge }}-lt text-{{ $vm->status_badge }}">
                            {{ ucfirst($vm->status) }}
                        </span>
                    </td>
                    <td class="text-end">
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('admin.vm-management.show', $vm) }}" class="btn btn-outline-secondary">View</a>
                            <a href="{{ route('admin.vm-management.edit', $vm) }}" class="btn btn-outline-primary">Edit</a>
                            <form method="POST" action="{{ route('admin.vm-management.destroy', $vm) }}" onsubmit="return confirm('Delete this VM and all its metrics?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" class="text-center text-muted py-4">No virtual machines found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($vms->hasPages())
    <div class="card-footer">{{ $vms->withQueryString()->links() }}</div>
    @endif
</div>
@endsection
