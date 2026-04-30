@extends('layouts.app')
@section('title', 'Add VM')
@section('breadcrumb', 'Administration / VM Management / Create')

@section('content')
<div class="row justify-content-center">
<div class="col-lg-10">
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 pt-4">
        <h3 class="card-title" style="color:#1a3c6b">Add New Virtual Machine</h3>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.vm-management.store') }}">
            @csrf
            <div class="row g-3">
                {{-- Basic Info --}}
                <div class="col-md-6">
                    <label class="form-label fw-semibold">VM Name *</label>
                    <input type="text" name="vm_name" class="form-control @error('vm_name') is-invalid @enderror"
                           value="{{ old('vm_name') }}" placeholder="e.g. VM-PETREL-01" required>
                    @error('vm_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Application Name *</label>
                    <input type="text" name="application_name" class="form-control @error('application_name') is-invalid @enderror"
                           value="{{ old('application_name') }}" placeholder="e.g. Petrel" required>
                    @error('application_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">OS Type *</label>
                    <input type="text" name="os_type" class="form-control @error('os_type') is-invalid @enderror"
                           value="{{ old('os_type') }}" placeholder="e.g. Windows Server 2022" required>
                    @error('os_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Status *</label>
                    <select name="status" class="form-select @error('status') is-invalid @enderror">
                        <option value="stopped" {{ old('status')=='stopped'?'selected':'' }}>Stopped</option>
                        <option value="running" {{ old('status')=='running'?'selected':'' }}>Running</option>
                        <option value="paused" {{ old('status')=='paused'?'selected':'' }}>Paused</option>
                    </select>
                    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Assigned User</label>
                    <select name="assigned_user_id" class="form-select">
                        <option value="">— None —</option>
                        @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ old('assigned_user_id')==$user->id?'selected':'' }}>{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Infrastructure --}}
                <div class="col-12"><hr class="my-1"><small class="text-muted text-uppercase fw-semibold">Infrastructure</small></div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Region</label>
                    <input type="text" name="region" class="form-control" value="{{ old('region') }}" placeholder="e.g. Jakarta">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Data Center</label>
                    <input type="text" name="data_center" class="form-control" value="{{ old('data_center') }}" placeholder="e.g. DC-JKT-01">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">IP Address</label>
                    <input type="text" name="ip_address" class="form-control" value="{{ old('ip_address') }}" placeholder="e.g. 10.0.1.50">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Host Server</label>
                    <input type="text" name="host_server" class="form-control" value="{{ old('host_server') }}" placeholder="e.g. ESXi-Host-03">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">CPU Cores</label>
                    <input type="number" name="cpu_cores" class="form-control" value="{{ old('cpu_cores') }}" min="1">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">RAM (GB)</label>
                    <input type="number" name="ram_gb" class="form-control" value="{{ old('ram_gb') }}" min="1">
                </div>

                {{-- GPU --}}
                <div class="col-md-4">
                    <label class="form-label fw-semibold">GPU</label>
                    <div class="form-check form-switch mt-2">
                        <input type="hidden" name="has_gpu" value="0">
                        <input class="form-check-input" type="checkbox" name="has_gpu" value="1"
                               id="has_gpu" {{ old('has_gpu') ? 'checked' : '' }}>
                        <label class="form-check-label" for="has_gpu">Has GPU</label>
                    </div>
                </div>
                <div class="col-md-8">
                    <label class="form-label fw-semibold">GPU Model</label>
                    <input type="text" name="gpu_model" class="form-control" value="{{ old('gpu_model') }}" placeholder="e.g. NVIDIA Tesla T4">
                </div>

                {{-- Notes --}}
                <div class="col-12">
                    <label class="form-label fw-semibold">Notes</label>
                    <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                </div>

                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn" style="background:#1a3c6b;color:#fff">Create VM</button>
                    <a href="{{ route('admin.vm-management.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>
</div>
</div>
@endsection
