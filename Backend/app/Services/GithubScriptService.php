<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Integrasi GitHub private repo untuk script & modul Lua.
 *
 * Token yang didukung (set di .env sebagai GITHUB_PAT):
 *  - Classic PAT (ghp_...) — REKOMENDASI untuk private repo, scope: repo
 *  - Fine-grained PAT (github_pat_...) — permission Contents: Read pada repo target
 */
class GithubScriptService
{
    private const CACHE_TTL = 300;

    /** @var list<string> */
    private const COMMON_LOADER_PATHS = [
        'loader.lua',
        'scripts/loader.lua',
        'src/loader.lua',
        'universal/loader.lua',
        'scripts/universal/loader.lua',
    ];

    public function patConfigured(): bool
    {
        return filled(config('services.github.pat'));
    }

    /**
     * @return array{type: string, label: string, recommendation: string}
     */
    public function patInfo(): array
    {
        $pat = (string) config('services.github.pat', '');

        if ($pat === '') {
            return [
                'type' => 'missing',
                'label' => 'Belum dikonfigurasi',
                'recommendation' => 'Set GITHUB_PAT di .env — gunakan Classic PAT (ghp_) dengan scope repo untuk private repository.',
            ];
        }

        if (str_starts_with($pat, 'ghp_') || str_starts_with($pat, 'gho_')) {
            return [
                'type' => 'classic',
                'label' => 'Classic PAT',
                'recommendation' => 'Classic PAT terdeteksi. Pastikan scope repo (full control of private repositories) aktif.',
            ];
        }

        if (str_starts_with($pat, 'github_pat_')) {
            return [
                'type' => 'fine_grained',
                'label' => 'Fine-grained PAT',
                'recommendation' => 'Fine-grained PAT terdeteksi. Berikan akses Contents: Read ke setiap private repo produk.',
            ];
        }

        return [
            'type' => 'unknown',
            'label' => 'Token tidak dikenali',
            'recommendation' => 'Gunakan Classic PAT (ghp_) dengan scope repo, atau Fine-grained PAT dengan Contents: Read.',
        ];
    }

    public function normalizeRepo(string $input): string
    {
        $input = trim($input);

        if (preg_match('#github\.com[:/]([^/]+)/([^/.\s]+)#i', $input, $matches)) {
            return strtolower($matches[1]).'/'.strtolower(preg_replace('/\.git$/', '', $matches[2]));
        }

        if (preg_match('#^([^/\s]+)/([^/\s]+)$#', $input, $matches)) {
            return strtolower($matches[1]).'/'.strtolower(preg_replace('/\.git$/', '', $matches[2]));
        }

        throw new RuntimeException('Format repo tidak valid. Gunakan owner/repo atau URL GitHub.');
    }

    /**
     * Inspeksi repo: branch, deteksi loader.lua, status PAT.
     *
     * @return array{
     *   ok: bool,
     *   message?: string,
     *   repo?: string,
     *   default_branch?: string,
     *   branches?: list<string>,
     *   loaders?: list<array{path: string, priority: int}>,
     *   recommended_path?: string|null,
     *   module_prefix?: string|null,
     *   pat?: array{type: string, label: string, recommendation: string, configured: bool}
     * }
     */
    public function inspectRepository(string $repoInput, ?string $branch = null): array
    {
        $patInfo = $this->patInfo();
        $patInfo['configured'] = $this->patConfigured();

        if (! $this->patConfigured()) {
            return [
                'ok' => false,
                'message' => 'GITHUB_PAT belum diset di .env',
                'pat' => $patInfo,
            ];
        }

        try {
            $repo = $this->normalizeRepo($repoInput);
        } catch (RuntimeException $e) {
            return [
                'ok' => false,
                'message' => $e->getMessage(),
                'pat' => $patInfo,
            ];
        }

        try {
            $repoResponse = $this->githubGet("/repos/{$repo}");
        } catch (ConnectionException $e) {
            return [
                'ok' => false,
                'message' => 'Tidak bisa terhubung ke GitHub API. Periksa koneksi internet atau sertifikat SSL server.',
                'repo' => $repo,
                'pat' => $patInfo,
            ];
        }

        if ($repoResponse->failed()) {
            return [
                'ok' => false,
                'message' => $this->formatGithubError($repoResponse, $repo),
                'repo' => $repo,
                'pat' => $patInfo,
            ];
        }

        $repoData = $repoResponse->json();
        $defaultBranch = (string) ($repoData['default_branch'] ?? 'main');
        $activeBranch = $branch ?: $defaultBranch;

        $branches = $this->listBranches($repo);
        if ($branches === []) {
            $branches = [$defaultBranch];
        }

        if (! in_array($activeBranch, $branches, true)) {
            $branches[] = $activeBranch;
        }

        $loaders = $this->detectLoaderPaths($repo, $activeBranch);
        $recommended = $loaders[0]['path'] ?? null;

        return [
            'ok' => true,
            'repo' => $repo,
            'default_branch' => $defaultBranch,
            'branches' => $branches,
            'loaders' => $loaders,
            'recommended_path' => $recommended,
            'module_prefix' => $recommended ? self::modulePathPrefixFromLoaderPath($recommended) : null,
            'pat' => $patInfo,
        ];
    }

    /**
     * @return list<string>
     */
    public function listBranches(string $repo): array
    {
        $response = $this->githubGet("/repos/{$repo}/branches", ['per_page' => 100]);

        if ($response->failed()) {
            return [];
        }

        $branches = collect($response->json())
            ->pluck('name')
            ->filter()
            ->values()
            ->all();

        return is_array($branches) ? $branches : [];
    }

    /**
     * @return list<array{path: string, priority: int}>
     */
    public function detectLoaderPaths(string $repo, string $branch): array
    {
        $found = [];

        foreach (self::COMMON_LOADER_PATHS as $index => $path) {
            if ($this->fileExists($repo, $branch, $path)) {
                $found[] = ['path' => $path, 'priority' => $index];
            }
        }

        if ($found !== []) {
            return $found;
        }

        return $this->scanTreeForLoaders($repo, $branch);
    }

    /**
     * @return list<array{path: string, priority: int}>
     */
    private function scanTreeForLoaders(string $repo, string $branch): array
    {
        $response = $this->githubGet("/repos/{$repo}/git/trees/{$branch}", ['recursive' => '1']);

        if ($response->failed()) {
            return [];
        }

        $tree = $response->json('tree') ?? [];
        $loaders = [];

        foreach ($tree as $item) {
            if (($item['type'] ?? '') !== 'blob') {
                continue;
            }

            $path = (string) ($item['path'] ?? '');
            if (! preg_match('#(^|/)loader\.lua$#i', $path)) {
                continue;
            }

            $loaders[] = [
                'path' => $path,
                'priority' => 100 + substr_count($path, '/'),
            ];
        }

        usort($loaders, fn (array $a, array $b) => $a['priority'] <=> $b['priority']);

        return array_slice($loaders, 0, 20);
    }

    public function fileExists(string $repo, string $branch, string $path): bool
    {
        try {
            $path = ltrim(str_replace('\\', '/', $path), '/');
            $response = $this->githubGet("/repos/{$repo}/contents/{$path}", ['ref' => $branch]);

            return $response->successful() && ! empty($response->json('content'));
        } catch (ConnectionException) {
            return false;
        }
    }

    public function fetchFileContent(string $repo, string $branch, string $path): string
    {
        $path = ltrim(str_replace('\\', '/', $path), '/');
        $cacheKey = "github_script:{$repo}:{$branch}:{$path}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($repo, $branch, $path) {
            $response = $this->githubGet("/repos/{$repo}/contents/{$path}", ['ref' => $branch]);

            if ($response->failed()) {
                throw new RuntimeException(
                    "Gagal fetch dari GitHub ({$repo}/{$path}): HTTP {$response->status()}"
                );
            }

            $content = $response->json('content');

            if (empty($content)) {
                throw new RuntimeException("File kosong di GitHub: {$path}");
            }

            return base64_decode(str_replace("\n", '', (string) $content));
        });
    }

    public function fetchModuleContent(
        string $repo,
        string $branch,
        string $pathPrefix,
        string $relativePath
    ): string {
        $relativePath = str_replace('\\', '/', $relativePath);
        $relativePath = ltrim($relativePath, '/');

        if (! str_ends_with(strtolower($relativePath), '.lua') && ! str_ends_with(strtolower($relativePath), '.luau')) {
            if ($this->fileExists($repo, $branch, $this->joinGithubPath($pathPrefix, $relativePath.'.lua'))) {
                $relativePath .= '.lua';
            } elseif ($this->fileExists($repo, $branch, $this->joinGithubPath($pathPrefix, $relativePath.'.luau'))) {
                $relativePath .= '.luau';
            } else {
                $relativePath .= '.lua';
            }
        }

        $fullPath = $this->joinGithubPath($pathPrefix, $relativePath);

        return $this->fetchFileContent($repo, $branch, $fullPath);
    }

    public function invalidateCache(string $repo, string $branch, string $path): void
    {
        $path = ltrim(str_replace('\\', '/', $path), '/');
        Cache::forget("github_script:{$repo}:{$branch}:{$path}");
    }

    public function invalidateProductCaches(string $repo, string $branch, string $loaderPath, string $pathPrefix): void
    {
        $this->invalidateCache($repo, $branch, $loaderPath);
    }

    /**
     * Pull loader + modul dari private repo ke storage lokal.
     * Inject/serve selanjutnya pakai folder lokal (lebih stabil dari fetch API tiap request).
     *
     * @return array{ok: bool, folder: string, files: int, message: string}
     */
    public function syncProductToLocal(Product $product): array
    {
        if (! $product->usesGithubScript()) {
            throw new RuntimeException('Produk bukan sumber GitHub.');
        }

        if (! $this->patConfigured()) {
            throw new RuntimeException('GITHUB_PAT belum diset di .env');
        }

        $repo = (string) $product->github_repo;
        $branch = $product->github_branch ?? 'main';
        $loaderPath = ltrim((string) $product->github_path, '/');
        $prefix = self::modulePathPrefixFromLoaderPath($loaderPath);
        $syncFolder = 'github-'.Str::slug($product->slug);
        $basePath = storage_path('app/private/scripts/'.$syncFolder);

        $paths = $this->listLuaFilesForSync($repo, $branch, $prefix, $loaderPath);

        if ($paths === []) {
            throw new RuntimeException('Tidak ada file .lua ditemukan di repo untuk di-sync.');
        }

        if (is_dir($basePath)) {
            $this->deleteDirectory($basePath);
        }
        mkdir($basePath, 0755, true);

        $count = 0;
        foreach ($paths as $githubPath) {
            $content = $this->fetchFileContent($repo, $branch, $githubPath);
            $relative = $this->relativePathForSync($githubPath, $prefix, $loaderPath);
            $localPath = $basePath.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relative);
            $dir = dirname($localPath);
            if (! is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            file_put_contents($localPath, $content);
            $count++;
        }

        if (! file_exists($basePath.DIRECTORY_SEPARATOR.'loader.lua')) {
            throw new RuntimeException('Sync gagal: loader.lua tidak ada di folder lokal hasil sync.');
        }

        return [
            'ok' => true,
            'folder' => $syncFolder,
            'files' => $count,
            'message' => "{$count} file Lua di-sync ke {$syncFolder}/",
        ];
    }

    /**
     * @return list<string>
     */
    private function listLuaFilesForSync(string $repo, string $branch, string $prefix, string $loaderPath): array
    {
        $response = $this->githubGet("/repos/{$repo}/git/trees/{$branch}", ['recursive' => '1']);

        if ($response->failed()) {
            throw new RuntimeException('Gagal membaca struktur repo GitHub (tree API).');
        }

        $paths = [];
        foreach ($response->json('tree') ?? [] as $item) {
            if (($item['type'] ?? '') !== 'blob') {
                continue;
            }

            $path = (string) ($item['path'] ?? '');
            if (! preg_match('/\.luau?$/i', $path)) {
                continue;
            }

            if ($prefix === '') {
                if ($path === $loaderPath || ! str_contains($path, '/')) {
                    $paths[] = $path;
                }

                continue;
            }

            if ($path === $loaderPath || str_starts_with($path, $prefix.'/')) {
                $paths[] = $path;
            }
        }

        if ($paths === [] && $this->fileExists($repo, $branch, $loaderPath)) {
            $paths[] = $loaderPath;
        }

        sort($paths);

        return array_values(array_unique($paths));
    }

    private function relativePathForSync(string $githubPath, string $prefix, string $loaderPath): string
    {
        if ($prefix === '') {
            return $githubPath;
        }

        if (str_starts_with($githubPath, $prefix.'/')) {
            return substr($githubPath, strlen($prefix) + 1);
        }

        return basename($loaderPath);
    }

    private function deleteDirectory(string $dir): void
    {
        if (! is_dir($dir)) {
            return;
        }

        foreach (scandir($dir) as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $dir.DIRECTORY_SEPARATOR.$item;
            if (is_dir($path)) {
                $this->deleteDirectory($path);
            } else {
                unlink($path);
            }
        }

        rmdir($dir);
    }

    public static function modulePathPrefixFromLoaderPath(?string $loaderPath): string
    {
        $path = str_replace('\\', '/', ltrim($loaderPath ?? '', '/'));
        $dir = dirname($path);

        return $dir === '.' ? '' : $dir;
    }

    private function joinGithubPath(string $prefix, string $relative): string
    {
        $prefix = trim(str_replace('\\', '/', $prefix), '/');
        $relative = trim(str_replace('\\', '/', $relative), '/');

        if ($prefix === '') {
            return $relative;
        }

        return $prefix.'/'.$relative;
    }

    private function githubGet(string $uri, array $query = []): Response
    {
        $pat = config('services.github.pat');

        $client = Http::withToken($pat)
            ->accept('application/vnd.github+json')
            ->withHeaders(['X-GitHub-Api-Version' => '2022-11-28'])
            ->timeout(20);

        if (! config('services.github.verify_ssl', true)) {
            $client = $client->withoutVerifying();
        }

        return $client->get('https://api.github.com'.$uri, $query);
    }

    private function formatGithubError(Response $response, string $repo): string
    {
        $message = $response->json('message') ?? 'Akses ditolak';

        if ($response->status() === 404) {
            return "Repo \"{$repo}\" tidak ditemukan atau PAT tidak punya akses. Pastikan token Classic (scope repo) atau Fine-grained (Contents: Read).";
        }

        if ($response->status() === 401) {
            return 'GITHUB_PAT tidak valid atau sudah expired. Buat token baru di GitHub Settings → Developer settings.';
        }

        return "GitHub API error: {$message} (HTTP {$response->status()})";
    }
}
