<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'raffle_id',
        'order_id',
        'order_item_id',
        'user_id',
        'code',
        'is_winner',
        'prize_position',
        'prize_id',
        'won_at',
    ];

    protected function casts(): array
    {
        return [
            'is_winner' => 'boolean',
            'prize_position' => 'integer',
            'won_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Ticket $ticket) {
            $ticket->ulid ??= (string) Str::ulid();
        });
    }

    // Relationships

    public function raffle(): BelongsTo
    {
        return $this->belongsTo(Raffle::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function prize(): BelongsTo
    {
        return $this->belongsTo(RafflePrize::class, 'prize_id');
    }

    // Computed attributes

    public function getFormattedCodeAttribute(): string
    {
        // Format ticket code for display using raffle's ticket_digits configuration
        $digits = $this->raffle->ticket_digits ?? 5;

        return str_pad($this->code, $digits, '0', STR_PAD_LEFT);
    }

    /**
     * Alias for code - used by WinnerCalculationService.
     */
    public function getTicketNumberAttribute(): ?string
    {
        return $this->code;
    }

    // Query scopes

    public function scopeWinners($query)
    {
        return $query->where('is_winner', true);
    }

    public function scopeForRaffle($query, int $raffleId)
    {
        return $query->where('raffle_id', $raffleId);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }
}
