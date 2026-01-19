<?php

declare(strict_types=1);

namespace App\Livewire\Pages;

use App\Enums\RaffleStatus;
use App\Models\Raffle;
use App\Models\Winner;
use App\Models\WinnerTestimonial;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Public winners page.
 *
 * Shows completed raffles with their winners and testimonials.
 */
class Winners extends Component
{
    use WithPagination;

    public ?int $selectedRaffleId = null;

    public function selectRaffle(?int $raffleId): void
    {
        $this->selectedRaffleId = $raffleId;
        $this->resetPage();
    }

    public function render(): View
    {
        // Get completed raffles with published results
        $completedRaffles = Raffle::query()
            ->where('status', RaffleStatus::Completed)
            ->whereHas('result', fn ($q) => $q->where('is_published', true))
            ->with(['primaryImage', 'result'])
            ->orderByDesc('draw_at')
            ->get();

        // Get winners for selected raffle or all published winners
        $winnersQuery = Winner::query()
            ->published()
            ->with(['raffle.primaryImage', 'prize', 'testimonial' => fn ($q) => $q->approved()])
            ->orderByDesc('created_at');

        if ($this->selectedRaffleId) {
            $winnersQuery->where('raffle_id', $this->selectedRaffleId);
        }

        $winners = $winnersQuery->paginate(12);

        // Get featured testimonials for homepage display
        $featuredTestimonials = WinnerTestimonial::query()
            ->approved()
            ->featured()
            ->with(['winner.raffle.primaryImage'])
            ->orderByDesc('created_at')
            ->take(6)
            ->get();

        return view('livewire.pages.winners', [
            'completedRaffles' => $completedRaffles,
            'winners' => $winners,
            'featuredTestimonials' => $featuredTestimonials,
        ])->layout('layouts.public', [
            'title' => 'Ganadores',
            'description' => 'Conoce a los ganadores de nuestros sorteos',
        ]);
    }
}
