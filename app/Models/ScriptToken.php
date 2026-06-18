<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScriptToken extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'token',
        'license_id',
        'product_id',
        'script_folder',
        'script_source',
        'expires_at',
        'used',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'used'       => 'boolean',
        ];
    }

    // ─────────────────────────────────────────
    // Relationships
    // ─────────────────────────────────────────

    public function license(): BelongsTo
    {
        return $this->belongsTo(License::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // ─────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────

    public function isValid(): bool
    {
        return ! $this->used && $this->expires_at->isFuture();
    }

    /**
     * Generate token sekali pakai yang aman secara kriptografis.
     */
    public static function generateToken(): string
    {
        return bin2hex(random_bytes(32)); // 64 karakter hex
    }
}
