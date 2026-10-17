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
        // Add company to users table
        if (!Schema::hasColumn('users', 'company')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('company', 255)->nullable()->after('department');
            });
        }

        // Pivot table: which vendors a user is allowed to access
        if (!Schema::hasTable('user_vendor_access')) {
            Schema::create('user_vendor_access', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->foreignId('vendor_id')->constrained()->onDelete('cascade');
                $table->foreignId('granted_by')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamps();

                $table->unique(['user_id', 'vendor_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_vendor_access');

        if (Schema::hasColumn('users', 'company')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('company');
            });
        }
    }
};
