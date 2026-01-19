<?php

use App\Enums\UserRole;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

it('denies access to admin panel for guests', function () {
    $this->get('/admin')
        ->assertRedirect('/admin/login');
});

it('denies access to admin panel for customers', function () {
    $user = User::factory()->create();
    $user->assignRole(UserRole::Customer->value);

    $this->actingAs($user)
        ->get('/admin')
        ->assertForbidden();
});

it('denies access to admin panel for support users', function () {
    $user = User::factory()->create();
    $user->assignRole(UserRole::Support->value);

    $this->actingAs($user)
        ->get('/admin')
        ->assertForbidden();
});

it('allows access to admin panel for admin users', function () {
    $user = User::factory()->create();
    $user->assignRole(UserRole::Admin->value);

    $this->actingAs($user)
        ->get('/admin')
        ->assertSuccessful();
});

it('allows access to admin panel for super admin users', function () {
    $user = User::factory()->create();
    $user->assignRole(UserRole::SuperAdmin->value);

    $this->actingAs($user)
        ->get('/admin')
        ->assertSuccessful();
});
