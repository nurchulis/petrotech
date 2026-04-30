<?php

namespace App\Policies;

use App\Models\User;
use Spatie\Permission\Models\Role;

class RolePolicy
{
    /**
     * Only super_admin can view roles.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('super_admin');
    }

    /**
     * Only super_admin can view a role.
     */
    public function view(User $user, Role $role): bool
    {
        return $user->hasRole('super_admin');
    }

    /**
     * Only super_admin can create roles.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('super_admin');
    }

    /**
     * Only super_admin can update roles.
     */
    public function update(User $user, Role $role): bool
    {
        return $user->hasRole('super_admin');
    }

    /**
     * Only super_admin can delete roles.
     */
    public function delete(User $user, Role $role): bool
    {
        return $user->hasRole('super_admin');
    }
}
