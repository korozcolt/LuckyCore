<?php

use App\Models\User;

it('renders profile settings using the public layout', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get('/settings/profile');

    $response->assertOk();
    $response->assertSee('Mi cuenta', false);
    $response->assertSee('/images/logo.webp', false);
    $response->assertSee('Sorteos', false);
});
