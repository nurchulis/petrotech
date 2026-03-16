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
    public function __construct(private LicenseService $service) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', License::class);
        $filters  = $request->only(['status', 'search']);
        $licenses = $this->service->list($filters);
        $expiring = $this->service->expiringWithinDays(30);
        return view('licenses.index', compact('licenses', 'expiring'));
    }

    public function create(): View
    {
        $this->authorize('create', License::class);
        $servers = LicenseServer::all();
        return view('licenses.create', compact('servers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', License::class);
        $data = $request->validate([
            'license_name'      => 'required|string|max:255',
            'application_name'  => 'required|string|max:255',
            'license_key'       => 'nullable|string',
            'status'            => 'required|in:enable,disable',
            'expiry_date'       => 'required|date',
            'log_file_path'     => 'nullable|string|max:500',
            'license_server_id' => 'nullable|exists:license_servers,id',
            'notes'             => 'nullable|string',
        ]);

        $this->service->create($data, auth()->user());
        return redirect()->route('admin.licenses.index')->with('success', 'License created successfully.');
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
        return view('licenses.edit', compact('license', 'servers'));
    }

    public function update(Request $request, License $license): RedirectResponse
    {
        $this->authorize('update', $license);
        $data = $request->validate([
            'license_name'      => 'required|string|max:255',
            'application_name'  => 'required|string|max:255',
            'status'            => 'required|in:enable,disable',
            'expiry_date'       => 'required|date',
            'license_server_id' => 'nullable|exists:license_servers,id',
            'notes'             => 'nullable|string',
        ]);

        $this->service->update($license, $data);
        return redirect()->route('admin.licenses.index')->with('success', 'License updated successfully.');
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
}
