<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hwid_reset_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('license_id')
                ->constrained('licenses')
                ->cascadeOnDelete();

            // HWID sebelum dan sesudah reset
            $table->string('old_hwid', 255)->nullable();  // Null jika belum pernah ada HWID
            $table->string('new_hwid', 255)->nullable();  // Null jika di-clear manual oleh admin

            // Siapa yang melakukan reset
            $table->enum('reset_by', ['user', 'admin'])->default('user');
            $table->foreignId('admin_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete(); // Diisi jika reset dilakukan oleh admin

            // Informasi jaringan
            $table->string('ip', 45)->nullable();
            $table->string('user_agent')->nullable();

            // Alasan reset (jika admin yang melakukan)
            $table->text('reason')->nullable();

            $table->timestamp('created_at')->useCurrent();

            // Index
            $table->index('license_id');
            $table->index('created_at');
            $table->index('reset_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hwid_reset_logs');
    }
};
