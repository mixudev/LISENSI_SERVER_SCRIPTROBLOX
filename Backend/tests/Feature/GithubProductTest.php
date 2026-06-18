<?php

use App\Models\License;
use App\Models\ModuleAccessToken;
use App\Models\Product;
use App\Models\User;
use App\Services\GithubScriptService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
    config(['services.github.pat' => 'ghp_test_token_classic']);
    Http::preventStrayRequests();
});

function fakeGithubApi(array $files = []): void
{
    Http::fake(function ($request) use ($files) {
        $url = $request->url();

        if (str_contains($url, '/repos/acme/scripts/branches')) {
            return Http::response([
                ['name' => 'main'],
                ['name' => 'dev'],
            ]);
        }

        if (preg_match('#/repos/acme/scripts$#', parse_url($url, PHP_URL_PATH) ?? '')) {
            return Http::response([
                'default_branch' => 'main',
                'name' => 'scripts',
            ]);
        }

        if (str_contains($url, '/contents/')) {
            $path = urldecode((string) parse_url($url, PHP_URL_PATH));
            $relative = str_replace('/repos/acme/scripts/contents/', '', $path);

            if (isset($files[$relative])) {
                return Http::response([
                    'content' => base64_encode($files[$relative]),
                ]);
            }

            return Http::response(['message' => 'Not Found'], 404);
        }

        return Http::response(['message' => 'Not Found'], 404);
    });
}

it('normalizes github repo input formats', function () {
    $service = app(GithubScriptService::class);

    expect($service->normalizeRepo('Owner/Repo-Name'))->toBe('owner/repo-name');
    expect($service->normalizeRepo('https://github.com/owner/repo-name.git'))->toBe('owner/repo-name');
});

it('detects classic pat type', function () {
    config(['services.github.pat' => 'ghp_abcdefghijklmnop']);

    $info = app(GithubScriptService::class)->patInfo();

    expect($info['type'])->toBe('classic');
    expect($info['label'])->toBe('Classic PAT');
});

it('detects fine-grained pat type', function () {
    config(['services.github.pat' => 'github_pat_11ABCDEF']);

    $info = app(GithubScriptService::class)->patInfo();

    expect($info['type'])->toBe('fine_grained');
});

it('inspects github repo and detects loader paths', function () {
    fakeGithubApi([
        'loader.lua' => '-- loader',
    ]);

    $this->actingAs(User::factory()->admin()->create())
        ->postJson(route('admin.products.github.inspect'), [
            'github_repo' => 'acme/scripts',
        ])
        ->assertSuccessful()
        ->assertJsonPath('ok', true)
        ->assertJsonPath('repo', 'acme/scripts')
        ->assertJsonPath('recommended_path', 'loader.lua');
});

it('serves github modules via session token', function () {
    Product::factory()->create([
        'script_source' => 'github',
        'github_repo' => 'acme/scripts',
        'github_branch' => 'main',
        'github_path' => 'universal/loader.lua',
        'script_folder' => null,
        'status' => 'active',
        'access_level' => 'user',
    ]);

    $license = License::factory()->create([
        'license_type' => 'user',
        'product_id' => null,
    ]);

    $product = Product::first();

    fakeGithubApi([
        'universal/features/fly.lua' => 'print("github fly module")',
    ]);

    $accessToken = ModuleAccessToken::issueFromResolved($license, [
        'product' => $product,
        'folder' => $product->github_repo,
        'source' => 'github',
    ]);

    $response = $this->get("/modules/{$accessToken->token}/features/fly.lua");

    $response->assertSuccessful();
    expect($response->getContent())->toContain('github fly module');
});

it('injects script source into getScript preamble for github product', function () {
    fakeGithubApi([
        'universal/loader.lua' => 'print("github loader")',
    ]);

    Product::factory()->create([
        'script_source' => 'github',
        'github_repo' => 'acme/scripts',
        'github_branch' => 'main',
        'github_path' => 'universal/loader.lua',
        'script_folder' => null,
        'status' => 'active',
        'access_level' => 'user',
    ]);

    $license = License::factory()->create([
        'license_type' => 'user',
        'product_id' => null,
    ]);

    $response = $this->get('/api/license/get?'.http_build_query([
        'key' => $license->license_key,
        'hwid' => 'HWID-GITHUB-TEST',
    ]));

    $response->assertSuccessful();
    expect($response->getContent())->toContain('_G.LIMEHUB_SCRIPT_SOURCE = "github"');
    expect($response->getContent())->toContain('github loader');
});

it('computes github module prefix from loader path', function () {
    expect(GithubScriptService::modulePathPrefixFromLoaderPath('scripts/universal/loader.lua'))
        ->toBe('scripts/universal');
    expect(GithubScriptService::modulePathPrefixFromLoaderPath('loader.lua'))
        ->toBe('');
});
