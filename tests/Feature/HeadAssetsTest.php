<?php

use App\Enums\UserRole;
use App\Models\User;
use Spatie\Permission\Models\Role;

it('renders the public layout with official favicon and logo', function () {
    $response = $this->get('/');

    $response->assertOk();
    $response->assertSee('/favicon.ico', false);
    $response->assertSee('/site.webmanifest', false);
    $response->assertSee('/images/logo.webp', false);
});

it('serves the favicon from /favicon.ico', function () {
    $response = $this->get('/favicon.ico');

    $response->assertOk();
    $response->assertHeader('content-type', 'image/x-icon');
});

it('renders the login page with official favicon and logo', function () {
    $response = $this->get('/login');

    $response->assertOk();
    $response->assertSee('/favicon.ico', false);
    $response->assertSee('/site.webmanifest', false);
    $response->assertSee('/images/logo.webp', false);
});

it('renders the filament login page with the official favicon', function () {
    $response = $this->get('/admin/login');

    $response->assertOk();
    $response->assertSee('/favicon.ico', false);
});

it('renders the filament dashboard with the official favicon', function () {
    if (! Role::where('name', UserRole::Admin->value)->exists()) {
        Role::create(['name' => UserRole::Admin->value, 'guard_name' => 'web']);
    }

    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin->value);

    $this->actingAs($admin);

    $response = $this->get('/admin');

    $response->assertOk();
    $response->assertSee('/favicon.ico', false);
});
