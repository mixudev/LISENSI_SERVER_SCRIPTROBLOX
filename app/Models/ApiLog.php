<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApiLog extends Model
{
    /**
     * Tabel ini hanya memiliki created_at, tidak ada updated_at.
     */
    public const UPDATED_AT = null;

    protected $fillable = [
        'license_id',
        'endpoint',
        'method',
        'ip',
        'user_agent',
        'license_key_used',
        'hwid_used',
        'roblox_username',
        'roblox_place_id',
        'inject_step',
        'status',
        'http_code',
        'response_message',
        'error_detail',
        'response_time_ms',
    ];

    protected function casts(): array
    {
        return [
            'http_code' => 'integer',
            'response_time_ms' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    // ─────────────────────────────────────────
    // Relationships
    // ─────────────────────────────────────────

    /**
     * Lisensi yang terkait dengan log ini (nullable jika key tidak valid).
     */
    public function license(): BelongsTo
    {
        return $this->belongsTo(License::class, 'license_id');
    }

    // ─────────────────────────────────────────
    // Scopes
    // ─────────────────────────────────────────

    public function scopeSuccess($query)
    {
        return $query->where('status', 'success');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', '!=', 'success');
    }

    public function scopeByEndpoint($query, string $endpoint)
    {
        return $query->where('endpoint', $endpoint);
    }

    public function scopeByIp($query, string $ip)
    {
        return $query->where('ip', $ip);
    }

    // ─────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────

    /**
     * Cek apakah request ini berhasil.
     */
    public function isSuccess(): bool
    {
        return $this->status === 'success';
    }

    /**
     * Cek apakah response lambat (> 200ms sesuai target SRS).
     */
    public function isSlow(): bool
    {
        return $this->response_time_ms > 200;
    }
}
