<?php

namespace App\Livewire\Pages\Cart;

use App\Models\Cart;
use App\Models\CartItem;
use App\Services\CartService;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * Shopping cart page component.
 *
 * @see PANTALLAS.md §A4 - Carrito (multi-sorteo)
 */
class Index extends Component
{
    public array $validationErrors = [];

    public function mount(CartService $cartService): void
    {
        $this->validateCartItems($cartService);
    }

    #[Computed]
    public function cart(): ?Cart
    {
        return app(CartService::class)->findActiveCart(
            auth()->user(),
            session()->getId()
        );
    }

    public function incrementQuantity(int $itemId, CartService $cartService): void
    {
        $item = CartItem::with('raffle')->find($itemId);
        if (! $item || $item->cart_id !== $this->cart?->id) {
            return;
        }

        $raffle = $item->raffle;
        $step = $raffle->quantity_step ?? 1;
        $max = $raffle->max_purchase_qty ?? PHP_INT_MAX;
        $newQuantity = $item->quantity + $step;

        if ($newQuantity <= $max && $newQuantity <= $raffle->available_tickets) {
            try {
                $cartService->updateItem($item, $newQuantity);
                unset($this->validationErrors[$raffle->slug]);
                $this->dispatch('cart-updated');
            } catch (\InvalidArgumentException $e) {
                $this->validationErrors[$raffle->slug] = $e->getMessage();
            }
        }

        unset($this->cart);
    }

    public function decrementQuantity(int $itemId, CartService $cartService): void
    {
        $item = CartItem::with('raffle')->find($itemId);
        if (! $item || $item->cart_id !== $this->cart?->id) {
            return;
        }

        $raffle = $item->raffle;
        $step = $raffle->quantity_step ?? 1;
        $min = $raffle->min_purchase_qty;
        $newQuantity = $item->quantity - $step;

        if ($newQuantity >= $min) {
            try {
                $cartService->updateItem($item, $newQuantity);
                unset($this->validationErrors[$raffle->slug]);
                $this->dispatch('cart-updated');
            } catch (\InvalidArgumentException $e) {
                $this->validationErrors[$raffle->slug] = $e->getMessage();
            }
        }

        unset($this->cart);
    }

    public function removeItem(int $itemId, CartService $cartService): void
    {
        $item = CartItem::with('raffle')->find($itemId);
        if (! $item || $item->cart_id !== $this->cart?->id) {
            return;
        }

        $slug = $item->raffle->slug;
        $cartService->removeItem($item);
        unset($this->validationErrors[$slug]);
        $this->dispatch('cart-updated');
        $this->dispatch('notify', message: 'Producto eliminado del carrito', type: 'info');

        unset($this->cart);
    }

    public function selectPackage(int $itemId, int $packageId, CartService $cartService): void
    {
        $item = CartItem::with('raffle.packages')->find($itemId);
        if (! $item || $item->cart_id !== $this->cart?->id) {
            return;
        }

        $package = $item->raffle->packages->find($packageId);
        if (! $package) {
            return;
        }

        try {
            $cartService->updateItem($item, $package->quantity, $package);
            unset($this->validationErrors[$item->raffle->slug]);
            $this->dispatch('cart-updated');
        } catch (\InvalidArgumentException $e) {
            $this->validationErrors[$item->raffle->slug] = $e->getMessage();
        }

        unset($this->cart);
    }

    public function proceedToCheckout(): void
    {
        if (! $this->cart || $this->cart->isEmpty()) {
            $this->dispatch('notify', message: 'Tu carrito está vacío', type: 'error');
            return;
        }

        if (! empty($this->validationErrors)) {
            $this->dispatch('notify', message: 'Hay errores en tu carrito que debes corregir', type: 'error');
            return;
        }

        $this->redirect(route('checkout'), navigate: true);
    }

    protected function validateCartItems(CartService $cartService): void
    {
        $cart = $this->cart;
        if ($cart) {
            $this->validationErrors = $cartService->validateCart($cart);
        }
    }

    public function render(): View
    {
        return view('livewire.pages.cart.index')
            ->layout('layouts.public', ['title' => 'Carrito de compras']);
    }
}
