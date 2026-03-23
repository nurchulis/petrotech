<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vms', function (Blueprint $table) {
            $table->boolean('is_dummy')->default(true)->after('notes');
            $table->string('rdp_host')->nullable()->after('is_dummy');
            $table->integer('rdp_port')->default(3389)->after('rdp_host');
            $table->string('rdp_username')->nullable()->after('rdp_port');
            $table->text('rdp_password')->nullable()->after('rdp_username'); // stored encrypted
        });
    }

    public function down(): void
    {
        Schema::table('vms', function (Blueprint $table) {
            $table->dropColumn(['is_dummy', 'rdp_host', 'rdp_port', 'rdp_username', 'rdp_password']);
        });
    }
};
