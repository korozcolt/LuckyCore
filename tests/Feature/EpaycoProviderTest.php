<?php

use App\Enums\PaymentProvider;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\PaymentGateway;
use App\Models\PaymentTransaction;
use App\Payments\Exceptions\InvalidWebhookSignatureException;
use App\Payments\PaymentManager;

describe('EpaycoProvider', function () {
    beforeEach(function () {
        $this->gateway = PaymentGateway::factory()->epaycoConfigured()->active()->create();
        $this->provider = app(PaymentManager::class)->provider(PaymentProvider::Epayco);
    });

    describe('getProviderName', function () {
        it('returns correct provider name', function () {
            expect($this->provider->getProviderName())->toBe('epayco');
        });
    });

    describe('isConfigured', function () {
        it('returns true when gateway has required credentials', function () {
            expect($this->provider->isConfigured())->toBeTrue();
        });

        it('returns false when credentials are missing', function () {
            $this->gateway->update(['credentials' => null]);

            $freshManager = new PaymentManager;
            $provider = $freshManager->provider(PaymentProvider::Epayco);

            expect($provider->isConfigured())->toBeFalse();
        });

        it('returns false when p_cust_id_cliente is missing', function () {
            $this->gateway->update(['credentials' => [
                'public_key' => 'test_key',
                'private_key' => 'test_key',
                'p_key' => 'test_key',
                // Missing p_cust_id_cliente
            ]]);

            expect($this->gateway->fresh()->isConfigured())->toBeFalse();
        });
    });

    describe('createPaymentIntent', function () {
        it('creates a payment transaction', function () {
            $order = Order::factory()->create(['total' => 50000]);

            $intentData = $this->provider->createPaymentIntent($order);

            expect($intentData->transaction)->toBeInstanceOf(PaymentTransaction::class)
                ->and($intentData->transaction->order_id)->toBe($order->id)
                ->and($intentData->transaction->provider)->toBe(PaymentProvider::Epayco)
                ->and($intentData->transaction->status)->toBe(PaymentStatus::Pending);
        });

        it('generates correct reference format', function () {
            $order = Order::factory()->create();

            $intentData = $this->provider->createPaymentIntent($order);

            expect($intentData->reference)->toMatch('/^ORD-.+-TXN-\d+$/');
        });

        it('includes customer data in extra', function () {
            $order = Order::factory()->create([
                'customer_email' => 'test@example.com',
                'customer_name' => 'John Doe',
            ]);

            $intentData = $this->provider->createPaymentIntent($order);

            expect($intentData->extra['customer_email'])->toBe('test@example.com')
                ->and($intentData->extra['customer_name'])->toBe('John Doe');
        });

        it('includes ePayco-specific parameters', function () {
            $order = Order::factory()->create(['total' => 50000]);

            $intentData = $this->provider->createPaymentIntent($order);

            expect($intentData->extra)->toHaveKeys([
                'name',
                'description',
                'invoice',
                'tax_base',
                'tax',
                'country',
                'lang',
                'test',
                'response',
                'confirmation',
                'extra1',
                'extra2',
                'extra3',
            ]);
        });

        it('sets transaction to pending status', function () {
            $order = Order::factory()->create();

            $intentData = $this->provider->createPaymentIntent($order);

            expect($intentData->transaction->status)->toBe(PaymentStatus::Pending);
        });
    });

    describe('getCheckoutConfig', function () {
        it('returns correct configuration for widget', function () {
            $order = Order::factory()->create(['total' => 50000]);
            $transaction = PaymentTransaction::factory()->create([
                'order_id' => $order->id,
                'provider' => PaymentProvider::Epayco,
            ]);

            $config = $this->provider->getCheckoutConfig($order, $transaction);

            expect($config)->toHaveKeys([
                'widget_url',
                'public_key',
                'amount_in_cents',
                'currency',
                'reference',
                'redirect_url',
                'extra',
            ])
                ->and($config['amount_in_cents'])->toBe(50000)
                ->and($config['currency'])->toBe('COP');
        });

        it('generates correct reference format', function () {
            $order = Order::factory()->create();
            $transaction = PaymentTransaction::factory()->create([
                'order_id' => $order->id,
                'provider' => PaymentProvider::Epayco,
            ]);

            $config = $this->provider->getCheckoutConfig($order, $transaction);

            expect($config['reference'])->toMatch('/^ORD-.+-TXN-\d+$/');
        });
    });

    describe('verifyWebhookSignature', function () {
        it('throws exception when x_signature is missing', function () {
            $request = new \Illuminate\Http\Request(content: json_encode([
                'x_ref_payco' => '123456',
                'x_transaction_id' => '789',
                'x_amount' => '500.00',
                'x_currency_code' => 'COP',
            ]));
            $request->headers->set('Content-Type', 'application/json');

            $this->provider->verifyWebhookSignature($request);
        })->throws(InvalidWebhookSignatureException::class, 'Missing x_signature');

        it('throws exception when required fields are missing', function () {
            $request = new \Illuminate\Http\Request(content: json_encode([
                'x_signature' => 'some_signature',
                // Missing other required fields
            ]));
            $request->headers->set('Content-Type', 'application/json');

            $this->provider->verifyWebhookSignature($request);
        })->throws(InvalidWebhookSignatureException::class, 'Missing required fields');

        it('verifies valid signature', function () {
            $clientId = '123456';
            $secretKey = 'test_p_key_xxxxxxxxxx';
            $refPayco = 'REF123456';
            $transactionId = 'TXN789';
            $amount = '500.00';
            $currencyCode = 'COP';

            // Build the expected signature
            $stringToHash = implode('^', [
                $clientId,
                $secretKey,
                $refPayco,
                $transactionId,
                $amount,
                $currencyCode,
            ]);
            $signature = hash('sha256', $stringToHash);

            $payload = [
                'x_ref_payco' => $refPayco,
                'x_transaction_id' => $transactionId,
                'x_amount' => $amount,
                'x_currency_code' => $currencyCode,
                'x_signature' => $signature,
            ];

            $request = new \Illuminate\Http\Request($payload);
            $request->headers->set('Content-Type', 'application/json');

            $result = $this->provider->verifyWebhookSignature($request);

            expect($result)->toBeTrue();
        });

        it('throws exception when signature verification fails', function () {
            $payload = [
                'x_ref_payco' => 'REF123456',
                'x_transaction_id' => 'TXN789',
                'x_amount' => '500.00',
                'x_currency_code' => 'COP',
                'x_signature' => 'invalid_signature',
            ];

            $request = new \Illuminate\Http\Request($payload);
            $request->headers->set('Content-Type', 'application/json');

            $this->provider->verifyWebhookSignature($request);
        })->throws(InvalidWebhookSignatureException::class, 'Webhook signature verification failed');
    });

    describe('processWebhook', function () {
        it('returns not found when transaction does not exist', function () {
            $payload = [
                'x_id_invoice' => 'ORD-nonexistent-TXN-99999',
                'x_ref_payco' => 'REF123456',
                'x_cod_response' => 1,
            ];

            $request = new \Illuminate\Http\Request($payload);
            $request->headers->set('Content-Type', 'application/json');

            $result = $this->provider->processWebhook($request);

            expect($result->success)->toBeFalse()
                ->and($result->errorCode)->toBe('NOT_FOUND');
        });

        it('returns error when reference is missing', function () {
            $payload = [
                'x_ref_payco' => 'REF123456',
                'x_cod_response' => 1,
                // Missing x_id_invoice and x_extra1/x_extra2
            ];

            $request = new \Illuminate\Http\Request($payload);
            $request->headers->set('Content-Type', 'application/json');

            $result = $this->provider->processWebhook($request);

            expect($result->success)->toBeFalse()
                ->and($result->errorCode)->toBe('MISSING_REFERENCE');
        });

        it('updates transaction status on approved payment', function () {
            $order = Order::factory()->create(['total' => 50000]);
            $transaction = PaymentTransaction::factory()->create([
                'order_id' => $order->id,
                'provider' => PaymentProvider::Epayco,
                'amount' => 50000,
                'status' => PaymentStatus::Pending,
            ]);

            $reference = "ORD-{$order->ulid}-TXN-{$transaction->id}";

            $payload = [
                'x_id_invoice' => $reference,
                'x_ref_payco' => 'EPAYCO123456',
                'x_cod_response' => 1, // Approved
                'x_response' => 'Aceptada',
                'x_amount' => '500.00',
                'x_currency_code' => 'COP',
            ];

            $request = new \Illuminate\Http\Request($payload);
            $request->headers->set('Content-Type', 'application/json');

            $result = $this->provider->processWebhook($request);

            expect($result->success)->toBeTrue()
                ->and($result->status)->toBe(PaymentStatus::Approved)
                ->and($result->transaction->provider_transaction_id)->toBe('EPAYCO123456');
        });

        it('handles idempotency for duplicate webhooks', function () {
            $order = Order::factory()->create(['total' => 50000]);
            $transaction = PaymentTransaction::factory()->create([
                'order_id' => $order->id,
                'provider' => PaymentProvider::Epayco,
                'amount' => 50000,
                'status' => PaymentStatus::Approved,
                'webhook_received_at' => now(),
                'completed_at' => now(),
            ]);

            $reference = "ORD-{$order->ulid}-TXN-{$transaction->id}";

            $payload = [
                'x_id_invoice' => $reference,
                'x_ref_payco' => 'EPAYCO123456',
                'x_cod_response' => 1,
                'x_amount' => '500.00',
                'x_currency_code' => 'COP',
            ];

            $request = new \Illuminate\Http\Request($payload);
            $request->headers->set('Content-Type', 'application/json');

            $result = $this->provider->processWebhook($request);

            expect($result->success)->toBeTrue()
                ->and($result->errorCode)->toBe('DUPLICATE');
        });

        it('maps ePayco status codes correctly', function () {
            $statusMap = [
                1 => PaymentStatus::Approved,
                2 => PaymentStatus::Rejected,
                3 => PaymentStatus::Pending,
                4 => PaymentStatus::Rejected,
                6 => PaymentStatus::Refunded,
                9 => PaymentStatus::Expired,
                11 => PaymentStatus::Voided,
            ];

            foreach ($statusMap as $codResponse => $expectedStatus) {
                $order = Order::factory()->create(['total' => 50000]);
                $transaction = PaymentTransaction::factory()->create([
                    'order_id' => $order->id,
                    'provider' => PaymentProvider::Epayco,
                    'amount' => 50000,
                    'status' => PaymentStatus::Pending,
                ]);

                $reference = "ORD-{$order->ulid}-TXN-{$transaction->id}";

                $payload = [
                    'x_id_invoice' => $reference,
                    'x_ref_payco' => "EPAYCO{$codResponse}",
                    'x_cod_response' => $codResponse,
                    'x_amount' => '500.00',
                    'x_currency_code' => 'COP',
                ];

                $request = new \Illuminate\Http\Request($payload);

                $result = $this->provider->processWebhook($request);

                expect($result->status)->toBe($expectedStatus, "Failed for cod_response: {$codResponse}");
            }
        });
    });

    describe('queryPaymentStatus', function () {
        it('returns error when no provider transaction ID', function () {
            $transaction = PaymentTransaction::factory()->create([
                'provider' => PaymentProvider::Epayco,
                'provider_transaction_id' => null,
            ]);

            $result = $this->provider->queryPaymentStatus($transaction);

            expect($result->success)->toBeFalse()
                ->and($result->errorCode)->toBe('NO_PROVIDER_ID');
        });
    });

    describe('status mapping', function () {
        it('correctly maps ePayco response codes', function () {
            // Test via reflection to access the constant
            $reflection = new \ReflectionClass($this->provider);
            $constant = $reflection->getConstant('STATUS_MAP');

            expect($constant)->toMatchArray([
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
            ]);
        });

        it('correctly maps ePayco text statuses', function () {
            $reflection = new \ReflectionClass($this->provider);
            $constant = $reflection->getConstant('TEXT_STATUS_MAP');

            expect($constant)->toMatchArray([
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
            ]);
        });
    });

    describe('gateway accessors', function () {
        it('can access gateway client_id', function () {
            expect($this->gateway->client_id)->not->toBeNull();
        });

        it('can access gateway secret_key', function () {
            expect($this->gateway->secret_key)->not->toBeNull();
        });

        it('can access gateway public_key', function () {
            expect($this->gateway->public_key)->not->toBeNull();
        });
    });

    describe('PaymentManager integration', function () {
        it('can resolve ePayco provider', function () {
            $manager = app(PaymentManager::class);

            expect($manager->isAvailable(PaymentProvider::Epayco))->toBeTrue();
        });

        it('includes ePayco in active providers', function () {
            $manager = app(PaymentManager::class);
            $providers = $manager->activeProviders();

            $providerNames = $providers->map(fn ($p) => $p->getProviderName())->toArray();

            expect($providerNames)->toContain('epayco');
        });
    });
});
