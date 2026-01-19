<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Raffle;
use App\Models\RafflePrize;
use App\Models\RaffleResult;
use App\Models\Ticket;
use App\Models\Winner;
use App\Notifications\WinnerNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Service to calculate raffle winners based on lottery results.
 *
 * Flow:
 * 1. Admin enters lottery number in RaffleResult
 * 2. This service finds matching tickets for each prize
 * 3. Creates Winner records with denormalized data
 * 4. Optionally notifies winners via email
 * 5. Optionally publishes results
 */
class WinnerCalculationService
{
    /**
     * Calculate winners for a raffle based on the lottery result.
     *
     * @param  RaffleResult  $result  The raffle result with lottery number
     * @param  int|null  $calculatedBy  User ID who triggered calculation
     * @param  bool  $notify  Whether to send email notifications
     * @param  bool  $publish  Whether to publish winners immediately
     * @return Collection<Winner> The created winner records
     */
    public function calculateWinners(
        RaffleResult $result,
        ?int $calculatedBy = null,
        bool $notify = true,
        bool $publish = true,
    ): Collection {
        $raffle = $result->raffle;
        $lotteryNumber = $result->lottery_number;

        if (empty($lotteryNumber)) {
            throw new \InvalidArgumentException('Lottery number is required to calculate winners');
        }

        // Get all active prizes for the raffle
        $prizes = $raffle->activePrizes()->get();

        if ($prizes->isEmpty()) {
            Log::warning("No active prizes found for raffle {$raffle->id}");

            return collect();
        }

        // Get all sold tickets for this raffle
        $tickets = $raffle->tickets()
            ->whereNotNull('order_id')
            ->with(['order.user', 'order'])
            ->get();

        if ($tickets->isEmpty()) {
            Log::warning("No sold tickets found for raffle {$raffle->id}");

            return collect();
        }

        $winners = collect();

        DB::transaction(function () use ($prizes, $tickets, $lotteryNumber, $raffle, $calculatedBy, $publish, &$winners, $result) {
            foreach ($prizes as $prize) {
                $winningTickets = $this->findWinningTickets($prize, $tickets, $lotteryNumber);

                foreach ($winningTickets as $ticket) {
                    // Skip if this ticket already won (for this prize)
                    if (Winner::where('ticket_id', $ticket->id)->where('raffle_prize_id', $prize->id)->exists()) {
                        continue;
                    }

                    $winner = $this->createWinner($ticket, $prize, $raffle, $calculatedBy, $publish);
                    $winners->push($winner);
                }
            }

            // Mark result as confirmed
            if (! $result->is_confirmed) {
                $result->update([
                    'is_confirmed' => true,
                    'confirmed_at' => now(),
                    'confirmed_by' => $calculatedBy,
                ]);
            }
        });

        // Notify winners (outside transaction for better error handling)
        if ($notify && $winners->isNotEmpty()) {
            $this->notifyWinners($winners);
        }

        // Publish result if all winners are published
        if ($publish && ! $result->is_published) {
            $result->update([
                'is_published' => true,
                'published_at' => now(),
                'published_by' => $calculatedBy,
            ]);
        }

        Log::info("Calculated {$winners->count()} winners for raffle {$raffle->id}");

        return $winners;
    }

    /**
     * Find tickets that match a prize's winning conditions.
     */
    protected function findWinningTickets(RafflePrize $prize, Collection $tickets, string $lotteryNumber): Collection
    {
        return $tickets->filter(function (Ticket $ticket) use ($prize, $lotteryNumber) {
            return $prize->matchesTicket($ticket->ticket_number, $lotteryNumber);
        });
    }

    /**
     * Create a winner record from a winning ticket.
     */
    protected function createWinner(
        Ticket $ticket,
        RafflePrize $prize,
        Raffle $raffle,
        ?int $calculatedBy,
        bool $publish,
    ): Winner {
        $order = $ticket->order;
        $user = $order?->user;

        // Get winner info from user or order
        $winnerName = $user?->name ?? $order?->customer_name ?? 'Unknown';
        $winnerEmail = $user?->email ?? $order?->customer_email ?? '';
        $winnerPhone = $order?->customer_phone;

        return Winner::create([
            'raffle_id' => $raffle->id,
            'raffle_prize_id' => $prize->id,
            'ticket_id' => $ticket->id,
            'user_id' => $user?->id,
            'winner_name' => $winnerName,
            'winner_email' => $winnerEmail,
            'winner_phone' => $winnerPhone,
            'ticket_number' => $ticket->ticket_number,
            'prize_name' => $prize->name,
            'prize_value' => $prize->prize_value,
            'prize_position' => $prize->prize_position,
            'is_published' => $publish,
            'published_at' => $publish ? now() : null,
            'calculated_by' => $calculatedBy,
        ]);
    }

    /**
     * Send notifications to all winners.
     */
    protected function notifyWinners(Collection $winners): void
    {
        foreach ($winners as $winner) {
            try {
                if ($winner->user) {
                    $winner->user->notify(new WinnerNotification($winner));
                    $winner->markAsNotified();
                } elseif ($winner->winner_email) {
                    // For guests, we could use a mailable or anonymous notification
                    // For now, just mark as notification pending
                    Log::info("Winner {$winner->id} is a guest - email notification pending implementation");
                }
            } catch (\Throwable $e) {
                Log::error("Failed to notify winner {$winner->id}: {$e->getMessage()}");
            }
        }
    }

    /**
     * Recalculate winners for a raffle (useful if prizes change).
     *
     * This will NOT delete existing winners, only add new ones.
     */
    public function recalculateWinners(RaffleResult $result, ?int $calculatedBy = null): Collection
    {
        return $this->calculateWinners($result, $calculatedBy, notify: false, publish: false);
    }

    /**
     * Get calculation details/summary for a raffle result.
     */
    public function getCalculationSummary(RaffleResult $result): array
    {
        $raffle = $result->raffle;
        $winners = $raffle->winners()->with(['prize', 'ticket', 'user'])->get();

        return [
            'raffle_id' => $raffle->id,
            'raffle_title' => $raffle->title,
            'lottery_number' => $result->lottery_number,
            'lottery_date' => $result->lottery_date?->format('Y-m-d'),
            'total_prizes' => $raffle->activePrizes()->count(),
            'total_winners' => $winners->count(),
            'winners_notified' => $winners->where('is_notified', true)->count(),
            'winners_claimed' => $winners->where('is_claimed', true)->count(),
            'winners_delivered' => $winners->where('is_delivered', true)->count(),
            'total_prize_value' => $winners->sum('prize_value'),
            'winners' => $winners->map(fn (Winner $w) => [
                'id' => $w->id,
                'prize_name' => $w->prize_name,
                'prize_position' => $w->prize_position,
                'ticket_number' => $w->ticket_number,
                'winner_name' => $w->winner_name,
                'is_notified' => $w->is_notified,
                'is_delivered' => $w->is_delivered,
            ])->toArray(),
        ];
    }
}
