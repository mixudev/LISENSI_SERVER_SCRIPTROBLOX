<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\ScriptToken;
use App\Services\ApiLogService;
use App\Services\ModuleAccessService;
use App\Services\ScriptService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Serve script Lua menggunakan token sekali pakai.
 */
class ScriptServeController extends Controller
{
    public function __construct(
        private readonly ScriptService $scriptService,
        private readonly ModuleAccessService $moduleAccessService,
        private readonly ApiLogService $apiLogService
    ) {}

    public function serve(Request $request, string $token): Response
    {
        $startTime = microtime(true);
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

            $product = $scriptToken->product;
            $productCtx = ScriptService::productLogContext(
                $product,
                $scriptToken->script_folder,
                $scriptToken->script_source
            );

            $this->apiLogService->logSuccess(
                request: $request,
                status: 'script_served',
                responseTimeMs: (int) ((microtime(true) - $startTime) * 1000),
                licenseId: $scriptToken->license_id,
                licenseKey: $scriptToken->license->license_key,
                hwid: $scriptToken->license->hwid,
                robloxUsername: $scriptToken->license->roblox_username,
                robloxPlaceId: $scriptToken->license->roblox_place_id,
                injectStep: 'script_served',
                productId: $productCtx['product_id'],
                productName: $productCtx['product_name'],
                scriptSource: $productCtx['script_source'],
                scriptFolder: $productCtx['script_folder'],
                meta: [
                    'token' => substr($token, 0, 8).'...',
                    'script_bytes' => strlen($scriptContent),
                    'delivery_mode' => ($product && $product->hasLocalScript()) ? 'local' : 'remote',
                ]
            );
        } catch (\RuntimeException $e) {
            $this->apiLogService->logFailure(
                request: $request,
                status: 'script_serve_failed',
                httpCode: 503,
                message: $e->getMessage(),
                responseTimeMs: (int) ((microtime(true) - $startTime) * 1000),
                licenseId: $scriptToken->license_id,
                injectStep: 'script_served',
                errorDetail: $e->getMessage(),
            );

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
