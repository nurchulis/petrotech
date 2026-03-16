@extends('layouts.app')
@section('title', 'Submit Ticket')
@section('breadcrumb', 'Ticketing / Submit New Ticket')
@section('content')
<div class="row justify-content-center">
<div class="col-lg-8">
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 pt-4">
        <h3 class="card-title" style="color:#1a3c6b">Submit a Support Ticket</h3>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('tickets.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label class="form-label fw-semibold">Title *</label>
                <input type="text" id="title" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" required>
                @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Category *</label>
                    <select id="category" name="category" class="form-select" required>
                        <option value="">Select...</option>
                        @foreach(['Application Error','VDI Access','License Management','Performance','Network Issue','Access Request','Other'] as $cat)
                        <option value="{{ $cat }}" {{ old('category')===$cat?'selected':'' }}>{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Priority *</label>
                    <select id="priority" name="priority" class="form-select" required>
                        <option value="low">Low</option>
                        <option value="medium" selected>Medium</option>
                        <option value="high">High</option>
                        <option value="critical">Critical</option>
                    </select>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Description *</label>
                <textarea id="description" name="description" class="form-control" rows="5" required>{{ old('description') }}</textarea>
            </div>
            <div class="mb-4">
                <label class="form-label fw-semibold">Attachment <span class="text-muted">(optional, max 5MB)</span></label>
                <input type="file" id="attachment" name="attachment" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn" style="background:#1a3c6b;color:#fff">Submit Ticket</button>
                <a href="{{ route('tickets.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
</div>
</div>
@endsection
