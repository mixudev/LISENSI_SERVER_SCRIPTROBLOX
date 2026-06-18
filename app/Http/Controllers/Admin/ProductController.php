<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\InspectGithubRepositoryRequest;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Models\Product;
use App\Repositories\ProductRepository;
use App\Services\GithubScriptService;
use App\Services\ScriptService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductRepository $products,
        private readonly ScriptService $scriptService,
        private readonly GithubScriptService $githubScriptService
    ) {}

    public function index(): View
    {
        $products      = $this->products->getAllWithLicenseCounts();
        $localFolders  = ScriptService::scanLocalFolders();
        $githubPatInfo = $this->githubScriptService->patInfo();
        $githubPatInfo['configured'] = $this->githubScriptService->patConfigured();

        return view('dashboard.admin.products.index', compact('products', 'localFolders', 'githubPatInfo'));
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $data = collect($request->validated())->except('place_ids_raw')->toArray();
        Product::create($data);

        return redirect()->route('admin.products.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    public function update(StoreProductRequest $request, Product $product): RedirectResponse
    {
        $data = collect($request->validated())->except('place_ids_raw')->toArray();
        $product->update($data);

        if ($product->usesGithubScript()) {
            $this->scriptService->invalidateGithubCache($product);
        }

        return redirect()->route('admin.products.index')->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        if ($this->products->hasActiveLicenses($product->id)) {
            return back()->with('error', 'Tidak bisa menghapus produk yang masih memiliki lisensi aktif.');
        }

        $product->delete();

        return redirect()->route('admin.products.index')->with('success', 'Produk dihapus.');
    }

    /**
     * POST /admin/products/github/inspect
     * Scan repo private: branch, deteksi loader.lua otomatis.
     */
    public function inspectGithub(InspectGithubRepositoryRequest $request): JsonResponse
    {
        try {
            $result = $this->githubScriptService->inspectRepository(
                $request->validated('github_repo'),
                $request->validated('github_branch')
            );
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'ok' => false,
                'message' => 'Server error saat scan repo: '.$e->getMessage(),
            ], 500);
        }

        return response()->json($result, $result['ok'] ? 200 : 422);
    }

    /**
     * GET /admin/products/github/status
     * Status PAT GitHub untuk panel admin.
     */
    public function githubStatus(): JsonResponse
    {
        $pat = $this->githubScriptService->patInfo();
        $pat['configured'] = $this->githubScriptService->patConfigured();

        return response()->json(['ok' => $pat['configured'], 'pat' => $pat]);
    }

    /**
     * POST /admin/products/{product}/refresh-script
     * Force-invalidate GitHub script cache agar server fetch ulang dari repo.
     */
    public function refreshScript(Product $product): JsonResponse
    {
        if (! $product->usesGithubScript()) {
            return response()->json(['ok' => false, 'message' => 'Produk ini tidak menggunakan GitHub source.']);
        }

        $this->scriptService->invalidateGithubCache($product);

        return response()->json([
            'ok'      => true,
            'message' => "Cache script {$product->name} dihapus. Request berikutnya akan fetch ulang dari GitHub.",
        ]);
    }
}
