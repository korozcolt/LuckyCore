<?php

namespace App\Livewire\Pages\Raffles;

use App\Models\Raffle;
use App\Services\CartService;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Raffle detail page component.
 *
 * @see PANTALLAS.md §A3 - Detalle de sorteo
 */
class Show extends Component
{
    #[Locked]
    public Raffle $raffle;

    public ?int $selectedPackageId = null;

    public int $quantity = 1;

    public bool $showAddedToCart = false;

    public function mount(Raffle $raffle): void
    {
        $this->raffle = $raffle->load([
            'images' => fn ($q) => $q->orderBy('sort_order'),
            'packages' => fn ($q) => $q->active()->orderBy('sort_order'),
            'activePrizes' => fn ($q) => $q->orderBy('sort_order')->orderBy('prize_position'),
        ]);

        // Pre-select recommended package if available
        $recommended = $this->raffle->packages->firstWhere('is_recommended', true);
        if ($recommended) {
            $this->selectedPackageId = $recommended->id;
            $this->quantity = $recommended->quantity;
        } else {
            $this->quantity = $this->raffle->min_purchase_qty;
        }
    }

    public function selectPackage(int $packageId): void
    {
        $package = $this->raffle->packages->find($packageId);
        if ($package) {
            $this->selectedPackageId = $packageId;
            $this->quantity = $package->quantity;
        }
    }

    public function incrementQuantity(): void
    {
        $step = $this->raffle->quantity_step ?? 1;
        $max = $this->raffle->max_purchase_qty ?? PHP_INT_MAX;

        if ($this->quantity + $step <= $max) {
            $this->quantity += $step;
            $this->selectedPackageId = null; // Clear package selection
        }
    }

    public function decrementQuantity(): void
    {
        $step = $this->raffle->quantity_step ?? 1;
        $min = $this->raffle->min_purchase_qty;

        if ($this->quantity - $step >= $min) {
            $this->quantity -= $step;
            $this->selectedPackageId = null; // Clear package selection
        }
    }

    public function getSelectedPackageProperty(): ?\App\Models\RafflePackage
    {
        if ($this->selectedPackageId) {
            return $this->raffle->packages->find($this->selectedPackageId);
        }

        return null;
    }

    public function getSubtotalProperty(): int
    {
        if ($this->selectedPackage) {
            return $this->selectedPackage->price ?? 0;
        }

        return $this->quantity * $this->raffle->ticket_price;
    }

    public function addToCart(CartService $cartService): void
    {
        try {
            $cart = $cartService->getOrCreateCart(
                auth()->user(),
                session()->getId()
            );

            $package = $this->selectedPackageId
                ? $this->raffle->packages->find($this->selectedPackageId)
                : null;

            $cartService->addItem($cart, $this->raffle, $this->quantity, $package);

            $this->showAddedToCart = true;
            $this->dispatch('cart-updated');
            $this->dispatch('open-cart-drawer');
        } catch (\InvalidArgumentException $e) {
            $this->dispatch('notify', message: $e->getMessage(), type: 'error');
        }
    }

    public function goToCart(): void
    {
        $this->redirect(route('cart'), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.pages.raffles.show')
            ->layout('layouts.public', ['title' => $this->raffle->title]);
    }
}
