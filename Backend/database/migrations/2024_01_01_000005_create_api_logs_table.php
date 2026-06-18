<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_logs', function (Blueprint $table) {
            $table->id();

            // Relasi ke license (nullable, karena log bisa untuk request yang gagal/key tidak valid)
            $table->foreignId('license_id')
                ->nullable()
                ->constrained('licenses')
                ->nullOnDelete();

            // Informasi Request
            $table->string('endpoint', 100);          // /api/license/activate, /api/license/check, dll
            $table->string('method', 10)->default('POST'); // HTTP Method
            $table->string('ip', 45);
            $table->text('user_agent')->nullable();

            // Payload (untuk debugging - simpan key yang digunakan, jangan simpan data sensitif lain)
            $table->string('license_key_used', 30)->nullable(); // Key yang digunakan dalam request
            $table->string('hwid_used', 255)->nullable();       // HWID yang dikirim

            // Response
            $table->string('status', 50);             // success, failed, invalid_key, hwid_mismatch, dll
            $table->unsignedSmallInteger('http_code')->default(200); // HTTP response code
            $table->text('response_message')->nullable(); // Pesan response

            // Waktu eksekusi (ms) untuk monitoring performa
            $table->unsignedInteger('response_time_ms')->nullable();

            $table->timestamp('created_at')->useCurrent();

            // Index untuk query log
            $table->index('license_id');
            $table->index('endpoint');
            $table->index('status');
            $table->index('ip');
            $table->index('created_at');
            $table->index('license_key_used');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_logs');
    }
};
