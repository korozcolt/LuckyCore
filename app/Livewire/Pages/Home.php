<?php

namespace App\Livewire\Pages;

use App\Enums\RaffleStatus;
use App\Models\Raffle;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Home page component.
 *
 * @see PANTALLAS.md §A1 - Home
 */
class Home extends Component
{
    public function render(): View
    {
        return view('livewire.pages.home', [
            'featuredRaffles' => Raffle::query()
                ->where('status', RaffleStatus::Active)
                ->where('featured', true)
                ->with(['primaryImage', 'packages' => fn ($q) => $q->active()->orderBy('sort_order')])
                ->orderBy('sort_order')
                ->take(4)
                ->get(),
            'activeRaffles' => Raffle::query()
                ->where('status', RaffleStatus::Active)
                ->with(['primaryImage'])
                ->orderBy('sort_order')
                ->take(8)
                ->get(),
        ])->layout('layouts.public');
    }
}
