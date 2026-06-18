<?php

namespace App\Services;

use App\Models\License;
use App\Models\Product;
use RuntimeException;

/**
 * Service untuk mengambil dan menyajikan script berdasarkan produk + place_id.
 *
 * Dua sumber script:
 *  1. LOCAL  — storage/app/private/scripts/{folder}/loader.lua
 *  2. GITHUB — GitHub private repo via Personal Access Token (PAT)
 *
 * Update script:
 *  - LOCAL : edit file di storage langsung, atau push via deploy script
 *  - GITHUB: push ke repo → server otomatis fetch versi terbaru (cached 5 menit)
 */
class ScriptService
{
    public function __construct(
        private readonly GithubScriptService $githubScriptService
    ) {}

    /**
     * Mapping place_id Roblox → subfolder script lokal.
     * Digunakan HANYA saat license tidak terikat ke produk tertentu.
     * Jika produk sudah punya script_folder sendiri, ini diabaikan.
     *
     * @var array<string, string>
     */
    private const PLACE_MAP = [
        '8775573954' => 'fish-it',
        '5350234932' => 'fish-it',
    ];

    private const UNIVERSAL_FOLDER = 'universal';

    // ─────────────────────────────────────────
    // Primary API — dipanggil dari LicenseController
    // ─────────────────────────────────────────

    /**
     * Resolve folder script berdasarkan produk.
     * Fallback ke place_id mapping jika produk tidak punya script_folder.
     */
    public function resolveFolderFromProduct(Product $product, ?string $placeId = null): string
    {
        if ($product->script_folder) {
            return $product->script_folder;
        }

        if ($product->usesGithubScript() && $product->github_repo) {
            return $product->github_repo;
        }

        throw new RuntimeException("Produk \"{$product->name}\" belum dikonfigurasi (script_folder atau GitHub repo kosong).");
    }

    /**
     * Metadata produk untuk API log inject.
     *
     * @return array{product_id: ?int, product_name: ?string, script_source: ?string, script_folder: ?string}
     */
    public static function productLogContext(?Product $product, ?string $folder = null, ?string $source = null): array
    {
        return [
            'product_id' => $product?->id,
            'product_name' => $product?->name,
            'script_source' => $source ?? $product?->script_source ?? 'local',
            'script_folder' => $product?->script_folder ?? $folder,
        ];
    }

    /**
     * Resolve script terbaik untuk lisensi tertentu berdasarkan:
     * 1. place_id yang dikirim executor — produk spesifik diprioritaskan
     * 2. license_type (user/admin)      — access_level divalidasi
     *
     * Urutan prioritas:
     *   (a) Produk aktif dengan place_ids spesifik yang cocok + license_type sesuai
     *   (b) Produk aktif universal (place_ids kosong) + license_type sesuai
     *   (c) Tidak ada produk → tidak ada script (tidak fallback ke folder universal)
     *
     * Soft-deleted product TIDAK akan muncul karena Eloquent SoftDeletes sudah filter otomatis.
     */
    public function resolveForLicense(string $licenseType, ?string $placeId): array
    {
        $licenseType = $licenseType === 'admin' ? 'admin' : 'user';

        $products = Product::where('status', 'active')->get();

        $specificCandidates = [];
        $universalCandidates = [];

        foreach ($products as $product) {
            if (! $product->isAccessibleBy($licenseType)) {
                continue;
            }

            if (! empty($product->place_ids)) {
                if ($product->isCompatibleWithPlace($placeId)) {
                    $specificCandidates[] = $product;
                }
            } else {
                $universalCandidates[] = $product;
            }
        }

        $product = $this->pickBestProduct($specificCandidates, $licenseType)
            ?? $this->pickBestProduct($universalCandidates, $licenseType);

        if ($product) {
            return [
                'product' => $product,
                'folder' => $this->resolveFolderFromProduct($product, $placeId),
                'source' => $product->script_source ?? 'local',
            ];
        }

        return [
            'product' => null,
            'folder' => null,
            'source' => null,
        ];
    }

    /**
     * Pilih produk terbaik dari kandidat — admin license mendapat prioritas produk admin-only.
     *
     * @param  list<Product>  $candidates
     */
    private function pickBestProduct(array $candidates, string $licenseType): ?Product
    {
        if ($candidates === []) {
            return null;
        }

        usort($candidates, fn (Product $a, Product $b) => $b->priorityForLicenseType($licenseType) <=> $a->priorityForLicenseType($licenseType));

        return $candidates[0];
    }

    /**
     * Baca script berdasarkan hasil resolveForLicense.
     *
     * @throws RuntimeException jika tidak ada produk aktif atau script tidak tersedia.
     */
    public function readScriptForResolved(array $resolved): string
    {
        $product = $resolved['product'];

        if ($product === null && $resolved['folder'] === null) {
            throw new RuntimeException('Tidak ada produk aktif yang tersedia untuk lisensi ini.');
        }

        if ($product && $product->usesGithubScript()) {
            if ($product->hasLocalScript()) {
                return $this->readScript($product->script_folder);
            }

            return $this->fetchFromGithub($product);
        }

        return $this->readScript($resolved['folder']);
    }

    // ─────────────────────────────────────────
    // Place ID / Folder Mapping (legacy + fallback)
    // ─────────────────────────────────────────

    public function resolveScriptFolder(?string $placeId): string
    {
        if ($placeId && isset(self::PLACE_MAP[$placeId])) {
            return self::PLACE_MAP[$placeId];
        }

        return self::UNIVERSAL_FOLDER;
    }

    /**
     * Baca loader.lua dari folder lokal.
     * TIDAK fallback ke universal — tiap folder harus ada file-nya sendiri.
     *
     * @throws RuntimeException
     */
    public function readScript(string $folder): string
    {
        $specificPath = storage_path("app/private/scripts/{$folder}/loader.lua");

        if (file_exists($specificPath)) {
            return file_get_contents($specificPath);
        }

        throw new RuntimeException("Script tidak tersedia (folder: {$folder}).");
    }

    // ─────────────────────────────────────────
    // GitHub Private Repo
    // ─────────────────────────────────────────

    /**
     * Fetch script dari GitHub private repo menggunakan PAT.
     * Di-cache selama GITHUB_CACHE_TTL detik — cukup untuk production.
     *
     * Setup:
     *   1. Buat GitHub Personal Access Token (PAT) dengan scope: repo → contents:read
     *   2. Tambahkan ke .env: GITHUB_PAT=ghp_xxxx
     *   3. Set produk: script_source=github, github_repo=owner/repo,
     *      github_branch=main, github_path=path/to/loader.lua
     *
     * @throws RuntimeException
     */
    public function fetchFromGithub(Product $product): string
    {
        $repo = (string) $product->github_repo;
        $branch = $product->github_branch ?? 'main';
        $path = ltrim($product->github_path ?? '', '/');

        if (! $this->githubScriptService->patConfigured()) {
            throw new RuntimeException('GitHub PAT tidak dikonfigurasi. Set GITHUB_PAT di .env');
        }

        return $this->githubScriptService->fetchFileContent($repo, $branch, $path);
    }

    /**
     * Invalidate GitHub cache untuk produk tertentu.
     * Dipanggil saat admin force-refresh script.
     */
    public function invalidateGithubCache(Product $product): void
    {
        $this->githubScriptService->invalidateCache(
            (string) $product->github_repo,
            $product->github_branch ?? 'main',
            ltrim($product->github_path ?? '', '/')
        );
    }

    // ─────────────────────────────────────────
    // Payload untuk inject
    // ─────────────────────────────────────────

    /**
     * @return array{script: string, folder: string, source: string}
     */
    public function getInjectionPayload(License $license, ?string $placeId): array
    {
        $resolved = $this->resolveForLicense($license->license_type ?? 'user', $placeId);

        if ($resolved['product'] === null && $resolved['folder'] === null) {
            throw new RuntimeException('Tidak ada produk aktif yang tersedia untuk lisensi ini.');
        }

        return [
            'script' => $this->readScriptForResolved($resolved),
            'folder' => $resolved['folder'],
            'source' => $resolved['source'] ?? 'local',
        ];
    }

    // ─────────────────────────────────────────
    // Scan folder lokal yang tersedia
    // ─────────────────────────────────────────

    /**
     * Scan semua subfolder di storage/app/private/scripts/ yang punya loader.lua.
     * Dipakai di dropdown "Pilih Folder Script" saat buat/edit produk.
     *
     * @return array<string, array{folder: string, has_loader: bool, file_count: int, size_kb: float}>
     */
    public static function scanLocalFolders(): array
    {
        $baseDir = storage_path('app/private/scripts');
        $result = [];

        if (! is_dir($baseDir)) {
            return $result;
        }

        foreach (scandir($baseDir) as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $fullPath = $baseDir.DIRECTORY_SEPARATOR.$item;
            if (! is_dir($fullPath)) {
                continue;
            }

            $hasLoader = file_exists($fullPath.DIRECTORY_SEPARATOR.'loader.lua');
            $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($fullPath));
            $luaFiles = 0;
            $totalBytes = 0;

            foreach ($files as $file) {
                if ($file->isFile()) {
                    $luaFiles++;
                    $totalBytes += $file->getSize();
                }
            }

            $result[$item] = [
                'folder' => $item,
                'has_loader' => $hasLoader,
                'file_count' => $luaFiles,
                'size_kb' => round($totalBytes / 1024, 1),
            ];
        }

        return $result;
    }

    // ─────────────────────────────────────────
    // Static Helpers untuk Blade
    // ─────────────────────────────────────────

    public static function getMapNameFromPlaceId(?string $placeId): string
    {
        if (! $placeId || $placeId === '0') {
            return '—';
        }

        $names = [
            '8775573954' => 'Fish It!',
            '5350234932' => 'Fish It!',
        ];

        if (isset($names[$placeId])) {
            return $names[$placeId];
        }

        $product = Product::query()
            ->where('status', 'active')
            ->whereNotNull('place_ids')
            ->get()
            ->first(fn (Product $product) => $product->isCompatibleWithPlace($placeId));

        if ($product) {
            return $product->name;
        }

        return 'Map tidak dikenal';
    }
}
