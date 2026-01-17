<?php

namespace Database\Factories;

use App\Models\Raffle;
use App\Models\RafflePackage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RafflePackage>
 */
class RafflePackageFactory extends Factory
{
    protected $model = RafflePackage::class;

    public function definition(): array
    {
        $quantity = fake()->randomElement([10, 20, 50, 100]);

        return [
            'raffle_id' => Raffle::factory(),
            'name' => "Paquete de {$quantity}",
            'quantity' => $quantity,
            'price' => $quantity * fake()->numberBetween(3000, 5000),
            'is_recommended' => false,
            'is_active' => true,
            'sort_order' => 0,
        ];
    }

    public function recommended(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_recommended' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
