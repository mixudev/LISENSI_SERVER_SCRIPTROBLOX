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
        // Cari lisensi yang langsung terikat ke discord_id
        $license = License::query()->where('discord_id', $discordId)->latest('id')->first();
        if ($license) {
            return $license;
        }

        // Fallback: cari lewat relasi user
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

    public function resetHwidForDiscordUser(string $discordId, Request $request): array
    {
        $license = $this->getLicenseForDiscordUser($discordId)
            ?? throw new RuntimeException('Akun Discord ini belum memiliki lisensi.');

        if (! $license->hasHwid()) {
            // HWID belum terikat — tidak ada yang perlu direset, tapi tetap sukses
            return [
                'license' => $license,
                'already_clear' => true,
            ];
        }

        $this->hwidService->resetByUser($license, $request);

        return [
            'license' => $license->fresh(),
            'already_clear' => false,
        ];
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

        // Cek apakah discord user ini sudah punya lisensi
        if ($this->getLicenseForDiscordUser($targetDiscordId)) {
            throw new RuntimeException('Akun Discord ini sudah memiliki lisensi. Satu Discord ID hanya boleh satu key.');
        }

        $user = $this->findOrCreateDiscordUser($targetDiscordId, $targetDisplayName);
        $actorUser = $this->findUserByDiscordId($actorDiscordId);

        return $this->licenseService->generate([
            'user_id' => $user->id,
            'discord_id' => $targetDiscordId,
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

    public function redeemLicense(string $licenseKey, string $discordId, ?string $displayName = null): License
    {
        $license = License::where('license_key', $licenseKey)->first()
            ?? throw new RuntimeException('Lisensi tidak ditemukan.');

        if (in_array($license->status, ['banned', 'suspended'], true)) {
            throw new RuntimeException('Lisensi ini sedang diblokir atau ditangguhkan.');
        }

        if ($license->isExpired()) {
            throw new RuntimeException('Lisensi ini sudah kadaluarsa.');
        }

        // Cek jika sudah terikat ke Discord
        if ($license->discord_id) {
            if ($license->discord_id === $discordId) {
                return $license;
            }
            throw new RuntimeException('Lisensi ini sudah terikat ke akun Discord lain.');
        }

        // Cek jika Discord user ini sudah punya lisensi aktif
        $existing = License::query()
            ->where('discord_id', $discordId)
            ->where('status', 'active')
            ->first();

        if ($existing) {
            throw new RuntimeException('Akun Discord ini sudah memiliki lisensi aktif.');
        }

        $user = $this->findOrCreateDiscordUser($discordId, $displayName);

        if (!$license->user_id) {
            $license->user_id = $user->id;
        }
        $license->discord_id = $discordId;

        $license->save();

        return $license->fresh();
    }

    public function linkRobloxAccount(string $discordId, ?string $robloxUsername): User
    {
        $user = $this->findOrCreateDiscordUser($discordId);

        if (!empty($robloxUsername)) {
            // Cek keunikan bind
            $exists = User::where('roblox_username', $robloxUsername)
                ->where('discord_id', '!=', $discordId)
                ->exists();
            if ($exists) {
                throw new RuntimeException('Username Roblox ini sudah dikaitkan ke akun Discord lain.');
            }
            $user->roblox_username = $robloxUsername;
        } else {
            $user->roblox_username = null;
        }

        $user->save();

        return $user;
    }

    /**
     * Daftar semua user dengan lisensi aktif.
     * @return array<int, array<string, mixed>>
     */
    public function listActiveUsers(): array
    {
        return License::query()
            ->where('status', 'active')
            ->whereNotNull('discord_id')
            ->with('user')
            ->orderByDesc('created_at')
            ->limit(25)
            ->get()
            ->map(fn (License $l) => [
                'discord_id'   => $l->discord_id,
                'key'          => $l->license_key,
                'license_type' => $l->license_type,
                'expires_at'   => $l->expired_at?->format('d M Y') ?? 'Lifetime',
                'hwid_bound'   => (bool) $l->hwid,
                'username'     => $l->user?->name ?? '—',
            ])
            ->toArray();
    }

    /**
     * Cabut / revoke lisensi berdasarkan Discord ID target.
     */
    public function revokeLicense(string $targetDiscordId, string $actorDiscordId): License
    {
        if (! $this->actorCanGenerate($actorDiscordId)) {
            throw new RuntimeException('Anda tidak memiliki izin untuk mencabut lisensi.');
        }

        $license = License::where('discord_id', $targetDiscordId)->first()
            ?? throw new RuntimeException('Lisensi untuk Discord ID tersebut tidak ditemukan.');

        $license->status = 'suspended';
        $license->save();

        return $license->fresh();
    }

    /**
     * Statistik server: total lisensi per status.
     * @return array<string, int>
     */
    public function serverStats(): array
    {
        $counts = License::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        return [
            'active'    => (int) ($counts['active']    ?? 0),
            'expired'   => (int) ($counts['expired']   ?? 0),
            'suspended' => (int) ($counts['suspended'] ?? 0),
            'banned'    => (int) ($counts['banned']    ?? 0),
            'total'     => array_sum($counts),
        ];
    }
}
