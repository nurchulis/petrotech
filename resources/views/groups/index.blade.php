@extends('layouts.app')
@section('title', 'Group Management')
@section('breadcrumb', 'Administration / Group Management')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-0" style="color:#1a3c6b">Group Management</h2>
        <small class="text-muted">Manage groups and their VM access</small>
    </div>
    <a href="{{ route('admin.groups.create') }}" class="btn" style="background:#1a3c6b;color:#fff">+ Add Group</a>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-vcenter card-table">
            <thead style="background:#f4f7fb">
                <tr>
                    <th>Group Name</th>
                    <th>Description</th>
                    <th class="text-center">Members</th>
                    <th class="text-center">VMs</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($groups as $group)
                <tr>
                    <td><strong>{{ $group->name }}</strong></td>
                    <td><span class="text-muted">{{ $group->description ?? '—' }}</span></td>
                    <td class="text-center"><span class="badge bg-blue-lt">{{ $group->users_count }}</span></td>
                    <td class="text-center"><span class="badge bg-purple-lt">{{ $group->vms_count }}</span></td>
                    <td class="text-end">
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('admin.groups.show', $group) }}" class="btn btn-outline-secondary">View</a>
                            <a href="{{ route('admin.groups.edit', $group) }}" class="btn btn-outline-primary">Edit</a>
                            <form method="POST" action="{{ route('admin.groups.destroy', $group) }}" onsubmit="return confirm('Delete this group?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center text-muted py-4">No groups found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($groups->hasPages())
    <div class="card-footer">{{ $groups->links() }}</div>
    @endif
</div>
@endsection
