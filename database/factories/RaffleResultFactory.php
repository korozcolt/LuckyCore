<?php

namespace Database\Factories;

use App\Models\Raffle;
use App\Models\RaffleResult;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RaffleResult>
 */
class RaffleResultFactory extends Factory
{
    protected $model = RaffleResult::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'raffle_id' => Raffle::factory(),
            'lottery_name' => $this->faker->randomElement([
                'Lotería de Bogotá',
                'Lotería del Valle',
                'Lotería de Medellín',
            ]),
            'lottery_number' => str_pad((string) $this->faker->numberBetween(1, 99999), 5, '0', STR_PAD_LEFT),
            'lottery_date' => now(),
            'calculation_formula' => 'standard',
            'calculation_details' => [],
            'is_confirmed' => false,
            'is_published' => false,
            'registered_by' => User::factory(),
        ];
    }

    /**
     * Indicate that the result is confirmed.
     */
    public function confirmed(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_confirmed' => true,
            'confirmed_at' => now(),
            'confirmed_by' => User::factory(),
        ]);
    }

    /**
     * Indicate that the result is published.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_confirmed' => true,
            'confirmed_at' => now(),
            'confirmed_by' => User::factory(),
            'is_published' => true,
            'published_at' => now(),
            'published_by' => User::factory(),
        ]);
    }

    /**
     * Set a specific winning number.
     */
    public function withNumber(string $number): static
    {
        return $this->state(fn (array $attributes) => [
            'lottery_number' => $number,
        ]);
    }
}
