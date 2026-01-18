<?php

use App\Enums\PaymentProvider;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\PaymentGateway;
use App\Models\PaymentTransaction;
use App\Payments\Exceptions\InvalidWebhookSignatureException;
use App\Payments\PaymentManager;

describe('MercadoPagoProvider', function () {
    beforeEach(function () {
        $this->gateway = PaymentGateway::factory()->mercadoPagoConfigured()->active()->create();
        $this->provider = app(PaymentManager::class)->provider(PaymentProvider::MercadoPago);
    });

    describe('getProviderName', function () {
        it('returns correct provider name', function () {
            expect($this->provider->getProviderName())->toBe('mercadopago');
        });
    });

    describe('isConfigured', function () {
        it('returns true when gateway has required credentials', function () {
            expect($this->provider->isConfigured())->toBeTrue();
        });

        it('returns false when credentials are missing', function () {
            $this->gateway->update(['credentials' => null]);

            $freshManager = new PaymentManager;
            $provider = $freshManager->provider(PaymentProvider::MercadoPago);

            expect($provider->isConfigured())->toBeFalse();
        });
    });

    describe('getCheckoutConfig', function () {
        it('returns correct configuration for widget', function () {
            $order = Order::factory()->create(['total' => 50000]);
            $transaction = PaymentTransaction::factory()->create([
                'order_id' => $order->id,
                'provider' => PaymentProvider::MercadoPago,
                'provider_transaction_id' => 'pref_test_123',
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
                ->and($config['extra']['preference_id'])->toBe('pref_test_123');
        });

        it('generates correct reference format', function () {
            $order = Order::factory()->create();
            $transaction = PaymentTransaction::factory()->create([
                'order_id' => $order->id,
                'provider' => PaymentProvider::MercadoPago,
            ]);

            $config = $this->provider->getCheckoutConfig($order, $transaction);

            expect($config['reference'])->toMatch('/^ORD-.+-TXN-\d+$/');
        });
    });

    describe('verifyWebhookSignature', function () {
        it('returns true when no signature header is present (IPN mode)', function () {
            $request = new \Illuminate\Http\Request(content: json_encode([
                'type' => 'payment',
                'data' => ['id' => '123456789'],
            ]));
            $request->headers->set('Content-Type', 'application/json');

            $result = $this->provider->verifyWebhookSignature($request);

            expect($result)->toBeTrue();
        });

        it('throws exception when signature format is invalid', function () {
            $request = new \Illuminate\Http\Request(content: json_encode([
                'data' => ['id' => '123456789'],
            ]));
            $request->headers->set('Content-Type', 'application/json');
            $request->headers->set('x-signature', 'invalid_format_without_equals');
            $request->headers->set('x-request-id', 'req-123');

            $this->provider->verifyWebhookSignature($request);
        })->throws(InvalidWebhookSignatureException::class, 'Invalid signature format');

        it('verifies valid signature', function () {
            $dataId = '123456789';
            $requestId = 'req-12345';
            $timestamp = time();
            $webhookSecret = 'test_webhook_secret_xxxxxxxxxx';

            // Build the signed payload
            $manifest = "id:{$dataId};request-id:{$requestId};ts:{$timestamp};";
            $signature = hash_hmac('sha256', $manifest, $webhookSecret);

            $payload = [
                'type' => 'payment',
                'data' => ['id' => $dataId],
            ];

            $request = new \Illuminate\Http\Request(content: json_encode($payload));
            $request->headers->set('Content-Type', 'application/json');
            $request->headers->set('x-signature', "ts={$timestamp},v1={$signature}");
            $request->headers->set('x-request-id', $requestId);

            $result = $this->provider->verifyWebhookSignature($request);

            expect($result)->toBeTrue();
        });

        it('throws exception when signature verification fails', function () {
            $payload = [
                'type' => 'payment',
                'data' => ['id' => '123456789'],
            ];

            $request = new \Illuminate\Http\Request(content: json_encode($payload));
            $request->headers->set('Content-Type', 'application/json');
            $request->headers->set('x-signature', 'ts=123456,v1=invalid_signature');
            $request->headers->set('x-request-id', 'req-123');

            $this->provider->verifyWebhookSignature($request);
        })->throws(InvalidWebhookSignatureException::class, 'Webhook signature verification failed');
    });

    describe('processWebhook', function () {
        it('ignores non-payment webhooks', function () {
            $payload = [
                'type' => 'merchant_order',
                'data' => ['id' => '123'],
            ];

            $request = new \Illuminate\Http\Request(content: json_encode($payload));
            $request->headers->set('Content-Type', 'application/json');

            $result = $this->provider->processWebhook($request);

            expect($result->success)->toBeFalse()
                ->and($result->errorCode)->toBe('IGNORED_WEBHOOK_TYPE');
        });

        it('returns error when data ID is missing', function () {
            $payload = [
                'type' => 'payment',
                'data' => [],
            ];

            $request = new \Illuminate\Http\Request(content: json_encode($payload));
            $request->headers->set('Content-Type', 'application/json');

            $result = $this->provider->processWebhook($request);

            expect($result->success)->toBeFalse()
                ->and($result->errorCode)->toBe('MISSING_DATA_ID');
        });
    });

    describe('queryPaymentStatus', function () {
        it('returns error when no provider transaction ID', function () {
            $transaction = PaymentTransaction::factory()->create([
                'provider' => PaymentProvider::MercadoPago,
                'provider_transaction_id' => null,
            ]);

            $result = $this->provider->queryPaymentStatus($transaction);

            expect($result->success)->toBeFalse()
                ->and($result->errorCode)->toBe('NO_PROVIDER_ID');
        });
    });

    describe('status mapping', function () {
        it('correctly maps MercadoPago statuses', function () {
            // Test via reflection to access the constant
            $reflection = new \ReflectionClass($this->provider);
            $constant = $reflection->getConstant('STATUS_MAP');

            expect($constant)->toMatchArray([
                'pending' => PaymentStatus::Pending,
                'approved' => PaymentStatus::Approved,
                'authorized' => PaymentStatus::Pending,
                'in_process' => PaymentStatus::Pending,
                'in_mediation' => PaymentStatus::Pending,
                'rejected' => PaymentStatus::Rejected,
                'cancelled' => PaymentStatus::Voided,
                'refunded' => PaymentStatus::Refunded,
                'charged_back' => PaymentStatus::Refunded,
            ]);
        });
    });

    describe('gateway accessors', function () {
        it('can access gateway access token', function () {
            expect($this->gateway->access_token)->not->toBeNull();
        });

        it('can access gateway webhook secret', function () {
            expect($this->gateway->webhook_secret)->not->toBeNull();
        });
    });

    describe('PaymentManager integration', function () {
        it('can resolve MercadoPago provider', function () {
            $manager = app(PaymentManager::class);

            expect($manager->isAvailable(PaymentProvider::MercadoPago))->toBeTrue();
        });

        it('includes MercadoPago in active providers', function () {
            $manager = app(PaymentManager::class);
            $providers = $manager->activeProviders();

            $providerNames = $providers->map(fn ($p) => $p->getProviderName())->toArray();

            expect($providerNames)->toContain('mercadopago');
        });
    });
});
