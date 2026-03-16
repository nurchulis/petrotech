@extends('layouts.app')
@section('title', 'VDI – ' . $vm->vm_name)
@section('breadcrumb', 'VDI Access / ' . $vm->vm_name)
@section('content')
<div class="row g-3">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 pt-4 pb-2 d-flex justify-content-between align-items-start">
                <div>
                    <small class="text-muted text-uppercase" style="font-size:.72rem;letter-spacing:.07em">Virtual Machine</small>
                    <h3 class="mb-0" style="color:#1a3c6b">{{ $vm->vm_name }}</h3>
                    <span class="badge mt-1 {{ $vm->status === 'running' ? 'bg-success' : ($vm->status === 'stopped' ? 'bg-secondary' : 'bg-warning') }}">
                        {{ ucfirst($vm->status) }}
                    </span>
                </div>
                <div class="d-flex gap-2">
                    @if($vm->status === 'running')
                    <form id="connect-form" method="POST" action="{{ route('vdi.connect', $vm) }}" style="display:none">
                        @csrf
                    </form>
                    <button type="button"
                        id="connect-btn"
                        class="btn"
                        style="background:#1a3c6b;color:#fff"
                        onclick="connectAndOpen()"
                    >
                        🔗 Connect via {{ strtoupper($vm->protocol ?? 'RDP') }}
                    </button>
                    <script>
                    function connectAndOpen() {
                        const btn = document.getElementById('connect-btn');
                        btn.disabled = true;
                        btn.textContent = '⏳ Connecting…';
                        const form = document.getElementById('connect-form');
                        const data = new FormData(form);
                        fetch(form.action, { method: 'POST', body: data, headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                            .then(() => {
                                window.open('{{ route('vdi.rdp', $vm) }}', '_blank');
                                btn.disabled = false;
                                btn.innerHTML = '🔗 Connect via {{ strtoupper($vm->protocol ?? 'RDP') }}';
                            })
                            .catch(() => {
                                btn.disabled = false;
                                btn.innerHTML = '🔗 Connect via {{ strtoupper($vm->protocol ?? 'RDP') }}';
                                alert('Connection failed. Please try again.');
                            });
                    }
                    </script>
                    @else
                    <button class="btn btn-outline-secondary" disabled>VM Offline</button>
                    @endif
                    <a href="{{ route('vdi.index') }}" class="btn btn-outline-secondary">← Back</a>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <dl class="row mb-0" style="font-size:.88rem">
                            <dt class="col-5 text-muted">Application</dt>
                            <dd class="col-7">{{ $vm->application_name }}</dd>
                            <dt class="col-5 text-muted">OS</dt>
                            <dd class="col-7">{{ $vm->os_type }}</dd>
                            <dt class="col-5 text-muted">IP Address</dt>
                            <dd class="col-7 font-monospace">{{ $vm->ip_address }}</dd>
                            <dt class="col-5 text-muted">Host Server</dt>
                            <dd class="col-7">{{ $vm->host_server ?? '—' }}</dd>
                        </dl>
                    </div>
                    <div class="col-md-6">
                        <dl class="row mb-0" style="font-size:.88rem">
                            <dt class="col-5 text-muted">Region</dt>
                            <dd class="col-7">{{ $vm->region }}</dd>
                            <dt class="col-5 text-muted">Data Center</dt>
                            <dd class="col-7">{{ $vm->data_center ?? '—' }}</dd>
                            <dt class="col-5 text-muted">CPU Cores</dt>
                            <dd class="col-7">{{ $vm->cpu_cores }} vCPU</dd>
                            <dt class="col-5 text-muted">RAM</dt>
                            <dd class="col-7">{{ $vm->ram_gb }} GB</dd>
                            @if($vm->gpu_model)
                            <dt class="col-5 text-muted">GPU</dt>
                            <dd class="col-7">{{ $vm->gpu_model }} ({{ $vm->gpu_vram_gb }} GB)</dd>
                            @endif
                        </dl>
                    </div>
                    @if($vm->notes)
                    <div class="col-12">
                        <div class="alert alert-light border mb-0" style="font-size:.88rem">
                            <strong>Notes:</strong> {{ $vm->notes }}
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        {{-- Assigned User --}}
        @if($vm->assignedUser)
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body text-center">
                <img src="{{ $vm->assignedUser->avatar_url }}" width="56" height="56" class="rounded-circle mb-2">
                <div class="fw-semibold">{{ $vm->assignedUser->name }}</div>
                <small class="text-muted">{{ $vm->assignedUser->department ?? 'Assigned User' }}</small>
            </div>
        </div>
        @endif

        {{-- Quick stats --}}
        @if($latestMetric)
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-2"><h6 class="mb-0">Latest Metrics</h6></div>
            <div class="card-body">
                @foreach([
                    ['CPU', $latestMetric->cpu_usage_pct, 'primary'],
                    ['Memory', $latestMetric->memory_usage_pct, 'info'],
                    ['Disk I/O', $latestMetric->disk_io_pct, 'success'],
                ] as [$label, $val, $color])
                <div class="mb-2">
                    <div class="d-flex justify-content-between mb-1">
                        <small>{{ $label }}</small><small>{{ round($val, 1) }}%</small>
                    </div>
                    <div class="progress" style="height:6px">
                        <div class="progress-bar bg-{{ $color }}" style="width:{{ min($val,100) }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
