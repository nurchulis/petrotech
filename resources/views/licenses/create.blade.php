@extends('layouts.app')
@section('title', 'Create License')
@section('breadcrumb', 'Licenses / Create')
@section('content')
<div class="row justify-content-center">
<div class="col-lg-8">
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 pt-4">
        <h3 class="card-title" style="color:#1a3c6b">Add New License</h3>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.licenses.store') }}">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">License Name *</label>
                    <input type="text" id="license_name" name="license_name" class="form-control @error('license_name') is-invalid @enderror" value="{{ old('license_name') }}" required>
                    @error('license_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Application Name *</label>
                    <input type="text" id="application_name" name="application_name" class="form-control" value="{{ old('application_name') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Status *</label>
                    <select id="status" name="status" class="form-select">
                        <option value="enable">Active (Enable)</option>
                        <option value="disable">Disabled</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Expiry Date *</label>
                    <input type="date" id="expiry_date" name="expiry_date" class="form-control" value="{{ old('expiry_date') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">License Server</label>
                    <select id="license_server_id" name="license_server_id" class="form-select">
                        <option value="">— None —</option>
                        @foreach($servers as $server)
                        <option value="{{ $server->id }}" {{ old('license_server_id')==$server->id?'selected':'' }}>{{ $server->server_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Log File Path</label>
                    <input type="text" id="log_file_path" name="log_file_path" class="form-control" value="{{ old('log_file_path') }}" placeholder="/var/log/flexlm/license.log">
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">License Key <span class="text-muted">(optional)</span></label>
                    <textarea id="license_key" name="license_key" class="form-control font-monospace" rows="3" placeholder="LICENSE KEY HERE...">{{ old('license_key') }}</textarea>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Notes</label>
                    <textarea id="notes" name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                </div>
                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn" style="background:#1a3c6b;color:#fff">Save License</button>
                    <a href="{{ route('admin.licenses.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>
</div>
</div>
@endsection
