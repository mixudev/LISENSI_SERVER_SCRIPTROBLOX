<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModuleAccessToken extends Model
{
    public const UPDATED_AT = null;

    /** TTL sesi modul dalam detik (6 jam). */
    public const SESSION_TTL_SECONDS = 21600;

    protected $fillable = [
        'token',
        'license_id',
        'script_folder',
        'script_source',
        'github_repo',
        'github_branch',
        'github_path_prefix',
        'expires_at',
        'last_used_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'last_used_at' => 'datetime',
        ];
    }

    public function license(): BelongsTo
    {
        return $this->belongsTo(License::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public static function generateToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    public static function issue(License $license, string $scriptFolder): self
    {
        return self::issueFromResolved($license, [
            'folder' => $scriptFolder,
            'source' => 'local',
            'product' => null,
        ]);
    }

    /**
     * @param  array{product: ?\App\Models\Product, folder: ?string, source: ?string}  $resolved
     */
    public static function issueFromResolved(License $license, array $resolved): self
    {
        $product = $resolved['product'] ?? null;
        $source = ($resolved['source'] ?? 'local') === 'github' ? 'github' : 'local';

        $data = [
            'token' => self::generateToken(),
            'license_id' => $license->id,
            'script_folder' => $resolved['folder'] ?? 'universal',
            'script_source' => $source,
            'expires_at' => now()->addSeconds(self::SESSION_TTL_SECONDS),
        ];

        if ($source === 'github' && $product) {
            $data['github_repo'] = $product->github_repo;
            $data['github_branch'] = $product->github_branch ?? 'main';
            $data['github_path_prefix'] = $product->getGithubModulePrefix();
        }

        return self::create($data);
    }

    public function usesGithubScript(): bool
    {
        return $this->script_source === 'github';
    }
}
