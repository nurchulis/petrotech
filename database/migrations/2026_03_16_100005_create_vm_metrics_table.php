<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vm_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vm_id')->constrained('vms')->cascadeOnDelete();
            $table->decimal('cpu_utilisation', 5, 2)->nullable();
            $table->decimal('memory_utilisation', 5, 2)->nullable();
            $table->decimal('disk_io_read_mb', 10, 2)->nullable();
            $table->decimal('disk_io_write_mb', 10, 2)->nullable();
            $table->decimal('network_in_mb', 10, 2)->nullable();
            $table->decimal('network_out_mb', 10, 2)->nullable();
            $table->decimal('gpu_utilisation', 5, 2)->nullable();
            $table->timestamp('recorded_at');

            $table->index(['vm_id', 'recorded_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vm_metrics');
    }
};
