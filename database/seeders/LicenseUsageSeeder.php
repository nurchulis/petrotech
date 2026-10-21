<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\License;
use Carbon\Carbon;

class LicenseUsageSeeder extends Seeder
{
    public function run(): void
    {
        $licenses = License::all();

        if ($licenses->isEmpty()) {
            $this->command->info('No licenses found, skipping.');
            return;
        }

        $now = Carbon::now()->startOfHour();
        $totalInserted = 0;

        foreach ($licenses as $license) {
            $maxSeats      = max(1, (int) $license->total_seats);
            $currentUsage  = rand(0, $maxSeats);
            $rows          = [];

            // 30 days back, hourly  →  720 data points per license
            for ($i = 30 * 24; $i >= 0; $i--) {
                // Randomly fluctuate usage by -1, 0, or +1, capped to [0, total_seats]
                $change       = rand(-1, 1);
                $currentUsage = max(0, min($maxSeats, $currentUsage + $change));

                $rows[] = [
                    'license_id'  => $license->id,
                    'seats_used'  => $currentUsage,
                    'recorded_at' => $now->copy()->subHours($i)->toDateTimeString(),
                ];

                // SQL Server max 2100 params; 3 cols × 200 rows = 600 params (safe)
                if (count($rows) >= 200) {
                    DB::table('license_usage_metrics')->insert($rows);
                    $totalInserted += count($rows);
                    $rows = [];
                }
            }

            if (!empty($rows)) {
                DB::table('license_usage_metrics')->insert($rows);
                $totalInserted += count($rows);
                $rows = [];
            }
        }

        $this->command->info("Inserted {$totalInserted} usage metric rows for {$licenses->count()} license(s).");
    }
}
