<?php

use App\Enums\UserRole;
use App\Models\User;
use Spatie\Permission\Models\Role;

it('shows the login page for guests', function () {
    $response = $this->get(route('login'));

    $response->assertOk();
});

it('redirects authenticated customers away from /login', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('login'));

    $response->assertRedirect(route('dashboard'));
});

it('redirects authenticated admins to filament via dashboard', function () {
    if (! Role::where('name', UserRole::Admin->value)->exists()) {
        Role::create(['name' => UserRole::Admin->value, 'guard_name' => 'web']);
    }

    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin->value);
    $this->actingAs($admin);

    $response = $this->get(route('dashboard'));

    $response->assertRedirect('/admin');
});
