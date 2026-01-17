<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order_number',
        'user_id',
        'cart_id',
        'subtotal',
        'total',
        'status',
        'support_code',
        'correlation_id',
        'customer_email',
        'customer_name',
        'customer_phone',
        'ip_address',
        'user_agent',
        'terms_accepted',
        'terms_accepted_at',
        'paid_at',
        'expired_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'integer',
            'total' => 'integer',
            'status' => OrderStatus::class,
            'terms_accepted' => 'boolean',
            'terms_accepted_at' => 'datetime',
            'paid_at' => 'datetime',
            'expired_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            $order->ulid ??= (string) Str::ulid();
            $order->order_number ??= static::generateOrderNumber();
            $order->support_code ??= static::generateSupportCode();
            $order->correlation_id ??= (string) Str::uuid();
        });
    }

    // Relationships

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(PaymentTransaction::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(OrderEvent::class)->orderBy('created_at');
    }

    // Computed attributes

    public function getFormattedTotalAttribute(): string
    {
        return '$'.number_format($this->total / 100, 0, ',', '.');
    }

    public function getLatestTransactionAttribute(): ?PaymentTransaction
    {
        return $this->transactions()->latest()->first();
    }

    public function getTotalTicketsAttribute(): int
    {
        return $this->items->sum('quantity');
    }

    public function getAssignedTicketsAttribute(): int
    {
        return $this->items->sum('tickets_assigned');
    }

    // Query scopes

    public function scopePending($query)
    {
        return $query->where('status', OrderStatus::Pending);
    }

    public function scopePaid($query)
    {
        return $query->where('status', OrderStatus::Paid);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    // Business logic

    public function isPaid(): bool
    {
        return $this->status === OrderStatus::Paid;
    }

    public function canRetry(): bool
    {
        return $this->status->canRetry();
    }

    public function canPay(): bool
    {
        return $this->status->canPay();
    }

    public function allTicketsAssigned(): bool
    {
        return $this->items->every(fn (OrderItem $item) => $item->tickets_complete);
    }

    // Generators

    public static function generateOrderNumber(): string
    {
        $prefix = 'ORD';
        $date = now()->format('ymd');
        $random = strtoupper(Str::random(6));

        return "{$prefix}-{$date}-{$random}";
    }

    public static function generateSupportCode(): string
    {
        // Short 8-character code for customer support
        return strtoupper(Str::random(8));
    }
}
