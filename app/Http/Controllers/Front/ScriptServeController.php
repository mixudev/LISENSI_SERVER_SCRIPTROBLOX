<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\ScriptToken;
use App\Services\ModuleAccessService;
use App\Services\ScriptService;
use Illuminate\Http\Response;

/**
 * Serve script Lua menggunakan token sekali pakai.
 */
class ScriptServeController extends Controller
{
    public function __construct(
        private readonly ScriptService $scriptService,
        private readonly ModuleAccessService $moduleAccessService
    ) {}

    /**
     * GET /s/{token}
     * Kembalikan script Lua plaintext jika token valid.
     */
    public function serve(string $token): Response
    {
        $scriptToken = ScriptToken::with(['product', 'license'])
            ->where('token', $token)
            ->where('used', false)
            ->where('expires_at', '>', now())
            ->first();

        if (! $scriptToken || ! $scriptToken->license) {
            return response(
                'error("[LimeHub] Token script tidak valid atau sudah kadaluarsa. Jalankan ulang loader.")',
                403,
                ['Content-Type' => 'text/plain; charset=UTF-8']
            );
        }

        $scriptToken->update(['used' => true]);

        try {
            $resolved = [
                'product' => $scriptToken->product,
                'folder' => $scriptToken->script_folder,
                'source' => $scriptToken->script_source ?? 'local',
            ];

            $scriptContent = $this->scriptService->readScriptForResolved($resolved);
            $scriptContent = $this->moduleAccessService->wrapScript(
                $scriptContent,
                $scriptToken->license,
                $resolved
            );
        } catch (\RuntimeException $e) {
            return response(
                'error("[LimeHub] Script tidak tersedia. Hubungi admin.")',
                503,
                ['Content-Type' => 'text/plain; charset=UTF-8']
            );
        }

        return response($scriptContent, 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
            'Pragma' => 'no-cache',
            'ngrok-skip-browser-warning' => 'true',
        ]);
    }
}
