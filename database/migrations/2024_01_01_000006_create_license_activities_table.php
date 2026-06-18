<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel ini menyimpan riwayat aktivitas user yang berkaitan dengan lisensi
     * (bukan aktivitas API mentah - itu ada di api_logs).
     * Contoh: user login dashboard, download produk, perpanjang lisensi, dll.
     */
    public function up(): void
    {
        Schema::create('license_activities', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('license_id')
                ->nullable()
                ->constrained('licenses')
                ->nullOnDelete();

            // Jenis aktivitas
            $table->string('action', 100); // login, logout, view_license, reset_hwid, download_product, renew_license, dll

            // Detail aktivitas dalam JSON (fleksibel)
            $table->json('meta')->nullable(); // {"product":"VIP","old_expired":"2024-01-01","new_expired":"2024-02-01"}

            // Jaringan
            $table->string('ip', 45)->nullable();
            $table->text('user_agent')->nullable();

            $table->timestamp('created_at')->useCurrent();

            // Index
            $table->index('user_id');
            $table->index('license_id');
            $table->index('action');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('license_activities');
    }
};
