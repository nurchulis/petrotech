<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Models\Vm;

class VmMetricDummySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * This will create hourly metric points for the past 7 days (including today) for up to 5 VMs.
     * It prefers VMs where `is_dummy` = true; if none exist it falls back to the first VMs.
     */
    public function run(): void
    {
        $vms = Vm::where('is_dummy', true)->limit(5)->get();
        if ($vms->isEmpty()) {
            $vms = Vm::limit(5)->get();
        }

        if ($vms->isEmpty()) {
            $this->command->info('No VMs found, skipping VmMetricDummySeeder.');
            return;
        }

        $now = Carbon::now()->startOfHour();
        $start = $now->copy()->subDays(6)->startOfDay(); // 7 days including today

        $rows = [];
        foreach ($vms as $vm) {
            $hasGpu = (bool) $vm->has_gpu;

            // generate hourly points from $start to $now (inclusive)
            $cursor = $start->copy();
            while ($cursor->lte($now)) {
                $cpu = $this->randFloat(2, 2, 80); // percent
                $memory = $this->randFloat(2, 10, 90);
                $diskRead = $this->randFloat(2, 0, 50);
                $diskWrite = $this->randFloat(2, 0, 30);
                $netIn = $this->randFloat(2, 0, 100);
                $netOut = $this->randFloat(2, 0, 80);
                $gpu = $hasGpu ? $this->randFloat(2, 0, 90) : null;

                $rows[] = [
                    'vm_id' => $vm->id,
                    'cpu_utilisation' => $cpu,
                    'memory_utilisation' => $memory,
                    'disk_io_read_mb' => $diskRead,
                    'disk_io_write_mb' => $diskWrite,
                    'network_in_mb' => $netIn,
                    'network_out_mb' => $netOut,
                    'gpu_utilisation' => $gpu,
                    'recorded_at' => $cursor->toDateTimeString(),
                ];

                // insert in chunks to avoid memory bloat
                if (count($rows) >= 500) {
                    DB::table('vm_metrics')->insert($rows);
                    $rows = [];
                }

                $cursor->addHour();
            }
        }

        if (!empty($rows)) {
            DB::table('vm_metrics')->insert($rows);
        }

        $this->command->info('Inserted dummy VM metrics for ' . $vms->count() . ' VM(s).');
    }

    /**
     * Return a random float with a given precision between min and max.
     */
    private function randFloat(int $decimals = 2, float $min = 0, float $max = 100): float
    {
        $factor = pow(10, $decimals);
        return mt_rand($min * $factor, $max * $factor) / $factor;
    }
}
