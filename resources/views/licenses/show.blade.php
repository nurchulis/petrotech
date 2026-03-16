@extends('layouts.app')
@section('title', $license->license_name)
@section('breadcrumb', 'Licenses / ' . $license->license_name)
@section('content')
<div class="row g-3">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white border-0 pt-4 d-flex justify-content-between align-items-start">
                <div>
                    <small class="text-muted text-uppercase" style="font-size:.72rem;letter-spacing:.07em">License</small>
                    <h3 class="mb-0" style="color:#1a3c6b">{{ $license->license_name }}</h3>
                    <span class="badge {{ $license->status === 'enable' ? 'bg-success' : 'bg-secondary' }}-lt text-{{ $license->status === 'enable' ? 'success' : 'secondary' }} mt-1">
                        {{ $license->status === 'enable' ? 'Active' : 'Disabled' }}
                    </span>
                    @if($license->is_expired)
                        <span class="badge bg-danger-lt text-danger mt-1">Expired</span>
                    @elseif($license->days_until_expiry !== null && $license->days_until_expiry <= 30)
                        <span class="badge bg-warning-lt text-warning mt-1">Expiring Soon</span>
                    @endif
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.licenses.edit', $license) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                    <a href="{{ route('admin.licenses.index') }}" class="btn btn-sm btn-outline-secondary">← Back</a>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3" style="font-size:.88rem">
                    <div class="col-md-6">
                        <dl class="row mb-0">
                            <dt class="col-5 text-muted">Application</dt>
                            <dd class="col-7">{{ $license->application_name }}</dd>
                            <dt class="col-5 text-muted">Expiry Date</dt>
                            <dd class="col-7">
                                {{ $license->expiry_date->format('d M Y') }}
                                @if($license->days_until_expiry !== null)
                                <small class="text-muted">({{ $license->days_until_expiry > 0 ? $license->days_until_expiry.'d left' : 'Expired' }})</small>
                                @endif
                            </dd>
                            <dt class="col-5 text-muted">License Server</dt>
                            <dd class="col-7">{{ $license->server?->server_name ?? '—' }}</dd>
                            <dt class="col-5 text-muted">Log File</dt>
                            <dd class="col-7 font-monospace" style="font-size:.78rem;word-break:break-all">{{ $license->log_file_path ?? '—' }}</dd>
                            <dt class="col-5 text-muted">Created By</dt>
                            <dd class="col-7">{{ $license->creator?->name ?? '—' }}</dd>
                        </dl>
                    </div>
                    <div class="col-md-6">
                        @if($license->notes)
                        <div class="alert alert-light border mb-0" style="font-size:.85rem">
                            <strong>Notes:</strong><br>{{ $license->notes }}
                        </div>
                        @endif
                    </div>
                    @if($license->license_key)
                    <div class="col-12">
                        <label class="form-label fw-semibold text-muted" style="font-size:.78rem;text-transform:uppercase;letter-spacing:.06em">License Key</label>
                        <pre class="bg-dark text-success p-3 rounded" style="font-size:.78rem;overflow-x:auto">{{ $license->license_key }}</pre>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Activity Logs --}}
        @if($license->logs && $license->logs->count())
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3"><h5 class="mb-0">Activity Log</h5></div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0" style="font-size:.83rem">
                    <thead class="table-light">
                        <tr>
                            <th>Event</th>
                            <th>Detail</th>
                            <th>User Count</th>
                            <th>Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($license->logs->take(20) as $log)
                        <tr>
                            <td><span class="badge bg-secondary-lt text-secondary">{{ $log->event_type }}</span></td>
                            <td>{{ $log->event_detail ?? '—' }}</td>
                            <td>{{ $log->user_count ?? '—' }}</td>
                            <td>{{ $log->recorded_at->format('d M Y H:i') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>

    {{-- Sidebar quick actions --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-2"><h6 class="mb-0">Quick Actions</h6></div>
            <div class="card-body d-grid gap-2">
                <form method="POST" action="{{ route('admin.licenses.toggle', $license) }}">
                    @csrf
                    <button type="submit" class="btn w-100 {{ $license->status === 'enable' ? 'btn-outline-warning' : 'btn-outline-success' }}">
                        {{ $license->status === 'enable' ? '⏸ Disable License' : '▶ Enable License' }}
                    </button>
                </form>
                <a href="{{ route('admin.licenses.edit', $license) }}" class="btn btn-outline-primary">✏️ Edit License</a>
                <form method="POST" action="{{ route('admin.licenses.destroy', $license) }}" onsubmit="return confirm('Delete this license?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger w-100">🗑 Delete License</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
