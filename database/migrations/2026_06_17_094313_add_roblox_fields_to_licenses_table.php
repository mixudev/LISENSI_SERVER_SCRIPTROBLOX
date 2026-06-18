<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('licenses', function (Blueprint $table) {
            // Roblox username yang sedang aktif menggunakan key ini
            $table->string('roblox_username', 64)->nullable()->after('last_user_agent');
            // Place ID (game map) tempat user sedang bermain
            $table->string('roblox_place_id', 32)->nullable()->after('roblox_username');

            $table->index('roblox_username');
        });
    }

    public function down(): void
    {
        Schema::table('licenses', function (Blueprint $table) {
            $table->dropIndex(['roblox_username']);
            $table->dropColumn(['roblox_username', 'roblox_place_id']);
        });
    }
};
