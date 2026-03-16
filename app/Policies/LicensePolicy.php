<?php

namespace App\Policies;

use App\Models\User;
use App\Models\License;

class LicensePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['admin', 'super_admin']);
    }

    public function view(User $user, License $license): bool
    {
        return $user->hasRole(['admin', 'super_admin']);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['admin', 'super_admin']);
    }

    public function update(User $user, License $license): bool
    {
        return $user->hasRole(['admin', 'super_admin']);
    }

    public function delete(User $user, License $license): bool
    {
        return $user->hasRole('super_admin');
    }
}
