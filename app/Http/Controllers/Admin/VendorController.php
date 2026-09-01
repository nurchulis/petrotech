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
            'name' => ['required', 'string', 'max:100', 'regex:/^[\pL\pN\s\-\.\@\(\)_]+$/u', 'unique:vendors,name'],
            'name_server' => ['nullable', 'string', 'max:255', 'regex:/^[\pL\pN\s\-\.\@\:\/_\(\)]+$/u'],
            'description' => 'nullable|string|max:1000',
            'license_server_id' => 'nullable|integer|min:1|max:2147483647|exists:license_servers,id',
            'port' => ['nullable', 'string', 'max:50', 'regex:/^[\pN\:\-]+$/'],
            'status' => 'required|in:enable,disable',
        ]);

        Vendor::create($data);

        return back()->with('success', 'Vendor added successfully.');
    }

    public function update(Request $request, Vendor $vendor)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', 'regex:/^[\pL\pN\s\-\.\@\(\)_]+$/u', 'unique:vendors,name,' . $vendor->id],
            'name_server' => ['nullable', 'string', 'max:255', 'regex:/^[\pL\pN\s\-\.\@\:\/_\(\)]+$/u'],
            'description' => 'nullable|string|max:1000',
            'license_server_id' => 'nullable|integer|min:1|max:2147483647|exists:license_servers,id',
            'port' => ['nullable', 'string', 'max:50', 'regex:/^[\pN\:\-]+$/'],
            'status' => 'required|in:enable,disable',
        ]);

        $vendor->update($data);

        return back()->with('success', 'Vendor updated successfully.');
    }
}
