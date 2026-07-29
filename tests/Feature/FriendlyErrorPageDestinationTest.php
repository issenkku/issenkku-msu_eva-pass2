<?php

use App\Models\User;
use App\Support\FriendlyErrorPage;
use Spatie\Permission\Models\Role;

test('anonymous and unknown-role users safely fall back to Login', function () {
    $user = User::factory()->create();

    expect(FriendlyErrorPage::destinationUrl(null))
        ->toBe(route('login'))
        ->and(FriendlyErrorPage::destinationUrl($user))
        ->toBe(route('login'));
});

test('a recognized role resolves to its configured dashboard', function () {
    Role::create(['name' => 'admin']);
    $user = User::factory()->create();
    $user->assignRole('admin');

    expect(FriendlyErrorPage::destinationUrl($user))
        ->toBe(route('dashboard'));
});
