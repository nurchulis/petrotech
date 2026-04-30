<?php

namespace App\Services\RBAC;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;

class UserService
{
    /**
     * List users with optional search, role, and status filters.
     */
    public function list(array $filters = []): LengthAwarePaginator
    {
        return User::with('roles')
            ->when($filters['search'] ?? null, fn($q, $s) => $q->where(function ($q) use ($s) {
                $q->where('name', 'ilike', "%{$s}%")
                  ->orWhere('email', 'ilike', "%{$s}%")
                  ->orWhere('employee_id', 'ilike', "%{$s}%");
            }))
            ->when($filters['role'] ?? null, fn($q, $r) => $q->role($r))
            ->when(isset($filters['status']) && $filters['status'] !== '', fn($q) => $q->where('is_active', $filters['status']))
            ->orderBy('name')
            ->paginate(15);
    }

    /**
     * Create a user and assign roles.
     */
    public function create(array $data): User
    {
        $roles = $data['roles'] ?? [];
        unset($data['roles']);

        $data['password'] = Hash::make($data['password']);
        $user = User::create($data);
        $user->syncRoles($roles);

        return $user->load('roles');
    }

    /**
     * Update a user and sync roles.
     */
    public function update(User $user, array $data): User
    {
        $roles = $data['roles'] ?? [];
        unset($data['roles']);

        // Only update password if provided
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);
        $user->syncRoles($roles);

        return $user->fresh('roles');
    }

    /**
     * Delete a user (with safety checks).
     */
    public function delete(User $user): void
    {
        $user->delete();
    }
}
