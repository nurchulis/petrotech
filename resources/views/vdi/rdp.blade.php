@php
    $isWindows = stripos($vm->os_type, 'windows') !== false;
    $isLinux   = !$isWindows;
    $osIcon    = $isWindows ? '⊞' : '🐧';
    $bgFrom    = $isWindows ? '#0a2a6e' : '#1a0a2e';
    $bgTo      = $isWindows ? '#1565c0' : '#2d1b69';
    $accent    = $isWindows ? '#0078d4' : '#e95420';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $isWindows ? 'RDP' : 'SSH' }} — {{ $vm->vm_name }}</title>
    <style>
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        body{font-family:'Segoe UI',system-ui,sans-serif;background:#000;overflow:hidden;height:100vh;width:100vw;user-select:none}

        /* ── Connection bar ─────────────────────────────────────────── */
        #rdp-bar{
            position:fixed;top:0;left:50%;transform:translateX(-50%);
            z-index:9999;background:linear-gradient(135deg,#0f2540 0%,#1a3c6b 100%);
            color:#fff;display:flex;align-items:center;gap:10px;
            padding:0 14px;height:36px;border-radius:0 0 10px 10px;
            box-shadow:0 4px 24px rgba(0,0,0,.5);font-size:12px;white-space:nowrap;
            transition:transform .3s ease;
        }
        #rdp-bar.hidden{transform:translateX(-50%) translateY(-100%)}
        .pill{background:rgba(255,255,255,.12);border-radius:20px;padding:2px 9px;font-size:11px;color:#a8c8f0}
        .btn-bar{background:none;border:1px solid rgba(255,255,255,.25);color:#fff;border-radius:5px;
            padding:2px 9px;font-size:11px;cursor:pointer;transition:background .15s}
        .btn-bar:hover{background:rgba(255,255,255,.15)}
        .btn-bar.danger:hover{background:#d63939;border-color:#d63939}
        .dot-live{width:7px;height:7px;border-radius:50%;background:#2fb344;display:inline-block;
            animation:pulse-g 2s infinite}
        @keyframes pulse-g{0%,100%{box-shadow:0 0 0 0 rgba(47,179,68,.5)}50%{box-shadow:0 0 0 5px rgba(47,179,68,0)}}

        /* ── Loading overlay ──────────────────────────────────────── */
        #loading{position:fixed;inset:0;background:#000d1a;z-index:8000;
            display:flex;flex-direction:column;align-items:center;justify-content:center;gap:16px;
            transition:opacity .6s ease}
        #loading.fade-out{opacity:0;pointer-events:none}
        .ld-logo{font-size:2.8rem;animation:float 2s ease-in-out infinite}
        @keyframes float{0%,100%{transform:translateY(0)}50%{transform:translateY(-8px)}}
        .ld-title{color:#a8c8f0;font-size:1.05rem;font-weight:600;letter-spacing:.05em}
        .ld-sub{color:#4a7fa5;font-size:.82rem}
        .ld-bar{width:260px;height:3px;background:rgba(255,255,255,.1);border-radius:99px;overflow:hidden}
        .ld-fill{height:100%;background:linear-gradient(90deg,{{ $isWindows ? '#0078d4,#50a8ff' : '#e95420,#ff8c55' }});border-radius:99px;width:0;transition:width .2s ease}
        .ld-step{color:#4a7fa5;font-size:.75rem;min-height:1em}

        /* ── Desktop ──────────────────────────────────────────────── */
        #desktop{position:fixed;inset:0;display:none;flex-direction:column;
            background:linear-gradient(135deg,{{ $bgFrom }} 0%,{{ $bgTo }} 100%);overflow:hidden}
        #desktop.visible{display:flex}
        #desktop::before{content:'';position:absolute;inset:0;
            background:radial-gradient(ellipse at 25% 55%,rgba(26,60,107,.45) 0%,transparent 60%),
                        radial-gradient(ellipse at 80% 20%,rgba({{ $isWindows ? '0,120,212' : '233,84,32' }},.15) 0%,transparent 55%);
            pointer-events:none}

        /* ── Desktop Icons ───────────────────────────────────────── */
        #d-icons{position:absolute;top:54px;left:20px;display:flex;flex-direction:column;gap:16px}
        .d-icon{display:flex;flex-direction:column;align-items:center;gap:3px;
            padding:8px;border-radius:8px;cursor:pointer;width:76px;transition:background .15s;text-align:center}
        .d-icon:hover{background:rgba(255,255,255,.12)}
        .d-icon .ic{font-size:1.9rem}
        .d-icon .lb{color:#fff;font-size:.68rem;text-shadow:0 1px 4px rgba(0,0,0,.7)}

        /* ── Window chrome ───────────────────────────────────────── */
        .win{position:absolute;background:{{ $isWindows ? '#1e2733' : '#2b1d3a' }};border-radius:{{ $isWindows ? '8px' : '10px' }};
            box-shadow:0 20px 80px rgba(0,0,0,.7);overflow:hidden;display:flex;flex-direction:column;
            animation:wopen .22s cubic-bezier(.22,.61,.36,1)}
        @keyframes wopen{from{transform:scale(.93);opacity:0}to{transform:scale(1);opacity:1}}
        .wtb{background:{{ $isWindows ? '#1a2535 ' : '#1e1230' }};display:flex;align-items:center;padding:7px 10px;gap:7px;
            border-bottom:1px solid rgba(255,255,255,.06)}
        .wbtn{width:12px;height:12px;border-radius:50%;border:none;cursor:pointer}
        .wbtn.c{background:#ff5f57}.wbtn.m{background:#ffbd2e}.wbtn.x{background:#28c941}
        .wtitle{color:#7a94b0;font-size:.75rem;flex:1;text-align:center}

        @if($isWindows)
        /* ══ WINDOWS ══════════════════════════════════════════════ */
        /* App window */
        #win-app{top:52px;left:130px;width:750px;height:470px}
        .app-toolbar{background:#111e2d;padding:5px 10px;display:flex;align-items:center;gap:7px;
            border-bottom:1px solid #1e2e42}
        .app-toolbar button{background:#172230;border:1px solid #1e2e42;color:#7a94b0;
            border-radius:4px;padding:3px 9px;font-size:.72rem;cursor:pointer;transition:background .1s}
        .app-toolbar button:hover{background:#1e3048;color:#fff}
        .app-content{flex:1;display:flex;background:#0d1520;overflow:hidden}
        .app-sidebar{width:152px;background:#111e2d;border-right:1px solid #1e2e42;padding:8px 0}
        .app-sidebar div{padding:6px 14px;font-size:.75rem;color:#5a7a9a;cursor:pointer;transition:background .1s}
        .app-sidebar div:hover{background:rgba(255,255,255,.05);color:#fff}
        .app-sidebar div.act{background:rgba(0,120,212,.25);color:#a8c8f0;border-left:2px solid #0078d4}
        .app-viewport{flex:1;position:relative;background:#0b1318;overflow:hidden;display:flex;align-items:center;justify-content:center}
        .seismic-grid{position:absolute;inset:0;
            background-image:linear-gradient(rgba(0,120,212,.06) 1px,transparent 1px),
                             linear-gradient(90deg,rgba(0,120,212,.06) 1px,transparent 1px);
            background-size:36px 36px}
        /* File explorer */
        #win-files{top:100px;left:60px;width:480px;height:300px;display:none}
        .fe-body{flex:1;display:flex;background:#131f2b}
        .fe-tree{width:130px;background:#0e1929;border-right:1px solid #1e2e42;padding:8px 0;font-size:.75rem}
        .fe-tree div{padding:5px 12px;color:#5a7a9a;cursor:pointer}
        .fe-tree div:hover{color:#fff;background:rgba(255,255,255,.05)}
        .fe-main{flex:1;padding:12px;display:flex;flex-wrap:wrap;gap:12px;align-content:flex-start}
        .fe-item{display:flex;flex-direction:column;align-items:center;gap:3px;width:64px;cursor:pointer;padding:6px;border-radius:4px}
        .fe-item:hover{background:rgba(255,255,255,.08)}
        .fe-item .ic{font-size:1.5rem}
        .fe-item .lb{color:#8fa8c8;font-size:.65rem;text-align:center;word-break:break-all}
        /* Taskbar */
        #taskbar{position:relative;z-index:100;height:44px;
            background:rgba(8,18,32,.88);backdrop-filter:blur(20px);
            border-top:1px solid rgba(255,255,255,.07);
            display:flex;align-items:center;padding:0 10px;gap:6px;margin-top:auto}
        #start{width:38px;height:34px;display:flex;align-items:center;justify-content:center;
            border-radius:6px;cursor:pointer;font-size:1.05rem;color:#a8c8f0;
            transition:background .15s}
        #start:hover{background:rgba(255,255,255,.1)}
        .tb-items{flex:1;display:flex;align-items:center;justify-content:center;gap:3px}
        .tb-item{width:38px;height:34px;display:flex;align-items:center;justify-content:center;
            border-radius:6px;cursor:pointer;font-size:1.1rem;
            position:relative;transition:background .15s}
        .tb-item:hover{background:rgba(255,255,255,.1)}
        .tb-item.active::after{content:'';position:absolute;bottom:2px;left:50%;transform:translateX(-50%);
            width:4px;height:4px;border-radius:50%;background:#0078d4}
        #tb-clock{color:#a8c8f0;font-size:.74rem;text-align:right;line-height:1.4;min-width:52px}
        @else
        /* ══ LINUX / DEFAULT ══════════════════════════════════════ */
        /* Terminal window */
        #win-term{top:55px;left:120px;width:700px;height:420px}
        .term-body{flex:1;background:#0d1117;padding:14px;font-family:'Courier New',monospace;
            font-size:.8rem;color:#58d68d;overflow-y:auto;line-height:1.7}
        .term-line{white-space:pre}
        .term-line .prompt{color:#e95420}
        .term-line .cmd{color:#e8f4fd}
        .term-line .out{color:#7fa8c8}
        .blink{display:inline-block;animation:blink .8s infinite}
        @keyframes blink{0%,100%{opacity:1}50%{opacity:0}}
        /* System monitor window */
        #win-sysmon{top:90px;left:50px;width:360px;height:260px;display:none}
        .sysmon-body{flex:1;padding:12px;background:#0d1117;font-size:.75rem}
        .smon-row{display:flex;justify-content:space-between;align-items:center;margin-bottom:8px}
        .smon-bar{height:6px;border-radius:3px;background:#1e2d3e;overflow:hidden;flex:1;margin:0 10px}
        .smon-fill{height:100%;background:#e95420;border-radius:3px;transition:width 1s}
        /* GNOME-style panel */
        #taskbar{position:relative;z-index:100;height:38px;
            background:rgba(30,20,50,.92);backdrop-filter:blur(20px);
            border-top:1px solid rgba(255,255,255,.07);
            display:flex;align-items:center;padding:0 10px;gap:6px;margin-top:auto}
        #start{width:34px;height:30px;display:flex;align-items:center;justify-content:center;
            border-radius:5px;cursor:pointer;font-size:1.1rem;color:#e95420;
            transition:background .15s}
        #start:hover{background:rgba(255,255,255,.1)}
        .tb-items{flex:1;display:flex;align-items:center;justify-content:center;gap:3px}
        .tb-item{width:34px;height:30px;display:flex;align-items:center;justify-content:center;
            border-radius:5px;cursor:pointer;font-size:1rem;
            position:relative;transition:background .15s}
        .tb-item:hover{background:rgba(255,255,255,.1)}
        .tb-item.active::after{content:'';position:absolute;bottom:2px;left:50%;transform:translateX(-50%);
            width:4px;height:4px;border-radius:50%;background:#e95420}
        #tb-clock{color:#a8c8f0;font-size:.72rem;text-align:right;line-height:1.4;min-width:52px}
        @endif

        /* ── Toast ──────────────────────────────────────────────── */
        #toast{position:fixed;bottom:52px;right:18px;background:#1e2d3e;
            border:1px solid rgba(255,255,255,.1);border-radius:10px;padding:10px 14px;
            color:#c8d8ee;font-size:.8rem;box-shadow:0 8px 32px rgba(0,0,0,.5);z-index:9000;
            translate:0 20px;opacity:0;transition:translate .3s ease,opacity .3s ease;max-width:260px}
        #toast.show{translate:0 0;opacity:1}
        #toast .tt{font-weight:600;color:#fff;margin-bottom:3px;font-size:.85rem}
    </style>
</head>
<body>

{{-- Loading overlay --}}
<div id="loading">
    <div class="ld-logo">{{ $isWindows ? '🖥️' : '🐧' }}</div>
    <div class="ld-title">Connecting to {{ $vm->vm_name }}</div>
    <div class="ld-sub">{{ $vm->ip_address }} · {{ $vm->os_type }}</div>
    <div class="ld-bar"><div class="ld-fill" id="ld-fill"></div></div>
    <div class="ld-step" id="ld-step">Initialising…</div>
</div>

{{-- Top bar --}}
<div id="rdp-bar">
    <span class="dot-live"></span>
    <strong>{{ $vm->vm_name }}</strong>
    <span class="pill">{{ $vm->ip_address }}</span>
    <span class="pill">{{ $vm->os_type }}</span>
    <span class="pill" id="sess-time">00:00</span>
    <button class="btn-bar" onclick="document.getElementById('rdp-bar').classList.toggle('hidden')">▲ Hide</button>
    <button class="btn-bar" onclick="document.documentElement.requestFullscreen()">⛶</button>
    <button class="btn-bar danger" onclick="doDisconnect()">✕ Disconnect</button>
</div>

{{-- Desktop --}}
<div id="desktop">
    {{-- Icons --}}
    <div id="d-icons">
        @if($isWindows)
        <div class="d-icon" ondblclick="openWin('win-files')">
            <div class="ic">📁</div><div class="lb">File Explorer</div>
        </div>
        <div class="d-icon" ondblclick="openWin('win-app')">
            <div class="ic">⛏️</div><div class="lb">{{ $vm->application_name }}</div>
        </div>
        <div class="d-icon"><div class="ic">🗑️</div><div class="lb">Recycle Bin</div></div>
        @else
        <div class="d-icon" ondblclick="openWin('win-term')">
            <div class="ic">⬛</div><div class="lb">Terminal</div>
        </div>
        <div class="d-icon" ondblclick="openWin('win-sysmon')">
            <div class="ic">📊</div><div class="lb">System Monitor</div>
        </div>
        <div class="d-icon"><div class="ic">⛏️</div><div class="lb">{{ $vm->application_name }}</div></div>
        @endif
    </div>

    @if($isWindows)
    {{-- ╔══ WINDOWS WINDOWS ══╗ --}}

    {{-- Main app window --}}
    <div class="win" id="win-app">
        <div class="wtb">
            <button class="wbtn c" onclick="closeWin('win-app')"></button>
            <button class="wbtn m" onclick="document.getElementById('win-app').style.display='none'"></button>
            <button class="wbtn x"></button>
            <span class="wtitle">{{ $vm->application_name }} — Petrotechnical Workstation</span>
        </div>
        <div class="app-toolbar">
            <button>File</button><button>Edit</button><button>View</button>
            <button>Tools</button><button>Windows</button><button>Help</button>
        </div>
        <div class="app-content">
            <div class="app-sidebar">
                <div class="act">🗂 Project</div>
                <div>🌐 Wells</div><div>📊 Seismic</div><div>🔧 Models</div>
                <div>📈 Surfaces</div><div>🗺 Maps</div><div>📋 Reports</div>
            </div>
            <div class="app-viewport">
                <div class="seismic-grid"></div>
                <canvas id="seismic-canvas" style="position:absolute;inset:0;width:100%;height:100%;opacity:.7"></canvas>
                <div style="position:relative;text-align:center;color:#3a5a7a;font-size:.82rem">
                    <div style="font-size:1.9rem;margin-bottom:6px">⛏️</div>
                    <div>{{ $vm->application_name }}</div>
                    <div style="font-size:.72rem;margin-top:3px">Loading project data…</div>
                </div>
            </div>
        </div>
    </div>

    {{-- File Explorer --}}
    <div class="win" id="win-files" style="display:none">
        <div class="wtb">
            <button class="wbtn c" onclick="closeWin('win-files')"></button>
            <button class="wbtn m" onclick="document.getElementById('win-files').style.display='none'"></button>
            <button class="wbtn x"></button>
            <span class="wtitle">File Explorer</span>
        </div>
        <div class="fe-body">
            <div class="fe-tree">
                <div>📌 Quick Access</div>
                <div>🖥️ This PC</div>
                <div>🌐 Network</div>
                <div style="color:#5a8ab0;padding-left:20px">Z: Projects</div>
                <div style="padding-left:20px">C: System</div>
            </div>
            <div class="fe-main">
                @foreach(['Upstream_Data','Petrel_Projects','Reports_2026','Seismic_Surveys','Well_Logs'] as $f)
                <div class="fe-item"><div class="ic">📂</div><div class="lb">{{ $f }}</div></div>
                @endforeach
                @foreach(['README.txt','config.ini','run.bat'] as $f)
                <div class="fe-item"><div class="ic">📄</div><div class="lb">{{ $f }}</div></div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Windows Taskbar --}}
    <div id="taskbar">
        <div id="start" title="Start">⊞</div>
        <div class="tb-items">
            <div class="tb-item active" title="{{ $vm->application_name }}">⛏️</div>
            <div class="tb-item" title="File Explorer" ondblclick="openWin('win-files')">📁</div>
            <div class="tb-item" title="Task Manager">📊</div>
            <div class="tb-item" title="Notepad">📝</div>
        </div>
        <div id="tb-clock"></div>
    </div>

    @else
    {{-- ╔══ LINUX LINUX ══╗ --}}

    {{-- Terminal window --}}
    <div class="win" id="win-term">
        <div class="wtb" style="background:#111;">
            <button class="wbtn c" onclick="closeWin('win-term')"></button>
            <button class="wbtn m" onclick="document.getElementById('win-term').style.display='none'"></button>
            <button class="wbtn x"></button>
            <span class="wtitle" style="color:#e95420">{{ auth()->user()->name ?? 'user' }}@{{ strtolower($vm->vm_name) }} — bash</span>
        </div>
        <div class="term-body" id="term-body">
            <div class="term-line"><span class="out">Last login: {{ now()->format('D M d H:i:s Y') }} from 10.10.0.1</span></div>
            <div class="term-line">&nbsp;</div>
        </div>
    </div>

    {{-- System Monitor --}}
    <div class="win" id="win-sysmon" style="display:none">
        <div class="wtb" style="background:#111;">
            <button class="wbtn c" onclick="closeWin('win-sysmon')"></button>
            <button class="wbtn m" onclick="document.getElementById('win-sysmon').style.display='none'"></button>
            <button class="wbtn x"></button>
            <span class="wtitle">System Monitor</span>
        </div>
        <div class="sysmon-body">
            @foreach(['CPU','Memory','Disk I/O','Network'] as $res)
            <div class="smon-row">
                <span style="color:#a8c8f0;width:64px">{{ $res }}</span>
                <div class="smon-bar"><div class="smon-fill" id="bar-{{ Str::slug($res) }}" style="width:{{ rand(15,75) }}%"></div></div>
                <span style="color:#5a8aaa;width:36px;text-align:right" id="pct-{{ Str::slug($res) }}">—%</span>
            </div>
            @endforeach
            <div style="margin-top:10px;color:#3a5a7a;font-size:.72rem">
                Kernel: Linux 5.15.0-100-generic · Uptime: 3d 14h · Load: 0.42 0.38 0.31
            </div>
        </div>
    </div>

    {{-- GNOME / Linux Top Panel --}}
    <div id="taskbar">
        <div id="start" title="Activities">🐧</div>
        <div class="tb-items">
            <div class="tb-item active" title="Terminal" ondblclick="openWin('win-term')">⬛</div>
            <div class="tb-item" title="System Monitor" ondblclick="openWin('win-sysmon')">📊</div>
            <div class="tb-item" title="{{ $vm->application_name }}">⛏️</div>
        </div>
        <div id="tb-clock"></div>
    </div>
    @endif
</div>

{{-- Toast --}}
<div id="toast"><div class="tt" id="t-title">Connected</div><div id="t-body"></div></div>

{{-- Disconnect form --}}
@if($session)
<form id="disc-form" method="POST" action="{{ route('vdi.terminate', $session) }}" style="display:none">@csrf</form>
@endif

<script>
const IS_WINDOWS = {{ $isWindows ? 'true' : 'false' }};

// ─── Loading steps ─────────────────────────────────────────────
const steps = IS_WINDOWS ? [
    [10, 'Resolving {{ $vm->ip_address }}…'],
    [25, 'Establishing RDP tunnel (TLS 1.3)…'],
    [42, 'Authenticating with domain controller…'],
    [60, 'Negotiating display settings (1680×940)…'],
    [78, 'Loading Windows session…'],
    [92, 'Applying group policies…'],
    [100, 'Desktop ready!'],
] : [
    [10, 'Resolving {{ $vm->ip_address }}…'],
    [28, 'Establishing SSH tunnel (AES-256)…'],
    [50, 'Authenticating public key…'],
    [70, 'Starting desktop session…'],
    [88, 'Launching Xorg display…'],
    [100, 'Desktop ready!'],
];

let si = 0;
const fill = document.getElementById('ld-fill');
const stepEl = document.getElementById('ld-step');
function runStep(){
    if(si >= steps.length) return;
    const [p,m] = steps[si++];
    fill.style.width = p + '%';
    stepEl.textContent = m;
    if(si < steps.length) setTimeout(runStep, 460 + Math.random()*280);
    else setTimeout(showDesktop, 550);
}
setTimeout(runStep, 250);

function showDesktop(){
    const ov = document.getElementById('loading');
    ov.classList.add('fade-out');
    setTimeout(()=>{
        ov.style.display = 'none';
        document.getElementById('desktop').classList.add('visible');
        startClock(); startTimer();
        IS_WINDOWS ? drawSeismic() : startTerminal();
        showToast('Session Active','Remote {{ $vm->os_type }} — {{ $vm->vm_name }} connected.');
    }, 600);
}

function startClock(){
    function u(){
        const n = new Date();
        const t = n.toLocaleTimeString('en-US',{hour:'2-digit',minute:'2-digit',hour12:false});
        const d = n.toLocaleDateString('en-GB',{day:'2-digit',month:'2-digit',year:'2-digit'});
        document.getElementById('tb-clock').innerHTML = `<div>${t}</div><div style="color:#4a7fa5">${d}</div>`;
    }
    u(); setInterval(u, 1000);
}

let secs = 0;
function startTimer(){
    setInterval(()=>{
        secs++;
        const m = String(Math.floor(secs/60)).padStart(2,'0');
        const s = String(secs%60).padStart(2,'0');
        document.getElementById('sess-time').textContent = m+':'+s;
    }, 1000);
}

// ── Seismic canvas (Windows) ────────────────────────────────────
function drawSeismic(){
    const c = document.getElementById('seismic-canvas');
    if(!c) return;
    const vp = c.parentElement;
    c.width = vp.offsetWidth; c.height = vp.offsetHeight;
    const ctx = c.getContext('2d');
    const L = 10, P = 70;
    const amp = Array.from({length:L},()=>7+Math.random()*18);
    const off = Array.from({length:L},()=>Math.random()*Math.PI*2);
    let fr = 0;
    function draw(){
        ctx.clearRect(0,0,c.width,c.height);
        for(let l=0;l<L;l++){
            const y0 = (c.height/(L+1))*(l+1);
            ctx.beginPath();
            ctx.strokeStyle = `rgba(0,120,212,${0.25+l*0.045})`;
            ctx.lineWidth = 1.2;
            for(let p=0;p<=P;p++){
                const x = (c.width/P)*p;
                const w = Math.sin((p/P)*Math.PI*6+fr*0.014+off[l])*amp[l];
                const sp = Math.random()<0.012 ? (Math.random()-.5)*38 : 0;
                p===0 ? ctx.moveTo(x,y0+w+sp) : ctx.lineTo(x,y0+w+sp);
            }
            ctx.stroke();
        }
        fr++; requestAnimationFrame(draw);
    }
    draw();
}

// ── Terminal emulator (Linux) ───────────────────────────────────
const TERM_LINES = [
    {t:300,  html:'<span class="prompt">{{ addslashes(auth()->user()->name ?? "user") }}@{{ strtolower(addslashes($vm->vm_name)) }}:~$</span> <span class="cmd">uname -a</span>'},
    {t:800,  html:'<span class="out">Linux {{ strtolower(addslashes($vm->vm_name)) }} 5.15.0-100-generic #110-Ubuntu SMP Tue Feb 20 09:41:30 UTC 2026 x86_64</span>'},
    {t:1400, html:'<span class="prompt">{{ addslashes(auth()->user()->name ?? "user") }}@{{ strtolower(addslashes($vm->vm_name)) }}:~$</span> <span class="cmd">df -h /</span>'},
    {t:1900, html:'<span class="out">Filesystem      Size  Used Avail Use%  Mounted on</span>'},
    {t:2000, html:'<span class="out">/dev/sda1       {{ $vm->cpu_cores * 50 }}G   {{ rand(20,60) }}G  {{ rand(10,40) }}G  {{ rand(30,70) }}%   /</span>'},
    {t:2600, html:'<span class="prompt">{{ addslashes(auth()->user()->name ?? "user") }}@{{ strtolower(addslashes($vm->vm_name)) }}:~$</span> <span class="cmd">top -bn1 | head -5</span>'},
    {t:3200, html:'<span class="out">top - {{ now()->format("H:i:s") }} up 3 days, 14:22,  1 user,  load average: 0.42, 0.38, 0.31</span>'},
    {t:3300, html:'<span class="out">Tasks: 187 total,   1 running, 186 sleeping,   0 stopped,   0 zombie</span>'},
    {t:3400, html:'<span class="out">%Cpu(s):  {{ rand(5,25) }}.3 us,  1.2 sy,  0.0 ni, {{ rand(70,90) }}.1 id</span>'},
    {t:4200, html:'<span class="prompt">{{ addslashes(auth()->user()->name ?? "user") }}@{{ strtolower(addslashes($vm->vm_name)) }}:~$</span> <span class="cmd"></span><span class="blink">█</span>'},
];
function startTerminal(){
    const body = document.getElementById('term-body');
    if(!body) return;
    TERM_LINES.forEach(({t,html})=>{
        setTimeout(()=>{
            const div = document.createElement('div');
            div.className = 'term-line';
            div.innerHTML = html;
            body.appendChild(div);
            body.scrollTop = body.scrollHeight;
        }, t);
    });
}

// ── Window helpers ──────────────────────────────────────────────
function closeWin(id){
    const el=document.getElementById(id);
    el.style.transition='transform .18s,opacity .18s';
    el.style.transform='scale(.91)';el.style.opacity='0';
    setTimeout(()=>el.style.display='none',180);
}
function openWin(id){
    const el=document.getElementById(id);
    el.style.display='flex';el.style.transform='';el.style.opacity='';el.style.transition='';
}

// ── Toast ────────────────────────────────────────────────────────
function showToast(title,body){
    document.getElementById('t-title').textContent=title;
    document.getElementById('t-body').textContent=body;
    const t=document.getElementById('toast');
    t.classList.add('show');
    setTimeout(()=>t.classList.remove('show'),4500);
}

// ── Disconnect ───────────────────────────────────────────────────
function doDisconnect(){
    if(!confirm('Disconnect from {{ addslashes($vm->vm_name) }}?')) return;
    @if($session)
    document.getElementById('disc-form').submit();
    @else
    window.location.href='{{ route("vdi.index") }}';
    @endif
}
</script>
</body>
</html>
