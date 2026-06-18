<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiLog;
use App\Models\License;
use App\Models\LicenseActivity;
use App\Models\User;
use App\Repositories\LicenseRepository;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private readonly LicenseRepository $licenses) {}

    public function index(): View
    {
        $stats = [
            'active_licenses'   => License::where('status', 'active')->count(),
            'total_users'       => User::where('role', 'user')->count(),
            'requests_today'    => ApiLog::whereDate('created_at', today())->count(),
            'expiring_soon'     => License::where('status', 'active')
                ->whereNotNull('expired_at')
                ->whereBetween('expired_at', [now(), now()->addDays(7)])
                ->count(),
            // Pengguna Roblox aktif = lisensi yang di-inject dalam 10 menit terakhir
            'roblox_active'     => License::where('status', 'active')
                ->whereNotNull('roblox_username')
                ->where('last_used_at', '>=', now()->subMinutes(10))
                ->count(),
        ];

        $licenseStatusCounts = License::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $recentActivities = LicenseActivity::with(['user', 'license'])
            ->latest()
            ->limit(10)
            ->get();

        $expiringSoon = $this->licenses->getExpiringSoon(7)->take(5);

        // Lisensi yang sedang aktif di Roblox (dalam 10 menit terakhir)
        $robloxActiveSessions = License::with(['user', 'product'])
            ->where('status', 'active')
            ->whereNotNull('roblox_username')
            ->where('last_used_at', '>=', now()->subMinutes(10))
            ->orderByDesc('last_used_at')
            ->limit(10)
            ->get();

        return view('dashboard.admin.index', compact(
            'stats',
            'licenseStatusCounts',
            'recentActivities',
            'expiringSoon',
            'robloxActiveSessions'
        ));
    }
}
