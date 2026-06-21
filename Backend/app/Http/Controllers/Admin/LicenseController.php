<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreLicenseRequest;
use App\Models\License;
use App\Models\Product;
use App\Models\User;
use App\Repositories\LicenseRepository;
use App\Services\HwidService;
use App\Services\LicenseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LicenseController extends Controller
{
    public function __construct(
        private readonly LicenseRepository $licenseRepository,
        private readonly LicenseService $licenseService,
        private readonly HwidService $hwidService
    ) {}

    public function index(Request $request): View
    {
        $filters = $request->only(['status', 'license_type', 'search', 'product_id']);
        $licenses = $this->licenseRepository->paginateForAdmin($filters);
        $products = Product::orderBy('name')->get(['id', 'name']);

        return view('dashboard.admin.licenses.index', compact('licenses', 'filters', 'products'));
    }

    public function store(StoreLicenseRequest $request): RedirectResponse
    {
        $data = array_merge($request->validated(), ['created_by' => auth()->id()]);
        $this->licenseService->generate($data);

        return back()->with('success', 'Lisensi berhasil dibuat.');
    }

    public function storeBulk(Request $request): RedirectResponse
    {
        $request->validate([
            'license_type' => ['required', 'in:user,admin'],
            'count' => ['required', 'integer', 'min:1', 'max:100'],
            'duration_days' => ['nullable', 'integer', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $count = (int) $request->input('count', 1);
        $data = array_merge(
            $request->only(['license_type', 'duration_days', 'notes']),
            ['created_by' => auth()->id()]
        );
        $this->licenseService->generateBulk($data, min($count, 100));

        return back()->with('success', "{$count} lisensi berhasil dibuat.");
    }

    public function show(License $license): View
    {
        $license->load(['user', 'creator', 'hwidResetLogs', 'activities']);

        $usageLogs = $license->apiLogs()
            ->latest()
            ->limit(50)
            ->get();

        $accessibleProducts = Product::where('status', 'active')
            ->get()
            ->filter(fn (Product $product) => $product->isAccessibleBy($license->license_type ?? 'user'))
            ->values();

        return view('dashboard.admin.licenses.show', compact('license', 'usageLogs', 'accessibleProducts'));
    }

    public function update(Request $request, License $license): RedirectResponse
    {
        $validated = $request->validate([
            'expired_at' => ['nullable', 'date'],
            'status' => ['required', 'in:active,suspended,banned'],
            'license_type' => ['required', 'in:user,admin'],
            'ban_reason' => ['nullable', 'string', 'max:1000'],
            'user_id' => ['nullable', 'exists:users,id'],
            'discord_id' => ['nullable', 'string', 'max:32'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $license->update($validated);
        $this->licenseRepository->invalidateCache($license->license_key);

        return back()->with('success', 'Lisensi berhasil diperbarui.');
    }

    public function destroy(License $license): RedirectResponse
    {
        $license->delete();
        $this->licenseRepository->invalidateCache($license->license_key);

        return redirect()->route('admin.licenses.index')->with('success', 'Lisensi dihapus.');
    }

    public function resetHwid(License $license, Request $request): RedirectResponse
    {
        $this->hwidService->resetByAdmin($license, $request, $request->input('reason'));

        return back()->with('success', 'HWID berhasil direset.');
    }

    /**
     * GET /admin/licenses/search-users?q=keyword
     * AJAX endpoint untuk search user saat assign lisensi.
     */
    public function searchUsers(Request $request): JsonResponse
    {
        $query = $request->input('q', '');

        $users = User::where('role', 'user')
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%");
            })
            ->limit(10)
            ->get(['id', 'name', 'email']);

        return response()->json($users);
    }

    public function export(Request $request): StreamedResponse
    {
        $filters = $request->only(['status', 'license_type', 'search']);
        $licenses = $this->licenseRepository->paginateForAdmin($filters, 5000);

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=licenses.csv',
        ];

        return response()->stream(function () use ($licenses) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['License Key', 'Tipe', 'User', 'Status', 'Expired At', 'Roblox User', 'Created At']);

            foreach ($licenses as $license) {
                fputcsv($file, [
                    $license->license_key,
                    $license->license_type ?? 'user',
                    $license->user?->email ?? '-',
                    $license->status,
                    $license->expired_at?->format('Y-m-d') ?? 'Lifetime',
                    $license->roblox_username ?? '-',
                    $license->created_at->format('Y-m-d'),
                ]);
            }

            fclose($file);
        }, 200, $headers);
    }
}
