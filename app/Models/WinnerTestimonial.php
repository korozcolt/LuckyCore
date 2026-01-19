<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * Testimonial from a winner about their prize/experience.
 *
 * Requires admin approval before being shown publicly.
 */
class WinnerTestimonial extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'winner_id',
        'comment',
        'photo_path',
        'rating',
        'status',
        'rejection_reason',
        'moderated_by',
        'moderated_at',
        'show_full_name',
        'is_featured',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'show_full_name' => 'boolean',
            'is_featured' => 'boolean',
            'moderated_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (WinnerTestimonial $testimonial) {
            $testimonial->ulid ??= (string) Str::ulid();
        });
    }

    // Relationships

    public function winner(): BelongsTo
    {
        return $this->belongsTo(Winner::class);
    }

    public function moderator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moderated_by');
    }

    // Computed attributes

    public function getDisplayNameAttribute(): string
    {
        if ($this->show_full_name) {
            return $this->winner->winner_name;
        }

        return $this->winner->display_name;
    }

    public function getPhotoUrlAttribute(): ?string
    {
        if (! $this->photo_path) {
            return null;
        }

        return asset('storage/'.$this->photo_path);
    }

    public function getIsApprovedAttribute(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function getIsPendingAttribute(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function getIsRejectedAttribute(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    // Query scopes

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    // Business logic

    public function approve(?int $moderatorId = null): void
    {
        $this->update([
            'status' => self::STATUS_APPROVED,
            'moderated_by' => $moderatorId,
            'moderated_at' => now(),
            'rejection_reason' => null,
        ]);
    }

    public function reject(?int $moderatorId = null, ?string $reason = null): void
    {
        $this->update([
            'status' => self::STATUS_REJECTED,
            'moderated_by' => $moderatorId,
            'moderated_at' => now(),
            'rejection_reason' => $reason,
        ]);
    }

    public function toggleFeatured(): void
    {
        $this->update([
            'is_featured' => ! $this->is_featured,
        ]);
    }
}
