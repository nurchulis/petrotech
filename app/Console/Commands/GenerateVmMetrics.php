<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateVmMetrics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-vm-metrics';
    protected $description = 'Generate fresh VM metrics for the last 24 hours for all VMs';

    public function handle()
    {
        $vms = \App\Models\Vm::all();
        $this->info("Generating metrics for {$vms->count()} VMs...");

        $bar = $this->output->createProgressBar($vms->count() * 24);
        $bar->start();

        foreach ($vms as $vm) {
            // Remove metrics for the last 24 hours to avoid duplicates if re-run
            \App\Models\VmMetric::where('vm_id', $vm->id)
                ->where('recorded_at', '>=', now()->subHours(24))
                ->delete();

            for ($i = 23; $i >= 0; $i--) {
                \App\Models\VmMetric::create([
                    'vm_id' => $vm->id,
                    'cpu_utilisation' => rand(15, 90) + (rand(0, 99) / 100),
                    'memory_utilisation' => rand(30, 95) + (rand(0, 99) / 100),
                    'disk_io_read_mb' => rand(5, 500),
                    'disk_io_write_mb' => rand(2, 200),
                    'network_in_mb' => rand(1, 150),
                    'network_out_mb' => rand(1, 100),
                    'gpu_utilisation' => $vm->has_gpu ? rand(20, 98) + (rand(0, 99) / 100) : null,
                    'recorded_at' => now()->subHours($i)->startOfHour(),
                ]);
                $bar->advance();
            }
        }

        $bar->finish();
        $this->newLine();
        $this->info('Metrics generated successfully for today (last 24 hours).');
    }
}
