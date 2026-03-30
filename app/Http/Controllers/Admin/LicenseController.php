<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\License;
use App\Models\LicenseServer;
use App\Services\License\LicenseService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LicenseController extends Controller
{
    public function __construct(private LicenseService $service)
    {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', License::class);
        $filters = $request->only(['search']);
        $vendors = $this->service->listVendors($filters);
        $expiring = $this->service->expiringWithinDays(30);

        return view('licenses.index', compact('vendors', 'expiring'));
    }

    public function vendorShow(int $vendorId): View
    {
        $this->authorize('viewAny', License::class);
        $data = $this->service->getVendorDetails($vendorId);

        return view('licenses.vendor_show', $data);
    }

    public function grantAccess(Request $request): RedirectResponse
    {
        $this->authorize('viewAny', License::class);
        $data = $request->validate([
            'username' => 'required|string|max:255',
            'license_ids' => 'required|array',
            'license_ids.*' => 'exists:licenses,id',
            'server_id' => 'required|exists:license_servers,id',
            'vendor_id' => 'required|exists:vendors,id',
        ]);

        // Get all candidate license IDs for this vendor to scope the sync
        $scopeLicenseIds = License::where('license_server_id', $data['server_id'])
            ->where('vendor_id', $data['vendor_id'])
            ->pluck('id')
            ->toArray();

        $this->service->syncAccess($data['username'], $data['license_ids'], $scopeLicenseIds, auth()->user());

        return back()->with('success', "Access for '{$data['username']}' updated successfully.")
            ->with('active_tab', 'access');
    }

    public function revokeAccess(Request $request): RedirectResponse
    {
        $this->authorize('viewAny', License::class);
        $data = $request->validate([
            'username' => 'required|string',
            'license_id' => 'required|exists:licenses,id',
        ]);

        $this->service->revokeAccess($data['username'], $data['license_id']);

        return back()->with('success', "Access revoked for '{$data['username']}'.")
            ->with('active_tab', 'access');
    }

    public function revokeAllAccess(Request $request): RedirectResponse
    {
        $this->authorize('viewAny', License::class);
        $data = $request->validate([
            'username' => 'required|string',
            'server_id' => 'required|exists:license_servers,id',
            'vendor_id' => 'required|exists:vendors,id',
        ]);

        $scopeLicenseIds = License::where('license_server_id', $data['server_id'])
            ->where('vendor_id', $data['vendor_id'])
            ->pluck('id')
            ->toArray();

        $this->service->revokeAllAccess($data['username'], $scopeLicenseIds);

        return back()->with('success', "All access for '{$data['username']}' on this vendor has been removed.")
            ->with('active_tab', 'access');
    }

    public function create(): View
    {
        $this->authorize('create', License::class);
        $servers = LicenseServer::all();
        $vendors = \App\Models\Vendor::all();
        return view('licenses.create', compact('servers', 'vendors'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', License::class);
        $data = $request->validate([
            'license_name' => 'required|string|max:255',
            'application_name' => 'required|string|max:255',
            'vendor_id' => 'required|exists:vendors,id',
            'version' => 'nullable|string|max:100',
            'total_seats' => 'required|integer|min:0',
            'license_key' => 'nullable|string',
            'status' => 'required|in:enable,disable',
            'expiry_date' => 'required|date',
            'log_file_path' => 'nullable|string|max:500',
            'license_server_id' => 'nullable|exists:license_servers,id',
            'notes' => 'nullable|string',
        ]);

        $license = $this->service->create($data, auth()->user());
        return redirect()->route('admin.licenses.vendor', $license->vendor_id)->with('success', 'License created successfully.');
    }

    public function show(License $license): View
    {
        $this->authorize('view', $license);
        $license->load(['server', 'logs', 'creator']);
        return view('licenses.show', compact('license'));
    }

    public function edit(License $license): View
    {
        $this->authorize('update', $license);
        $servers = LicenseServer::all();
        $vendors = \App\Models\Vendor::all();
        return view('licenses.edit', compact('license', 'servers', 'vendors'));
    }

    public function update(Request $request, License $license): RedirectResponse
    {
        $this->authorize('update', $license);
        $data = $request->validate([
            'license_name' => 'required|string|max:255',
            'application_name' => 'required|string|max:255',
            'vendor_id' => 'required|exists:vendors,id',
            'version' => 'nullable|string|max:100',
            'total_seats' => 'required|integer|min:0',
            'status' => 'required|in:enable,disable',
            'expiry_date' => 'required|date',
            'log_file_path' => 'nullable|string|max:500',
            'license_server_id' => 'nullable|exists:license_servers,id',
            'notes' => 'nullable|string',
        ]);

        $this->service->update($license, $data);
        return redirect()->route('admin.licenses.vendor', $license->vendor_id)->with('success', 'License updated successfully.');
    }

    public function destroy(License $license): RedirectResponse
    {
        $this->authorize('delete', $license);
        $this->service->delete($license);
        return redirect()->route('admin.licenses.index')->with('success', 'License deleted.');
    }

    public function toggle(License $license): RedirectResponse
    {
        $this->authorize('update', $license);
        $this->service->toggleStatus($license);
        return back()->with('success', 'License status updated.');
    }

    public function getUsageMetrics(Request $request, int $licenseId): \Illuminate\Http\JsonResponse
    {
        $this->authorize('viewAny', License::class);
        
        $range = $request->input('range', 'daily');
        $date = $request->input('date');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $data = $this->service->getUsageMetrics($licenseId, $range, $date, $startDate, $endDate);

        return response()->json($data);
    }

    public function exportLogs(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $this->authorize('viewAny', License::class);

        $vendorId = $request->input('vendor_id');
        $data = $this->service->getVendorDetails($vendorId);
        $logs = $data['logs'] ?? collect();

        // Create CSV headers
        $headers = ['Username', 'Feature', 'Timestamp', 'Event Type'];

        // Create the response
        return response()->streamDownload(function () use ($logs, $headers) {
            $handle = fopen('php://output', 'w');

            // Write headers
            fputcsv($handle, $headers);

            // Write data rows
            foreach ($logs as $log) {
                fputcsv($handle, [
                    $log->username,
                    $log->license_name ?? 'Unknown',
                    $log->timestamp ? $log->timestamp->format('d M Y H:i:s') : 'Unknown',
                    str_replace('_', ' ', strtoupper($log->event_type)),
                ]);
            }

            fclose($handle);
        }, 'license-logs-' . now()->format('Y-m-d-His') . '.csv', [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="license-logs-' . now()->format('Y-m-d-His') . '.csv"',
        ]);
    }
}
