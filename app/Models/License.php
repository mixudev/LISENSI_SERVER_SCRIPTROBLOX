<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class License extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'product_id',
        'license_key',
        'license_type',
        'hwid',
        'hwid_reset_count',
        'hwid_last_reset_at',
        'status',
        'expired_at',
        'ban_reason',
        'last_ip',
        'last_user_agent',
        'last_used_at',
        'activated_at',
        'created_by',
        'notes',
        'roblox_username',
        'roblox_place_id',
    ];

    protected function casts(): array
    {
        return [
            'hwid_reset_count' => 'integer',
            'hwid_last_reset_at' => 'datetime',
            'expired_at' => 'datetime',
            'last_used_at' => 'datetime',
            'activated_at' => 'datetime',
        ];
    }

    // ─────────────────────────────────────────
    // Hooks
    // ─────────────────────────────────────────

    protected static function booted(): void
    {
        // Auto-generate license key saat dibuat jika tidak disediakan
        static::creating(function (License $license) {
            if (empty($license->license_key)) {
                $license->license_key = static::generateKey();
            }
        });
    }

    // ─────────────────────────────────────────
    // Key Generator
    // ─────────────────────────────────────────

    /**
     * Generate license key yang aman secara kriptografis.
     * Format: LZD-XXXXXX-XXXXXX-XXXXXX-XXXXXX (6 karakter hex per segment = 24 karakter hex total)
     * Total panjang: 3 + 1 + 6 + 1 + 6 + 1 + 6 + 1 + 6 = 31 karakter
     */
    public static function generateKey(): string
    {
        do {
            $segments = [];
            for ($i = 0; $i < 4; $i++) {
                $bytes      = random_bytes(3); // 3 bytes = 6 hex chars
                $segments[] = strtoupper(bin2hex($bytes));
            }
            $key = 'LZD-'.implode('-', $segments);
        } while (static::where('license_key', $key)->exists());

        return $key;
    }

    // ─────────────────────────────────────────
    // Relationships
    // ─────────────────────────────────────────

    /**
     * Pemilik lisensi.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Produk yang terkait dengan lisensi ini.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Admin yang membuat lisensi ini.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Log reset HWID untuk lisensi ini.
     */
    public function hwidResetLogs(): HasMany
    {
        return $this->hasMany(HwidResetLog::class, 'license_id');
    }

    /**
     * Log API untuk lisensi ini.
     */
    public function apiLogs(): HasMany
    {
        return $this->hasMany(ApiLog::class, 'license_id');
    }

    /**
     * Log aktivitas untuk lisensi ini.
     */
    public function activities(): HasMany
    {
        return $this->hasMany(LicenseActivity::class, 'license_id');
    }

    // ─────────────────────────────────────────
    // Scopes
    // ─────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeExpired($query)
    {
        return $query->where(function ($q) {
            $q->where('status', 'expired')
                ->orWhere(function ($inner) {
                    $inner->whereNotNull('expired_at')
                        ->where('expired_at', '<', now());
                });
        });
    }

    public function scopeByKey($query, string $key)
    {
        return $query->where('license_key', $key);
    }

    public function scopeByProduct($query, int $productId)
    {
        return $query->where('product_id', $productId);
    }

    // ─────────────────────────────────────────
    // Accessors & Helpers
    // ─────────────────────────────────────────

    /**
     * Cek apakah lisensi masih aktif (status + belum expired).
     */
    public function isActive(): bool
    {
        return $this->status === 'active' && ! $this->isExpired();
    }

    /**
     * Cek apakah lisensi sudah expired berdasarkan tanggal.
     */
    public function isExpired(): bool
    {
        if ($this->expired_at === null) {
            return false; // Lifetime license
        }

        return $this->expired_at->isPast();
    }

    /**
     * Cek apakah HWID sudah terikat.
     */
    public function hasHwid(): bool
    {
        return ! empty($this->hwid);
    }

    /**
     * Cek apakah HWID yang diberikan cocok.
     */
    public function matchesHwid(string $hwid): bool
    {
        return $this->hwid === $hwid;
    }

    /**
     * Cek apakah user masih bisa reset HWID.
     * Sekarang tidak ada batasan — user bisa reset kapan saja.
     */
    public function canResetHwid(): bool
    {
        return true;
    }

    /**
     * Cek apakah lisensi bertipe admin.
     */
    public function isAdminLicense(): bool
    {
        return $this->license_type === 'admin';
    }

    /**
     * Validasi lisensi untuk keperluan API.
     * Mengembalikan array ['valid' => bool, 'reason' => string|null]
     */
    public function validate(string $hwid): array
    {
        // Cek status
        if ($this->status === 'banned') {
            return ['valid' => false, 'reason' => 'License is banned.'];
        }

        if ($this->status === 'suspended') {
            return ['valid' => false, 'reason' => 'License is suspended.'];
        }

        if ($this->status === 'expired') {
            return ['valid' => false, 'reason' => 'License is expired.'];
        }

        // Cek expired_at
        if ($this->isExpired()) {
            // Auto-update status menjadi expired
            $this->update(['status' => 'expired']);

            return ['valid' => false, 'reason' => 'License has expired.'];
        }

        // Cek HWID
        if ($this->hasHwid()) {
            if (! $this->matchesHwid($hwid)) {
                return ['valid' => false, 'reason' => 'HWID mismatch.'];
            }
        }

        return ['valid' => true, 'reason' => null];
    }
}
