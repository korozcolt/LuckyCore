<?php

use App\Enums\PaymentStatus;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

it('implements filament enum contracts', function () {
    expect(PaymentStatus::Pending)
        ->toBeInstanceOf(HasColor::class)
        ->toBeInstanceOf(HasIcon::class)
        ->toBeInstanceOf(HasLabel::class);
});

it('returns expected labels, colors and icons', function (PaymentStatus $status, string $label, string $color, string $icon) {
    expect($status->getLabel())->toBe($label);
    expect($status->getColor())->toBe($color);
    expect($status->getIcon())->toBe($icon);
})->with([
    'pending' => [PaymentStatus::Pending, 'Pendiente', 'warning', 'heroicon-o-clock'],
    'processing' => [PaymentStatus::Processing, 'Procesando', 'warning', 'heroicon-o-arrow-path'],
    'approved' => [PaymentStatus::Approved, 'Aprobado', 'success', 'heroicon-o-check-circle'],
    'rejected' => [PaymentStatus::Rejected, 'Rechazado', 'danger', 'heroicon-o-x-circle'],
    'expired' => [PaymentStatus::Expired, 'Expirado', 'danger', 'heroicon-o-exclamation-triangle'],
    'refunded' => [PaymentStatus::Refunded, 'Reembolsado', 'gray', 'heroicon-o-arrow-uturn-left'],
    'voided' => [PaymentStatus::Voided, 'Anulado', 'danger', 'heroicon-o-no-symbol'],
]);
