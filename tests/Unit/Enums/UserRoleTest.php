<?php

use App\Enums\UserRole;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

it('implements filament enum contracts', function () {
    expect(UserRole::Customer)
        ->toBeInstanceOf(HasColor::class)
        ->toBeInstanceOf(HasIcon::class)
        ->toBeInstanceOf(HasLabel::class);
});

it('returns expected labels, colors and icons', function (UserRole $role, string $label, string $color, string $icon) {
    expect($role->getLabel())->toBe($label);
    expect($role->getColor())->toBe($color);
    expect($role->getIcon())->toBe($icon);
})->with([
    'customer' => [UserRole::Customer, 'Cliente', 'info', 'heroicon-o-user'],
    'support' => [UserRole::Support, 'Soporte', 'warning', 'heroicon-o-lifebuoy'],
    'admin' => [UserRole::Admin, 'Administrador', 'success', 'heroicon-o-shield-check'],
    'super admin' => [UserRole::SuperAdmin, 'Super Administrador', 'danger', 'heroicon-o-shield-exclamation'],
]);
