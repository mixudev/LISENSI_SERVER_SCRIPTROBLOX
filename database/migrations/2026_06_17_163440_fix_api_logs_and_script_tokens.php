<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('api_logs', function (Blueprint $table) {
            $table->string('license_key_used', 64)->nullable()->change();
        });

        Schema::table('script_tokens', function (Blueprint $table) {
            $table->foreignId('product_id')->nullable()->after('license_id')->constrained('products')->nullOnDelete();
            $table->string('script_source', 20)->nullable()->after('script_folder');
        });
    }

    public function down(): void
    {
        Schema::table('script_tokens', function (Blueprint $table) {
            $table->dropConstrainedForeignId('product_id');
            $table->dropColumn('script_source');
        });

        Schema::table('api_logs', function (Blueprint $table) {
            $table->string('license_key_used', 30)->nullable()->change();
        });
    }
};
