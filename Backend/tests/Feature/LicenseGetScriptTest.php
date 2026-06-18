<?php

use App\Models\License;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
    Product::factory()->withFolder('universal')->create();
    $this->user = User::factory()->create();
    $this->license = License::factory()->for($this->user)->create([
        'product_id' => null,
    ]);
});

it('returns lua script for valid license via get endpoint', function () {
    $response = $this->get('/api/license/get?'.http_build_query([
        'key' => $this->license->license_key,
        'hwid' => 'HWID-GET-TEST',
        'username' => 'TestPlayer',
        'place' => '0',
    ]));

    $response->assertSuccessful();
    expect($response->getContent())->toContain('_G.LIMEHUB_MODULE_TOKEN');
    expect($response->getContent())->toContain('Alpha Project');
});

it('returns lua error for unknown license key via get endpoint', function () {
    $response = $this->get('/api/license/get?'.http_build_query([
        'key' => 'LZD-FFFFFF-FFFFFF-FFFFFF-FFFFFF',
        'hwid' => 'HWID-GET-TEST',
    ]));

    $response->assertSuccessful();
    expect($response->getContent())->toContain('error("[LimeHub]');
    expect($response->getContent())->toContain('tidak ditemukan');
});

it('returns lua error when hwid does not match', function () {
    $this->license->update(['hwid' => 'HWID-ORIGINAL', 'activated_at' => now()]);

    $response = $this->get('/api/license/get?'.http_build_query([
        'key' => $this->license->license_key,
        'hwid' => 'HWID-DIFFERENT',
    ]));

    $response->assertSuccessful();
    expect($response->getContent())->toContain('HWID tidak cocok');
});
