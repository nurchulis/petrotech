<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StorageDevice;
use App\Services\Storage\StorageMetricService;
use Illuminate\View\View;

class StorageController extends Controller
{
    public function __construct(private StorageMetricService $service) {}

    public function index(): View
    {
        $devices = StorageDevice::with(['metrics' => fn($q) => $q->latest('recorded_at')->limit(1)])->get();
        $summary = $this->service->summaryTotals();
        return view('storage.index', compact('devices', 'summary'));
    }

    public function show(StorageDevice $storage): View
    {
        $trendData = $this->service->trendData($storage, 30);
        $latest    = $storage->latestMetric();
        return view('storage.show', compact('storage', 'trendData', 'latest'));
    }
}
