<?php

namespace App\Services;

use App\Models\HwidResetLog;
use App\Models\License;
use App\Models\LicenseActivity;
use App\Repositories\LicenseRepository;
use Illuminate\Http\Request;

/**
 * Service untuk manajemen HWID binding dan reset.
 */
class HwidService
{
    public function __construct(
        private readonly LicenseRepository $licenseRepository
    ) {}

    /**
     * Bind HWID ke lisensi (aktivasi pertama kali).
     */
    public function bind(License $license, string $hwid, Request $request): void
    {
        $license->hwid = $hwid;
        $license->activated_at = now();
        $license->last_used_at = now();
        $license->last_ip = $request->ip();
        $license->last_user_agent = $request->userAgent();
        $this->licenseRepository->save($license);

        LicenseActivity::log(
            action: LicenseActivity::ACTION_LICENSE_ACTIVATED,
            userId: $license->user_id,
            licenseId: $license->id,
            meta: ['hwid' => $hwid],
            request: $request
        );
    }

    /**
     * Update last_used_at dan last_ip setelah validasi sukses.
     */
    public function touch(License $license, Request $request): void
    {
        $license->last_used_at = now();
        $license->last_ip = $request->ip();
        $license->last_user_agent = $request->userAgent();
        $this->licenseRepository->save($license);
    }

    /**
     * Reset HWID oleh user dari dashboard.
     * Throws exception jika tidak diizinkan.
     */
    public function resetByUser(License $license, Request $request): void
    {
        if (! $license->canResetHwid()) {
            throw new \RuntimeException('HWID reset limit reached or cooldown active.');
        }

        $this->performReset($license, 'user', null, $request);
    }

    /**
     * Reset HWID oleh admin (tidak terbatas oleh cooldown).
     */
    public function resetByAdmin(License $license, Request $request, ?string $reason = null): void
    {
        $this->performReset($license, 'admin', $request->user()?->id, $request, $reason);
    }

    /**
     * Eksekusi reset HWID: update license, catat log dan aktivitas.
     */
    private function performReset(
        License $license,
        string $resetBy,
        ?int $adminId,
        Request $request,
        ?string $reason = null
    ): void {
        $oldHwid = $license->hwid;

        HwidResetLog::create([
            'license_id' => $license->id,
            'old_hwid' => $oldHwid,
            'new_hwid' => null,
            'reset_by' => $resetBy,
            'admin_id' => $adminId,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'reason' => $reason,
        ]);

        $license->hwid = null;
        $license->hwid_reset_count += 1;
        $license->hwid_last_reset_at = now();
        $this->licenseRepository->save($license);

        LicenseActivity::log(
            action: LicenseActivity::ACTION_RESET_HWID,
            userId: $license->user_id,
            licenseId: $license->id,
            meta: ['old_hwid' => $oldHwid, 'reset_by' => $resetBy],
            request: $request
        );
    }
}
