<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_keys', function (Blueprint $table) {
            $table->id();
            $table->string('provider', 32)->index(); // gemini, groq, openrouter
            $table->string('api_key', 255);
            $table->string('model', 100);
            $table->integer('priority')->default(1); // 1 = highest priority
            $table->boolean('is_active')->default(true);
            $table->integer('error_count')->default(0);
            $table->integer('usage_count')->default(0);
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_keys');
    }
};
