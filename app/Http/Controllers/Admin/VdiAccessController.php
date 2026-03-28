<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Vm;
use App\Services\VDI\VdiAccessService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class VdiAccessController extends Controller
{
    public function __construct(private VdiAccessService $service) {}

    /**
     * Show a user's VM access (direct + group-based).
     */
    public function userAccess(User $user): View
    {
        $user->load(['directVmAccess', 'groups.vms']);

        $directVms = $user->directVmAccess;
        $groupVms  = $user->groups->flatMap->vms->unique('id');
        $allVms    = Vm::orderBy('vm_name')->get();

        return view('vdi-access.user', compact('user', 'directVms', 'groupVms', 'allVms'));
    }

    /**
     * Sync direct VM access for a user.
     */
    public function syncUserAccess(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'vm_ids'   => 'nullable|array',
            'vm_ids.*' => 'exists:vms,id',
        ]);

        $this->service->syncUserVmAccess($user, $data['vm_ids'] ?? []);
        return redirect()->route('admin.vdi-access.user', $user)->with('success', 'VM access updated.');
    }
}
