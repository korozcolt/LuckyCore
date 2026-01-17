<?php

declare(strict_types=1);

namespace App\Payments\DTOs;

use App\Enums\PaymentProvider;
use App\Models\PaymentTransaction;

/**
 * Data transfer object for payment intent data.
 *
 * Contains all the information needed by the frontend to render
 * the payment widget and complete the transaction.
 */
final readonly class PaymentIntentData
{
    public function __construct(
        public PaymentTransaction $transaction,
        public PaymentProvider $provider,
        public string $widgetUrl,
        public string $publicKey,
        public int $amountInCents,
        public string $currency,
        public string $reference,
        public string $signature,
        public string $redirectUrl,
        public array $extra = [],
    ) {}

    public function toArray(): array
    {
        return [
            'transaction_id' => $this->transaction->id,
            'transaction_ulid' => $this->transaction->ulid,
            'provider' => $this->provider->value,
            'widget_url' => $this->widgetUrl,
            'public_key' => $this->publicKey,
            'amount_in_cents' => $this->amountInCents,
            'currency' => $this->currency,
            'reference' => $this->reference,
            'signature' => $this->signature,
            'redirect_url' => $this->redirectUrl,
            'extra' => $this->extra,
        ];
    }
}
