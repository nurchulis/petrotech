@extends('layouts.app')
@section('title', 'Ticketing System')
@section('breadcrumb', 'Ticketing / All Tickets')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-0" style="color:#1a3c6b">Support Tickets</h2>
        <small class="text-muted">Track and manage support requests</small>
    </div>
    <a href="{{ route('tickets.create') }}" class="btn" style="background:#1a3c6b;color:#fff">+ New Ticket</a>
</div>

{{-- Stats Row --}}
<div class="row g-2 mb-4">
    @foreach(['open'=>['Open','primary'],'in_progress'=>['In Progress','warning'],'resolved'=>['Resolved','success'],'closed'=>['Closed','secondary']] as $key => [$label,$color])
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center py-2">
            <div style="font-size:1.5rem;font-weight:700;color:var(--tblr-{{ $color }})">{{ $stats[$key] }}</div>
            <div class="text-muted" style="font-size:.75rem">{{ $label }}</div>
        </div>
    </div>
    @endforeach
</div>

{{-- Filters --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" class="d-flex gap-2 flex-wrap align-items-center">
            <input type="text" name="search" class="form-control form-control-sm" style="max-width:200px" placeholder="Search..." value="{{ request('search') }}">
            <select name="status" class="form-select form-select-sm" style="max-width:150px">
                <option value="">All Status</option>
                @foreach(['open','in_progress','resolved','closed'] as $s)
                <option value="{{ $s }}" {{ request('status')===$s?'selected':'' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                @endforeach
            </select>
            <select name="priority" class="form-select form-select-sm" style="max-width:130px">
                <option value="">All Priority</option>
                @foreach(['low','medium','high','critical'] as $p)
                <option value="{{ $p }}" {{ request('priority')===$p?'selected':'' }}>{{ ucfirst($p) }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-sm btn-outline-primary">Filter</button>
            <a href="{{ route('tickets.index') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-vcenter card-table">
            <thead style="background:#f4f7fb">
                <tr>
                    <th>Ticket #</th>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th>Assigned To</th>
                    <th>Created</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($tickets as $ticket)
                <tr>
                    <td><code style="font-size:.78rem">{{ $ticket->ticket_number }}</code></td>
                    <td>
                        <a href="{{ route('tickets.show', $ticket) }}" style="color:#1a3c6b;font-weight:600">{{ Str::limit($ticket->title, 50) }}</a>
                        <div><small class="text-muted">By {{ $ticket->creator->name }}</small></div>
                    </td>
                    <td><span class="badge bg-azure-lt">{{ $ticket->category }}</span></td>
                    <td><span class="badge bg-{{ $ticket->priority_badge }}-lt text-{{ $ticket->priority_badge }}">{{ ucfirst($ticket->priority) }}</span></td>
                    <td><span class="badge bg-{{ $ticket->status_badge }}-lt text-{{ $ticket->status_badge }}">{{ ucfirst(str_replace('_',' ',$ticket->status)) }}</span></td>
                    <td>{{ $ticket->assignedAdmin?->name ?? '<span class="text-muted">Unassigned</span>' }}</td>
                    <td><small class="text-muted">{{ $ticket->created_at->format('d M Y') }}</small></td>
                    <td><a href="{{ route('tickets.show', $ticket) }}" class="btn btn-sm btn-outline-primary">View</a></td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted py-4">No tickets found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($tickets->hasPages())
    <div class="card-footer">{{ $tickets->withQueryString()->links() }}</div>
    @endif
</div>
@endsection
