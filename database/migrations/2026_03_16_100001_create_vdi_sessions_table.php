<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vdi_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vm_id')->constrained('vms')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('protocol', 20)->default('RDP');
            $table->string('status', 50)->default('active')->index();
            $table->string('session_token')->unique()->nullable();
            $table->timestamp('connected_at');
            $table->timestamp('disconnected_at')->nullable();
            $table->integer('duration_minutes')->nullable();
            $table->timestamps();

            $table->index(['vm_id', 'status']);
            $table->index(['user_id', 'connected_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vdi_sessions');
    }
};
