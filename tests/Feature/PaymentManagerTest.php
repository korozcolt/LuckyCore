<?php

use App\Enums\PaymentProvider;
use App\Models\PaymentGateway;
use App\Payments\Contracts\PaymentProviderContract;
use App\Payments\Exceptions\PaymentProviderException;
use App\Payments\PaymentManager;
use App\Payments\Providers\WompiProvider;

describe('PaymentManager', function () {
    beforeEach(function () {
        $this->manager = app(PaymentManager::class);
    });

    it('resolves a provider by name', function () {
        PaymentGateway::factory()->wompiConfigured()->active()->create();

        $provider = $this->manager->provider(PaymentProvider::Wompi);

        expect($provider)->toBeInstanceOf(PaymentProviderContract::class)
            ->and($provider)->toBeInstanceOf(WompiProvider::class);
    });

    it('resolves a provider by string', function () {
        PaymentGateway::factory()->wompiConfigured()->active()->create();

        $provider = $this->manager->provider('wompi');

        expect($provider)->toBeInstanceOf(WompiProvider::class);
    });

    it('throws exception when provider is not configured', function () {
        $this->manager->provider(PaymentProvider::Wompi);
    })->throws(PaymentProviderException::class);

    it('returns the default provider (first active)', function () {
        PaymentGateway::factory()->wompi()->create(['is_active' => false, 'sort_order' => 1]);
        PaymentGateway::factory()->mercadoPago()->active()->create(['sort_order' => 2]);

        $provider = $this->manager->defaultProvider();

        expect($provider->getProviderName())->toBe('mercadopago');
    });

    it('throws exception when no active provider exists', function () {
        PaymentGateway::factory()->wompi()->create(['is_active' => false]);

        $this->manager->defaultProvider();
    })->throws(PaymentProviderException::class, 'No active payment provider configured');

    it('returns all active providers', function () {
        PaymentGateway::factory()->wompiConfigured()->active()->create();
        PaymentGateway::factory()->mercadoPago()->create(['is_active' => false]);

        $providers = $this->manager->activeProviders();

        expect($providers)->toHaveCount(1)
            ->and($providers->first())->toBeInstanceOf(WompiProvider::class);
    });

    it('returns all active gateways', function () {
        PaymentGateway::factory()->wompi()->active()->create(['sort_order' => 2]);
        PaymentGateway::factory()->mercadoPago()->active()->create(['sort_order' => 1]);
        PaymentGateway::factory()->epayco()->create(['is_active' => false]);

        $gateways = $this->manager->activeGateways();

        expect($gateways)->toHaveCount(2)
            ->and($gateways->first()->provider)->toBe(PaymentProvider::MercadoPago);
    });

    it('checks if a provider is available', function () {
        PaymentGateway::factory()->wompiConfigured()->active()->create();

        expect($this->manager->isAvailable(PaymentProvider::Wompi))->toBeTrue()
            ->and($this->manager->isAvailable(PaymentProvider::MercadoPago))->toBeFalse();
    });

    it('checks if any provider is available', function () {
        expect($this->manager->hasActiveProvider())->toBeFalse();

        PaymentGateway::factory()->wompiConfigured()->active()->create();

        expect($this->manager->hasActiveProvider())->toBeTrue();
    });

    it('caches resolved providers', function () {
        PaymentGateway::factory()->wompiConfigured()->active()->create();

        $provider1 = $this->manager->provider('wompi');
        $provider2 = $this->manager->provider('wompi');

        expect($provider1)->toBe($provider2);
    });
});
