<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Models\StorageDevice;

class StorageMetricDummySeeder extends Seeder
{
    /**
     * Create daily storage metrics for the last 30 days (including today) for up to 5 devices.
     */
    public function run(): void
    {
        $devices = StorageDevice::limit(5)->get();
        if ($devices->isEmpty()) {
            $this->command->info('No storage devices found, skipping StorageMetricDummySeeder.');
            return;
        }

        $now = Carbon::now()->startOfDay();
        $start = $now->copy()->subDays(29); // last 30 days including today

        $rows = [];
        foreach ($devices as $dev) {
            // pick a consistent base used GB between 50 and 2000 for this device
            $totalGb = $dev->total_space_gb ?? 1024; // fallback
            $baseUsed = min(max($totalGb * 0.2, 50), $totalGb * 0.9);

            $cursor = $start->copy();
            while ($cursor->lte($now)) {
                // small daily fluctuation around baseUsed
                $variance = $this->randFloat(2, -($baseUsed * 0.05), ($baseUsed * 0.05));
                $used = max(0, min($totalGb, $baseUsed + $variance));
                $free = max(0, $totalGb - $used);
                $pct = $totalGb > 0 ? ($used / $totalGb) * 100 : 0;

                $rows[] = [
                    'storage_device_id' => $dev->id,
                    'used_space_gb' => round($used, 2),
                    'free_space_gb' => round($free, 2),
                    'usage_percentage' => round($pct, 2),
                    'recorded_at' => $cursor->toDateString() . ' 00:00:00',
                ];

                if (count($rows) >= 500) {
                    DB::table('storage_metrics')->insert($rows);
                    $rows = [];
                }

                $cursor->addDay();
            }
        }

        if (!empty($rows)) {
            DB::table('storage_metrics')->insert($rows);
        }

        $this->command->info('Inserted storage metrics for ' . $devices->count() . ' device(s).');
    }

    private function randFloat(int $decimals = 2, float $min = 0, float $max = 1): float
    {
        $factor = pow(10, $decimals);
        return mt_rand((int)($min * $factor), (int)($max * $factor)) / $factor;
    }
}
