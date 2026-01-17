<?php

declare(strict_types=1);

namespace App\Payments\Providers;

use App\Models\PaymentGateway;
use App\Payments\Contracts\PaymentProviderContract;
use App\Payments\Exceptions\PaymentProviderException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Abstract base class for payment providers.
 *
 * Provides common functionality shared across all payment providers.
 */
abstract class AbstractPaymentProvider implements PaymentProviderContract
{
    protected ?PaymentGateway $gateway = null;

    public function setGateway(PaymentGateway $gateway): static
    {
        $this->gateway = $gateway;

        return $this;
    }

    public function getGateway(): PaymentGateway
    {
        if (! $this->gateway) {
            throw new \RuntimeException('Gateway not set. Call setGateway() first.');
        }

        return $this->gateway;
    }

    public function isConfigured(): bool
    {
        return $this->gateway?->isConfigured() ?? false;
    }

    /**
     * Log payment-related information to the payments channel.
     */
    protected function log(string $level, string $message, array $context = []): void
    {
        $context['provider'] = $this->getProviderName();
        $context['sandbox'] = $this->gateway?->is_sandbox ?? true;

        Log::channel('payments')->{$level}($message, $context);
    }

    /**
     * Make an HTTP request to the provider's API.
     */
    protected function makeRequest(
        string $method,
        string $endpoint,
        array $data = [],
        array $headers = [],
    ): array {
        $baseUrl = $this->gateway->getBaseUrl();
        $url = rtrim($baseUrl, '/').'/'.ltrim($endpoint, '/');

        $this->log('debug', 'Making API request', [
            'method' => $method,
            'url' => $url,
            'data' => $data,
        ]);

        $response = Http::withHeaders($headers)
            ->timeout(30)
            ->{strtolower($method)}($url, $data);

        $responseData = $response->json() ?? [];

        $this->log('debug', 'API response received', [
            'status' => $response->status(),
            'response' => $responseData,
        ]);

        if ($response->failed()) {
            throw PaymentProviderException::apiError(
                provider: $this->gateway->provider,
                message: $responseData['error']['message'] ?? 'API request failed',
                context: [
                    'status' => $response->status(),
                    'response' => $responseData,
                ],
            );
        }

        return $responseData;
    }
}
