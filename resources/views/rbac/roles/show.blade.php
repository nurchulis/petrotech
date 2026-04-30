@extends('layouts.app')
@section('title', $role->name . ' — Role Detail')
@section('breadcrumb', 'System / Role Management / ' . ucfirst($role->name))

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-0" style="color:#1a3c6b">{{ $role->display_name ?? ucfirst($role->name) }}</h2>
        <small class="text-muted">{{ $role->description ?? 'No description' }}</small>
    </div>
    <div class="d-flex gap-2">
        @can('update', $role)
        <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-sm btn-outline-primary">Edit</a>
        @endcan
        <a href="{{ route('admin.roles.index') }}" class="btn btn-sm btn-outline-secondary">← Back</a>
    </div>
</div>

<div class="row g-3">
    {{-- Role Info --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0">
                <h4 class="card-title mb-0">Role Details</h4>
            </div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr>
                        <td class="text-muted" style="width:45%">System Name</td>
                        <td><code>{{ $role->name }}</code></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Display Name</td>
                        <td>{{ $role->display_name ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Description</td>
                        <td>{{ $role->description ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Users</td>
                        <td><span class="badge bg-blue-lt">{{ $role->users->count() }}</span></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Permissions</td>
                        <td><span class="badge bg-purple-lt">{{ $role->permissions->count() }}</span></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    {{-- Permissions --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0">
                <h4 class="card-title mb-0">Permissions</h4>
            </div>
            <div class="card-body">
                @forelse($role->permissions->groupBy(fn($p) => explode('.', $p->name)[0] ?? 'general') as $group => $perms)
                <div class="mb-3">
                    <small class="text-muted text-uppercase fw-semibold">{{ $group }}</small>
                    <div class="d-flex flex-wrap gap-1 mt-1">
                        @foreach($perms as $perm)
                        <span class="badge bg-purple-lt text-purple">{{ $perm->name }}</span>
                        @endforeach
                    </div>
                </div>
                @empty
                <p class="text-muted mb-0">No permissions assigned.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Users with this role --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0">
                <h4 class="card-title mb-0">Users with Role</h4>
            </div>
            <div class="card-body">
                @forelse($role->users as $user)
                <div class="d-flex align-items-center gap-2 mb-2">
                    <img src="{{ $user->avatar_url }}" class="rounded-circle" width="28" height="28" alt="">
                    <div>
                        <strong style="font-size:.9rem">{{ $user->name }}</strong>
                        <div class="text-muted" style="font-size:.75rem">{{ $user->email }}</div>
                    </div>
                </div>
                @empty
                <p class="text-muted mb-0">No users assigned to this role.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
