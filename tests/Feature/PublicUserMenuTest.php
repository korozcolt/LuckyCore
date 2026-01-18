<?php

use App\Models\User;

it('renders the public user menu for authenticated users', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get('/');

    $response->assertOk();
    $response->assertSee(route('orders.index'), false);
    $response->assertSee(route('profile.edit'), false);
    $response->assertSee(route('logout'), false);
});

it('allows authenticated users to visit their profile settings', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('profile.edit'));

    $response->assertOk();
});
