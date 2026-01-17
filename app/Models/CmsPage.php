<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CmsPage extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'title',
        'content',
        'sections',
        'meta_title',
        'meta_description',
        'is_published',
        'published_at',
        'last_edited_by',
    ];

    protected function casts(): array
    {
        return [
            'sections' => 'array',
            'is_published' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    // Predefined page slugs
    public const SLUG_HOW_IT_WORKS = 'como-funciona';
    public const SLUG_TERMS = 'terminos-y-condiciones';
    public const SLUG_FAQ = 'preguntas-frecuentes';

    // Relationships

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_edited_by');
    }

    // Query scopes

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeBySlug($query, string $slug)
    {
        return $query->where('slug', $slug);
    }

    // Factory method for seeding default pages

    public static function getDefaultPages(): array
    {
        return [
            [
                'slug' => self::SLUG_HOW_IT_WORKS,
                'title' => 'Como Funciona',
                'content' => '',
                'is_published' => false,
            ],
            [
                'slug' => self::SLUG_TERMS,
                'title' => 'Terminos y Condiciones',
                'content' => '',
                'is_published' => false,
            ],
            [
                'slug' => self::SLUG_FAQ,
                'title' => 'Preguntas Frecuentes',
                'content' => '',
                'sections' => [],
                'is_published' => false,
            ],
        ];
    }
}
