<?php

use App\Enums\UserRole;
use App\Filament\Resources\Tickets\Pages\ListTickets;
use App\Models\Ticket;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

it('allows admin users to access tickets', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin->value);

    $ticket = Ticket::factory()->create([
        'code' => '123',
    ]);

    actingAs($admin);

    $this->get('/admin/tickets')
        ->assertSuccessful()
        ->assertSee('Tickets');

    Livewire::test(ListTickets::class)
        ->assertOk()
        ->assertCanSeeTableRecords([$ticket])
        ->assertSee('00123', false);
});

it('denies ticket access to customers', function () {
    $customer = User::factory()->create();
    $customer->assignRole(UserRole::Customer->value);

    actingAs($customer);

    $this->get('/admin/tickets')
        ->assertForbidden();
});
