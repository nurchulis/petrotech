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
        Schema::table('licenses', function (Blueprint $table) {
            $table->string('vendor')->nullable()->after('application_name');
            $table->string('version')->nullable()->after('vendor');
            $table->integer('total_seats')->default(0)->after('version');
            $table->integer('used_seats')->default(0)->after('total_seats');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('licenses', function (Blueprint $table) {
            $table->dropColumn(['vendor', 'version', 'total_seats', 'used_seats']);
        });
    }
};
