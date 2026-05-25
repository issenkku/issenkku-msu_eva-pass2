<?php

use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('profile page is displayed', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get('/settings/profile');

    $response->assertOk();
});

test('profile information can be updated', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch('/settings/profile', [
            'prefix' => $user->prefix,
            'name' => 'Test User',
            'email' => 'test.user@gmail.com',
            'employee_id' => $user->employee_id,
            'phone' => '0999999999',
            'personnel_type' => $user->personnel_type,
            'position_id' => $user->position_id,
            'department_id' => $user->department_id,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    $user->refresh();

    expect($user->name)->toBe('Test User');
    expect($user->email)->toBe('test.user@gmail.com');
    expect($user->email_verified_at)->toBeNull();
});

test('email verification status is unchanged when the email address is unchanged', function () {
    $user = User::factory()->create([
        'email' => 'unchanged.user@gmail.com',
    ]);

    $response = $this
        ->actingAs($user)
        ->patch('/settings/profile', [
            'prefix' => $user->prefix,
            'name' => 'Test User',
            'email' => $user->email,
            'employee_id' => $user->employee_id,
            'phone' => $user->phone,
            'personnel_type' => $user->personnel_type,
            'position_id' => $user->position_id,
            'department_id' => $user->department_id,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    expect($user->refresh()->email_verified_at)->not->toBeNull();
});

test('user can delete their account', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->delete('/settings/profile', [
            'password' => 'password',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/');

    $this->assertGuest('web');
    expect($user->fresh())->toBeNull();
});

test('correct password must be provided to delete account', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->from('/settings/profile')
        ->delete('/settings/profile', [
            'password' => 'wrong-password',
        ]);

    $response
        ->assertSessionHasErrors('password')
        ->assertRedirect('/settings/profile');

    expect($user->fresh())->not->toBeNull();
});
