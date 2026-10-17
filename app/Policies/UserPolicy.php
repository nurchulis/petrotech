<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Admin and super_admin can view user list.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['admin', 'super_admin']);
    }

    /**
     * Admin and super_admin can view a user.
     */
    public function view(User $user, User $model): bool
    {
        return $user->hasRole(['admin', 'super_admin']);
    }

    /**
     * Admin and super_admin can create users.
     */
    public function create(User $user): bool
    {
        return $user->hasRole(['admin', 'super_admin']);
    }

    /**
     * Admin and super_admin can update users, but CANNOT update admin or super_admin accounts.
     * Only users with role 'user' can be edited.
     */
    public function update(User $user, User $model): bool
    {
        if (!$user->hasRole(['admin', 'super_admin'])) {
            return false;
        }

        // Prevent editing admin and super_admin accounts
        return !$model->hasRole(['admin', 'super_admin']);
    }

    /**
     * Only super_admin can delete users, and cannot delete themselves.
     */
    public function delete(User $user, User $model): bool
    {
        return $user->hasRole('super_admin') && $user->id !== $model->id;
    }
}
