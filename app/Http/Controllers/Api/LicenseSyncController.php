<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\License;
use App\Models\LicenseLog;
use App\Models\LicenseServer;
use App\Models\LicenseUsageMetric;
use App\Models\Vendor;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LicenseSyncController extends Controller
{
    /**
     * Handle incoming FlexLM logs and usage snapshot.
     */
    public function sync(Request $request)
    {
        $data = $request->validate([
            'server_hostname' => 'required|string',
            'vendor_name' => 'required|string',
            'timestamp' => 'required|date',
            'features_usage' => 'nullable|array',
            'features_usage.*.feature_name' => 'required|string',
            'features_usage.*.version' => 'nullable|string',
            'features_usage.*.total_seats' => 'required|integer',
            'features_usage.*.used_seats' => 'required|integer',
            'events' => 'nullable|array',
            'events.*.event_type' => 'required|string',
            'events.*.feature_name' => 'required|string',
            'events.*.username' => 'required|string',
            'events.*.hostname' => 'nullable|string',
            'events.*.ip_address' => 'nullable|string',
            'events.*.recorded_at' => 'required|date',
            'events.*.raw_log' => 'required|string',
        ]);

        try {
            DB::beginTransaction();

            // 1. Find Server and Vendor
            $server = LicenseServer::where('hostname', $data['server_hostname'])
                ->orWhere('server_name', $data['server_hostname'])
                ->first();

            if (!$server) {
                return response()->json(['error' => 'Server not found'], 404);
            }

            $vendor = Vendor::where('name', $data['vendor_name'])
                ->where('license_server_id', $server->id)
                ->first();

            if (!$vendor) {
                return response()->json(['error' => 'Vendor not found on this server'], 404);
            }

            // Update vendor last heartbeat
            $vendor->update(['last_updated' => Carbon::parse($data['timestamp'])]);

            // Cache licenses for quick lookup: array keyed by feature_name
            $licenses = License::where('vendor_id', $vendor->id)->get()->keyBy('license_name');

            $featuresProcessed = 0;
            $eventsProcessed = 0;

            // 2. Process Usage Snapshot
            if (!empty($data['features_usage'])) {
                foreach ($data['features_usage'] as $usage) {
                    $featureName = $usage['feature_name'];

                    if (!$licenses->has($featureName)) {
                        // Optionally create new feature if it doesn't exist
                        $license = License::create([
                            'license_name' => $featureName,
                            'application_name' => $data['vendor_name'] . ' App', // default fallback
                            'vendor_id' => $vendor->id,
                            'version' => $usage['version'] ?? '1.0',
                            'total_seats' => $usage['total_seats'],
                            'used_seats' => $usage['used_seats'],
                            'status' => 'enable',
                            'expiry_date' => now()->addYears(1),
                            'license_server_id' => $server->id,
                        ]);
                        $licenses->put($featureName, $license);
                    } else {
                        // Update existing
                        $license = $licenses->get($featureName);
                        $license->update([
                            'used_seats' => $usage['used_seats'],
                            'total_seats' => $usage['total_seats'],
                        ]);
                    }

                    // Insert Usage Metric
                    LicenseUsageMetric::create([
                        'license_id' => $license->id,
                        'seats_used' => $usage['used_seats'],
                        'recorded_at' => Carbon::parse($data['timestamp']),
                    ]);

                    $featuresProcessed++;
                }
            }

            // 3. Process Events (Logs)
            if (!empty($data['events'])) {
                foreach ($data['events'] as $event) {
                    $featureName = $event['feature_name'];

                    if (!$licenses->has($featureName)) {
                        // Skip logs for unknown features, or handle them based on business logic
                        continue;
                    }

                    $license = $licenses->get($featureName);

                    LicenseLog::create([
                        'license_id' => $license->id,
                        'event_type' => $event['event_type'],
                        'event_detail' => $event['raw_log'],
                        // Here we don't have accurate user_count per event in FlexLM stream,
                        // so we store current snapshot usage as an approximation, or 0.
                        'user_count' => $license->used_seats,
                        'recorded_at' => Carbon::parse($event['recorded_at']),
                        'ip_address' => $event['ip_address'] ?? null,
                    ]);

                    $eventsProcessed++;
                }
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Sync completed',
                'processed' => [
                    'features' => $featuresProcessed,
                    'events' => $eventsProcessed,
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('FlexLM Sync Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Internal Server Error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
