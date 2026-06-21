<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('discord_id', 32)->index();
            $table->string('channel_id', 32)->unique()->index();
            $table->string('status', 32)->default('open'); // open, processing, closed
            $table->string('processed_by', 32)->nullable();
            $table->string('closed_by', 32)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
