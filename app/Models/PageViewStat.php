<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Aggregated page view statistics for efficient reporting.
 */
class PageViewStat extends Model
{
    protected $fillable = [
        'date',
        'path',
        'views',
        'unique_visitors',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'views' => 'integer',
            'unique_visitors' => 'integer',
        ];
    }

    // Query scopes

    public function scopeForDate($query, $date)
    {
        return $query->whereDate('date', $date);
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

    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }
}
