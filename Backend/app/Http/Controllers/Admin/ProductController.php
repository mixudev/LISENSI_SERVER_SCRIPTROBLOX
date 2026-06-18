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
use Illuminate\Http\Request;
use Illuminate\Support\Str;
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
        $products = $this->products->getAllWithLicenseCounts();
        $localFolders = ScriptService::scanLocalFolders();
        $githubPatInfo = $this->githubScriptService->patInfo();
        $githubPatInfo['configured'] = $this->githubScriptService->patConfigured();

        return view('dashboard.admin.products.index', compact('products', 'localFolders', 'githubPatInfo'));
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $data = collect($request->validated())->except('place_ids_raw')->toArray();
        $product = Product::create($data);

        return $this->finishGithubProductSave($product, 'Produk berhasil ditambahkan.');
    }

    public function update(StoreProductRequest $request, Product $product): RedirectResponse
    {
        $data = collect($request->validated())->except('place_ids_raw')->toArray();
        $product->update($data);

        return $this->finishGithubProductSave($product, 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        if ($this->products->hasActiveLicenses($product->id)) {
            return back()->with('error', 'Tidak bisa menghapus produk yang masih memiliki lisensi aktif.');
        }

        $product->delete();

        return redirect()->route('admin.products.index')->with('success', 'Produk dihapus.');
    }

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

    public function githubStatus(): JsonResponse
    {
        $pat = $this->githubScriptService->patInfo();
        $pat['configured'] = $this->githubScriptService->patConfigured();

        return response()->json(['ok' => $pat['configured'], 'pat' => $pat]);
    }

    public function checkAvailability(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'place_ids_raw' => ['nullable', 'string', 'max:2000'],
            'exclude_product_id' => ['nullable', 'integer', 'exists:products,id'],
        ]);

        $excludeId = $validated['exclude_product_id'] ?? null;
        $name = trim($validated['name'] ?? '');
        $placeIds = Product::parsePlaceIdsFromRaw($validated['place_ids_raw'] ?? '') ?? [];
        $conflicts = Product::findPlaceIdConflicts($placeIds, $excludeId);

        $slugBase = Str::slug($name);
        $slug = $name !== '' ? Product::generateUniqueSlug($name, $excludeId) : '';
        $slugTaken = $slugBase !== '' && Product::withTrashed()
            ->when($excludeId, fn ($query) => $query->where('id', '!=', $excludeId))
            ->where('slug', $slugBase)
            ->exists();

        return response()->json([
            'ok' => true,
            'slug' => $slug,
            'slug_base' => $slugBase,
            'slug_available' => $slugBase !== '' && ! $slugTaken,
            'slug_auto_suffix' => $slugBase !== '' && $slug !== $slugBase,
            'place_ids' => $placeIds,
            'place_conflicts' => $conflicts,
            'place_available' => $conflicts === [],
        ]);
    }

    public function refreshScript(Product $product): JsonResponse
    {
        if (! $product->usesGithubScript()) {
            return response()->json(['ok' => false, 'message' => 'Produk ini tidak menggunakan GitHub source.']);
        }

        try {
            $sync = $this->githubScriptService->syncProductToLocal($product);
            $product->update([
                'script_folder' => $sync['folder'],
                'github_synced_at' => now(),
            ]);
            $this->scriptService->invalidateGithubCache($product);

            return response()->json([
                'ok' => true,
                'message' => "Sync ulang berhasil. {$sync['files']} file di folder {$sync['folder']}/.",
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'ok' => false,
                'message' => 'Gagal sync dari GitHub: '.$e->getMessage(),
            ], 422);
        }
    }

    private function finishGithubProductSave(Product $product, string $baseMessage): RedirectResponse
    {
        if (! $product->usesGithubScript()) {
            return redirect()->route('admin.products.index')->with('success', $baseMessage);
        }

        try {
            $sync = $this->githubScriptService->syncProductToLocal($product);
            $product->update([
                'script_folder' => $sync['folder'],
                'github_synced_at' => now(),
            ]);

            return redirect()->route('admin.products.index')->with(
                'success',
                "{$baseMessage} {$sync['message']} Script siap di-inject dari folder lokal."
            );
        } catch (\Throwable $e) {
            report($e);

            return redirect()->route('admin.products.index')->with(
                'warning',
                "{$baseMessage} Namun sync GitHub gagal: {$e->getMessage()}. Perbaiki PAT/repo lalu klik Refresh."
            );
        }
    }
}
