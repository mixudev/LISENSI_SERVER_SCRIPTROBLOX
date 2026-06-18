<?php

namespace App\Repositories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;

class ProductRepository
{
    /**
     * Dapatkan semua produk yang aktif.
     */
    public function getActive(): Collection
    {
        return Product::active()->orderBy('price')->get();
    }

    /**
     * Cari produk berdasarkan slug.
     */
    public function findBySlug(string $slug): ?Product
    {
        return Product::where('slug', $slug)->first();
    }

    /**
     * Dapatkan semua produk dengan jumlah lisensi aktif.
     */
    public function getAllWithLicenseCounts(): Collection
    {
        return Product::withCount([
            'licenses',
            'licenses as active_licenses_count' => fn ($q) => $q->where('status', 'active'),
        ])
            ->orderBy('id')
            ->get();
    }

    /**
     * Cek apakah produk masih memiliki lisensi aktif (untuk mencegah penghapusan).
     */
    public function hasActiveLicenses(int $productId): bool
    {
        return Product::where('id', $productId)
            ->whereHas('licenses', fn ($q) => $q->where('status', 'active'))
            ->exists();
    }
}
