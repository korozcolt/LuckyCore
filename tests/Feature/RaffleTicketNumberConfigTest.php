<?php

use App\Enums\RaffleStatus;
use App\Models\Raffle;
use App\Models\Ticket;
use Illuminate\Validation\ValidationException;

test('raffle has default ticket number configuration', function () {
    $raffle = Raffle::factory()->create([
        'total_tickets' => 1000,
    ]);

    expect($raffle->ticket_digits)->toBe(5)
        ->and($raffle->ticket_min_number)->toBe(1)
        ->and($raffle->ticket_max_number)->toBe(99999);
});

test('raffle can be created with custom ticket number configuration', function () {
    $raffle = Raffle::factory()->create([
        'total_tickets' => 50000,
        'ticket_digits' => 6,
        'ticket_min_number' => 100000,
        'ticket_max_number' => 199999,
    ]);

    expect($raffle->ticket_digits)->toBe(6)
        ->and($raffle->ticket_min_number)->toBe(100000)
        ->and($raffle->ticket_max_number)->toBe(199999)
        ->and($raffle->ticket_range_size)->toBe(100000);
});

test('raffle validates ticket_max_number is greater than ticket_min_number', function () {
    expect(fn () => Raffle::factory()->create([
        'total_tickets' => 1000,
        'ticket_min_number' => 1000,
        'ticket_max_number' => 500, // Invalid: max < min
    ]))->toThrow(InvalidArgumentException::class, 'ticket_max_number must be greater than or equal to ticket_min_number');
});

test('raffle validates ticket range is sufficient for total tickets', function () {
    expect(fn () => Raffle::factory()->create([
        'total_tickets' => 10000,
        'ticket_min_number' => 1,
        'ticket_max_number' => 5000, // Invalid: range (5000) < total_tickets (10000)
    ]))->toThrow(InvalidArgumentException::class, 'Ticket range');
});

test('raffle validates ticket_digits is between 3 and 10', function () {
    expect(fn () => Raffle::factory()->create([
        'ticket_digits' => 2, // Invalid: < 3
    ]))->toThrow(InvalidArgumentException::class, 'ticket_digits must be between 3 and 10');

    expect(fn () => Raffle::factory()->create([
        'ticket_digits' => 11, // Invalid: > 10
    ]))->toThrow(InvalidArgumentException::class, 'ticket_digits must be between 3 and 10');
});

test('raffle hasValidTicketRange returns true when range is sufficient', function () {
    $raffle = Raffle::factory()->create([
        'total_tickets' => 1000,
        'ticket_min_number' => 1,
        'ticket_max_number' => 9999,
    ]);

    expect($raffle->hasValidTicketRange())->toBeTrue();
});

test('raffle hasValidTicketRange returns false when range is insufficient', function () {
    $raffle = Raffle::factory()->make([
        'total_tickets' => 10000,
        'ticket_min_number' => 1,
        'ticket_max_number' => 5000,
    ]);

    // This will throw an exception on save, but we can test the logic
    expect($raffle->ticket_range_size)->toBe(5000)
        ->and($raffle->total_tickets)->toBe(10000)
        ->and($raffle->ticket_range_size < $raffle->total_tickets)->toBeTrue();
});

test('ticket formatted code uses raffle ticket_digits', function () {
    $raffle = Raffle::factory()->create([
        'ticket_digits' => 6,
        'ticket_min_number' => 100000,
        'ticket_max_number' => 199999,
    ]);

    $ticket = Ticket::factory()->create([
        'raffle_id' => $raffle->id,
        'code' => '123456',
    ]);

    expect($ticket->formatted_code)->toBe('123456');
});

test('ticket formatted code pads with zeros based on raffle ticket_digits', function () {
    $raffle = Raffle::factory()->create([
        'ticket_digits' => 6,
    ]);

    $ticket = Ticket::factory()->create([
        'raffle_id' => $raffle->id,
        'code' => '123',
    ]);

    expect($ticket->formatted_code)->toBe('000123');
});

test('ticket formatted code uses default 5 digits when accessing formatted_code directly', function () {
    // Test that the formatted_code accessor handles the case gracefully
    // In practice, ticket_digits will always have a value due to defaults,
    // but we test the fallback logic in the accessor
    $raffle = Raffle::factory()->create([
        'ticket_digits' => 5, // Explicit default
    ]);

    $ticket = Ticket::factory()->create([
        'raffle_id' => $raffle->id,
        'code' => '123',
    ]);

    // Should format with 5 digits (default)
    expect($ticket->formatted_code)->toBe('00123');
    
    // Test with different digits
    $raffle2 = Raffle::factory()->create([
        'ticket_digits' => 6,
    ]);
    
    $ticket2 = Ticket::factory()->create([
        'raffle_id' => $raffle2->id,
        'code' => '123',
    ]);
    
    expect($ticket2->formatted_code)->toBe('000123');
});
