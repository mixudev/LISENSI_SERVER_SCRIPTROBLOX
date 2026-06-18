<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');                            // Nama produk: "Main", "VIP", dll
            $table->string('slug')->unique();                  // URL-friendly: "main", "vip"
            $table->text('description')->nullable();           // Deskripsi produk
            $table->string('version')->default('1.0.0');       // Versi produk
            $table->string('script_path')->nullable();         // Path ke file script (storage/app/scripts/main.lua)

            // Konfigurasi lisensi untuk produk ini
            $table->unsignedInteger('license_duration_days')->default(30);  // Durasi lisensi default dalam hari
            $table->unsignedTinyInteger('max_hwid_resets')->default(3);     // Maks reset HWID seumur hidup
            $table->unsignedTinyInteger('hwid_reset_interval_days')->default(30); // Interval reset dalam hari

            // Harga (opsional, untuk integrasi payment di masa depan)
            $table->decimal('price', 10, 2)->nullable();
            $table->string('currency', 3)->default('IDR');

            $table->enum('status', ['active', 'inactive', 'maintenance'])->default('active');

            $table->timestamps();
            $table->softDeletes();

            // Index
            $table->index('slug');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
