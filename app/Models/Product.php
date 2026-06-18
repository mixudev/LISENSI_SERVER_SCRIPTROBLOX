<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'version',
        'script_folder',
        'script_source',
        'github_repo',
        'github_branch',
        'github_path',
        'access_level',   // 'user' | 'admin'
        'place_ids',      // JSON array place_id Roblox yang kompatibel
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'place_ids' => 'array',   // auto JSON encode/decode
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Product $product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
        });
    }

    // ─────────────────────────────────────────
    // Relationships
    // ─────────────────────────────────────────

    public function licenses(): HasMany
    {
        return $this->hasMany(License::class, 'product_id');
    }

    public function activeLicenses(): HasMany
    {
        return $this->hasMany(License::class, 'product_id')->where('status', 'active');
    }

    // ─────────────────────────────────────────
    // Scopes
    // ─────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    // ─────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Apakah produk ini bisa diakses oleh tipe lisensi tertentu.
     * - Lisensi user  → hanya produk access_level "user"
     * - Lisensi admin → produk "user" dan "admin"
     */
    public function isAccessibleBy(string $licenseType): bool
    {
        $accessLevel = $this->access_level ?? 'user';

        if ($licenseType === 'admin') {
            return true;
        }

        return $accessLevel === 'user';
    }

    /**
     * Skor prioritas produk untuk tipe lisensi (lebih tinggi = lebih diprioritaskan).
     */
    public function priorityForLicenseType(string $licenseType): int
    {
        if ($licenseType === 'admin' && $this->access_level === 'admin') {
            return 2;
        }

        if ($this->access_level === 'user') {
            return 1;
        }

        return 0;
    }

    /**
     * Apakah script ini kompatibel dengan place_id tertentu.
     * Null place_ids = universal (kompatibel semua).
     */
    public function isCompatibleWithPlace(?string $placeId): bool
    {
        if (empty($this->place_ids)) {
            return true; // universal
        }

        if (! $placeId || $placeId === '0') {
            return false;
        }

        return in_array($placeId, $this->place_ids, true);
    }

    /**
     * Dapatkan place_ids sebagai string yang dipisah koma untuk display.
     */
    public function getPlaceIdsDisplay(): string
    {
        if (empty($this->place_ids)) {
            return 'Universal (semua game)';
        }

        return implode(', ', $this->place_ids);
    }

    public function usesLocalScript(): bool
    {
        return $this->script_source === 'local' || ! $this->script_source;
    }

    public function usesGithubScript(): bool
    {
        return $this->script_source === 'github';
    }

    /**
     * Path folder script lokal — misal storage/app/private/scripts/universal/
     */
    public function getScriptLocalFolder(): ?string
    {
        if (! $this->script_folder) {
            return null;
        }

        return storage_path('app/private/scripts/'.ltrim($this->script_folder, '/'));
    }

    /**
     * Apakah folder script lokal ada dan punya loader.lua.
     */
    public function hasLocalScript(): bool
    {
        $folder = $this->getScriptLocalFolder();
        if (! $folder) {
            return false;
        }

        return file_exists($folder.DIRECTORY_SEPARATOR.'loader.lua');
    }

    /**
     * Backward-compat untuk code lama yang pakai script_path.
     */
    public function getScriptFullPath(): ?string
    {
        if ($this->script_folder) {
            return $this->getScriptLocalFolder().DIRECTORY_SEPARATOR.'loader.lua';
        }

        return null;
    }

    public function hasScript(): bool
    {
        if ($this->usesGithubScript()) {
            return filled($this->github_repo) && filled($this->github_path);
        }

        return $this->hasLocalScript();
    }

    /**
     * Direktori dasar modul di repo GitHub (parent folder dari loader.lua).
     */
    public function getGithubModulePrefix(): string
    {
        return \App\Services\GithubScriptService::modulePathPrefixFromLoaderPath($this->github_path);
    }
}
