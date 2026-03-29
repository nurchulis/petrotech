<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\License;
use App\Models\LicenseUsageMetric;
use Carbon\Carbon;

class LicenseUsageSeeder extends Seeder
{
    public function run(): void
    {
        $licenses = License::all();
        $now = now();

        foreach ($licenses as $license) {
            $currentUsage = rand(0, $license->total_seats);
            
            // Loop for the last 30 days, every hour
            for ($i = 30 * 24; $i >= 0; $i--) {
                $recordedAt = $now->copy()->subHours($i);
                
                // Randomly fluctuate usage by -1, 0, or +1
                $change = rand(-1, 1);
                $currentUsage = max(0, min($license->total_seats, $currentUsage + $change));

                LicenseUsageMetric::create([
                    'license_id' => $license->id,
                    'seats_used' => $currentUsage,
                    'recorded_at' => $recordedAt,
                ]);
            }
        }
    }
}
