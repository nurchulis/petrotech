@extends('layouts.app')
@section('title', 'Settings')
@section('breadcrumb', 'Settings')
@section('content')
<div class="row g-4">

    {{-- Profile Settings --}}
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 pt-4">
                <h4 class="card-title mb-0" style="color:#1a3c6b">👤 Profile Information</h4>
            </div>
            <div class="card-body">
                @if(session('success'))
                <div class="alert alert-success alert-dismissible mb-3">
                    {{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif
                <form method="POST" action="{{ route('settings.profile') }}">
                    @csrf
                    @method('PATCH')
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Full Name</label>
                        <input type="text" id="name" name="name"
                            class="form-control @error('name') is-invalid @enderror"
                            value="{{ old('name', $user->name) }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Email Address</label>
                        <input type="email" id="email" name="email"
                            class="form-control @error('email') is-invalid @enderror"
                            value="{{ old('email', $user->email) }}" required>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Department</label>
                        <input type="text" id="department" name="department"
                            class="form-control"
                            value="{{ old('department', $user->department) }}"
                            placeholder="e.g. Upstream IT">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Phone</label>
                        <input type="text" id="phone" name="phone"
                            class="form-control"
                            value="{{ old('phone', $user->phone) }}"
                            placeholder="+62 ...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-muted">Employee ID</label>
                        <input type="text" class="form-control" value="{{ $user->employee_id ?? '—' }}" disabled>
                        <small class="text-muted">Employee ID cannot be changed here.</small>
                    </div>
                    <button type="submit" class="btn" style="background:#1a3c6b;color:#fff">Save Profile</button>
                </form>
            </div>
        </div>
    </div>

    {{-- Password Change --}}
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 pt-4">
                <h4 class="card-title mb-0" style="color:#1a3c6b">🔒 Change Password</h4>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('settings.password') }}">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Current Password</label>
                        <input type="password" id="current_password" name="current_password"
                            class="form-control @error('current_password') is-invalid @enderror"
                            autocomplete="current-password">
                        @error('current_password')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">New Password</label>
                        <input type="password" id="password" name="password"
                            class="form-control @error('password') is-invalid @enderror"
                            autocomplete="new-password">
                        @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Minimum 8 characters.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Confirm New Password</label>
                        <input type="password" id="password_confirmation" name="password_confirmation"
                            class="form-control"
                            autocomplete="new-password">
                    </div>
                    <button type="submit" class="btn btn-warning">🔑 Update Password</button>
                </form>
            </div>
        </div>

        {{-- Account Info Card --}}
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-body">
                <h6 class="text-muted mb-3">Account Information</h6>
                <dl class="row mb-0" style="font-size:.86rem">
                    <dt class="col-5 text-muted">Role</dt>
                    <dd class="col-7">
                        @foreach($user->getRoleNames() as $role)
                        <span class="badge bg-primary-lt text-primary">{{ $role }}</span>
                        @endforeach
                    </dd>
                    <dt class="col-5 text-muted">Account Status</dt>
                    <dd class="col-7">
                        <span class="badge {{ $user->is_active ? 'bg-success' : 'bg-danger' }}-lt text-{{ $user->is_active ? 'success' : 'danger' }}">
                            {{ $user->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </dd>
                    <dt class="col-5 text-muted">Last Login</dt>
                    <dd class="col-7">{{ $user->last_login_at ? $user->last_login_at->format('d M Y H:i') : 'Never' }}</dd>
                    <dt class="col-5 text-muted">Member Since</dt>
                    <dd class="col-7">{{ $user->created_at->format('d M Y') }}</dd>
                </dl>
            </div>
        </div>
    </div>

    @role('super_admin')
    {{-- Menu Visibility Configuration (.env) --}}
    <div class="col-12">
        <div class="card border-0 shadow-sm mt-2">
            <div class="card-header bg-white border-0 pt-4 d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="card-title mb-0" style="color:#1a3c6b">🧭 Sidebar Menu Visibility Configuration</h4>
                    <small class="text-muted">Konfigurasi show / hide menu navigasi platform. Nilai akan disimpan langsung ke file <code>.env</code> tanpa merubah database.</small>
                </div>
                <span class="badge bg-purple-lt text-purple fw-bold">.env Config (Super Admin Only)</span>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('settings.menu') }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="row g-4">
                        {{-- Operations Section --}}
                        <div class="col-md-4">
                            <div class="border rounded p-3 h-100 bg-light-subtle">
                                <h6 class="text-uppercase fw-bold text-muted mb-3" style="letter-spacing: 0.05em;">
                                    <i class="fas fa-tasks me-1 text-primary"></i> Operations
                                </h6>
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" name="vdi_access" id="menu_vdi_access" value="1"
                                        {{ config('menu.vdi_access', true) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold" for="menu_vdi_access">VDI Access</label>
                                    <div class="text-muted small"><code>MENU_VDI_ACCESS</code></div>
                                </div>
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" name="ticketing" id="menu_ticketing" value="1"
                                        {{ config('menu.ticketing', true) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold" for="menu_ticketing">Ticketing</label>
                                    <div class="text-muted small"><code>MENU_TICKETING</code></div>
                                </div>
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox" name="license_management" id="menu_license_management" value="1"
                                        {{ config('menu.license_management', true) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold" for="menu_license_management">License Management</label>
                                    <div class="text-muted small"><code>MENU_LICENSE_MANAGEMENT</code></div>
                                </div>
                            </div>
                        </div>

                        {{-- Administration Section --}}
                        <div class="col-md-4">
                            <div class="border rounded p-3 h-100 bg-light-subtle">
                                <h6 class="text-uppercase fw-bold text-muted mb-3" style="letter-spacing: 0.05em;">
                                    <i class="fas fa-server me-1 text-primary"></i> Administration
                                </h6>
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" name="vm_monitoring" id="menu_vm_monitoring" value="1"
                                        {{ config('menu.vm_monitoring', true) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold" for="menu_vm_monitoring">VM Monitoring</label>
                                    <div class="text-muted small"><code>MENU_VM_MONITORING</code></div>
                                </div>
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" name="vm_management" id="menu_vm_management" value="1"
                                        {{ config('menu.vm_management', true) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold" for="menu_vm_management">VM Management</label>
                                    <div class="text-muted small"><code>MENU_VM_MANAGEMENT</code></div>
                                </div>
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" name="storage_monitor" id="menu_storage_monitor" value="1"
                                        {{ config('menu.storage_monitor', true) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold" for="menu_storage_monitor">Storage Monitor</label>
                                    <div class="text-muted small"><code>MENU_STORAGE_MONITOR</code></div>
                                </div>
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" name="analytics" id="menu_analytics" value="1"
                                        {{ config('menu.analytics', true) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold" for="menu_analytics">Analytics &amp; Reports</label>
                                    <div class="text-muted small"><code>MENU_ANALYTICS</code></div>
                                </div>
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox" name="group_management" id="menu_group_management" value="1"
                                        {{ config('menu.group_management', true) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold" for="menu_group_management">Group Management</label>
                                    <div class="text-muted small"><code>MENU_GROUP_MANAGEMENT</code></div>
                                </div>
                            </div>
                        </div>

                        {{-- System Section --}}
                        <div class="col-md-4">
                            <div class="border rounded p-3 h-100 bg-light-subtle">
                                <h6 class="text-uppercase fw-bold text-muted mb-3" style="letter-spacing: 0.05em;">
                                    <i class="fas fa-cogs me-1 text-primary"></i> System
                                </h6>
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" name="user_management" id="menu_user_management" value="1"
                                        {{ config('menu.user_management', true) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold" for="menu_user_management">User Management</label>
                                    <div class="text-muted small"><code>MENU_USER_MANAGEMENT</code></div>
                                </div>
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" name="role_management" id="menu_role_management" value="1"
                                        {{ config('menu.role_management', true) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold" for="menu_role_management">Role Management</label>
                                    <div class="text-muted small"><code>MENU_ROLE_MANAGEMENT</code></div>
                                </div>
                                <div class="p-2 rounded bg-white border text-muted small">
                                    <i class="fas fa-info-circle me-1 text-primary"></i> <strong>Dashboard</strong> dan <strong>Settings</strong> bersifat core dan akan selalu tampil untuk role yang berhak.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 pt-2 border-top d-flex gap-2">
                        <button type="submit" class="btn btn-primary" style="background:#1a3c6b">
                            <i class="fas fa-save me-1"></i> Save Menu Configuration (.env)
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endrole
</div>
@endsection
