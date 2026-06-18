<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\License;
use App\Models\LicenseActivity;
use App\Repositories\LicenseRepository;
use App\Services\HwidService;
use App\Services\ModuleAccessService;
use App\Services\ScriptService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class LicenseController extends Controller
{
    public function __construct(
        private readonly LicenseRepository $licenseRepository,
        private readonly HwidService $hwidService,
        private readonly ScriptService $scriptService,
        private readonly ModuleAccessService $moduleAccessService
    ) {}

    public function index(Request $request): View
    {
        $user = auth()->user();
        $licenses = $this->licenseRepository->getByUser($user->id);
        $filter = $request->input('filter', 'all');

        $filtered = match ($filter) {
            'active' => $licenses->filter(fn ($l) => $l->status === 'active' && ! $l->isExpired()),
            'expired' => $licenses->filter(fn ($l) => $l->isExpired() || $l->status === 'expired'),
            default => $licenses,
        };

        LicenseActivity::log(
            action: LicenseActivity::ACTION_VIEW_LICENSE,
            userId: $user->id,
            request: $request
        );

        return view('dashboard.user.licenses.index', compact('filtered', 'filter'));
    }

    public function resetHwid(License $license, Request $request): RedirectResponse
    {
        // Pastikan lisensi milik user yang login
        abort_if($license->user_id !== auth()->id(), 403);

        try {
            $this->hwidService->resetByUser($license, $request);

            return back()->with('success', 'HWID berhasil direset. Perangkat baru dapat melakukan binding.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function download(License $license, Request $request): Response|RedirectResponse
    {
        abort_if($license->user_id !== auth()->id(), 403);
        abort_if(! $license->isActive(), 403, 'Lisensi tidak aktif.');

        $resolved = $this->scriptService->resolveForLicense(
            $license->license_type ?? 'user',
            $license->roblox_place_id
        );

        if ($resolved['product'] === null && $resolved['folder'] === null) {
            return back()->with('error', 'File script tidak tersedia.');
        }

        try {
            $scriptContent = $this->scriptService->readScriptForResolved($resolved);
            $scriptContent = $this->moduleAccessService->wrapScript(
                $scriptContent,
                $license,
                $resolved
            );
        } catch (\RuntimeException) {
            return back()->with('error', 'File script tidak tersedia.');
        }

        $productName = $resolved['product']?->name ?? $resolved['folder'];
        $version = $resolved['product']?->version ?? '1.0';

        LicenseActivity::log(
            action: LicenseActivity::ACTION_DOWNLOAD_PRODUCT,
            userId: auth()->id(),
            licenseId: $license->id,
            meta: ['product' => $productName, 'version' => $version],
            request: $request
        );

        $filename = str($productName)->slug().'-v'.$version.'.lua';

        return response($scriptContent, 200, [
            'Content-Type' => 'text/plain',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }
}
