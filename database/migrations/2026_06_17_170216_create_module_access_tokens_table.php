<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('module_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('token', 64)->unique();
            $table->foreignId('license_id')->constrained('licenses')->cascadeOnDelete();
            $table->string('script_folder', 100);
            $table->timestamp('expires_at');
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['token', 'expires_at']);
            $table->index('license_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('module_access_tokens');
    }
};
