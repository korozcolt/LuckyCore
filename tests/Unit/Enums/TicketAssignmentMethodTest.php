<?php

use App\Enums\TicketAssignmentMethod;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

it('implements filament enum contracts', function () {
    expect(TicketAssignmentMethod::Random)
        ->toBeInstanceOf(HasIcon::class)
        ->toBeInstanceOf(HasLabel::class);
});

it('returns expected labels and icons', function (TicketAssignmentMethod $method, string $label, string $icon) {
    expect($method->getLabel())->toBe($label);
    expect($method->getIcon())->toBe($icon);
})->with([
    'random' => [TicketAssignmentMethod::Random, 'Aleatorio', 'heroicon-o-arrows-right-left'],
    'sequential' => [TicketAssignmentMethod::Sequential, 'Secuencial', 'heroicon-o-bars-3'],
]);
