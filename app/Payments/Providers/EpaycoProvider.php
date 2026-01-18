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

/**
 * ePayco Payment Provider Implementation.
 *
 * @see https://docs.epayco.com/
 * @see https://docs.epayco.com/docs/integracion-personalizada
 */
class EpaycoProvider extends AbstractPaymentProvider
{
    /**
     * Map ePayco response codes to internal statuses.
     *
     * x_cod_response values:
     * 1 = Approved
     * 2 = Rejected
     * 3 = Pending
     * 4 = Failed
     * 6 = Reversed
     * 7 = Held (retained)
     * 9 = Expired
     * 10 = Abandoned
     * 11 = Cancelled
     * 12 = Antifraud
     */
    protected const array STATUS_MAP = [
        1 => PaymentStatus::Approved,
        2 => PaymentStatus::Rejected,
        3 => PaymentStatus::Pending,
        4 => PaymentStatus::Rejected,
        6 => PaymentStatus::Refunded,
        7 => PaymentStatus::Pending,
        9 => PaymentStatus::Expired,
        10 => PaymentStatus::Voided,
        11 => PaymentStatus::Voided,
        12 => PaymentStatus::Rejected,
    ];

    /**
     * Map ePayco text statuses to codes for consistency.
     */
    protected const array TEXT_STATUS_MAP = [
        'Aceptada' => 1,
        'Rechazada' => 2,
        'Pendiente' => 3,
        'Fallida' => 4,
        'Reversada' => 6,
        'Retenida' => 7,
        'Expirada' => 9,
        'Abandonada' => 10,
        'Cancelada' => 11,
        'Fraude' => 12,
    ];

    public function getProviderName(): string
    {
        return PaymentProvider::Epayco->value;
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
                'provider' => PaymentProvider::Epayco,
                'amount' => $order->total,
                'currency' => 'COP',
                'status' => PaymentStatus::Pending,
                'idempotency_key' => (string) Str::uuid(),
                'initiated_at' => now(),
            ]);
        });

        $reference = $this->generateReference($order, $transaction);
        $redirectUrl = route('payments.callback', [
            'provider' => 'epayco',
            'order' => $order->ulid,
        ]);

        $confirmationUrl = route('webhooks.payments.handle', ['provider' => 'epayco']);

        // Build description from order items
        $description = $this->buildDescription($order);

        $this->log('info', 'Payment intent created', [
            'transaction_id' => $transaction->id,
            'reference' => $reference,
        ]);

        return new PaymentIntentData(
            transaction: $transaction,
            provider: PaymentProvider::Epayco,
            widgetUrl: $this->getGateway()->getWidgetUrl(),
            publicKey: $this->getGateway()->public_key,
            amountInCents: $order->total,
            currency: 'COP',
            reference: $reference,
            signature: '', // ePayco doesn't require pre-generated signature for checkout
            redirectUrl: $redirectUrl,
            extra: [
                'name' => $this->buildPaymentName($order),
                'description' => $description,
                'invoice' => $reference,
                'tax_base' => '0',
                'tax' => '0',
                'tax_ico' => '0',
                'country' => 'co',
                'lang' => 'es',
                'test' => $this->getGateway()->is_sandbox,
                'external' => 'false',
                'response' => $redirectUrl,
                'confirmation' => $confirmationUrl,
                'customer_email' => $order->customer_email,
                'customer_name' => $order->customer_name,
                'customer_phone' => $order->customer_phone,
                // Extra fields for tracking
                'extra1' => (string) $order->id,
                'extra2' => (string) $transaction->id,
                'extra3' => $order->ulid,
            ],
        );
    }

    public function getCheckoutConfig(Order $order, PaymentTransaction $transaction): array
    {
        $reference = $this->generateReference($order, $transaction);
        $redirectUrl = route('payments.callback', [
            'provider' => 'epayco',
            'order' => $order->ulid,
        ]);

        $confirmationUrl = route('webhooks.payments.handle', ['provider' => 'epayco']);

        return [
            'widget_url' => $this->getGateway()->getWidgetUrl(),
            'public_key' => $this->getGateway()->public_key,
            'amount_in_cents' => $order->total,
            'currency' => 'COP',
            'reference' => $reference,
            'signature' => '',
            'redirect_url' => $redirectUrl,
            'extra' => [
                'name' => $this->buildPaymentName($order),
                'description' => $this->buildDescription($order),
                'invoice' => $reference,
                'tax_base' => '0',
                'tax' => '0',
                'tax_ico' => '0',
                'country' => 'co',
                'lang' => 'es',
                'test' => $this->getGateway()->is_sandbox,
                'external' => 'false',
                'response' => $redirectUrl,
                'confirmation' => $confirmationUrl,
                'customer_email' => $order->customer_email,
                'customer_name' => $order->customer_name,
                'extra1' => (string) $order->id,
                'extra2' => (string) $transaction->id,
                'extra3' => $order->ulid,
            ],
        ];
    }

    public function verifyWebhookSignature(Request $request): bool
    {
        $this->log('debug', 'Verifying webhook signature');

        // Get signature from request
        $receivedSignature = $request->input('x_signature');

        if (! $receivedSignature) {
            throw new InvalidWebhookSignatureException('Missing x_signature in webhook payload');
        }

        // Get required fields for signature generation
        $clientId = $this->getGateway()->client_id;
        $secretKey = $this->getGateway()->secret_key;
        $refPayco = $request->input('x_ref_payco');
        $transactionId = $request->input('x_transaction_id');
        $amount = $request->input('x_amount');
        $currencyCode = $request->input('x_currency_code');

        if (! $clientId || ! $secretKey) {
            throw new InvalidWebhookSignatureException('ePayco credentials not configured');
        }

        if (! $refPayco || ! $transactionId || ! $amount || ! $currencyCode) {
            throw new InvalidWebhookSignatureException('Missing required fields for signature verification');
        }

        // Generate expected signature
        // Formula: SHA256(p_cust_id_cliente ^ p_key ^ x_ref_payco ^ x_transaction_id ^ x_amount ^ x_currency_code)
        $stringToHash = implode('^', [
            $clientId,
            $secretKey,
            $refPayco,
            $transactionId,
            $amount,
            $currencyCode,
        ]);

        $expectedSignature = hash('sha256', $stringToHash);

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
            'x_ref_payco' => $payload['x_ref_payco'] ?? 'unknown',
            'x_id_invoice' => $payload['x_id_invoice'] ?? 'unknown',
        ]);

        // Extract transaction data from ePayco webhook
        $reference = $payload['x_id_invoice'] ?? $payload['x_extra1'] ?? null;
        $providerTransactionId = $payload['x_ref_payco'] ?? null;
        $codResponse = (int) ($payload['x_cod_response'] ?? 3);
        $responseText = $payload['x_response'] ?? null;

        // Try to get response code from text if numeric code is missing
        if ($codResponse === 0 && $responseText) {
            $codResponse = self::TEXT_STATUS_MAP[$responseText] ?? 3;
        }

        if (! $reference) {
            // Try to find by extra2 (transaction_id)
            $transactionId = $payload['x_extra2'] ?? null;
            if ($transactionId) {
                $transaction = PaymentTransaction::find($transactionId);
                if ($transaction) {
                    $reference = $this->generateReference($transaction->order, $transaction);
                }
            }
        }

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

        // Verify amount matches (security check)
        $webhookAmount = (int) (floatval($payload['x_amount'] ?? 0) * 100);
        if ($webhookAmount > 0 && $webhookAmount !== $transaction->amount) {
            $this->log('warning', 'Amount mismatch in webhook', [
                'expected' => $transaction->amount,
                'received' => $webhookAmount,
            ]);

            return WebhookResult::failed(
                transaction: $transaction,
                errorCode: 'AMOUNT_MISMATCH',
                errorMessage: 'Webhook amount does not match transaction amount',
                rawPayload: $payload,
            );
        }

        // Check for idempotency
        if ($transaction->webhook_received_at && $transaction->isFinal()) {
            $this->log('info', 'Webhook already processed (idempotency)', [
                'transaction_id' => $transaction->id,
            ]);

            return WebhookResult::duplicate($transaction, $payload);
        }

        // Map ePayco status to internal status
        $status = self::STATUS_MAP[$codResponse] ?? PaymentStatus::Pending;

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
                description: 'Webhook recibido desde ePayco',
                metadata: [
                    'provider_transaction_id' => $providerTransactionId,
                    'status' => $status->value,
                    'x_response' => $payload['x_response'] ?? null,
                    'x_cod_response' => $payload['x_cod_response'] ?? null,
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
                        'provider_transaction_id' => $providerTransactionId,
                        'approval_code' => $payload['x_approval_code'] ?? null,
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
            // ePayco transaction query endpoint
            // GET https://secure.epayco.co/restpagos/transaction/response.json?ref_payco={ref_payco}&public_key={public_key}
            $response = $this->makeRequest(
                method: 'GET',
                endpoint: '/restpagos/transaction/response.json',
                query: [
                    'ref_payco' => $transaction->provider_transaction_id,
                    'public_key' => $this->getGateway()->public_key,
                ],
            );

            $data = $response['data'] ?? $response;
            $codResponse = (int) ($data['x_cod_response'] ?? $data['x_cod_transaction_state'] ?? 3);
            $status = self::STATUS_MAP[$codResponse] ?? PaymentStatus::Pending;

            // Update transaction if status changed
            if ($transaction->status !== $status) {
                $transaction->update([
                    'status' => $status,
                    'provider_response' => $response,
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
                providerTransactionId: $data['x_ref_payco'] ?? $transaction->provider_transaction_id,
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

    /**
     * Build payment name from order.
     */
    protected function buildPaymentName(Order $order): string
    {
        $itemCount = $order->items->count();
        if ($itemCount === 1) {
            $item = $order->items->first();

            return "Boletos - {$item->raffle?->title}";
        }

        return "Orden #{$order->order_number} - {$itemCount} sorteos";
    }

    /**
     * Build description from order items.
     */
    protected function buildDescription(Order $order): string
    {
        $descriptions = [];

        // Ensure items relationship is loaded
        $orderItems = $order->relationLoaded('items')
            ? $order->items
            : $order->items()->with('raffle')->get();

        foreach ($orderItems as $item) {
            $raffleName = $item->raffle?->title ?? 'Sorteo';
            $descriptions[] = "{$item->quantity} boleto(s) para {$raffleName}";
        }

        return implode(', ', $descriptions);
    }
}
