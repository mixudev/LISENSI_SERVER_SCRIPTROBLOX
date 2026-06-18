<?php

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

it('saves place_ids when creating product via admin', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->post(route('admin.products.store'), [
        'name' => 'Map Game',
        'version' => '1.0.0',
        'script_source' => 'local',
        'script_folder' => 'universal',
        'access_level' => 'user',
        'status' => 'active',
        'place_ids_raw' => '123456789, 987654321',
    ]);

    $response->assertRedirect();

    $product = Product::where('name', 'Map Game')->first();

    expect($product)->not->toBeNull()
        ->and($product->place_ids)->toBe(['123456789', '987654321']);
});

it('updates place_ids when editing product via admin', function () {
    $admin = User::factory()->admin()->create();
    $product = Product::factory()->withFolder('universal')->create([
        'place_ids' => ['111111'],
    ]);

    $response = $this->actingAs($admin)->put(route('admin.products.update', $product), [
        'name' => $product->name,
        'version' => $product->version,
        'script_source' => 'local',
        'script_folder' => 'universal',
        'access_level' => 'user',
        'status' => 'active',
        'place_ids_raw' => '222222, 333333',
    ]);

    $response->assertRedirect();

    expect($product->fresh()->place_ids)->toBe(['222222', '333333']);
});

it('rejects duplicate place_id assigned to another product', function () {
    Product::factory()->withFolder('universal')->withPlaceIds(['111111'])->create([
        'name' => 'Existing Map',
    ]);

    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->post(route('admin.products.store'), [
        'name' => 'New Map',
        'version' => '1.0.0',
        'script_source' => 'local',
        'script_folder' => 'universal',
        'access_level' => 'user',
        'status' => 'active',
        'place_ids_raw' => '111111',
    ]);

    $response->assertSessionHasErrors('place_ids_raw');
    expect(Product::where('name', 'New Map')->exists())->toBeFalse();
});

it('reports slug and place availability via api', function () {
    Product::factory()->create([
        'name' => 'VD',
        'slug' => 'vd',
        'place_ids' => ['555555'],
    ]);

    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->postJson(route('admin.products.check-availability'), [
        'name' => 'VD',
        'place_ids_raw' => '555555, 777777',
    ]);

    $response->assertSuccessful()
        ->assertJsonPath('slug', 'vd-2')
        ->assertJsonPath('slug_auto_suffix', true)
        ->assertJsonPath('place_available', false)
        ->assertJsonPath('place_conflicts.0.place_id', '555555');
});
