<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('storage_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('storage_device_id')->constrained('storage_devices')->cascadeOnDelete();
            $table->decimal('used_space_gb', 12, 2);
            $table->decimal('free_space_gb', 12, 2);
            $table->decimal('usage_percentage', 5, 2);
            $table->timestamp('recorded_at');

            $table->index(['storage_device_id', 'recorded_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('storage_metrics');
    }
};
