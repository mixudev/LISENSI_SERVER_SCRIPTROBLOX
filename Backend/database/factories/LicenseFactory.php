<?php

namespace Database\Factories;

use App\Models\License;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<License>
 */
class LicenseFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'product_id' => Product::factory(),
            'license_key' => License::generateKey(),
            'hwid' => null,
            'hwid_reset_count' => 0,
            'hwid_last_reset_at' => null,
            'status' => 'active',
            'expired_at' => now()->addDays(30),
            'ban_reason' => null,
            'last_ip' => null,
            'last_user_agent' => null,
            'last_used_at' => null,
            'activated_at' => null,
            'created_by' => null,
            'notes' => null,
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'expired_at' => now()->addDays(30),
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'expired',
            'expired_at' => now()->subDays(5),
        ]);
    }

    public function banned(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'banned',
            'ban_reason' => fake()->sentence(),
        ]);
    }

    public function suspended(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'suspended',
            'ban_reason' => fake()->sentence(),
        ]);
    }

    public function lifetime(): static
    {
        return $this->state(fn (array $attributes) => [
            'expired_at' => null,
        ]);
    }

    public function withHwid(string $hwid = 'TEST-HWID-ABC123'): static
    {
        return $this->state(fn (array $attributes) => [
            'hwid' => $hwid,
            'activated_at' => now(),
        ]);
    }

    public function activated(): static
    {
        return $this->state(fn (array $attributes) => [
            'hwid' => fake()->sha256(),
            'activated_at' => now()->subDays(5),
            'last_used_at' => now()->subHours(2),
            'last_ip' => fake()->ipv4(),
        ]);
    }
}
