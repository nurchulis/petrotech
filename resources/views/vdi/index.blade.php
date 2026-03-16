@extends('layouts.app')
@section('title', 'VDI Access')
@section('breadcrumb', 'VDI Access / Virtual Machines')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-0" style="color:#1a3c6b">VDI Access</h2>
        <small class="text-muted">Connect to petrotechnical application VMs</small>
    </div>
</div>

{{-- Active Sessions --}}
@if($activeSessions->isNotEmpty())
<div class="alert alert-info mb-4">
    <strong>Active Sessions:</strong>
    @foreach($activeSessions as $s)
        <span class="badge bg-info-lt text-info ms-1">{{ $s->vm->vm_name }} ({{ $s->connected_at->diffForHumans() }})</span>
    @endforeach
</div>
@endif

{{-- VM Cards --}}
<div class="row g-3">
    @forelse($vms as $vm)
    <div class="col-md-6 col-xl-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <h4 class="mb-0" style="font-size:1rem;font-weight:700">{{ $vm->vm_name }}</h4>
                        <small class="text-muted">{{ $vm->application_name }}</small>
                    </div>
                    <span class="badge bg-{{ $vm->status_badge }}-lt text-{{ $vm->status_badge }} d-flex align-items-center gap-1">
                        @if($vm->status === 'running') <span style="width:8px;height:8px;border-radius:50%;background:#2fb344;display:inline-block"></span> @endif
                        {{ ucfirst($vm->status) }}
                    </span>
                </div>

                <div class="mb-3" style="font-size:.82rem">
                    <div class="row g-1">
                        <div class="col-6 text-muted">OS:</div><div class="col-6">{{ $vm->os_type }}</div>
                        <div class="col-6 text-muted">Region:</div><div class="col-6">{{ $vm->region ?? '—' }}</div>
                        <div class="col-6 text-muted">Data Center:</div><div class="col-6">{{ $vm->data_center ?? '—' }}</div>
                        <div class="col-6 text-muted">CPU / RAM:</div><div class="col-6">{{ $vm->cpu_cores }}c / {{ $vm->ram_gb }}GB</div>
                        @if($vm->has_gpu)
                        <div class="col-6 text-muted">GPU:</div><div class="col-6"><span class="badge bg-purple-lt">{{ $vm->gpu_model }}</span></div>
                        @endif
                    </div>
                </div>

                <div class="d-flex gap-2">
                                    @if($vm->status === 'running')
                        <form id="cf-{{ $vm->id }}" method="POST" action="{{ route('vdi.connect', $vm) }}" style="display:none">@csrf</form>
                        <button type="button" id="cb-{{ $vm->id }}"
                            class="btn btn-sm" style="background:#1a3c6b;color:#fff"
                            onclick="rdpConnect({{ $vm->id }}, '{{ route('vdi.rdp', $vm) }}')"
                        >🖥️ Connect RDP</button>
                    @else
                        <button class="btn btn-sm btn-outline-secondary" disabled>VM Offline</button>
                    @endif
                    <a href="{{ route('vdi.show', $vm) }}" class="btn btn-sm btn-outline-secondary">Details</a>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="card border-0 shadow-sm text-center py-5">
            <div class="text-muted">No virtual machines available.</div>
        </div>
    </div>
    @endforelse
</div>
@endsection

@push('scripts')
<script>
function rdpConnect(vmId, rdpUrl) {
    const btn  = document.getElementById('cb-' + vmId);
    const form = document.getElementById('cf-' + vmId);
    if (!btn || !form) return;
    btn.disabled = true;
    btn.textContent = '⏳ Connecting…';
    fetch(form.action, {
        method: 'POST',
        body: new FormData(form),
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    }).then(() => {
        window.open(rdpUrl, '_blank');
        btn.disabled = false;
        btn.innerHTML = '🖥️ Connect RDP';
    }).catch(() => {
        btn.disabled = false;
        btn.innerHTML = '🖥️ Connect RDP';
        alert('Connection failed. Please try again.');
    });
}
</script>
@endpush
