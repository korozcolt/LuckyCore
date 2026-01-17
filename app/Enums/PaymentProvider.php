<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

/**
 * Payment provider enum.
 *
 * @see ALCANCE.md §3 - Integracion con pasarelas: Wompi, MercadoPago, ePayco
 * @see ARQUITECTURA.md §5 - PaymentProviderContract
 */
enum PaymentProvider: string implements HasIcon, HasLabel
{
    case Wompi = 'wompi';
    case MercadoPago = 'mercadopago';
    case Epayco = 'epayco';

    public function getLabel(): string
    {
        return match ($this) {
            self::Wompi => 'Wompi',
            self::MercadoPago => 'MercadoPago',
            self::Epayco => 'ePayco',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Wompi => 'heroicon-o-banknotes',
            self::MercadoPago => 'heroicon-o-credit-card',
            self::Epayco => 'heroicon-o-building-library',
        };
    }
}
