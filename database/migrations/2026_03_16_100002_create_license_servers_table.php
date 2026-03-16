<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('license_servers', function (Blueprint $table) {
            $table->id();
            $table->string('server_name');
            $table->string('hostname');
            $table->string('ip_address', 50);
            $table->integer('port')->default(27000);
            $table->string('os_type', 100)->nullable();
            $table->string('location')->nullable();
            $table->string('status', 50)->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('license_servers');
    }
};
