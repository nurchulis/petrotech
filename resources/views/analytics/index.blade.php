@extends('layouts.app')
@section('title', 'Analytics & Reports')
@section('breadcrumb', 'Administration / Analytics & Reports')
@section('content')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0" style="color:#1a3c6b">Analytics &amp; Reports</h2>
            <small class="text-muted">Platform-wide operational metrics</small>
        </div>
        <form method="GET" class="d-flex gap-2">
            <select name="period" id="period" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="week" {{ $period === 'week' ? 'selected' : '' }}>Last 7 days</option>
                <option value="month" {{ $period === 'month' ? 'selected' : '' }}>Last 30 days</option>
                <option value="year" {{ $period === 'year' ? 'selected' : '' }}>Last 12 months</option>
            </select>
        </form>
    </div>

    {{-- KPI Row --}}
    <div class="row g-3 mb-4">
        @foreach([
                ['Running VMs', $widgets['running_vms'], 'primary', '🖥️'],
                ['Active Licenses', $widgets['total_licenses'], 'success', '📋'],
                ['Open Tickets', $widgets['open_tickets'], 'danger', '🎫'],
                ['Active Sessions', $widgets['active_sessions'], 'info', '🔗'],
                ['Expiring Licenses', $widgets['expiring_licenses'], 'warning', '⚠️'],
            ] as [$label, $val, $color, $icon])
            <div class="col-6 col-xl">
                <div class="card border-0 shadow-sm text-center metric-card">
                    <div class="card-body py-3">
                        <div style="font-size:1.4rem">{{ $icon }}</div>
                        <div style="font-size:1.5rem;font-weight:700;color:var(--tblr-{{ $color }})">{{ $val }}</div>
                        <div class="text-muted" style="font-size:.72rem">{{ $label }}</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>





                <div class="row g-3">
        {{-- Ticket by Status chart --}}
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0"><h5 class="card-title mb-0">Tickets by Status</h5></div>
                <div class="card-body"><div id="chart-ticket-status"></div></div>
            </div>


                    </div>



        {{-- Ticket by Priority chart --}}
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0"><h5 class="card-title mb-0">Tickets by Priority</h5></div>
                <div class="card-body"><div id="chart-ticket-priority"></div></div>
            </div>


                    </div>

        {{-- License Usage --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0"><h5 class="card-title mb-0">License Usage</h5></div>
                <div class="card-body">
                    <div id="chart-licenses"></div>
                </div>
            </div>


                        </div>

        {{-- VM Utilisation --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0"><h5 class="card-title mb-0">Avg VM Utilisation</h5></div>
                <div class="card-body">
                    @foreach(['avg_cpu' => ['CPU', 'primary'], 'avg_memory' => ['Memory', 'info'], 'avg_gpu' => ['GPU', 'purple']] as $key => [$label, $color])
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <small>{{ $label }}</small><small>{{ $vmStats[$key] }}%</small>
                            </div>
                            <div class="progress" style="height:8px">
                                <div class="progress-bar bg-{{ $color }}" style="width:{{ $vmStats[$key] }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>


                    </div>

        {{-- Storage --}}
        <div class="col-lg-4">

                                   <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0"><h5 class="card-title mb-0">Storage Summary</h5></div>
                <div class="card-body">
                    <div id="chart-storage"></div>
                    <div class="text-center mt-2" style="font-size:.8rem">
                        <span class="badge bg-primary-lt me-1">Used: {{ number_format($storage['used_gb'] / 1024, 1) }} TB</span>
                        <span class="badge bg-success-lt">Free: {{ number_format($storage['free_gb'] / 1024, 1) }} TB</span>
                    </div>
                </div>
            </div>
            </div>
        </div>
@endsection

@push('scripts')
    <script>
        // Ticket by Status donut
        new ApexCharts(document.querySelector('#chart-ticket-status'), {
            series: [
                {{ $tickets['by_status']['open'] ?? 0 }},
                {{ $tickets['by_status']['in_progress'] ?? 0 }},
                {{ $tickets['by_status']['resolved'] ?? 0 }},
                {{ $tickets['by_status']['closed'] ?? 0 }}
            ],
        labels: ['Open', 'In Progress', 'Resolved', 'Closed'],
            colors: ['#206bc4','#f59f00','#2fb344','#6c757d'],
            chart: { type: 'donut', height: 260 },
            legend: { position: 'bottom' },
            plotOptions: { pie: { donut: { size: '65%' } } },
        }).render();

    // Ticket by Priority
    new ApexCharts(document.querySelector('#chart-ticket-priority'), {
            series: [{
                name: 'Tickets',
                data: [
                    {{ $tickets['by_priority']['critical'] ?? 0 }},
                    {{ $tickets['by_priority']['high'] ?? 0 }},
                    {{ $tickets['by_priority']['medium'] ?? 0 }},
                    {{ $tickets['by_priority']['low'] ?? 0 }} 
                ]
            }],
        chart: { type: 'bar', height: 260, toolbar: { show: false } },
            colors: ['#d63939','#f59f00','#206bc4','#6c757d'],
            plotOptions: { bar: { distributed: true, borderRadius: 4 } },
            xaxis: { categories: ['Critical','High','Medium','Low'] },
            legend: { show: false },
        }).render();

        // License donut
        new ApexCharts(document.querySelector('#chart-licenses'), {
        series: [{{ $licenses['active'] }}, {{ $licenses['expiring'] }}, {{ $licenses['expired'] }}],
            labels: ['Active', 'Expiring Soon', 'Expired'],
        colors: ['#2fb344','#f59f00','#d63939'],
            chart: { type: 'donut', height: 200 },
            legend: { position: 'bottom' },
        }).render();

        @php
            $storageColor = $storage['usage_pct'] >= 90 ? "'#d63939'" : ($storage['usage_pct'] >= 75 ? "'#f59f00'" : "'#2fb344'");
        @endphp
        // Storage radial
    new ApexCharts(document.querySelector('#chart-storage'), {
        series: [{{ $storage['usage_pct'] }}],
        chart: { type: 'radialBar', height: 200 },
        plotOptions: { radialBar: { hollow: { size: '60%' }, dataLabels: { value: { formatter: v => v + '%', fontSize: '22px' } } } },
        colors: [{!! $storageColor !!}],
        labels: ['Used'],
    }).render();
    </script>
@endpush
