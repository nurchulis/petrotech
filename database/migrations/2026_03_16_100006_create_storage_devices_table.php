<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('storage_devices', function (Blueprint $table) {
            $table->id();
            $table->string('storage_name');
            $table->string('storage_type', 50); // NAS, SAN, Object Storage
            $table->decimal('total_space_gb', 12, 2);
            $table->string('mount_location', 500)->nullable();
            $table->string('region', 100)->nullable();
            $table->string('data_center', 100)->nullable();
            $table->string('ip_address', 50)->nullable();
            $table->string('status', 50)->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('storage_devices');
    }
};
