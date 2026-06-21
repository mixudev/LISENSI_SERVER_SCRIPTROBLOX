<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->string('ticket_type', 32)->default('support')->after('status');
            $table->string('payment_status', 32)->default('unpaid')->after('ticket_type');
            $table->integer('payment_amount')->nullable()->after('payment_status');
            $table->text('payment_qr_url')->nullable()->after('payment_amount');
            $table->string('license_key_generated', 64)->nullable()->after('payment_qr_url');
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn([
                'ticket_type',
                'payment_status',
                'payment_amount',
                'payment_qr_url',
                'license_key_generated',
            ]);
        });
    }
};
