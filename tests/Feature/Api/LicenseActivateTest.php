<?php

use App\Models\License;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
    $this->product = Product::factory()->withFolder('universal')->create();
    $this->user = User::factory()->create();
    $this->license = License::factory()->for($this->user)->create([
        'product_id' => null,
    ]);
});

it('activates license and binds hwid on first use', function () {
    $response = $this->postJson('/api/license/activate', [
        'key' => $this->license->license_key,
        'hwid' => 'HWID-ABC12345',
    ]);

    $response->assertSuccessful()
        ->assertJson(['status' => true, 'message' => 'Activated']);

    expect($this->license->fresh())
        ->hwid->toBe('HWID-ABC12345')
        ->activated_at->not->toBeNull();
});

it('validates license with matching hwid on second use', function () {
    $this->license->update(['hwid' => 'HWID-EXISTING', 'activated_at' => now()]);

    $this->postJson('/api/license/activate', [
        'key' => $this->license->license_key,
        'hwid' => 'HWID-EXISTING',
    ])->assertSuccessful()->assertJson(['status' => true]);
});

it('rejects activation when hwid does not match', function () {
    $this->license->update(['hwid' => 'HWID-ORIGINAL']);

    $this->postJson('/api/license/activate', [
        'key' => $this->license->license_key,
        'hwid' => 'HWID-DIFFERENT',
    ])->assertForbidden()->assertJson(['status' => false]);
});

it('rejects activation for expired license', function () {
    $this->license->update(['expired_at' => now()->subDay()]);

    $this->postJson('/api/license/activate', [
        'key' => $this->license->license_key,
        'hwid' => 'HWID-TEST',
    ])->assertStatus(410)->assertJson(['status' => false]);
});

it('rejects activation for banned license', function () {
    $this->license->update(['status' => 'banned', 'ban_reason' => 'Violation']);

    $this->postJson('/api/license/activate', [
        'key' => $this->license->license_key,
        'hwid' => 'HWID-TEST',
    ])->assertForbidden()->assertJson(['status' => false]);
});

it('returns 404 for unknown license key', function () {
    $this->postJson('/api/license/activate', [
        'key' => 'LZD-000000-000000-000000-000000',
        'hwid' => 'HWID-TEST',
    ])->assertNotFound()->assertJson(['status' => false]);
});

it('returns validation error for invalid key format', function () {
    $this->postJson('/api/license/activate', [
        'key' => 'INVALID-KEY',
        'hwid' => 'HWID-TEST',
    ])->assertUnprocessable();
});

it('enforces rate limiting at 60 requests per minute', function () {
    $license = License::factory()->create(['product_id' => null]);

    for ($i = 0; $i < 60; $i++) {
        $this->postJson('/api/license/activate', [
            'key' => $license->license_key,
            'hwid' => 'HWID-TEST',
        ]);
    }

    $this->postJson('/api/license/activate', [
        'key' => $license->license_key,
        'hwid' => 'HWID-TEST',
    ])->assertStatus(429);
});
