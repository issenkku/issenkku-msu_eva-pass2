# Friendly Error Pages Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Replace raw web error responses with actionable Thai pages, redirect expired web sessions to Login with a warning, and leave API/AJAX JSON responses unchanged.

**Architecture:** Laravel's existing exception reporting remains intact; a final response callback in `bootstrap/app.php` transforms only non-JSON exception responses. A focused support class resolves safe dashboard/Login destinations, an anonymous Blade component owns the shared standalone error shell, and thin status views provide status-specific copy and actions.

**Tech Stack:** PHP 8.2+, Laravel 11, Blade, Pest 3, PHPUnit feature testing, Vite.

## Global Constraints

- Web `401` and `419` responses redirect immediately to `/login` with `เซสชันหมดอายุหรือออกจากระบบแล้ว กรุณาเข้าสู่ระบบอีกครั้ง`.
- Intentional logout redirects to `/login` with the distinct success message `ออกจากระบบเรียบร้อยแล้ว`.
- Web `403`, `404`, `500`, and `503` responses use friendly Thai HTML and retain their original HTTP status.
- API, AJAX, and all requests for which `Request::expectsJson()` is true retain Laravel's original JSON response, status, and reporting behavior.
- HTML and flash data must not expose exception messages, stack traces, file paths, SQL, environment values, or internal identifiers.
- Unexpected exceptions must continue through Laravel's normal reporting pipeline; do not register `dontReport`, `stopIgnoring`, or a replacement reporting callback.
- Error-page rendering must fall back to Laravel's already-produced response if the friendly view itself cannot render.
- Error pages must not query site settings or another database-backed model while handling a failure; use `config('app.name')` and the static public favicon.
- All PHP test commands run sequentially because `phpunit.xml` points every process at `database/testing.sqlite`; never run Pest invocations in parallel.
- Preserve unrelated working-tree changes and stage only files named by the current task.

## File Structure

- Create `app/Support/FriendlyErrorPage.php`: central copy constants and safe role-aware destination URL resolver.
- Create `tests/Feature/FriendlyErrorPageDestinationTest.php`: resolver behavior for anonymous, unknown-role, and recognized-role users.
- Modify `bootstrap/app.php`: transform only final non-JSON exception responses.
- Modify `app/Http/Controllers/Auth/AuthController.php`: use the approved intentional-logout copy.
- Modify `resources/views/user/management/loginForm.blade.php`: render accessible server-side warning/success regions independently of AJAX credential errors.
- Modify `tests/Feature/User/AuthenticateTest.php`: update the intentional logout assertion.
- Create `tests/Feature/FriendlySessionErrorTest.php`: protected-request, explicit `401`/`419`, Login notification, and JSON regression coverage.
- Create `resources/views/components/error-page.blade.php`: dependency-light responsive error shell and accessible action markup.
- Create `resources/views/errors/403.blade.php`: permission-denied copy and dashboard/back actions.
- Create `resources/views/errors/404.blade.php`: not-found copy and dashboard/back actions.
- Create `resources/views/errors/500.blade.php`: temporary-failure copy and retry/dashboard actions.
- Create `resources/views/errors/503.blade.php`: unavailable copy and retry/Login actions.
- Create `tests/Feature/FriendlyErrorPagesTest.php`: status, content, redaction, JSON, and safe-action integration coverage.

---

### Task 1: Safe Error-Page Destination Resolver

**Files:**

- Create: `app/Support/FriendlyErrorPage.php`
- Create: `tests/Feature/FriendlyErrorPageDestinationTest.php`

**Interfaces:**

- Consumes: `App\Models\User::defaultDashboardRoute(): ?string`, Laravel's named-route registry.
- Produces: `FriendlyErrorPage::destinationUrl(?User $user): string`, `FriendlyErrorPage::SESSION_EXPIRED_MESSAGE`, and `FriendlyErrorPage::LOGOUT_MESSAGE`.

- [ ] **Step 1: Write the failing destination tests**

Create `tests/Feature/FriendlyErrorPageDestinationTest.php`:

```php
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
```

- [ ] **Step 2: Run the focused test and verify RED**

Run:

```powershell
php -d memory_limit=512M vendor/bin/pest tests/Feature/FriendlyErrorPageDestinationTest.php
```

Expected: FAIL because `App\Support\FriendlyErrorPage` does not exist.

- [ ] **Step 3: Implement the resolver and approved message constants**

Create `app/Support/FriendlyErrorPage.php`:

```php
<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Route;
use Throwable;

final class FriendlyErrorPage
{
    public const SESSION_EXPIRED_MESSAGE = 'เซสชันหมดอายุหรือออกจากระบบแล้ว กรุณาเข้าสู่ระบบอีกครั้ง';

    public const LOGOUT_MESSAGE = 'ออกจากระบบเรียบร้อยแล้ว';

    public static function destinationUrl(?User $user): string
    {
        $routeName = $user?->defaultDashboardRoute();

        if ($routeName !== null && Route::has($routeName)) {
            try {
                return route($routeName);
            } catch (Throwable) {
                // The Login fallback below must remain safe inside error handling.
            }
        }

        return Route::has('login') ? route('login') : '/login';
    }
}
```

- [ ] **Step 4: Run the focused test and verify GREEN**

Run:

```powershell
php -d memory_limit=512M vendor/bin/pest tests/Feature/FriendlyErrorPageDestinationTest.php
```

Expected: 2 tests PASS.

- [ ] **Step 5: Commit the resolver**

```powershell
git add app/Support/FriendlyErrorPage.php tests/Feature/FriendlyErrorPageDestinationTest.php
git commit -m "feat: resolve safe error page destinations"
```

---

### Task 2: Expired-Session and Intentional-Logout Login Notices

**Files:**

- Create: `tests/Feature/FriendlySessionErrorTest.php`
- Modify: `bootstrap/app.php`
- Modify: `resources/views/user/management/loginForm.blade.php`
- Modify: `app/Http/Controllers/Auth/AuthController.php`
- Modify: `tests/Feature/User/AuthenticateTest.php`

**Interfaces:**

- Consumes: `FriendlyErrorPage::SESSION_EXPIRED_MESSAGE` and `FriendlyErrorPage::LOGOUT_MESSAGE` from Task 1.
- Produces: session flash key `session_warning` for expired/unauthenticated web requests; keeps `success` for intentional logout.

- [ ] **Step 1: Write failing session-flow tests**

Create `tests/Feature/FriendlySessionErrorTest.php`:

```php
<?php

use App\Support\FriendlyErrorPage;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    Route::middleware(['web', 'auth'])->get('/__test/protected-page', fn () => 'protected');
    Route::middleware('web')->get('/__test/session-error/{status}', function (string $status) {
        abort((int) $status, 'internal session detail');
    });
});

test('an unauthenticated protected web request redirects to Login with a warning', function () {
    $this->get('/__test/protected-page')
        ->assertRedirect(route('login'))
        ->assertSessionHas('session_warning', FriendlyErrorPage::SESSION_EXPIRED_MESSAGE);
});

test('explicit web 401 and 419 responses redirect to Login with the same warning', function (int $status) {
    $this->get("/__test/session-error/{$status}")
        ->assertRedirect(route('login'))
        ->assertSessionHas('session_warning', FriendlyErrorPage::SESSION_EXPIRED_MESSAGE);
})->with([401, 419]);

test('the Login page renders the session warning once in an accessible region', function () {
    $html = $this->withSession([
        'session_warning' => FriendlyErrorPage::SESSION_EXPIRED_MESSAGE,
    ])->get('/login')->assertOk()->getContent();

    expect(substr_count($html, FriendlyErrorPage::SESSION_EXPIRED_MESSAGE))
        ->toBe(1)
        ->and($html)
        ->toContain('role="alert"')
        ->toContain('aria-live="assertive"');
});

test('JSON 401 and 419 responses are not redirected to Login', function (int $status) {
    $this->getJson("/__test/session-error/{$status}")
        ->assertStatus($status)
        ->assertHeader('content-type', 'application/json');
})->with([401, 419]);
```

In `tests/Feature/User/AuthenticateTest.php`, change the logout assertion to:

```php
$response->assertSessionHas('success', \App\Support\FriendlyErrorPage::LOGOUT_MESSAGE);
```

- [ ] **Step 2: Run the session tests and verify RED**

Run these commands sequentially:

```powershell
php -d memory_limit=512M vendor/bin/pest tests/Feature/FriendlySessionErrorTest.php
php -d memory_limit=512M vendor/bin/pest tests/Feature/User/AuthenticateTest.php --filter=logout
```

Expected: the new tests fail because exception responses do not carry `session_warning`, the Login view does not render it, and logout still uses the old copy.

- [ ] **Step 3: Register the minimal non-JSON session response transformation**

Add these imports to `bootstrap/app.php`:

```php
use App\Support\FriendlyErrorPage;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
```

Replace the empty `withExceptions` body with:

```php
->withExceptions(function (Exceptions $exceptions) {
    $exceptions->respond(function (
        Response $response,
        \Throwable $exception,
        Request $request
    ) {
        if ($request->expectsJson()) {
            return $response;
        }

        $status = $response->getStatusCode();

        if ($exception instanceof AuthenticationException || in_array($status, [401, 419], true)) {
            return redirect()
                ->route('login')
                ->with('session_warning', FriendlyErrorPage::SESSION_EXPIRED_MESSAGE);
        }

        return $response;
    });
})->create();
```

This is a final-response transformation, so Laravel reports unexpected exceptions before this callback runs. Do not add reporting suppression.

- [ ] **Step 4: Add separate accessible server-rendered notices to Login**

Near the existing `#error` styles in `resources/views/user/management/loginForm.blade.php`, add:

```css
.login-notice {
    margin-bottom: 1rem;
    padding: 0.75rem 0.9rem;
    border: 1px solid;
    border-radius: 10px;
    font-size: 0.82rem;
    line-height: 1.5;
}

.login-notice--warning {
    border-color: #fbbf24;
    background: #fffbeb;
    color: #92400e;
}

.login-notice--success {
    border-color: #86efac;
    background: #f0fdf4;
    color: #166534;
}
```

Immediately before the existing `<div id="error" class="hidden"></div>`, add:

```blade
@if (session('session_warning'))
    <div class="login-notice login-notice--warning" role="alert" aria-live="assertive">
        {{ session('session_warning') }}
    </div>
@endif

@if (session('success'))
    <div class="login-notice login-notice--success" role="status" aria-live="polite">
        {{ session('success') }}
    </div>
@endif
```

Keep `#error` separate for client-rendered credential errors; do not reuse its ID or let Login JavaScript overwrite either server notice.

- [ ] **Step 5: Change intentional logout to the approved success copy**

Import the support class in `app/Http/Controllers/Auth/AuthController.php`:

```php
use App\Support\FriendlyErrorPage;
```

Replace the logout return statement with:

```php
return redirect('/login')->with('success', FriendlyErrorPage::LOGOUT_MESSAGE);
```

- [ ] **Step 6: Run the focused tests and verify GREEN**

Run sequentially:

```powershell
php -d memory_limit=512M vendor/bin/pest tests/Feature/FriendlySessionErrorTest.php
php -d memory_limit=512M vendor/bin/pest tests/Feature/User/AuthenticateTest.php --filter=logout
php -d memory_limit=512M vendor/bin/pest tests/Feature/LoginFormTest.php
```

Expected: all focused tests PASS; the session warning occurs once, intentional logout uses distinct success copy, and JSON is not redirected.

- [ ] **Step 7: Commit the session flow**

```powershell
git add bootstrap/app.php app/Http/Controllers/Auth/AuthController.php resources/views/user/management/loginForm.blade.php tests/Feature/FriendlySessionErrorTest.php tests/Feature/User/AuthenticateTest.php
git commit -m "feat: explain expired sessions on Login"
```

---

### Task 3: Friendly 403, 404, 500, and 503 Pages

**Files:**

- Create: `resources/views/components/error-page.blade.php`
- Create: `resources/views/errors/403.blade.php`
- Create: `resources/views/errors/404.blade.php`
- Create: `resources/views/errors/500.blade.php`
- Create: `resources/views/errors/503.blade.php`
- Create: `tests/Feature/FriendlyErrorPagesTest.php`
- Modify: `bootstrap/app.php`

**Interfaces:**

- Consumes: `FriendlyErrorPage::destinationUrl(?User $user): string`.
- Produces: anonymous component props `title`, `message`, `primaryLabel`, `primaryUrl`, `secondaryLabel`, `secondaryUrl`, and `tone`; each status view consumes `homeUrl`, `loginUrl`, and/or `retryUrl` from the exception callback.

- [ ] **Step 1: Write failing HTML, redaction, action, and JSON tests**

Create `tests/Feature/FriendlyErrorPagesTest.php`:

```php
<?php

use App\Models\User;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    config(['app.debug' => false]);

    Route::middleware('web')->get('/__test/friendly-error/{status}', function (string $status) {
        abort((int) $status, 'SENSITIVE_EXCEPTION_DETAIL');
    });

    Route::middleware('web')->get('/__test/friendly-crash', function () {
        throw new RuntimeException('SENSITIVE_RUNTIME_DETAIL');
    });
});

test('friendly web errors retain status and show approved Thai copy', function (
    int $status,
    string $title
) {
    $this->get("/__test/friendly-error/{$status}")
        ->assertStatus($status)
        ->assertSeeText($title)
        ->assertDontSeeText('SENSITIVE_EXCEPTION_DETAIL')
        ->assertDontSee(base_path(), escape: false)
        ->assertDontSeeText("Error {$status}");
})->with([
    [403, 'ไม่สามารถเข้าถึงหน้านี้ได้'],
    [404, 'ไม่พบหน้าที่ต้องการ'],
    [500, 'ระบบขัดข้องชั่วคราว'],
    [503, 'ระบบยังไม่พร้อมใช้งาน'],
]);

test('an unexpected exception uses the friendly 500 page without leaking details', function () {
    $this->get('/__test/friendly-crash')
        ->assertInternalServerError()
        ->assertSeeText('ระบบขัดข้องชั่วคราว')
        ->assertDontSeeText('SENSITIVE_RUNTIME_DETAIL')
        ->assertDontSee(base_path(), escape: false);
});

test('an admin error page links to the admin dashboard', function () {
    Role::create(['name' => 'admin']);
    $user = User::factory()->create();
    $user->assignRole('admin');

    $this->actingAs($user)
        ->get('/__test/friendly-error/403')
        ->assertForbidden()
        ->assertSee(route('dashboard'), escape: false);
});

test('an anonymous error page safely links to Login', function () {
    $this->get('/__test/friendly-error/404')
        ->assertNotFound()
        ->assertSee(route('login'), escape: false);
});

test('server and maintenance pages retain a retry action for the current URL', function (int $status) {
    $url = url("/__test/friendly-error/{$status}");

    $this->get($url)
        ->assertStatus($status)
        ->assertSee($url, escape: false)
        ->assertSeeText('ลองอีกครั้ง');
})->with([500, 503]);

test('JSON errors retain JSON content type and are never converted to friendly HTML', function (int $status) {
    $this->getJson("/__test/friendly-error/{$status}")
        ->assertStatus($status)
        ->assertHeader('content-type', 'application/json')
        ->assertDontSee('resources/views/components/error-page.blade.php');
})->with([403, 404, 500, 503]);
```

- [ ] **Step 2: Run the focused test and verify RED**

Run:

```powershell
php -d memory_limit=512M vendor/bin/pest tests/Feature/FriendlyErrorPagesTest.php
```

Expected: FAIL because no friendly views or response transformation exist.

- [ ] **Step 3: Create the shared standalone error component**

Create `resources/views/components/error-page.blade.php` with no database access and no Vite dependency:

```blade
@props([
    'title',
    'message',
    'primaryLabel',
    'primaryUrl',
    'secondaryLabel' => null,
    'secondaryUrl' => null,
    'tone' => 'warning',
])

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>{{ $title }} · {{ config('app.name', 'ระบบประเมิน') }}</title>
    <link rel="icon" href="{{ asset('favicon-msu.png') }}">
    <style>
        :root {
            color-scheme: light;
            font-family: "Noto Sans Thai", "Leelawadee UI", Tahoma, sans-serif;
            color: #172033;
            background: #f5f7fb;
        }

        * { box-sizing: border-box; }

        body {
            min-height: 100vh;
            min-height: 100dvh;
            margin: 0;
            display: grid;
            place-items: center;
            padding: 24px;
            background:
                radial-gradient(circle at top, rgba(111, 45, 189, 0.11), transparent 36rem),
                #f5f7fb;
        }

        .error-card {
            width: min(100%, 560px);
            padding: clamp(28px, 6vw, 48px);
            border: 1px solid #e3e7ef;
            border-radius: 22px;
            background: #fff;
            box-shadow: 0 18px 48px rgba(23, 32, 51, 0.10);
            text-align: center;
        }

        .error-mark {
            width: 72px;
            height: 72px;
            margin: 0 auto 22px;
            display: grid;
            place-items: center;
            border-radius: 22px;
            background: #f3e8ff;
            color: #7e22ce;
            font-size: 2rem;
            font-weight: 800;
        }

        .error-mark[data-tone="danger"] {
            background: #fef2f2;
            color: #b91c1c;
        }

        h1 {
            margin: 0;
            font-size: clamp(1.55rem, 4vw, 2rem);
            line-height: 1.3;
        }

        p {
            margin: 14px auto 0;
            max-width: 44ch;
            color: #596579;
            line-height: 1.75;
        }

        .actions {
            margin-top: 30px;
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 12px;
        }

        .button {
            min-height: 46px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 11px 20px;
            border: 1px solid #7e22ce;
            border-radius: 12px;
            color: #fff;
            background: #7e22ce;
            font-weight: 700;
            text-decoration: none;
        }

        .button--secondary {
            border-color: #d7dce5;
            color: #354056;
            background: #fff;
        }

        .button:hover { filter: brightness(0.96); }

        .button:focus-visible {
            outline: 3px solid #fbbf24;
            outline-offset: 3px;
        }

        @media (max-width: 480px) {
            .actions, .button { width: 100%; }
        }
    </style>
</head>
<body>
    <main class="error-card" aria-labelledby="error-title">
        <div class="error-mark" data-tone="{{ $tone }}" aria-hidden="true">!</div>
        <h1 id="error-title">{{ $title }}</h1>
        <p>{{ $message }}</p>
        <nav class="actions" aria-label="ตัวเลือกดำเนินการ">
            <a class="button" href="{{ $primaryUrl }}">{{ $primaryLabel }}</a>
            @if ($secondaryLabel && $secondaryUrl)
                <a class="button button--secondary" href="{{ $secondaryUrl }}">
                    {{ $secondaryLabel }}
                </a>
            @endif
        </nav>
    </main>
</body>
</html>
```

- [ ] **Step 4: Create the four thin status views**

Create `resources/views/errors/403.blade.php`:

```blade
<x-error-page
    title="ไม่สามารถเข้าถึงหน้านี้ได้"
    message="บัญชีของคุณไม่มีสิทธิ์ดูหน้าที่ร้องขอ หากคิดว่าเป็นข้อผิดพลาด กรุณาติดต่อผู้ดูแลระบบ"
    primary-label="กลับหน้าหลัก"
    :primary-url="$homeUrl"
    secondary-label="ย้อนกลับ"
    :secondary-url="url()->previous()"
/>
```

Create `resources/views/errors/404.blade.php`:

```blade
<x-error-page
    title="ไม่พบหน้าที่ต้องการ"
    message="ลิงก์อาจไม่ถูกต้อง หมดอายุ ถูกย้าย หรือข้อมูลนี้ไม่มีอยู่แล้ว"
    primary-label="กลับหน้าหลัก"
    :primary-url="$homeUrl"
    secondary-label="ย้อนกลับ"
    :secondary-url="url()->previous()"
/>
```

Create `resources/views/errors/500.blade.php`:

```blade
<x-error-page
    title="ระบบขัดข้องชั่วคราว"
    message="ระบบยังดำเนินการคำขอนี้ไม่ได้ กรุณาลองอีกครั้ง หรือกลับไปหน้าหลัก"
    primary-label="ลองอีกครั้ง"
    :primary-url="$retryUrl"
    secondary-label="กลับหน้าหลัก"
    :secondary-url="$homeUrl"
    tone="danger"
/>
```

Create `resources/views/errors/503.blade.php`:

```blade
<x-error-page
    title="ระบบยังไม่พร้อมใช้งาน"
    message="ระบบอาจอยู่ระหว่างการบำรุงรักษาหรือไม่พร้อมใช้งานชั่วคราว กรุณาลองใหม่ภายหลัง"
    primary-label="ลองอีกครั้ง"
    :primary-url="$retryUrl"
    secondary-label="ไปหน้าเข้าสู่ระบบ"
    :secondary-url="$loginUrl"
/>
```

- [ ] **Step 5: Extend the final-response callback for friendly status views**

In `bootstrap/app.php`, after the `401`/`419` branch from Task 2 and before `return $response;`, add:

```php
if (! in_array($status, [403, 404, 500, 503], true)) {
    return $response;
}

try {
    return response()->view("errors.{$status}", [
        'homeUrl' => FriendlyErrorPage::destinationUrl($request->user()),
        'loginUrl' => route('login'),
        'retryUrl' => $request->fullUrl(),
    ], $status);
} catch (\Throwable) {
    return $response;
}
```

The completed callback must preserve this order:

1. Return the original response for `expectsJson()`.
2. Redirect Authentication exceptions and web `401`/`419`.
3. Return the original response for statuses outside `403`, `404`, `500`, `503`.
4. Attempt the friendly view and return Laravel's original response if rendering throws.

- [ ] **Step 6: Run focused tests and verify GREEN**

Run sequentially:

```powershell
php -d memory_limit=512M vendor/bin/pest tests/Feature/FriendlyErrorPagesTest.php
php -d memory_limit=512M vendor/bin/pest tests/Feature/FriendlySessionErrorTest.php
php -d memory_limit=512M vendor/bin/pest tests/Feature/FriendlyErrorPageDestinationTest.php
```

Expected: all focused tests PASS. HTML uses Thai guidance and correct statuses; sensitive exception detail is absent from HTML; JSON keeps its status and content type.

- [ ] **Step 7: Verify authentication and authorization regressions**

Run sequentially:

```powershell
php -d memory_limit=512M vendor/bin/pest tests/Feature/User/AuthenticateTest.php
php -d memory_limit=512M vendor/bin/pest tests/Feature/ReportExportAuthorizationTest.php
php -d memory_limit=512M vendor/bin/pest tests/Feature/LoginFormTest.php
```

Expected: all tests PASS.

- [ ] **Step 8: Verify formatting, production assets, and the full PHP suite**

Run sequentially:

```powershell
git diff --check
npm run build
php -d memory_limit=512M vendor/bin/pest
```

Expected:

- `git diff --check` reports no whitespace errors.
- Vite production build succeeds.
- No new PHP test failures are introduced. If the known `Tests\Feature\Evaluation\SupportScoreServiceTest` baseline still reports its three pre-existing required-evidence expectation failures, record them explicitly and confirm every other test passes; do not alter unrelated evaluation behavior under this feature.

- [ ] **Step 9: Commit the friendly status pages**

```powershell
git add bootstrap/app.php resources/views/components/error-page.blade.php resources/views/errors/403.blade.php resources/views/errors/404.blade.php resources/views/errors/500.blade.php resources/views/errors/503.blade.php tests/Feature/FriendlyErrorPagesTest.php
git commit -m "feat: add friendly web error pages"
```

- [ ] **Step 10: Inspect the final commit scope**

Run:

```powershell
git status --short
git log --oneline -4
```

Expected: the three feature commits are present, planned feature files are clean, and all unrelated pre-existing modified/untracked files remain untouched.
