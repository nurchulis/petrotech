@extends('layouts.app')
@section('title', $group->name . ' — Group Detail')
@section('breadcrumb', 'Administration / Group Management / ' . $group->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-0" style="color:#1a3c6b">{{ $group->name }}</h2>
        <small class="text-muted">{{ $group->description ?? 'No description' }}</small>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.groups.edit', $group) }}" class="btn btn-sm btn-outline-primary">Edit</a>
        <a href="{{ route('admin.groups.index') }}" class="btn btn-sm btn-outline-secondary">← Back</a>
    </div>
</div>

<div class="row g-3">
    {{-- Members --}}
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">Members ({{ $group->users->count() }})</h4>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.groups.members.sync', $group) }}">
                    @csrf
                    <div class="mb-3" style="max-height:300px;overflow-y:auto">
                        @foreach($allUsers as $user)
                        <label class="form-check mb-1">
                            <input type="checkbox" name="user_ids[]" value="{{ $user->id }}"
                                   class="form-check-input"
                                   {{ $group->users->contains($user->id) ? 'checked' : '' }}>
                            <span class="form-check-label">
                                {{ $user->name }} <small class="text-muted">{{ $user->email }}</small>
                            </span>
                        </label>
                        @endforeach
                    </div>
                    <button type="submit" class="btn btn-sm" style="background:#1a3c6b;color:#fff">Save Members</button>
                </form>
            </div>
        </div>
    </div>

    {{-- VM Access --}}
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">VM Access ({{ $group->vms->count() }})</h4>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.groups.vm-access.sync', $group) }}">
                    @csrf
                    <div class="mb-3" style="max-height:300px;overflow-y:auto">
                        @foreach($allVms as $vm)
                        <label class="form-check mb-1">
                            <input type="checkbox" name="vm_ids[]" value="{{ $vm->id }}"
                                   class="form-check-input"
                                   {{ $group->vms->contains($vm->id) ? 'checked' : '' }}>
                            <span class="form-check-label">
                                {{ $vm->vm_name }}
                                <small class="text-muted">{{ $vm->application_name }}</small>
                                <span class="badge bg-{{ $vm->status_badge }}-lt text-{{ $vm->status_badge }} ms-1" style="font-size:.65rem">{{ $vm->status }}</span>
                            </span>
                        </label>
                        @endforeach
                    </div>
                    <button type="submit" class="btn btn-sm" style="background:#1a3c6b;color:#fff">Save VM Access</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
