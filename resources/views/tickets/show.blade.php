@extends('layouts.app')
@section('title', $ticket->ticket_number)
@section('breadcrumb', 'Ticketing / ' . $ticket->ticket_number)
@section('content')

<div class="row g-3">
    <div class="col-lg-8">
        {{-- Ticket Detail --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white border-0 pt-4 pb-2">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <small class="text-muted">{{ $ticket->ticket_number }}</small>
                        <h3 class="mb-0" style="color:#1a3c6b">{{ $ticket->title }}</h3>
                    </div>
                    <div class="d-flex gap-2">
                        <span class="badge bg-{{ $ticket->priority_badge }}-lt text-{{ $ticket->priority_badge }}">{{ ucfirst($ticket->priority) }}</span>
                        <span class="badge bg-{{ $ticket->status_badge }}-lt text-{{ $ticket->status_badge }}">{{ ucfirst(str_replace('_',' ',$ticket->status)) }}</span>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <p>{{ $ticket->description }}</p>
                @if($ticket->attachment_path)
                    <a href="{{ Storage::url($ticket->attachment_path) }}" target="_blank" class="btn btn-sm btn-outline-secondary">📎 View Attachment</a>
                @endif
            </div>
        </div>

        {{-- Comments --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white border-0 py-3"><h5 class="mb-0">Replies ({{ $ticket->comments->count() }})</h5></div>
            <div class="card-body p-0">
                @forelse($ticket->comments as $comment)
                <div class="px-4 py-3 border-bottom {{ $comment->is_internal_note ? 'bg-yellow-lt' : '' }}">
                    <div class="d-flex gap-3">
                        <img src="{{ $comment->user->avatar_url }}" width="36" height="36" class="rounded-circle flex-shrink-0">
                        <div class="w-100">
                            <div class="d-flex justify-content-between">
                                <strong style="font-size:.88rem">{{ $comment->user->name }}</strong>
                                <small class="text-muted">{{ $comment->created_at->diffForHumans() }}</small>
                            </div>
                            @if($comment->is_internal_note)
                                <span class="badge bg-warning-lt text-warning mb-1">Internal Note</span>
                            @endif
                            <p class="mb-0 mt-1">{{ $comment->body }}</p>
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center text-muted py-4">No replies yet.</div>
                @endforelse
            </div>
        </div>

        {{-- Add Reply --}}
        @if(!in_array($ticket->status, ['closed']))
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3"><h5 class="mb-0">Add Reply</h5></div>
            <div class="card-body">
                <form method="POST" action="{{ route('tickets.comment', $ticket) }}" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <textarea id="body" name="body" class="form-control" rows="4" placeholder="Write your reply..." required></textarea>
                    </div>
                    <div class="mb-3">
                        <input type="file" name="attachment" class="form-control form-control-sm" accept=".jpg,.jpeg,.png,.pdf">
                    </div>
                    @role(['admin','super_admin'])
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_internal_note" id="internal" value="1">
                            <label class="form-check-label text-muted" for="internal">Internal note (only visible to admins)</label>
                        </div>
                    </div>
                    @endrole
                    <button type="submit" class="btn btn-sm" style="background:#1a3c6b;color:#fff">Post Reply</button>
                </form>
            </div>
        </div>
        @endif
    </div>

    {{-- Sidebar --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <h6 class="text-muted mb-3">Ticket Info</h6>
                <dl class="row mb-0" style="font-size:.85rem">
                    <dt class="col-5 text-muted">Submitted by</dt>
                    <dd class="col-7">{{ $ticket->creator->name }}</dd>
                    <dt class="col-5 text-muted">Assigned to</dt>
                    <dd class="col-7">{{ $ticket->assignedAdmin?->name ?? '—' }}</dd>
                    <dt class="col-5 text-muted">Category</dt>
                    <dd class="col-7">{{ $ticket->category }}</dd>
                    <dt class="col-5 text-muted">Created</dt>
                    <dd class="col-7">{{ $ticket->created_at->format('d M Y H:i') }}</dd>
                    @if($ticket->resolved_at)
                    <dt class="col-5 text-muted">Resolved</dt>
                    <dd class="col-7">{{ $ticket->resolved_at->format('d M Y') }}</dd>
                    @endif
                </dl>
            </div>
        </div>

        @role(['admin','super_admin'])
        {{-- Assign --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white border-0 py-2"><h6 class="mb-0">Assign Ticket</h6></div>
            <div class="card-body">
                <form method="POST" action="{{ route('tickets.assign', $ticket) }}">
                    @csrf
                    <div class="mb-2">
                        <select name="admin_id" id="admin_id" class="form-select form-select-sm">
                            @foreach($admins as $admin)
                            <option value="{{ $admin->id }}" {{ $ticket->assigned_to===$admin->id?'selected':'' }}>{{ $admin->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-sm btn-outline-primary w-100">Assign</button>
                </form>
            </div>
        </div>
        {{-- Update Status --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-2"><h6 class="mb-0">Update Status</h6></div>
            <div class="card-body">
                <form method="POST" action="{{ route('tickets.status', $ticket) }}">
                    @csrf
                    <div class="mb-2">
                        <select name="status" id="status" class="form-select form-select-sm">
                            @foreach(['open','in_progress','resolved','closed'] as $s)
                            <option value="{{ $s }}" {{ $ticket->status===$s?'selected':'' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <textarea name="resolution_notes" class="form-control form-control-sm" rows="2" placeholder="Resolution notes (optional)"></textarea>
                    </div>
                    <button type="submit" class="btn btn-sm btn-outline-success w-100">Update Status</button>
                </form>
            </div>
        </div>
        @endrole
    </div>
</div>
@endsection
