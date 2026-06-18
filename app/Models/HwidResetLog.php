<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HwidResetLog extends Model
{
    /**
     * Tabel ini hanya memiliki created_at, tidak ada updated_at.
     */
    public const UPDATED_AT = null;

    protected $fillable = [
        'license_id',
        'old_hwid',
        'new_hwid',
        'reset_by',
        'admin_id',
        'ip',
        'user_agent',
        'reason',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    // ─────────────────────────────────────────
    // Relationships
    // ─────────────────────────────────────────

    /**
     * Lisensi yang direset HWID-nya.
     */
    public function license(): BelongsTo
    {
        return $this->belongsTo(License::class, 'license_id');
    }

    /**
     * Admin yang melakukan reset (nullable jika dilakukan oleh user sendiri).
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    // ─────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────

    /**
     * Cek apakah reset ini dilakukan oleh admin.
     */
    public function isAdminReset(): bool
    {
        return $this->reset_by === 'admin';
    }

    /**
     * Cek apakah reset ini dilakukan oleh user.
     */
    public function isUserReset(): bool
    {
        return $this->reset_by === 'user';
    }
}
