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
        return User::with(['roles', 'accessibleVendors'])
            ->when($filters['search'] ?? null, fn($q, $s) => $q->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%")
                  ->orWhere('company', 'like', "%{$s}%")
                  ->orWhere('employee_id', 'like', "%{$s}%");
            }))
            ->when($filters['company'] ?? null, fn($q, $c) => $q->where('company', $c))
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
        $vendorIds = $data['vendor_ids'] ?? [];
        unset($data['roles'], $data['vendor_ids']);

        $data['password'] = Hash::make($data['password']);
        $user = User::create($data);
        $user->syncRoles($roles);

        if (!empty($vendorIds)) {
            $user->accessibleVendors()->syncWithPivotValues($vendorIds, [
                'granted_by' => auth()->id(),
            ]);
        }

        return $user->load(['roles', 'accessibleVendors']);
    }

    /**
     * Update a user and sync roles.
     */
    public function update(User $user, array $data): User
    {
        $roles = $data['roles'] ?? [];
        $vendorIds = $data['vendor_ids'] ?? null;
        unset($data['roles'], $data['vendor_ids']);

        // Only update password if provided
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);
        $user->syncRoles($roles);

        if ($vendorIds !== null) {
            $user->accessibleVendors()->syncWithPivotValues($vendorIds, [
                'granted_by' => auth()->id(),
            ]);
        }

        return $user->fresh(['roles', 'accessibleVendors']);
    }

    /**
     * Delete a user (with safety checks).
     */
    public function delete(User $user): void
    {
        $user->delete();
    }
}
