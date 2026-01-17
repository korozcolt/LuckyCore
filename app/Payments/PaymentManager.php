<?php

declare(strict_types=1);

namespace App\Payments;

use App\Enums\PaymentProvider;
use App\Models\PaymentGateway;
use App\Payments\Contracts\PaymentProviderContract;
use App\Payments\Exceptions\PaymentProviderException;
use App\Payments\Providers\EpaycoProvider;
use App\Payments\Providers\MercadoPagoProvider;
use App\Payments\Providers\WompiProvider;
use Illuminate\Support\Collection;

/**
 * Payment Manager - Resolves and manages payment providers.
 *
 * @see ARQUITECTURA.md §5 - PaymentManager para resolver provider según config
 */
class PaymentManager
{
    /**
     * @var array<string, class-string<PaymentProviderContract>>
     */
    protected array $providers = [
        'wompi' => WompiProvider::class,
        'mercadopago' => MercadoPagoProvider::class,
        'epayco' => EpaycoProvider::class,
    ];

    /**
     * @var array<string, PaymentProviderContract>
     */
    protected array $resolvedProviders = [];

    /**
     * Get a payment provider instance by name.
     *
     * @throws PaymentProviderException
     */
    public function provider(PaymentProvider|string $provider): PaymentProviderContract
    {
        $providerName = $provider instanceof PaymentProvider
            ? $provider->value
            : $provider;

        if (isset($this->resolvedProviders[$providerName])) {
            return $this->resolvedProviders[$providerName];
        }

        $gateway = $this->getGatewayConfig($providerName);

        if (! $gateway) {
            throw PaymentProviderException::notConfigured(
                PaymentProvider::from($providerName)
            );
        }

        return $this->resolvedProviders[$providerName] = $this->createProvider($gateway);
    }

    /**
     * Get the default (first active) payment provider.
     *
     * @throws PaymentProviderException
     */
    public function defaultProvider(): PaymentProviderContract
    {
        $gateway = PaymentGateway::query()
            ->active()
            ->ordered()
            ->first();

        if (! $gateway) {
            throw new PaymentProviderException(
                message: 'No active payment provider configured',
                provider: PaymentProvider::Wompi, // Default for error context
                errorCode: 'NO_ACTIVE_PROVIDER',
            );
        }

        return $this->provider($gateway->provider);
    }

    /**
     * Get all active payment providers.
     *
     * @return Collection<int, PaymentProviderContract>
     */
    public function activeProviders(): Collection
    {
        return PaymentGateway::query()
            ->active()
            ->ordered()
            ->get()
            ->map(fn (PaymentGateway $gateway) => $this->createProvider($gateway));
    }

    /**
     * Get all active gateways (for checkout display).
     *
     * @return Collection<int, PaymentGateway>
     */
    public function activeGateways(): Collection
    {
        return PaymentGateway::query()
            ->active()
            ->ordered()
            ->get();
    }

    /**
     * Check if a specific provider is available and configured.
     */
    public function isAvailable(PaymentProvider|string $provider): bool
    {
        $providerName = $provider instanceof PaymentProvider
            ? $provider->value
            : $provider;

        $gateway = $this->getGatewayConfig($providerName);

        return $gateway?->canProcess() ?? false;
    }

    /**
     * Check if any payment provider is available.
     */
    public function hasActiveProvider(): bool
    {
        return PaymentGateway::query()
            ->active()
            ->whereNotNull('credentials')
            ->exists();
    }

    /**
     * Get gateway configuration from database.
     */
    protected function getGatewayConfig(string $providerName): ?PaymentGateway
    {
        return PaymentGateway::query()
            ->where('provider', $providerName)
            ->first();
    }

    /**
     * Create a provider instance with gateway configuration.
     */
    protected function createProvider(PaymentGateway $gateway): PaymentProviderContract
    {
        $providerClass = $this->providers[$gateway->provider->value]
            ?? throw new PaymentProviderException(
                message: "Unknown payment provider: {$gateway->provider->value}",
                provider: $gateway->provider,
                errorCode: 'UNKNOWN_PROVIDER',
            );

        /** @var PaymentProviderContract $provider */
        $provider = app($providerClass);

        return $provider->setGateway($gateway);
    }

    /**
     * Register a custom provider.
     *
     * @param  class-string<PaymentProviderContract>  $providerClass
     */
    public function extend(string $name, string $providerClass): void
    {
        $this->providers[$name] = $providerClass;
    }
}
