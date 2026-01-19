<?php

namespace Database\Factories;

use App\Models\Raffle;
use App\Models\RafflePrize;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Winner;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Winner>
 */
class WinnerFactory extends Factory
{
    protected $model = Winner::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'raffle_id' => Raffle::factory(),
            'raffle_prize_id' => RafflePrize::factory(),
            'ticket_id' => Ticket::factory(),
            'user_id' => User::factory(),
            'winner_name' => $this->faker->name(),
            'winner_email' => $this->faker->safeEmail(),
            'winner_phone' => $this->faker->phoneNumber(),
            'ticket_number' => str_pad((string) $this->faker->numberBetween(1, 99999), 5, '0', STR_PAD_LEFT),
            'prize_name' => $this->faker->randomElement(['Primer Premio', 'Segundo Premio', 'Tercer Premio']),
            'prize_value' => $this->faker->numberBetween(1000000, 100000000),
            'prize_position' => $this->faker->numberBetween(1, 3),
            'is_notified' => false,
            'is_claimed' => false,
            'is_delivered' => false,
            'is_published' => true,
        ];
    }

    /**
     * Indicate that the winner has been notified.
     */
    public function notified(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_notified' => true,
            'notified_at' => now(),
        ]);
    }

    /**
     * Indicate that the prize has been claimed.
     */
    public function claimed(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_claimed' => true,
            'claimed_at' => now(),
        ]);
    }

    /**
     * Indicate that the prize has been delivered.
     */
    public function delivered(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_delivered' => true,
            'delivered_at' => now(),
        ]);
    }

    /**
     * Indicate that the winner is published.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_published' => true,
            'published_at' => now(),
        ]);
    }

    /**
     * Indicate that the winner is not published.
     */
    public function unpublished(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_published' => false,
            'published_at' => null,
        ]);
    }

    /**
     * First prize winner.
     */
    public function firstPrize(): static
    {
        return $this->state(fn (array $attributes) => [
            'prize_name' => 'Primer Premio',
            'prize_position' => 1,
        ]);
    }

    /**
     * Second prize winner.
     */
    public function secondPrize(): static
    {
        return $this->state(fn (array $attributes) => [
            'prize_name' => 'Segundo Premio',
            'prize_position' => 2,
        ]);
    }

    /**
     * Third prize winner.
     */
    public function thirdPrize(): static
    {
        return $this->state(fn (array $attributes) => [
            'prize_name' => 'Tercer Premio',
            'prize_position' => 3,
        ]);
    }
}
