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

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Group::class);
        
        $query = Group::withCount(['users', 'vms'])->orderBy('name');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $groups = $query->paginate(15)->withQueryString();
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
        return redirect()->route('admin.groups.index')->with('success', 'Group updated.');
    }

    public function destroy(Group $group): RedirectResponse
    {
        $this->authorize('delete', $group);
        $group->delete();
        return redirect()->route('admin.groups.index')->with('success', 'Group deleted.');
    }

    // ── Members Management ───────────────────────────────────────────────────

    public function members(Group $group): View
    {
        $this->authorize('view', $group);
        $members = $group->users()->orderBy('name')->paginate(15);
        $availableUsers = User::where('is_active', true)
            ->whereNotIn('id', $group->users()->pluck('users.id'))
            ->orderBy('name')
            ->get();
        return view('groups.members', compact('group', 'members', 'availableUsers'));
    }

    public function addMember(Request $request, Group $group): RedirectResponse
    {
        $this->authorize('update', $group);
        $data = $request->validate([
            'user_ids'   => 'required|array|min:1',
            'user_ids.*' => 'exists:users,id'
        ]);
        $this->accessService->addMembersToGroup($group, $data['user_ids']);
        return back()->with('success', 'Users added to group.');
    }

    public function removeMember(Group $group, User $user): RedirectResponse
    {
        $this->authorize('update', $group);
        $this->accessService->removeMemberFromGroup($group, $user);
        return back()->with('success', 'User removed from group.');
    }

    // ── VM Access Management ─────────────────────────────────────────────────

    public function vms(Group $group): View
    {
        $this->authorize('view', $group);
        $vms = $group->vms()->orderBy('vm_name')->paginate(15);
        $availableVms = Vm::whereNotIn('id', $group->vms()->pluck('vms.id'))
            ->orderBy('vm_name')
            ->get();
        return view('groups.vms', compact('group', 'vms', 'availableVms'));
    }

    public function addVm(Request $request, Group $group): RedirectResponse
    {
        $this->authorize('update', $group);
        $data = $request->validate([
            'vm_ids'   => 'required|array|min:1',
            'vm_ids.*' => 'exists:vms,id'
        ]);
        $this->accessService->addVmsToGroup($group, $data['vm_ids']);
        return back()->with('success', 'VMs added to group.');
    }

    public function removeVm(Group $group, Vm $vm): RedirectResponse
    {
        $this->authorize('update', $group);
        $this->accessService->removeVmFromGroup($group, $vm);
        return back()->with('success', 'VM removed from group.');
    }
}
