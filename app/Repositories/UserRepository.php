<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class UserRepository
{
    /**
     * Pagination user untuk admin dengan filter opsional.
     *
     * @param  array{search?: string, role?: string, is_active?: bool}  $filters
     */
    public function paginateForAdmin(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        $query = User::withCount('licenses');

        if (! empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                    ->orWhere('email', 'like', "%{$filters['search']}%");
            });
        }

        if (isset($filters['role'])) {
            $query->where('role', $filters['role']);
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        return $query->orderBy('id', 'desc')->paginate($perPage);
    }

    /**
     * Cari user berdasarkan email (untuk autocomplete assign lisensi).
     */
    public function searchByEmail(string $email, int $limit = 10): Collection
    {
        return User::where('email', 'like', "%{$email}%")
            ->where('role', 'user')
            ->select(['id', 'name', 'email'])
            ->limit($limit)
            ->get();
    }
}
