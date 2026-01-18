<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PaymentProvider;
use App\Models\PaymentGateway;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PaymentGateway>
 */
class PaymentGatewayFactory extends Factory
{
    protected $model = PaymentGateway::class;

    public function definition(): array
    {
        return [
            'provider' => PaymentProvider::Wompi,
            'display_name' => 'Wompi',
            'is_active' => false,
            'is_sandbox' => true,
            'credentials' => null,
            'logo_url' => null,
            'description' => null,
            'sort_order' => 0,
            'metadata' => null,
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    public function production(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_sandbox' => false,
        ]);
    }

    public function wompi(): static
    {
        return $this->state(fn (array $attributes) => [
            'provider' => PaymentProvider::Wompi,
            'display_name' => 'Wompi',
            'description' => 'Paga con tarjeta de crédito, débito, PSE, Nequi y más',
        ]);
    }

    public function wompiConfigured(): static
    {
        return $this->wompi()->state(fn (array $attributes) => [
            'credentials' => [
                'public_key' => 'pub_test_xxxxxxxxxx',
                'private_key' => 'prv_test_xxxxxxxxxx',
                'events_secret' => 'test_events_xxxxxxxxxx',
                'integrity_secret' => 'test_integrity_xxxxxxxxxx',
            ],
        ]);
    }

    public function mercadoPago(): static
    {
        return $this->state(fn (array $attributes) => [
            'provider' => PaymentProvider::MercadoPago,
            'display_name' => 'MercadoPago',
            'description' => 'Paga con MercadoPago',
        ]);
    }

    public function mercadoPagoConfigured(): static
    {
        return $this->mercadoPago()->state(fn (array $attributes) => [
            'credentials' => [
                'access_token' => 'TEST-1234567890123456-123456-abcdefghijklmnopqrstuvwxyz123456-123456789',
                'webhook_secret' => 'test_webhook_secret_xxxxxxxxxx',
            ],
        ]);
    }

    public function epayco(): static
    {
        return $this->state(fn (array $attributes) => [
            'provider' => PaymentProvider::Epayco,
            'display_name' => 'ePayco',
            'description' => 'Paga con tarjeta, PSE, Efecty y más',
        ]);
    }

    public function epaycoConfigured(): static
    {
        return $this->epayco()->state(fn (array $attributes) => [
            'credentials' => [
                'public_key' => 'test_public_key_xxxxxxxxxx',
                'private_key' => 'test_private_key_xxxxxxxxxx',
                'p_cust_id_cliente' => '123456',
                'p_key' => 'test_p_key_xxxxxxxxxx',
            ],
        ]);
    }
}
