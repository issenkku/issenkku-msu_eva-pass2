<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

function registerAdminNavigationRoute(string $routeName): array
{
    $role = Role::firstOrCreate(['name' => 'admin']);
    $user = User::factory()->create();
    $user->assignRole($role);
    $uri = '/_navigation-state/'.str_replace(['.', '*'], '-', $routeName);

    Route::get($uri, fn () => view('layouts.app'))
        ->name($routeName);

    return [$user, $uri];
}

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

test('evaluator navigation distinguishes evaluation work from other dashboard links', function () {
    $layout = file_get_contents(resource_path('views/layouts/app.blade.php'));

    preg_match_all(
        '/<a\b[^>]*href="\/evaluator-dashboard"[^>]*>(?:(?!<\/a>).)*?หน้าประเมินผู้อื่น(?:(?!<\/a>).)*?<\/a>/su',
        $layout,
        $evaluatorLinks,
    );
    preg_match_all(
        '/<a\b[^>]*href="\/evaluator-dashboard"[^>]*>(?:(?!<\/a>).)*?หน้าหลัก(?:(?!<\/a>).)*?<\/a>/su',
        $layout,
        $oldEvaluatorLinks,
    );
    preg_match_all(
        '/<a\b[^>]*href="\/dashboard"[^>]*>(?:(?!<\/a>).)*?หน้าหลัก(?:(?!<\/a>).)*?<\/a>/su',
        $layout,
        $adminDesktopLinks,
    );
    preg_match_all(
        '/<a\b[^>]*href="\/dashboard"[^>]*>(?:(?!<\/a>).)*?หน้าแรก(?:(?!<\/a>).)*?<\/a>/su',
        $layout,
        $adminMobileLinks,
    );
    preg_match_all(
        '/<a\b[^>]*href="\/evaluatee-dashboard"[^>]*>(?:(?!<\/a>).)*?หน้าประเมินตนเอง(?:(?!<\/a>).)*?<\/a>/su',
        $layout,
        $evaluateeLinks,
    );

    expect($evaluatorLinks[0])->toHaveCount(2)
        ->and($oldEvaluatorLinks[0])->toHaveCount(0)
        ->and($adminDesktopLinks[0])->toHaveCount(1)
        ->and($adminMobileLinks[0])->toHaveCount(1)
        ->and($evaluateeLinks[0])->toHaveCount(2);
});

test('admin routes activate the owning desktop and mobile navigation', function (
    string $routeName,
    string $parentKey,
    string $childKey,
) {
    [$user, $uri] = registerAdminNavigationRoute($routeName);

    $html = $this->actingAs($user)->get($uri)->assertOk()->getContent();

    expect($html)
        ->toMatch('/<a(?=[^>]*data-nav-key="'.$parentKey.'")(?=[^>]*class="[^"]*is-active)(?=[^>]*aria-current="true")[^>]*>/s')
        ->toMatch('/<a(?=[^>]*data-nav-child="'.$childKey.'")(?=[^>]*class="[^"]*is-active)(?=[^>]*aria-current="page")[^>]*>/s')
        ->toMatch('/<(?:a|button)(?=[^>]*data-mobile-nav-key="'.$childKey.'")(?=[^>]*class="[^"]*is-active)(?=[^>]*aria-current="page")[^>]*>/s');
})->with([
    ['users.navigation-test', 'user-data', 'users'],
    ['user.management.log.navigation-test', 'user-data', 'user-log'],
    ['criteria_config.navigation-test', 'criteria-management', 'criteria-config'],
    ['assignment-data.navigation-test', 'criteria-management', 'assignment-data'],
    ['settings.navigation-test', 'settings', 'settings-website'],
    ['departments.navigation-test', 'settings', 'departments'],
    ['positions.navigation-test', 'settings', 'positions'],
    ['job-level.navigation-test', 'settings', 'job-level'],
    ['subjects.navigation-test', 'settings', 'subjects'],
]);

test('active mobile settings route expands its parent dropdown', function () {
    [$user, $uri] = registerAdminNavigationRoute('departments.mobile-navigation-test');

    $html = $this->actingAs($user)->get($uri)->assertOk()->getContent();

    expect($html)
        ->toMatch('/<div(?=[^>]*id="settingsDropdown")(?=[^>]*class="[^"]*active)[^>]*>/s')
        ->toMatch('/<button(?=[^>]*data-mobile-nav-key="settings")(?=[^>]*class="[^"]*is-active)(?=[^>]*aria-expanded="true")(?=[^>]*aria-current="true")[^>]*>/s');
});

test('layout defines route ownership for every primary navigation role', function () {
    $layout = file_get_contents(resource_path('views/layouts/app.blade.php'));

    expect($layout)
        ->toContain("routeIs('dashboard', 'dashboard.*', 'admin.show')")
        ->toContain("routeIs('manager.dashboard', 'manager.show')")
        ->toContain("routeIs('director.dashboard', 'director.show')")
        ->toContain("routeIs('evaluator.*')")
        ->toContain("routeIs('evaluatee.dashboard', 'evaluation.show', 'evaluatee.workload')")
        ->toContain("routeIs('profile.show', 'profile.edit')");
});

test('admin dashboard marks only admin home active in desktop and mobile navigation', function () {
    $role = Role::firstOrCreate(['name' => 'admin']);
    $user = User::factory()->create();
    $user->assignRole($role);

    $html = $this->actingAs($user)->get('/dashboard')->assertOk()->getContent();

    expect($html)
        ->toMatch('/<a(?=[^>]*data-nav-key="admin-home")(?=[^>]*class="[^"]*is-active)(?=[^>]*aria-current="page")[^>]*>/s')
        ->toMatch('/<a(?=[^>]*data-mobile-nav-key="admin-home")(?=[^>]*class="[^"]*is-active)(?=[^>]*aria-current="page")[^>]*>/s')
        ->not->toMatch('/<a(?=[^>]*data-nav-key="user-data")(?=[^>]*class="[^"]*is-active)[^>]*>/s');
});

test('desktop navigation dropdown trigger ids are unique', function () {
    $layout = file_get_contents(resource_path('views/layouts/app.blade.php'));

    preg_match_all(
        '/id="(userDataDropdown|criteriaManagementDropdown|settingsDropdownDesktop)"/',
        $layout,
        $matches,
    );

    expect($matches[1])->toHaveCount(3)
        ->and(array_unique($matches[1]))->toHaveCount(3)
        ->and($layout)->not->toContain('id="settingDropdown"');
});

test('navigation active states use the approved accessible visual tokens', function () {
    $styles = file_get_contents(
        resource_path('views/partials/layout-app-styles.blade.php')
    );

    expect($styles)
        ->toContain('.app-nav-link.is-active')
        ->toContain('rgba(139, 92, 246, 0.18)')
        ->toContain('inset 0 -3px 0 #8b5cf6')
        ->toContain('#c4b5fd')
        ->toContain('.app-dropdown-item.is-active')
        ->toContain('.mobile-nav-item.is-active')
        ->toContain('.mobile-dropdown-toggle.is-active')
        ->toContain('.mobile-dropdown-item.is-active')
        ->toContain('border-left: 4px solid #8b5cf6')
        ->toContain('#f3e8ff')
        ->toContain('#6d28d9')
        ->toContain(':focus-visible')
        ->toContain('@media (prefers-reduced-motion: reduce)');
});

test('mobile profile link does not suppress the active edge marker', function () {
    $layout = file_get_contents(resource_path('views/layouts/app.blade.php'));
    $profileStart = strpos($layout, 'data-mobile-nav-key="profile"');
    $profileMarkup = substr($layout, $profileStart, 500);

    expect($profileMarkup)->not->toContain('border: none');
});
