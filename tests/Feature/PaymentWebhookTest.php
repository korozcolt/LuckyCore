<?php

use App\Enums\PaymentProvider;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\PaymentGateway;
use App\Models\PaymentTransaction;

describe('Payment Webhook Endpoint', function () {
    beforeEach(function () {
        $this->gateway = PaymentGateway::factory()->wompiConfigured()->active()->create();
    });

    it('returns 400 for unknown provider', function () {
        $response = $this->postJson('/api/webhooks/payments/unknown', []);

        $response->assertStatus(400)
            ->assertJson(['error' => 'Unknown provider']);
    });

    it('returns 401 for invalid signature', function () {
        $response = $this->postJson('/api/webhooks/payments/wompi', [
            'event' => 'transaction.updated',
            'signature' => [
                'properties' => ['id'],
                'timestamp' => time(),
                'checksum' => 'invalid_checksum',
            ],
            'data' => [
                'transaction' => [
                    'id' => 'txn_123',
                ],
            ],
        ]);

        $response->assertStatus(401)
            ->assertJson(['error' => 'Invalid signature']);
    });

    it('processes valid webhook and updates transaction', function () {
        $order = Order::factory()->create();
        $transaction = PaymentTransaction::factory()->create([
            'order_id' => $order->id,
            'provider' => PaymentProvider::Wompi,
            'status' => PaymentStatus::Pending,
        ]);

        // Build valid webhook payload
        $timestamp = time();
        $transactionData = [
            'id' => 'wompi_txn_123',
            'status' => 'APPROVED',
            'reference' => "ORD-{$order->ulid}-TXN-{$transaction->id}",
        ];

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

        $response = $this->postJson('/api/webhooks/payments/wompi', $payload);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        // Verify transaction was updated
        $transaction->refresh();
        expect($transaction->status)->toBe(PaymentStatus::Approved)
            ->and($transaction->provider_transaction_id)->toBe('wompi_txn_123');

        // Verify order was updated
        $order->refresh();
        expect($order->status)->toBe(\App\Enums\OrderStatus::Paid);
    });

    it('handles duplicate webhooks with idempotency', function () {
        $order = Order::factory()->create();
        $transaction = PaymentTransaction::factory()->create([
            'order_id' => $order->id,
            'provider' => PaymentProvider::Wompi,
            'status' => PaymentStatus::Approved,
            'webhook_received_at' => now(),
        ]);

        // Build webhook payload
        $timestamp = time();
        $transactionData = [
            'id' => 'wompi_txn_123',
            'status' => 'APPROVED',
            'reference' => "ORD-{$order->ulid}-TXN-{$transaction->id}",
        ];

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

        $response = $this->postJson('/api/webhooks/payments/wompi', $payload);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    });
});
