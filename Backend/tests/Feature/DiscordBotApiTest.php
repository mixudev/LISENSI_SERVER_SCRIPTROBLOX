<?php

use App\Models\License;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
    config([
        'services.discord_bot.token' => 'test-bot-token-secret',
        'services.discord_bot.admin_discord_ids' => ['999000111222333444'],
    ]);

    $this->botHeaders = [
        'Authorization' => 'Bearer test-bot-token-secret',
        'Accept' => 'application/json',
    ];

    $this->targetDiscordId = '111222333444555666';
    $this->adminDiscordId = '999000111222333444';
});

it('rejects bot api without bearer token', function () {
    $this->getJson('/api/bot/health')
        ->assertUnauthorized()
        ->assertJson(['status' => false]);
});

it('returns health for authenticated bot', function () {
    $this->getJson('/api/bot/health', $this->botHeaders)
        ->assertSuccessful()
        ->assertJsonPath('status', true);
});

it('returns license stats by discord id', function () {
    $user = User::factory()->create(['discord_id' => $this->targetDiscordId]);
    $license = License::factory()->for($user)->create([
        'product_id' => null,
        'hwid' => 'HWID-TEST-123',
    ]);

    $this->getJson('/api/bot/stats?discord_id='.$this->targetDiscordId, $this->botHeaders)
        ->assertSuccessful()
        ->assertJsonPath('data.key', $license->license_key)
        ->assertJsonPath('data.hwid', 'HWID-TEST-123');
});

it('generates one license per discord user', function () {
    $response = $this->postJson('/api/bot/generate', [
        'target_discord_id' => $this->targetDiscordId,
        'actor_discord_id' => $this->adminDiscordId,
        'duration_days' => 30,
    ], $this->botHeaders);

    $response->assertCreated()
        ->assertJsonPath('status', true);

    expect(User::where('discord_id', $this->targetDiscordId)->exists())->toBeTrue()
        ->and(License::where('user_id', User::where('discord_id', $this->targetDiscordId)->value('id'))->count())->toBe(1);

    $this->postJson('/api/bot/generate', [
        'target_discord_id' => $this->targetDiscordId,
        'actor_discord_id' => $this->adminDiscordId,
        'duration_days' => 30,
    ], $this->botHeaders)
        ->assertStatus(409)
        ->assertJsonPath('status', false);
});

it('rejects generate from non admin discord id', function () {
    $this->postJson('/api/bot/generate', [
        'target_discord_id' => $this->targetDiscordId,
        'actor_discord_id' => '000111222333444555',
        'duration_days' => 30,
    ], $this->botHeaders)
        ->assertForbidden();
});

it('resets hwid for discord user license', function () {
    $user = User::factory()->create(['discord_id' => $this->targetDiscordId]);
    $license = License::factory()->for($user)->create([
        'product_id' => null,
        'hwid' => 'OLD-HWID',
        'hwid_reset_count' => 0,
    ]);

    $this->postJson('/api/bot/reset-hwid', [
        'discord_id' => $this->targetDiscordId,
    ], $this->botHeaders)
        ->assertSuccessful()
        ->assertJsonPath('status', true);

    expect($license->fresh())
        ->hwid->toBeNull()
        ->hwid_reset_count->toBe(1);
});

it('returns loader script template with license key', function () {
    $user = User::factory()->create(['discord_id' => $this->targetDiscordId]);
    $license = License::factory()->for($user)->create(['product_id' => null]);

    $this->getJson('/api/bot/script-template?discord_id='.$this->targetDiscordId, $this->botHeaders)
        ->assertSuccessful()
        ->assertJsonPath('data.key', $license->license_key)
        ->assertJsonStructure(['data' => ['script', 'loader_url']]);
});
