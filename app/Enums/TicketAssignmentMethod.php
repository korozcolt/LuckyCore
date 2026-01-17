<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

/**
 * Ticket assignment method enum.
 *
 * @see REGLAS_NEGOCIO.md §4 - Metodo por sorteo: random (default) / sequential
 * @see ALCANCE.md §3 - Tickets no secuenciales por defecto; configurable por sorteo
 */
enum TicketAssignmentMethod: string implements HasIcon, HasLabel
{
    case Random = 'random';
    case Sequential = 'sequential';

    public function getLabel(): string
    {
        return match ($this) {
            self::Random => 'Aleatorio',
            self::Sequential => 'Secuencial',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Random => 'heroicon-o-arrows-right-left',
            self::Sequential => 'heroicon-o-bars-3',
        };
    }

    public static function default(): self
    {
        return self::Random;
    }
}
