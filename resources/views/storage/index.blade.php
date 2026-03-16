@extends('layouts.app')
@section('title', 'Storage Monitoring')
@section('breadcrumb', 'Administration / Storage Monitoring')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-0" style="color:#1a3c6b">Storage Monitoring</h2>
        <small class="text-muted">Infrastructure capacity overview</small>
    </div>
</div>

{{-- Summary Banner --}}
<div class="row g-3 mb-4">
    <div class="col-sm-4">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body py-3">
                <div class="text-muted" style="font-size:.78rem">Total Capacity</div>
                <div style="font-size:1.6rem;font-weight:700;color:#1a3c6b">{{ number_format($summary['total_gb'] / 1024, 1) }} TB</div>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body py-3">
                <div class="text-muted" style="font-size:.78rem">Used Storage</div>
                <div style="font-size:1.6rem;font-weight:700;color:#e8731a">{{ number_format($summary['used_gb'] / 1024, 1) }} TB</div>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body py-3">
                <div class="text-muted" style="font-size:.78rem">Free Storage</div>
                <div style="font-size:1.6rem;font-weight:700;color:#2fb344">{{ number_format($summary['free_gb'] / 1024, 1) }} TB</div>
            </div>
        </div>
    </div>
</div>

{{-- Overall usage bar --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between mb-1">
            <span class="text-muted" style="font-size:.85rem">Overall Utilisation</span>
            <strong>{{ $summary['usage_pct'] }}%</strong>
        </div>
        <div class="progress" style="height:18px;border-radius:9px">
            <div class="progress-bar {{ $summary['usage_pct'] >= 90 ? 'bg-danger' : ($summary['usage_pct'] >= 75 ? 'bg-warning' : 'bg-success') }}"
                 style="width:{{ $summary['usage_pct'] }}%;transition:width 1s ease">
                {{ $summary['usage_pct'] }}%
            </div>
        </div>
    </div>
</div>

{{-- Storage Device Cards --}}
<div class="row g-3">
    @foreach($devices as $device)
    @php $latest = $device->metrics->first(); @endphp
    <div class="col-md-6 col-xl-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between mb-1">
                    <div>
                        <h4 style="font-size:.95rem;font-weight:700;margin-bottom:0">{{ $device->storage_name }}</h4>
                        <small class="text-muted">{{ $device->storage_type }} · {{ $device->region }}</small>
                    </div>
                    <span class="badge bg-{{ $device->usage_badge }}-lt">{{ $device->usage_percent }}%</span>
                </div>
                @if($latest)
                <div class="my-2">
                    <div class="progress" style="height:10px;border-radius:5px">
                        <div class="progress-bar bg-{{ $device->usage_badge }}"
                             style="width:{{ $latest->usage_percentage }}%"></div>
                    </div>
                </div>
                <div class="row text-center g-1 mt-2" style="font-size:.78rem">
                    <div class="col-4"><div class="text-muted">Total</div><strong>{{ number_format($device->total_space_gb / 1024, 1) }} TB</strong></div>
                    <div class="col-4"><div class="text-muted">Used</div><strong>{{ number_format($latest->used_space_gb / 1024, 1) }} TB</strong></div>
                    <div class="col-4"><div class="text-muted">Free</div><strong>{{ number_format($latest->free_space_gb / 1024, 1) }} TB</strong></div>
                </div>
                @endif
                <div class="mt-3">
                    <a href="{{ route('admin.storage.show', $device) }}" class="btn btn-sm btn-outline-primary w-100">View Trends</a>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endsection
