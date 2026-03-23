<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RDP — {{ $vm->vm_name }}</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', system-ui, sans-serif; background: #000d1a; overflow: hidden; height: 100vh; width: 100vw; }

        /* ── Top bar ───────────────────────────────────────────────────── */
        #rdp-bar {
            position: fixed; top: 0; left: 50%; transform: translateX(-50%);
            z-index: 9999; background: linear-gradient(135deg, #0f2540 0%, #1a3c6b 100%);
            color: #fff; display: flex; align-items: center; gap: 10px;
            padding: 0 16px; height: 38px; border-radius: 0 0 10px 10px;
            box-shadow: 0 4px 24px rgba(0,0,0,.5); font-size: 12px; white-space: nowrap;
            transition: transform .3s ease;
        }
        #rdp-bar.hidden { transform: translateX(-50%) translateY(-100%); }
        .pill { background: rgba(255,255,255,.12); border-radius: 20px; padding: 2px 9px; font-size: 11px; color: #a8c8f0; }
        .dot-live { width: 7px; height: 7px; border-radius: 50%; background: #2fb344; display: inline-block; animation: pulse-g 2s infinite; }
        @keyframes pulse-g { 0%,100% { box-shadow: 0 0 0 0 rgba(47,179,68,.5); } 50% { box-shadow: 0 0 0 5px rgba(47,179,68,0); } }
        .btn-bar { background: none; border: 1px solid rgba(255,255,255,.25); color: #fff; border-radius: 5px; padding: 2px 10px; font-size: 11px; cursor: pointer; transition: background .15s; }
        .btn-bar:hover { background: rgba(255,255,255,.15); }
        .btn-bar.danger:hover { background: #d63939; border-color: #d63939; }
        #sess-time { font-variant-numeric: tabular-nums; min-width: 36px; text-align: center; }

        /* ── Guacamole iframe ──────────────────────────────────────────── */
        #guac-frame {
            position: fixed; inset: 38px 0 0 0;
            width: 100%; height: calc(100vh - 38px);
            border: none; background: #000;
        }

        /* ── Error overlay ─────────────────────────────────────────────── */
        #error-overlay {
            display: none; position: fixed; inset: 38px 0 0 0;
            background: #000d1a; z-index: 8000;
            flex-direction: column; align-items: center; justify-content: center; gap: 12px;
        }
        #error-overlay.show { display: flex; }
        .err-icon { font-size: 2.5rem; }
        .err-title { color: #e0534a; font-size: 1.1rem; font-weight: 600; }
        .err-sub { color: #4a7fa5; font-size: .85rem; max-width: 380px; text-align: center; }
    </style>
</head>
<body>

{{-- Top bar --}}
<div id="rdp-bar">
    <span class="dot-live"></span>
    <strong>{{ $vm->vm_name }}</strong>
    <span class="pill">{{ $vm->rdp_host }}:{{ $vm->rdp_port }}</span>
    <span class="pill">{{ $vm->os_type }}</span>
    <span class="pill" id="sess-time">00:00</span>
    <button class="btn-bar" onclick="document.getElementById('rdp-bar').classList.toggle('hidden')">▲ Hide</button>
    <button class="btn-bar" onclick="document.documentElement.requestFullscreen()">⛶</button>
    <button class="btn-bar danger" onclick="doDisconnect()">✕ Disconnect</button>
</div>

{{-- Guacamole client iframe --}}
<iframe
    id="guac-frame"
    src="{{ $clientUrl }}"
    allowfullscreen
    title="Remote Desktop — {{ $vm->vm_name }}"
></iframe>

{{-- Error fallback --}}
<div id="error-overlay">
    <div class="err-icon">🔌</div>
    <div class="err-title">Connection failed</div>
    <div class="err-sub">Could not load the Guacamole session. The VM may be unreachable or the session expired.</div>
    <a href="{{ route('vdi.index') }}" style="color:#a8c8f0;font-size:.85rem;margin-top:4px">← Return to VDI</a>
</div>

{{-- Disconnect form --}}
@if($session)
<form id="disc-form" method="POST" action="{{ route('vdi.terminate', $session) }}" style="display:none">@csrf</form>
@endif

<script>
// ── Session timer ─────────────────────────────────────────────────
let secs = 0;
setInterval(() => {
    secs++;
    const m = String(Math.floor(secs / 60)).padStart(2, '0');
    const s = String(secs % 60).padStart(2, '0');
    document.getElementById('sess-time').textContent = m + ':' + s;
}, 1000);

// ── Iframe error detection ────────────────────────────────────────
document.getElementById('guac-frame').addEventListener('error', () => {
    document.getElementById('error-overlay').classList.add('show');
});

// ── Disconnect ────────────────────────────────────────────────────
function doDisconnect() {
    if (!confirm('Disconnect from {{ addslashes($vm->vm_name) }}?')) return;
    @if($session)
    document.getElementById('disc-form').submit();
    @else
    window.location.href = '{{ route("vdi.index") }}';
    @endif
}
</script>
</body>
</html>
