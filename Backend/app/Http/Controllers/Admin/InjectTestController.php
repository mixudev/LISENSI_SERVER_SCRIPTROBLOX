<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\License;
use App\Models\ScriptToken;
use App\Services\LicenseService;
use App\Services\ScriptService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

/**
 * Tool debug untuk test alur inject dari browser — tanpa perlu Roblox/ngrok.
 * Hanya bisa diakses admin.
 */
class InjectTestController extends Controller
{
    public function __construct(
        private readonly LicenseService $licenseService,
        private readonly ScriptService $scriptService
    ) {}

    public function index(): View
    {
        $licenses = License::with('product')
            ->where('status', 'active')
            ->latest()
            ->limit(20)
            ->get();

        return view('dashboard.admin.inject-test.index', compact('licenses'));
    }

    /**
     * POST /admin/inject-test/run
     * Simulasi alur inject penuh — validasi key, buat token, baca script.
     * Return JSON dengan hasil setiap step.
     */
    public function run(Request $request): JsonResponse
    {
        $key = $request->input('key', '');
        $hwid = $request->input('hwid', 'TEST-HWID-BROWSER-DEBUG');
        $placeId = $request->input('place_id', '0');

        $steps = [];
        $failed = false;

        // ── Step 1: Validasi key format ────────────────────
        $steps[] = ['step' => 'format_check', 'status' => 'ok', 'detail' => "Key: {$key}"];

        if (! preg_match('/^LZD-[A-F0-9]{6}-[A-F0-9]{6}-[A-F0-9]{6}-[A-F0-9]{6}$/', $key)) {
            $steps[] = ['step' => 'format_check', 'status' => 'fail', 'detail' => 'Format key tidak valid. Harus LZD-XXXXXX-XXXXXX-XXXXXX-XXXXXX (huruf kapital)'];

            return response()->json(['steps' => $steps, 'ok' => false]);
        }

        // ── Step 2: Cari license di database ───────────────
        $license = License::where('license_key', $key)->with('product')->first();
        if (! $license) {
            $steps[] = ['step' => 'db_lookup', 'status' => 'fail', 'detail' => 'License key tidak ada di database.'];

            return response()->json(['steps' => $steps, 'ok' => false]);
        }
        $steps[] = ['step' => 'db_lookup', 'status' => 'ok',
            'detail' => "Ditemukan — status: {$license->status}, produk: {$license->product?->name}, "
                .'expired: '.($license->expired_at ? $license->expired_at->format('d M Y H:i') : 'Lifetime')];

        // ── Step 3: Cek status license ─────────────────────
        if (! in_array($license->status, ['active'])) {
            $steps[] = ['step' => 'status_check', 'status' => 'fail',
                'detail' => "Status tidak aktif: {$license->status}. Ban reason: {$license->ban_reason}"];

            return response()->json(['steps' => $steps, 'ok' => false]);
        }
        if ($license->isExpired()) {
            $steps[] = ['step' => 'status_check', 'status' => 'fail',
                'detail' => 'License expired pada '.$license->expired_at?->format('d M Y H:i')];

            return response()->json(['steps' => $steps, 'ok' => false]);
        }
        $steps[] = ['step' => 'status_check', 'status' => 'ok', 'detail' => 'License aktif dan belum expired'];

        // ── Step 4: Cek HWID ───────────────────────────────
        if ($license->hasHwid()) {
            if ($license->matchesHwid($hwid)) {
                $steps[] = ['step' => 'hwid_check', 'status' => 'ok',
                    'detail' => 'HWID cocok: '.substr($license->hwid, 0, 12).'...'];
            } else {
                $steps[] = ['step' => 'hwid_check', 'status' => 'warn',
                    'detail' => 'HWID MISMATCH — DB: '.substr($license->hwid, 0, 12).'... | Test HWID: '.substr($hwid, 0, 12)
                        .'... (di test ini kita skip mismatch karena ini simulasi)'];
            }
        } else {
            $steps[] = ['step' => 'hwid_check', 'status' => 'ok',
                'detail' => 'HWID belum terikat — akan di-bind saat inject pertama kali'];
        }

        // ── Step 5: Resolve script via produk aktif ──────────────────
        $resolved = $this->scriptService->resolveForLicense(
            $license->license_type ?? 'user',
            $placeId ?: null
        );
        $folder = $resolved['folder'] ?? 'none';
        $steps[] = ['step' => 'script_folder', 'status' => 'ok',
            'detail' => "Place ID: {$placeId} → produk: ".($resolved['product']?->name ?? 'tidak ada')
                ." → folder: {$folder} ("
                .ScriptService::getMapNameFromPlaceId($placeId ?: null).')'];

        // ── Step 6: Baca script ────────────────────────────
        try {
            if ($resolved['product'] === null && $resolved['folder'] === null) {
                throw new \RuntimeException('Tidak ada produk aktif yang cocok untuk lisensi ini.');
            }

            $scriptContent = $this->scriptService->readScriptForResolved($resolved);
            $steps[] = ['step' => 'script_read', 'status' => 'ok',
                'detail' => 'Script berhasil dibaca — '.number_format(strlen($scriptContent)).' bytes, '
                    .substr_count($scriptContent, "\n").' baris'];
        } catch (Throwable $e) {
            $steps[] = ['step' => 'script_read', 'status' => 'fail',
                'detail' => 'Gagal baca script: '.$e->getMessage()];

            return response()->json(['steps' => $steps, 'ok' => false]);
        }

        // ── Step 7: Buat token ─────────────────────────────
        $token = ScriptToken::create([
            'token' => ScriptToken::generateToken(),
            'license_id' => $license->id,
            'product_id' => $resolved['product']?->id,
            'script_folder' => $folder,
            'script_source' => $resolved['source'],
            'expires_at' => now()->addSeconds(30),
        ]);

        $scriptUrl = rtrim(config('app.url'), '/').'/s/'.$token->token;
        $steps[] = ['step' => 'token_create', 'status' => 'ok',
            'detail' => "Token dibuat — valid 30 detik\nURL: {$scriptUrl}"];

        // ── Step 8: Verifikasi token bisa serve script ─────
        $verifyToken = ScriptToken::where('token', $token->token)
            ->where('used', false)
            ->where('expires_at', '>', now())
            ->first();

        if ($verifyToken) {
            $steps[] = ['step' => 'token_verify', 'status' => 'ok',
                'detail' => 'Token valid dan belum dipakai — siap digunakan executor'];
        } else {
            $steps[] = ['step' => 'token_verify', 'status' => 'fail', 'detail' => 'Token gagal diverifikasi'];
        }

        // ── Step 9: Loader.lua URL ─────────────────────────
        $loaderUrl = rtrim(config('app.url'), '/').'/Loader.lua';
        $getUrl = rtrim(config('app.url'), '/').'/api/license/get';

        $executorScript = "script_key = \"{$key}\"\n"
            ."loadstring(game:HttpGet(\"{$loaderUrl}\"))()";

        $steps[] = ['step' => 'executor_script', 'status' => 'ok',
            'detail' => "URL Loader.lua: {$loaderUrl}\n\n{$executorScript}"];

        return response()->json([
            'ok' => true,
            'steps' => $steps,
            'script_url' => $scriptUrl,
            'loader_url' => $loaderUrl,
            'get_url' => $getUrl,
            'summary' => [
                'key' => $key,
                'product' => $license->product?->name,
                'folder' => $folder,
                'script_bytes' => strlen($scriptContent),
                'token' => substr($token->token, 0, 12).'...',
            ],
        ]);
    }
}
