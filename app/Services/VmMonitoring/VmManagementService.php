<?php

namespace App\Services\VmMonitoring;

use App\Models\Vm;
use Illuminate\Pagination\LengthAwarePaginator;

class VmManagementService
{
    /**
     * List VMs with optional search, status, and region filters.
     */
    public function list(array $filters = []): LengthAwarePaginator
    {
        return Vm::with('assignedUser')
            ->when($filters['search'] ?? null, fn($q, $s) => $q->where(function ($q) use ($s) {
                $q->where('vm_name', 'ilike', "%{$s}%")
                  ->orWhere('application_name', 'ilike', "%{$s}%")
                  ->orWhere('ip_address', 'ilike', "%{$s}%");
            }))
            ->when($filters['status'] ?? null, fn($q, $s) => $q->where('status', $s))
            ->when($filters['region'] ?? null, fn($q, $r) => $q->where('region', $r))
            ->orderBy('vm_name')
            ->paginate(15);
    }

    /**
     * Create a new VM.
     */
    public function create(array $data): Vm
    {
        return Vm::create($data);
    }

    /**
     * Update a VM.
     */
    public function update(Vm $vm, array $data): Vm
    {
        $vm->update($data);
        return $vm->fresh();
    }

    /**
     * Delete a VM and its associated metrics.
     */
    public function delete(Vm $vm): void
    {
        $vm->metrics()->delete();
        $vm->delete();
    }
}
