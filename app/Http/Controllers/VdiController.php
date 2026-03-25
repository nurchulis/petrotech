<?php

namespace App\Http\Controllers;

use App\Models\Vm;
use App\Models\VdiSession;
use App\Services\VDI\VdiSessionService;
use App\Services\VDI\VdiAccessService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class VdiController extends Controller
{
    public function __construct(
        private VdiSessionService $service,
        private VdiAccessService $accessService,
    ) {}

    public function index(): View
    {
        $user = auth()->user();

        // Admins see all VMs, regular users see only accessible VMs
        if ($user->hasRole(['admin', 'super_admin'])) {
            $vms = Vm::with('assignedUser')->orderBy('status', 'desc')->orderBy('region')->get();
        } else {
            $vms = $this->accessService->getAccessibleVms($user);
        }

        $activeSessions = $this->service->getUserSessions($user);
        return view('vdi.index', compact('vms', 'activeSessions'));
    }

    public function show(Vm $vm): View
    {
        $latestMetric = $vm->latestMetricData();
        return view('vdi.show', compact('vm', 'latestMetric'));
    }

    public function connect(Vm $vm): mixed
    {
        abort_if($vm->status !== 'running', 403, 'VM is not running.');
        $session = $this->service->connect(auth()->user(), $vm);

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json(['ok' => true, 'session_id' => $session->id]);
        }

        return redirect()->route('vdi.rdp', $vm)
            ->with('success', "Connected to {$vm->vm_name}");
    }

    public function rdp(Vm $vm): View
    {
        abort_if($vm->status !== 'running', 403, 'VM is not running.');

        $session = VdiSession::where('vm_id', $vm->id)
            ->where('user_id', auth()->id())
            ->whereIn('status', [VdiSession::STATUS_ACTIVE, VdiSession::STATUS_CONNECTING])
            ->latest('connected_at')
            ->first();

        // Real RDP via Guacamole iframe
        if (!$vm->is_dummy && $session?->guacamole_connection_id) {
            $clientUrl = $this->service->getGuacamoleClientUrl($session);
            return view('vdi.rdp-guacamole', compact('vm', 'session', 'clientUrl'));
        }

        // Dummy simulation
        return view('vdi.rdp', compact('vm', 'session'));
    }

    public function terminate(VdiSession $session): RedirectResponse
    {
        abort_if(
            $session->user_id !== auth()->id() && !auth()->user()->hasRole(['admin', 'super_admin']),
            403
        );
        $this->service->terminate($session);
        return back()->with('success', 'VDI session terminated.');
    }
}
