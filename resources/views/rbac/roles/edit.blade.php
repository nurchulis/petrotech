@extends('layouts.app')
@section('title', 'Edit Role')
@section('breadcrumb', 'System / Role Management / Edit')

@section('content')
<div class="row justify-content-center">
<div class="col-lg-8">
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 pt-4">
        <h3 class="card-title" style="color:#1a3c6b">Edit Role — {{ $role->display_name ?? $role->name }}</h3>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.roles.update', $role) }}">
            @csrf @method('PUT')
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Role Name *</label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name', $role->name) }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Display Name</label>
                    <input type="text" name="display_name" class="form-control"
                           value="{{ old('display_name', $role->display_name) }}">
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Description</label>
                    <textarea name="description" class="form-control" rows="2">{{ old('description', $role->description) }}</textarea>
                </div>

                {{-- Permission Assignment --}}
                @if(count($permissions) > 0)
                <div class="col-12">
                    <label class="form-label fw-semibold">Assign Permissions</label>
                    @php $currentPerms = $role->permissions->pluck('name')->toArray(); @endphp
                    @foreach($permissions as $group => $perms)
                    <div class="mb-3">
                        <div class="fw-semibold text-muted text-uppercase" style="font-size:.75rem;letter-spacing:.05em">
                            {{ ucfirst($group) }}
                        </div>
                        <div class="d-flex flex-wrap gap-3 mt-1">
                            @foreach($perms as $perm)
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="permissions[]"
                                       value="{{ $perm['name'] }}" id="perm_{{ $perm['id'] }}"
                                       {{ in_array($perm['name'], old('permissions', $currentPerms)) ? 'checked' : '' }}>
                                <label class="form-check-label" for="perm_{{ $perm['id'] }}">{{ $perm['name'] }}</label>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif

                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn" style="background:#1a3c6b;color:#fff">Update Role</button>
                    <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>
</div>
</div>
@endsection
