<?php

namespace App\Services;

use App\Jobs\LogApiRequestJob;
use Illuminate\Http\Request;

/**
 * Service untuk mencatat API request ke queue secara async.
 * Setiap call dispatch Job sehingga tidak memblokir response.
 */
class ApiLogService
{
    /**
     * Catat API request via Queue.
     *
     * @param  array{
     *   license_id?: int|null,
     *   license_key_used?: string|null,
     *   hwid_used?: string|null,
     *   roblox_username?: string|null,
     *   roblox_place_id?: string|null,
     *   inject_step?: string|null,
     *   status: string,
     *   http_code: int,
     *   response_message?: string|null,
     *   error_detail?: string|null,
     *   response_time_ms?: int|null
     * }  $data
     */
    public function dispatch(Request $request, array $data): void
    {
        if (isset($data['license_key_used'])) {
            $data['license_key_used'] = self::maskLicenseKey($data['license_key_used']);
        }

        \App\Models\ApiLog::create(array_merge([
            'endpoint'   => $request->path(),
            'method'     => $request->method(),
            'ip'         => $request->ip(),
            'user_agent' => $request->userAgent(),
        ], $data));
    }

    /**
     * Catat aktivitas sukses.
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
        ?string $injectStep = null
    ): void {
        $this->dispatch($request, [
            'license_id'       => $licenseId,
            'license_key_used' => $licenseKey,
            'hwid_used'        => $hwid,
            'roblox_username'  => $robloxUsername,
            'roblox_place_id'  => $robloxPlaceId,
            'inject_step'      => $injectStep,
            'status'           => $status,
            'http_code'        => 200,
            'response_time_ms' => $responseTimeMs,
        ]);
    }

    /**
     * Catat aktivitas gagal.
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
        ?string $errorDetail = null
    ): void {
        $this->dispatch($request, [
            'license_key_used' => $licenseKey,
            'roblox_username'  => $robloxUsername,
            'roblox_place_id'  => $robloxPlaceId,
            'inject_step'      => $injectStep,
            'status'           => $status,
            'http_code'        => $httpCode,
            'response_message' => $message,
            'error_detail'     => $errorDetail,
            'response_time_ms' => $responseTimeMs,
        ]);
    }

    /**
     * Mask license key untuk log — simpan prefix + suffix saja.
     */
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
