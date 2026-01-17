<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = fake()->numberBetween(10000, 500000);
        $total = $subtotal;

        return [
            'order_number' => Order::generateOrderNumber(),
            'user_id' => User::factory(),
            'cart_id' => null,
            'subtotal' => $subtotal,
            'total' => $total,
            'status' => OrderStatus::Pending,
            'support_code' => Order::generateSupportCode(),
            'correlation_id' => null,
            'customer_email' => fake()->safeEmail(),
            'customer_name' => fake()->name(),
            'customer_phone' => fake()->phoneNumber(),
        ];
    }
}
