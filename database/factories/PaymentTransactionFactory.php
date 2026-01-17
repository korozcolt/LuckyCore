<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PaymentProvider;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\PaymentTransaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PaymentTransaction>
 */
class PaymentTransactionFactory extends Factory
{
    protected $model = PaymentTransaction::class;

    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'provider' => PaymentProvider::Wompi,
            'provider_transaction_id' => null,
            'provider_reference' => null,
            'amount' => fake()->numberBetween(10000, 500000),
            'currency' => 'COP',
            'status' => PaymentStatus::Pending,
            'idempotency_key' => null,
            'webhook_received_at' => null,
            'webhook_attempts' => 0,
            'provider_request' => null,
            'provider_response' => null,
            'webhook_payload' => null,
            'error_code' => null,
            'error_message' => null,
            'initiated_at' => now(),
            'completed_at' => null,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PaymentStatus::Pending,
        ]);
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PaymentStatus::Approved,
            'completed_at' => now(),
            'provider_transaction_id' => 'txn_' . fake()->uuid(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PaymentStatus::Rejected,
            'completed_at' => now(),
            'error_code' => 'DECLINED',
            'error_message' => 'Transaction was declined',
        ]);
    }

    public function wompi(): static
    {
        return $this->state(fn (array $attributes) => [
            'provider' => PaymentProvider::Wompi,
        ]);
    }

    public function mercadoPago(): static
    {
        return $this->state(fn (array $attributes) => [
            'provider' => PaymentProvider::MercadoPago,
        ]);
    }

    public function epayco(): static
    {
        return $this->state(fn (array $attributes) => [
            'provider' => PaymentProvider::Epayco,
        ]);
    }
}
