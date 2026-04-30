@extends('layouts.app')
@section('title', 'Create License')
@section('breadcrumb', 'Licenses / Create')
@section('content')
<div class="row justify-content-center">
<div class="col-lg-8">
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 pt-4">
        <h3 class="card-title h3 mb-1" style="color:#1a3c6b">Add New License Feature</h3>
  
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.licenses.store') }}">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">License Name (Feature) *</label>
                    <input type="text" id="license_name" name="license_name"
                        class="form-control @error('license_name') is-invalid @enderror"
                        value="{{ old('license_name') }}" required placeholder="e.g. GGM_ADVANCE">
                    @error('license_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Module Name *</label>
                    <input type="text" id="application_name" name="application_name"
                        class="form-control @error('application_name') is-invalid @enderror"
                        value="{{ old('application_name') }}" required placeholder="e.g. Geographix Discovery">
                    @error('application_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Version</label>
                    <input type="text" id="version" name="version"
                        class="form-control @error('version') is-invalid @enderror"
                        value="{{ old('version') }}" placeholder="e.g. 2024.1">
                    @error('version')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Total Seats *</label>
                    <input type="number" id="total_seats" name="total_seats"
                        class="form-control @error('total_seats') is-invalid @enderror"
                        value="{{ old('total_seats', 0) }}" required min="0">
                    @error('total_seats')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Status *</label>
                    <select id="status" name="status" class="form-select @error('status') is-invalid @enderror" required>
                        <option value="enable" {{ old('status') === 'enable' ? 'selected' : '' }}>Active (Enable)</option>
                        <option value="disable" {{ old('status') === 'disable' ? 'selected' : '' }}>Disabled</option>
                    </select>
                    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Expiry Date *</label>
                    <input type="date" id="expiry_date" name="expiry_date"
                        class="form-control @error('expiry_date') is-invalid @enderror"
                        value="{{ old('expiry_date') }}" required>
                    @error('expiry_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Vendor *</label>
                    <select id="vendor_id" name="vendor_id" class="form-select @error('vendor_id') is-invalid @enderror" required>
                        <option value="">— Select Vendor —</option>
                        @foreach($vendors as $vendor)
                        <option value="{{ $vendor->id }}" {{ old('vendor_id', request('vendor_id')) == $vendor->id ? 'selected' : '' }}>
                            {{ $vendor->name }}
                        </option>
                        @endforeach
                    </select>
                    @error('vendor_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">License Server</label>
                    <select id="license_server_id" name="license_server_id" class="form-select @error('license_server_id') is-invalid @enderror">
                        <option value="">— No Server —</option>
                        @foreach($servers as $server)
                        <option value="{{ $server->id }}"
                            {{ old('license_server_id', request('server_id')) == $server->id ? 'selected' : '' }}>
                            {{ $server->server_name }} ({{ $server->ip_address }})
                        </option>
                        @endforeach
                    </select>
                    @error('license_server_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Log File Path</label>
                    <input type="text" id="log_file_path" name="log_file_path"
                        class="form-control @error('log_file_path') is-invalid @enderror" placeholder="/var/log/flexlm/license.log"
                        value="{{ old('log_file_path') }}">
                    @error('log_file_path')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Technical Notes</label>
                    <textarea id="notes" name="notes" class="form-control" rows="2" placeholder="Details about this feature license...">{{ old('notes') }}</textarea>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">License Key <span class="text-muted">(optional)</span></label>
                    <textarea id="license_key" name="license_key" class="form-control font-monospace" rows="3" placeholder="Paste raw license key text here...">{{ old('license_key') }}</textarea>
                </div>
                <hr class="my-2">
                <div class="col-12 d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.licenses.index') }}" class="btn btn-ghost-secondary">Cancel</a>
                    <button type="submit" class="btn" style="background:#1a3c6b;color:#fff">
                        <i class="fas fa-plus me-1"></i> Create License Feature
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
</div>
</div>
@endsection
