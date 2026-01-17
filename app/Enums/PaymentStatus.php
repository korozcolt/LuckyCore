<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

/**
 * Payment transaction status enum.
 *
 * @see REGLAS_NEGOCIO.md §3 - Estados: pending/paid/failed/expired
 * @see ALCANCE.md §3 - Estados de pago: pending/approved/rejected/expired/refunded
 */
enum PaymentStatus: string implements HasColor, HasIcon, HasLabel
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Expired = 'expired';
    case Refunded = 'refunded';
    case Voided = 'voided';

    public function getLabel(): string
    {
        return match ($this) {
            self::Pending => 'Pendiente',
            self::Processing => 'Procesando',
            self::Approved => 'Aprobado',
            self::Rejected => 'Rechazado',
            self::Expired => 'Expirado',
            self::Refunded => 'Reembolsado',
            self::Voided => 'Anulado',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Processing => 'warning',
            self::Approved => 'success',
            self::Rejected => 'danger',
            self::Expired => 'danger',
            self::Refunded => 'gray',
            self::Voided => 'danger',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Pending => 'heroicon-o-clock',
            self::Processing => 'heroicon-o-arrow-path',
            self::Approved => 'heroicon-o-check-circle',
            self::Rejected => 'heroicon-o-x-circle',
            self::Expired => 'heroicon-o-exclamation-triangle',
            self::Refunded => 'heroicon-o-arrow-uturn-left',
            self::Voided => 'heroicon-o-no-symbol',
        };
    }

    public function isSuccessful(): bool
    {
        return $this === self::Approved;
    }

    public function isFinal(): bool
    {
        return in_array($this, [self::Approved, self::Rejected, self::Expired, self::Refunded, self::Voided]);
    }
}
