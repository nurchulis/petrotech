<?php

namespace App\Services\License;

use App\Models\License;
use App\Models\LicenseLog;
use App\Models\LicenseUserAccess;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class LicenseService
{
    public function list(array $filters = []): LengthAwarePaginator
    {
        return License::with(['server', 'creator'])
            ->when($filters['status'] ?? null, fn($q, $s) => $q->where('status', $s))
            ->when($filters['search'] ?? null, fn($q, $s) => $q->where('license_name', 'ilike', "%{$s}%")
                ->orWhere('application_name', 'ilike', "%{$s}%")
                ->orWhere('vendor', 'ilike', "%{$s}%"))
            ->orderBy('expiry_date')
            ->paginate(15);
    }

    public function listVendors(array $filters = []): LengthAwarePaginator
    {
        // Group by server and vendor to show "Vendors" on the main page
        // We use a raw select to count features per vendor

        return License::select('vendor', 'license_server_id', \DB::raw('count(*) as features_count'))
            ->with('server')
            ->whereNotNull('vendor')
            ->groupBy('vendor', 'license_server_id')
            ->when($filters['search'] ?? null, function ($q, $s) {
                $q->where('vendor', 'ilike', "%{$s}%");
            })
            ->paginate(15);
    }

    public function getVendorDetails(int $serverId, string $vendorName): array
    {
        $features = License::where('license_server_id', $serverId)
            ->where('vendor', $vendorName)
            ->get();

        $features->each(function ($f) {
            $recentCheckouts = LicenseLog::where('license_id', $f->id)
                ->where('event_type', 'checkout')
                ->orderBy('recorded_at', 'desc')
                ->get()
                ->map(function ($log) {
                    preg_match("/'([^']+)'/", $log->event_detail, $matches);
                    return (object)[
                        'username' => $matches[1] ?? 'Unknown',
                        'recorded_at' => $log->recorded_at,
                        'ip_address' => $log->ip_address,
                    ];
                })
                ->unique('username')
                ->take($f->used_seats)
                ->values();

            $f->active_users_list = $recentCheckouts->pluck('username')->implode(', ');
            $f->active_checkouts = $recentCheckouts; // Now contains objects with timestamps
        });

        $server = \App\Models\LicenseServer::find($serverId);

        // Authorized users (usernames) for these features
        $accessRecords = LicenseUserAccess::whereIn('license_id', $features->pluck('id'))
            ->with('license')
            ->get();
        
        $authorizedUsers = $accessRecords->groupBy(function($item) {
            return (string) $item->username;
        })->map(function($records, $username) {
            return (object) [
                'name' => (string) $username,
                'username' => (string) $username,
                'accessibleLicenses' => $records->map(fn($r) => $r->license),
            ];
        })->values();

        // Usage logs for these features
        $logs = LicenseLog::whereIn('license_id', $features->pluck('id'))
            ->with('license')
            ->orderBy('recorded_at', 'desc')
            ->limit(20)
            ->get()
            ->map(function ($log) {
                preg_match("/'([^']+)'/", $log->event_detail, $matches);
                $log->username = $matches[1] ?? 'System';
                $log->timestamp = $log->recorded_at;
                $log->license_name = $log->license?->license_name;
                return $log;
            });

        return [
            'vendor' => $vendorName,
            'server' => $server,
            'features' => $features,
            'authorizedUsers' => $authorizedUsers,
            'logs' => $logs,
        ];
    }

    public function syncAccess(string $username, array $licenseIds, array $scopeLicenseIds, User $grantor): void
    {
        // 1. Remove access for any licenses in the scope that are NOT in the new list
        LicenseUserAccess::where('username', $username)
            ->whereIn('license_id', $scopeLicenseIds)
            ->whereNotIn('license_id', $licenseIds)
            ->delete();

        // 2. Grant/Update access for the new list
        foreach ($licenseIds as $id) {
            LicenseUserAccess::updateOrCreate(
                ['username' => $username, 'license_id' => $id],
                ['granted_by' => $grantor->id]
            );
        }
    }

    public function revokeAccess(string $username, int $licenseId): void
    {
        LicenseUserAccess::where('username', $username)
            ->where('license_id', $licenseId)
            ->delete();
    }

    public function revokeAllAccess(string $username, array $scopeLicenseIds): void
    {
        LicenseUserAccess::where('username', $username)
            ->whereIn('license_id', $scopeLicenseIds)
            ->delete();
    }

    public function create(array $data, User $user): License
    {
        $data['created_by'] = $user->id;
        $license = License::create($data);

        LicenseLog::create([
            'license_id' => $license->id,
            'event_type' => 'created',
            'event_detail' => 'License created by ' . $user->name,
            'recorded_at' => now(),
        ]);

        return $license;
    }

    public function update(License $license, array $data): License
    {
        $license->update($data);

        LicenseLog::create([
            'license_id' => $license->id,
            'event_type' => 'updated',
            'event_detail' => 'License updated',
            'recorded_at' => now(),
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
