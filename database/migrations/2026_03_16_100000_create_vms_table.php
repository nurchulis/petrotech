<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vms', function (Blueprint $table) {
            $table->id();
            $table->string('vm_name')->unique();
            $table->string('os_type', 100);
            $table->string('application_name');
            $table->string('status', 50)->default('stopped')->index();
            $table->string('region', 100)->nullable()->index();
            $table->string('data_center', 100)->nullable();
            $table->string('ip_address', 50)->nullable();
            $table->string('host_server')->nullable();
            $table->boolean('has_gpu')->default(false);
            $table->string('gpu_model')->nullable();
            $table->integer('cpu_cores')->nullable();
            $table->integer('ram_gb')->nullable();
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vms');
    }
};
