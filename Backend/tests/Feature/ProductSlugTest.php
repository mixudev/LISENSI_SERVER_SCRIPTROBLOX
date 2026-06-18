<?php

use App\Models\Product;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

it('generates unique slug when name collides with existing product', function () {
    Product::factory()->create([
        'name' => 'VD',
        'slug' => 'vd',
    ]);

    $product = Product::create([
        'name' => 'VD',
        'version' => '1.0.0',
        'script_source' => 'github',
        'github_repo' => 'mixudev/tools---violancedistrict',
        'github_branch' => 'main',
        'github_path' => 'loader.lua',
        'access_level' => 'user',
        'status' => 'active',
    ]);

    expect($product->slug)->toBe('vd-2');
});

it('generates unique slug when soft deleted product still occupies slug', function () {
    Product::factory()->create([
        'name' => 'VD',
        'slug' => 'vd',
    ])->delete();

    $product = Product::create([
        'name' => 'VD',
        'version' => '1.0.0',
        'script_source' => 'local',
        'script_folder' => 'universal',
        'access_level' => 'user',
        'status' => 'active',
    ]);

    expect($product->slug)->toBe('vd-2');
});
