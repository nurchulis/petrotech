<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Vm;

class VmPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['admin', 'super_admin']);
    }

    public function view(User $user, Vm $vm): bool
    {
        return $user->hasRole(['admin', 'super_admin']);
    }

    public function create(User $user): bool
    {
        return $user->hasRole('super_admin');
    }

    public function update(User $user, Vm $vm): bool
    {
        return $user->hasRole(['admin', 'super_admin']);
    }

    public function delete(User $user, Vm $vm): bool
    {
        return $user->hasRole('super_admin');
    }
}
