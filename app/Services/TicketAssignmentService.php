<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\TicketAssignmentMethod;
use App\Models\Order;
use App\Models\OrderEvent;
use App\Models\OrderItem;
use App\Models\Ticket;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class TicketAssignmentService
{
    public function assignForOrder(Order $order): void
    {
        if ($order->status !== OrderStatus::Paid) {
            return;
        }

        DB::transaction(function () use ($order): void {
            $lockedOrder = Order::query()
                ->whereKey($order->id)
                ->lockForUpdate()
                ->firstOrFail();

            $items = OrderItem::query()
                ->where('order_id', $lockedOrder->id)
                ->with('raffle')
                ->lockForUpdate()
                ->get();

            $assignedTicketsCount = 0;

            foreach ($items as $item) {
                $assignedTicketsCount += $this->assignForOrderItem($lockedOrder, $item);
            }

            if ($assignedTicketsCount > 0) {
                OrderEvent::log(
                    order: $lockedOrder,
                    eventType: OrderEvent::TICKETS_ASSIGNED,
                    description: 'Tickets asignados automáticamente',
                    metadata: [
                        'tickets_assigned' => $assignedTicketsCount,
                    ],
                );
            }
        });
    }

    protected function assignForOrderItem(Order $order, OrderItem $item): int
    {
        $alreadyAssigned = Ticket::query()
            ->where('order_item_id', $item->id)
            ->count();

        $pending = max(0, $item->quantity - $alreadyAssigned);

        if ($pending === 0) {
            $item->update([
                'tickets_assigned' => $item->quantity,
                'tickets_complete' => true,
            ]);

            return 0;
        }

        $raffle = $item->raffle;
        $min = (int) ($raffle->ticket_min_number ?? 1);
        $max = (int) ($raffle->ticket_max_number ?? (int) str_repeat('9', (int) ($raffle->ticket_digits ?? 5)));

        $created = 0;
        $attempts = 0;
        $maxAttempts = max(100, $pending * 50);

        $nextSequentialCandidate = $this->getSequentialStartCandidate(raffleId: $raffle->id, min: $min);

        while ($created < $pending) {
            $attempts++;

            if ($attempts > $maxAttempts) {
                $this->logAssignmentFailure($order, $item, $pending, $created);

                throw new \RuntimeException('No fue posible asignar todos los tickets (demasiados intentos).');
            }

            $candidate = match ($raffle->ticket_assignment_method ?? TicketAssignmentMethod::default()) {
                TicketAssignmentMethod::Sequential => $nextSequentialCandidate++,
                default => random_int($min, $max),
            };

            if (($candidate < $min) || ($candidate > $max)) {
                continue;
            }

            try {
                Ticket::create([
                    'raffle_id' => $raffle->id,
                    'order_id' => $order->id,
                    'order_item_id' => $item->id,
                    'user_id' => $order->user_id,
                    'code' => (string) $candidate,
                    'is_winner' => false,
                    'prize_position' => null,
                    'won_at' => null,
                ]);

                $created++;
            } catch (QueryException $e) {
                if ($this->isDuplicateTicketCodeException($e)) {
                    continue;
                }

                $this->logAssignmentFailure($order, $item, $pending, $created, $e);

                throw $e;
            }
        }

        $item->update([
            'tickets_assigned' => $alreadyAssigned + $created,
            'tickets_complete' => ($alreadyAssigned + $created) >= $item->quantity,
        ]);

        return $created;
    }

    protected function getSequentialStartCandidate(int $raffleId, int $min): int
    {
        $maxCode = Ticket::query()
            ->where('raffle_id', $raffleId)
            ->max(DB::raw('CAST(code AS INTEGER)'));

        if (! $maxCode) {
            return $min;
        }

        return max($min, ((int) $maxCode) + 1);
    }

    protected function isDuplicateTicketCodeException(QueryException $e): bool
    {
        $message = $e->getMessage();

        return str_contains($message, 'tickets_raffle_id_code_unique')
            || str_contains($message, 'UNIQUE constraint failed: tickets.raffle_id, tickets.code')
            || str_contains($message, 'Duplicate entry');
    }

    protected function logAssignmentFailure(
        Order $order,
        OrderItem $item,
        int $pending,
        int $created,
        ?\Throwable $exception = null,
    ): void {
        OrderEvent::log(
            order: $order,
            eventType: OrderEvent::TICKETS_FAILED,
            description: 'Error asignando tickets automáticamente',
            metadata: [
                'order_item_id' => $item->id,
                'raffle_id' => $item->raffle_id,
                'quantity' => $item->quantity,
                'pending' => $pending,
                'created' => $created,
            ],
            isError: true,
            errorCode: 'TICKETS_ASSIGNMENT_FAILED',
            errorMessage: $exception?->getMessage(),
        );
    }
}
