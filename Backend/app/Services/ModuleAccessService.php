<?php

namespace App\Services;

use App\Models\License;
use App\Models\ModuleAccessToken;
use App\Models\Product;

/**
 * Mengelola token sesi untuk akses modul Lua (/modules/{token}/...).
 */
class ModuleAccessService
{
    public function issue(License $license, array $resolved): ModuleAccessToken
    {
        return ModuleAccessToken::issueFromResolved($license, $resolved);
    }

    /**
     * Validasi token modul + pastikan lisensi masih aktif.
     */
    public function validate(string $token): ?ModuleAccessToken
    {
        $accessToken = ModuleAccessToken::with('license')
            ->where('token', $token)
            ->where('expires_at', '>', now())
            ->first();

        if (! $accessToken) {
            return null;
        }

        $license = $accessToken->license;

        if (! $license || ! $license->isActive()) {
            return null;
        }

        if (in_array($license->status, ['banned', 'suspended'], true)) {
            return null;
        }

        if ($license->isExpired()) {
            return null;
        }

        $accessToken->update(['last_used_at' => now()]);

        return $accessToken;
    }

    /**
     * Sisipkan token sesi ke awal script agar loader bisa fetch modul terproteksi.
     *
     * @param  array{product: ?Product, folder: ?string, source: ?string}  $resolved
     */
    public function wrapScript(string $scriptContent, License $license, array $resolved): string
    {
        $accessToken = $this->issue($license, $resolved);
        $moduleBaseUrl = rtrim(config('app.url'), '/').'/modules/'.$accessToken->token;
        $scriptSource = ($resolved['source'] ?? 'local') === 'github' ? 'github' : 'local';

        $preamble = "-- LimeHub protected session\n"
            ."_G.LIMEHUB_MODULE_TOKEN = \"{$accessToken->token}\"\n"
            ."_G.LIMEHUB_BASE_URL = \"{$moduleBaseUrl}\"\n"
            ."_G.LIMEHUB_SCRIPT_SOURCE = \"{$scriptSource}\"\n\n";

        return $preamble.$scriptContent;
    }

    /**
     * Path modul yang tidak boleh di-serve via /modules (hanya lewat endpoint berlisensi).
     *
     * @return list<string>
     */
    public static function blockedModulePaths(): array
    {
        return [
            'loader.lua',
            'loader.luau',
        ];
    }

    public static function isBlockedModulePath(string $sanitizedPath): bool
    {
        $normalized = strtolower(str_replace('\\', '/', $sanitizedPath));

        foreach (self::blockedModulePaths() as $blocked) {
            if ($normalized === $blocked || str_ends_with($normalized, '/'.$blocked)) {
                return true;
            }
        }

        return false;
    }
}
