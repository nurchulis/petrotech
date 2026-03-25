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
        $filters = $request->only(['search', 'role', 'status']);
        $users   = $this->service->list($filters);
        $roles   = Role::orderBy('name')->get();
        return view('rbac.users.index', compact('users', 'roles'));
    }

    public function create(): View
    {
        $this->authorize('create', User::class);
        $roles = Role::orderBy('name')->get();
        return view('rbac.users.create', compact('roles'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', User::class);

        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'email'       => 'required|email|unique:users,email',
            'password'    => 'required|string|min:8|confirmed',
            'employee_id' => 'nullable|string|max:50|unique:users,employee_id',
            'department'  => 'nullable|string|max:100',
            'phone'       => 'nullable|string|max:20',
            'is_active'   => 'boolean',
            'roles'       => 'required|array|min:1',
            'roles.*'     => 'string|exists:roles,name',
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $this->service->create($data);

        return redirect()->route('admin.users.index')->with('success', 'User created successfully.');
    }

    public function show(User $user): View
    {
        $this->authorize('view', $user);
        $user->load('roles');
        return view('rbac.users.show', compact('user'));
    }

    public function edit(User $user): View
    {
        $this->authorize('update', $user);
        $roles = Role::orderBy('name')->get();
        return view('rbac.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'email'       => 'required|email|unique:users,email,' . $user->id,
            'password'    => 'nullable|string|min:8|confirmed',
            'employee_id' => 'nullable|string|max:50|unique:users,employee_id,' . $user->id,
            'department'  => 'nullable|string|max:100',
            'phone'       => 'nullable|string|max:20',
            'is_active'   => 'boolean',
            'roles'       => 'required|array|min:1',
            'roles.*'     => 'string|exists:roles,name',
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $this->service->update($user, $data);

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->authorize('delete', $user);
        $this->service->delete($user);
        return redirect()->route('admin.users.index')->with('success', 'User deleted.');
    }
}
