<?php

use App\Enums\RaffleStatus;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

it('implements filament enum contracts', function () {
    expect(RaffleStatus::Draft)
        ->toBeInstanceOf(HasColor::class)
        ->toBeInstanceOf(HasIcon::class)
        ->toBeInstanceOf(HasLabel::class);
});

it('returns expected labels, colors and icons', function (RaffleStatus $status, string $label, string $color, string $icon) {
    expect($status->getLabel())->toBe($label);
    expect($status->getColor())->toBe($color);
    expect($status->getIcon())->toBe($icon);
})->with([
    'draft' => [RaffleStatus::Draft, 'Borrador', 'gray', 'heroicon-o-pencil-square'],
    'upcoming' => [RaffleStatus::Upcoming, 'Proximo', 'info', 'heroicon-o-calendar-days'],
    'active' => [RaffleStatus::Active, 'Activo', 'success', 'heroicon-o-play'],
    'closed' => [RaffleStatus::Closed, 'Cerrado', 'warning', 'heroicon-o-lock-closed'],
    'completed' => [RaffleStatus::Completed, 'Finalizado', 'primary', 'heroicon-o-trophy'],
    'cancelled' => [RaffleStatus::Cancelled, 'Cancelado', 'danger', 'heroicon-o-x-mark'],
]);
