<?php

declare(strict_types=1);

namespace App\Payments\Providers;

use App\Enums\OrderStatus;
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
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\MercadoPagoConfig;

/**
 * MercadoPago Payment Provider Implementation.
 *
 * @see https://www.mercadopago.com.co/developers/es/docs
 * @see https://github.com/mercadopago/sdk-php
 */
class MercadoPagoProvider extends AbstractPaymentProvider
{
    /**
     * Map MercadoPago payment statuses to internal statuses.
     */
    protected const array STATUS_MAP = [
        'pending' => PaymentStatus::Pending,
        'approved' => PaymentStatus::Approved,
        'authorized' => PaymentStatus::Pending,
        'in_process' => PaymentStatus::Pending,
        'in_mediation' => PaymentStatus::Pending,
        'rejected' => PaymentStatus::Rejected,
        'cancelled' => PaymentStatus::Voided,
        'refunded' => PaymentStatus::Refunded,
        'charged_back' => PaymentStatus::Refunded,
    ];

    public function getProviderName(): string
    {
        return PaymentProvider::MercadoPago->value;
    }

    public function createPaymentIntent(Order $order): PaymentIntentData
    {
        $this->log('info', 'Creating payment intent', [
            'order_id' => $order->id,
            'amount' => $order->total,
        ]);

        // Configure MercadoPago SDK
        $this->configureSdk();

        // Create transaction record
        $transaction = DB::transaction(function () use ($order) {
            return PaymentTransaction::create([
                'order_id' => $order->id,
                'provider' => PaymentProvider::MercadoPago,
                'amount' => $order->total,
                'currency' => 'COP',
                'status' => PaymentStatus::Pending,
                'idempotency_key' => (string) Str::uuid(),
                'initiated_at' => now(),
            ]);
        });

        $reference = $this->generateReference($order, $transaction);
        $redirectUrl = route('payments.callback', [
            'provider' => 'mercadopago',
            'order' => $order->ulid,
        ]);

        // Create MercadoPago Preference
        $preferenceClient = new PreferenceClient;

        $preferenceData = [
            'items' => $this->buildItems($order),
            'payer' => [
                'email' => $order->customer_email,
                'name' => $order->customer_name,
            ],
            'back_urls' => [
                'success' => $redirectUrl.'?status=approved',
                'failure' => $redirectUrl.'?status=rejected',
                'pending' => $redirectUrl.'?status=pending',
            ],
            'auto_return' => 'approved',
            'external_reference' => $reference,
            'notification_url' => route('webhooks.payments.handle', ['provider' => 'mercadopago']),
            'statement_descriptor' => config('app.name'),
        ];

        try {
            $preference = $preferenceClient->create($preferenceData);

            // Store preference ID in transaction
            $transaction->update([
                'provider_transaction_id' => $preference->id,
            ]);

            $this->log('info', 'Payment intent created', [
                'transaction_id' => $transaction->id,
                'preference_id' => $preference->id,
                'reference' => $reference,
            ]);

            return new PaymentIntentData(
                transaction: $transaction->fresh(),
                provider: PaymentProvider::MercadoPago,
                widgetUrl: $this->getGateway()->getWidgetUrl(),
                publicKey: $this->getPublicKey(),
                amountInCents: $order->total,
                currency: 'COP',
                reference: $reference,
                signature: $preference->id, // Use preference ID as signature
                redirectUrl: $redirectUrl,
                extra: [
                    'preference_id' => $preference->id,
                    'init_point' => $this->getGateway()->is_sandbox
                        ? $preference->sandbox_init_point
                        : $preference->init_point,
                    'customer_email' => $order->customer_email,
                    'customer_name' => $order->customer_name,
                ],
            );
        } catch (\Exception $e) {
            $this->log('error', 'Failed to create preference', [
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function getCheckoutConfig(Order $order, PaymentTransaction $transaction): array
    {
        $this->configureSdk();

        $reference = $this->generateReference($order, $transaction);
        $redirectUrl = route('payments.callback', [
            'provider' => 'mercadopago',
            'order' => $order->ulid,
        ]);

        return [
            'widget_url' => $this->getGateway()->getWidgetUrl(),
            'public_key' => $this->getPublicKey(),
            'amount_in_cents' => $order->total,
            'currency' => 'COP',
            'reference' => $reference,
            'signature' => $transaction->provider_transaction_id ?? '',
            'redirect_url' => $redirectUrl,
            'extra' => [
                'preference_id' => $transaction->provider_transaction_id,
                'customer_email' => $order->customer_email,
                'customer_name' => $order->customer_name,
            ],
        ];
    }

    public function verifyWebhookSignature(Request $request): bool
    {
        $this->log('debug', 'Verifying webhook signature');

        // MercadoPago uses x-signature header for webhook verification
        $xSignature = $request->header('x-signature');
        $xRequestId = $request->header('x-request-id');

        if (! $xSignature || ! $xRequestId) {
            // For IPN notifications, MercadoPago doesn't always send signature
            // We'll verify by fetching the payment directly
            $this->log('debug', 'No signature header, will verify via API');

            return true;
        }

        $webhookSecret = $this->getGateway()->webhook_secret;
        if (! $webhookSecret) {
            throw new InvalidWebhookSignatureException('Webhook secret not configured');
        }

        // Parse x-signature header (format: ts=timestamp,v1=signature)
        $parts = [];
        foreach (explode(',', $xSignature) as $part) {
            $segments = explode('=', $part, 2);
            if (count($segments) === 2) {
                $parts[$segments[0]] = $segments[1];
            }
        }

        $timestamp = $parts['ts'] ?? null;
        $receivedSignature = $parts['v1'] ?? null;

        if (! $timestamp || ! $receivedSignature) {
            throw new InvalidWebhookSignatureException('Invalid signature format');
        }

        // Build the signed payload
        $dataId = $request->input('data.id');
        $manifest = "id:{$dataId};request-id:{$xRequestId};ts:{$timestamp};";

        $expectedSignature = hash_hmac('sha256', $manifest, $webhookSecret);

        if (! hash_equals($expectedSignature, $receivedSignature)) {
            $this->log('warning', 'Invalid webhook signature', [
                'expected' => $expectedSignature,
                'received' => $receivedSignature,
            ]);

            throw new InvalidWebhookSignatureException(
                message: 'Webhook signature verification failed',
                expectedSignature: $expectedSignature,
                receivedSignature: $receivedSignature,
            );
        }

        $this->log('debug', 'Webhook signature verified successfully');

        return true;
    }

    public function processWebhook(Request $request): WebhookResult
    {
        $payload = $request->all();

        $this->log('info', 'Processing webhook', [
            'type' => $payload['type'] ?? $payload['topic'] ?? 'unknown',
        ]);

        // MercadoPago sends different webhook types
        $type = $payload['type'] ?? $payload['topic'] ?? null;
        $dataId = $payload['data']['id'] ?? $payload['id'] ?? null;

        // Only process payment notifications
        if (! in_array($type, ['payment', 'payment.created', 'payment.updated'])) {
            $this->log('debug', 'Ignoring non-payment webhook', ['type' => $type]);

            return WebhookResult::failed(
                transaction: null,
                errorCode: 'IGNORED_WEBHOOK_TYPE',
                errorMessage: "Webhook type '{$type}' is not a payment notification",
                rawPayload: $payload,
            );
        }

        if (! $dataId) {
            $this->log('error', 'Missing data ID in webhook payload');

            return WebhookResult::failed(
                transaction: null,
                errorCode: 'MISSING_DATA_ID',
                errorMessage: 'Missing data ID in webhook payload',
                rawPayload: $payload,
            );
        }

        // Fetch payment details from MercadoPago API
        $this->configureSdk();
        $paymentClient = new PaymentClient;

        try {
            $payment = $paymentClient->get((int) $dataId);
        } catch (\Exception $e) {
            $this->log('error', 'Failed to fetch payment from API', [
                'payment_id' => $dataId,
                'error' => $e->getMessage(),
            ]);

            return WebhookResult::failed(
                transaction: null,
                errorCode: 'API_ERROR',
                errorMessage: $e->getMessage(),
                rawPayload: $payload,
            );
        }

        $reference = $payment->external_reference ?? null;

        if (! $reference) {
            $this->log('error', 'Missing reference in payment');

            return WebhookResult::failed(
                transaction: null,
                errorCode: 'MISSING_REFERENCE',
                errorMessage: 'Missing external reference in payment',
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

        // Check for idempotency
        if ($transaction->webhook_received_at && $transaction->isFinal()) {
            $this->log('info', 'Webhook already processed (idempotency)', [
                'transaction_id' => $transaction->id,
            ]);

            return WebhookResult::duplicate($transaction, $payload);
        }

        // Map MercadoPago status to internal status
        $mpStatus = $payment->status ?? 'pending';
        $status = self::STATUS_MAP[$mpStatus] ?? PaymentStatus::Pending;

        // Update transaction
        DB::transaction(function () use ($transaction, $status, $payment, $payload) {
            $transaction->update([
                'status' => $status,
                'provider_transaction_id' => (string) $payment->id,
                'webhook_payload' => $payload,
                'webhook_received_at' => now(),
                'webhook_attempts' => $transaction->webhook_attempts + 1,
                'completed_at' => $status->isFinal() ? now() : null,
            ]);

            OrderEvent::log(
                order: $transaction->order,
                eventType: OrderEvent::WEBHOOK_RECEIVED,
                description: 'Webhook recibido desde MercadoPago',
                metadata: [
                    'provider_transaction_id' => (string) $payment->id,
                    'status' => $status->value,
                    'mp_status' => $payment->status,
                ],
                transaction: $transaction,
                actorType: OrderEvent::ACTOR_WEBHOOK,
            );

            // Update order status if payment approved
            if ($status === PaymentStatus::Approved) {
                $transaction->order->update([
                    'status' => OrderStatus::Paid,
                    'paid_at' => now(),
                ]);

                OrderEvent::log(
                    order: $transaction->order,
                    eventType: OrderEvent::PAYMENT_APPROVED,
                    description: 'Pago aprobado',
                    metadata: [
                        'provider_transaction_id' => (string) $payment->id,
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
            providerTransactionId: (string) $payment->id,
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

        $this->configureSdk();

        try {
            $paymentClient = new PaymentClient;
            $payment = $paymentClient->get((int) $transaction->provider_transaction_id);

            $mpStatus = $payment->status ?? 'pending';
            $status = self::STATUS_MAP[$mpStatus] ?? PaymentStatus::Pending;

            // Update transaction if status changed
            if ($transaction->status !== $status) {
                $transaction->update([
                    'status' => $status,
                    'provider_response' => [
                        'id' => $payment->id,
                        'status' => $payment->status,
                        'status_detail' => $payment->status_detail ?? null,
                    ],
                    'completed_at' => $status->isFinal() ? now() : null,
                ]);

                if ($status === PaymentStatus::Approved && ! $transaction->order->isPaid()) {
                    DB::transaction(function () use ($transaction): void {
                        $transaction->order->update([
                            'status' => OrderStatus::Paid,
                            'paid_at' => now(),
                        ]);

                        app(TicketAssignmentService::class)->assignForOrder($transaction->order);
                    });
                }
            }

            return WebhookResult::success(
                transaction: $transaction->fresh(),
                status: $status,
                providerTransactionId: (string) $payment->id,
                rawPayload: [
                    'id' => $payment->id,
                    'status' => $payment->status,
                    'status_detail' => $payment->status_detail ?? null,
                ],
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
     * Configure the MercadoPago SDK with access token.
     */
    protected function configureSdk(): void
    {
        $accessToken = $this->getGateway()->access_token;

        if (! $accessToken) {
            throw new \RuntimeException('MercadoPago access token not configured');
        }

        MercadoPagoConfig::setAccessToken($accessToken);

        // Set runtime environment for proper SDK behavior
        if ($this->getGateway()->is_sandbox) {
            MercadoPagoConfig::setRuntimeEnviroment(MercadoPagoConfig::LOCAL);
        }
    }

    /**
     * Get the public key for the checkout widget.
     */
    protected function getPublicKey(): string
    {
        // MercadoPago uses the access token prefix as public key
        // Format: TEST-xxx or APP_USR-xxx
        $accessToken = $this->getGateway()->access_token;

        // For the Bricks SDK, we need to return a proper public key
        // which should be configured separately or derived from access token
        return $this->getGateway()->public_key ?? $accessToken;
    }

    /**
     * Build items array for MercadoPago preference.
     */
    protected function buildItems(Order $order): array
    {
        $items = [];

        // Ensure items relationship is loaded
        $orderItems = $order->relationLoaded('items')
            ? $order->items
            : $order->items()->with('raffle')->get();

        foreach ($orderItems as $item) {
            $items[] = [
                'id' => (string) $item->id,
                'title' => $item->raffle?->title ?? 'Boletos de sorteo',
                'description' => "Boletos para {$item->raffle?->title}",
                'quantity' => 1,
                'unit_price' => $item->subtotal / 100, // MercadoPago uses decimal amounts
                'currency_id' => 'COP',
            ];
        }

        return $items;
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
