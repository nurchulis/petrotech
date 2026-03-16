<?php

namespace App\Services\License;

use App\Models\License;
use App\Models\LicenseLog;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class LicenseService
{
    public function list(array $filters = []): LengthAwarePaginator
    {
        return License::with(['server', 'creator'])
            ->when($filters['status'] ?? null, fn($q, $s) => $q->where('status', $s))
            ->when($filters['search'] ?? null, fn($q, $s) => $q->where('license_name', 'ilike', "%{$s}%")
                ->orWhere('application_name', 'ilike', "%{$s}%"))
            ->orderBy('expiry_date')
            ->paginate(15);
    }

    public function create(array $data, User $user): License
    {
        $data['created_by'] = $user->id;
        $license = License::create($data);

        LicenseLog::create([
            'license_id'   => $license->id,
            'event_type'   => 'created',
            'event_detail' => 'License created by ' . $user->name,
            'recorded_at'  => now(),
        ]);

        return $license;
    }

    public function update(License $license, array $data): License
    {
        $license->update($data);

        LicenseLog::create([
            'license_id'   => $license->id,
            'event_type'   => 'updated',
            'event_detail' => 'License updated',
            'recorded_at'  => now(),
        ]);

        return $license->fresh();
    }

    public function delete(License $license): void
    {
        $license->delete();
    }

    public function toggleStatus(License $license): License
    {
        $license->update(['status' => $license->status === 'enable' ? 'disable' : 'enable']);
        return $license->fresh();
    }

    public function expiringWithinDays(int $days = 30): \Illuminate\Database\Eloquent\Collection
    {
        return License::expiringSoon($days)->active()->with('server')->get();
    }
}
