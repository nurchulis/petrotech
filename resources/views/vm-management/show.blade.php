@extends('layouts.app')
@section('title', $vm->vm_name . ' — VM Detail')
@section('breadcrumb', 'Administration / VM Management / ' . $vm->vm_name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-0" style="color:#1a3c6b">{{ $vm->vm_name }}</h2>
        <small class="text-muted">{{ $vm->application_name }} · {{ $vm->os_type }}</small>
    </div>
    <div class="d-flex gap-2">
        <span class="badge bg-{{ $vm->status_badge }} p-2">{{ ucfirst($vm->status) }}</span>
        <a href="{{ route('admin.vm-management.edit', $vm) }}" class="btn btn-sm btn-outline-primary">Edit</a>
        <a href="{{ route('admin.vm-management.index') }}" class="btn btn-sm btn-outline-secondary">← Back</a>
    </div>
</div>

<div class="row g-3">
    {{-- Basic Information --}}
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0">
                <h4 class="card-title mb-0">Basic Information</h4>
            </div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr>
                        <td class="text-muted" style="width:40%">VM Name</td>
                        <td><strong>{{ $vm->vm_name }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Application</td>
                        <td>{{ $vm->application_name }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">OS Type</td>
                        <td>{{ $vm->os_type }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Status</td>
                        <td>
                            <span class="badge bg-{{ $vm->status_badge }}-lt text-{{ $vm->status_badge }}">
                                {{ ucfirst($vm->status) }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Assigned User</td>
                        <td>{{ $vm->assignedUser?->name ?? '—' }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    {{-- Infrastructure --}}
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0">
                <h4 class="card-title mb-0">Infrastructure</h4>
            </div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr>
                        <td class="text-muted" style="width:40%">Region</td>
                        <td>{{ $vm->region ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Data Center</td>
                        <td>{{ $vm->data_center ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">IP Address</td>
                        <td><code>{{ $vm->ip_address ?? '—' }}</code></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Host Server</td>
                        <td>{{ $vm->host_server ?? '—' }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    {{-- Hardware Specs --}}
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0">
                <h4 class="card-title mb-0">Hardware Specifications</h4>
            </div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr>
                        <td class="text-muted" style="width:40%">CPU Cores</td>
                        <td>{{ $vm->cpu_cores ?? '—' }} vCPU</td>
                    </tr>
                    <tr>
                        <td class="text-muted">RAM</td>
                        <td>{{ $vm->ram_gb ?? '—' }} GB</td>
                    </tr>
                    <tr>
                        <td class="text-muted">GPU</td>
                        <td>
                            @if($vm->has_gpu)
                                <span class="badge bg-success-lt text-success">Yes</span>
                                {{ $vm->gpu_model ?? '' }}
                            @else
                                <span class="badge bg-secondary-lt text-secondary">No</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    {{-- Notes --}}
    @if($vm->notes)
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0">
                <h4 class="card-title mb-0">Notes</h4>
            </div>
            <div class="card-body">
                <p class="text-muted mb-0">{{ $vm->notes }}</p>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
