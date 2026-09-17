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
        if (!Schema::hasColumn('vendors', 'company')) {
            Schema::table('vendors', function (Blueprint $table) {
                $table->string('company', 255)->nullable()->after('name');
            });
        }

        if (!Schema::hasColumn('license_logs', 'company')) {
            Schema::table('license_logs', function (Blueprint $table) {
                $table->string('company', 255)->nullable()->after('event_type');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('vendors', 'company')) {
            Schema::table('vendors', function (Blueprint $table) {
                $table->dropColumn('company');
            });
        }

        if (Schema::hasColumn('license_logs', 'company')) {
            Schema::table('license_logs', function (Blueprint $table) {
                $table->dropColumn('company');
            });
        }
    }
};
