<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Analytics\ReportService;
use App\Services\VmMonitoring\VmMetricService;
use App\Services\Storage\StorageMetricService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnalyticsController extends Controller
{
    public function __construct(
        private ReportService $reportService,
        private VmMetricService $vmService,
        private StorageMetricService $storageService
    ) {}

    public function index(Request $request): View
    {
        $period   = $request->get('period', 'month');
        $widgets  = $this->reportService->dashboardWidgets();
        $tickets  = $this->reportService->ticketStatistics($period);
        $vmStats  = $this->reportService->vmUtilisationSummary();
        $licenses = $this->reportService->licenseUsage();
        $storage  = $this->storageService->summaryTotals();

        return view('analytics.index', compact('widgets', 'tickets', 'vmStats', 'licenses', 'storage', 'period'));
    }
}
