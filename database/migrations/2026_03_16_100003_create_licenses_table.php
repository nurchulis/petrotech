<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('licenses', function (Blueprint $table) {
            $table->id();
            $table->string('license_name');
            $table->string('application_name')->index();
            $table->text('license_key')->nullable();
            $table->string('status', 20)->default('enable')->index();
            $table->date('expiry_date')->index();
            $table->string('log_file_path', 500)->nullable();
            $table->foreignId('license_server_id')->nullable()->constrained('license_servers')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('licenses');
    }
};
