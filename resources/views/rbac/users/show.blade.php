@extends('layouts.app')
@section('title', $user->name . ' — User Detail')
@section('breadcrumb', 'Administration / User Management / ' . $user->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-0" style="color:#1a3c6b">{{ $user->name }}</h2>
        <small class="text-muted">{{ $user->email }}</small>
    </div>
    <div class="d-flex gap-2">
        <span class="badge bg-{{ $user->is_active ? 'success' : 'secondary' }} p-2">
            {{ $user->is_active ? 'Active' : 'Inactive' }}
        </span>
        @can('update', $user)
        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-outline-primary">Edit</a>
        @endcan
        <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-secondary">← Back</a>
    </div>
</div>

<div class="row g-3">
    {{-- Profile Information --}}
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0">
                <h4 class="card-title mb-0">Profile Information</h4>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <img src="{{ $user->avatar_url }}" class="rounded-circle" width="56" height="56" alt="">
                    <div>
                        <h3 class="mb-0">{{ $user->name }}</h3>
                        <div class="text-muted">{{ $user->email }}</div>
                    </div>
                </div>
                <table class="table table-borderless mb-0">
                    <tr>
                        <td class="text-muted" style="width:40%">Employee ID</td>
                        <td>{{ $user->employee_id ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Department</td>
                        <td>{{ $user->department ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Phone</td>
                        <td>{{ $user->phone ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Status</td>
                        <td>
                            <span class="badge bg-{{ $user->is_active ? 'success' : 'secondary' }}-lt text-{{ $user->is_active ? 'success' : 'secondary' }}">
                                {{ $user->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Joined</td>
                        <td>{{ $user->created_at->format('d M Y, H:i') }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    {{-- Roles --}}
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0">
                <h4 class="card-title mb-0">Assigned Roles</h4>
            </div>
            <div class="card-body">
                @forelse($user->roles as $role)
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge bg-primary-lt text-primary p-2">{{ $role->display_name ?? ucfirst($role->name) }}</span>
                    @if($role->description)
                    <small class="text-muted">{{ $role->description }}</small>
                    @endif
                </div>
                @empty
                <p class="text-muted mb-0">No roles assigned.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
