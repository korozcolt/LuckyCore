<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/**
 * Order status enum.
 *
 * @see ALCANCE.md §3 - Confirmacion: aprobado/pendiente/rechazado/expirado
 * @see REGLAS_NEGOCIO.md §2 - Orden
 */
enum OrderStatus: string implements HasColor, HasLabel
{
    case Pending = 'pending';
    case Paid = 'paid';
    case Failed = 'failed';
    case Expired = 'expired';
    case Refunded = 'refunded';
    case PartialRefund = 'partial_refund';

    public function getLabel(): string
    {
        return match ($this) {
            self::Pending => 'Pendiente',
            self::Paid => 'Pagado',
            self::Failed => 'Rechazado',
            self::Expired => 'Expirado',
            self::Refunded => 'Reembolsado',
            self::PartialRefund => 'Reembolso parcial',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Paid => 'success',
            self::Failed => 'danger',
            self::Expired => 'gray',
            self::Refunded => 'info',
            self::PartialRefund => 'info',
        };
    }

    public function isSuccessful(): bool
    {
        return $this === self::Paid;
    }

    public function canRetry(): bool
    {
        return in_array($this, [self::Failed, self::Expired]);
    }

    /**
     * Check if an order with this status can be paid.
     * Includes pending orders (first time) and failed/expired (retry).
     */
    public function canPay(): bool
    {
        return in_array($this, [self::Pending, self::Failed, self::Expired]);
    }
}
