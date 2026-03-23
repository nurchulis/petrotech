<?php

namespace App\Services\VDI;

use App\Models\Vm;
use App\Models\VdiSession;
use App\Models\User;
use Illuminate\Support\Str;

class VdiSessionService
{
    public function __construct(private GuacamoleService $guacamole) {}

    // -------------------------------------------------------------------------
    // Connect — branches on is_dummy
    // -------------------------------------------------------------------------

    public function connect(User $user, Vm $vm): VdiSession
    {
        // Terminate any existing active session for this user on this VM
        VdiSession::where('vm_id', $vm->id)
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->get()
            ->each->terminate();

        if ($vm->is_dummy) {
            return $this->connectDummy($user, $vm);
        }

        return $this->connectGuacamole($user, $vm);
    }

    // -------------------------------------------------------------------------
    // Dummy mode — legacy simulation session
    // -------------------------------------------------------------------------

    private function connectDummy(User $user, Vm $vm): VdiSession
    {
        return VdiSession::create([
            'vm_id'        => $vm->id,
            'user_id'      => $user->id,
            'protocol'     => 'RDP',
            'status'       => VdiSession::STATUS_ACTIVE,
            'session_token'=> Str::uuid(),
            'connected_at' => now(),
            'started_at'   => now(),
        ]);
    }

    // -------------------------------------------------------------------------
    // Real RDP via Guacamole
    // -------------------------------------------------------------------------

    private function connectGuacamole(User $user, Vm $vm): VdiSession
    {
        $token        = $this->guacamole->authenticate();
        $connectionId = $this->guacamole->createRdpConnection($vm, $token);

        return VdiSession::create([
            'vm_id'                   => $vm->id,
            'user_id'                 => $user->id,
            'protocol'                => 'RDP',
            'status'                  => VdiSession::STATUS_CONNECTING,
            'guacamole_connection_id' => $connectionId,
            'started_at'              => now(),
            'connected_at'            => now(),
        ]);
    }

    // -------------------------------------------------------------------------
    // Build Guacamole iframe URL for an active session
    // -------------------------------------------------------------------------

    public function getGuacamoleClientUrl(VdiSession $session): string
    {
        $token = $this->guacamole->authenticate();

        return $this->guacamole->buildClientUrl($session->guacamole_connection_id, $token);
    }

    // -------------------------------------------------------------------------
    // Terminate — cleans up Guacamole connection if applicable
    // -------------------------------------------------------------------------

    public function terminate(VdiSession $session): void
    {
        if ($session->guacamole_connection_id) {
            try {
                $token = $this->guacamole->authenticate();
                $this->guacamole->deleteConnection($session->guacamole_connection_id, $token);
            } catch (\Throwable $e) {
                // Log but don't block the local session termination
                \Log::warning('Guacamole deleteConnection error during terminate', [
                    'session_id' => $session->id,
                    'error'      => $e->getMessage(),
                ]);
            }
        }

        $session->terminate();
    }

    // -------------------------------------------------------------------------
    // Queries
    // -------------------------------------------------------------------------

    public function getActiveSessions(): \Illuminate\Database\Eloquent\Collection
    {
        return VdiSession::with(['vm', 'user'])
            ->where('status', VdiSession::STATUS_ACTIVE)
            ->latest('connected_at')
            ->get();
    }

    public function getUserSessions(User $user): \Illuminate\Database\Eloquent\Collection
    {
        return VdiSession::with('vm')
            ->where('user_id', $user->id)
            ->latest('connected_at')
            ->limit(20)
            ->get();
    }
}
