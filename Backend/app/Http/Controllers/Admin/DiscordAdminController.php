<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DiscordAdmin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class DiscordAdminController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->input('search');
        $query = DiscordAdmin::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('discord_id', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('note', 'like', "%{$search}%");
            });
        }

        $admins = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('dashboard.admin.discord-admins.index', compact('admins', 'search'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'discord_id' => ['required', 'string', 'regex:/^[0-9]+$/', 'unique:discord_admins,discord_id'],
            'username'   => ['nullable', 'string', 'max:100'],
            'avatar_url' => ['nullable', 'string', 'max:512'],
            'note'       => ['nullable', 'string', 'max:255'],
            'is_active'  => ['nullable', 'boolean'],
        ], [
            'discord_id.required' => 'Discord ID wajib diisi.',
            'discord_id.regex'    => 'Discord ID harus berupa angka.',
            'discord_id.unique'   => 'Discord ID ini sudah terdaftar sebagai admin.',
        ]);

        DiscordAdmin::create([
            'discord_id' => $validated['discord_id'],
            'username'   => $validated['username'] ?? null,
            'avatar_url' => $validated['avatar_url'] ?? null,
            'note'       => $validated['note'] ?? null,
            'is_active'  => $request->has('is_active') ? (bool) $request->input('is_active') : true,
        ]);

        return redirect()->route('admin.discord-admins.index')->with('success', 'Admin Discord berhasil ditambahkan.');
    }

    public function update(Request $request, DiscordAdmin $discordAdmin): RedirectResponse
    {
        $validated = $request->validate([
            'username'   => ['nullable', 'string', 'max:100'],
            'avatar_url' => ['nullable', 'string', 'max:512'],
            'note'       => ['nullable', 'string', 'max:255'],
            'is_active'  => ['nullable', 'boolean'],
        ]);

        $discordAdmin->update([
            'username'   => $validated['username'] ?? null,
            'avatar_url' => $validated['avatar_url'] ?? $discordAdmin->avatar_url,
            'note'       => $validated['note'] ?? null,
            'is_active'  => $request->has('is_active') ? (bool) $request->input('is_active') : false,
        ]);

        return redirect()->route('admin.discord-admins.index')->with('success', "Admin Discord {$discordAdmin->discord_id} berhasil diperbarui.");
    }

    public function lookupDiscord(Request $request)
    {
        $request->validate([
            'discord_id' => ['required', 'string', 'regex:/^[0-9]{16,20}$/'],
        ]);

        $discordId = $request->input('discord_id');
        $botToken  = env('DISCORD_BOT_TOKEN', '');

        if (empty($botToken)) {
            return response()->json(['status' => false, 'message' => 'DISCORD_BOT_TOKEN tidak terkonfigurasi.'], 500);
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bot {$botToken}",
            ])->timeout(5)->get("https://discord.com/api/v10/users/{$discordId}");

            if (!$response->successful()) {
                return response()->json(['status' => false, 'message' => 'User Discord tidak ditemukan atau ID tidak valid.'], 404);
            }

            $data      = $response->json();
            $avatarHash = $data['avatar'] ?? null;
            $avatarUrl  = $avatarHash
                ? "https://cdn.discordapp.com/avatars/{$discordId}/{$avatarHash}.png?size=128"
                : "https://cdn.discordapp.com/embed/avatars/" . (((int) $discordId >> 22) % 6) . ".png";

            return response()->json([
                'status' => true,
                'data'   => [
                    'username'    => $data['username'] ?? null,
                    'global_name' => $data['global_name'] ?? $data['username'] ?? null,
                    'avatar_url'  => $avatarUrl,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Gagal menghubungi Discord API.'], 500);
        }
    }

    public function toggleActive(DiscordAdmin $discordAdmin): RedirectResponse
    {
        $discordAdmin->update(['is_active' => !$discordAdmin->is_active]);
        $status = $discordAdmin->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Status admin Discord {$discordAdmin->discord_id} berhasil {$status}.");
    }

    public function destroy(DiscordAdmin $discordAdmin): RedirectResponse
    {
        $discordAdmin->delete();

        return redirect()->route('admin.discord-admins.index')->with('success', 'Admin Discord berhasil dihapus.');
    }
}
