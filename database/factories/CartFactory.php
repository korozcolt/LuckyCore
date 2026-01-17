<?php

namespace Database\Factories;

use App\Models\Cart;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Cart>
 */
class CartFactory extends Factory
{
    protected $model = Cart::class;

    public function definition(): array
    {
        return [
            'session_id' => fake()->uuid(),
            'user_id' => null,
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'merged_at' => null,
            'converted_at' => null,
        ];
    }

    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    public function merged(): static
    {
        return $this->state(fn (array $attributes) => [
            'merged_at' => now(),
        ]);
    }

    public function converted(): static
    {
        return $this->state(fn (array $attributes) => [
            'converted_at' => now(),
        ]);
    }
}
