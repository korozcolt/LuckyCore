<?php

declare(strict_types=1);

namespace App\Payments\Providers;

use App\Enums\PaymentProvider;
use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Payments\DTOs\PaymentIntentData;
use App\Payments\DTOs\WebhookResult;
use App\Payments\Exceptions\PaymentProviderException;
use Illuminate\Http\Request;

/**
 * MercadoPago Payment Provider (Stub - To be implemented in Sprint 5).
 *
 * @see https://www.mercadopago.com.co/developers/es/docs
 */
class MercadoPagoProvider extends AbstractPaymentProvider
{
    public function getProviderName(): string
    {
        return PaymentProvider::MercadoPago->value;
    }

    public function createPaymentIntent(Order $order): PaymentIntentData
    {
        throw PaymentProviderException::notImplemented(PaymentProvider::MercadoPago);
    }

    public function getCheckoutConfig(Order $order, PaymentTransaction $transaction): array
    {
        throw PaymentProviderException::notImplemented(PaymentProvider::MercadoPago);
    }

    public function verifyWebhookSignature(Request $request): bool
    {
        throw PaymentProviderException::notImplemented(PaymentProvider::MercadoPago);
    }

    public function processWebhook(Request $request): WebhookResult
    {
        throw PaymentProviderException::notImplemented(PaymentProvider::MercadoPago);
    }

    public function queryPaymentStatus(PaymentTransaction $transaction): WebhookResult
    {
        throw PaymentProviderException::notImplemented(PaymentProvider::MercadoPago);
    }
}
