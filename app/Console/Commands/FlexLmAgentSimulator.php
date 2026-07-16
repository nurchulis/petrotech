<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class FlexLmAgentSimulator extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'flexlm:simulate {--server=flexlm01.upstream.petrotech.co.id} {--vendor=schlumb} {--url=http://127.0.0.1:8000}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Simulate FlexLM Agent by generating dummy usage data and pushing it to the API';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $serverName = $this->option('server');
        $vendorName = $this->option('vendor');

        $this->info("Starting FlexLM Simulation for {$vendorName} on {$serverName}...");

        $baseUrl = rtrim($this->option('url'), '/');
        $apiUrl = $baseUrl . '/api/v1/licenses/sync';

        // Features map with realistic seats for Schlumberger
        $features = [
            'DATA_ANALYZER' => 5,
            'EDM' => 3,
            '3D' => 2,
            'PROFILE' => 4
        ];

        // Dummy users and hosts
        $users = ['ahmad.ramadhan', 'budi.santoso', 'siti.rahayu', 'eko.prasetyo', 'fajar.hidayat'];
        $hosts = ['PETREL-WS01', 'GEO-WS01', 'RESV-PC01', 'ENG-WS01'];
        $eventTypes = ['checkout', 'checkin', 'denied'];

        $events = [];
        $featuresUsage = [];

        $timestamp = Carbon::now();

        // 1. Generate current state (features_usage)
        foreach ($features as $featureName => $totalSeats) {
            $usedSeats = rand(0, $totalSeats);
            $featuresUsage[] = [
                'feature_name' => $featureName,
                'version' => '5000',
                'total_seats' => $totalSeats,
                'used_seats' => $usedSeats,
            ];
        }

        // 2. Generate random events (logs)
        $numEvents = rand(3, 8);
        for ($i = 0; $i < $numEvents; $i++) {
            $featureName = array_rand($features);
            $user = $users[array_rand($users)];
            $host = $hosts[array_rand($hosts)];
            $evType = $eventTypes[array_rand($eventTypes)];
            
            $detail = '';
            if ($evType === 'denied') {
                $detail = "({$vendorName}) DENIED: \"{$featureName}\" {$user}@{$host} (Licensed number of users already reached. MAX={$features[$featureName]})";
            } else if ($evType === 'checkout') {
                $detail = "({$vendorName}) OUT: \"{$featureName}\" {$user}@{$host}";
            } else {
                $detail = "({$vendorName}) IN: \"{$featureName}\" {$user}@{$host}";
            }

            $events[] = [
                'event_type' => $evType,
                'feature_name' => $featureName,
                'username' => $user,
                'hostname' => $host,
                'ip_address' => '10.10.1.' . rand(100, 200),
                'recorded_at' => $timestamp->copy()->subMinutes(rand(1, 10))->toIso8601String(),
                'raw_log' => $detail,
            ];
        }

        $payload = [
            'server_hostname' => $serverName,
            'vendor_name' => $vendorName,
            'timestamp' => $timestamp->toIso8601String(),
            'features_usage' => $featuresUsage,
            'events' => $events,
        ];

        $this->info("Sending payload to API: {$apiUrl}");

        $response = Http::post($apiUrl, $payload);

        if ($response->successful()) {
            $this->info("Successfully synced data: " . $response->body());
        } else {
            $this->error("Failed to sync data: " . $response->status() . " " . $response->body());
        }
    }
}
