<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('licenses', function (Blueprint $table) {
            $table->id();

            // Relasi
            $table->foreignId('user_id')
                ->nullable()                 // Nullable: license bisa dibuat dulu sebelum diberikan ke user
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('product_id')
                ->nullable()
                ->constrained('products')
                ->nullOnDelete();

            // License Key
            $table->string('license_key', 255)->unique(); // Format: LZD-XXXX-XXXX-XXXX-XXXX

            // HWID Binding
            $table->string('hwid', 255)->nullable();     // Hardware ID perangkat yang terikat
            $table->unsignedTinyInteger('hwid_reset_count')->default(0); // Jumlah reset yang sudah dilakukan
            $table->timestamp('hwid_last_reset_at')->nullable(); // Waktu reset HWID terakhir

            // Status & Masa Aktif
            $table->enum('status', ['active', 'expired', 'banned', 'suspended'])->default('active');
            $table->timestamp('expired_at')->nullable();        // Null = tidak ada masa aktif (seumur hidup)
            $table->text('ban_reason')->nullable();             // Alasan ban/suspend

            // Informasi Penggunaan Terakhir
            $table->string('last_ip', 45)->nullable();          // IPv4 & IPv6
            $table->string('last_user_agent')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('activated_at')->nullable();      // Kapan pertama kali diaktifkan

            // Metadata Admin
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();             // Admin yang membuat license ini
            $table->text('notes')->nullable();  // Catatan internal admin

            $table->timestamps();
            $table->softDeletes();

            // Index untuk performa query
            $table->index('license_key');
            $table->index('user_id');
            $table->index('product_id');
            $table->index('status');
            $table->index('expired_at');
            $table->index('hwid');
            $table->index('created_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('licenses');
    }
};
