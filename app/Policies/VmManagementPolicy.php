<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Vm;

class VmManagementPolicy
{
    /**
     * Admin and super_admin can view VM list.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['admin', 'super_admin']);
    }

    /**
     * Admin and super_admin can view a VM.
     */
    public function view(User $user, Vm $vm): bool
    {
        return $user->hasRole(['admin', 'super_admin']);
    }

    /**
     * Admin and super_admin can create VMs.
     */
    public function create(User $user): bool
    {
        return $user->hasRole(['admin', 'super_admin']);
    }

    /**
     * Admin and super_admin can update VMs.
     */
    public function update(User $user, Vm $vm): bool
    {
        return $user->hasRole(['admin', 'super_admin']);
    }

    /**
     * Admin and super_admin can delete VMs.
     */
    public function delete(User $user, Vm $vm): bool
    {
        return $user->hasRole(['admin', 'super_admin']);
    }
}
