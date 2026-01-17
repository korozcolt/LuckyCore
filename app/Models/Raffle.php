<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\RaffleStatus;
use App\Enums\TicketAssignmentMethod;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Raffle extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'short_description',
        'ticket_price',
        'total_tickets',
        'sold_tickets',
        'min_purchase_qty',
        'max_purchase_qty',
        'max_per_user',
        'allow_custom_quantity',
        'quantity_step',
        'ticket_assignment_method',
        'status',
        'starts_at',
        'ends_at',
        'draw_at',
        'lottery_source',
        'lottery_reference',
        'meta_title',
        'meta_description',
        'sort_order',
        'featured',
    ];

    protected function casts(): array
    {
        return [
            'ticket_price' => 'integer',
            'total_tickets' => 'integer',
            'sold_tickets' => 'integer',
            'min_purchase_qty' => 'integer',
            'max_purchase_qty' => 'integer',
            'max_per_user' => 'integer',
            'allow_custom_quantity' => 'boolean',
            'quantity_step' => 'integer',
            'ticket_assignment_method' => TicketAssignmentMethod::class,
            'status' => RaffleStatus::class,
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'draw_at' => 'datetime',
            'sort_order' => 'integer',
            'featured' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Raffle $raffle) {
            $raffle->ulid ??= (string) Str::ulid();
            $raffle->slug ??= Str::slug($raffle->title);
        });
    }

    // Relationships

    public function packages(): HasMany
    {
        return $this->hasMany(RafflePackage::class)->orderBy('sort_order');
    }

    public function images(): HasMany
    {
        return $this->hasMany(RaffleImage::class)->orderBy('sort_order');
    }

    public function primaryImage(): HasOne
    {
        return $this->hasOne(RaffleImage::class)->where('is_primary', true);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function result(): HasOne
    {
        return $this->hasOne(RaffleResult::class);
    }

    // Computed attributes

    public function getAvailableTicketsAttribute(): int
    {
        return $this->total_tickets - $this->sold_tickets;
    }

    public function getSoldPercentageAttribute(): float
    {
        if ($this->total_tickets === 0) {
            return 0;
        }

        return round(($this->sold_tickets / $this->total_tickets) * 100, 2);
    }

    public function getFormattedPriceAttribute(): string
    {
        return '$' . number_format($this->ticket_price / 100, 0, ',', '.');
    }

    // Query scopes

    public function scopeActive($query)
    {
        return $query->where('status', RaffleStatus::Active);
    }

    public function scopePublic($query)
    {
        return $query->whereIn('status', [
            RaffleStatus::Upcoming,
            RaffleStatus::Active,
            RaffleStatus::Closed,
            RaffleStatus::Completed,
        ]);
    }

    public function scopeFeatured($query)
    {
        return $query->where('featured', true);
    }

    // Business logic

    public function canPurchase(): bool
    {
        return $this->status->canPurchase() && $this->available_tickets > 0;
    }

    public function hasStock(int $quantity): bool
    {
        return $this->available_tickets >= $quantity;
    }
}
