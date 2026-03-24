<?php

namespace App\Services\RBAC;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class RoleService
{
    /**
     * List roles with optional search filter.
     */
    public function list(array $filters = []): LengthAwarePaginator
    {
        return Role::withCount(['users', 'permissions'])
            ->when($filters['search'] ?? null, fn($q, $s) => $q->where('name', 'ilike', "%{$s}%"))
            ->orderBy('name')
            ->paginate(15);
    }

    /**
     * Create a role and assign permissions.
     */
    public function create(array $data): Role
    {
        $permissions = $data['permissions'] ?? [];
        unset($data['permissions']);

        $data['guard_name'] = 'web';
        $role = Role::create($data);

        if (!empty($permissions)) {
            $role->syncPermissions($permissions);
        }

        return $role->load('permissions');
    }

    /**
     * Update a role and sync permissions.
     */
    public function update(Role $role, array $data): Role
    {
        $permissions = $data['permissions'] ?? [];
        unset($data['permissions']);

        $role->update($data);

        $role->syncPermissions($permissions);

        return $role->fresh('permissions');
    }

    /**
     * Delete a role (prevent deleting built-in roles).
     */
    public function delete(Role $role): void
    {
        $builtIn = ['user', 'admin', 'super_admin'];

        if (in_array($role->name, $builtIn)) {
            throw new \RuntimeException('Cannot delete built-in role.');
        }

        $role->delete();
    }

    /**
     * Get all permissions grouped by prefix (e.g., "users", "roles").
     */
    public function allPermissionsGrouped(): array
    {
        return Permission::orderBy('name')
            ->get()
            ->groupBy(fn($p) => explode('.', $p->name)[0] ?? 'general')
            ->toArray();
    }
}
