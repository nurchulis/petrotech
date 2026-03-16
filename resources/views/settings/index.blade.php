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
</div>
@endsection
