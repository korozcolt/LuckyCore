<?php

use App\Enums\WinningConditionType;
use App\Models\Raffle;
use App\Models\RafflePrize;
use App\Models\Ticket;
use App\Services\PrizeCalculationService;

beforeEach(function () {
    $this->service = new PrizeCalculationService();
});

test('prize calculation service identifies exact match winner', function () {
    $raffle = Raffle::factory()->create();

    $prize = RafflePrize::factory()->create([
        'raffle_id' => $raffle->id,
        'name' => 'Primer Premio',
        'prize_position' => 1,
        'winning_conditions' => [
            'type' => WinningConditionType::ExactMatch->value,
        ],
    ]);

    // Create tickets
    $winningTicket = Ticket::factory()->create([
        'raffle_id' => $raffle->id,
        'code' => '12345',
    ]);

    $losingTicket = Ticket::factory()->create([
        'raffle_id' => $raffle->id,
        'code' => '54321',
    ]);

    $result = $this->service->calculateWinners($raffle, '12345');

    expect($result['winners'])->toHaveCount(1)
        ->and($result['winners']->first()->id)->toBe($winningTicket->id)
        ->and($result['details'][$prize->id]['winners_count'])->toBe(1);
});

test('prize calculation service identifies reverse match winner', function () {
    $raffle = Raffle::factory()->create();

    $prize = RafflePrize::factory()->create([
        'raffle_id' => $raffle->id,
        'name' => 'Premio Reverso',
        'winning_conditions' => [
            'type' => WinningConditionType::Reverse->value,
        ],
    ]);

    $winningTicket = Ticket::factory()->create([
        'raffle_id' => $raffle->id,
        'code' => '54321', // Reverse of 12345
    ]);

    $losingTicket = Ticket::factory()->create([
        'raffle_id' => $raffle->id,
        'code' => '11111',
    ]);

    $result = $this->service->calculateWinners($raffle, '12345');

    expect($result['winners'])->toHaveCount(1)
        ->and($result['winners']->first()->id)->toBe($winningTicket->id);
});

test('prize calculation service identifies last digits winner', function () {
    $raffle = Raffle::factory()->create();

    $prize = RafflePrize::factory()->create([
        'raffle_id' => $raffle->id,
        'name' => 'Premio Últimos 2 Dígitos',
        'winning_conditions' => [
            'type' => WinningConditionType::LastDigits->value,
            'digit_count' => 2,
        ],
    ]);

    $winningTicket = Ticket::factory()->create([
        'raffle_id' => $raffle->id,
        'code' => '99945', // Last 2 digits = 45
    ]);

    $losingTicket = Ticket::factory()->create([
        'raffle_id' => $raffle->id,
        'code' => '99999',
    ]);

    $result = $this->service->calculateWinners($raffle, '12345'); // Last 2 = 45

    expect($result['winners'])->toHaveCount(1)
        ->and($result['winners']->first()->id)->toBe($winningTicket->id);
});

test('prize calculation service identifies permutation winner', function () {
    $raffle = Raffle::factory()->create();

    $prize = RafflePrize::factory()->create([
        'raffle_id' => $raffle->id,
        'name' => 'Premio Permutación',
        'winning_conditions' => [
            'type' => WinningConditionType::Permutation->value,
        ],
    ]);

    // All these have the same digits as 12345
    $winningTicket1 = Ticket::factory()->create([
        'raffle_id' => $raffle->id,
        'code' => '54321', // Same digits, different order
    ]);

    $winningTicket2 = Ticket::factory()->create([
        'raffle_id' => $raffle->id,
        'code' => '31245', // Same digits, different order
    ]);

    $losingTicket = Ticket::factory()->create([
        'raffle_id' => $raffle->id,
        'code' => '11111',
    ]);

    $result = $this->service->calculateWinners($raffle, '12345');

    expect($result['winners'])->toHaveCount(2)
        ->and($result['winners']->pluck('id')->toArray())
        ->toContain($winningTicket1->id)
        ->toContain($winningTicket2->id);
});

test('prize calculation service handles multiple prizes', function () {
    $raffle = Raffle::factory()->create();

    $firstPrize = RafflePrize::factory()->create([
        'raffle_id' => $raffle->id,
        'name' => 'Primer Premio',
        'prize_position' => 1,
        'winning_conditions' => [
            'type' => WinningConditionType::ExactMatch->value,
        ],
    ]);

    $secondPrize = RafflePrize::factory()->create([
        'raffle_id' => $raffle->id,
        'name' => 'Segundo Premio',
        'prize_position' => 2,
        'winning_conditions' => [
            'type' => WinningConditionType::LastDigits->value,
            'digit_count' => 2,
        ],
    ]);

    $exactMatchTicket = Ticket::factory()->create([
        'raffle_id' => $raffle->id,
        'code' => '12345',
    ]);

    $lastDigitsTicket = Ticket::factory()->create([
        'raffle_id' => $raffle->id,
        'code' => '99945', // Last 2 = 45
    ]);

    $result = $this->service->calculateWinners($raffle, '12345');

    // exactMatchTicket wins BOTH prizes (exact match AND last 2 digits)
    // lastDigitsTicket wins only last digits prize
    expect($result['details'][$firstPrize->id]['winners_count'])->toBe(1)
        ->and($result['details'][$secondPrize->id]['winners_count'])->toBe(2);
});

test('prize calculation service returns empty when no prizes configured', function () {
    $raffle = Raffle::factory()->create();

    Ticket::factory()->create([
        'raffle_id' => $raffle->id,
        'code' => '12345',
    ]);

    $result = $this->service->calculateWinners($raffle, '12345');

    expect($result['winners'])->toBeEmpty()
        ->and($result['errors'])->toContain('El sorteo no tiene premios configurados.');
});

test('prize calculation service returns empty when no tickets sold', function () {
    $raffle = Raffle::factory()->create();

    RafflePrize::factory()->create([
        'raffle_id' => $raffle->id,
        'winning_conditions' => [
            'type' => WinningConditionType::ExactMatch->value,
        ],
    ]);

    $result = $this->service->calculateWinners($raffle, '12345');

    expect($result['winners'])->toBeEmpty()
        ->and($result['errors'])->toContain('El sorteo no tiene tickets vendidos.');
});

test('prize calculation service applies winners to database', function () {
    $raffle = Raffle::factory()->create();

    $prize = RafflePrize::factory()->create([
        'raffle_id' => $raffle->id,
        'name' => 'Primer Premio',
        'prize_position' => 1,
        'winning_conditions' => [
            'type' => WinningConditionType::ExactMatch->value,
        ],
    ]);

    $winningTicket = Ticket::factory()->create([
        'raffle_id' => $raffle->id,
        'code' => '12345',
    ]);

    $losingTicket = Ticket::factory()->create([
        'raffle_id' => $raffle->id,
        'code' => '99999',
    ]);

    $calculationResult = $this->service->calculateWinners($raffle, '12345');
    $result = $this->service->applyWinners($raffle, '12345', $calculationResult);

    // Refresh tickets from database
    $winningTicket->refresh();
    $losingTicket->refresh();

    expect($winningTicket->is_winner)->toBeTrue()
        ->and($winningTicket->prize_id)->toBe($prize->id)
        ->and($winningTicket->prize_position)->toBe(1)
        ->and($winningTicket->won_at)->not->toBeNull()
        ->and($losingTicket->is_winner)->toBeFalse()
        ->and($losingTicket->prize_id)->toBeNull();

    // Verify raffle result was created
    expect($result->lottery_number)->toBe('12345')
        ->and($result->calculation_details)->not->toBeEmpty();
});

test('prize calculation service preview does not modify database', function () {
    $raffle = Raffle::factory()->create();

    $prize = RafflePrize::factory()->create([
        'raffle_id' => $raffle->id,
        'winning_conditions' => [
            'type' => WinningConditionType::ExactMatch->value,
        ],
    ]);

    $ticket = Ticket::factory()->create([
        'raffle_id' => $raffle->id,
        'code' => '12345',
        'is_winner' => false,
    ]);

    $preview = $this->service->previewWinners($raffle, '12345');

    // Ticket should NOT be marked as winner in database
    $ticket->refresh();
    expect($ticket->is_winner)->toBeFalse()
        ->and($preview['total_winners'])->toBe(1);
});
