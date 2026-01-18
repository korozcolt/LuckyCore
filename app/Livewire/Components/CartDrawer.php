<?php

declare(strict_types=1);

namespace App\Livewire\Components;

use App\Models\Cart;
use App\Models\CartItem;
use App\Services\CartService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Cart drawer component for header with slide-over functionality.
 */
class CartDrawer extends Component
{
    public ?Cart $cart = null;

    public function mount(): void
    {
        $this->loadCart();
    }

    #[Computed]
    public function count(): int
    {
        return app(CartService::class)->getItemCount(
            auth()->user(),
            session()->getId()
        );
    }

    #[On('cart-updated')]
    public function refreshCart(): void
    {
        unset($this->count);
        $this->loadCart();
    }

    #[On('open-cart-drawer')]
    public function openDrawer(): void
    {
        $this->loadCart();
        $this->dispatch('modal-show', name: 'cart-drawer');
    }

    public function loadCart(): void
    {
        $this->cart = app(CartService::class)->findActiveCart(
            auth()->user(),
            session()->getId()
        );
    }

    public function incrementItem(int $itemId): void
    {
        $item = CartItem::find($itemId);

        if (! $item || $item->cart_id !== $this->cart?->id) {
            return;
        }

        $raffle = $item->raffle;
        $newQuantity = $item->quantity + ($raffle->quantity_step ?: 1);

        // Check max quantity constraints
        if ($raffle->max_purchase_qty && $newQuantity > $raffle->max_purchase_qty) {
            $this->dispatch('notify', message: "Máximo {$raffle->max_purchase_qty} boletos permitidos.", type: 'warning');

            return;
        }

        // Check available stock
        if ($newQuantity > $raffle->available_tickets) {
            $this->dispatch('notify', message: "Solo quedan {$raffle->available_tickets} boletos disponibles.", type: 'warning');

            return;
        }

        try {
            app(CartService::class)->updateItem($item, $newQuantity, $item->package);
            $this->refreshCart();
            $this->dispatch('cart-updated');
        } catch (\InvalidArgumentException $e) {
            $this->dispatch('notify', message: $e->getMessage(), type: 'error');
        }
    }

    public function decrementItem(int $itemId): void
    {
        $item = CartItem::find($itemId);

        if (! $item || $item->cart_id !== $this->cart?->id) {
            return;
        }

        $raffle = $item->raffle;
        $step = $raffle->quantity_step ?: 1;
        $newQuantity = $item->quantity - $step;

        // If quantity would go below minimum, remove the item
        if ($newQuantity < $raffle->min_purchase_qty) {
            $this->removeItem($itemId);

            return;
        }

        try {
            app(CartService::class)->updateItem($item, $newQuantity, $item->package);
            $this->refreshCart();
            $this->dispatch('cart-updated');
        } catch (\InvalidArgumentException $e) {
            $this->dispatch('notify', message: $e->getMessage(), type: 'error');
        }
    }

    public function removeItem(int $itemId): void
    {
        $item = CartItem::find($itemId);

        if (! $item || $item->cart_id !== $this->cart?->id) {
            return;
        }

        app(CartService::class)->removeItem($item);
        $this->refreshCart();
        $this->dispatch('cart-updated');
    }

    public function render()
    {
        return view('livewire.components.cart-drawer');
    }
}
