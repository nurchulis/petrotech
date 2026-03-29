<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Vendor;

class VendorController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:vendors,name',
            'name_server' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'license_server_id' => 'nullable|exists:license_servers,id',
            'port' => 'nullable|string|max:50',
            'status' => 'required|in:enable,disable',
        ]);

        Vendor::create($data);

        return back()->with('success', 'Vendor added successfully.');
    }

    public function update(Request $request, Vendor $vendor)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:vendors,name,' . $vendor->id,
            'name_server' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'license_server_id' => 'nullable|exists:license_servers,id',
            'port' => 'nullable|string|max:50',
            'status' => 'required|in:enable,disable',
        ]);

        $vendor->update($data);

        return back()->with('success', 'Vendor updated successfully.');
    }
}
