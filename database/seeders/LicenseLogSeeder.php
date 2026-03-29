<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\License;
use App\Models\LicenseLog;
use Carbon\Carbon;

class LicenseLogSeeder extends Seeder
{
    public function run(): void
    {
        $licenses = License::all();
        $usernames = ['nurchulis', 'ahmad', 'budi', 'siti', 'dewi', 'john.doe', 'jane.smith'];
        $eventTypes = [
            'checkout',
            'checkin',
            'failed_checkout',
            'failed_checkin',
            'denied'
        ];

        foreach ($licenses as $license) {
            // Generate 10-15 random logs per license
            for ($i = 0; $i < rand(10, 15); $i++) {
                $username = $usernames[array_rand($usernames)];
                $type = $eventTypes[array_rand($eventTypes)];

                $recordedAt = Carbon::now()->subMinutes(rand(10, 2000));

                $detail = match ($type) {
                    'checkout' => "User '{$username}' checked out feature",
                    'checkin' => "User '{$username}' checked in feature",
                    'failed_checkout' => "User '{$username}' failed checkout: No seats available",
                    'failed_checkin' => "User '{$username}' failed checkin: Connection lost",
                    'denied' => "User '{$username}' denied access: Unauthorized machine",
                    default => "User '{$username}' performed {$type}",
                };

                LicenseLog::create([
                    'license_id' => $license->id,
                    'event_type' => $type,
                    'event_detail' => $detail,
                    'user_count' => rand(1, 5),
                    'recorded_at' => $recordedAt,
                    'ip_address' => '10.0.0.' . rand(1, 254),
                ]);
            }
        }
    }
}
