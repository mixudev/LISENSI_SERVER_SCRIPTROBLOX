<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class RobloxAuthController extends Controller
{
    public function connect(Request $request)
    {
        $discordId = $request->query('discord_id');
        $signature = $request->query('signature');

        if (!$discordId || !$signature) {
            return view('front.roblox-connect', [
                'error' => 'Parameter tidak lengkap. Harap akses melalui tombol di Discord bot.',
            ]);
        }

        // Verifikasi signature
        $expectedSignature = hash_hmac('sha256', $discordId, config('app.key'));
        if (!hash_equals($expectedSignature, $signature)) {
            return view('front.roblox-connect', [
                'error' => 'Tanda tangan digital tidak valid atau sudah kadaluarsa.',
            ]);
        }

        // Simpan discord_id ke session
        session(['link_discord_id' => $discordId]);

        // Cek jika Roblox OAuth terkonfigurasi
        $robloxClientId = config('services.roblox.client_id');
        if ($robloxClientId) {
            // Redirect ke Roblox OAuth 2.0
            $redirectUri = config('services.roblox.redirect_uri') ?? url('/roblox/callback');
            $authUrl = 'https://apis.roblox.com/oauth/v1/authorize?' . http_build_query([
                'client_id' => $robloxClientId,
                'redirect_uri' => $redirectUri,
                'response_type' => 'code',
                'scope' => 'openid profile',
                'state' => csrf_token(),
            ]);

            return redirect($authUrl);
        }

        // Fallback: Tampilkan halaman untuk manual input username
        return view('front.roblox-connect', [
            'discord_id' => $discordId,
            'roblox_username' => null,
            'success' => false,
        ]);
    }

    public function manualSubmit(Request $request)
    {
        $discordId = session('link_discord_id');
        if (!$discordId) {
            return view('front.roblox-connect', [
                'error' => 'Sesi habis atau tidak valid. Silakan klik tombol di Discord lagi.',
            ]);
        }

        $validated = $request->validate([
            'roblox_username' => ['required', 'string', 'min:3', 'max:30', 'regex:/^[a-zA-Z0-9_]+$/'],
        ]);

        $robloxUsername = $validated['roblox_username'];

        try {
            // Cek keunikan
            $exists = User::where('roblox_username', $robloxUsername)
                ->where('discord_id', '!=', $discordId)
                ->exists();

            if ($exists) {
                throw new RuntimeException('Username Roblox ini sudah dikaitkan ke akun Discord lain.');
            }

            // Validasi ke Roblox API (cek apakah user itu ada)
            $response = Http::get("https://users.roblox.com/v1/users/search", [
                'keyword' => $robloxUsername,
                'limit' => 1,
            ]);

            if (!$response->successful() || empty($response->json('data'))) {
                throw new RuntimeException('Username Roblox tidak ditemukan.');
            }

            // Dapatkan username yang terdaftar resmi di Roblox
            $realRobloxUsername = $response->json('data.0.name');

            // Kaitkan
            $user = User::where('discord_id', $discordId)->first();
            if (!$user) {
                // Jika user belum ada di db Laravel, buat baru
                $user = User::create([
                    'name' => 'Discord User ' . $discordId,
                    'email' => $discordId . '@discord.user',
                    'password' => bcrypt(\Illuminate\Support\Str::random(16)),
                    'discord_id' => $discordId,
                ]);
            }

            $user->roblox_username = $realRobloxUsername;
            $user->save();

            return view('front.roblox-connect', [
                'roblox_username' => $realRobloxUsername,
                'success' => true,
            ]);

        } catch (RuntimeException $e) {
            return view('front.roblox-connect', [
                'discord_id' => $discordId,
                'error' => $e->getMessage(),
                'success' => false,
            ]);
        }
    }

    public function callback(Request $request)
    {
        $code = $request->query('code');
        $state = $request->query('state');
        $discordId = session('link_discord_id');

        if (!$discordId) {
            return view('front.roblox-connect', [
                'error' => 'Sesi habis atau tidak valid. Silakan klik tombol di Discord lagi.',
            ]);
        }

        if (!$code) {
            return view('front.roblox-connect', [
                'error' => 'Otorisasi Roblox dibatalkan atau gagal.',
            ]);
        }

        try {
            $clientId = config('services.roblox.client_id');
            $clientSecret = config('services.roblox.client_secret');
            $redirectUri = config('services.roblox.redirect_uri') ?? url('/roblox/callback');

            // Tukar code dengan token
            $response = Http::asForm()->post('https://apis.roblox.com/oauth/v1/token', [
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'code' => $code,
                'grant_type' => 'authorization_code',
                'redirect_uri' => $redirectUri,
            ]);

            if (!$response->successful()) {
                Log::error('Roblox Token Exchange Failed', ['response' => $response->json()]);
                throw new RuntimeException('Gagal mendapatkan token dari Roblox.');
            }

            $accessToken = $response->json('access_token');

            // Ambil profile info
            $profileResponse = Http::withToken($accessToken)
                ->get('https://apis.roblox.com/oauth/v1/userinfo');

            if (!$profileResponse->successful()) {
                throw new RuntimeException('Gagal mengambil informasi profil Roblox.');
            }

            $robloxUsername = $profileResponse->json('preferred_username') 
                ?? $profileResponse->json('name');

            if (!$robloxUsername) {
                throw new RuntimeException('Gagal mendeteksi username Roblox.');
            }

            // Cek keunikan
            $exists = User::where('roblox_username', $robloxUsername)
                ->where('discord_id', '!=', $discordId)
                ->exists();

            if ($exists) {
                throw new RuntimeException('Username Roblox ini sudah dikaitkan ke akun Discord lain.');
            }

            // Kaitkan ke User
            $user = User::where('discord_id', $discordId)->first();
            if (!$user) {
                $user = User::create([
                    'name' => 'Discord User ' . $discordId,
                    'email' => $discordId . '@discord.user',
                    'password' => bcrypt(encrypt($discordId)),
                    'discord_id' => $discordId,
                ]);
            }

            $user->roblox_username = $robloxUsername;
            $user->save();

            return view('front.roblox-connect', [
                'roblox_username' => $robloxUsername,
                'success' => true,
            ]);

        } catch (RuntimeException $e) {
            return view('front.roblox-connect', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
