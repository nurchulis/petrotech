<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('license_user_access', function (Blueprint $table) {
            $table->id();
            $table->string('username'); // String username as requested
            $table->foreignId('license_id')->constrained()->onDelete('cascade');
            $table->foreignId('granted_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->unique(['username', 'license_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('license_user_access');
    }
};
