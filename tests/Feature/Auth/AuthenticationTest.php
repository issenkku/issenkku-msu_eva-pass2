<?php

use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('login screen can be rendered', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
});

test('login assets use https behind the trusted local UAT proxy', function () {
    $response = $this
        ->withServerVariables([
            'REMOTE_ADDR' => '127.0.0.1',
            'HTTP_HOST' => 'msu-eva.test:8443',
            'SERVER_NAME' => 'msu-eva.test',
            'SERVER_PORT' => '8000',
        ])
        ->withHeaders([
            'Host' => 'msu-eva.test:8443',
            'X-Forwarded-Host' => 'msu-eva.test:8443',
            'X-Forwarded-Port' => '8443',
            'X-Forwarded-Proto' => 'https',
        ])
        ->get('/login');

    $response
        ->assertOk()
        ->assertSee('https://msu-eva.test:8443/favicon-msu.png', false)
        ->assertDontSee('http://msu-eva.test:8443/favicon-msu.png', false);
});

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create();

    $response = $this->post('/login', [
        'employee_id' => $user->employee_id,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('home'));
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    $this->post('/login', [
        'employee_id' => $user->employee_id,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});

test('users can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/logout');

    $this->assertGuest();
    $response->assertRedirect('/login');
});
