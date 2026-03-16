<?php

namespace App\Services\VmMonitoring;

use App\Models\Vm;
use App\Models\VmMetric;
use Illuminate\Support\Collection;

class VmMetricService
{
    public function ingest(array $data): VmMetric
    {
        return VmMetric::create(array_merge($data, [
            'recorded_at' => $data['recorded_at'] ?? now(),
        ]));
    }

    public function bulkIngest(array $records): void
    {
        $now = now();
        $rows = array_map(fn($r) => array_merge($r, ['recorded_at' => $r['recorded_at'] ?? $now]), $records);
        VmMetric::insert($rows);
    }

    public function latestAll(): Collection
    {
        return Vm::with(['metrics' => fn($q) => $q->latest('recorded_at')->limit(1)])
            ->get()
            ->map(function (Vm $vm) {
                $m = $vm->metrics->first();
                return [
                    'vm'                 => $vm,
                    'cpu_utilisation'    => $m?->cpu_utilisation ?? 0,
                    'memory_utilisation' => $m?->memory_utilisation ?? 0,
                    'gpu_utilisation'    => $m?->gpu_utilisation ?? 0,
                    'recorded_at'        => $m?->recorded_at,
                ];
            });
    }

    public function trendData(Vm $vm, int $hours = 24): array
    {
        $metrics = VmMetric::where('vm_id', $vm->id)
            ->lastHours($hours)
            ->orderBy('recorded_at')
            ->get(['recorded_at', 'cpu_utilisation', 'memory_utilisation',
                   'disk_io_read_mb', 'network_in_mb', 'gpu_utilisation']);

        return [
            'labels'  => $metrics->pluck('recorded_at')->map(fn($d) => $d->format('H:i')),
            'cpu'     => $metrics->pluck('cpu_utilisation'),
            'memory'  => $metrics->pluck('memory_utilisation'),
            'disk'    => $metrics->pluck('disk_io_read_mb'),
            'network' => $metrics->pluck('network_in_mb'),
            'gpu'     => $metrics->pluck('gpu_utilisation'),
        ];
    }
}
