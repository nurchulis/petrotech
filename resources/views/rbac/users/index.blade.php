@extends('layouts.app')
@section('title', 'User Management')
@section('breadcrumb', 'Administration / User Management')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-0" style="color:#1a3c6b">User Management</h2>
        <small class="text-muted">Manage platform users and their roles</small>
    </div>
    @can('create', \App\Models\User::class)
    <a href="{{ route('admin.users.create') }}" class="btn" style="background:#1a3c6b;color:#fff">+ Add User</a>
    @endcan
</div>

{{-- Filters --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" class="d-flex gap-2 align-items-center flex-wrap">
            <input type="text" name="search" class="form-control form-control-sm" style="max-width:220px"
                   placeholder="Search name, email..." value="{{ request('search') }}">
            <select name="role" class="form-select form-select-sm" style="max-width:150px">
                <option value="">All Roles</option>
                @foreach($roles as $role)
                <option value="{{ $role->name }}" {{ request('role')==$role->name?'selected':'' }}>{{ $role->display_name ?? ucfirst($role->name) }}</option>
                @endforeach
            </select>
            <select name="status" class="form-select form-select-sm" style="max-width:130px">
                <option value="">All Status</option>
                <option value="1" {{ request('status')==='1'?'selected':'' }}>Active</option>
                <option value="0" {{ request('status')==='0'?'selected':'' }}>Inactive</option>
            </select>
            <button type="submit" class="btn btn-sm btn-outline-primary">Filter</button>
            <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-vcenter card-table">
            <thead style="background:#f4f7fb">
                <tr>
                    <th>User</th>
                    <th>Employee ID</th>
                    <th>Department</th>
                    <th>Roles</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <img src="{{ $user->avatar_url }}" class="rounded-circle" width="32" height="32" alt="">
                            <div>
                                <strong>{{ $user->name }}</strong>
                                <div class="text-muted" style="font-size:.8rem">{{ $user->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td>{{ $user->employee_id ?? '—' }}</td>
                    <td>{{ $user->department ?? '—' }}</td>
                    <td>
                        @foreach($user->roles as $role)
                        <span class="badge bg-primary-lt text-primary">{{ $role->display_name ?? ucfirst($role->name) }}</span>
                        @endforeach
                    </td>
                    <td>
                        <span class="badge bg-{{ $user->is_active ? 'success' : 'secondary' }}-lt text-{{ $user->is_active ? 'success' : 'secondary' }}">
                            {{ $user->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="text-end">
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('admin.users.show', $user) }}" class="btn btn-outline-secondary">View</a>
                            @can('update', $user)
                            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-outline-primary">Edit</a>
                            @endcan
                            @can('delete', $user)
                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Delete this user?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger">Delete</button>
                            </form>
                            @endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No users found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($users->hasPages())
    <div class="card-footer">{{ $users->withQueryString()->links() }}</div>
    @endif
</div>
@endsection
