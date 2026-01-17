<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'raffle_id',
        'raffle_package_id',
        'quantity',
        'unit_price',
        'subtotal',
        'raffle_title',
        'tickets_assigned',
        'tickets_complete',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price' => 'integer',
            'subtotal' => 'integer',
            'tickets_assigned' => 'integer',
            'tickets_complete' => 'boolean',
        ];
    }

    // Relationships

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function raffle(): BelongsTo
    {
        return $this->belongsTo(Raffle::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(RafflePackage::class, 'raffle_package_id');
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    // Computed attributes

    public function getFormattedSubtotalAttribute(): string
    {
        return '$' . number_format($this->subtotal / 100, 0, ',', '.');
    }

    public function getFormattedUnitPriceAttribute(): string
    {
        return '$' . number_format($this->unit_price / 100, 0, ',', '.');
    }

    public function getPendingTicketsAttribute(): int
    {
        return $this->quantity - $this->tickets_assigned;
    }
}
