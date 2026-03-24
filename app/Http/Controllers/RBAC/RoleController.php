<?php

namespace App\Http\Controllers\RBAC;

use App\Http\Controllers\Controller;
use App\Services\RBAC\RoleService;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RoleController extends Controller
{
    public function __construct(private RoleService $service) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Role::class);
        $filters = $request->only(['search']);
        $roles   = $this->service->list($filters);
        return view('rbac.roles.index', compact('roles'));
    }

    public function create(): View
    {
        $this->authorize('create', Role::class);
        $permissions = Permission::orderBy('name')->get()
            ->groupBy(fn($p) => explode('.', $p->name)[0] ?? 'general');
        return view('rbac.roles.create', compact('permissions'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Role::class);

        $data = $request->validate([
            'name'          => 'required|string|max:50|unique:roles,name',
            'display_name'  => 'nullable|string|max:100',
            'description'   => 'nullable|string|max:255',
            'permissions'   => 'nullable|array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        $this->service->create($data);
        return redirect()->route('admin.roles.index')->with('success', 'Role created successfully.');
    }

    public function edit(Role $role): View
    {
        $this->authorize('update', $role);
        $role->load('permissions');
        $permissions = Permission::orderBy('name')->get()
            ->groupBy(fn($p) => explode('.', $p->name)[0] ?? 'general');
        return view('rbac.roles.edit', compact('role', 'permissions'));
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $this->authorize('update', $role);

        $data = $request->validate([
            'name'          => 'required|string|max:50|unique:roles,name,' . $role->id,
            'display_name'  => 'nullable|string|max:100',
            'description'   => 'nullable|string|max:255',
            'permissions'   => 'nullable|array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        $this->service->update($role, $data);
        return redirect()->route('admin.roles.index')->with('success', 'Role updated successfully.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        $this->authorize('delete', $role);

        try {
            $this->service->delete($role);
            return redirect()->route('admin.roles.index')->with('success', 'Role deleted.');
        } catch (\RuntimeException $e) {
            return redirect()->route('admin.roles.index')->with('error', $e->getMessage());
        }
    }
}
