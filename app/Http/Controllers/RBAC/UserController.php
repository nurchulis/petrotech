<?php

namespace App\Http\Controllers\RBAC;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\RBAC\UserService;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(private UserService $service) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', User::class);

        $request->validate([
            'role'   => 'nullable|string|exists:roles,name',
            'status' => 'nullable|in:1,0',
            'search' => 'nullable|string|max:100',
        ]);

        $filters = $request->only(['search', 'role', 'status']);
        $users   = $this->service->list($filters);
        $roles   = Role::orderBy('name')->get();
        return view('rbac.users.index', compact('users', 'roles'));
    }

    public function create(): View
    {
        $this->authorize('create', User::class);
        $roles = Role::orderBy('name')->get();
        $vendors = \App\Models\Vendor::withCount('licenses')->with('server')->orderBy('name')->get();
        $existingCompanies = \App\Models\Vendor::whereNotNull('company')->distinct()->pluck('company');
        return view('rbac.users.create', compact('roles', 'vendors', 'existingCompanies'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', User::class);

        $data = $request->validate([
            'name'         => 'required|string|max:255',
            'email'        => 'required|email|unique:users,email',
            'password'     => 'required|string|min:8|confirmed',
            'employee_id'  => 'nullable|string|max:50|unique:users,employee_id',
            'department'   => 'nullable|string|max:100',
            'company'      => 'nullable|string|max:255',
            'phone'        => 'nullable|string|max:20',
            'is_active'    => 'boolean',
            'roles'        => 'required|array|min:1',
            'roles.*'      => 'string|exists:roles,name',
            'vendor_ids'   => 'nullable|array',
            'vendor_ids.*' => 'integer|exists:vendors,id',
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $this->service->create($data);

        return redirect()->route('admin.users.index')->with('success', 'User created successfully.');
    }

    public function show(User $user): View
    {
        $this->authorize('view', $user);
        $user->load(['roles', 'accessibleVendors.server', 'accessibleVendors.licenses']);
        $allVendors = \App\Models\Vendor::withCount('licenses')->with('server')->orderBy('name')->get();
        return view('rbac.users.show', compact('user', 'allVendors'));
    }

    public function edit(User $user): View
    {
        $this->authorize('update', $user);

        if ($user->hasRole(['admin', 'super_admin'])) {
            abort(403, 'Administrator and Super Administrator accounts cannot be edited.');
        }

        $roles = Role::where('name', 'user')->get();
        $vendors = \App\Models\Vendor::withCount('licenses')->with('server')->orderBy('name')->get();
        $existingCompanies = \App\Models\Vendor::whereNotNull('company')->distinct()->pluck('company');
        $user->load('accessibleVendors');
        return view('rbac.users.edit', compact('user', 'roles', 'vendors', 'existingCompanies'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        if ($user->hasRole(['admin', 'super_admin'])) {
            abort(403, 'Administrator and Super Administrator accounts cannot be edited.');
        }

        $data = $request->validate([
            'name'         => 'required|string|max:255',
            'email'        => 'required|email|unique:users,email,' . $user->id,
            'password'     => 'nullable|string|min:8|confirmed',
            'employee_id'  => 'nullable|string|max:50|unique:users,employee_id,' . $user->id,
            'department'   => 'nullable|string|max:100',
            'company'      => 'nullable|string|max:255',
            'phone'        => 'nullable|string|max:20',
            'is_active'    => 'boolean',
            'roles'        => 'nullable|array',
            'roles.*'      => 'string|in:user',
            'vendor_ids'   => 'nullable|array',
            'vendor_ids.*' => 'integer|exists:vendors,id',
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $data['roles'] = ['user']; // Guarantee that role cannot be changed to admin or super_admin
        $this->service->update($user, $data);

        return redirect()->route('admin.users.index')->with('success', "User '{$user->name}' updated successfully.");
    }

    public function licenseAccess(User $user): View
    {
        $this->authorize('view', $user);
        $user->load(['accessibleVendors.server', 'accessibleVendors.licenses']);
        $allVendors = \App\Models\Vendor::withCount('licenses')->with('server')->orderBy('name')->get();
        return view('rbac.users.license_access', compact('user', 'allVendors'));
    }

    public function syncLicenseAccess(Request $request, User $user): RedirectResponse
    {
        $this->authorize('view', $user);

        $data = $request->validate([
            'vendor_ids'   => 'nullable|array',
            'vendor_ids.*' => 'integer|exists:vendors,id',
        ]);

        $user->accessibleVendors()->syncWithPivotValues($data['vendor_ids'] ?? [], [
            'granted_by' => auth()->id(),
        ]);

        return back()->with('success', "License access for {$user->name} has been updated successfully.");
    }

    // ============================================================
    // [DAST-SCAN] destroy() method temporarily disabled.
    // Re-enable after DAST scanning is complete.
    // ============================================================
    // public function destroy(User $user): RedirectResponse
    // {
    //     $this->authorize('delete', $user);
    //     $this->service->delete($user);
    //     return redirect()->route('admin.users.index')->with('success', 'User deleted.');
    // }
}
