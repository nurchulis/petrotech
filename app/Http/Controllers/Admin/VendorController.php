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
            'description' => 'nullable|string',
            'license_server_id' => 'required|exists:license_servers,id',
            'status' => 'required|in:enable,disable',
        ]);

        Vendor::create($data);

        return back()->with('success', 'Vendor added successfully.');
    }

    public function update(Request $request, Vendor $vendor)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:vendors,name,' . $vendor->id,
            'description' => 'nullable|string',
            'license_server_id' => 'required|exists:license_servers,id',
            'status' => 'required|in:enable,disable',
        ]);

        $vendor->update($data);

        return back()->with('success', 'Vendor updated successfully.');
    }
}
