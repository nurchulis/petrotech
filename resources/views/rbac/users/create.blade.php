@extends('layouts.app')
@section('title', 'Add User')
@section('breadcrumb', 'Administration / User Management / Create')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 pt-4">
                <h3 class="card-title" style="color:#1a3c6b">Add New User</h3>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.users.store') }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Full Name *</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name') }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Email *</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                value="{{ old('email') }}" required>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Password *</label>
                            <input type="password" name="password"
                                class="form-control @error('password') is-invalid @enderror" required>
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Confirm Password *</label>
                            <input type="password" name="password_confirmation" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Employee ID</label>
                            <input type="text" name="employee_id"
                                class="form-control @error('employee_id') is-invalid @enderror"
                                value="{{ old('employee_id') }}">
                            @error('employee_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Department</label>
                            <input type="text" name="department"
                                class="form-control @error('department') is-invalid @enderror"
                                value="{{ old('department') }}">
                            @error('department')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Company</label>
                            <input type="text" name="company"
                                class="form-control @error('company') is-invalid @enderror"
                                value="{{ old('company') }}" placeholder="e.g. Pertamina EP, PHE, Patra Niaga"
                                list="company_list">
                            <datalist id="company_list">
                                @foreach($existingCompanies ?? [] as $c)
                                    <option value="{{ $c }}">
                                @endforeach
                            </datalist>
                            @error('company')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Phone</label>
                            <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
                                value="{{ old('phone') }}">
                            @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Status</label>
                            <div class="form-check form-switch mt-2">
                                <input type="hidden" name="is_active" value="0">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" {{
                                    old('is_active', '1' )=='1' ? 'checked' : '' }}>
                                <label class="form-check-label">Active</label>
                            </div>
                        </div>

                        {{-- Role Assignment --}}
                        <div class="col-12">
                            <label class="form-label fw-semibold">Assign Roles *</label>
                            @error('roles')<div class="text-danger small mb-1">{{ $message }}</div>@enderror
                            <div class="d-flex flex-wrap gap-3">
                                @foreach($roles as $role)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="roles[]"
                                        value="{{ $role->name }}" id="role_{{ $role->id }}" {{ in_array($role->name,
                                    old('roles', [])) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="role_{{ $role->id }}">
                                        {{ $role->display_name ?? ucfirst($role->name) }}
                                    </label>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- License Management Access Assignment --}}
                        <div class="col-12">
                            <label class="form-label fw-semibold">License Management Access (Assigned Vendors)</label>
                            <small class="text-muted d-block mb-2">Assign which vendor licenses this user is allowed to view and manage in License Management.</small>
                            <div class="row g-2">
                                @forelse($vendors ?? [] as $v)
                                <div class="col-md-6">
                                    <div class="border rounded p-2 d-flex align-items-center justify-content-between">
                                        <div class="form-check mb-0">
                                            <input class="form-check-input" type="checkbox" name="vendor_ids[]"
                                                value="{{ $v->id }}" id="vendor_{{ $v->id }}"
                                                {{ in_array($v->id, old('vendor_ids', [])) ? 'checked' : '' }}>
                                            <label class="form-check-label fw-bold text-dark cursor-pointer" for="vendor_{{ $v->id }}">
                                                {{ $v->name }}
                                                @if($v->company)
                                                    <span class="badge bg-blue-lt text-blue ms-1">{{ $v->company }}</span>
                                                @endif
                                            </label>
                                            <div class="small text-muted">{{ $v->licenses_count ?? 0 }} features • Server: {{ $v->server->server_name ?? 'N/A' }}</div>
                                        </div>
                                        <span class="badge bg-{{ $v->status == 'enable' ? 'success' : 'danger' }}-lt">
                                            {{ strtoupper($v->status) }}
                                        </span>
                                    </div>
                                </div>
                                @empty
                                <div class="col-12 text-muted small">No vendors found to assign.</div>
                                @endforelse
                            </div>
                        </div>

                        <div class="col-12 d-flex gap-2">
                            <button type="submit" class="btn" style="background:#1a3c6b;color:#fff">Create User</button>
                            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection