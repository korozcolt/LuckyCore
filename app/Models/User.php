<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserRole;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasRoles, Notifiable, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Check if user can access Filament admin panel.
     *
     * Solo SuperAdmin y Admin pueden acceder al panel de administración.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->hasAnyRole([
            UserRole::Admin->value,
            UserRole::SuperAdmin->value,
        ]);
    }

    /**
     * Check if the user is a super admin.
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole(UserRole::SuperAdmin->value);
    }

    /**
     * Check if the user is an admin (admin or super admin).
     */
    public function isAdmin(): bool
    {
        return $this->hasAnyRole([
            UserRole::Admin->value,
            UserRole::SuperAdmin->value,
        ]);
    }

    /**
     * Check if the user is support staff.
     */
    public function isSupport(): bool
    {
        return $this->hasRole(UserRole::Support->value);
    }

    /**
     * Check if the user is a regular customer.
     */
    public function isCustomer(): bool
    {
        return $this->hasRole(UserRole::Customer->value) || $this->roles->isEmpty();
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    // Relationships

    public function cart(): HasOne
    {
        return $this->hasOne(Cart::class)->whereNull('converted_at');
    }

    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    // Helper methods

    public function getActiveCart(): ?Cart
    {
        return $this->cart;
    }

    public function getTicketsForRaffle(int $raffleId): \Illuminate\Database\Eloquent\Collection
    {
        return $this->tickets()->where('raffle_id', $raffleId)->get();
    }
}
