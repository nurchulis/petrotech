@extends('layouts.app')
@section('title', 'VM Monitoring')
@section('breadcrumb', 'Administration / VM Monitoring')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-0" style="color:#1a3c6b">VM Monitoring</h2>
        <small class="text-muted">Real-time virtual machine utilisation</small>
    </div>
    <div class="d-flex gap-2">
        <span class="badge bg-success-lt text-success p-2">● {{ $totals['running'] }} Running</span>
        <span class="badge bg-danger-lt text-danger p-2">● {{ $totals['stopped'] }} Stopped</span>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-vcenter card-table">
            <thead style="background:#f4f7fb">
                <tr>
                    <th>VM Name</th>
                    <th>Application</th>
                    <th>Region</th>
                    <th>Status</th>
                    <th>CPU %</th>
                    <th>Memory %</th>
                    <th>GPU %</th>
                    <th>Last Update</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($vms as $vm)
                @php $m = $latestMetrics->firstWhere('vm.id', $vm->id); @endphp
                <tr>
                    <td><strong>{{ $vm->vm_name }}</strong><br><small class="text-muted">{{ $vm->os_type }}</small></td>
                    <td>{{ $vm->application_name }}</td>
                    <td>{{ $vm->region }}</td>
                    <td><span class="badge bg-{{ $vm->status_badge }}-lt text-{{ $vm->status_badge }}">{{ ucfirst($vm->status) }}</span></td>
                    <td>
                        @if($m && $vm->status==='running')
                        <div class="progress" style="height:6px;width:80px">
                            <div class="progress-bar {{ $m['cpu_utilisation'] > 80 ? 'bg-danger' : 'bg-primary' }}"
                                 style="width:{{ $m['cpu_utilisation'] }}%"></div>
                        </div>
                        <small>{{ number_format($m['cpu_utilisation'], 1) }}%</small>
                        @else <span class="text-muted">—</span> @endif
                    </td>
                    <td>
                        @if($m && $vm->status==='running')
                        <div class="progress" style="height:6px;width:80px">
                            <div class="progress-bar {{ $m['memory_utilisation'] > 85 ? 'bg-danger' : 'bg-info' }}"
                                 style="width:{{ $m['memory_utilisation'] }}%"></div>
                        </div>
                        <small>{{ number_format($m['memory_utilisation'], 1) }}%</small>
                        @else <span class="text-muted">—</span> @endif
                    </td>
                    <td>
                        @if($vm->has_gpu && $m && $vm->status==='running')
                            <small>{{ number_format($m['gpu_utilisation'], 1) }}%</small>
                        @else <span class="text-muted">N/A</span> @endif
                    </td>
                    <td><small class="text-muted">{{ $m ? $m['recorded_at']?->diffForHumans() : '—' }}</small></td>
                    <td><a href="{{ route('admin.vm-monitoring.show', $vm) }}" class="btn btn-sm btn-outline-primary">Charts</a></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
