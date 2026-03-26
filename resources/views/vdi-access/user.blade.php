@extends('layouts.app')
@section('title', 'VM Access — ' . $user->name)
@section('breadcrumb', 'Administration / VDI Access / ' . $user->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-0" style="color:#1a3c6b">VDI Access — {{ $user->name }}</h2>
        <small class="text-muted">Manage VM access for this user (direct + group-based)</small>
    </div>
    <a href="{{ route('admin.users.show', $user) }}" class="btn btn-sm btn-outline-secondary">← Back to User</a>
</div>

<div class="row g-3">
    {{-- Direct Access --}}
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0">
                <h4 class="card-title mb-0">Direct VM Access</h4>
                <small class="text-muted">Assign VMs directly to this user</small>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.vdi-access.user.sync', $user) }}">
                    @csrf
                    <div class="mb-3">
                        <input type="text" id="search-direct-vms" class="form-control form-control-sm" placeholder="Search VMs...">
                    </div>
                    <div class="mb-3" id="direct-vms-list" style="max-height:350px;overflow-y:auto">
                        @foreach($allVms as $vm)
                        <label class="form-check mb-1">
                            <input type="checkbox" name="vm_ids[]" value="{{ $vm->id }}"
                                   class="form-check-input"
                                   {{ $directVms->contains('id', $vm->id) ? 'checked' : '' }}>
                            <span class="form-check-label">
                                {{ $vm->vm_name }}
                                <small class="text-muted">{{ $vm->application_name }}</small>
                                <span class="badge bg-{{ $vm->status_badge }}-lt text-{{ $vm->status_badge }} ms-1" style="font-size:.65rem">{{ $vm->status }}</span>
                            </span>
                        </label>
                        @endforeach
                    </div>
                    <button type="submit" class="btn btn-sm" style="background:#1a3c6b;color:#fff">Save Direct Access</button>
                </form>
            </div>
        </div>
    </div>

    {{-- Group-Based Access (read-only) --}}
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0">
                <h4 class="card-title mb-0">Group-Based Access</h4>
                <small class="text-muted">VMs inherited from group membership (read-only)</small>
            </div>
            <div class="card-body">
                @forelse($user->groups as $group)
                <div class="mb-3">
                    <strong>{{ $group->name }}</strong>
                    <a href="{{ route('admin.groups.show', $group) }}" class="text-muted ms-1" style="font-size:.8rem">manage →</a>
                    <div class="mt-1">
                        @forelse($group->vms as $vm)
                        <span class="badge bg-purple-lt text-purple mb-1">{{ $vm->vm_name }}</span>
                        @empty
                        <small class="text-muted">No VMs assigned to this group.</small>
                        @endforelse
                    </div>
                </div>
                @empty
                <p class="text-muted mb-0">User is not in any groups.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const input = document.getElementById('search-direct-vms');
        const list = document.getElementById('direct-vms-list');
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
