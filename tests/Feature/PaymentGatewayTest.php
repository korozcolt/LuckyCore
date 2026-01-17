<?php

use App\Enums\PaymentProvider;
use App\Models\PaymentGateway;

describe('PaymentGateway Model', function () {
    it('can create a payment gateway', function () {
        $gateway = PaymentGateway::factory()->wompi()->create();

        expect($gateway)->toBeInstanceOf(PaymentGateway::class)
            ->and($gateway->provider)->toBe(PaymentProvider::Wompi)
            ->and($gateway->display_name)->toBe('Wompi');
    });

    it('encrypts credentials when stored', function () {
        $gateway = PaymentGateway::factory()->wompiConfigured()->create();

        // Refresh to ensure we're reading from database
        $gateway->refresh();

        expect($gateway->credentials)->toBeInstanceOf(\Illuminate\Database\Eloquent\Casts\ArrayObject::class)
            ->and($gateway->public_key)->toBe('pub_test_xxxxxxxxxx');
    });

    it('checks if gateway is configured', function () {
        $unconfigured = PaymentGateway::factory()->wompi()->create();

        expect($unconfigured->isConfigured())->toBeFalse();

        // Update the same gateway with credentials
        $unconfigured->update([
            'credentials' => [
                'public_key' => 'pub_test_xxx',
                'integrity_secret' => 'test_integrity_xxx',
            ],
        ]);

        expect($unconfigured->fresh()->isConfigured())->toBeTrue();
    });

    it('checks if gateway can process payments', function () {
        $gateway = PaymentGateway::factory()->wompiConfigured()->create(['is_active' => false]);

        expect($gateway->canProcess())->toBeFalse();

        $gateway->update(['is_active' => true]);

        expect($gateway->fresh()->canProcess())->toBeTrue();
    });

    it('returns correct base url for sandbox mode', function () {
        $gateway = PaymentGateway::factory()->wompi()->create(['is_sandbox' => true]);

        expect($gateway->getBaseUrl())->toBe('https://sandbox.wompi.co/v1');

        $gateway->update(['is_sandbox' => false]);

        expect($gateway->fresh()->getBaseUrl())->toBe('https://production.wompi.co/v1');
    });

    it('scopes to active gateways', function () {
        PaymentGateway::factory()->wompi()->create(['is_active' => false]);
        PaymentGateway::factory()->mercadoPago()->active()->create();

        $active = PaymentGateway::active()->get();

        expect($active)->toHaveCount(1)
            ->and($active->first()->provider)->toBe(PaymentProvider::MercadoPago);
    });

    it('orders gateways by sort_order', function () {
        PaymentGateway::factory()->epayco()->create(['sort_order' => 3]);
        PaymentGateway::factory()->wompi()->create(['sort_order' => 1]);
        PaymentGateway::factory()->mercadoPago()->create(['sort_order' => 2]);

        $ordered = PaymentGateway::ordered()->get();

        expect($ordered->pluck('provider')->toArray())->toBe([
            PaymentProvider::Wompi,
            PaymentProvider::MercadoPago,
            PaymentProvider::Epayco,
        ]);
    });
});
