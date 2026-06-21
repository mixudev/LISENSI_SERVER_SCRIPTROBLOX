<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\DiscordBotDiscordIdRequest;
use App\Http\Requests\Api\DiscordBotGenerateRequest;
use App\Http\Requests\Api\DiscordBotRedeemRequest;
use App\Http\Requests\Api\DiscordBotLinkRobloxRequest;
use App\Http\Requests\Api\DiscordBotRevokeRequest;
use App\Services\DiscordBotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
            $result = $this->discordBot->resetHwidForDiscordUser(
                $request->validated('discord_id'),
                $request,
            );
        } catch (RuntimeException $e) {
            return response()->json([
                'status'  => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        $license      = $result['license'];
        $alreadyClear = $result['already_clear'];

        $message = $alreadyClear
            ? 'HWID Anda belum terikat ke perangkat manapun — tidak ada yang perlu direset.'
            : 'HWID berhasil direset. Jalankan ulang loader di perangkat baru.';

        return response()->json([
            'status'  => true,
            'message' => $message,
            'data'    => [
                'key'              => $license->license_key,
                'hwid_reset_count' => $license->hwid_reset_count,
                'already_clear'    => $alreadyClear,
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

    public function redeem(DiscordBotRedeemRequest $request): JsonResponse
    {
        try {
            $license = $this->discordBot->redeemLicense(
                licenseKey: $request->validated('license_key'),
                discordId: $request->validated('discord_id'),
                displayName: $request->validated('display_name')
            );
        } catch (RuntimeException $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'status' => true,
            'message' => 'Lisensi berhasil terikat ke Discord.',
            'data' => [
                'key' => $license->license_key,
                'discord_id' => $license->discord_id,
            ],
        ]);
    }

    public function linkRoblox(DiscordBotLinkRobloxRequest $request): JsonResponse
    {
        try {
            $user = $this->discordBot->linkRobloxAccount(
                discordId: $request->validated('discord_id'),
                robloxUsername: $request->validated('roblox_username')
            );
        } catch (RuntimeException $e) {
            return response()->json([
                'status'  => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        $msg = $user->roblox_username 
            ? "Akun Roblox '{$user->roblox_username}' berhasil dikaitkan ke Discord." 
            : "Kaitan akun Roblox berhasil dihapus.";

        return response()->json([
            'status'  => true,
            'message' => $msg,
            'data'    => [
                'discord_id'      => $user->discord_id,
                'roblox_username' => $user->roblox_username,
            ],
        ]);
    }

    public function robloxConnectUrl(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'discord_id' => ['required', 'string', 'regex:/^\d{17,20}$/']
        ]);

        $discordId = $validated['discord_id'];
        $signature = hash_hmac('sha256', $discordId, config('app.key'));
        
        $connectUrl = rtrim(config('app.url'), '/') . '/roblox/connect?' . http_build_query([
            'discord_id' => $discordId,
            'signature' => $signature,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Roblox connection URL generated.',
            'data' => [
                'url' => $connectUrl,
            ],
        ]);
    }

    /** Daftar user dengan lisensi aktif — Admin only */
    public function listUsers(Request $request): JsonResponse
    {
        $users = $this->discordBot->listActiveUsers();

        return response()->json([
            'status'  => true,
            'message' => 'Daftar user aktif berhasil diambil.',
            'data'    => $users,
        ]);
    }

    /** Cabut / revoke lisensi berdasarkan Discord ID — Admin only */
    public function revokeKey(DiscordBotRevokeRequest $request): JsonResponse
    {
        try {
            $license = $this->discordBot->revokeLicense(
                targetDiscordId: $request->validated('target_discord_id'),
                actorDiscordId:  $request->validated('actor_discord_id'),
            );
        } catch (RuntimeException $e) {
            $status = str_contains($e->getMessage(), 'tidak ditemukan') ? 404 : 403;
            return response()->json([
                'status'  => false,
                'message' => $e->getMessage(),
            ], $status);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Lisensi berhasil dicabut.',
            'data'    => [
                'key'        => $license->license_key,
                'discord_id' => $license->discord_id,
                'status'     => $license->status,
            ],
        ]);
    }

    /** Statistik server — total lisensi per status */
    public function serverStats(): JsonResponse
    {
        $stats = $this->discordBot->serverStats();

        return response()->json([
            'status'  => true,
            'message' => 'Server stats berhasil diambil.',
            'data'    => $stats,
        ]);
    }

    /** List Discord Admin IDs yang aktif */
    public function listDiscordAdmins(): JsonResponse
    {
        $admins = \App\Models\DiscordAdmin::where('is_active', true)->pluck('discord_id')->toArray();
        
        return response()->json([
            'status'  => true,
            'message' => 'Active Discord admins retrieved.',
            'data'    => $admins,
        ]);
    }

    /** Chat dengan AI menggunakan fallback key */
    public function askAi(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'prompt'     => ['required', 'string', 'max:1000'],
            'discord_id' => ['required', 'string'],
        ]);

        $prompt = $validated['prompt'];

        $keys = \App\Models\AiKey::where('is_active', true)
            ->orderBy('priority', 'asc')
            ->orderBy('created_at', 'desc')
            ->get();

        if ($keys->isEmpty()) {
            return response()->json([
                'status'  => true,
                'message' => 'No active AI API keys configured.',
                'data'    => 'Maaf sob, admin belum setting API Key buat gw diajak ngobrol. Colek admin dulu ya!',
            ]);
        }

        $systemPrompt = "Kamu adalah WOLF, teman ngobrol yang santai, asyik, dan memakai bahasa gaul santai/bahasa Indonesia informal (lu-gue, bro, sob). Jawab seperlunya saja (singkat, padat, secukupnya), jangan terlalu panjang lebar. Selalu bersikap santai seperti teman dekat.";

        foreach ($keys as $key) {
            try {
                $response = null;

                if ($key->provider === 'groq') {
                    $response = \Illuminate\Support\Facades\Http::withHeaders([
                        'Authorization' => "Bearer {$key->api_key}",
                        'Content-Type'  => 'application/json',
                    ])->timeout(10)->post('https://api.groq.com/openai/v1/chat/completions', [
                        'model'    => $key->model,
                        'messages' => [
                            ['role' => 'system', 'content' => $systemPrompt],
                            ['role' => 'user', 'content' => $prompt],
                        ],
                    ]);

                    if ($response->successful()) {
                        $content = $response->json('choices.0.message.content');
                        if ($content) {
                            $key->increment('usage_count');
                            $key->update(['last_used_at' => now()]);

                            return response()->json([
                                'status'  => true,
                                'message' => 'Success',
                                'data'    => $content,
                            ]);
                        }
                    }
                } elseif ($key->provider === 'gemini') {
                    $response = \Illuminate\Support\Facades\Http::withHeaders([
                        'Content-Type' => 'application/json',
                    ])->timeout(10)->post("https://generativelanguage.googleapis.com/v1beta/models/{$key->model}:generateContent?key={$key->api_key}", [
                        'systemInstruction' => [
                            'parts' => [
                                ['text' => $systemPrompt]
                            ]
                        ],
                        'contents' => [
                            [
                                'role' => 'user',
                                'parts' => [
                                    ['text' => $prompt]
                                ]
                            ]
                        ]
                    ]);

                    if ($response->successful()) {
                        $content = $response->json('candidates.0.content.parts.0.text');
                        if ($content) {
                            $key->increment('usage_count');
                            $key->update(['last_used_at' => now()]);

                            return response()->json([
                                'status'  => true,
                                'message' => 'Success',
                                'data'    => $content,
                            ]);
                        }
                    }
                } elseif ($key->provider === 'openrouter') {
                    $response = \Illuminate\Support\Facades\Http::withHeaders([
                        'Authorization' => "Bearer {$key->api_key}",
                        'Content-Type'  => 'application/json',
                        'HTTP-Referer'  => config('app.url', 'http://localhost:8000'),
                        'X-Title'       => config('app.name', 'Script Lisensi'),
                    ])->timeout(30)->post('https://openrouter.ai/api/v1/chat/completions', [
                        'model'    => $key->model,
                        'messages' => [
                            ['role' => 'system', 'content' => $systemPrompt],
                            ['role' => 'user', 'content' => $prompt],
                        ],
                        'max_tokens' => 512,
                    ]);

                    if ($response->successful()) {
                        $content = $response->json('choices.0.message.content');
                        if ($content) {
                            $key->increment('usage_count');
                            $key->update(['last_used_at' => now()]);

                            return response()->json([
                                'status'  => true,
                                'message' => 'Success',
                                'data'    => $content,
                            ]);
                        }
                    }

                    \Illuminate\Support\Facades\Log::warning("OpenRouter failed [{$key->model}] HTTP {$response->status()}: " . $response->body());
                }

                \Illuminate\Support\Facades\Log::warning("AI Key failed for provider {$key->provider}: " . ($response ? $response->body() : 'No response'));
                $key->increment('error_count');

            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("AI Key exception for provider {$key->provider}: {$e->getMessage()}");
                $key->increment('error_count');
            }
        }

        return response()->json([
            'status'  => true,
            'message' => 'All AI keys failed or limited.',
            'data'    => 'Waduh sob, semua API Key AI gw lagi error/limit nih. Coba lagi nanti atau kabari admin ya!',
        ]);
    }
}
