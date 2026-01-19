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

it('only allows Admin and SuperAdmin to access admin panel', function (UserRole $role, bool $canAccess) {
    expect($role->canAccessAdmin())->toBe($canAccess);
})->with([
    'customer cannot access' => [UserRole::Customer, false],
    'support cannot access' => [UserRole::Support, false],
    'admin can access' => [UserRole::Admin, true],
    'super admin can access' => [UserRole::SuperAdmin, true],
]);

it('returns correct admin roles', function () {
    $adminRoles = UserRole::adminRoles();

    expect($adminRoles)->toHaveCount(2)
        ->toContain(UserRole::Admin)
        ->toContain(UserRole::SuperAdmin)
        ->not->toContain(UserRole::Support)
        ->not->toContain(UserRole::Customer);
});
