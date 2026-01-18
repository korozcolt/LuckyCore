<?php

declare(strict_types=1);

namespace App\Payments\Providers;

use App\Enums\PaymentProvider;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\OrderEvent;
use App\Models\PaymentTransaction;
use App\Payments\DTOs\PaymentIntentData;
use App\Payments\DTOs\WebhookResult;
use App\Payments\Exceptions\InvalidWebhookSignatureException;
use App\Services\TicketAssignmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Wompi Payment Provider Implementation.
 *
 * @see https://docs.wompi.co/docs/colombia/widget-checkout-web/
 * @see https://docs.wompi.co/docs/colombia/eventos/
 */
class WompiProvider extends AbstractPaymentProvider
{
    /**
     * Map Wompi transaction statuses to internal statuses.
     */
    protected const array STATUS_MAP = [
        'PENDING' => PaymentStatus::Pending,
        'APPROVED' => PaymentStatus::Approved,
        'DECLINED' => PaymentStatus::Rejected,
        'VOIDED' => PaymentStatus::Voided,
        'ERROR' => PaymentStatus::Rejected,
    ];

    public function getProviderName(): string
    {
        return PaymentProvider::Wompi->value;
    }

    public function createPaymentIntent(Order $order): PaymentIntentData
    {
        $this->log('info', 'Creating payment intent', [
            'order_id' => $order->id,
            'amount' => $order->total,
        ]);

        // Create transaction record
        $transaction = DB::transaction(function () use ($order) {
            return PaymentTransaction::create([
                'order_id' => $order->id,
                'provider' => PaymentProvider::Wompi,
                'amount' => $order->total,
                'currency' => 'COP',
                'status' => PaymentStatus::Pending,
                'idempotency_key' => (string) Str::uuid(),
                'initiated_at' => now(),
            ]);
        });

        $reference = $this->generateReference($order, $transaction);
        $signature = $this->generateIntegritySignature(
            reference: $reference,
            amountInCents: $order->total,
            currency: 'COP',
        );

        $redirectUrl = route('payments.callback', [
            'provider' => 'wompi',
            'order' => $order->ulid,
        ]);

        $this->log('info', 'Payment intent created', [
            'transaction_id' => $transaction->id,
            'reference' => $reference,
        ]);

        return new PaymentIntentData(
            transaction: $transaction,
            provider: PaymentProvider::Wompi,
            widgetUrl: $this->getGateway()->getWidgetUrl(),
            publicKey: $this->getGateway()->public_key,
            amountInCents: $order->total,
            currency: 'COP',
            reference: $reference,
            signature: $signature,
            redirectUrl: $redirectUrl,
            extra: [
                'customer_email' => $order->customer_email,
                'customer_name' => $order->customer_name,
                'customer_phone' => $order->customer_phone,
            ],
        );
    }

    public function getCheckoutConfig(Order $order, PaymentTransaction $transaction): array
    {
        $reference = $this->generateReference($order, $transaction);
        $signature = $this->generateIntegritySignature(
            reference: $reference,
            amountInCents: $order->total,
            currency: 'COP',
        );

        return [
            'widget_url' => $this->getGateway()->getWidgetUrl(),
            'public_key' => $this->getGateway()->public_key,
            'amount_in_cents' => $order->total,
            'currency' => 'COP',
            'reference' => $reference,
            'signature' => $signature,
            'redirect_url' => route('payments.callback', [
                'provider' => 'wompi',
                'order' => $order->ulid,
            ]),
            'extra' => [
                'customer_email' => $order->customer_email,
                'customer_name' => $order->customer_name,
            ],
        ];
    }

    public function verifyWebhookSignature(Request $request): bool
    {
        $payload = $request->all();

        $this->log('debug', 'Verifying webhook signature', [
            'event' => $payload['event'] ?? 'unknown',
        ]);

        // Get the signature data from payload
        $signature = $payload['signature'] ?? null;
        if (! $signature) {
            throw new InvalidWebhookSignatureException('Missing signature in webhook payload');
        }

        $properties = $signature['properties'] ?? [];
        $timestamp = $signature['timestamp'] ?? null;
        $checksum = $signature['checksum'] ?? null;

        if (! $properties || ! $timestamp || ! $checksum) {
            throw new InvalidWebhookSignatureException('Incomplete signature data');
        }

        // Build the string to hash following Wompi's specification
        $data = $payload['data']['transaction'] ?? $payload['data'] ?? [];
        $stringToHash = '';

        foreach ($properties as $property) {
            $value = data_get($data, $property, '');
            $stringToHash .= $value;
        }

        $stringToHash .= $timestamp;
        $stringToHash .= $this->getGateway()->events_secret;

        // Generate SHA256 hash
        $expectedChecksum = hash('sha256', $stringToHash);

        if (! hash_equals($expectedChecksum, $checksum)) {
            $this->log('warning', 'Invalid webhook signature', [
                'expected' => $expectedChecksum,
                'received' => $checksum,
            ]);

            throw new InvalidWebhookSignatureException(
                message: 'Webhook signature verification failed',
                expectedSignature: $expectedChecksum,
                receivedSignature: $checksum,
            );
        }

        $this->log('debug', 'Webhook signature verified successfully');

        return true;
    }

    public function processWebhook(Request $request): WebhookResult
    {
        $payload = $request->all();

        $this->log('info', 'Processing webhook', [
            'event' => $payload['event'] ?? 'unknown',
        ]);

        // Extract transaction data
        $transactionData = $payload['data']['transaction'] ?? $payload['data'] ?? [];
        $reference = $transactionData['reference'] ?? null;
        $providerTransactionId = $transactionData['id'] ?? null;
        $wompiStatus = $transactionData['status'] ?? null;

        if (! $reference) {
            $this->log('error', 'Missing reference in webhook payload');

            return WebhookResult::failed(
                transaction: null,
                errorCode: 'MISSING_REFERENCE',
                errorMessage: 'Missing reference in webhook payload',
                rawPayload: $payload,
            );
        }

        // Find the transaction by reference
        $transaction = $this->findTransactionByReference($reference);

        if (! $transaction) {
            $this->log('warning', 'Transaction not found for reference', [
                'reference' => $reference,
            ]);

            return WebhookResult::notFound($reference, $payload);
        }

        // Check for idempotency - if already processed, skip
        if ($transaction->webhook_received_at && $transaction->isFinal()) {
            $this->log('info', 'Webhook already processed (idempotency)', [
                'transaction_id' => $transaction->id,
            ]);

            return WebhookResult::duplicate($transaction, $payload);
        }

        // Map Wompi status to internal status
        $status = self::STATUS_MAP[$wompiStatus] ?? PaymentStatus::Pending;

        // Update transaction
        DB::transaction(function () use ($transaction, $status, $providerTransactionId, $payload) {
            $transaction->update([
                'status' => $status,
                'provider_transaction_id' => $providerTransactionId,
                'webhook_payload' => $payload,
                'webhook_received_at' => now(),
                'webhook_attempts' => $transaction->webhook_attempts + 1,
                'completed_at' => $status->isFinal() ? now() : null,
            ]);

            OrderEvent::log(
                order: $transaction->order,
                eventType: OrderEvent::WEBHOOK_RECEIVED,
                description: 'Webhook recibido desde Wompi',
                metadata: [
                    'provider_transaction_id' => $providerTransactionId,
                    'status' => $status->value,
                ],
                transaction: $transaction,
                actorType: OrderEvent::ACTOR_WEBHOOK,
            );

            // Update order status if payment approved
            if ($status === PaymentStatus::Approved) {
                $transaction->order->update([
                    'status' => \App\Enums\OrderStatus::Paid,
                    'paid_at' => now(),
                ]);

                OrderEvent::log(
                    order: $transaction->order,
                    eventType: OrderEvent::PAYMENT_APPROVED,
                    description: 'Pago aprobado',
                    metadata: [
                        'provider_transaction_id' => $providerTransactionId,
                    ],
                    transaction: $transaction,
                    actorType: OrderEvent::ACTOR_WEBHOOK,
                );

                app(TicketAssignmentService::class)->assignForOrder($transaction->order);

                $transaction->order->refresh();
                if ($transaction->order->allTicketsAssigned()) {
                    OrderEvent::log(
                        order: $transaction->order,
                        eventType: OrderEvent::ORDER_COMPLETED,
                        description: 'Orden completada',
                        actorType: OrderEvent::ACTOR_SYSTEM,
                    );
                }
            }
        });

        $this->log('info', 'Webhook processed successfully', [
            'transaction_id' => $transaction->id,
            'status' => $status->value,
        ]);

        return WebhookResult::success(
            transaction: $transaction->fresh(),
            status: $status,
            providerTransactionId: $providerTransactionId,
            rawPayload: $payload,
        );
    }

    public function queryPaymentStatus(PaymentTransaction $transaction): WebhookResult
    {
        $this->log('info', 'Querying payment status from API', [
            'transaction_id' => $transaction->id,
        ]);

        if (! $transaction->provider_transaction_id) {
            return WebhookResult::failed(
                transaction: $transaction,
                errorCode: 'NO_PROVIDER_ID',
                errorMessage: 'No provider transaction ID available for query',
            );
        }

        try {
            $response = $this->makeRequest(
                method: 'GET',
                endpoint: "/transactions/{$transaction->provider_transaction_id}",
                headers: [
                    'Authorization' => 'Bearer '.$this->getGateway()->private_key,
                ],
            );

            $data = $response['data'] ?? [];
            $wompiStatus = $data['status'] ?? null;
            $status = self::STATUS_MAP[$wompiStatus] ?? PaymentStatus::Pending;

            // Update transaction if status changed
            if ($transaction->status !== $status) {
                $transaction->update([
                    'status' => $status,
                    'provider_response' => $response,
                    'completed_at' => $status->isFinal() ? now() : null,
                ]);

                if ($status === PaymentStatus::Approved && (! $transaction->order->isPaid())) {
                    DB::transaction(function () use ($transaction): void {
                        $transaction->order->update([
                            'status' => \App\Enums\OrderStatus::Paid,
                            'paid_at' => now(),
                        ]);

                        app(TicketAssignmentService::class)->assignForOrder($transaction->order);
                    });
                }
            }

            return WebhookResult::success(
                transaction: $transaction->fresh(),
                status: $status,
                providerTransactionId: $data['id'] ?? null,
                rawPayload: $response,
            );

        } catch (\Exception $e) {
            $this->log('error', 'Failed to query payment status', [
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage(),
            ]);

            return WebhookResult::failed(
                transaction: $transaction,
                errorCode: 'API_ERROR',
                errorMessage: $e->getMessage(),
            );
        }
    }

    /**
     * Generate a unique reference for the transaction.
     *
     * Format: ORD-{order_ulid}-TXN-{transaction_id}
     */
    protected function generateReference(Order $order, PaymentTransaction $transaction): string
    {
        return "ORD-{$order->ulid}-TXN-{$transaction->id}";
    }

    /**
     * Generate the integrity signature for Wompi widget.
     *
     * @see https://docs.wompi.co/docs/colombia/widget-checkout-web/
     */
    protected function generateIntegritySignature(
        string $reference,
        int $amountInCents,
        string $currency,
    ): string {
        $integritySecret = $this->getGateway()->integrity_secret;

        // Concatenate: reference + amount + currency + secret
        $stringToHash = $reference.$amountInCents.$currency.$integritySecret;

        return hash('sha256', $stringToHash);
    }

    /**
     * Find a transaction by its reference.
     */
    protected function findTransactionByReference(string $reference): ?PaymentTransaction
    {
        // Reference format: ORD-{order_ulid}-TXN-{transaction_id}
        if (preg_match('/^ORD-(.+)-TXN-(\d+)$/', $reference, $matches)) {
            $transactionId = $matches[2];

            return PaymentTransaction::find($transactionId);
        }

        return null;
    }
}
