<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\DiscordBotDiscordIdRequest;
use App\Http\Requests\Api\DiscordBotGenerateRequest;
use App\Services\DiscordBotService;
use Illuminate\Http\JsonResponse;
use RuntimeException;

class DiscordBotController extends Controller
{
    public function __construct(
        private readonly DiscordBotService $discordBot,
    ) {}

    public function health(): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => 'Discord bot API is online.',
            'data' => [
                'app' => config('app.name'),
                'time' => now()->toIso8601String(),
            ],
        ]);
    }

    public function stats(DiscordBotDiscordIdRequest $request): JsonResponse
    {
        try {
            $data = $this->discordBot->licenseStats($request->validated('discord_id'));
        } catch (RuntimeException $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'License stats retrieved.',
            'data' => $data,
        ]);
    }

    public function resetHwid(DiscordBotDiscordIdRequest $request): JsonResponse
    {
        try {
            $license = $this->discordBot->resetHwidForDiscordUser(
                $request->validated('discord_id'),
                $request,
            );
        } catch (RuntimeException $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'status' => true,
            'message' => 'HWID berhasil direset. Jalankan ulang loader di perangkat baru.',
            'data' => [
                'key' => $license->license_key,
                'hwid_reset_count' => $license->hwid_reset_count,
            ],
        ]);
    }

    public function generate(DiscordBotGenerateRequest $request): JsonResponse
    {
        try {
            $license = $this->discordBot->generateLicenseForDiscordUser(
                targetDiscordId: $request->validated('target_discord_id'),
                actorDiscordId: $request->validated('actor_discord_id'),
                durationDays: (int) $request->validated('duration_days'),
                licenseType: $request->validated('license_type') ?? 'user',
            );
        } catch (RuntimeException $e) {
            $status = str_contains($e->getMessage(), 'sudah memiliki') ? 409 : 403;

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], $status);
        }

        return response()->json([
            'status' => true,
            'message' => 'License key berhasil dibuat.',
            'data' => [
                'key' => $license->license_key,
                'target_discord_id' => $request->validated('target_discord_id'),
                'expires_at' => $license->expired_at?->toIso8601String(),
                'expires_at_human' => $license->expired_at?->format('d M Y H:i') ?? 'Lifetime',
            ],
        ], 201);
    }

    public function scriptTemplate(DiscordBotDiscordIdRequest $request): JsonResponse
    {
        $license = $this->discordBot->getLicenseForDiscordUser($request->validated('discord_id'));

        if (! $license) {
            return response()->json([
                'status' => false,
                'message' => 'Akun Discord ini belum memiliki lisensi. Minta admin generate key terlebih dahulu.',
            ], 404);
        }

        $loaderUrl = rtrim(config('app.url'), '/').'/Loader.lua';

        return response()->json([
            'status' => true,
            'message' => 'Script loader template siap.',
            'data' => [
                'key' => $license->license_key,
                'loader_url' => $loaderUrl,
                'script' => $this->discordBot->buildLoaderScript($license),
            ],
        ]);
    }
}
