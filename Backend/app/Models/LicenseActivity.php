<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;

class LicenseActivity extends Model
{
    /**
     * Tabel ini hanya memiliki created_at, tidak ada updated_at.
     */
    public const UPDATED_AT = null;

    // Konstanta action yang tersedia untuk konsistensi
    public const ACTION_LOGIN = 'login';

    public const ACTION_LOGOUT = 'logout';

    public const ACTION_VIEW_LICENSE = 'view_license';

    public const ACTION_RESET_HWID = 'reset_hwid';

    public const ACTION_DOWNLOAD_PRODUCT = 'download_product';

    public const ACTION_RENEW_LICENSE = 'renew_license';

    public const ACTION_LICENSE_BANNED = 'license_banned';

    public const ACTION_LICENSE_SUSPENDED = 'license_suspended';

    public const ACTION_LICENSE_ACTIVATED = 'license_activated';

    protected $fillable = [
        'user_id',
        'license_id',
        'action',
        'meta',
        'ip',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
            'created_at' => 'datetime',
        ];
    }

    // ─────────────────────────────────────────
    // Relationships
    // ─────────────────────────────────────────

    /**
     * User yang melakukan aktivitas.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Lisensi yang terkait dengan aktivitas.
     */
    public function license(): BelongsTo
    {
        return $this->belongsTo(License::class, 'license_id');
    }

    // ─────────────────────────────────────────
    // Scopes
    // ─────────────────────────────────────────

    public function scopeByAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // ─────────────────────────────────────────
    // Static Helpers (untuk kemudahan logging)
    // ─────────────────────────────────────────

    /**
     * Catat aktivitas dengan mudah.
     *
     * Contoh penggunaan:
     * LicenseActivity::log(
     *     action: LicenseActivity::ACTION_RESET_HWID,
     *     userId: auth()->id(),
     *     licenseId: $license->id,
     *     meta: ['old_hwid' => $oldHwid],
     *     request: $request
     * );
     */
    public static function log(
        string $action,
        ?int $userId = null,
        ?int $licenseId = null,
        array $meta = [],
        ?Request $request = null
    ): static {
        return static::create([
            'action' => $action,
            'user_id' => $userId,
            'license_id' => $licenseId,
            'meta' => ! empty($meta) ? $meta : null,
            'ip' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
        ]);
    }

    /**
     * Label aksi dalam Bahasa Indonesia untuk tampilan dashboard.
     */
    public static function labelFor(string $action): string
    {
        return match ($action) {
            self::ACTION_LOGIN => 'Login dashboard',
            self::ACTION_LOGOUT => 'Logout dashboard',
            self::ACTION_VIEW_LICENSE => 'Lihat lisensi',
            self::ACTION_RESET_HWID => 'Reset HWID',
            self::ACTION_DOWNLOAD_PRODUCT => 'Download script',
            self::ACTION_RENEW_LICENSE => 'Perpanjang lisensi',
            self::ACTION_LICENSE_BANNED => 'Lisensi dibanned',
            self::ACTION_LICENSE_SUSPENDED => 'Lisensi disuspend',
            self::ACTION_LICENSE_ACTIVATED => 'Lisensi diaktivasi',
            default => str_replace('_', ' ', ucfirst($action)),
        };
    }
}
