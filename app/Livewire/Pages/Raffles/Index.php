<?php

namespace App\Livewire\Pages\Raffles;

use App\Enums\RaffleStatus;
use App\Models\Raffle;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Raffle listing page component.
 *
 * @see PANTALLAS.md §A2 - Listado de sorteos
 */
class Index extends Component
{
    use WithPagination;

    public string $status = '';

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $query = Raffle::query()
            ->public()
            ->with(['primaryImage'])
            ->orderByRaw("FIELD(status, 'active', 'upcoming', 'closed', 'completed')")
            ->orderBy('sort_order');

        if ($this->status) {
            $query->where('status', $this->status);
        }

        return view('livewire.pages.raffles.index', [
            'raffles' => $query->paginate(12),
        ])->layout('layouts.public', ['title' => 'Sorteos']);
    }
}
