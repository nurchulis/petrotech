@extends('layouts.app')
@section('title', 'License Access — ' . $user->name)
@section('breadcrumb', 'Administration / License Access / ' . $user->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-0" style="color:#1a3c6b">License Access — {{ $user->name }}</h2>
        <small class="text-muted">
            Company: <strong>{{ $user->company ?? 'No Company Assigned' }}</strong> • 
            Assign vendor licenses this user is allowed to access at <code>/admin/licenses</code>
        </small>
    </div>
    <a href="{{ route('admin.users.show', $user) }}" class="btn btn-sm btn-outline-secondary">← Back to User</a>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0">
                <h4 class="card-title mb-0">Assigned Vendor Licenses</h4>
                <small class="text-muted">Check the vendors that {{ $user->name }} is permitted to access</small>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.license-access.user.sync', $user) }}">
                    @csrf
                    <div class="mb-3">
                        <input type="text" id="search-vendors" class="form-control form-control-sm" placeholder="Search vendors or companies...">
                    </div>

                    <div class="mb-4" id="vendors-list" style="max-height:450px;overflow-y:auto">
                        @forelse($allVendors as $vendor)
                        @php
                            $isAssigned = $user->accessibleVendors->contains('id', $vendor->id);
                        @endphp
                        <label class="form-check border rounded p-3 mb-2 d-flex align-items-center justify-content-between cursor-pointer hover-bg-light">
                            <div class="d-flex align-items-center">
                                <input type="checkbox" name="vendor_ids[]" value="{{ $vendor->id }}"
                                       class="form-check-input me-3"
                                       {{ $isAssigned ? 'checked' : '' }}>
                                <div>
                                    <div class="fw-bold text-dark fs-5">
                                        {{ $vendor->name }}
                                        @if($vendor->company)
                                            <span class="badge bg-blue-lt text-blue ms-1">{{ $vendor->company }}</span>
                                        @endif
                                    </div>
                                    <div class="text-muted small">
                                        Server: <code>{{ $vendor->name_server ?? optional($vendor->server)->hostname ?? 'N/A' }}</code> • 
                                        <strong>{{ $vendor->licenses_count ?? $vendor->licenses->count() }}</strong> Features
                                    </div>
                                </div>
                            </div>
                            <span class="badge bg-{{ $vendor->status == 'enable' ? 'success' : 'danger' }}-lt">
                                {{ strtoupper($vendor->status) }}
                            </span>
                        </label>
                        @empty
                        <p class="text-muted mb-0">No vendors found on the platform.</p>
                        @endforelse
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn" style="background:#1a3c6b;color:#fff">
                            <i class="fas fa-save me-1"></i> Save License Access
                        </button>
                        <a href="{{ route('admin.users.show', $user) }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Info Card --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white border-0">
                <h4 class="card-title mb-0">User Summary</h4>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <img src="{{ $user->avatar_url }}" class="rounded-circle" width="48" height="48" alt="">
                    <div>
                        <h4 class="mb-0">{{ $user->name }}</h4>
                        <div class="text-muted small">{{ $user->email }}</div>
                    </div>
                </div>
                <ul class="list-unstyled mb-0">
                    <li class="mb-2"><strong>Company:</strong> <span class="badge bg-blue-lt">{{ $user->company ?? 'None' }}</span></li>
                    <li class="mb-2"><strong>Employee ID:</strong> {{ $user->employee_id ?? '—' }}</li>
                    <li class="mb-2"><strong>Department:</strong> {{ $user->department ?? '—' }}</li>
                    <li>
                        <strong>Roles:</strong>
                        @foreach($user->roles as $role)
                            <span class="badge bg-primary-lt">{{ $role->display_name ?? ucfirst($role->name) }}</span>
                        @endforeach
                    </li>
                </ul>
            </div>
        </div>

        <div class="card border-0 shadow-sm bg-light-subtle">
            <div class="card-body">
                <h5 class="fw-bold mb-2"><i class="fas fa-info-circle text-primary me-1"></i> How It Works</h5>
                <p class="text-muted small mb-2">
                    When this user logs in with their account and opens <strong>License Management</strong> (<code>admin/licenses</code>):
                </p>
                <ul class="text-muted small ps-3 mb-0">
                    <li>Only the vendor licenses checked on the left will be visible to this user.</li>
                    <li>Infographics and health statistics will reflect only these assigned vendors.</li>
                    <li>Direct URL access to non-assigned vendors is strictly prohibited (403 Forbidden).</li>
                    <li>Admins and Super Admins always retain full access across all vendors.</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const input = document.getElementById('search-vendors');
        const list = document.getElementById('vendors-list');
        if(input && list) {
            input.addEventListener('input', function() {
                const term = this.value.toLowerCase();
                const labels = list.querySelectorAll('label.form-check');
                labels.forEach(label => {
                    const text = label.textContent.toLowerCase();
                    label.style.display = text.includes(term) ? '' : 'none';
                });
            });
        }
    });
</script>
@endsection
