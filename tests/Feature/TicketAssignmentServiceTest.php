<?php

use App\Enums\OrderStatus;
use App\Enums\TicketAssignmentMethod;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Raffle;
use App\Models\Ticket;
use App\Models\User;
use App\Services\TicketAssignmentService;

it('assigns sequential tickets for a paid order', function () {
    $raffle = Raffle::factory()->create([
        'ticket_assignment_method' => TicketAssignmentMethod::Sequential,
        'ticket_digits' => 3,
        'ticket_min_number' => 1,
        'ticket_max_number' => 50,
        'total_tickets' => 50,
    ]);

    $user = User::factory()->create();
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'status' => OrderStatus::Paid,
        'paid_at' => now(),
    ]);

    $item = OrderItem::factory()->create([
        'order_id' => $order->id,
        'raffle_id' => $raffle->id,
        'quantity' => 3,
        'tickets_assigned' => 0,
        'tickets_complete' => false,
    ]);

    app(TicketAssignmentService::class)->assignForOrder($order);

    $tickets = Ticket::query()
        ->where('order_id', $order->id)
        ->where('order_item_id', $item->id)
        ->orderByRaw('CAST(code AS INTEGER)')
        ->get();

    expect($tickets)->toHaveCount(3);
    expect($tickets->pluck('code')->all())->toBe(['1', '2', '3']);

    $item->refresh();
    expect($item->tickets_assigned)->toBe(3)
        ->and($item->tickets_complete)->toBeTrue();
});

it('is idempotent when called multiple times', function () {
    $raffle = Raffle::factory()->create([
        'ticket_assignment_method' => TicketAssignmentMethod::Sequential,
        'ticket_min_number' => 1,
        'ticket_max_number' => 50,
        'total_tickets' => 50,
    ]);

    $order = Order::factory()->create([
        'status' => OrderStatus::Paid,
        'paid_at' => now(),
    ]);

    $item = OrderItem::factory()->create([
        'order_id' => $order->id,
        'raffle_id' => $raffle->id,
        'quantity' => 2,
        'tickets_assigned' => 0,
        'tickets_complete' => false,
    ]);

    $service = app(TicketAssignmentService::class);
    $service->assignForOrder($order);
    $service->assignForOrder($order);

    expect(Ticket::query()->where('order_item_id', $item->id)->count())->toBe(2);

    $item->refresh();
    expect($item->tickets_assigned)->toBe(2)
        ->and($item->tickets_complete)->toBeTrue();
});

it('assigns tickets for a guest order without a user_id', function () {
    $raffle = Raffle::factory()->create([
        'ticket_assignment_method' => TicketAssignmentMethod::Random,
        'ticket_min_number' => 1,
        'ticket_max_number' => 100,
        'total_tickets' => 100,
    ]);

    $order = Order::factory()->create([
        'user_id' => null,
        'status' => OrderStatus::Paid,
        'paid_at' => now(),
    ]);

    $item = OrderItem::factory()->create([
        'order_id' => $order->id,
        'raffle_id' => $raffle->id,
        'quantity' => 2,
        'tickets_assigned' => 0,
        'tickets_complete' => false,
    ]);

    app(TicketAssignmentService::class)->assignForOrder($order);

    $tickets = Ticket::query()->where('order_item_id', $item->id)->get();
    expect($tickets)->toHaveCount(2);
    expect($tickets->pluck('user_id')->unique()->values()->all())->toBe([null]);
});
