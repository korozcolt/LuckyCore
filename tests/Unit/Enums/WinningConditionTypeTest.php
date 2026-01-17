<?php

use App\Enums\WinningConditionType;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

it('implements filament enum contracts', function () {
    expect(WinningConditionType::ExactMatch)
        ->toBeInstanceOf(HasColor::class)
        ->toBeInstanceOf(HasIcon::class)
        ->toBeInstanceOf(HasLabel::class);
});

it('returns expected labels, colors and icons', function (WinningConditionType $type, string $label, string $color, string $icon) {
    expect($type->getLabel())->toBe($label);
    expect($type->getColor())->toBe($color);
    expect($type->getIcon())->toBe($icon);
})->with([
    'exact match' => [WinningConditionType::ExactMatch, 'Número exacto', 'success', 'heroicon-o-check-circle'],
    'reverse' => [WinningConditionType::Reverse, 'Número al revés', 'warning', 'heroicon-o-arrow-uturn-left'],
    'permutation' => [WinningConditionType::Permutation, 'Cualquier permutación', 'info', 'heroicon-o-arrows-right-left'],
    'last digits' => [WinningConditionType::LastDigits, 'Últimos dígitos', 'primary', 'heroicon-o-arrow-right'],
    'first digits' => [WinningConditionType::FirstDigits, 'Primeros dígitos', 'primary', 'heroicon-o-arrow-left'],
    'combination' => [WinningConditionType::Combination, 'Combinación específica', 'gray', 'heroicon-o-sparkles'],
]);
