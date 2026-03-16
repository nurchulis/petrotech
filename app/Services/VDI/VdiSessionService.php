<?php

namespace App\Services\VDI;

use App\Models\Vm;
use App\Models\VdiSession;
use App\Models\User;
use Illuminate\Support\Str;

class VdiSessionService
{
    public function connect(User $user, Vm $vm): VdiSession
    {
        // Terminate any existing active session for this user on this VM
        VdiSession::where('vm_id', $vm->id)
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->get()
            ->each->terminate();

        return VdiSession::create([
            'vm_id'         => $vm->id,
            'user_id'       => $user->id,
            'protocol'      => 'RDP',
            'status'        => 'active',
            'session_token' => Str::uuid(),
            'connected_at'  => now(),
        ]);
    }

    public function terminate(VdiSession $session): void
    {
        $session->terminate();
    }

    public function getActiveSessions(): \Illuminate\Database\Eloquent\Collection
    {
        return VdiSession::with(['vm', 'user'])
            ->where('status', 'active')
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
