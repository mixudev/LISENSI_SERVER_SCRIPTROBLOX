<?php

namespace App\Services;

use App\Exceptions\HwidMismatchException;
use App\Exceptions\LicenseBannedException;
use App\Exceptions\LicenseExpiredException;
use App\Exceptions\LicenseNotFoundException;
use App\Models\License;
use App\Repositories\LicenseRepository;
use Illuminate\Http\Request;

/**
 * Core service untuk generate, aktivasi, validasi, dan manajemen lisensi.
 */
class LicenseService
{
    public function __construct(
        private readonly LicenseRepository $licenseRepository,
        private readonly HwidService $hwidService
    ) {}

    /**
     * Generate satu license key baru.
     *
     * @param  array{
     *   user_id?: int|null,
     *   license_type?: string,
     *   duration_days?: int|null,
     *   notes?: string|null,
     *   created_by?: int|null
     * }  $data
     */
    public function generate(array $data): License
    {
        $durationDays = $data['duration_days'] ?? null;

        return License::create([
            'user_id' => $data['user_id'] ?? null,
            'product_id' => null, // lisensi tidak terikat produk
            'license_type' => $data['license_type'] ?? 'user',
            'expired_at' => ($durationDays !== null && (int) $durationDays > 0)
                ? now()->addDays((int) $durationDays)
                : null,
            'notes' => $data['notes'] ?? null,
            'created_by' => $data['created_by'] ?? null,
            'status' => 'active',
        ]);
    }

    /**
     * Generate banyak license key sekaligus (bulk).
     *
     * @param  array{product_id: int, duration_days?: int, notes?: string, created_by?: int}  $data
     * @return License[]
     */
    public function generateBulk(array $data, int $count): array
    {
        $licenses = [];
        for ($i = 0; $i < $count; $i++) {
            $licenses[] = $this->generate($data);
        }

        return $licenses;
    }

    /**
     * Aktivasi lisensi: bind HWID jika belum terikat, atau validasi HWID jika sudah.
     * Melempar exception yang sesuai jika gagal.
     */
    public function activate(string $licenseKey, string $hwid, Request $request): License
    {
        $license = $this->licenseRepository->findByKey($licenseKey)
            ?? throw new LicenseNotFoundException;

        $this->assertNotBannedOrSuspended($license);
        $this->assertNotExpired($license);

        if (! $license->hasHwid()) {
            // Binding pertama kali
            $this->hwidService->bind($license, $hwid, $request);
        } elseif (! $license->matchesHwid($hwid)) {
            throw new HwidMismatchException;
        } else {
            // HWID cocok — update last used
            $this->hwidService->touch($license, $request);
        }

        return $license->fresh();
    }

    /**
     * Aktivasi lisensi untuk pengiriman script (GET /api/license/get, inject).
     * Sama seperti activate(), plus simpan info Roblox terbaru.
     */
    public function activateForDelivery(
        string $licenseKey,
        string $hwid,
        Request $request,
        ?string $robloxUsername = null,
        ?string $placeId = null
    ): License {
        $license = $this->activate($licenseKey, $hwid, $request);

        $license->roblox_username = $robloxUsername ?: null;
        $license->roblox_place_id = $placeId ?: null;
        $this->licenseRepository->save($license);

        return $license->fresh();
    }

    /**
     * Cari lisensi milik user tertentu tanpa validasi HWID.
     * Dipakai untuk reset HWID via dashboard/API.
     *
     * @throws LicenseNotFoundException
     */
    public function findOwnedByKey(string $licenseKey, int $userId): License
    {
        $license = $this->licenseRepository->findByKey($licenseKey)
            ?? throw new LicenseNotFoundException;

        if ($license->user_id !== $userId) {
            abort(403, 'Anda bukan pemilik lisensi ini.');
        }

        $this->assertNotBannedOrSuspended($license);
        $this->assertNotExpired($license);

        return $license;
    }

    /**
     * Validasi lisensi tanpa mengubah state.
     * Melempar exception yang sesuai jika tidak valid.
     */
    public function check(string $licenseKey, string $hwid, Request $request): License
    {
        $license = $this->licenseRepository->findByKey($licenseKey)
            ?? throw new LicenseNotFoundException;

        $this->assertNotBannedOrSuspended($license);
        $this->assertNotExpired($license);

        if ($license->hasHwid() && ! $license->matchesHwid($hwid)) {
            throw new HwidMismatchException;
        }

        // Update last_used_at saat validasi berhasil
        $this->hwidService->touch($license, $request);

        return $license;
    }

    /**
     * Suspend atau ban lisensi.
     */
    public function suspend(License $license, string $reason): void
    {
        $license->update([
            'status' => 'suspended',
            'ban_reason' => $reason,
        ]);
        $this->licenseRepository->invalidateCache($license->license_key);
    }

    /**
     * Ban lisensi permanen.
     */
    public function ban(License $license, string $reason): void
    {
        $license->update([
            'status' => 'banned',
            'ban_reason' => $reason,
        ]);
        $this->licenseRepository->invalidateCache($license->license_key);
    }

    /**
     * Reaktivasi lisensi yang suspended/banned.
     */
    public function reactivate(License $license): void
    {
        $license->update([
            'status' => 'active',
            'ban_reason' => null,
        ]);
        $this->licenseRepository->invalidateCache($license->license_key);
    }

    // ─────────────────────────────────────────
    // Private Helpers
    // ─────────────────────────────────────────

    private function assertNotBannedOrSuspended(License $license): void
    {
        if (in_array($license->status, ['banned', 'suspended'], true)) {
            throw new LicenseBannedException(
                $license->status === 'banned' ? 'License is banned.' : 'License is suspended.'
            );
        }
    }

    private function assertNotExpired(License $license): void
    {
        if ($license->isExpired()) {
            // Auto-mark expired jika belum diupdate
            if ($license->status !== 'expired') {
                $license->update(['status' => 'expired']);
                $this->licenseRepository->invalidateCache($license->license_key);
            }
            throw new LicenseExpiredException;
        }
    }
}
