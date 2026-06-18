<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Licenses table ──────────────────────────────────
        Schema::table('licenses', function (Blueprint $table) {
            // Tipe lisensi: user (akses semua script user) atau admin (akses semua termasuk admin-only)
            $table->enum('license_type', ['user', 'admin'])->default('user')->after('license_key');

            // product_id sekarang nullable — lisensi tidak terikat ke satu produk
            $table->foreignId('product_id')->nullable()->change();

            // HWID reset bebas — hapus constraint limit, tapi tetap catat jumlah reset untuk audit
            // hwid_reset_count dan hwid_last_reset_at tetap ada untuk history

            $table->index('license_type');
        });

        // ── Products table ──────────────────────────────────
        Schema::table('products', function (Blueprint $table) {
            // Kategori script: siapa yang boleh akses
            $table->enum('access_level', ['user', 'admin'])->default('user')->after('status')
                ->comment('user = semua lisensi bisa akses, admin = hanya lisensi admin');

            // Place IDs yang kompatibel dengan script ini (JSON array of place_id strings)
            // Contoh: ["8775573954","5350234932"]
            // Null = universal/berlaku untuk semua game
            $table->json('place_ids')->nullable()->after('access_level')
                ->comment('Array place_id Roblox yang kompatibel. Null = semua game.');

            $table->dropColumn(['max_hwid_resets', 'hwid_reset_interval_days']);
        });
    }

    public function down(): void
    {
        Schema::table('licenses', function (Blueprint $table) {
            $table->dropIndex(['license_type']);
            $table->dropColumn('license_type');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['access_level', 'place_ids']);
            $table->unsignedTinyInteger('max_hwid_resets')->default(3);
            $table->unsignedTinyInteger('hwid_reset_interval_days')->default(30);
        });
    }
};
