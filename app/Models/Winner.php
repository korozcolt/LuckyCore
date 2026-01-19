<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

/**
 * Represents a winner of a raffle prize.
 *
 * Stores denormalized data for historical record even if related entities are deleted.
 */
class Winner extends Model
{
    use HasFactory;

    protected $fillable = [
        'raffle_id',
        'raffle_prize_id',
        'ticket_id',
        'user_id',
        'winner_name',
        'winner_email',
        'winner_phone',
        'ticket_number',
        'prize_name',
        'prize_value',
        'prize_position',
        'is_notified',
        'is_claimed',
        'is_delivered',
        'is_published',
        'notified_at',
        'claimed_at',
        'delivered_at',
        'published_at',
        'calculated_by',
        'delivered_by',
        'delivery_notes',
    ];

    protected function casts(): array
    {
        return [
            'prize_value' => 'integer',
            'prize_position' => 'integer',
            'is_notified' => 'boolean',
            'is_claimed' => 'boolean',
            'is_delivered' => 'boolean',
            'is_published' => 'boolean',
            'notified_at' => 'datetime',
            'claimed_at' => 'datetime',
            'delivered_at' => 'datetime',
            'published_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Winner $winner) {
            $winner->ulid ??= (string) Str::ulid();
            if ($winner->is_published && ! $winner->published_at) {
                $winner->published_at = now();
            }
        });
    }

    // Relationships

    public function raffle(): BelongsTo
    {
        return $this->belongsTo(Raffle::class);
    }

    public function prize(): BelongsTo
    {
        return $this->belongsTo(RafflePrize::class, 'raffle_prize_id');
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function calculatedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'calculated_by');
    }

    public function deliveredByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delivered_by');
    }

    public function testimonial(): HasOne
    {
        return $this->hasOne(WinnerTestimonial::class);
    }

    // Computed attributes

    public function getFormattedPrizeValueAttribute(): string
    {
        return '$'.number_format($this->prize_value, 0, ',', '.');
    }

    public function getDisplayNameAttribute(): string
    {
        // For privacy, show only first name + last initial by default
        $parts = explode(' ', $this->winner_name);
        if (count($parts) >= 2) {
            return $parts[0].' '.substr($parts[1], 0, 1).'.';
        }

        return $parts[0];
    }

    public function getMaskedEmailAttribute(): string
    {
        $parts = explode('@', $this->winner_email);
        if (count($parts) !== 2) {
            return '***@***.***';
        }

        $name = $parts[0];
        $domain = $parts[1];

        $maskedName = substr($name, 0, 2).str_repeat('*', max(strlen($name) - 2, 3));

        return $maskedName.'@'.$domain;
    }

    // Query scopes

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeNotified($query)
    {
        return $query->where('is_notified', true);
    }

    public function scopeDelivered($query)
    {
        return $query->where('is_delivered', true);
    }

    public function scopePending($query)
    {
        return $query->where('is_delivered', false);
    }

    public function scopeForRaffle($query, int $raffleId)
    {
        return $query->where('raffle_id', $raffleId);
    }

    // Business logic

    public function markAsNotified(): void
    {
        $this->update([
            'is_notified' => true,
            'notified_at' => now(),
        ]);
    }

    public function markAsClaimed(): void
    {
        $this->update([
            'is_claimed' => true,
            'claimed_at' => now(),
        ]);
    }

    public function markAsDelivered(?int $deliveredBy = null, ?string $notes = null): void
    {
        $this->update([
            'is_delivered' => true,
            'delivered_at' => now(),
            'delivered_by' => $deliveredBy,
            'delivery_notes' => $notes,
        ]);
    }

    public function publish(): void
    {
        $this->update([
            'is_published' => true,
            'published_at' => now(),
        ]);
    }

    public function unpublish(): void
    {
        $this->update([
            'is_published' => false,
        ]);
    }
}
