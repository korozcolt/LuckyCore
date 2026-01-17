<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Raffle;
use App\Models\RafflePrize;
use App\Models\RaffleResult;
use App\Models\Ticket;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Service for calculating raffle prize winners.
 *
 * @see REGLAS_NEGOCIO.md §6 - Resultados y ganador
 * @see ANALISIS_REGLAS_NEGOCIO.md §3 - Premios Múltiples con Combinaciones
 */
class PrizeCalculationService
{
    /**
     * Calculate winners for a raffle based on the lottery number.
     *
     * @param Raffle $raffle The raffle to calculate winners for
     * @param string $lotteryNumber The winning lottery number
     * @return array{
     *     winners: Collection,
     *     details: array,
     *     errors: array
     * }
     */
    public function calculateWinners(Raffle $raffle, string $lotteryNumber): array
    {
        $winners = collect();
        $details = [];
        $errors = [];

        // Get all active prizes for this raffle
        $prizes = $raffle->activePrizes()->get();

        if ($prizes->isEmpty()) {
            $errors[] = 'El sorteo no tiene premios configurados.';
            return compact('winners', 'details', 'errors');
        }

        // Get all tickets for this raffle
        $tickets = $raffle->tickets()->get();

        if ($tickets->isEmpty()) {
            $errors[] = 'El sorteo no tiene tickets vendidos.';
            return compact('winners', 'details', 'errors');
        }

        Log::info("Calculating winners for raffle {$raffle->id}", [
            'lottery_number' => $lotteryNumber,
            'total_tickets' => $tickets->count(),
            'total_prizes' => $prizes->count(),
        ]);

        // Check each prize
        foreach ($prizes as $prize) {
            $prizeWinners = $this->findWinnersForPrize($prize, $tickets, $lotteryNumber);

            $details[$prize->id] = [
                'prize_id' => $prize->id,
                'prize_name' => $prize->name,
                'prize_position' => $prize->prize_position,
                'prize_value' => $prize->prize_value,
                'condition_type' => $prize->winning_conditions['type'] ?? null,
                'winners_count' => $prizeWinners->count(),
                'winning_ticket_codes' => $prizeWinners->pluck('code')->toArray(),
            ];

            $winners = $winners->merge($prizeWinners);
        }

        Log::info("Winners calculated for raffle {$raffle->id}", [
            'total_winners' => $winners->unique('id')->count(),
            'prizes_with_winners' => collect($details)->where('winners_count', '>', 0)->count(),
        ]);

        return [
            'winners' => $winners->unique('id'),
            'details' => $details,
            'errors' => $errors,
        ];
    }

    /**
     * Find winning tickets for a specific prize.
     */
    private function findWinnersForPrize(RafflePrize $prize, Collection $tickets, string $lotteryNumber): Collection
    {
        return $tickets->filter(function (Ticket $ticket) use ($prize, $lotteryNumber) {
            return $prize->matchesTicket($ticket->code, $lotteryNumber);
        });
    }

    /**
     * Apply winners to the database and create/update the raffle result.
     *
     * @param Raffle $raffle
     * @param string $lotteryNumber
     * @param array $calculationResult Result from calculateWinners()
     * @return RaffleResult
     */
    public function applyWinners(Raffle $raffle, string $lotteryNumber, array $calculationResult): RaffleResult
    {
        return DB::transaction(function () use ($raffle, $lotteryNumber, $calculationResult) {
            // Reset previous winners for this raffle
            $raffle->tickets()->update([
                'is_winner' => false,
                'prize_id' => null,
                'prize_position' => null,
                'won_at' => null,
            ]);

            // Mark new winners
            foreach ($calculationResult['details'] as $prizeId => $prizeDetails) {
                if ($prizeDetails['winners_count'] > 0) {
                    Ticket::whereIn('code', $prizeDetails['winning_ticket_codes'])
                        ->where('raffle_id', $raffle->id)
                        ->update([
                            'is_winner' => true,
                            'prize_id' => $prizeId,
                            'prize_position' => $prizeDetails['prize_position'],
                            'won_at' => now(),
                        ]);
                }
            }

            // Create or update raffle result
            $result = RaffleResult::updateOrCreate(
                ['raffle_id' => $raffle->id],
                [
                    'lottery_number' => $lotteryNumber,
                    'lottery_date' => now()->toDateString(),
                    'calculation_formula' => $this->buildFormulaDescription($calculationResult['details']),
                    'calculation_details' => $calculationResult['details'],
                ]
            );

            Log::info("Winners applied for raffle {$raffle->id}", [
                'result_id' => $result->id,
                'total_winners' => $calculationResult['winners']->count(),
            ]);

            return $result;
        });
    }

    /**
     * Build a human-readable formula description.
     */
    private function buildFormulaDescription(array $details): string
    {
        $parts = [];

        foreach ($details as $prizeDetails) {
            $conditionType = $prizeDetails['condition_type'];
            $prizeName = $prizeDetails['prize_name'];

            $conditionDesc = match ($conditionType) {
                'exact_match' => 'número exacto',
                'reverse' => 'número al revés',
                'permutation' => 'cualquier permutación',
                'last_digits' => 'últimos dígitos',
                'first_digits' => 'primeros dígitos',
                'combination' => 'combinación',
                default => 'condición desconocida',
            };

            $parts[] = "{$prizeName}: {$conditionDesc}";
        }

        return implode('; ', $parts);
    }

    /**
     * Preview winners without applying changes to the database.
     *
     * @param Raffle $raffle
     * @param string $lotteryNumber
     * @return array Preview of calculation results
     */
    public function previewWinners(Raffle $raffle, string $lotteryNumber): array
    {
        $result = $this->calculateWinners($raffle, $lotteryNumber);

        return [
            'lottery_number' => $lotteryNumber,
            'total_winners' => $result['winners']->count(),
            'prizes' => collect($result['details'])->map(function ($detail) {
                return [
                    'name' => $detail['prize_name'],
                    'position' => $detail['prize_position'],
                    'value' => $detail['prize_value'],
                    'condition' => $detail['condition_type'],
                    'winners_count' => $detail['winners_count'],
                    'winning_codes' => $detail['winning_ticket_codes'],
                ];
            })->values()->toArray(),
            'errors' => $result['errors'],
        ];
    }
}
