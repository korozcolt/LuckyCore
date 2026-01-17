<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\WinningConditionType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * Represents a prize that can be won in a raffle.
 *
 * @see REGLAS_NEGOCIO.md §6 - Premios múltiples
 * @see ANALISIS_REGLAS_NEGOCIO.md §3 - Premios Múltiples con Combinaciones
 *
 * @property int $id
 * @property string $ulid
 * @property int $raffle_id
 * @property string $name
 * @property string|null $description
 * @property int $prize_value
 * @property int $prize_position
 * @property array|null $winning_conditions
 * @property bool $is_active
 * @property int $sort_order
 */
class RafflePrize extends Model
{
    use HasFactory;

    protected $fillable = [
        'raffle_id',
        'name',
        'description',
        'prize_value',
        'prize_position',
        'winning_conditions',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'prize_value' => 'integer',
            'prize_position' => 'integer',
            'winning_conditions' => 'array',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (RafflePrize $prize) {
            $prize->ulid ??= (string) Str::ulid();
        });
    }

    // Relationships

    public function raffle(): BelongsTo
    {
        return $this->belongsTo(Raffle::class);
    }

    public function winningTickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'prize_id');
    }

    // Computed attributes

    public function getFormattedValueAttribute(): string
    {
        return '$' . number_format($this->prize_value / 100, 0, ',', '.');
    }

    public function getConditionTypeAttribute(): ?WinningConditionType
    {
        $conditions = $this->winning_conditions;
        if (!$conditions || !isset($conditions['type'])) {
            return null;
        }

        return WinningConditionType::tryFrom($conditions['type']);
    }

    // Query scopes

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('prize_position');
    }

    // Business logic

    /**
     * Check if a ticket code matches this prize's winning conditions.
     *
     * @param string $ticketCode The ticket code to check
     * @param string $lotteryNumber The winning lottery number
     * @return bool Whether the ticket wins this prize
     */
    public function matchesTicket(string $ticketCode, string $lotteryNumber): bool
    {
        $conditions = $this->winning_conditions;
        if (!$conditions || !isset($conditions['type'])) {
            return false;
        }

        $type = WinningConditionType::tryFrom($conditions['type']);
        if (!$type) {
            return false;
        }

        // Normalize codes - remove leading zeros for comparison
        $ticketCode = ltrim($ticketCode, '0') ?: '0';
        $lotteryNumber = ltrim($lotteryNumber, '0') ?: '0';

        return match ($type) {
            WinningConditionType::ExactMatch => $this->matchExact($ticketCode, $lotteryNumber),
            WinningConditionType::Reverse => $this->matchReverse($ticketCode, $lotteryNumber),
            WinningConditionType::Permutation => $this->matchPermutation($ticketCode, $lotteryNumber),
            WinningConditionType::LastDigits => $this->matchLastDigits($ticketCode, $lotteryNumber, $conditions),
            WinningConditionType::FirstDigits => $this->matchFirstDigits($ticketCode, $lotteryNumber, $conditions),
            WinningConditionType::Combination => $this->matchCombination($ticketCode, $lotteryNumber, $conditions),
        };
    }

    /**
     * Check exact match: ticket code equals lottery number.
     */
    private function matchExact(string $ticketCode, string $lotteryNumber): bool
    {
        return $ticketCode === $lotteryNumber;
    }

    /**
     * Check reverse match: ticket code equals reversed lottery number.
     */
    private function matchReverse(string $ticketCode, string $lotteryNumber): bool
    {
        $reversed = strrev($lotteryNumber);
        $normalizedReversed = ltrim($reversed, '0') ?: '0';
        return $ticketCode === $normalizedReversed;
    }

    /**
     * Check permutation match: ticket code contains same digits as lottery number.
     */
    private function matchPermutation(string $ticketCode, string $lotteryNumber): bool
    {
        // Sort digits and compare
        $ticketDigits = str_split($ticketCode);
        $lotteryDigits = str_split($lotteryNumber);

        sort($ticketDigits);
        sort($lotteryDigits);

        return implode('', $ticketDigits) === implode('', $lotteryDigits);
    }

    /**
     * Check last digits match: last N digits of ticket match last N digits of lottery number.
     */
    private function matchLastDigits(string $ticketCode, string $lotteryNumber, array $conditions): bool
    {
        $digitCount = $conditions['digit_count'] ?? 2;

        $ticketLast = substr($ticketCode, -$digitCount);
        $lotteryLast = substr($lotteryNumber, -$digitCount);

        return $ticketLast === $lotteryLast;
    }

    /**
     * Check first digits match: first N digits of ticket match first N digits of lottery number.
     */
    private function matchFirstDigits(string $ticketCode, string $lotteryNumber, array $conditions): bool
    {
        $digitCount = $conditions['digit_count'] ?? 2;

        // For first digits, we need to pad to ensure correct comparison
        $raffle = $this->raffle;
        $digits = $raffle ? $raffle->ticket_digits : 5;

        $ticketPadded = str_pad($ticketCode, $digits, '0', STR_PAD_LEFT);
        $lotteryPadded = str_pad($lotteryNumber, $digits, '0', STR_PAD_LEFT);

        $ticketFirst = substr($ticketPadded, 0, $digitCount);
        $lotteryFirst = substr($lotteryPadded, 0, $digitCount);

        return $ticketFirst === $lotteryFirst;
    }

    /**
     * Check custom combination match.
     */
    private function matchCombination(string $ticketCode, string $lotteryNumber, array $conditions): bool
    {
        $combinations = $conditions['combinations'] ?? [];

        foreach ($combinations as $combo) {
            $digits = $combo['digits'] ?? [];
            $order = $combo['order'] ?? 'any';

            // This is a simplified implementation - extend as needed
            $ticketDigits = str_split($ticketCode);
            $targetDigits = array_map('strval', $digits);

            if ($order === 'exact') {
                // Exact order match
                if (array_slice($ticketDigits, 0, count($targetDigits)) === $targetDigits) {
                    return true;
                }
            } elseif ($order === 'reverse') {
                // Reverse order match
                if (array_slice($ticketDigits, 0, count($targetDigits)) === array_reverse($targetDigits)) {
                    return true;
                }
            } else {
                // Any order (contained)
                sort($ticketDigits);
                sort($targetDigits);
                if (count(array_intersect($ticketDigits, $targetDigits)) === count($targetDigits)) {
                    return true;
                }
            }
        }

        return false;
    }
}
