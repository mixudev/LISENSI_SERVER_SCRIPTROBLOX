<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Admin Utama ──────────────────────────────────
        User::create([
            'name'              => 'Super Admin',
            'email'             => 'admin@example.com',
            'password'          => Hash::make('password'),
            'role'              => 'admin',
            'is_active'         => true,
            'email_verified_at' => now(),
        ]);

        // ── User Demo ────────────────────────────────────
        User::create([
            'name'              => 'Demo User',
            'email'             => 'user@example.com',
            'password'          => Hash::make('password'),
            'role'              => 'user',
            'is_active'         => true,
            'email_verified_at' => now(),
        ]);

        // ── Produk Default ───────────────────────────────
        $products = [
            [
                'name'          => 'Universal',
                'slug'          => 'universal',
                'version'       => '1.0.0',
                'script_folder' => 'universal',
                'script_source' => 'local',
                'access_level'  => 'user',
                'status'        => 'active',
            ],
            [
                'name'          => 'Admin Tools',
                'slug'          => 'admin-tools',
                'version'       => '1.0.0',
                'script_folder' => null,
                'script_source' => 'local',
                'access_level'  => 'admin',
                'status'        => 'active',
                'notes'         => 'Script khusus admin — pastikan folder admin sudah dibuat.',
            ],
        ];

        foreach ($products as $productData) {
            Product::create($productData);
        }
    }
}
