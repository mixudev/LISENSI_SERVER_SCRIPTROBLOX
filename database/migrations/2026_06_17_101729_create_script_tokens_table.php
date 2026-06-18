<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('script_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('token', 64)->unique();     // token sekali pakai
            $table->foreignId('license_id')->constrained('licenses')->cascadeOnDelete();
            $table->string('script_folder', 100);       // folder script yang akan diservce
            $table->timestamp('expires_at');             // valid 30 detik
            $table->boolean('used')->default(false);     // sekali pakai
            $table->timestamp('created_at')->useCurrent();

            $table->index('token');
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('script_tokens');
    }
};
