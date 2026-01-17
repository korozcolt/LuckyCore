<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PaymentProvider;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class PaymentTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'provider',
        'provider_transaction_id',
        'provider_reference',
        'amount',
        'currency',
        'status',
        'idempotency_key',
        'webhook_received_at',
        'webhook_attempts',
        'provider_request',
        'provider_response',
        'webhook_payload',
        'error_code',
        'error_message',
        'initiated_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'provider' => PaymentProvider::class,
            'amount' => 'integer',
            'status' => PaymentStatus::class,
            'webhook_received_at' => 'datetime',
            'webhook_attempts' => 'integer',
            'provider_request' => 'array',
            'provider_response' => 'array',
            'webhook_payload' => 'array',
            'initiated_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (PaymentTransaction $transaction) {
            $transaction->ulid ??= (string) Str::ulid();
            $transaction->idempotency_key ??= (string) Str::uuid();
        });
    }

    // Relationships

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(OrderEvent::class)->orderBy('created_at');
    }

    // Computed attributes

    public function getFormattedAmountAttribute(): string
    {
        return '$' . number_format($this->amount / 100, 0, ',', '.');
    }

    // Query scopes

    public function scopePending($query)
    {
        return $query->where('status', PaymentStatus::Pending);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', PaymentStatus::Approved);
    }

    public function scopeForProvider($query, PaymentProvider $provider)
    {
        return $query->where('provider', $provider);
    }

    // Business logic

    public function isSuccessful(): bool
    {
        return $this->status->isSuccessful();
    }

    public function isFinal(): bool
    {
        return $this->status->isFinal();
    }

    public function canProcess(): bool
    {
        return !$this->isFinal();
    }
}
