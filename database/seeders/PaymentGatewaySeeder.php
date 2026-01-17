<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\PaymentProvider;
use App\Models\PaymentGateway;
use Illuminate\Database\Seeder;

/**
 * Seeder for payment gateways configuration.
 *
 * Creates the three supported payment providers with sandbox credentials.
 */
class PaymentGatewaySeeder extends Seeder
{
    public function run(): void
    {
        // Wompi - Active and configured for testing
        PaymentGateway::updateOrCreate(
            ['provider' => PaymentProvider::Wompi],
            [
                'display_name' => 'Wompi',
                'description' => 'Paga con tarjeta de crédito, débito, PSE, Nequi y más',
                'is_active' => true,
                'is_sandbox' => true,
                'sort_order' => 1,
                'credentials' => [
                    'public_key' => 'pub_test_rlJ9XTDsOVo6csv7tPtIfKSAbyu6NYHf',
                    'private_key' => 'prv_test_xy3IXHaPJn9GnkBluLfyORfisXfxHKg3',
                    'events_secret' => 'test_events_hdGohLcr3068xdbqqxoIk5fYZVuvEPbl',
                    'integrity_secret' => 'test_integrity_z3lG4Wv2AKkECtjOUJJ2MPVWgwm3PGsn',
                ],
            ]
        );

        // MercadoPago - Inactive, to be configured later
        PaymentGateway::updateOrCreate(
            ['provider' => PaymentProvider::MercadoPago],
            [
                'display_name' => 'MercadoPago',
                'description' => 'Paga con MercadoPago',
                'is_active' => false,
                'is_sandbox' => true,
                'sort_order' => 2,
                'credentials' => null,
            ]
        );

        // ePayco - Inactive, to be configured later
        PaymentGateway::updateOrCreate(
            ['provider' => PaymentProvider::Epayco],
            [
                'display_name' => 'ePayco',
                'description' => 'Paga con ePayco',
                'is_active' => false,
                'is_sandbox' => true,
                'sort_order' => 3,
                'credentials' => null,
            ]
        );
    }
}
