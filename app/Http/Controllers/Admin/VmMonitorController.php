<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vm;
use App\Models\VmMetric;
use App\Services\VmMonitoring\VmMetricService;
use Illuminate\View\View;

class VmMonitorController extends Controller
{
    public function __construct(private VmMetricService $service) {}

    public function index(): View
    {
        $this->authorize('viewAny', Vm::class);
        $vms    = Vm::with('assignedUser')->orderBy('status', 'desc')->get();
        $totals = [
            'running' => $vms->where('status', 'running')->count(),
            'stopped' => $vms->where('status', 'stopped')->count(),
            'total'   => $vms->count(),
        ];
        $latestMetrics = $this->service->latestAll();
        return view('vm-monitoring.index', compact('vms', 'totals', 'latestMetrics'));
    }

    public function show(Vm $vm): View
    {
        $this->authorize('view', $vm);
        $trendData = $this->service->trendData($vm, 24);
        $latest    = $vm->latestMetricData();
        return view('vm-monitoring.show', compact('vm', 'trendData', 'latest'));
    }
}
