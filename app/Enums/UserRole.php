<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/**
 * User role enum.
 *
 * @see ALCANCE.md §2 - Actores del sistema
 */
enum UserRole: string implements HasColor, HasLabel
{
    case Customer = 'customer';
    case Support = 'support';
    case Admin = 'admin';
    case SuperAdmin = 'super_admin';

    public function getLabel(): string
    {
        return match ($this) {
            self::Customer => 'Cliente',
            self::Support => 'Soporte',
            self::Admin => 'Administrador',
            self::SuperAdmin => 'Super Administrador',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Customer => 'info',
            self::Support => 'warning',
            self::Admin => 'success',
            self::SuperAdmin => 'danger',
        };
    }

    /**
     * Check if this role can access the admin panel.
     */
    public function canAccessAdmin(): bool
    {
        return in_array($this, [self::Support, self::Admin, self::SuperAdmin]);
    }

    /**
     * Check if this role has full admin privileges.
     */
    public function isAdmin(): bool
    {
        return in_array($this, [self::Admin, self::SuperAdmin]);
    }

    /**
     * Check if this is the super admin role.
     */
    public function isSuperAdmin(): bool
    {
        return $this === self::SuperAdmin;
    }

    /**
     * Get the default role for new users.
     */
    public static function default(): self
    {
        return self::Customer;
    }

    /**
     * Get all roles that can access admin panel.
     *
     * @return array<self>
     */
    public static function adminRoles(): array
    {
        return [self::Support, self::Admin, self::SuperAdmin];
    }
}
