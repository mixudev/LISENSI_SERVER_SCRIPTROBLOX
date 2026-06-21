<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlaylistController extends Controller
{
    private function getFilePath()
    {
        return storage_path('bot_data/playlists.json');
    }

    public function index(): View
    {
        $filePath = $this->getFilePath();
        $playlists = [];

        if (file_exists($filePath)) {
            $json = file_get_contents($filePath);
            $rawPlaylists = json_decode($json, true) ?: [];

            foreach ($rawPlaylists as $key => $tracks) {
                $parts = explode('_', $key);
                $userId = $parts[0] ?? null;
                $guildId = $parts[1] ?? null;

                $user = User::where('discord_id', $userId)->first();

                $playlists[] = [
                    'key' => $key,
                    'userId' => $userId,
                    'guildId' => $guildId,
                    'user' => $user ? [
                        'name' => $user->name,
                        'avatar' => $user->avatar,
                        'email' => $user->email,
                    ] : null,
                    'tracks' => $tracks,
                ];
            }
        }

        return view('dashboard.admin.playlists.index', compact('playlists'));
    }

    public function destroyTrack(string $key, int $index): RedirectResponse
    {
        $filePath = $this->getFilePath();

        if (file_exists($filePath)) {
            $json = file_get_contents($filePath);
            $rawPlaylists = json_decode($json, true) ?: [];

            if (isset($rawPlaylists[$key][$index])) {
                $trackTitle = $rawPlaylists[$key][$index]['title'];
                array_splice($rawPlaylists[$key], $index, 1);
                file_put_contents($filePath, json_encode($rawPlaylists, JSON_PRETTY_PRINT));
                @chmod($filePath, 0666);
                return back()->with('success', "Lagu \"{$trackTitle}\" berhasil dihapus dari playlist.");
            }
        }

        return back()->with('error', 'Lagu tidak ditemukan.');
    }

    public function destroyPlaylist(string $key): RedirectResponse
    {
        $filePath = $this->getFilePath();

        if (file_exists($filePath)) {
            $json = file_get_contents($filePath);
            $rawPlaylists = json_decode($json, true) ?: [];

            if (isset($rawPlaylists[$key])) {
                unset($rawPlaylists[$key]);
                file_put_contents($filePath, json_encode($rawPlaylists, JSON_PRETTY_PRINT));
                @chmod($filePath, 0666);
                return back()->with('success', 'Seluruh playlist berhasil dihapus.');
            }
        }

        return back()->with('error', 'Playlist tidak ditemukan.');
    }
}
