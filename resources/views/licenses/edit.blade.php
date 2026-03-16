@extends('layouts.app')
@section('title', 'Edit License – ' . $license->license_name)
@section('breadcrumb', 'Licenses / Edit / ' . $license->license_name)
@section('content')
<div class="row justify-content-center">
<div class="col-lg-8">
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 pt-4">
        <h3 class="card-title" style="color:#1a3c6b">Edit License</h3>
        <small class="text-muted">{{ $license->license_name }}</small>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.licenses.update', $license) }}">
            @csrf
            @method('PUT')
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">License Name *</label>
                    <input type="text" id="license_name" name="license_name"
                        class="form-control @error('license_name') is-invalid @enderror"
                        value="{{ old('license_name', $license->license_name) }}" required>
                    @error('license_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Application Name *</label>
                    <input type="text" id="application_name" name="application_name"
                        class="form-control @error('application_name') is-invalid @enderror"
                        value="{{ old('application_name', $license->application_name) }}" required>
                    @error('application_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Status *</label>
                    <select id="status" name="status" class="form-select">
                        <option value="enable"  {{ old('status', $license->status) === 'enable'  ? 'selected' : '' }}>Active (Enable)</option>
                        <option value="disable" {{ old('status', $license->status) === 'disable' ? 'selected' : '' }}>Disabled</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Expiry Date *</label>
                    <input type="date" id="expiry_date" name="expiry_date"
                        class="form-control @error('expiry_date') is-invalid @enderror"
                        value="{{ old('expiry_date', $license->expiry_date?->format('Y-m-d')) }}" required>
                    @error('expiry_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">License Server</label>
                    <select id="license_server_id" name="license_server_id" class="form-select">
                        <option value="">— None —</option>
                        @foreach($servers as $server)
                        <option value="{{ $server->id }}"
                            {{ old('license_server_id', $license->license_server_id) == $server->id ? 'selected' : '' }}>
                            {{ $server->server_name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Log File Path</label>
                    <input type="text" id="log_file_path" name="log_file_path"
                        class="form-control" placeholder="/var/log/flexlm/license.log"
                        value="{{ old('log_file_path', $license->log_file_path) }}">
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Notes</label>
                    <textarea id="notes" name="notes" class="form-control" rows="2">{{ old('notes', $license->notes) }}</textarea>
                </div>
                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn" style="background:#1a3c6b;color:#fff">Save Changes</button>
                    <a href="{{ route('admin.licenses.show', $license) }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>
</div>
</div>
@endsection
