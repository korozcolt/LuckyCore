<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Raffle;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ticket>
 */
class TicketFactory extends Factory
{
    protected $model = Ticket::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'raffle_id' => Raffle::factory(),
            'order_id' => Order::factory(),
            'order_item_id' => OrderItem::factory(),
            'user_id' => User::factory(),
            'code' => (string) fake()->numberBetween(1, 99999),
            'is_winner' => false,
            'prize_position' => null,
            'won_at' => null,
        ];
    }
}
