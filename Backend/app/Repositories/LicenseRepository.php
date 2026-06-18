<?php

namespace App\Repositories;

use App\Models\License;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class LicenseRepository
{
    private const CACHE_TTL = 300; // 5 menit sesuai SRS

    /**
     * Cari lisensi berdasarkan key, menggunakan Redis Cache.
     */
    public function findByKey(string $licenseKey): ?License
    {
        $cacheKey = "license:{$licenseKey}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($licenseKey) {
            return License::with('product')
                ->where('license_key', $licenseKey)
                ->first();
        });
    }

    /**
     * Invalidate cache untuk license key tertentu.
     */
    public function invalidateCache(string $licenseKey): void
    {
        Cache::forget("license:{$licenseKey}");
    }

    /**
     * Dapatkan lisensi dengan filter dan pagination untuk admin.
     *
     * @param  array{status?: string, license_type?: string, search?: string}  $filters
     */
    public function paginateForAdmin(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        $query = License::with(['user', 'creator'])
            ->withTrashed(false);

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['license_type'])) {
            $query->where('license_type', $filters['license_type']);
        }

        if (! empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('license_key', 'like', "%{$filters['search']}%")
                    ->orWhere('roblox_username', 'like', "%{$filters['search']}%")
                    ->orWhereHas('user', fn ($u) => $u->where('email', 'like', "%{$filters['search']}%")
                        ->orWhere('name', 'like', "%{$filters['search']}%"));
            });
        }

        return $query->orderBy('id', 'desc')->paginate($perPage);
    }

    /**
     * Dapatkan lisensi yang akan expired dalam N hari ke depan.
     */
    public function getExpiringSoon(int $days = 7): Collection
    {
        return License::with(['user', 'product'])
            ->where('status', 'active')
            ->whereNotNull('expired_at')
            ->whereBetween('expired_at', [now(), now()->addDays($days)])
            ->orderBy('expired_at')
            ->get();
    }

    /**
     * Dapatkan semua lisensi milik seorang user.
     */
    public function getByUser(int $userId): Collection
    {
        return License::with('product')
            ->where('user_id', $userId)
            ->orderBy('id', 'desc')
            ->get();
    }

    /**
     * Simpan perubahan lisensi dan invalidate cache-nya.
     */
    public function save(License $license): bool
    {
        $saved = $license->save();

        if ($saved) {
            $this->invalidateCache($license->license_key);
        }

        return $saved;
    }
}
