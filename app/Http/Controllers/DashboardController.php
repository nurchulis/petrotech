<?php

namespace App\Http\Controllers;

use App\Services\Analytics\ReportService;
use App\Services\Storage\StorageMetricService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private ReportService $reportService,
        private StorageMetricService $storageService
    ) {}

    public function index(): View
    {
        $widgets  = $this->reportService->dashboardWidgets();
        $tickets  = $this->reportService->ticketStatistics('month');
        $licenses = $this->reportService->licenseUsage();
        $storage  = $this->storageService->summaryTotals();

        return view('dashboard', compact('widgets', 'tickets', 'licenses', 'storage'));
    }
}
