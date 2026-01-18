<?php

it('renders the public layout with official favicon and logo', function () {
    $response = $this->get('/');

    $response->assertOk();
    $response->assertSee('/favicon.ico', false);
    $response->assertSee('/site.webmanifest', false);
    $response->assertSee('/images/logo.webp', false);
});

it('renders the login page with official favicon and logo', function () {
    $response = $this->get('/login');

    $response->assertOk();
    $response->assertSee('/favicon.ico', false);
    $response->assertSee('/site.webmanifest', false);
    $response->assertSee('/images/logo.webp', false);
});
