@extends('layouts.app')
@section('title', 'Group VMs — ' . $group->name)
@section('breadcrumb', 'Administration / Group Management / ' . $group->name . ' / VMs')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-0" style="color:#1a3c6b">VM Access for {{ $group->name }}</h2>
        <small class="text-muted">Manage Virtual Machines assigned to this group</small>
    </div>
    <a href="{{ route('admin.groups.index') }}" class="btn btn-sm btn-outline-secondary">← Back to Groups</a>
</div>

{{-- Add VM Form --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.groups.vms.add', $group) }}" class="d-flex flex-wrap gap-3 align-items-end">
            @csrf
            <div class="flex-grow-1" style="min-width: 250px;">
                <label class="form-label fw-semibold">Grant Access to VMs</label>
                <select id="vm-select" name="vm_ids[]" class="form-select" multiple required>
                    <option value="">-- Select VMs --</option>
                    @foreach($availableVms as $vm)
                        <option value="{{ $vm->id }}">{{ $vm->vm_name }} ({{ $vm->application_name ?? 'No App' }})</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn" style="background:#1a3c6b;color:#fff">+ Add VM Access</button>
        </form>
    </div>
</div>

{{-- Current VMs Table --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0">
        <h3 class="card-title">Assigned VMs ({{ $vms->total() }})</h3>
    </div>
    <div class="table-responsive">
        <table class="table table-vcenter card-table">
            <thead style="background:#f4f7fb">
                <tr>
                    <th>VM Name</th>
                    <th>Application</th>
                    <th>Status</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($vms as $vm)
                <tr>
                    <td><strong>{{ $vm->vm_name }}</strong></td>
                    <td class="text-muted">{{ $vm->application_name ?? '—' }}</td>
                    <td>
                        <span class="badge bg-{{ $vm->status_badge }}-lt text-{{ $vm->status_badge }}">{{ $vm->status }}</span>
                    </td>
                    <td class="text-end">
                        <form method="POST" action="{{ route('admin.groups.vms.remove', [$group, $vm]) }}" onsubmit="return confirm('Remove VM access from this group?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">Remove</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center text-muted py-4">No VMs assigned.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($vms->hasPages())
    <div class="card-footer bg-white border-0 pt-0">
        {{ $vms->links() }}
    </div>
    @endif
</div>

<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        new TomSelect("#vm-select", {
            plugins: ['remove_button'],
            placeholder: 'Search and select VMs...',
        });
    });
</script>
@endsection
