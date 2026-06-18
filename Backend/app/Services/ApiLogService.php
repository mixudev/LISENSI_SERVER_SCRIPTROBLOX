<?php

namespace App\Services;

use App\Models\ApiLog;
use Illuminate\Http\Request;

/**
 * Service untuk mencatat API request.
 */
class ApiLogService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function dispatch(Request $request, array $data): void
    {
        if (isset($data['license_key_used'])) {
            $data['license_key_used'] = self::maskLicenseKey($data['license_key_used']);
        }

        ApiLog::create(array_merge([
            'endpoint' => $request->path(),
            'method' => $request->method(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ], $data));
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    public function logSuccess(
        Request $request,
        string $status,
        int $responseTimeMs,
        ?int $licenseId = null,
        ?string $licenseKey = null,
        ?string $hwid = null,
        ?string $robloxUsername = null,
        ?string $robloxPlaceId = null,
        ?string $injectStep = null,
        ?int $productId = null,
        ?string $productName = null,
        ?string $scriptSource = null,
        ?string $scriptFolder = null,
        ?array $meta = null
    ): void {
        $this->dispatch($request, array_filter([
            'license_id' => $licenseId,
            'license_key_used' => $licenseKey,
            'hwid_used' => $hwid,
            'roblox_username' => $robloxUsername,
            'roblox_place_id' => $robloxPlaceId,
            'inject_step' => $injectStep,
            'product_id' => $productId,
            'product_name' => $productName,
            'script_source' => $scriptSource,
            'script_folder' => $scriptFolder,
            'request_meta' => $meta,
            'status' => $status,
            'http_code' => 200,
            'response_time_ms' => $responseTimeMs,
        ], fn ($value) => $value !== null));
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    public function logFailure(
        Request $request,
        string $status,
        int $httpCode,
        string $message,
        int $responseTimeMs,
        ?string $licenseKey = null,
        ?string $robloxUsername = null,
        ?string $robloxPlaceId = null,
        ?string $injectStep = null,
        ?string $errorDetail = null,
        ?int $licenseId = null,
        ?string $hwid = null,
        ?int $productId = null,
        ?string $productName = null,
        ?string $scriptSource = null,
        ?string $scriptFolder = null,
        ?array $meta = null
    ): void {
        $this->dispatch($request, array_filter([
            'license_id' => $licenseId,
            'license_key_used' => $licenseKey,
            'hwid_used' => $hwid,
            'roblox_username' => $robloxUsername,
            'roblox_place_id' => $robloxPlaceId,
            'inject_step' => $injectStep,
            'product_id' => $productId,
            'product_name' => $productName,
            'script_source' => $scriptSource,
            'script_folder' => $scriptFolder,
            'request_meta' => $meta,
            'status' => $status,
            'http_code' => $httpCode,
            'response_message' => $message,
            'error_detail' => $errorDetail,
            'response_time_ms' => $responseTimeMs,
        ], fn ($value) => $value !== null));
    }

    public static function maskLicenseKey(?string $key): ?string
    {
        if ($key === null || $key === '') {
            return $key;
        }

        if (preg_match('/^(LZD-[A-F0-9]{6}-)[A-F0-9-]+([A-F0-9]{6})$/', $key, $matches)) {
            return $matches[1].'****-****-****-'.$matches[2];
        }

        return substr($key, 0, 4).'****'.substr($key, -4);
    }
}
