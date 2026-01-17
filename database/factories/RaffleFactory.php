<?php

namespace Database\Factories;

use App\Enums\RaffleStatus;
use App\Enums\TicketAssignmentMethod;
use App\Models\Raffle;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Raffle>
 */
class RaffleFactory extends Factory
{
    protected $model = Raffle::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence(3);
        $totalTickets = fake()->numberBetween(100, 10000);
        $ticketDigits = 5;
        $ticketMaxNumber = (int) str_repeat('9', $ticketDigits);

        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'description' => fake()->paragraphs(3, true),
            'short_description' => fake()->sentence(),
            'ticket_price' => fake()->numberBetween(1000, 50000), // in cents
            'total_tickets' => $totalTickets,
            'sold_tickets' => 0,
            'min_purchase_qty' => 1,
            'max_purchase_qty' => null,
            'max_per_user' => null,
            'allow_custom_quantity' => false,
            'quantity_step' => 1,
            'ticket_assignment_method' => TicketAssignmentMethod::Random,
            'ticket_digits' => $ticketDigits,
            'ticket_min_number' => 1,
            'ticket_max_number' => $ticketMaxNumber,
            'status' => RaffleStatus::Draft,
            'starts_at' => null,
            'ends_at' => null,
            'draw_at' => null,
            'lottery_source' => null,
            'lottery_reference' => null,
            'meta_title' => null,
            'meta_description' => null,
            'sort_order' => 0,
            'featured' => false,
        ];
    }
}
