<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class RaffleResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'raffle_id',
        'lottery_name',
        'lottery_number',
        'lottery_date',
        'calculation_formula',
        'calculation_details',
        'is_confirmed',
        'is_published',
        'confirmed_at',
        'published_at',
        'registered_by',
        'confirmed_by',
        'published_by',
    ];

    protected function casts(): array
    {
        return [
            'lottery_date' => 'date',
            'calculation_details' => 'array',
            'is_confirmed' => 'boolean',
            'is_published' => 'boolean',
            'confirmed_at' => 'datetime',
            'published_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (RaffleResult $result) {
            $result->ulid ??= (string) Str::ulid();
        });
    }

    // Relationships

    public function raffle(): BelongsTo
    {
        return $this->belongsTo(Raffle::class);
    }

    public function registeredByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registered_by');
    }

    public function confirmedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function publishedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    // Query scopes

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeConfirmed($query)
    {
        return $query->where('is_confirmed', true);
    }

    // Business logic

    public function canConfirm(): bool
    {
        return !$this->is_confirmed && $this->lottery_number !== null;
    }

    public function canPublish(): bool
    {
        return $this->is_confirmed && !$this->is_published;
    }
}
