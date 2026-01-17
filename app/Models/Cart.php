<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'user_id',
        'ip_address',
        'user_agent',
        'merged_at',
        'converted_at',
    ];

    protected function casts(): array
    {
        return [
            'merged_at' => 'datetime',
            'converted_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Cart $cart) {
            $cart->ulid ??= (string) Str::ulid();
        });
    }

    // Relationships

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    // Computed attributes

    public function getTotalAttribute(): int
    {
        return $this->items->sum(fn (CartItem $item) => $item->subtotal);
    }

    public function getFormattedTotalAttribute(): string
    {
        return '$' . number_format($this->total / 100, 0, ',', '.');
    }

    public function getItemCountAttribute(): int
    {
        return $this->items->sum('quantity');
    }

    // Query scopes

    public function scopeActive($query)
    {
        return $query->whereNull('converted_at');
    }

    public function scopeForSession($query, string $sessionId)
    {
        return $query->where('session_id', $sessionId)->active();
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId)->active();
    }

    // Business logic

    public function isGuest(): bool
    {
        return $this->user_id === null;
    }

    public function isEmpty(): bool
    {
        return $this->items->isEmpty();
    }

    public function hasItem(int $raffleId): bool
    {
        return $this->items->contains('raffle_id', $raffleId);
    }

    public function getItem(int $raffleId): ?CartItem
    {
        return $this->items->firstWhere('raffle_id', $raffleId);
    }
}
