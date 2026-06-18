<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('api_logs', function (Blueprint $table) {
            // Data Roblox untuk debugging inject
            $table->string('roblox_username', 64)->nullable()->after('hwid_used');
            $table->string('roblox_place_id', 32)->nullable()->after('roblox_username');

            // Langkah inject yang dicapai — untuk tahu stuck di mana
            // loader_download, inject_start, hwid_check, token_created, script_served, done
            $table->string('inject_step', 32)->nullable()->after('roblox_place_id');

            // Detail error yang lengkap
            $table->text('error_detail')->nullable()->after('response_message');

            $table->index('roblox_username');
            $table->index('inject_step');
        });
    }

    public function down(): void
    {
        Schema::table('api_logs', function (Blueprint $table) {
            $table->dropIndex(['roblox_username']);
            $table->dropIndex(['inject_step']);
            $table->dropColumn(['roblox_username', 'roblox_place_id', 'inject_step', 'error_detail']);
        });
    }
};
