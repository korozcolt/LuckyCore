<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/**
 * Payment transaction status enum.
 *
 * @see REGLAS_NEGOCIO.md §3 - Estados: pending/paid/failed/expired
 * @see ALCANCE.md §3 - Estados de pago: pending/approved/rejected/expired/refunded
 */
enum PaymentStatus: string implements HasColor, HasLabel
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
            self::Processing => 'info',
            self::Approved => 'success',
            self::Rejected => 'danger',
            self::Expired => 'gray',
            self::Refunded => 'info',
            self::Voided => 'gray',
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
