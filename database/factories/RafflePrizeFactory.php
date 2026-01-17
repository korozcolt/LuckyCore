<?php

namespace Database\Factories;

use App\Enums\WinningConditionType;
use App\Models\Raffle;
use App\Models\RafflePrize;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RafflePrize>
 */
class RafflePrizeFactory extends Factory
{
    protected $model = RafflePrize::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'raffle_id' => Raffle::factory(),
            'name' => $this->faker->randomElement([
                'Primer Premio',
                'Segundo Premio',
                'Tercer Premio',
                'Premio Especial',
                'Premio Consolación',
            ]),
            'description' => $this->faker->optional()->sentence(),
            'prize_value' => $this->faker->numberBetween(10000, 100000000), // In cents
            'prize_position' => $this->faker->numberBetween(1, 5),
            'winning_conditions' => [
                'type' => WinningConditionType::ExactMatch->value,
            ],
            'is_active' => true,
            'sort_order' => 0,
        ];
    }

    /**
     * Indicate that the prize is for exact match.
     */
    public function exactMatch(): static
    {
        return $this->state(fn (array $attributes) => [
            'winning_conditions' => [
                'type' => WinningConditionType::ExactMatch->value,
            ],
        ]);
    }

    /**
     * Indicate that the prize is for reverse match.
     */
    public function reverse(): static
    {
        return $this->state(fn (array $attributes) => [
            'winning_conditions' => [
                'type' => WinningConditionType::Reverse->value,
            ],
        ]);
    }

    /**
     * Indicate that the prize is for last digits match.
     */
    public function lastDigits(int $digitCount = 2): static
    {
        return $this->state(fn (array $attributes) => [
            'winning_conditions' => [
                'type' => WinningConditionType::LastDigits->value,
                'digit_count' => $digitCount,
            ],
        ]);
    }

    /**
     * Indicate that the prize is for first digits match.
     */
    public function firstDigits(int $digitCount = 2): static
    {
        return $this->state(fn (array $attributes) => [
            'winning_conditions' => [
                'type' => WinningConditionType::FirstDigits->value,
                'digit_count' => $digitCount,
            ],
        ]);
    }

    /**
     * Indicate that the prize is for permutation match.
     */
    public function permutation(): static
    {
        return $this->state(fn (array $attributes) => [
            'winning_conditions' => [
                'type' => WinningConditionType::Permutation->value,
            ],
        ]);
    }

    /**
     * Indicate that the prize is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
