<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Repositories\LicenseRepository;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private readonly LicenseRepository $licenses) {}

    public function index(): View
    {
        $user = auth()->user();
        $licenses = $this->licenses->getByUser($user->id);

        $stats = [
            'active' => $licenses->where('status', 'active')->filter(fn ($l) => ! $l->isExpired())->count(),
            'expired' => $licenses->filter(fn ($l) => $l->isExpired() || $l->status === 'expired')->count(),
            'expiring_soon' => $licenses->where('status', 'active')
                ->filter(fn ($l) => $l->expired_at && $l->expired_at->between(now(), now()->addDays(7)))
                ->count(),
        ];

        $activeLicenses = $licenses
            ->filter(fn ($l) => $l->status === 'active' && ! $l->isExpired())
            ->take(3);

        $expiringLicenses = $licenses
            ->filter(fn ($l) => $l->status === 'active' && $l->expired_at?->between(now(), now()->addDays(7)));

        return view('dashboard.user.index', compact('stats', 'activeLicenses', 'expiringLicenses'));
    }
}
