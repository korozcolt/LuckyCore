<?php

use App\Enums\OrderStatus;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

it('implements filament enum contracts', function () {
    expect(OrderStatus::Pending)
        ->toBeInstanceOf(HasColor::class)
        ->toBeInstanceOf(HasIcon::class)
        ->toBeInstanceOf(HasLabel::class);
});

it('returns expected labels, colors and icons', function (OrderStatus $status, string $label, string $color, string $icon) {
    expect($status->getLabel())->toBe($label);
    expect($status->getColor())->toBe($color);
    expect($status->getIcon())->toBe($icon);
})->with([
    'pending' => [OrderStatus::Pending, 'Pendiente', 'warning', 'heroicon-o-clock'],
    'paid' => [OrderStatus::Paid, 'Pagado', 'success', 'heroicon-o-check-circle'],
    'failed' => [OrderStatus::Failed, 'Rechazado', 'danger', 'heroicon-o-x-circle'],
    'expired' => [OrderStatus::Expired, 'Expirado', 'danger', 'heroicon-o-exclamation-triangle'],
    'refunded' => [OrderStatus::Refunded, 'Reembolsado', 'gray', 'heroicon-o-arrow-uturn-left'],
    'partial refund' => [OrderStatus::PartialRefund, 'Reembolso parcial', 'gray', 'heroicon-o-arrow-uturn-left'],
]);
