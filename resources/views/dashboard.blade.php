@extends('layouts.app')
@section('title', 'Dashboard')
@section('breadcrumb', 'Dashboard')

@section('content')

{{-- KPI Cards --}}
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="card metric-card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded p-2" style="background:#e8f4fd">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#1a3c6b" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/></svg>
                </div>
                <div>
                    <div class="text-muted" style="font-size:.78rem">Running VMs</div>
                    <div style="font-size:1.6rem;font-weight:700;color:#1a3c6b">{{ $widgets['running_vms'] }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card metric-card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded p-2" style="background:#fef3e8">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#e8731a" stroke-width="2"><rect x="1" y="3" width="15" height="13" rx="1"/><path d="M16 8h5a1 1 0 011 1v9a1 1 0 01-1 1H10a1 1 0 01-1-1v-4"/></svg>
                </div>
                <div>
                    <div class="text-muted" style="font-size:.78rem">Active Licenses</div>
                    <div style="font-size:1.6rem;font-weight:700;color:#e8731a">{{ $licenses['active'] }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card metric-card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded p-2" style="background:#fde8e8">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#d63939" stroke-width="2"><path d="M15 5v2M15 11v2M15 17v2M5 5h14a2 2 0 012 2v3a2 2 0 000 4v3a2 2 0 01-2 2H5a2 2 0 01-2-2v-3a2 2 0 000-4V7a2 2 0 012-2z"/></svg>
                </div>
                <div>
                    <div class="text-muted" style="font-size:.78rem">Open Tickets</div>
                    <div style="font-size:1.6rem;font-weight:700;color:#d63939">{{ $widgets['open_tickets'] }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card metric-card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded p-2" style="background:#e8f8ec">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#2fb344" stroke-width="2"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/></svg>
                </div>
                <div>
                    <div class="text-muted" style="font-size:.78rem">Storage Used</div>
                    <div style="font-size:1.6rem;font-weight:700;color:#2fb344">{{ $storage['usage_pct'] }}%</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Alerts row --}}
@if($widgets['expiring_licenses'] > 0)
<div class="alert alert-warning d-flex align-items-center gap-2 mb-4">
    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
    <strong>{{ $widgets['expiring_licenses'] }} license(s)</strong> expiring within 30 days.
    @role(['admin','super_admin'])
    <a href="{{ route('admin.licenses.index') }}?status=enable" class="ms-2">Review now →</a>
    @endrole
</div>
@endif

<div class="row g-3">
    {{-- Ticket Summary --}}
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 pb-0">
                <h3 class="card-title" style="font-size:1rem;color:#1a3c6b">Ticket Overview</h3>
            </div>
            <div class="card-body">
                <div class="row text-center g-3">
                    @foreach(['open'=>['Open','primary'],'in_progress'=>['In Progress','warning'],'resolved'=>['Resolved','success'],'closed'=>['Closed','secondary']] as $key => [$label, $color])
                    <div class="col-6 col-md-3">
                        <div class="p-3 rounded" style="background:var(--tblr-{{ $color }}-lt)">
                            <div style="font-size:1.4rem;font-weight:700">{{ $tickets['by_status'][$key] ?? 0 }}</div>
                            <div class="text-muted" style="font-size:.75rem">{{ $label }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>
                <div class="mt-3 text-center">
                    <a href="{{ route('tickets.index') }}" class="btn btn-sm" style="background:#1a3c6b;color:#fff">View all tickets →</a>
                </div>
            </div>
        </div>
    </div>

    {{-- Storage Summary --}}
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 pb-0">
                <h3 class="card-title" style="font-size:1rem;color:#1a3c6b">Storage Utilisation</h3>
            </div>
            <div class="card-body d-flex flex-column justify-content-center">
                <div class="mb-1 d-flex justify-content-between">
                    <small class="text-muted">Used: <strong>{{ number_format($storage['used_gb'] / 1024, 1) }} TB</strong></small>
                    <small class="text-muted">Total: <strong>{{ number_format($storage['total_gb'] / 1024, 1) }} TB</strong></small>
                </div>
                <div class="progress mb-3" style="height:16px;border-radius:8px">
                    <div class="progress-bar {{ $storage['usage_pct'] >= 90 ? 'bg-danger' : ($storage['usage_pct'] >= 75 ? 'bg-warning' : 'bg-success') }}"
                         style="width:{{ $storage['usage_pct'] }}%">
                        {{ $storage['usage_pct'] }}%
                    </div>
                </div>
                <div class="text-muted text-center" style="font-size:.8rem">
                    Free: <strong>{{ number_format($storage['free_gb'] / 1024, 1) }} TB</strong>
                </div>
                @role(['admin','super_admin'])
                <div class="mt-3 text-center">
                    <a href="{{ route('admin.storage.index') }}" class="btn btn-sm btn-outline-secondary">View storage details →</a>
                </div>
                @endrole
            </div>
        </div>
    </div>

    {{-- License Status --}}
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 pb-0">
                <h3 class="card-title" style="font-size:1rem;color:#1a3c6b">License Status</h3>
            </div>
            <div class="card-body">
                <div class="row text-center g-2">
                    <div class="col-4"><div class="p-2 rounded bg-success-lt"><div style="font-size:1.3rem;font-weight:700">{{ $licenses['active'] }}</div><div class="text-muted" style="font-size:.75rem">Active</div></div></div>
                    <div class="col-4"><div class="p-2 rounded bg-warning-lt"><div style="font-size:1.3rem;font-weight:700">{{ $licenses['expiring'] }}</div><div class="text-muted" style="font-size:.75rem">Expiring Soon</div></div></div>
                    <div class="col-4"><div class="p-2 rounded bg-danger-lt"><div style="font-size:1.3rem;font-weight:700">{{ $licenses['expired'] }}</div><div class="text-muted" style="font-size:.75rem">Expired</div></div></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Links --}}
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 pb-0">
                <h3 class="card-title" style="font-size:1rem;color:#1a3c6b">Quick Actions</h3>
            </div>
            <div class="card-body d-grid gap-2">
                <a href="{{ route('vdi.index') }}" class="btn btn-outline-primary text-start">🖥️ &nbsp; Launch VDI Session</a>
                <a href="{{ route('tickets.create') }}" class="btn btn-outline-danger text-start">🎫 &nbsp; Submit Support Ticket</a>
                @role(['admin','super_admin'])
                <a href="{{ route('admin.analytics.index') }}" class="btn btn-outline-secondary text-start">📊 &nbsp; View Analytics Report</a>
                @endrole
            </div>
        </div>
    </div>
</div>

@endsection
