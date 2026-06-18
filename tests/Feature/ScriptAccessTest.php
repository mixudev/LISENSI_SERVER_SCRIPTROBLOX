<?php

use App\Models\License;
use App\Models\Product;
use App\Models\User;
use App\Services\ScriptService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
    Product::factory()->withFolder('universal')->create([
        'name' => 'User Universal',
        'slug' => 'user-universal',
        'access_level' => 'user',
    ]);

    Product::factory()->create([
        'name' => 'Admin Only Tools',
        'slug' => 'admin-tools',
        'script_folder' => 'universal',
        'access_level' => 'admin',
    ]);
});

it('blocks user license from resolving admin-only product', function () {
    $resolved = app(ScriptService::class)->resolveForLicense('user', '0');

    expect($resolved['product'])->not->toBeNull()
        ->and($resolved['product']->access_level)->toBe('user')
        ->and($resolved['product']->name)->toBe('User Universal');
});

it('allows admin license to resolve admin-only product when prioritized', function () {
    $resolved = app(ScriptService::class)->resolveForLicense('admin', '0');

    expect($resolved['product'])->not->toBeNull()
        ->and($resolved['product']->access_level)->toBe('admin')
        ->and($resolved['product']->name)->toBe('Admin Only Tools');
});

it('returns no script via api for user license when only admin product exists', function () {
    Product::query()->where('access_level', 'user')->delete();

    $user = User::factory()->create();
    $license = License::factory()->for($user)->create([
        'license_type' => 'user',
        'product_id' => null,
    ]);

    $response = $this->get('/api/license/get?'.http_build_query([
        'key' => $license->license_key,
        'hwid' => 'HWID-ACCESS-TEST',
    ]));

    $response->assertSuccessful();
    expect($response->getContent())->toContain('Tidak ada script');
});

it('denies user license access to admin product via isAccessibleBy', function () {
    $adminProduct = Product::where('access_level', 'admin')->first();

    expect($adminProduct->isAccessibleBy('user'))->toBeFalse()
        ->and($adminProduct->isAccessibleBy('admin'))->toBeTrue();
});
