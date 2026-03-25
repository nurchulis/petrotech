<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\User;
use App\Models\Vm;
use App\Services\VDI\VdiAccessService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class GroupController extends Controller
{
    public function __construct(private VdiAccessService $accessService) {}

    public function index(): View
    {
        $this->authorize('viewAny', Group::class);
        $groups = Group::withCount(['users', 'vms'])->orderBy('name')->paginate(15);
        return view('groups.index', compact('groups'));
    }

    public function create(): View
    {
        $this->authorize('create', Group::class);
        return view('groups.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Group::class);

        $data = $request->validate([
            'name'        => 'required|string|max:100|unique:groups,name',
            'description' => 'nullable|string|max:255',
        ]);

        Group::create($data);
        return redirect()->route('admin.groups.index')->with('success', 'Group created successfully.');
    }

    public function show(Group $group): View
    {
        $this->authorize('view', $group);
        $group->load(['users', 'vms']);
        $allUsers = User::where('is_active', true)->orderBy('name')->get();
        $allVms   = Vm::orderBy('vm_name')->get();
        return view('groups.show', compact('group', 'allUsers', 'allVms'));
    }

    public function edit(Group $group): View
    {
        $this->authorize('update', $group);
        return view('groups.edit', compact('group'));
    }

    public function update(Request $request, Group $group): RedirectResponse
    {
        $this->authorize('update', $group);

        $data = $request->validate([
            'name'        => 'required|string|max:100|unique:groups,name,' . $group->id,
            'description' => 'nullable|string|max:255',
        ]);

        $group->update($data);
        return redirect()->route('admin.groups.show', $group)->with('success', 'Group updated.');
    }

    public function destroy(Group $group): RedirectResponse
    {
        $this->authorize('delete', $group);
        $group->delete();
        return redirect()->route('admin.groups.index')->with('success', 'Group deleted.');
    }

    /**
     * Sync group members.
     */
    public function syncMembers(Request $request, Group $group): RedirectResponse
    {
        $this->authorize('update', $group);

        $data = $request->validate([
            'user_ids'   => 'nullable|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        $this->accessService->syncGroupMembers($group, $data['user_ids'] ?? []);
        return redirect()->route('admin.groups.show', $group)->with('success', 'Members updated.');
    }

    /**
     * Sync group VM access.
     */
    public function syncVmAccess(Request $request, Group $group): RedirectResponse
    {
        $this->authorize('update', $group);

        $data = $request->validate([
            'vm_ids'   => 'nullable|array',
            'vm_ids.*' => 'exists:vms,id',
        ]);

        $this->accessService->syncGroupVmAccess($group, $data['vm_ids'] ?? []);
        return redirect()->route('admin.groups.show', $group)->with('success', 'VM access updated.');
    }
}
