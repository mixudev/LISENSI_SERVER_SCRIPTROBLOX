<?php

use App\Models\License;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
    Product::factory()->withFolder('universal')->create();
    $this->user = User::factory()->create();
    $this->license = License::factory()
        ->for($this->user)
        ->withHwid('HWID-BOUND')
        ->create(['product_id' => null]);
});

it('returns valid status for active license with matching hwid', function () {
    $this->postJson('/api/license/check', [
        'key' => $this->license->license_key,
        'hwid' => 'HWID-BOUND',
    ])->assertSuccessful()->assertJson(['status' => true]);
});

it('rejects check when hwid does not match', function () {
    $this->postJson('/api/license/check', [
        'key' => $this->license->license_key,
        'hwid' => 'HWID-WRONG',
    ])->assertForbidden()->assertJson(['status' => false]);
});

it('rejects check for expired license', function () {
    $this->license->update(['expired_at' => now()->subDay()]);

    $this->postJson('/api/license/check', [
        'key' => $this->license->license_key,
        'hwid' => 'HWID-BOUND',
    ])->assertStatus(410)->assertJson(['status' => false]);
});

it('rejects check for suspended license', function () {
    $this->license->update(['status' => 'suspended', 'ban_reason' => 'Test']);

    $this->postJson('/api/license/check', [
        'key' => $this->license->license_key,
        'hwid' => 'HWID-BOUND',
    ])->assertForbidden()->assertJson(['status' => false]);
});

it('returns 404 for unknown license key', function () {
    $this->postJson('/api/license/check', [
        'key' => 'LZD-FFFFFF-FFFFFF-FFFFFF-FFFFFF',
        'hwid' => 'HWID-TEST',
    ])->assertNotFound()->assertJson(['status' => false]);
});

it('returns validation error for missing fields', function () {
    $this->postJson('/api/license/check', [])->assertUnprocessable();
});
