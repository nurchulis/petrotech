<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Vm;
use App\Policies\VmManagementPolicy;
use App\Services\VmMonitoring\VmManagementService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Gate;

class VmManagementController extends Controller
{
    public function __construct(private VmManagementService $service) {}

    /**
     * Authorize against VmManagementPolicy explicitly.
     */
    private function authorizeVm(string $ability, mixed $model = null): void
    {
        $policy = app(VmManagementPolicy::class);
        $user   = auth()->user();

        $result = $model
            ? $policy->{$ability}($user, $model)
            : $policy->{$ability}($user);

        if (!$result) {
            abort(403);
        }
    }

    public function index(Request $request): View
    {
        $this->authorizeVm('viewAny');
        $filters = $request->only(['search', 'status', 'region']);
        $vms     = $this->service->list($filters);
        $regions = Vm::whereNotNull('region')->distinct()->pluck('region')->sort();
        return view('vm-management.index', compact('vms', 'regions'));
    }

    public function create(): View
    {
        $this->authorizeVm('create');
        $users = User::where('is_active', true)->orderBy('name')->get();
        return view('vm-management.create', compact('users'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeVm('create');

        $data = $request->validate([
            'vm_name'          => 'required|string|max:255|unique:vms,vm_name',
            'os_type'          => 'required|string|max:100',
            'application_name' => 'required|string|max:255',
            'status'           => 'required|in:running,stopped,paused',
            'region'           => 'nullable|string|max:100',
            'data_center'      => 'nullable|string|max:100',
            'ip_address'       => 'nullable|string|max:50',
            'host_server'      => 'nullable|string|max:255',
            'has_gpu'          => 'boolean',
            'gpu_model'        => 'nullable|string|max:255',
            'cpu_cores'        => 'nullable|integer|min:1',
            'ram_gb'           => 'nullable|integer|min:1',
            'assigned_user_id' => 'nullable|exists:users,id',
            'notes'            => 'nullable|string',
        ]);

        $data['has_gpu'] = $request->boolean('has_gpu');
        $this->service->create($data);

        return redirect()->route('admin.vm-management.index')->with('success', 'VM created successfully.');
    }

    public function show(Vm $vm): View
    {
        $this->authorizeVm('view', $vm);
        $vm->load('assignedUser');
        return view('vm-management.show', compact('vm'));
    }

    public function edit(Vm $vm): View
    {
        $this->authorizeVm('update', $vm);
        $users = User::where('is_active', true)->orderBy('name')->get();
        return view('vm-management.edit', compact('vm', 'users'));
    }

    public function update(Request $request, Vm $vm): RedirectResponse
    {
        $this->authorizeVm('update', $vm);

        $data = $request->validate([
            'vm_name'          => 'required|string|max:255|unique:vms,vm_name,' . $vm->id,
            'os_type'          => 'required|string|max:100',
            'application_name' => 'required|string|max:255',
            'status'           => 'required|in:running,stopped,paused',
            'region'           => 'nullable|string|max:100',
            'data_center'      => 'nullable|string|max:100',
            'ip_address'       => 'nullable|string|max:50',
            'host_server'      => 'nullable|string|max:255',
            'has_gpu'          => 'boolean',
            'gpu_model'        => 'nullable|string|max:255',
            'cpu_cores'        => 'nullable|integer|min:1',
            'ram_gb'           => 'nullable|integer|min:1',
            'assigned_user_id' => 'nullable|exists:users,id',
            'notes'            => 'nullable|string',
        ]);

        $data['has_gpu'] = $request->boolean('has_gpu');
        $this->service->update($vm, $data);

        return redirect()->route('admin.vm-management.index')->with('success', 'VM updated successfully.');
    }

    public function destroy(Vm $vm): RedirectResponse
    {
        $this->authorizeVm('delete', $vm);
        $this->service->delete($vm);
        return redirect()->route('admin.vm-management.index')->with('success', 'VM deleted.');
    }
}
