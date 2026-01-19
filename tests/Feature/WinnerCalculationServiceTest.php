<?php

use App\Enums\RaffleStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Raffle;
use App\Models\RafflePrize;
use App\Models\RaffleResult;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Winner;
use App\Notifications\WinnerNotification;
use App\Services\WinnerCalculationService;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    $this->service = new WinnerCalculationService;
});

/**
 * Helper to create a ticket with proper relationships.
 */
function createTicketWithOrder(Raffle $raffle, User $user, string $code): Ticket
{
    $order = Order::factory()->create([
        'user_id' => $user->id,
    ]);

    $orderItem = OrderItem::factory()->create([
        'order_id' => $order->id,
        'raffle_id' => $raffle->id,
    ]);

    return Ticket::factory()->create([
        'raffle_id' => $raffle->id,
        'order_id' => $order->id,
        'order_item_id' => $orderItem->id,
        'user_id' => $user->id,
        'code' => $code,
    ]);
}

describe('WinnerCalculationService', function () {
    describe('calculateWinners', function () {
        it('creates winners from raffle result', function () {
            Notification::fake();

            $raffle = Raffle::factory()->create([
                'status' => RaffleStatus::Closed,
            ]);

            $prize = RafflePrize::factory()->exactMatch()->create([
                'raffle_id' => $raffle->id,
                'prize_position' => 1,
            ]);

            $user = User::factory()->create();
            $ticket = createTicketWithOrder($raffle, $user, '12345');

            $result = RaffleResult::factory()->create([
                'raffle_id' => $raffle->id,
                'lottery_number' => '12345',
            ]);

            $winners = $this->service->calculateWinners($result);

            expect($winners)->toHaveCount(1)
                ->and(Winner::count())->toBe(1)
                ->and(Winner::first())
                ->winner_name->toBe($user->name)
                ->ticket_number->toBe('12345')
                ->prize_position->toBe(1)
                ->is_published->toBeTrue();
        });

        it('notifies winners when notify option is true', function () {
            Notification::fake();

            $raffle = Raffle::factory()->create(['status' => RaffleStatus::Closed]);

            $prize = RafflePrize::factory()->exactMatch()->create([
                'raffle_id' => $raffle->id,
            ]);

            $user = User::factory()->create();
            $ticket = createTicketWithOrder($raffle, $user, '12345');

            $result = RaffleResult::factory()->create([
                'raffle_id' => $raffle->id,
                'lottery_number' => '12345',
            ]);

            $this->service->calculateWinners($result, notify: true);

            $winner = Winner::first();
            expect($winner->is_notified)->toBeTrue()
                ->and($winner->notified_at)->not->toBeNull();

            Notification::assertSentTo($user, WinnerNotification::class);
        });

        it('does not notify winners when notify option is false', function () {
            Notification::fake();

            $raffle = Raffle::factory()->create(['status' => RaffleStatus::Closed]);

            $prize = RafflePrize::factory()->exactMatch()->create([
                'raffle_id' => $raffle->id,
            ]);

            $user = User::factory()->create();
            $ticket = createTicketWithOrder($raffle, $user, '12345');

            $result = RaffleResult::factory()->create([
                'raffle_id' => $raffle->id,
                'lottery_number' => '12345',
            ]);

            $this->service->calculateWinners($result, notify: false);

            $winner = Winner::first();
            expect($winner->is_notified)->toBeFalse();

            Notification::assertNotSentTo($user, WinnerNotification::class);
        });

        it('does not publish winners when publish option is false', function () {
            Notification::fake();

            $raffle = Raffle::factory()->create(['status' => RaffleStatus::Closed]);

            $prize = RafflePrize::factory()->exactMatch()->create([
                'raffle_id' => $raffle->id,
            ]);

            $user = User::factory()->create();
            $ticket = createTicketWithOrder($raffle, $user, '12345');

            $result = RaffleResult::factory()->create([
                'raffle_id' => $raffle->id,
                'lottery_number' => '12345',
            ]);

            $this->service->calculateWinners($result, publish: false);

            $winner = Winner::first();
            expect($winner->is_published)->toBeFalse();
        });

        it('handles multiple prizes correctly', function () {
            Notification::fake();

            $raffle = Raffle::factory()->create(['status' => RaffleStatus::Closed]);

            $firstPrize = RafflePrize::factory()->exactMatch()->create([
                'raffle_id' => $raffle->id,
                'prize_position' => 1,
                'name' => 'Primer Premio',
            ]);

            $secondPrize = RafflePrize::factory()->lastDigits(2)->create([
                'raffle_id' => $raffle->id,
                'prize_position' => 2,
                'name' => 'Segundo Premio',
            ]);

            $user1 = User::factory()->create();
            $ticket1 = createTicketWithOrder($raffle, $user1, '12345');

            $user2 = User::factory()->create();
            $ticket2 = createTicketWithOrder($raffle, $user2, '99945');

            $result = RaffleResult::factory()->create([
                'raffle_id' => $raffle->id,
                'lottery_number' => '12345',
            ]);

            $winners = $this->service->calculateWinners($result);

            // Ticket1 should win both prizes (exact match + last 2 digits)
            // Ticket2 should win second prize (last 2 digits match: 45)
            expect($winners->count())->toBeGreaterThanOrEqual(2);
        });

        it('records who calculated the winners', function () {
            Notification::fake();

            $admin = User::factory()->create();
            $raffle = Raffle::factory()->create(['status' => RaffleStatus::Closed]);

            $prize = RafflePrize::factory()->exactMatch()->create([
                'raffle_id' => $raffle->id,
            ]);

            $user = User::factory()->create();
            $ticket = createTicketWithOrder($raffle, $user, '12345');

            $result = RaffleResult::factory()->create([
                'raffle_id' => $raffle->id,
                'lottery_number' => '12345',
            ]);

            $this->service->calculateWinners($result, calculatedBy: $admin->id);

            $winner = Winner::first();
            expect($winner->calculated_by)->toBe($admin->id);
        });

        it('returns empty collection when no prizes match', function () {
            Notification::fake();

            $raffle = Raffle::factory()->create(['status' => RaffleStatus::Closed]);

            $prize = RafflePrize::factory()->exactMatch()->create([
                'raffle_id' => $raffle->id,
            ]);

            $user = User::factory()->create();
            $ticket = createTicketWithOrder($raffle, $user, '99999');

            $result = RaffleResult::factory()->create([
                'raffle_id' => $raffle->id,
                'lottery_number' => '12345',
            ]);

            $winners = $this->service->calculateWinners($result);

            expect($winners)->toBeEmpty()
                ->and(Winner::count())->toBe(0);
        });
    });
});

describe('Winner model', function () {
    it('computes display name correctly for privacy', function () {
        $winner = Winner::factory()->create([
            'winner_name' => 'Juan Carlos Pérez',
        ]);

        expect($winner->display_name)->toBe('Juan C.');
    });

    it('computes masked email correctly', function () {
        $winner = Winner::factory()->create([
            'winner_email' => 'example@test.com',
        ]);

        // "example" has 7 characters, so 2 shown + 5 masked = ex*****
        expect($winner->masked_email)->toBe('ex*****@test.com');
    });

    it('can mark as delivered with notes', function () {
        $admin = User::factory()->create();
        $winner = Winner::factory()->create([
            'is_delivered' => false,
        ]);

        $winner->markAsDelivered($admin->id, 'Entregado en oficina');

        expect($winner->fresh())
            ->is_delivered->toBeTrue()
            ->delivered_at->not->toBeNull()
            ->delivered_by->toBe($admin->id)
            ->delivery_notes->toBe('Entregado en oficina');
    });

    it('can publish and unpublish', function () {
        $winner = Winner::factory()->unpublished()->create();

        expect($winner->is_published)->toBeFalse();

        $winner->publish();
        expect($winner->fresh()->is_published)->toBeTrue();

        $winner->unpublish();
        expect($winner->fresh()->is_published)->toBeFalse();
    });
});
