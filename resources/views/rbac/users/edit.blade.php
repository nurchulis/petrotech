@extends('layouts.app')
@section('title', 'Edit User')
@section('breadcrumb', 'Administration / User Management / Edit')

@section('content')
<div class="row justify-content-center">
<div class="col-lg-8">
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 pt-4">
        <h3 class="card-title" style="color:#1a3c6b">Edit User — {{ $user->name }}</h3>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.users.update', $user) }}">
            @csrf @method('PUT')
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Full Name *</label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name', $user->name) }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Email *</label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                           value="{{ old('email', $user->email) }}" required>
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">New Password <span class="text-muted">(leave blank to keep current)</span></label>
                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror">
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Confirm Password</label>
                    <input type="password" name="password_confirmation" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Employee ID</label>
                    <input type="text" name="employee_id" class="form-control @error('employee_id') is-invalid @enderror"
                           value="{{ old('employee_id', $user->employee_id) }}">
                    @error('employee_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Department</label>
                    <input type="text" name="department" class="form-control @error('department') is-invalid @enderror"
                           value="{{ old('department', $user->department) }}">
                    @error('department')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Company</label>
                    <input type="text" name="company" id="user_company" class="form-control @error('company') is-invalid @enderror"
                           value="{{ old('company', $user->company) }}" placeholder="e.g. Pertamina EP, PHE, Patra Niaga"
                           list="company_edit_list" autocomplete="off">
                    <datalist id="company_edit_list">
                        @foreach($existingCompanies ?? [] as $c)
                            <option value="{{ $c }}">
                        @endforeach
                    </datalist>
                    @error('company')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Phone</label>
                    <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
                           value="{{ old('phone', $user->phone) }}">
                    @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Status</label>
                    <div class="form-check form-switch mt-2">
                        <input type="hidden" name="is_active" value="0">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1"
                               {{ old('is_active', $user->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label">Active</label>
                    </div>
                </div>

                {{-- Role Assignment (Admin and User only) --}}
                <div class="col-12">
                    <label class="form-label fw-semibold">Assign Roles *</label>
                    @error('roles')<div class="text-danger small mb-1">{{ $message }}</div>@enderror
                    <div class="d-flex flex-wrap gap-3">
                        @foreach($roles as $role)
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="roles[]" value="{{ $role->name }}"
                                   id="role_{{ $role->id }}"
                                   {{ in_array($role->name, old('roles', $user->getRoleNames()->toArray())) ? 'checked' : '' }}>
                            <label class="form-check-label" for="role_{{ $role->id }}">
                                {{ $role->display_name ?? ucfirst($role->name) }}
                            </label>
                        </div>
                        @endforeach
                    </div>
                    <small class="text-muted">Super Administrator role cannot be assigned or edited.</small>
                </div>

                {{-- License Management Access Assignment --}}
                <div class="col-12">
                    <label class="form-label fw-semibold">License Management Access (Assigned Vendors)</label>
                    <small class="text-muted d-block mb-2">Assign which vendor licenses this user is allowed to view and manage in License Management.</small>

                    <div id="company-empty-alert" class="text-muted small py-2" style="{{ !empty(old('company', $user->company)) ? 'display:none;' : '' }}">
                        No company specified. Please set user company above to assign license server.
                    </div>

                    <div id="no-matching-vendors-alert" class="text-muted small py-2" style="display:none;">
                        No license servers found for this company.
                    </div>

                    <div class="row g-2" id="vendors-container">
                        @forelse($vendors ?? [] as $v)
                        @php
                            $userCompany = trim(old('company', $user->company ?? ''));
                            $vCompany = trim($v->company ?? '');
                            $matchesCompany = !empty($userCompany) && !empty($vCompany) && strcasecmp($vCompany, $userCompany) === 0;
                            $assigned = in_array($v->id, old('vendor_ids', $user->accessibleVendors->pluck('id')->toArray()));
                        @endphp
                        <div class="col-md-6 vendor-card-item" data-company="{{ strtolower($vCompany) }}" style="{{ $matchesCompany ? '' : 'display:none;' }}">
                            <div class="border rounded p-2 d-flex align-items-center justify-content-between">
                                <div class="form-check mb-0">
                                    <input class="form-check-input vendor-checkbox" type="checkbox" name="vendor_ids[]"
                                        value="{{ $v->id }}" id="edit_vendor_{{ $v->id }}"
                                        {{ ($assigned && $matchesCompany) ? 'checked' : '' }}
                                        {{ $matchesCompany ? '' : 'disabled' }}>
                                    <label class="form-check-label fw-bold text-dark cursor-pointer" for="edit_vendor_{{ $v->id }}">
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
                        <div class="col-12 text-muted small">No vendors found on the platform.</div>
                        @endforelse
                    </div>
                </div>

                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn" style="background:#1a3c6b;color:#fff">Update User</button>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>
</div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const companyInput = document.getElementById('user_company');
    const vendorItems = document.querySelectorAll('.vendor-card-item');
    const emptyCompanyAlert = document.getElementById('company-empty-alert');
    const noMatchingAlert = document.getElementById('no-matching-vendors-alert');

    function filterVendorsByCompany() {
        const val = (companyInput ? companyInput.value : '').trim();
        const lowerVal = val.toLowerCase();

        if (!val) {
            if (emptyCompanyAlert) emptyCompanyAlert.style.display = '';
            if (noMatchingAlert) noMatchingAlert.style.display = 'none';

            vendorItems.forEach(item => {
                item.style.display = 'none';
                const cb = item.querySelector('.vendor-checkbox');
                if (cb) {
                    cb.checked = false;
                    cb.disabled = true;
                }
            });
            return;
        }

        if (emptyCompanyAlert) emptyCompanyAlert.style.display = 'none';

        let visibleCount = 0;
        vendorItems.forEach(item => {
            const itemCompany = (item.dataset.company || '').trim().toLowerCase();
            const cb = item.querySelector('.vendor-checkbox');

            if (itemCompany && itemCompany === lowerVal) {
                item.style.display = '';
                if (cb) cb.disabled = false;
                visibleCount++;
            } else {
                item.style.display = 'none';
                if (cb) {
                    cb.checked = false;
                    cb.disabled = true;
                }
            }
        });

        if (noMatchingAlert) {
            noMatchingAlert.style.display = (visibleCount === 0) ? '' : 'none';
        }
    }

    if (companyInput) {
        companyInput.addEventListener('input', filterVendorsByCompany);
        companyInput.addEventListener('change', filterVendorsByCompany);
        filterVendorsByCompany();
    }
});
</script>
@endpush

