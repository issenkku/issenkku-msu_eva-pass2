# Navigation Active State Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Show the current top-level navigation section and exact dropdown child across authenticated desktop and mobile navigation.

**Architecture:** The shared Blade layout derives route ownership once with `request()->routeIs(...)` and exposes named booleans consumed by both desktop and mobile markup. Semantic `is-active` classes and `aria-current` attributes carry state; the shared layout stylesheet provides the approved purple desktop, dropdown, and mobile treatments without JavaScript route detection.

**Tech Stack:** Laravel 11, Blade, Bootstrap navigation markup, Tailwind utility classes, Pest/PHPUnit, CSS

## Global Constraints

- Route names are the source of truth; do not inspect `window.location` or URL strings in JavaScript.
- Desktop and mobile navigation must consume the same route-state booleans.
- Active state must use background, text/icon colour, and an edge marker rather than colour alone.
- Direct links use `aria-current="page"`; active parent dropdowns use `aria-current="true"`.
- Keep the current navigation order, labels, role checks, breakpoints, and mobile-menu architecture.
- Use active purple `#8b5cf6`, active lavender `#c4b5fd`, desktop wash `rgba(139, 92, 246, 0.18)`, mobile wash `#f3e8ff`, and mobile active text `#6d28d9`.
- Do not add a font, frontend framework, package, breadcrumb, or active state for the external handbook.
- Preserve unrelated working-tree changes.

---

## File Structure

- Modify `resources/views/layouts/app.blade.php`: derive route ownership, apply desktop/mobile state hooks, fix duplicate dropdown IDs, and initially expand active mobile Settings.
- Modify `resources/views/partials/layout-app-styles.blade.php`: define active, dropdown-child, mobile, focus-visible, and reduced-motion treatments.
- Modify `tests/Feature/LayoutAppShellTest.php`: verify route grouping, semantic markup, desktop/mobile parity, unique IDs, and visual CSS contract.

### Task 1: Route Ownership and Semantic Navigation State

**Files:**
- Modify: `tests/Feature/LayoutAppShellTest.php`
- Modify: `resources/views/layouts/app.blade.php`

**Interfaces:**
- Produces Blade booleans: `$isAdminHomeActive`, `$isManagerHomeActive`, `$isDirectorHomeActive`, `$isEvaluatorActive`, `$isEvaluateeActive`, `$isUserDataActive`, `$isCriteriaManagementActive`, `$isSettingsActive`, and `$isProfileActive`
- Produces child booleans: `$isUsersActive`, `$isUserLogActive`, `$isCriteriaConfigActive`, `$isAssignmentDataActive`, `$isWebsiteSettingsActive`, `$isDepartmentsActive`, `$isPositionsActive`, `$isJobLevelActive`, and `$isSubjectsActive`
- Produces DOM hooks: `data-nav-key`, `data-mobile-nav-key`, `is-active`, and `aria-current`

- [ ] **Step 1: Add failing route-state feature tests**

Add these imports and helper to `LayoutAppShellTest.php`:

```php
use Illuminate\Support\Facades\Route;

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
```

Add one dataset-driven test for admin parents and children:

```php
test('admin routes activate the owning desktop and mobile navigation', function (
    string $routeName,
    string $parentKey,
    string $childKey,
) {
    [$user, $uri] = registerAdminNavigationRoute($routeName);
    $response = $this->actingAs($user)->get($uri);

    $response->assertOk()
        ->assertSee(
            'data-nav-key="'.$parentKey.'" class="nav-link',
            false,
        )
        ->assertSee(
            'data-nav-key="'.$parentKey.'" class="nav-link dropdown-toggle'
                .' app-nav-link is-active',
            false,
        )
        ->assertSee(
            'data-nav-child="'.$childKey.'" class="dropdown-item'
                .' app-dropdown-item is-active" aria-current="page"',
            false,
        )
        ->assertSee(
            'data-mobile-nav-key="'.$childKey.'" class="mobile-nav-item is-active"'
                .' aria-current="page"',
            false,
        );
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
```

Because mobile Settings is a grouped control rather than a direct parent link,
add its expansion test separately:

```php
test('active mobile settings route expands its parent dropdown', function () {
    [$user, $uri] = registerAdminNavigationRoute('departments.navigation-test');
    $response = $this->actingAs($user)->get($uri);

    $response->assertOk()
        ->assertSee(
            'id="settingsDropdown" class="mobile-dropdown active"',
            false,
        )
        ->assertSee(
            'data-mobile-nav-key="settings" class="mobile-dropdown-toggle is-active"',
            false,
        )
        ->assertSee('aria-expanded="true"', false);
});
```

Add a source-contract test for role-specific primary navigation:

```php
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
```

Add a dashboard test proving one active owner is rendered in both variants:

```php
test('admin dashboard marks only admin home active in desktop and mobile navigation', function () {
    $role = Role::firstOrCreate(['name' => 'admin']);
    $user = User::factory()->create();
    $user->assignRole($role);

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertOk()
        ->assertSee(
            'data-nav-key="admin-home" class="nav-link text-white'
                .' d-flex align-items-center gap-2 app-nav-link is-active"'
                .' href="/dashboard" aria-current="page"',
            false,
        )
        ->assertSee(
            'data-mobile-nav-key="admin-home"'
                .' class="mobile-nav-item is-active" aria-current="page"',
            false,
        )
        ->assertDontSee(
            'data-nav-key="user-data" class="nav-link dropdown-toggle'
                .' app-nav-link is-active',
            false,
        );
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
```

- [ ] **Step 2: Run the layout tests and verify RED**

Run:

```powershell
vendor\bin\pest.bat tests/Feature/LayoutAppShellTest.php
```

Expected: FAIL because the layout does not expose route ownership, `is-active`,
`aria-current`, or navigation data hooks.

- [ ] **Step 3: Compute all route-state booleans once**

Extend the existing `@php` block near `$appSetting` in
`resources/views/layouts/app.blade.php`:

```blade
@php
    $appSetting = \App\Models\Setting\Settings::first();

    $isAdminHomeActive = request()->routeIs('dashboard', 'dashboard.*', 'admin.show');
    $isManagerHomeActive = request()->routeIs('manager.dashboard', 'manager.show');
    $isDirectorHomeActive = request()->routeIs('director.dashboard', 'director.show');
    $isEvaluatorActive = request()->routeIs('evaluator.*');
    $isEvaluateeActive = request()->routeIs(
        'evaluatee.dashboard',
        'evaluation.show',
        'evaluatee.workload',
    );
    $isUsersActive = request()->routeIs('users.*');
    $isUserLogActive = request()->routeIs('user.management.log*');
    $isUserDataActive = $isUsersActive || $isUserLogActive;
    $isCriteriaConfigActive = request()->routeIs('criteria_config.*');
    $isAssignmentDataActive = request()->routeIs('assignment-data.*');
    $isCriteriaManagementActive = $isCriteriaConfigActive || $isAssignmentDataActive;
    $isWebsiteSettingsActive = request()->routeIs('settings.*');
    $isDepartmentsActive = request()->routeIs('departments.*');
    $isPositionsActive = request()->routeIs('positions.*');
    $isJobLevelActive = request()->routeIs('job-level.*');
    $isSubjectsActive = request()->routeIs('subjects.*');
    $isSettingsActive = $isWebsiteSettingsActive
        || $isDepartmentsActive
        || $isPositionsActive
        || $isJobLevelActive
        || $isSubjectsActive;
    $isProfileActive = request()->routeIs('profile.show', 'profile.edit');
@endphp
```

- [ ] **Step 4: Apply semantic state to desktop direct links**

For each role-owned direct link, use the exact state:

```blade
<a
    data-nav-key="manager-home"
    @class([
        'nav-link text-white d-flex align-items-center gap-2 app-nav-link',
        'is-active' => $isManagerHomeActive,
    ])
    href="/manager-dashboard"
    @if($isManagerHomeActive) aria-current="page" @endif>
```

```blade
<a
    data-nav-key="admin-home"
    @class([
        'nav-link text-white d-flex align-items-center gap-2 app-nav-link',
        'is-active' => $isAdminHomeActive,
    ])
    href="/dashboard"
    @if($isAdminHomeActive) aria-current="page" @endif>
```

```blade
<a
    data-nav-key="evaluator"
    @class([
        'nav-link text-white d-flex align-items-center gap-2 app-nav-link',
        'is-active' => $isEvaluatorActive,
    ])
    href="/evaluator-dashboard"
    @if($isEvaluatorActive) aria-current="page" @endif>
```

```blade
<a
    data-nav-key="director-home"
    @class([
        'nav-link text-white d-flex align-items-center gap-2 app-nav-link',
        'is-active' => $isDirectorHomeActive,
    ])
    href="/director-dashboard"
    @if($isDirectorHomeActive) aria-current="page" @endif>
```

```blade
<a
    data-nav-key="evaluatee"
    @class([
        'nav-link text-white app-nav-link',
        'is-active' => $isEvaluateeActive,
    ])
    href="/evaluatee-dashboard"
    @if($isEvaluateeActive) aria-current="page" @endif>
```

- [ ] **Step 5: Apply desktop parent and child state**

Use unique trigger IDs and these exact keys:

```blade
<a
    data-nav-key="user-data"
    @class([
        'nav-link dropdown-toggle text-white d-flex align-items-center gap-2 app-nav-link',
        'is-active' => $isUserDataActive,
    ])
    href="#"
    id="userDataDropdown"
    role="button"
    data-bs-toggle="dropdown"
    aria-expanded="false"
    @if($isUserDataActive) aria-current="true" @endif>
```

The user dropdown menu uses `aria-labelledby="userDataDropdown"` and:

```blade
<a
    data-nav-child="users"
    @class(['dropdown-item app-dropdown-item', 'is-active' => $isUsersActive])
    href="{{ route('users.index') }}"
    @if($isUsersActive) aria-current="page" @endif>
```

```blade
<a
    data-nav-child="user-log"
    @class(['dropdown-item app-dropdown-item', 'is-active' => $isUserLogActive])
    href="{{ route('user.management.log') }}"
    @if($isUserLogActive) aria-current="page" @endif>
```

The criteria trigger uses `data-nav-key="criteria-management"`,
`id="criteriaManagementDropdown"`, `$isCriteriaManagementActive`, and
`aria-labelledby="criteriaManagementDropdown"`. Its children use:

```blade
@class(['dropdown-item app-dropdown-item', 'is-active' => $isCriteriaConfigActive])
data-nav-child="criteria-config"
```

```blade
@class(['dropdown-item app-dropdown-item', 'is-active' => $isAssignmentDataActive])
data-nav-child="assignment-data"
```

The Settings trigger uses `data-nav-key="settings"`,
`id="settingsDropdownDesktop"`, `$isSettingsActive`, and
`aria-labelledby="settingsDropdownDesktop"`. Its five children use these
key/state pairs:

```text
settings-website => $isWebsiteSettingsActive
departments      => $isDepartmentsActive
positions        => $isPositionsActive
job-level        => $isJobLevelActive
subjects         => $isSubjectsActive
```

Every active parent uses `aria-current="true"` and every active child uses
`aria-current="page"`.

- [ ] **Step 6: Apply profile and mobile state**

The profile dropdown trigger receives `data-nav-key="profile"`,
`app-nav-link`, `$isProfileActive`, and `aria-current="true"` when active. Its
profile child receives `app-dropdown-item`, `is-active`, and
`aria-current="page"`.

Apply these mobile direct-link key/state pairs with
`data-mobile-nav-key`, `is-active`, and `aria-current="page"`:

```text
manager-home     => $isManagerHomeActive
evaluator        => $isEvaluatorActive
director-home    => $isDirectorHomeActive
evaluatee        => $isEvaluateeActive
admin-home       => $isAdminHomeActive
users            => $isUsersActive
user-log         => $isUserLogActive
criteria-config  => $isCriteriaConfigActive
assignment-data  => $isAssignmentDataActive
profile          => $isProfileActive
```

Initialize the mobile Settings block with:

```blade
<div
    id="settingsDropdown"
    @class(['mobile-dropdown', 'active' => $isSettingsActive])>
    <button
        data-mobile-nav-key="settings"
        @class(['mobile-dropdown-toggle', 'is-active' => $isSettingsActive])
        type="button"
        data-mobile-dropdown-toggle
        data-mobile-dropdown-target="settingsDropdown"
        aria-controls="settingsDropdownMenu"
        aria-expanded="{{ $isSettingsActive ? 'true' : 'false' }}"
        @if($isSettingsActive) aria-current="true" @endif>
```

Each mobile Settings child receives `data-mobile-nav-key`, `is-active`, and
`aria-current="page"` using the five Settings key/state pairs defined in Step
5.

- [ ] **Step 7: Run route-state tests and verify GREEN**

Run:

```powershell
vendor\bin\pest.bat tests/Feature/LayoutAppShellTest.php
```

Expected: PASS.

- [ ] **Step 8: Commit semantic active states**

```powershell
git add resources/views/layouts/app.blade.php tests/Feature/LayoutAppShellTest.php
git commit -m "feat: mark active navigation routes"
```

### Task 2: Accessible Active-State Styling

**Files:**
- Modify: `tests/Feature/LayoutAppShellTest.php`
- Modify: `resources/views/partials/layout-app-styles.blade.php`

**Interfaces:**
- Consumes: `.app-nav-link.is-active`, `.app-dropdown-item.is-active`, `.mobile-nav-item.is-active`, `.mobile-dropdown-toggle.is-active`, and `.mobile-dropdown-item.is-active`
- Produces: desktop bottom marker, dropdown active wash, mobile left marker, focus-visible ring, and reduced-motion behaviour

- [ ] **Step 1: Add a failing CSS contract test**

Add:

```php
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
```

- [ ] **Step 2: Run the CSS contract test and verify RED**

Run:

```powershell
vendor\bin\pest.bat tests/Feature/LayoutAppShellTest.php --filter="approved accessible visual tokens"
```

Expected: FAIL because the active selectors and approved tokens are absent.

- [ ] **Step 3: Add desktop and dropdown active styles**

Add near the existing navbar styles:

```css
.app-nav-link {
    position: relative;
    border-radius: 0.5rem;
    transition: background-color 160ms ease, color 160ms ease, box-shadow 160ms ease;
}

.app-nav-link.is-active {
    background: rgba(139, 92, 246, 0.18);
    color: #c4b5fd !important;
    box-shadow: inset 0 -3px 0 #8b5cf6;
}

.app-nav-link.is-active i {
    color: #c4b5fd;
}

.app-nav-link:focus-visible {
    outline: 2px solid #c4b5fd;
    outline-offset: 2px;
}

.app-dropdown-item.is-active {
    background: #f3e8ff;
    color: #6d28d9;
    font-weight: 600;
    box-shadow: inset 4px 0 0 #8b5cf6;
}

.app-dropdown-item.is-active i {
    color: #8b5cf6;
}

.app-dropdown-item:focus-visible {
    outline: 2px solid #8b5cf6;
    outline-offset: -2px;
}
```

- [ ] **Step 4: Add mobile active and motion styles**

Add near existing mobile navigation styles:

```css
.mobile-nav-item.is-active,
.mobile-dropdown-toggle.is-active,
.mobile-dropdown-item.is-active {
    background: #f3e8ff;
    color: #6d28d9;
    border-left: 4px solid #8b5cf6;
    font-weight: 600;
}

.mobile-nav-item.is-active i,
.mobile-dropdown-toggle.is-active i {
    color: #8b5cf6;
}

.mobile-nav-item:focus-visible,
.mobile-dropdown-toggle:focus-visible,
.mobile-dropdown-item:focus-visible {
    outline: 2px solid #8b5cf6;
    outline-offset: -2px;
}

@media (prefers-reduced-motion: reduce) {
    .app-nav-link,
    .mobile-nav-item,
    .mobile-dropdown-toggle,
    .mobile-dropdown-content,
    .mobile-dropdown-item {
        transition: none;
    }
}
```

- [ ] **Step 5: Run layout and related accessibility tests**

Run:

```powershell
vendor\bin\pest.bat tests/Feature/LayoutAppShellTest.php tests/Feature/RoleTableRenderTest.php
```

Expected: every selected test passes.

- [ ] **Step 6: Commit active-state styling**

```powershell
git add resources/views/partials/layout-app-styles.blade.php tests/Feature/LayoutAppShellTest.php
git commit -m "style: highlight active navigation"
```

### Task 3: Full Verification

**Files:**
- Verify only

**Interfaces:**
- Verifies the route-state and styling interfaces from Tasks 1–2

- [ ] **Step 1: Check scoped changes and duplicated IDs**

Run:

```powershell
git diff --check
rg -n 'id="settingDropdown"' resources/views/layouts/app.blade.php
rg -n 'id="(userDataDropdown|criteriaManagementDropdown|settingsDropdownDesktop)"' resources/views/layouts/app.blade.php
```

Expected: no whitespace errors, no remaining `id="settingDropdown"`, and one
match for each new unique desktop dropdown ID.

- [ ] **Step 2: Format changed PHP test files**

Run:

```powershell
vendor\bin\pint.bat tests/Feature/LayoutAppShellTest.php
```

Expected: Pint completes successfully.

- [ ] **Step 3: Run the complete automated suite**

Run:

```powershell
composer test
```

Expected: all tests pass with zero failures.

- [ ] **Step 4: Verify the final branch state**

Run:

```powershell
git status --short
git log -4 --oneline
```

Expected: the two focused implementation commits are present and only
pre-existing unrelated working-tree changes remain.
