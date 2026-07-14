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

test('evaluatee navigation uses formal wording in desktop and mobile menus', function () {
    $layout = file_get_contents(resource_path('views/layouts/app.blade.php'));

    preg_match_all(
        '/<a\b[^>]*href="\/evaluatee-dashboard"[^>]*>.*?หน้าประเมินตนเอง.*?<\/a>/su',
        $layout,
        $matchingLinks,
    );

    expect($matchingLinks[0])->toHaveCount(2)
        ->and($layout)->not->toContain('หน้าประเมินตัวเอง');
});
