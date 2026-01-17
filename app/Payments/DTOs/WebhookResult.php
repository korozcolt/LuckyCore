<?php

declare(strict_types=1);

namespace App\Payments\DTOs;

use App\Enums\PaymentStatus;
use App\Models\PaymentTransaction;

/**
 * Data transfer object for webhook processing results.
 */
final readonly class WebhookResult
{
    public function __construct(
        public bool $success,
        public ?PaymentTransaction $transaction,
        public ?PaymentStatus $status,
        public ?string $providerTransactionId = null,
        public ?string $providerReference = null,
        public ?string $errorCode = null,
        public ?string $errorMessage = null,
        public array $rawPayload = [],
    ) {}

    public static function success(
        PaymentTransaction $transaction,
        PaymentStatus $status,
        ?string $providerTransactionId = null,
        ?string $providerReference = null,
        array $rawPayload = [],
    ): self {
        return new self(
            success: true,
            transaction: $transaction,
            status: $status,
            providerTransactionId: $providerTransactionId,
            providerReference: $providerReference,
            rawPayload: $rawPayload,
        );
    }

    public static function failed(
        ?PaymentTransaction $transaction,
        string $errorCode,
        string $errorMessage,
        array $rawPayload = [],
    ): self {
        return new self(
            success: false,
            transaction: $transaction,
            status: null,
            errorCode: $errorCode,
            errorMessage: $errorMessage,
            rawPayload: $rawPayload,
        );
    }

    public static function notFound(string $reference, array $rawPayload = []): self
    {
        return new self(
            success: false,
            transaction: null,
            status: null,
            errorCode: 'TRANSACTION_NOT_FOUND',
            errorMessage: "Transaction not found for reference: {$reference}",
            rawPayload: $rawPayload,
        );
    }

    public static function duplicate(PaymentTransaction $transaction, array $rawPayload = []): self
    {
        return new self(
            success: true,
            transaction: $transaction,
            status: $transaction->status,
            errorCode: 'DUPLICATE_WEBHOOK',
            errorMessage: 'Webhook already processed (idempotency)',
            rawPayload: $rawPayload,
        );
    }
}
