@extends('layouts.app')
@section('title', 'Role Management')
@section('breadcrumb', 'System / Role Management')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-0" style="color:#1a3c6b">Role Management</h2>
        <small class="text-muted">Manage roles and their permissions</small>
    </div>
    @can('create', \Spatie\Permission\Models\Role::class)
    <a href="{{ route('admin.roles.create') }}" class="btn" style="background:#1a3c6b;color:#fff">+ Add Role</a>
    @endcan
</div>

{{-- Filters --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" class="d-flex gap-2 align-items-center flex-wrap">
            <input type="text" name="search" class="form-control form-control-sm" style="max-width:220px"
                   placeholder="Search role name..." value="{{ request('search') }}">
            <button type="submit" class="btn btn-sm btn-outline-primary">Filter</button>
            <a href="{{ route('admin.roles.index') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-vcenter card-table">
            <thead style="background:#f4f7fb">
                <tr>
                    <th>Role Name</th>
                    <th>Display Name</th>
                    <th>Description</th>
                    <th class="text-center">Users</th>
                    <th class="text-center">Permissions</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($roles as $role)
                <tr>
                    <td><strong>{{ $role->name }}</strong></td>
                    <td>{{ $role->display_name ?? '—' }}</td>
                    <td><span class="text-muted">{{ Str::limit($role->description, 50) ?? '—' }}</span></td>
                    <td class="text-center">
                        <span class="badge bg-blue-lt">{{ $role->users_count }}</span>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-purple-lt">{{ $role->permissions_count }}</span>
                    </td>
                    <td class="text-end">
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('admin.roles.show', $role) }}" class="btn btn-outline-secondary">View</a>
                            @can('update', $role)
                            <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-outline-primary">Edit</a>
                            @endcan
                            @can('delete', $role)
                            @unless(in_array($role->name, ['user', 'admin', 'super_admin']))
                            <form method="POST" action="{{ route('admin.roles.destroy', $role) }}" onsubmit="return confirm('Delete this role?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger">Delete</button>
                            </form>
                            @endunless
                            @endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No roles found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($roles->hasPages())
    <div class="card-footer">{{ $roles->withQueryString()->links() }}</div>
    @endif
</div>
@endsection
