<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('api_logs', function (Blueprint $table) {
            $table->foreignId('product_id')->nullable()->after('license_id')->constrained('products')->nullOnDelete();
            $table->string('product_name')->nullable()->after('product_id');
            $table->string('script_source', 20)->nullable()->after('product_name');
            $table->string('script_folder', 120)->nullable()->after('script_source');
            $table->json('request_meta')->nullable()->after('error_detail');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->timestamp('github_synced_at')->nullable()->after('github_path');
        });
    }

    public function down(): void
    {
        Schema::table('api_logs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('product_id');
            $table->dropColumn(['product_name', 'script_source', 'script_folder', 'request_meta']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('github_synced_at');
        });
    }
};
