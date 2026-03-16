<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('license_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('license_id')->constrained('licenses')->cascadeOnDelete();
            $table->string('event_type', 100);
            $table->text('event_detail')->nullable();
            $table->integer('user_count')->default(0);
            $table->timestamp('recorded_at');

            $table->index(['license_id', 'recorded_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('license_logs');
    }
};
