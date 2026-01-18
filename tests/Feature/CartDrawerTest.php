<?php

use App\Enums\RaffleStatus;
use App\Livewire\Components\CartDrawer;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Raffle;
use App\Models\User;
use Livewire\Livewire;

describe('CartDrawer', function () {
    describe('count', function () {
        it('shows zero count when cart is empty', function () {
            Livewire::test(CartDrawer::class)
                ->assertSet('count', 0);
        });

        it('shows correct count with items in cart', function () {
            $raffle = Raffle::factory()->create(['ticket_price' => 5000]);
            $cart = Cart::factory()->create(['session_id' => session()->getId()]);
            CartItem::factory()->create([
                'cart_id' => $cart->id,
                'raffle_id' => $raffle->id,
                'quantity' => 5,
                'unit_price' => 5000,
            ]);

            Livewire::test(CartDrawer::class)
                ->assertSet('count', 5);
        });

        it('shows correct count for authenticated user', function () {
            $user = User::factory()->create();
            $raffle = Raffle::factory()->create(['ticket_price' => 5000]);
            $cart = Cart::factory()->create(['user_id' => $user->id]);
            CartItem::factory()->create([
                'cart_id' => $cart->id,
                'raffle_id' => $raffle->id,
                'quantity' => 10,
                'unit_price' => 5000,
            ]);

            Livewire::actingAs($user)
                ->test(CartDrawer::class)
                ->assertSet('count', 10);
        });
    });

    describe('loadCart', function () {
        it('loads cart with items', function () {
            $raffle = Raffle::factory()->create(['ticket_price' => 5000]);
            $cart = Cart::factory()->create(['session_id' => session()->getId()]);
            CartItem::factory()->create([
                'cart_id' => $cart->id,
                'raffle_id' => $raffle->id,
                'quantity' => 5,
                'unit_price' => 5000,
            ]);

            $component = Livewire::test(CartDrawer::class);

            expect($component->get('cart'))->not->toBeNull()
                ->and($component->get('cart')->id)->toBe($cart->id);
        });

        it('returns null cart when no cart exists', function () {
            $component = Livewire::test(CartDrawer::class);

            expect($component->get('cart'))->toBeNull();
        });
    });

    describe('incrementItem', function () {
        it('increments item quantity', function () {
            $raffle = Raffle::factory()->create([
                'status' => RaffleStatus::Active,
                'ticket_price' => 5000,
                'total_tickets' => 1000,
                'sold_tickets' => 0,
                'min_purchase_qty' => 1,
                'quantity_step' => 1,
            ]);
            $cart = Cart::factory()->create(['session_id' => session()->getId()]);
            $item = CartItem::factory()->create([
                'cart_id' => $cart->id,
                'raffle_id' => $raffle->id,
                'quantity' => 5,
                'unit_price' => 5000,
            ]);

            Livewire::test(CartDrawer::class)
                ->call('incrementItem', $item->id)
                ->assertDispatched('cart-updated');

            expect($item->fresh()->quantity)->toBe(6);
        });

        it('respects quantity step', function () {
            $raffle = Raffle::factory()->create([
                'status' => RaffleStatus::Active,
                'ticket_price' => 5000,
                'total_tickets' => 1000,
                'sold_tickets' => 0,
                'min_purchase_qty' => 5,
                'quantity_step' => 5,
            ]);
            $cart = Cart::factory()->create(['session_id' => session()->getId()]);
            $item = CartItem::factory()->create([
                'cart_id' => $cart->id,
                'raffle_id' => $raffle->id,
                'quantity' => 5,
                'unit_price' => 5000,
            ]);

            Livewire::test(CartDrawer::class)
                ->call('incrementItem', $item->id);

            expect($item->fresh()->quantity)->toBe(10);
        });

        it('does not exceed max purchase quantity', function () {
            $raffle = Raffle::factory()->create([
                'status' => RaffleStatus::Active,
                'ticket_price' => 5000,
                'total_tickets' => 1000,
                'sold_tickets' => 0,
                'min_purchase_qty' => 1,
                'max_purchase_qty' => 10,
                'quantity_step' => 1,
            ]);
            $cart = Cart::factory()->create(['session_id' => session()->getId()]);
            $item = CartItem::factory()->create([
                'cart_id' => $cart->id,
                'raffle_id' => $raffle->id,
                'quantity' => 10,
                'unit_price' => 5000,
            ]);

            Livewire::test(CartDrawer::class)
                ->call('incrementItem', $item->id)
                ->assertDispatched('notify');

            expect($item->fresh()->quantity)->toBe(10);
        });

        it('ignores invalid item id', function () {
            $cart = Cart::factory()->create(['session_id' => session()->getId()]);

            Livewire::test(CartDrawer::class)
                ->call('incrementItem', 99999)
                ->assertNotDispatched('cart-updated');
        });

        it('ignores item from different cart', function () {
            $raffle = Raffle::factory()->create(['ticket_price' => 5000]);
            Cart::factory()->create(['session_id' => session()->getId()]);
            $otherCart = Cart::factory()->create();
            $item = CartItem::factory()->create([
                'cart_id' => $otherCart->id,
                'raffle_id' => $raffle->id,
                'quantity' => 5,
                'unit_price' => 5000,
            ]);

            Livewire::test(CartDrawer::class)
                ->call('incrementItem', $item->id)
                ->assertNotDispatched('cart-updated');

            expect($item->fresh()->quantity)->toBe(5);
        });
    });

    describe('decrementItem', function () {
        it('decrements item quantity', function () {
            $raffle = Raffle::factory()->create([
                'status' => RaffleStatus::Active,
                'ticket_price' => 5000,
                'total_tickets' => 1000,
                'sold_tickets' => 0,
                'min_purchase_qty' => 1,
                'quantity_step' => 1,
            ]);
            $cart = Cart::factory()->create(['session_id' => session()->getId()]);
            $item = CartItem::factory()->create([
                'cart_id' => $cart->id,
                'raffle_id' => $raffle->id,
                'quantity' => 5,
                'unit_price' => 5000,
            ]);

            Livewire::test(CartDrawer::class)
                ->call('decrementItem', $item->id)
                ->assertDispatched('cart-updated');

            expect($item->fresh()->quantity)->toBe(4);
        });

        it('removes item when quantity goes below minimum', function () {
            $raffle = Raffle::factory()->create([
                'status' => RaffleStatus::Active,
                'ticket_price' => 5000,
                'total_tickets' => 1000,
                'sold_tickets' => 0,
                'min_purchase_qty' => 5,
                'quantity_step' => 5,
            ]);
            $cart = Cart::factory()->create(['session_id' => session()->getId()]);
            $item = CartItem::factory()->create([
                'cart_id' => $cart->id,
                'raffle_id' => $raffle->id,
                'quantity' => 5,
                'unit_price' => 5000,
            ]);

            Livewire::test(CartDrawer::class)
                ->call('decrementItem', $item->id)
                ->assertDispatched('cart-updated');

            expect(CartItem::find($item->id))->toBeNull();
        });
    });

    describe('removeItem', function () {
        it('removes item from cart', function () {
            $raffle = Raffle::factory()->create(['ticket_price' => 5000]);
            $cart = Cart::factory()->create(['session_id' => session()->getId()]);
            $item = CartItem::factory()->create([
                'cart_id' => $cart->id,
                'raffle_id' => $raffle->id,
                'quantity' => 5,
                'unit_price' => 5000,
            ]);

            Livewire::test(CartDrawer::class)
                ->call('removeItem', $item->id)
                ->assertDispatched('cart-updated');

            expect(CartItem::find($item->id))->toBeNull();
        });

        it('ignores item from different cart', function () {
            $raffle = Raffle::factory()->create(['ticket_price' => 5000]);
            Cart::factory()->create(['session_id' => session()->getId()]);
            $otherCart = Cart::factory()->create();
            $item = CartItem::factory()->create([
                'cart_id' => $otherCart->id,
                'raffle_id' => $raffle->id,
                'quantity' => 5,
                'unit_price' => 5000,
            ]);

            Livewire::test(CartDrawer::class)
                ->call('removeItem', $item->id)
                ->assertNotDispatched('cart-updated');

            expect(CartItem::find($item->id))->not->toBeNull();
        });
    });

    describe('refreshCart', function () {
        it('refreshes cart on cart-updated event', function () {
            $raffle = Raffle::factory()->create(['ticket_price' => 5000]);
            $cart = Cart::factory()->create(['session_id' => session()->getId()]);
            CartItem::factory()->create([
                'cart_id' => $cart->id,
                'raffle_id' => $raffle->id,
                'quantity' => 5,
                'unit_price' => 5000,
            ]);

            $component = Livewire::test(CartDrawer::class);
            expect($component->get('count'))->toBe(5);

            // Add another item directly
            CartItem::factory()->create([
                'cart_id' => $cart->id,
                'raffle_id' => Raffle::factory()->create(['ticket_price' => 3000])->id,
                'quantity' => 3,
                'unit_price' => 3000,
            ]);

            // Dispatch event to refresh
            $component->dispatch('cart-updated');

            expect($component->get('count'))->toBe(8);
        });
    });

    describe('view', function () {
        it('shows empty cart message when cart is empty', function () {
            Livewire::test(CartDrawer::class)
                ->assertSee('Tu carrito está vacío');
        });

        it('shows cart items when cart has items', function () {
            $raffle = Raffle::factory()->create([
                'title' => 'Test Raffle',
                'ticket_price' => 5000,
            ]);
            $cart = Cart::factory()->create(['session_id' => session()->getId()]);
            CartItem::factory()->create([
                'cart_id' => $cart->id,
                'raffle_id' => $raffle->id,
                'quantity' => 5,
                'unit_price' => 5000,
            ]);

            Livewire::test(CartDrawer::class)
                ->assertSee('Test Raffle')
                ->assertSee('Ir al checkout')
                ->assertSee('Ver carrito completo')
                ->assertDontSee('Tu carrito está vacío');
        });
    });
});
