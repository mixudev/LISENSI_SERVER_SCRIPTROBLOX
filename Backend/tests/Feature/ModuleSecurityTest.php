<?php

use App\Models\License;
use App\Models\ModuleAccessToken;
use App\Models\Product;
use App\Models\User;
use App\Services\ApiLogService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
    Product::factory()->withFolder('universal')->create();
    $this->user = User::factory()->create();
    $this->license = License::factory()->for($this->user)->create([
        'license_type' => 'user',
        'product_id' => null,
    ]);
});

it('blocks module access without token', function () {
    $this->get('/modules/features/esp.lua')
        ->assertNotFound();
});

it('blocks module access with invalid token', function () {
    $fakeToken = str_repeat('a', 64);

    $this->get("/modules/{$fakeToken}/features/esp.lua")
        ->assertForbidden();
});

it('blocks loader.lua via modules even with valid token', function () {
    $accessToken = ModuleAccessToken::issue($this->license, 'universal');

    $this->get("/modules/{$accessToken->token}/loader.lua")
        ->assertForbidden();
});

it('allows module access with valid session token', function () {
    $accessToken = ModuleAccessToken::issue($this->license, 'universal');

    $response = $this->get("/modules/{$accessToken->token}/features/fly.lua");

    $response->assertSuccessful();
    expect($response->getContent())->not->toBeEmpty();
});

it('injects module session into getScript response', function () {
    $response = $this->get('/api/license/get?'.http_build_query([
        'key' => $this->license->license_key,
        'hwid' => 'HWID-SEC-TEST',
    ]));

    $response->assertSuccessful();
    expect($response->getContent())->toContain('_G.LIMEHUB_MODULE_TOKEN');
    expect($response->getContent())->toContain('_G.LIMEHUB_BASE_URL');
    $this->assertDatabaseCount('module_access_tokens', 1);
});

it('masks license key in api logs', function () {
    $masked = ApiLogService::maskLicenseKey('LZD-FC8198-3661ED-2A72BA-16FCA3');

    expect($masked)->toBe('LZD-FC8198-****-****-****-16FCA3');
});
