@extends('layouts.app')
@section('title', $storage->storage_name . ' – Storage Detail')
@section('breadcrumb', 'Storage Monitor / ' . $storage->storage_name)
@section('content')
<div class="row g-3">
    <div class="col-lg-8">
        {{-- Trend chart: Used GB --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Storage Used — Last 30 Days (GB)</h5>
                <a href="{{ route('admin.storage.index') }}" class="btn btn-sm btn-outline-secondary">← Back</a>
            </div>
            <div class="card-body">
                <div id="chart-used" style="min-height:240px"></div>
            </div>
        </div>
        {{-- Trend chart: Usage % --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white border-0">
                <h5 class="card-title mb-0">Usage % — Last 30 Days</h5>
            </div>
            <div class="card-body">
                <div id="chart-pct" style="min-height:220px"></div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        {{-- Current usage radial --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white border-0 py-2"><h6 class="mb-0">Current Usage</h6></div>
            <div class="card-body text-center">
                <div id="chart-radial"></div>
                @if($latest)
                <div class="mt-2" style="font-size:.85rem">
                    <span class="badge bg-primary-lt me-1">Used: {{ number_format($latest->used_space_gb, 1) }} GB</span>
                    <span class="badge bg-success-lt">Free: {{ number_format($latest->free_space_gb, 1) }} GB</span>
                </div>
                @endif
            </div>
        </div>

        {{-- Device Info --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-2"><h6 class="mb-0">Device Info</h6></div>
            <div class="card-body">
                <dl class="row mb-0" style="font-size:.84rem">
                    <dt class="col-5 text-muted">Type</dt>
                    <dd class="col-7">{{ strtoupper($storage->storage_type) }}</dd>
                    <dt class="col-5 text-muted">Total</dt>
                    <dd class="col-7">{{ number_format($storage->total_space_gb/1024, 1) }} TB</dd>
                    <dt class="col-5 text-muted">Mount</dt>
                    <dd class="col-7 font-monospace" style="font-size:.78rem">{{ $storage->mount_location ?? '—' }}</dd>
                    <dt class="col-5 text-muted">Region</dt>
                    <dd class="col-7">{{ $storage->region }}</dd>
                    <dt class="col-5 text-muted">Data Center</dt>
                    <dd class="col-7">{{ $storage->data_center ?? '—' }}</dd>
                    <dt class="col-5 text-muted">IP Address</dt>
                    <dd class="col-7 font-monospace" style="font-size:.78rem">{{ $storage->ip_address ?? '—' }}</dd>
                    <dt class="col-5 text-muted">Status</dt>
                    <dd class="col-7">
                        <span class="badge {{ $storage->status === 'online' ? 'bg-success' : 'bg-danger' }}-lt text-{{ $storage->status === 'online' ? 'success' : 'danger' }}">
                            {{ ucfirst($storage->status) }}
                        </span>
                    </dd>
                </dl>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const storageLabels = @json($trendData['labels']);
const usedGb  = @json($trendData['used_gb']);
const usagePct = @json($trendData['usage_pct']);

const baseOpts = (title, data, color, unit) => ({
    series: [{ name: title, data: data }],
    chart: { type: 'area', height: 240, toolbar: { show: false } },
    colors: [color],
    fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.05 } },
    stroke: { curve: 'smooth', width: 2 },
    xaxis: { categories: storageLabels, labels: { style: { fontSize: '11px' }, rotate: -30 }, tickAmount: 10 },
    yaxis: { labels: { formatter: v => v.toFixed(1) + unit } },
    tooltip: { y: { formatter: v => v.toFixed(2) + unit } },
    grid: { borderColor: '#f0f3f8' },
});

new ApexCharts(document.querySelector('#chart-used'), baseOpts('Used GB', usedGb, '#206bc4', ' GB')).render();
new ApexCharts(document.querySelector('#chart-pct'),  baseOpts('Usage %', usagePct, '#e8731a', '%')).render();
</script>
@endpush
