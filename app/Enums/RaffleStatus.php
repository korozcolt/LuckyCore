<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/**
 * Raffle status enum.
 *
 * @see PANTALLAS.md §A2 - Estados: Activo/Proximo/Finalizado
 * @see REGLAS_NEGOCIO.md §6 - Resultados y ganador
 */
enum RaffleStatus: string implements HasColor, HasLabel
{
    case Draft = 'draft';
    case Upcoming = 'upcoming';
    case Active = 'active';
    case Closed = 'closed';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function getLabel(): string
    {
        return match ($this) {
            self::Draft => 'Borrador',
            self::Upcoming => 'Proximo',
            self::Active => 'Activo',
            self::Closed => 'Cerrado',
            self::Completed => 'Finalizado',
            self::Cancelled => 'Cancelado',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Upcoming => 'info',
            self::Active => 'success',
            self::Closed => 'warning',
            self::Completed => 'primary',
            self::Cancelled => 'danger',
        };
    }

    public function isPublic(): bool
    {
        return in_array($this, [self::Upcoming, self::Active, self::Closed, self::Completed]);
    }

    public function canPurchase(): bool
    {
        return $this === self::Active;
    }
}
