<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vdi_sessions', function (Blueprint $table) {
            $table->string('guacamole_connection_id')->nullable()->after('user_id');
            $table->timestamp('started_at')->nullable()->after('guacamole_connection_id');
            $table->timestamp('ended_at')->nullable()->after('started_at');
        });
    }

    public function down(): void
    {
        Schema::table('vdi_sessions', function (Blueprint $table) {
            $table->dropColumn(['guacamole_connection_id', 'started_at', 'ended_at']);
        });
    }
};
