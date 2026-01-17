<?php

use App\Enums\PaymentProvider;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

it('implements filament enum contracts', function () {
    expect(PaymentProvider::Wompi)
        ->toBeInstanceOf(HasIcon::class)
        ->toBeInstanceOf(HasLabel::class);
});

it('returns expected labels and icons', function (PaymentProvider $provider, string $label, string $icon) {
    expect($provider->getLabel())->toBe($label);
    expect($provider->getIcon())->toBe($icon);
})->with([
    'wompi' => [PaymentProvider::Wompi, 'Wompi', 'heroicon-o-banknotes'],
    'mercadopago' => [PaymentProvider::MercadoPago, 'MercadoPago', 'heroicon-o-credit-card'],
    'epayco' => [PaymentProvider::Epayco, 'ePayco', 'heroicon-o-building-library'],
]);
