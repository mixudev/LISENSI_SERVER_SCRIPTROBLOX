<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Ganti script_path (path ke single file) dengan script_folder (path ke folder)
            // Folder berisi loader.lua + semua sub-modul
            $table->string('script_folder', 100)->nullable()->after('version')
                ->comment('Nama subfolder di storage/app/private/scripts/, contoh: universal, fish-it');

            // Tambah source type: local (dari storage) atau github (dari GitHub private repo)
            $table->enum('script_source', ['local', 'github'])->default('local')->after('script_folder');

            // GitHub config — diisi jika script_source = github
            $table->string('github_repo', 200)->nullable()->after('script_source')
                ->comment('Format: owner/repo-name');
            $table->string('github_branch', 100)->nullable()->default('main')->after('github_repo');
            $table->string('github_path', 200)->nullable()->after('github_branch')
                ->comment('Path ke loader.lua di repo, contoh: scripts/universal/loader.lua');

            // Catatan internal admin
            $table->text('notes')->nullable()->after('status');

            $table->index('script_folder');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['script_folder']);
            $table->dropColumn(['script_folder', 'script_source', 'github_repo', 'github_branch', 'github_path', 'notes']);
        });
    }
};
