<?php

namespace App\Services;

use App\Models\License;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use RuntimeException;

class DiscordBotService
{
    public function __construct(
        private readonly LicenseService $licenseService,
        private readonly HwidService $hwidService,
    ) {}

    public function findUserByDiscordId(string $discordId): ?User
    {
        return User::query()->where('discord_id', $discordId)->first();
    }

    public function findOrCreateDiscordUser(string $discordId, ?string $displayName = null): User
    {
        $existing = $this->findUserByDiscordId($discordId);

        if ($existing) {
            return $existing;
        }

        return User::create([
            'discord_id' => $discordId,
            'name' => $displayName ?: 'Discord User',
            'email' => 'discord_'.$discordId.'@discord.local',
            'password' => Hash::make(Str::random(40)),
            'role' => 'user',
            'is_active' => true,
        ]);
    }

    public function getLicenseForDiscordUser(string $discordId): ?License
    {
        $user = $this->findUserByDiscordId($discordId);

        if (! $user) {
            return null;
        }

        return $user->licenses()->latest('id')->first();
    }

    /**
     * @return array<string, mixed>
     */
    public function licenseStats(string $discordId): array
    {
        $license = $this->getLicenseForDiscordUser($discordId);

        if (! $license) {
            throw new RuntimeException('Akun Discord ini belum memiliki lisensi.');
        }

        return $this->formatLicensePayload($license);
    }

    public function resetHwidForDiscordUser(string $discordId, Request $request): License
    {
        $license = $this->getLicenseForDiscordUser($discordId)
            ?? throw new RuntimeException('Akun Discord ini belum memiliki lisensi.');

        if (! $license->hasHwid()) {
            throw new RuntimeException('HWID belum terikat — tidak ada yang perlu direset.');
        }

        $this->hwidService->resetByUser($license, $request);

        return $license->fresh();
    }

    public function generateLicenseForDiscordUser(
        string $targetDiscordId,
        string $actorDiscordId,
        int $durationDays,
        string $licenseType = 'user',
        ?string $targetDisplayName = null,
    ): License {
        if (! $this->actorCanGenerate($actorDiscordId)) {
            throw new RuntimeException('Discord ID admin tidak diizinkan melakukan generate key.');
        }

        $user = $this->findOrCreateDiscordUser($targetDiscordId, $targetDisplayName);

        if ($user->licenses()->exists()) {
            throw new RuntimeException('Akun Discord ini sudah memiliki lisensi. Satu Discord ID hanya boleh satu key.');
        }

        $actorUser = $this->findUserByDiscordId($actorDiscordId);

        return $this->licenseService->generate([
            'user_id' => $user->id,
            'license_type' => $licenseType,
            'duration_days' => $durationDays,
            'notes' => 'Generated via Discord bot',
            'created_by' => $actorUser?->id,
        ]);
    }

    public function buildLoaderScript(License $license): string
    {
        $loaderUrl = rtrim(config('app.url'), '/').'/Loader.lua';
        $key = $license->license_key;

        return <<<LUA
-- LimeHub Loader
script_key = "{$key}"
loadstring(game:HttpGet("{$loaderUrl}"))()
LUA;
    }

    public function actorCanGenerate(string $actorDiscordId): bool
    {
        $allowedIds = config('services.discord_bot.admin_discord_ids', []);

        if (in_array($actorDiscordId, $allowedIds, true)) {
            return true;
        }

        return User::query()
            ->where('discord_id', $actorDiscordId)
            ->where('role', 'admin')
            ->where('is_active', true)
            ->exists();
    }

    /**
     * @return array<string, mixed>
     */
    private function formatLicensePayload(License $license): array
    {
        return [
            'key' => $license->license_key,
            'status' => $license->status,
            'license_type' => $license->license_type,
            'hwid' => $license->hwid,
            'hwid_reset_count' => $license->hwid_reset_count,
            'expires_at' => $license->expired_at?->toIso8601String(),
            'expires_at_human' => $license->expired_at?->format('d M Y H:i') ?? 'Lifetime',
            'activated_at' => $license->activated_at?->toIso8601String(),
            'last_used_at' => $license->last_used_at?->toIso8601String(),
            'roblox_username' => $license->roblox_username,
            'roblox_place_id' => $license->roblox_place_id,
        ];
    }
}
