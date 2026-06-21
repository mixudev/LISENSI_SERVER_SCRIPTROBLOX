<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReminderController extends Controller
{
    private function getFilePath()
    {
        return storage_path('bot_data/reminders.json');
    }

    public function index(): View
    {
        $filePath = $this->getFilePath();
        $reminders = [];

        if (file_exists($filePath)) {
            $json = file_get_contents($filePath);
            $reminders = json_decode($json, true) ?: [];
        }

        // Resolve user info from database based on Discord ID
        foreach ($reminders as &$reminder) {
            $user = User::where('discord_id', $reminder['userId'])->first();
            $reminder['user'] = $user ? [
                'name' => $user->name,
                'avatar' => $user->avatar,
                'email' => $user->email,
            ] : null;
        }

        return view('dashboard.admin.reminders.index', compact('reminders'));
    }

    public function destroy(string $id): RedirectResponse
    {
        $filePath = $this->getFilePath();

        if (file_exists($filePath)) {
            $json = file_get_contents($filePath);
            $reminders = json_decode($json, true) ?: [];

            $filtered = array_filter($reminders, function ($r) use ($id) {
                return $r['id'] !== $id;
            });

            file_put_contents($filePath, json_encode(array_values($filtered), JSON_PRETTY_PRINT));
            @chmod($filePath, 0666);
        }

        return redirect()->route('admin.reminders.index')->with('success', 'Pengingat berhasil dihapus.');
    }
}
