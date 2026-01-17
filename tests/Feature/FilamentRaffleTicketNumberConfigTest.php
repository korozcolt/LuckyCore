<?php

use App\Enums\UserRole;
use App\Models\User;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    // Ensure roles exist
    if (!Role::where('name', UserRole::Admin->value)->exists()) {
        Role::create(['name' => UserRole::Admin->value, 'guard_name' => 'web']);
    }
    if (!Role::where('name', UserRole::SuperAdmin->value)->exists()) {
        Role::create(['name' => UserRole::SuperAdmin->value, 'guard_name' => 'web']);
    }
});

test('admin can access raffle create form with ticket number configuration fields', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin->value);

    actingAs($admin);

    $response = $this->get('/admin/raffles/create');
    
    $response->assertOk();
    $response->assertSee('Cantidad de dígitos', false);
    $response->assertSee('Número mínimo', false);
    $response->assertSee('Número máximo', false);
    $response->assertSee('Configuración de números de tickets', false);
});
