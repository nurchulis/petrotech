<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{Vm, VdiSession, License, LicenseServer, LicenseLog, VmMetric, StorageDevice, StorageMetric, Ticket, TicketComment, User};
use Carbon\Carbon;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@petrotech.id')->first();
        $user = User::where('email', 'user@petrotech.id')->first();

        // ─── VMs ──────────────────────────────────────────────────────────
        $vms = [
            ['vm_name' => 'VM-PETRA-001', 'os_type' => 'Windows Server 2022', 'application_name' => 'Petrel RE', 'status' => 'running', 'region' => 'Jakarta', 'data_center' => 'DC-JKT-01', 'ip_address' => '10.10.1.11', 'has_gpu' => true, 'gpu_model' => 'NVIDIA A100', 'cpu_cores' => 32, 'ram_gb' => 128],
            ['vm_name' => 'VM-PETRA-002', 'os_type' => 'Windows Server 2022', 'application_name' => 'Kingdom Suite', 'status' => 'running', 'region' => 'Jakarta', 'data_center' => 'DC-JKT-01', 'ip_address' => '10.10.1.12', 'has_gpu' => false, 'cpu_cores' => 16, 'ram_gb' => 64],
            ['vm_name' => 'VM-TECT-001', 'os_type' => 'Red Hat Enterprise Linux 8', 'application_name' => 'Techlog', 'status' => 'running', 'region' => 'Balikpapan', 'data_center' => 'DC-BPN-01', 'ip_address' => '10.20.1.11', 'has_gpu' => true, 'gpu_model' => 'NVIDIA T4', 'cpu_cores' => 24, 'ram_gb' => 96],
            ['vm_name' => 'VM-TECT-002', 'os_type' => 'Red Hat Enterprise Linux 8', 'application_name' => 'Eclipse', 'status' => 'stopped', 'region' => 'Balikpapan', 'data_center' => 'DC-BPN-01', 'ip_address' => '10.20.1.12', 'has_gpu' => false, 'cpu_cores' => 16, 'ram_gb' => 64],
            ['vm_name' => 'VM-SEISM-001', 'os_type' => 'Windows Server 2019', 'application_name' => 'SeisEarth', 'status' => 'running', 'region' => 'Surabaya', 'data_center' => 'DC-SBY-01', 'ip_address' => '10.30.1.11', 'has_gpu' => true, 'gpu_model' => 'NVIDIA V100', 'cpu_cores' => 48, 'ram_gb' => 256],
        ];

        foreach ($vms as $vmData) {
            $vm = Vm::updateOrCreate(['vm_name' => $vmData['vm_name']], array_merge($vmData, ['assigned_user_id' => $user->id]));

            // Generate 48 hourly metrics per VM
            for ($i = 47; $i >= 0; $i--) {
                VmMetric::create([
                    'vm_id' => $vm->id,
                    'cpu_utilisation' => rand(20, 85) + (rand(0, 99) / 100),
                    'memory_utilisation' => rand(40, 90) + (rand(0, 99) / 100),
                    'disk_io_read_mb' => rand(10, 500),
                    'disk_io_write_mb' => rand(5, 200),
                    'network_in_mb' => rand(1, 100),
                    'network_out_mb' => rand(1, 50),
                    'gpu_utilisation' => $vm->has_gpu ? rand(30, 95) + (rand(0, 99) / 100) : null,
                    'recorded_at' => now()->subHours($i),
                ]);
            }
        }

        // ─── VDI Sessions ─────────────────────────────────────────────────
        VdiSession::create([
            'vm_id' => Vm::where('vm_name', 'VM-PETRA-001')->first()->id,
            'user_id' => $user->id,
            'protocol' => 'RDP',
            'status' => 'active',
            'session_token' => \Illuminate\Support\Str::uuid(),
            'connected_at' => now()->subHours(2),
        ]);

        // ─── License Servers ──────────────────────────────────────────────
        $server = LicenseServer::create([
            'server_name' => 'LIC-SVR-JKT-01',
            'hostname' => 'licsrv01.application.local',
            'ip_address' => '10.10.1.100',
            'port' => 27000,
            'os_type' => 'Windows Server 2019',
            'location' => 'Jakarta',
            'status' => 'active',
        ]);

        // ─── Licenses (Features) ──────────────────────────────────────────
        $licenses = [
            [
                'license_name' => 'DATA_ANALYZER',
                'vendor' => 'lgcx',
                'version' => '5000',
                'application_name' => 'Petrel RE',
                'status' => 'enable',
                'expiry_date' => Carbon::createFromFormat('d-M-Y', '01-Jan-2027'),
                'total_seats' => 5,
                'used_seats' => 2
            ],
            [
                'license_name' => '3D',
                'vendor' => 'lgcx',
                'version' => '5020.0',
                'application_name' => 'Petrel RE',
                'status' => 'enable',
                'expiry_date' => Carbon::createFromFormat('d-M-Y', '01-Jan-2027'),
                'total_seats' => 10,
                'used_seats' => 4
            ],
            [
                'license_name' => 'GEO_MODELER',
                'vendor' => 'DAEMON',
                'version' => '2023.1',
                'application_name' => 'Techlog',
                'status' => 'enable',
                'expiry_date' => Carbon::createFromFormat('d-M-Y', '15-Dec-2026'),
                'total_seats' => 8,
                'used_seats' => 1
            ],
            [
                'license_name' => 'SEISMIC_ATTRIBUTES',
                'vendor' => 'DAEMON',
                'version' => '2023.1',
                'application_name' => 'SeisEarth',
                'status' => 'enable',
                'expiry_date' => Carbon::createFromFormat('d-M-Y', '01-Jun-2026'),
                'total_seats' => 3,
                'used_seats' => 0
            ],
            [
                'license_name' => 'RESERVOIR_SIM',
                'vendor' => 'lgcx',
                'version' => '2022.2',
                'application_name' => 'Eclipse',
                'status' => 'enable',
                'expiry_date' => Carbon::createFromFormat('d-M-Y', '01-Oct-2026'),
                'total_seats' => 15,
                'used_seats' => 7
            ],
        ];

        foreach ($licenses as $licData) {
            $license = License::updateOrCreate(
                ['license_name' => $licData['license_name'], 'vendor' => $licData['vendor'], 'version' => $licData['version']],
                array_merge($licData, [
                    'license_server_id' => $server->id,
                    'created_by' => $admin->id,
                ])
            );

            // Add some dummy usage logs only if none exist
            if ($license->logs()->count() === 0) {
                $usernames = ['nurchulis', 'ahmad', 'budi', 'siti', 'dewi'];
                for ($j = 0; $j < rand(2, 5); $j++) {
                    $username = $usernames[array_rand($usernames)];
                    LicenseLog::create([
                        'license_id'   => $license->id,
                        'event_type'   => 'checkout',
                        'event_detail' => "User '{$username}' checked out feature",
                        'user_count'   => rand(1, 10),
                        'recorded_at'  => now()->subMinutes(rand(10, 500)),
                    ]);
                }
            }
        }

        // ─── Additional Example Features (User-provided) ──────────────────
        $additionalFeatures = [
            ['license_name' => '3D', 'vendor' => 'lgcx', 'version' => '5000.8', 'total_seats' => 1],
            ['license_name' => '3D', 'vendor' => 'lgcx', 'version' => '5020.0', 'total_seats' => 1],
            ['license_name' => 'COMPASS_SURV_PLAN_AC', 'vendor' => 'lgcx', 'version' => '5000', 'total_seats' => 1],
            ['license_name' => 'DATA_ANALYZER', 'vendor' => 'lgcx', 'version' => '5000', 'total_seats' => 2],
            ['license_name' => 'EDM', 'vendor' => 'lgcx', 'version' => '5000', 'total_seats' => 5],
            ['license_name' => 'OPWPACKAGE', 'vendor' => 'lgcx', 'version' => '5000', 'total_seats' => 2],
            ['license_name' => 'PREDICT', 'vendor' => 'lgcx', 'version' => '5000.8', 'total_seats' => 1],
            ['license_name' => 'PREDICT', 'vendor' => 'lgcx', 'version' => '5020.0', 'total_seats' => 1],
            ['license_name' => 'PROFILE', 'vendor' => 'lgcx', 'version' => '5000', 'total_seats' => 2],
            ['license_name' => 'STRESSCHECK_CASINGSEAT', 'vendor' => 'lgcx', 'version' => '5000', 'total_seats' => 1],
            ['license_name' => 'WELLPLAN_BHA_DYNAMICS_S_PIPE', 'vendor' => 'lgcx', 'version' => '5000', 'total_seats' => 1],
            ['license_name' => 'WELLPLAN_CEMENTING', 'vendor' => 'lgcx', 'version' => '5000', 'total_seats' => 1],
            ['license_name' => 'WELLPLAN_HYDRAULICS', 'vendor' => 'lgcx', 'version' => '5000', 'total_seats' => 1],
            ['license_name' => 'WELLPLAN_TORQUE_DRAG', 'vendor' => 'lgcx', 'version' => '5000', 'total_seats' => 1],
            
            ['license_name' => 'CEMENT', 'vendor' => 'licsrv', 'version' => '5000', 'total_seats' => 1],
            ['license_name' => 'COMPASS_SURV_PLAN_AC', 'vendor' => 'licsrv', 'version' => '5000', 'total_seats' => 1],
            ['license_name' => 'DATA_ANALYZER', 'vendor' => 'licsrv', 'version' => '5000', 'total_seats' => 2],
            ['license_name' => 'EDM', 'vendor' => 'licsrv', 'version' => '5000', 'total_seats' => 3],
            ['license_name' => 'HYDRAULICS', 'vendor' => 'licsrv', 'version' => '5000', 'total_seats' => 1],
            ['license_name' => 'OPWCOMBINED', 'vendor' => 'licsrv', 'version' => '5000', 'total_seats' => 2],
            ['license_name' => 'PROFILE', 'vendor' => 'licsrv', 'version' => '5000', 'total_seats' => 2],
            ['license_name' => 'SPIPE_BHA_CSPEED', 'vendor' => 'licsrv', 'version' => '5000', 'total_seats' => 1],
            ['license_name' => 'STRESSCHECK_CASINGSEAT', 'vendor' => 'licsrv', 'version' => '5000', 'total_seats' => 1],
            ['license_name' => 'TORQUEDRAG', 'vendor' => 'licsrv', 'version' => '5000', 'total_seats' => 1],
        ];

        foreach ($additionalFeatures as $feat) {
            License::updateOrCreate(
                ['license_name' => $feat['license_name'], 'vendor' => $feat['vendor'], 'version' => $feat['version']],
                array_merge($feat, [
                    'application_name' => 'Landmark Suite',
                    'status' => 'enable',
                    'expiry_date' => Carbon::createFromFormat('d-M-Y', '01-Jan-2030'),
                    'license_server_id' => $server->id,
                    'created_by' => $admin->id,
                    'used_seats' => rand(0, $feat['total_seats']),
                ])
            );
        }

        // ─── Storage Devices ──────────────────────────────────────────────
        $storages = [
            ['storage_name' => 'NAS-JKT-01', 'storage_type' => 'NAS', 'total_space_gb' => 200000, 'mount_location' => '/mnt/nas-jkt-01', 'region' => 'Jakarta'],
            ['storage_name' => 'SAN-JKT-01', 'storage_type' => 'SAN', 'total_space_gb' => 500000, 'mount_location' => '/dev/san-jkt-01', 'region' => 'Jakarta'],
            ['storage_name' => 'OBJ-BPN-01', 'storage_type' => 'Object Storage', 'total_space_gb' => 1000000, 'mount_location' => 's3://upstream-data', 'region' => 'Balikpapan'],
        ];

        foreach ($storages as $storData) {
            $device = StorageDevice::create(array_merge($storData, ['status' => 'active']));
            $usedPct = rand(55, 80);
            StorageMetric::create([
                'storage_device_id' => $device->id,
                'used_space_gb' => round($device->total_space_gb * $usedPct / 100, 2),
                'free_space_gb' => round($device->total_space_gb * (100 - $usedPct) / 100, 2),
                'usage_percentage' => $usedPct,
                'recorded_at' => now(),
            ]);
        }

        // ─── Tickets ──────────────────────────────────────────────────────
        $tickets = [
            ['title' => 'Petrel application crashes on startup', 'category' => 'Application Error', 'priority' => 'high', 'status' => 'in_progress', 'assigned_to' => $admin->id],
            ['title' => 'Cannot connect to VDI session VM-TECT-001', 'category' => 'VDI Access', 'priority' => 'critical', 'status' => 'open', 'assigned_to' => null],
            ['title' => 'License expiry warning for Kingdom Suite', 'category' => 'License Management', 'priority' => 'medium', 'status' => 'open', 'assigned_to' => $admin->id],
            ['title' => 'Slow performance on VM-SEISM-001', 'category' => 'Performance', 'priority' => 'high', 'status' => 'resolved', 'assigned_to' => $admin->id],
            ['title' => 'Request access to Eclipse application', 'category' => 'Access Request', 'priority' => 'low', 'status' => 'closed', 'assigned_to' => $admin->id],
        ];

        foreach ($tickets as $tData) {
            $ticket = Ticket::create(array_merge($tData, ['description' => 'Detailed description of the issue.', 'created_by' => $user->id]));
            TicketComment::create([
                'ticket_id' => $ticket->id,
                'user_id' => $admin->id,
                'body' => 'We are looking into this issue. Will update shortly.',
                'is_internal_note' => false,
            ]);
        }
    }
}
