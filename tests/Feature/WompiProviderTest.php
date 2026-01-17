<?php

use App\Enums\PaymentProvider;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\PaymentGateway;
use App\Models\PaymentTransaction;
use App\Payments\DTOs\PaymentIntentData;
use App\Payments\Exceptions\InvalidWebhookSignatureException;
use App\Payments\PaymentManager;

describe('WompiProvider', function () {
    beforeEach(function () {
        $this->gateway = PaymentGateway::factory()->wompiConfigured()->active()->create();
        $this->provider = app(PaymentManager::class)->provider(PaymentProvider::Wompi);
    });

    describe('createPaymentIntent', function () {
        it('creates a payment transaction', function () {
            $order = Order::factory()->create(['total' => 100000]); // $1,000 COP

            $intent = $this->provider->createPaymentIntent($order);

            expect($intent)->toBeInstanceOf(PaymentIntentData::class)
                ->and($intent->transaction)->toBeInstanceOf(PaymentTransaction::class)
                ->and($intent->amountInCents)->toBe(100000)
                ->and($intent->currency)->toBe('COP')
                ->and($intent->provider)->toBe(PaymentProvider::Wompi);
        });

        it('generates correct reference format', function () {
            $order = Order::factory()->create();

            $intent = $this->provider->createPaymentIntent($order);

            expect($intent->reference)->toMatch('/^ORD-.+-TXN-\d+$/');
        });

        it('generates integrity signature', function () {
            $order = Order::factory()->create(['total' => 100000]);

            $intent = $this->provider->createPaymentIntent($order);

            // Verify signature is a 64-character hex string (SHA256)
            expect($intent->signature)->toMatch('/^[a-f0-9]{64}$/');
        });

        it('includes customer data in extra', function () {
            $order = Order::factory()->create([
                'customer_email' => 'test@example.com',
                'customer_name' => 'John Doe',
            ]);

            $intent = $this->provider->createPaymentIntent($order);

            expect($intent->extra['customer_email'])->toBe('test@example.com')
                ->and($intent->extra['customer_name'])->toBe('John Doe');
        });

        it('sets transaction to pending status', function () {
            $order = Order::factory()->create();

            $intent = $this->provider->createPaymentIntent($order);

            expect($intent->transaction->status)->toBe(PaymentStatus::Pending);
        });
    });

    describe('getCheckoutConfig', function () {
        it('returns correct configuration for widget', function () {
            $order = Order::factory()->create(['total' => 50000]);
            $transaction = PaymentTransaction::factory()->create([
                'order_id' => $order->id,
                'provider' => PaymentProvider::Wompi,
            ]);

            $config = $this->provider->getCheckoutConfig($order, $transaction);

            expect($config)->toHaveKeys([
                'widget_url',
                'public_key',
                'amount_in_cents',
                'currency',
                'reference',
                'signature',
                'redirect_url',
                'extra',
            ])
                ->and($config['amount_in_cents'])->toBe(50000)
                ->and($config['currency'])->toBe('COP')
                ->and($config['public_key'])->toBe('pub_test_xxxxxxxxxx');
        });
    });

    describe('verifyWebhookSignature', function () {
        it('throws exception when signature is missing', function () {
            $request = new \Illuminate\Http\Request(content: json_encode([]));
            $request->headers->set('Content-Type', 'application/json');

            $this->provider->verifyWebhookSignature($request);
        })->throws(InvalidWebhookSignatureException::class, 'Missing signature');

        it('throws exception when signature data is incomplete', function () {
            $request = new \Illuminate\Http\Request(content: json_encode([
                'signature' => [
                    'properties' => [],
                    // Missing timestamp and checksum
                ],
            ]));
            $request->headers->set('Content-Type', 'application/json');

            $this->provider->verifyWebhookSignature($request);
        })->throws(InvalidWebhookSignatureException::class, 'Incomplete signature');

        it('verifies valid signature', function () {
            // Create a valid webhook payload with correct signature
            $timestamp = time();
            $transactionData = [
                'id' => 'txn_123',
                'status' => 'APPROVED',
                'reference' => 'ORD-test-TXN-1',
            ];

            // Build the string to hash
            $properties = ['id', 'status', 'reference'];
            $stringToHash = '';
            foreach ($properties as $property) {
                $stringToHash .= $transactionData[$property];
            }
            $stringToHash .= $timestamp;
            $stringToHash .= 'test_events_xxxxxxxxxx';

            $checksum = hash('sha256', $stringToHash);

            $payload = [
                'event' => 'transaction.updated',
                'data' => [
                    'transaction' => $transactionData,
                ],
                'signature' => [
                    'properties' => $properties,
                    'timestamp' => $timestamp,
                    'checksum' => $checksum,
                ],
            ];

            $request = new \Illuminate\Http\Request(content: json_encode($payload));
            $request->headers->set('Content-Type', 'application/json');

            $result = $this->provider->verifyWebhookSignature($request);

            expect($result)->toBeTrue();
        });
    });

    describe('processWebhook', function () {
        it('returns not found when transaction does not exist', function () {
            $payload = [
                'event' => 'transaction.updated',
                'data' => [
                    'transaction' => [
                        'reference' => 'ORD-nonexistent-TXN-999',
                        'id' => 'txn_123',
                        'status' => 'APPROVED',
                    ],
                ],
            ];

            $request = new \Illuminate\Http\Request(content: json_encode($payload));
            $request->headers->set('Content-Type', 'application/json');

            $result = $this->provider->processWebhook($request);

            expect($result->success)->toBeFalse()
                ->and($result->errorCode)->toBe('TRANSACTION_NOT_FOUND');
        });

        it('updates transaction status on approved payment', function () {
            $order = Order::factory()->create();
            $transaction = PaymentTransaction::factory()->create([
                'order_id' => $order->id,
                'provider' => PaymentProvider::Wompi,
                'status' => PaymentStatus::Pending,
            ]);

            $payload = [
                'event' => 'transaction.updated',
                'data' => [
                    'transaction' => [
                        'reference' => "ORD-{$order->ulid}-TXN-{$transaction->id}",
                        'id' => 'wompi_txn_123',
                        'status' => 'APPROVED',
                    ],
                ],
            ];

            $request = new \Illuminate\Http\Request(content: json_encode($payload));
            $request->headers->set('Content-Type', 'application/json');

            $result = $this->provider->processWebhook($request);

            expect($result->success)->toBeTrue()
                ->and($result->status)->toBe(PaymentStatus::Approved)
                ->and($result->transaction->status)->toBe(PaymentStatus::Approved)
                ->and($result->providerTransactionId)->toBe('wompi_txn_123');

            // Verify order was updated
            $order->refresh();
            expect($order->status)->toBe(\App\Enums\OrderStatus::Paid);
        });

        it('handles idempotency for duplicate webhooks', function () {
            $order = Order::factory()->create();
            $transaction = PaymentTransaction::factory()->create([
                'order_id' => $order->id,
                'provider' => PaymentProvider::Wompi,
                'status' => PaymentStatus::Approved,
                'webhook_received_at' => now(),
            ]);

            $payload = [
                'event' => 'transaction.updated',
                'data' => [
                    'transaction' => [
                        'reference' => "ORD-{$order->ulid}-TXN-{$transaction->id}",
                        'id' => 'wompi_txn_123',
                        'status' => 'APPROVED',
                    ],
                ],
            ];

            $request = new \Illuminate\Http\Request(content: json_encode($payload));
            $request->headers->set('Content-Type', 'application/json');

            $result = $this->provider->processWebhook($request);

            expect($result->success)->toBeTrue()
                ->and($result->errorCode)->toBe('DUPLICATE_WEBHOOK');
        });

        it('maps Wompi statuses correctly', function () {
            $statuses = [
                'PENDING' => PaymentStatus::Pending,
                'APPROVED' => PaymentStatus::Approved,
                'DECLINED' => PaymentStatus::Rejected,
                'VOIDED' => PaymentStatus::Voided,
                'ERROR' => PaymentStatus::Rejected,
            ];

            foreach ($statuses as $wompiStatus => $expectedStatus) {
                $order = Order::factory()->create();
                $transaction = PaymentTransaction::factory()->create([
                    'order_id' => $order->id,
                    'provider' => PaymentProvider::Wompi,
                    'status' => PaymentStatus::Pending,
                ]);

                $payload = [
                    'event' => 'transaction.updated',
                    'data' => [
                        'transaction' => [
                            'reference' => "ORD-{$order->ulid}-TXN-{$transaction->id}",
                            'id' => "wompi_txn_{$wompiStatus}",
                            'status' => $wompiStatus,
                        ],
                    ],
                ];

                $request = new \Illuminate\Http\Request(content: json_encode($payload));
                $request->headers->set('Content-Type', 'application/json');

                $result = $this->provider->processWebhook($request);

                expect($result->status)->toBe($expectedStatus, "Failed for status: {$wompiStatus}");
            }
        });
    });

    describe('isConfigured', function () {
        it('returns true when gateway has required credentials', function () {
            expect($this->provider->isConfigured())->toBeTrue();
        });

        it('returns false when credentials are missing', function () {
            // Remove credentials from existing gateway
            $this->gateway->update(['credentials' => null]);

            // Get a fresh instance of the provider
            $freshManager = new PaymentManager();
            $provider = $freshManager->provider(PaymentProvider::Wompi);

            expect($provider->isConfigured())->toBeFalse();
        });
    });
});
