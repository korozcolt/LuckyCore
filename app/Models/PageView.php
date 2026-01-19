<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Page view tracking for analytics.
 */
class PageView extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'path',
        'session_hash',
        'user_id',
        'ip_hash',
        'user_agent',
        'referrer',
        'device_type',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Query scopes

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeForPath($query, string $path)
    {
        return $query->where('path', $path);
    }

    public function scopePublicPages($query)
    {
        return $query->where('path', 'not like', '/admin%')
            ->where('path', 'not like', '/livewire%');
    }

    // Helper to detect device type from user agent
    public static function detectDeviceType(?string $userAgent): string
    {
        if (empty($userAgent)) {
            return 'unknown';
        }

        $userAgent = strtolower($userAgent);

        if (preg_match('/(tablet|ipad|playbook|silk)/i', $userAgent)) {
            return 'tablet';
        }

        if (preg_match('/(mobile|android|iphone|ipod|phone)/i', $userAgent)) {
            return 'mobile';
        }

        return 'desktop';
    }
}
