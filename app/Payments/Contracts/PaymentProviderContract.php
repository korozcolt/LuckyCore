<?php

declare(strict_types=1);

namespace App\Payments\Contracts;

use App\Models\Order;
use App\Models\PaymentGateway;
use App\Models\PaymentTransaction;
use App\Payments\DTOs\PaymentIntentData;
use App\Payments\DTOs\WebhookResult;
use Illuminate\Http\Request;

/**
 * Contract for payment providers.
 *
 * All payment providers (Wompi, MercadoPago, ePayco) must implement this interface
 * to ensure consistent behavior across the payment system.
 *
 * @see ARQUITECTURA.md §5 - PaymentProviderContract
 */
interface PaymentProviderContract
{
    /**
     * Set the gateway configuration for this provider.
     */
    public function setGateway(PaymentGateway $gateway): static;

    /**
     * Get the gateway configuration.
     */
    public function getGateway(): PaymentGateway;

    /**
     * Create a payment intent/transaction for the given order.
     *
     * This prepares the payment but does not process it yet.
     * Returns data needed by the frontend to complete the payment.
     */
    public function createPaymentIntent(Order $order): PaymentIntentData;

    /**
     * Get the widget/checkout configuration for frontend rendering.
     *
     * @return array{
     *     widget_url: string,
     *     public_key: string,
     *     amount_in_cents: int,
     *     currency: string,
     *     reference: string,
     *     signature: string,
     *     redirect_url: string,
     *     extra: array
     * }
     */
    public function getCheckoutConfig(Order $order, PaymentTransaction $transaction): array;

    /**
     * Verify the webhook signature.
     *
     * @throws \App\Payments\Exceptions\InvalidWebhookSignatureException
     */
    public function verifyWebhookSignature(Request $request): bool;

    /**
     * Process the webhook payload.
     *
     * This method should:
     * 1. Parse the webhook payload
     * 2. Find the corresponding transaction
     * 3. Update the transaction status
     * 4. Return the result
     */
    public function processWebhook(Request $request): WebhookResult;

    /**
     * Query the payment status directly from the provider's API.
     *
     * Useful for reconciliation or when webhooks fail.
     */
    public function queryPaymentStatus(PaymentTransaction $transaction): WebhookResult;

    /**
     * Check if this provider is properly configured and ready to process payments.
     */
    public function isConfigured(): bool;

    /**
     * Get the provider identifier.
     */
    public function getProviderName(): string;
}
