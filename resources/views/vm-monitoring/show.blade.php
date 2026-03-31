@extends('layouts.app')
@section('title', $vm->vm_name . ' — VM Detail')
@section('breadcrumb', 'VM Monitoring / ' . $vm->vm_name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-0" style="color:#1a3c6b">{{ $vm->vm_name }}</h2>
        <small class="text-muted">{{ $vm->application_name }} · {{ $vm->os_type }} · {{ $vm->region }}</small>
    </div>
    <span class="badge bg-{{ $vm->status_badge }} p-2">{{ ucfirst($vm->status) }}</span>
</div>

{{-- Current metrics row --}}
@if($latest)
<div class="row g-3 mb-4">
    @foreach(['cpu_utilisation'=>['CPU','primary'],'memory_utilisation'=>['Memory','info'],'gpu_utilisation'=>['GPU','purple']] as $key => [$label, $color])
    <div class="col-sm-4">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body py-3">
                <div class="text-muted mb-1" style="font-size:.78rem">{{ $label }} Utilisation</div>
                <div style="font-size:1.8rem;font-weight:700;color:var(--tblr-{{ $color }})">
                    {{ number_format($latest->$key ?? 0, 1) }}%
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endif

{{-- Charts --}}
<div class="row g-3">
    @if(empty($trendData['labels']))
    <div class="col-12">
        <div class="alert alert-info border-0 shadow-sm mb-0">
            <div class="d-flex">
                <div>
                    <i class="fas fa-info-circle me-2"></i>
                </div>
                <div>
                    <h4 class="alert-title">No Monitoring Data Available</h4>
                    <div class="text-muted">No metrics recorded for this VM in the last 24 hours. Please check the monitoring agent status or generate test data.</div>
                </div>
            </div>
        </div>
    </div>
    @else
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0"><h4 class="card-title mb-0">CPU Usage (24h)</h4></div>
            <div class="card-body"><div id="chart-cpu"></div></div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0"><h4 class="card-title mb-0">Memory Usage (24h)</h4></div>
            <div class="card-body"><div id="chart-memory"></div></div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0"><h4 class="card-title mb-0">Disk I/O Read (24h)</h4></div>
            <div class="card-body"><div id="chart-disk"></div></div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0"><h4 class="card-title mb-0">Network In (24h)</h4></div>
            <div class="card-body"><div id="chart-network"></div></div>
        </div>
    </div>
    @if($vm->has_gpu)
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0"><h4 class="card-title mb-0">GPU Utilisation (24h)</h4></div>
            <div class="card-body"><div id="chart-gpu"></div></div>
        </div>
    </div>
    @endif
    @endif
</div>
@endsection

@push('scripts')
<script>
const labels  = @json($trendData['labels']);
const chartOpts = (title, data, color) => ({
    series: [{ name: title, data: data }],
    chart: { type: 'area', height: 220, toolbar: { show: false }, sparkline: { enabled: false } },
    colors: [color],
    fill:  { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: .4, opacityTo: .05 } },
    stroke: { curve: 'smooth', width: 2 },
    xaxis: { categories: labels, labels: { style: { fontSize: '11px' } }, tickAmount: 8 },
    yaxis: { labels: { formatter: v => v.toFixed(1) } },
    tooltip: { y: { formatter: v => v.toFixed(2) } },
    grid: { borderColor: '#f0f3f8' },
});
new ApexCharts(document.querySelector('#chart-cpu'),     chartOpts('CPU %',       @json($trendData['cpu']),     '#1a3c6b')).render();
new ApexCharts(document.querySelector('#chart-memory'),  chartOpts('Memory %',    @json($trendData['memory']),  '#0ea5e9')).render();
new ApexCharts(document.querySelector('#chart-disk'),    chartOpts('Disk MB/s',   @json($trendData['disk']),    '#e8731a')).render();
new ApexCharts(document.querySelector('#chart-network'), chartOpts('Network MB/s',@json($trendData['network']), '#2fb344')).render();
@if($vm->has_gpu)
new ApexCharts(document.querySelector('#chart-gpu'),     chartOpts('GPU %',       @json($trendData['gpu']),     '#9c27b0')).render();
@endif
</script>
@endpush
