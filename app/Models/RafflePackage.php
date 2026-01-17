<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RafflePackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'raffle_id',
        'name',
        'quantity',
        'price',
        'is_recommended',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'price' => 'integer',
            'is_recommended' => 'boolean',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    // Relationships

    public function raffle(): BelongsTo
    {
        return $this->belongsTo(Raffle::class);
    }

    // Computed attributes

    public function getFormattedPriceAttribute(): string
    {
        return '$' . number_format($this->price / 100, 0, ',', '.');
    }

    public function getUnitPriceAttribute(): int
    {
        if ($this->quantity === 0) {
            return 0;
        }

        return (int) round($this->price / $this->quantity);
    }

    public function getDiscountPercentageAttribute(): ?float
    {
        if (!$this->raffle || $this->quantity === 0) {
            return null;
        }

        $regularPrice = $this->raffle->ticket_price * $this->quantity;
        if ($regularPrice === 0) {
            return null;
        }

        $discount = (($regularPrice - $this->price) / $regularPrice) * 100;

        return $discount > 0 ? round($discount, 1) : null;
    }

    // Query scopes

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeRecommended($query)
    {
        return $query->where('is_recommended', true);
    }
}
