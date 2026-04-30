@extends('layouts.app')
@section('title', 'Create Group')
@section('breadcrumb', 'Administration / Group Management / Create')

@section('content')
<div class="row justify-content-center">
<div class="col-lg-8">
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 pt-4">
        <h3 class="card-title" style="color:#1a3c6b">Create New Group</h3>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.groups.store') }}">
            @csrf
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label fw-semibold">Group Name *</label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name') }}" placeholder="e.g. IT Department" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Description</label>
                    <textarea name="description" class="form-control" rows="2">{{ old('description') }}</textarea>
                </div>
                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn" style="background:#1a3c6b;color:#fff">Create Group</button>
                    <a href="{{ route('admin.groups.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>
</div>
</div>
@endsection
