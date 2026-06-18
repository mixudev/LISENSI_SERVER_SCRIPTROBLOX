<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('module_access_tokens', function (Blueprint $table) {
            $table->string('script_source', 20)->default('local')->after('script_folder');
            $table->string('github_repo', 200)->nullable()->after('script_source');
            $table->string('github_branch', 100)->nullable()->after('github_repo');
            $table->string('github_path_prefix', 300)->nullable()->after('github_branch');
        });
    }

    public function down(): void
    {
        Schema::table('module_access_tokens', function (Blueprint $table) {
            $table->dropColumn(['script_source', 'github_repo', 'github_branch', 'github_path_prefix']);
        });
    }
};
