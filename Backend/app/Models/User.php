<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'discord_id',
        'roblox_username',
        'password',
        'role',
        'phone',
        'avatar',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    // ─────────────────────────────────────────
    // Relationships
    // ─────────────────────────────────────────

    /**
     * Lisensi yang dimiliki oleh user ini.
     */
    public function licenses(): HasMany
    {
        return $this->hasMany(License::class, 'user_id');
    }

    /**
     * Lisensi yang dibuat oleh admin ini.
     */
    public function createdLicenses(): HasMany
    {
        return $this->hasMany(License::class, 'created_by');
    }

    /**
     * Riwayat aktivitas user.
     */
    public function activities(): HasMany
    {
        return $this->hasMany(LicenseActivity::class, 'user_id');
    }

    // ─────────────────────────────────────────
    // Accessors & Helpers
    // ─────────────────────────────────────────

    /**
     * Cek apakah user adalah admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Cek apakah user aktif.
     */
    public function isActive(): bool
    {
        return $this->is_active === true;
    }

    /**
     * Hitung total lisensi aktif yang dimiliki user.
     */
    public function activeLicensesCount(): int
    {
        return $this->licenses()->where('status', 'active')->count();
    }
}
