<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

/**
 * Types of winning conditions for raffle prizes.
 *
 * @see REGLAS_NEGOCIO.md §6 - Premios múltiples
 */
enum WinningConditionType: string implements HasColor, HasIcon, HasLabel
{
    case ExactMatch = 'exact_match';
    case Reverse = 'reverse';
    case Permutation = 'permutation';
    case LastDigits = 'last_digits';
    case FirstDigits = 'first_digits';
    case Combination = 'combination';

    public function getLabel(): string
    {
        return match ($this) {
            self::ExactMatch => 'Número exacto',
            self::Reverse => 'Número al revés',
            self::Permutation => 'Cualquier permutación',
            self::LastDigits => 'Últimos dígitos',
            self::FirstDigits => 'Primeros dígitos',
            self::Combination => 'Combinación específica',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::ExactMatch => 'success',
            self::Reverse => 'warning',
            self::Permutation => 'info',
            self::LastDigits => 'primary',
            self::FirstDigits => 'primary',
            self::Combination => 'gray',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::ExactMatch => 'heroicon-o-check-circle',
            self::Reverse => 'heroicon-o-arrow-uturn-left',
            self::Permutation => 'heroicon-o-arrows-right-left',
            self::LastDigits => 'heroicon-o-arrow-right',
            self::FirstDigits => 'heroicon-o-arrow-left',
            self::Combination => 'heroicon-o-sparkles',
        };
    }

    public function getDescription(): string
    {
        return match ($this) {
            self::ExactMatch => 'El ticket debe coincidir exactamente con el número ganador de la lotería',
            self::Reverse => 'El ticket debe ser el número ganador de la lotería pero invertido (ej: 12345 → 54321)',
            self::Permutation => 'El ticket debe contener los mismos dígitos del número ganador en cualquier orden',
            self::LastDigits => 'El ticket debe coincidir en los últimos N dígitos con el número ganador',
            self::FirstDigits => 'El ticket debe coincidir en los primeros N dígitos con el número ganador',
            self::Combination => 'Combinación personalizada de condiciones',
        };
    }
}
