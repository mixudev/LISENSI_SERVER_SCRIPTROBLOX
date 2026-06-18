<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\HwidMismatchException;
use App\Exceptions\LicenseBannedException;
use App\Exceptions\LicenseExpiredException;
use App\Exceptions\LicenseNotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ActivateLicenseRequest;
use App\Http\Requests\Api\CheckLicenseRequest;
use App\Http\Requests\Api\InjectScriptRequest;
use App\Models\ScriptToken;
use App\Services\ApiLogService;
use App\Services\LicenseService;
use App\Services\ModuleAccessService;
use App\Services\ScriptService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use RuntimeException;
use Throwable;

class LicenseController extends Controller
{
    public function __construct(
        private readonly LicenseService $licenseService,
        private readonly ApiLogService $apiLogService,
        private readonly ScriptService $scriptService,
        private readonly ModuleAccessService $moduleAccessService
    ) {}

    /**
     * POST /api/license/activate
     * Aktivasi lisensi dan bind HWID.
     */
    public function activate(ActivateLicenseRequest $request): JsonResponse
    {
        $startTime = microtime(true);
        $key = $request->validated('key');
        $hwid = $request->validated('hwid');

        try {
            $license = $this->licenseService->activate($key, $hwid, $request);
            $responseTime = (int) ((microtime(true) - $startTime) * 1000);

            $this->apiLogService->logSuccess(
                request: $request,
                status: 'success',
                responseTimeMs: $responseTime,
                licenseId: $license->id,
                licenseKey: $key,
                hwid: $hwid
            );

            return response()->json(['status' => true, 'message' => 'Activated']);
        } catch (Throwable $e) {
            $responseTime = (int) ((microtime(true) - $startTime) * 1000);

            $this->apiLogService->logFailure(
                request: $request,
                status: class_basename($e),
                httpCode: $e->getCode() ?: 400,
                message: $e->getMessage(),
                responseTimeMs: $responseTime,
                licenseKey: $key
            );

            throw $e;
        }
    }

    /**
     * POST /api/license/check
     * Validasi lisensi.
     */
    public function check(CheckLicenseRequest $request): JsonResponse
    {
        $startTime = microtime(true);
        $key = $request->validated('key');
        $hwid = $request->validated('hwid');

        try {
            $license = $this->licenseService->check($key, $hwid, $request);
            $responseTime = (int) ((microtime(true) - $startTime) * 1000);

            $this->apiLogService->logSuccess(
                request: $request,
                status: 'success',
                responseTimeMs: $responseTime,
                licenseId: $license->id,
                licenseKey: $key,
                hwid: $hwid
            );

            return response()->json(['status' => true]);
        } catch (Throwable $e) {
            $responseTime = (int) ((microtime(true) - $startTime) * 1000);

            $this->apiLogService->logFailure(
                request: $request,
                status: class_basename($e),
                httpCode: $e->getCode() ?: 400,
                message: $e->getMessage(),
                responseTimeMs: $responseTime,
                licenseKey: $key
            );

            throw $e;
        }
    }

    /**
     * POST /api/license/inject
     * Validasi lisensi dari Roblox executor, rekam username+place_id, kembalikan URL script token.
     */
    public function inject(InjectScriptRequest $request): JsonResponse
    {
        $startTime = microtime(true);
        $key = $request->validated('key');
        $hwid = $request->validated('hwid');
        $robloxUsername = $request->validated('roblox_username');
        $placeId = $request->validated('place_id');

        try {
            $license = $this->licenseService->activateForDelivery(
                $key,
                $hwid,
                $request,
                $robloxUsername,
                $placeId
            );

            $resolved = $this->scriptService->resolveForLicense(
                $license->license_type ?? 'user',
                $placeId ?: null
            );

            if ($resolved['product'] === null && $resolved['folder'] === null) {
                throw new RuntimeException('Tidak ada script yang tersedia untuk lisensi ini.');
            }

            $token = ScriptToken::create([
                'token' => ScriptToken::generateToken(),
                'license_id' => $license->id,
                'product_id' => $resolved['product']?->id,
                'script_folder' => $resolved['folder'],
                'script_source' => $resolved['source'],
                'expires_at' => now()->addSeconds(30),
            ]);

            $scriptUrl = rtrim(config('app.url'), '/').'/s/'.$token->token;
            $productCtx = ScriptService::productLogContext(
                $resolved['product'],
                $resolved['folder'],
                $resolved['source']
            );

            $responseTime = (int) ((microtime(true) - $startTime) * 1000);

            $this->apiLogService->logSuccess(
                request: $request,
                status: 'inject_success',
                responseTimeMs: $responseTime,
                licenseId: $license->id,
                licenseKey: $key,
                hwid: $hwid,
                robloxUsername: $robloxUsername,
                robloxPlaceId: $placeId,
                injectStep: 'token_created',
                productId: $productCtx['product_id'],
                productName: $productCtx['product_name'],
                scriptSource: $productCtx['script_source'],
                scriptFolder: $productCtx['script_folder'],
                meta: [
                    'license_type' => $license->license_type,
                    'script_url' => $scriptUrl,
                    'token_expires_sec' => 30,
                    'map_folder' => $resolved['folder'],
                ]
            );

            return response()->json([
                'status' => true,
                'map' => $resolved['folder'],
                'script_url' => $scriptUrl,
            ]);
        } catch (Throwable $e) {
            $responseTime = (int) ((microtime(true) - $startTime) * 1000);

            $this->apiLogService->logFailure(
                request: $request,
                status: class_basename($e),
                httpCode: $e->getCode() ?: 400,
                message: $e->getMessage(),
                responseTimeMs: $responseTime,
                licenseKey: $key,
                robloxUsername: $robloxUsername ?? null,
                robloxPlaceId: $placeId ?? null,
                injectStep: 'license_check',
                errorDetail: $e->getTraceAsString()
            );

            throw $e;
        }
    }

    /**
     * GET /api/license/get?key=LZD-XXXX&hwid=YYYY&username=Z&place=0
     *
     * Endpoint khusus executor Roblox yang pakai game:HttpGet().
     * Return: Lua script plaintext atau error() string.
     */
    public function getScript(Request $request): Response
    {
        $startTime = microtime(true);
        $key = (string) $request->query('key', '');
        $hwid = (string) $request->query('hwid', '');
        $robloxUsername = (string) $request->query('username', '');
        $placeId = (string) $request->query('place', '0');

        $luaError = function (
            string $msg,
            string $status = 'error',
            ?string $licenseKey = null,
            ?Throwable $cause = null
        ) use ($request, $startTime, $robloxUsername, $placeId): Response {
            $responseTime = (int) ((microtime(true) - $startTime) * 1000);

            $this->apiLogService->logFailure(
                request: $request,
                status: $status,
                httpCode: 200,
                message: $msg,
                responseTimeMs: $responseTime,
                licenseKey: $licenseKey,
                robloxUsername: $robloxUsername ?: null,
                robloxPlaceId: $placeId ?: null,
                injectStep: 'get_script',
                errorDetail: $cause?->getMessage(),
            );

            return response(
                "error(\"[LimeHub] {$msg}\")",
                200,
                ['Content-Type' => 'text/plain; charset=UTF-8', 'Cache-Control' => 'no-store']
            );
        };

        if (! preg_match('/^LZD-[A-F0-9]{6}-[A-F0-9]{6}-[A-F0-9]{6}-[A-F0-9]{6}$/', $key)) {
            return $luaError('License key tidak valid. Format: LZD-XXXXXX-XXXXXX-XXXXXX-XXXXXX', 'invalid_key');
        }

        if (strlen($hwid) < 4) {
            return $luaError('HWID tidak valid.', 'invalid_hwid');
        }

        try {
            $license = $this->licenseService->activateForDelivery(
                $key,
                $hwid,
                $request,
                $robloxUsername ?: null,
                $placeId ?: null
            );

            $resolved = $this->scriptService->resolveForLicense(
                $license->license_type ?? 'user',
                $placeId ?: null
            );

            if ($resolved['product'] === null && $resolved['folder'] === null) {
                return $luaError('Tidak ada script yang tersedia. Hubungi admin.', 'no_product', $key);
            }

            $scriptContent = $this->scriptService->readScriptForResolved($resolved);
            $scriptContent = $this->moduleAccessService->wrapScript(
                $scriptContent,
                $license,
                $resolved
            );

            $productCtx = ScriptService::productLogContext(
                $resolved['product'],
                $resolved['folder'],
                $resolved['source']
            );
            $product = $resolved['product'];
            $deliveryMode = ($product && $product->usesGithubScript() && $product->hasLocalScript())
                ? 'github_local_sync'
                : (($product && $product->usesGithubScript()) ? 'github_api' : 'local');

            $responseTime = (int) ((microtime(true) - $startTime) * 1000);
            $this->apiLogService->logSuccess(
                request: $request,
                status: 'get_script_success',
                responseTimeMs: $responseTime,
                licenseId: $license->id,
                licenseKey: $key,
                hwid: $hwid,
                robloxUsername: $robloxUsername ?: null,
                robloxPlaceId: $placeId ?: null,
                injectStep: 'script_served',
                productId: $productCtx['product_id'],
                productName: $productCtx['product_name'],
                scriptSource: $productCtx['script_source'],
                scriptFolder: $productCtx['script_folder'],
                meta: [
                    'license_type' => $license->license_type,
                    'script_bytes' => strlen($scriptContent),
                    'delivery_mode' => $deliveryMode,
                    'github_synced_at' => $product?->github_synced_at?->toIso8601String(),
                ]
            );

            return response($scriptContent, 200, [
                'Content-Type' => 'text/plain; charset=UTF-8',
                'Cache-Control' => 'no-store, no-cache, must-revalidate',
                'ngrok-skip-browser-warning' => 'true',
            ]);
        } catch (LicenseNotFoundException) {
            return $luaError('License key tidak ditemukan.', 'not_found', $key);
        } catch (LicenseBannedException $e) {
            return $luaError($e->getMessage().' Hubungi admin.', 'banned', $key);
        } catch (LicenseExpiredException) {
            return $luaError('License sudah kadaluarsa. Hubungi admin untuk perpanjangan.', 'expired', $key);
        } catch (HwidMismatchException) {
            return $luaError(
                'HWID tidak cocok. Gunakan perangkat yang terdaftar atau reset HWID dari dashboard.',
                'hwid_mismatch',
                $key
            );
        } catch (RuntimeException $e) {
            return $luaError($e->getMessage(), 'no_script', $key, $e);
        } catch (Throwable $e) {
            report($e);

            return $luaError('Server error. Coba lagi atau hubungi admin.', 'server_error', $key ?: null, $e);
        }
    }
}
