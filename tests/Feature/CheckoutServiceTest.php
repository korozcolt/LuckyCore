<?php

use App\Enums\OrderStatus;
use App\Enums\RaffleStatus;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Raffle;
use App\Models\User;
use App\Services\CartService;
use App\Services\CheckoutService;

beforeEach(function () {
    $this->cartService = new CartService();
    $this->checkoutService = new CheckoutService($this->cartService);
});

describe('CheckoutService', function () {
    describe('createOrder', function () {
        it('creates order from cart for authenticated user', function () {
            $user = User::factory()->create();
            $raffle = Raffle::factory()->create([
                'status' => RaffleStatus::Active,
                'ticket_price' => 5000,
                'total_tickets' => 1000,
                'sold_tickets' => 0,
                'min_purchase_qty' => 1,
            ]);
            $cart = Cart::factory()->create(['user_id' => $user->id]);
            CartItem::factory()->create([
                'cart_id' => $cart->id,
                'raffle_id' => $raffle->id,
                'quantity' => 10,
                'unit_price' => 5000,
            ]);

            $order = $this->checkoutService->createOrder(
                cart: $cart,
                user: $user,
                customerData: [
                    'name' => 'John Doe',
                    'email' => 'john@example.com',
                    'phone' => '3001234567',
                ],
                termsAccepted: true
            );

            expect($order)->toBeInstanceOf(Order::class)
                ->and($order->user_id)->toBe($user->id)
                ->and($order->status)->toBe(OrderStatus::Pending)
                ->and($order->customer_name)->toBe('John Doe')
                ->and($order->customer_email)->toBe('john@example.com')
                ->and($order->terms_accepted)->toBeTrue()
                ->and($order->items)->toHaveCount(1)
                ->and($cart->fresh()->converted_at)->not->toBeNull();
        });

        it('creates order from cart for guest', function () {
            $raffle = Raffle::factory()->create([
                'status' => RaffleStatus::Active,
                'ticket_price' => 5000,
                'total_tickets' => 1000,
                'sold_tickets' => 0,
                'min_purchase_qty' => 1,
            ]);
            $cart = Cart::factory()->create(['user_id' => null]);
            CartItem::factory()->create([
                'cart_id' => $cart->id,
                'raffle_id' => $raffle->id,
                'quantity' => 10,
                'unit_price' => 5000,
            ]);

            $order = $this->checkoutService->createOrder(
                cart: $cart,
                user: null,
                customerData: [
                    'name' => 'Jane Doe',
                    'email' => 'jane@example.com',
                ],
                termsAccepted: true
            );

            expect($order->user_id)->toBeNull()
                ->and($order->customer_email)->toBe('jane@example.com');
        });

        it('calculates order total correctly', function () {
            $raffle1 = Raffle::factory()->create([
                'status' => RaffleStatus::Active,
                'total_tickets' => 1000,
                'sold_tickets' => 0,
                'min_purchase_qty' => 1,
            ]);
            $raffle2 = Raffle::factory()->create([
                'status' => RaffleStatus::Active,
                'total_tickets' => 1000,
                'sold_tickets' => 0,
                'min_purchase_qty' => 1,
            ]);
            $cart = Cart::factory()->create();
            CartItem::factory()->create([
                'cart_id' => $cart->id,
                'raffle_id' => $raffle1->id,
                'quantity' => 10,
                'unit_price' => 5000, // 50,000
            ]);
            CartItem::factory()->create([
                'cart_id' => $cart->id,
                'raffle_id' => $raffle2->id,
                'quantity' => 5,
                'unit_price' => 10000, // 50,000
            ]);

            $order = $this->checkoutService->createOrder(
                cart: $cart,
                user: null,
                customerData: ['name' => 'Test', 'email' => 'test@example.com'],
                termsAccepted: true
            );

            expect($order->total)->toBe(100000) // 50,000 + 50,000
                ->and($order->items)->toHaveCount(2);
        });

        it('throws exception for empty cart', function () {
            $cart = Cart::factory()->create();

            $this->checkoutService->createOrder(
                cart: $cart,
                user: null,
                customerData: ['name' => 'Test', 'email' => 'test@example.com'],
                termsAccepted: true
            );
        })->throws(InvalidArgumentException::class);

        it('throws exception when terms not accepted', function () {
            $raffle = Raffle::factory()->create([
                'status' => RaffleStatus::Active,
                'total_tickets' => 1000,
                'sold_tickets' => 0,
                'min_purchase_qty' => 1,
            ]);
            $cart = Cart::factory()->create();
            CartItem::factory()->create([
                'cart_id' => $cart->id,
                'raffle_id' => $raffle->id,
                'quantity' => 10,
            ]);

            $this->checkoutService->createOrder(
                cart: $cart,
                user: null,
                customerData: ['name' => 'Test', 'email' => 'test@example.com'],
                termsAccepted: false
            );
        })->throws(InvalidArgumentException::class);

        it('throws exception for invalid email', function () {
            $raffle = Raffle::factory()->create([
                'status' => RaffleStatus::Active,
                'total_tickets' => 1000,
                'sold_tickets' => 0,
                'min_purchase_qty' => 1,
            ]);
            $cart = Cart::factory()->create();
            CartItem::factory()->create([
                'cart_id' => $cart->id,
                'raffle_id' => $raffle->id,
                'quantity' => 10,
            ]);

            $this->checkoutService->createOrder(
                cart: $cart,
                user: null,
                customerData: ['name' => 'Test', 'email' => 'invalid-email'],
                termsAccepted: true
            );
        })->throws(InvalidArgumentException::class);

        it('creates order event on order creation', function () {
            $raffle = Raffle::factory()->create([
                'status' => RaffleStatus::Active,
                'total_tickets' => 1000,
                'sold_tickets' => 0,
                'min_purchase_qty' => 1,
            ]);
            $cart = Cart::factory()->create();
            CartItem::factory()->create([
                'cart_id' => $cart->id,
                'raffle_id' => $raffle->id,
                'quantity' => 10,
            ]);

            $order = $this->checkoutService->createOrder(
                cart: $cart,
                user: null,
                customerData: ['name' => 'Test', 'email' => 'test@example.com'],
                termsAccepted: true
            );

            expect($order->events)->toHaveCount(1)
                ->and($order->events->first()->event_type)->toBe('order.created');
        });
    });
});
