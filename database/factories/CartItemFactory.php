<?php

namespace Database\Factories;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Raffle;
use App\Models\RafflePackage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CartItem>
 */
class CartItemFactory extends Factory
{
    protected $model = CartItem::class;

    public function definition(): array
    {
        return [
            'cart_id' => Cart::factory(),
            'raffle_id' => Raffle::factory(),
            'raffle_package_id' => null,
            'quantity' => fake()->numberBetween(1, 100),
            'unit_price' => fake()->numberBetween(1000, 10000),
        ];
    }

    public function withPackage(RafflePackage $package): static
    {
        return $this->state(fn (array $attributes) => [
            'raffle_package_id' => $package->id,
            'raffle_id' => $package->raffle_id,
            'quantity' => $package->quantity,
            'unit_price' => (int) ($package->price / $package->quantity),
        ]);
    }
}
