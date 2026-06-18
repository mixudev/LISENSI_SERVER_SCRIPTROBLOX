<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\ModuleAccessToken;
use App\Services\GithubScriptService;
use App\Services\ModuleAccessService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use RuntimeException;

/**
 * Serve file modul Lua hanya dengan token sesi valid dari lisensi aktif.
 *
 * URL: GET /modules/{token}/{path}
 */
class ModuleServeController extends Controller
{
    public function __construct(
        private readonly ModuleAccessService $moduleAccessService,
        private readonly GithubScriptService $githubScriptService
    ) {}

    public function serve(Request $request, string $token, string $path): Response
    {
        $accessToken = $this->moduleAccessService->validate($token);

        if (! $accessToken) {
            return $this->luaError('Sesi modul tidak valid atau sudah kadaluarsa. Jalankan ulang loader.', 403);
        }

        $sanitized = $this->sanitizePath($path);

        if ($sanitized === null) {
            return $this->luaError('Path tidak valid.', 400);
        }

        if (ModuleAccessService::isBlockedModulePath($sanitized)) {
            return $this->luaError('Akses ditolak.', 403);
        }

        try {
            $content = $accessToken->usesGithubScript()
                ? $this->fetchFromGithub($accessToken, $sanitized)
                : $this->fetchFromLocal($accessToken, $sanitized);
        } catch (RuntimeException $e) {
            return $this->luaError($e->getMessage(), 404);
        }

        return response($content, 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
            'ngrok-skip-browser-warning' => 'true',
        ]);
    }

    private function fetchFromGithub(ModuleAccessToken $accessToken, string $sanitized): string
    {
        if (! $accessToken->github_repo) {
            throw new RuntimeException('Konfigurasi GitHub modul tidak lengkap.');
        }

        return $this->githubScriptService->fetchModuleContent(
            $accessToken->github_repo,
            $accessToken->github_branch ?? 'main',
            $accessToken->github_path_prefix ?? '',
            str_replace(DIRECTORY_SEPARATOR, '/', $sanitized)
        );
    }

    private function fetchFromLocal(ModuleAccessToken $accessToken, string $sanitized): string
    {
        $baseScriptPath = storage_path('app/private/scripts/'.$accessToken->script_folder);
        $fullPath = $baseScriptPath.DIRECTORY_SEPARATOR.$sanitized;

        if (! file_exists($fullPath)) {
            if (! str_ends_with($sanitized, '.lua') && ! str_ends_with($sanitized, '.luau')) {
                $fullPath = $baseScriptPath.DIRECTORY_SEPARATOR.$sanitized.'.lua';
            }
        }

        if (! file_exists($fullPath)) {
            throw new RuntimeException("Module '{$sanitized}' tidak ditemukan.");
        }

        $realBase = realpath($baseScriptPath);
        $realFile = realpath($fullPath);

        if ($realBase === false || $realFile === false || ! str_starts_with($realFile, $realBase)) {
            throw new RuntimeException('Path tidak valid.');
        }

        $content = file_get_contents($realFile);

        if ($content === false) {
            throw new RuntimeException('Gagal membaca module.');
        }

        return $content;
    }

    private function sanitizePath(string $path): ?string
    {
        $path = str_replace('\\', '/', $path);

        if (str_contains($path, '..') || str_contains($path, '//') || str_starts_with($path, '/')) {
            return null;
        }

        if (preg_match('/[<>:"|?*\x00-\x1f]/', $path)) {
            return null;
        }

        return str_replace('/', DIRECTORY_SEPARATOR, $path);
    }

    private function luaError(string $message, int $httpCode): Response
    {
        return response(
            "error(\"[LimeHub Module] {$message}\")",
            $httpCode,
            ['Content-Type' => 'text/plain; charset=UTF-8']
        );
    }
}
