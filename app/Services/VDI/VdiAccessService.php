<?php

namespace App\Services\VDI;

use App\Models\Group;
use App\Models\User;
use App\Models\Vm;
use Illuminate\Support\Collection;

class VdiAccessService
{
    /**
     * Get all VMs accessible to a user (direct + group-based).
     * Only returns active (running/stopped/paused) VMs.
     * Excludes expired direct access.
     */
    public function getAccessibleVms(User $user): Collection
    {
        // 1. Direct user VM access (non-expired)
        $directVmIds = $user->directVmAccess()
            ->where(function ($q) {
                $q->whereNull('user_vm_access.expires_at')
                  ->orWhere('user_vm_access.expires_at', '>', now());
            })
            ->pluck('vms.id');

        // 2. Group-based VM access
        $groupVmIds = Vm::whereHas('accessGroups', function ($q) use ($user) {
            $q->whereHas('users', fn($q2) => $q2->where('users.id', $user->id));
        })->pluck('id');

        // 3. Merge, dedupe, filter active
        $allVmIds = $directVmIds->merge($groupVmIds)->unique();

        return Vm::with('assignedUser')
            ->whereIn('id', $allVmIds)
            ->orderBy('vm_name')
            ->get();
    }

    /**
     * Assign VMs directly to a user.
     */
    public function assignVmsToUser(User $user, array $vmIds, ?string $expiresAt = null): void
    {
        $pivotData = [];
        foreach ($vmIds as $vmId) {
            $pivotData[$vmId] = ['expires_at' => $expiresAt];
        }
        $user->directVmAccess()->syncWithoutDetaching($pivotData);
    }

    /**
     * Sync direct VM access for a user (replaces all existing).
     */
    public function syncUserVmAccess(User $user, array $vmIds): void
    {
        $user->directVmAccess()->sync($vmIds);
    }

    /**
     * Revoke direct VM access from a user.
     */
    public function revokeVmsFromUser(User $user, array $vmIds): void
    {
        $user->directVmAccess()->detach($vmIds);
    }

    /**
     * Sync VM access for a group.
     */
    public function syncGroupVmAccess(Group $group, array $vmIds): void
    {
        $group->vms()->sync($vmIds);
    }

    /**
     * Sync members of a group.
     */
    public function syncGroupMembers(Group $group, array $userIds): void
    {
        $group->users()->sync($userIds);
    }
}
