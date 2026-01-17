<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Raffle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderItem>
 */
class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = fake()->numberBetween(1, 10);
        $unitPrice = fake()->numberBetween(1000, 50000);
        $subtotal = $quantity * $unitPrice;

        return [
            'order_id' => Order::factory(),
            'raffle_id' => Raffle::factory(),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'subtotal' => $subtotal,
            'raffle_package_id' => null,
            'raffle_title' => fn (array $attributes) => Raffle::find($attributes['raffle_id'])->title ?? 'Test Raffle',
            'tickets_assigned' => 0,
            'tickets_complete' => false,
        ];
    }
}
