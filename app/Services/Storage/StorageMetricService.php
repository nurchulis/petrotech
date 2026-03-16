<?php

namespace App\Services\Storage;

use App\Models\StorageDevice;
use App\Models\StorageMetric;

class StorageMetricService
{
    public function summaryTotals(): array
    {
        $devices = StorageDevice::where('status', 'active')->get();
        $totalGb = $devices->sum('total_space_gb');
        $totalUsed = 0;
        $totalFree = 0;

        foreach ($devices as $device) {
            $latest = $device->latestMetric();
            if ($latest) {
                $totalUsed += $latest->used_space_gb;
                $totalFree += $latest->free_space_gb;
            }
        }

        return [
            'total_gb'   => round($totalGb, 2),
            'used_gb'    => round($totalUsed, 2),
            'free_gb'    => round($totalFree, 2),
            'usage_pct'  => $totalGb > 0 ? round(($totalUsed / $totalGb) * 100, 1) : 0,
        ];
    }

    public function ingest(array $data): StorageMetric
    {
        return StorageMetric::create(array_merge($data, [
            'recorded_at' => $data['recorded_at'] ?? now(),
        ]));
    }

    public function trendData(StorageDevice $device, int $days = 30): array
    {
        $metrics = StorageMetric::where('storage_device_id', $device->id)
            ->lastDays($days)
            ->orderBy('recorded_at')
            ->get(['recorded_at', 'used_space_gb', 'free_space_gb', 'usage_percentage']);

        return [
            'labels'     => $metrics->pluck('recorded_at')->map(fn($d) => $d->format('M d'))->values()->toArray(),
            'used_gb'    => $metrics->pluck('used_space_gb')->values()->toArray(),
            'usage_pct'  => $metrics->pluck('usage_percentage')->values()->toArray(),
        ];
    }
}
