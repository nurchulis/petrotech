<?php

namespace App\Services\Analytics;

use App\Models\License;
use App\Models\Vm;
use App\Models\VmMetric;
use App\Models\StorageDevice;
use App\Models\Ticket;
use App\Models\VdiSession;
use Illuminate\Support\Facades\DB;

class ReportService
{
    public function dashboardWidgets(): array
    {
        return [
            'running_vms'      => Vm::running()->count(),
            'total_licenses'   => License::active()->count(),
            'active_sessions'  => VdiSession::where('status', 'active')->count(),
            'open_tickets'     => Ticket::open()->count(),
            'expiring_licenses'=> License::expiringSoon(30)->count(),
        ];
    }

    public function ticketStatistics(string $period = 'month'): array
    {
        $from = match ($period) {
            'week'  => now()->subWeek(),
            'year'  => now()->subYear(),
            default => now()->subMonth(),
        };

        $byStatus = Ticket::select('status', DB::raw('count(*) as total'))
            ->where('created_at', '>=', $from)
            ->groupBy('status')
            ->pluck('total', 'status');

        $byPriority = Ticket::select('priority', DB::raw('count(*) as total'))
            ->where('created_at', '>=', $from)
            ->groupBy('priority')
            ->pluck('total', 'priority');

        return [
            'by_status'   => $byStatus,
            'by_priority' => $byPriority,
            'total'       => Ticket::where('created_at', '>=', $from)->count(),
        ];
    }

    public function vmUtilisationSummary(): array
    {
        $recent = VmMetric::where('recorded_at', '>=', now()->subHour())
            ->select(
                DB::raw('avg(cpu_utilisation) as avg_cpu'),
                DB::raw('avg(memory_utilisation) as avg_mem'),
                DB::raw('avg(gpu_utilisation) as avg_gpu')
            )->first();

        return [
            'avg_cpu'    => round($recent?->avg_cpu ?? 0, 1),
            'avg_memory' => round($recent?->avg_mem ?? 0, 1),
            'avg_gpu'    => round($recent?->avg_gpu ?? 0, 1),
        ];
    }

    public function licenseUsage(): array
    {
        return [
            'total'   => License::count(),
            'active'  => License::active()->count(),
            'expired' => License::expired()->count(),
            'expiring'=> License::expiringSoon(30)->count(),
        ];
    }
}
