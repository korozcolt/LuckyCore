<?php

namespace App\Livewire\Components;

use App\Services\CartService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Cart counter component for header.
 */
class CartCounter extends Component
{
    #[Computed]
    public function count(): int
    {
        return app(CartService::class)->getItemCount(
            auth()->user(),
            session()->getId()
        );
    }

    #[On('cart-updated')]
    public function refreshCount(): void
    {
        unset($this->count);
    }

    public function render()
    {
        return view('livewire.components.cart-counter');
    }
}
