<?php

use App\Enums\RaffleStatus;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Raffle;
use App\Models\RafflePackage;
use App\Models\User;
use App\Services\CartService;

beforeEach(function () {
    $this->cartService = new CartService();
});

describe('CartService', function () {
    describe('getOrCreateCart', function () {
        it('creates a new cart for guest', function () {
            $sessionId = 'test-session-123';

            $cart = $this->cartService->getOrCreateCart(null, $sessionId);

            expect($cart)->toBeInstanceOf(Cart::class)
                ->and($cart->session_id)->toBe($sessionId)
                ->and($cart->user_id)->toBeNull();
        });

        it('creates a new cart for authenticated user', function () {
            $user = User::factory()->create();

            $cart = $this->cartService->getOrCreateCart($user);

            expect($cart)->toBeInstanceOf(Cart::class)
                ->and($cart->user_id)->toBe($user->id);
        });

        it('returns existing cart for user', function () {
            $user = User::factory()->create();
            $existingCart = Cart::factory()->create(['user_id' => $user->id]);

            $cart = $this->cartService->getOrCreateCart($user);

            expect($cart->id)->toBe($existingCart->id);
        });
    });

    describe('addItem', function () {
        it('adds item to cart', function () {
            $raffle = Raffle::factory()->create([
                'status' => RaffleStatus::Active,
                'ticket_price' => 5000,
                'total_tickets' => 1000,
                'sold_tickets' => 0,
                'min_purchase_qty' => 1,
            ]);
            $cart = Cart::factory()->create();

            $item = $this->cartService->addItem($cart, $raffle, 10);

            expect($item)->toBeInstanceOf(CartItem::class)
                ->and($item->raffle_id)->toBe($raffle->id)
                ->and($item->quantity)->toBe(10)
                ->and($item->unit_price)->toBe(5000);
        });

        it('adds item with package', function () {
            $raffle = Raffle::factory()->create([
                'status' => RaffleStatus::Active,
                'ticket_price' => 5000,
                'total_tickets' => 1000,
                'sold_tickets' => 0,
                'min_purchase_qty' => 1,
            ]);
            $package = RafflePackage::factory()->create([
                'raffle_id' => $raffle->id,
                'quantity' => 50,
                'price' => 200000,
            ]);
            $cart = Cart::factory()->create();

            $item = $this->cartService->addItem($cart, $raffle, 50, $package);

            expect($item->raffle_package_id)->toBe($package->id)
                ->and($item->quantity)->toBe(50)
                ->and($item->unit_price)->toBe(4000); // 200000 / 50
        });

        it('throws exception for inactive raffle', function () {
            $raffle = Raffle::factory()->create(['status' => RaffleStatus::Draft]);
            $cart = Cart::factory()->create();

            $this->cartService->addItem($cart, $raffle, 10);
        })->throws(InvalidArgumentException::class);

        it('throws exception for quantity below minimum', function () {
            $raffle = Raffle::factory()->create([
                'status' => RaffleStatus::Active,
                'min_purchase_qty' => 10,
                'total_tickets' => 1000,
                'sold_tickets' => 0,
            ]);
            $cart = Cart::factory()->create();

            $this->cartService->addItem($cart, $raffle, 5);
        })->throws(InvalidArgumentException::class);

        it('throws exception for quantity above maximum', function () {
            $raffle = Raffle::factory()->create([
                'status' => RaffleStatus::Active,
                'max_purchase_qty' => 10,
                'min_purchase_qty' => 1,
                'total_tickets' => 1000,
                'sold_tickets' => 0,
            ]);
            $cart = Cart::factory()->create();

            $this->cartService->addItem($cart, $raffle, 20);
        })->throws(InvalidArgumentException::class);

        it('updates existing item in cart', function () {
            $raffle = Raffle::factory()->create([
                'status' => RaffleStatus::Active,
                'ticket_price' => 5000,
                'total_tickets' => 1000,
                'sold_tickets' => 0,
                'min_purchase_qty' => 1,
            ]);
            $cart = Cart::factory()->create();

            $this->cartService->addItem($cart, $raffle, 10);
            $cart->load('items');
            $item = $this->cartService->addItem($cart, $raffle, 20);

            expect($item->quantity)->toBe(20)
                ->and($cart->fresh()->items)->toHaveCount(1);
        });
    });

    describe('removeItem', function () {
        it('removes item from cart', function () {
            $cart = Cart::factory()->create();
            $raffle = Raffle::factory()->create();
            $item = CartItem::factory()->create([
                'cart_id' => $cart->id,
                'raffle_id' => $raffle->id,
            ]);

            $result = $this->cartService->removeItem($item);

            expect($result)->toBeTrue()
                ->and($cart->fresh()->items)->toHaveCount(0);
        });
    });

    describe('mergeGuestCart', function () {
        it('merges guest cart into user cart', function () {
            $user = User::factory()->create();
            $sessionId = 'guest-session-123';

            $raffle = Raffle::factory()->create(['ticket_price' => 5000]);
            $guestCart = Cart::factory()->create(['session_id' => $sessionId, 'user_id' => null]);
            CartItem::factory()->create([
                'cart_id' => $guestCart->id,
                'raffle_id' => $raffle->id,
                'quantity' => 10,
                'unit_price' => 5000,
            ]);

            $mergedCart = $this->cartService->mergeGuestCart($user, $sessionId);

            expect($mergedCart)->not->toBeNull()
                ->and($mergedCart->user_id)->toBe($user->id)
                ->and($mergedCart->items)->toHaveCount(1)
                ->and($guestCart->fresh()->merged_at)->not->toBeNull();
        });

        it('takes larger quantity when merging duplicate items', function () {
            $user = User::factory()->create();
            $sessionId = 'guest-session-123';
            $raffle = Raffle::factory()->create(['ticket_price' => 5000]);

            // Create user cart with item
            $userCart = Cart::factory()->create(['user_id' => $user->id]);
            CartItem::factory()->create([
                'cart_id' => $userCart->id,
                'raffle_id' => $raffle->id,
                'quantity' => 5,
                'unit_price' => 5000,
            ]);

            // Create guest cart with same item but larger quantity
            $guestCart = Cart::factory()->create(['session_id' => $sessionId, 'user_id' => null]);
            CartItem::factory()->create([
                'cart_id' => $guestCart->id,
                'raffle_id' => $raffle->id,
                'quantity' => 20,
                'unit_price' => 5000,
            ]);

            $mergedCart = $this->cartService->mergeGuestCart($user, $sessionId);

            expect($mergedCart->items)->toHaveCount(1)
                ->and($mergedCart->items->first()->quantity)->toBe(20);
        });
    });

    describe('validateCart', function () {
        it('returns empty array for valid cart', function () {
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

            $errors = $this->cartService->validateCart($cart);

            expect($errors)->toBeEmpty();
        });

        it('returns errors for invalid items', function () {
            $raffle = Raffle::factory()->create([
                'status' => RaffleStatus::Closed,
                'slug' => 'closed-raffle',
            ]);
            $cart = Cart::factory()->create();
            CartItem::factory()->create([
                'cart_id' => $cart->id,
                'raffle_id' => $raffle->id,
                'quantity' => 10,
            ]);

            $errors = $this->cartService->validateCart($cart);

            expect($errors)->not->toBeEmpty()
                ->and($errors)->toHaveKey('closed-raffle');
        });
    });
});
