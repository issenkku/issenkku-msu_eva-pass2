<?php

use App\Models\User;
use Spatie\Permission\Models\Role;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('layout app shell renders mobile controls with data hooks instead of inline handlers', function () {
    Role::create(['name' => 'admin']);
    $user = User::factory()->create();
    $user->assignRole('admin');

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertStatus(200)
        ->assertSee('data-mobile-menu-toggle', false)
        ->assertSee('data-mobile-menu-close', false)
        ->assertSee('data-mobile-dropdown-toggle', false)
        ->assertSee('data-mobile-dropdown-target="settingsDropdown"', false)
        ->assertSee('aria-controls="mobileMenu"', false)
        ->assertSee('aria-expanded="false"', false)
        ->assertSee('aria-controls="settingsDropdownMenu"', false)
        ->assertDontSee('onclick="toggleMobileMenu()"', false)
        ->assertDontSee('onclick="closeMobileMenu()"', false)
        ->assertDontSee('onclick="toggleMobileDropdown(\'settingsDropdown\')"', false);
});
