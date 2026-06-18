<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'name'         => ucwords($name),
            'slug'         => Str::slug($name),
            'description'  => fake()->sentence(),
            'version'      => fake()->semver(),
            'script_folder' => null,
            'script_source' => 'local',
            'github_repo'   => null,
            'github_branch' => 'main',
            'github_path'   => null,
            'access_level'  => 'user',
            'place_ids'     => null,
            'price'        => fake()->randomElement([50000, 100000, 200000, 500000]),
            'currency'     => 'IDR',
            'status'       => 'active',
            'notes'        => null,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }

    public function withFolder(string $folder = 'universal'): static
    {
        return $this->state(fn (array $attributes) => [
            'script_folder' => $folder,
        ]);
    }

    public function withPlaceIds(array $placeIds): static
    {
        return $this->state(fn (array $attributes) => [
            'place_ids' => $placeIds,
        ]);
    }

    public function adminOnly(): static
    {
        return $this->state(fn (array $attributes) => [
            'access_level' => 'admin',
        ]);
    }
}
