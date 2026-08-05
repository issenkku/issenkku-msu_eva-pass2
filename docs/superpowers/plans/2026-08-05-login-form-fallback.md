# Login Form Fallback Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Prevent raw JSON from replacing the Login page when invalid credentials are submitted without working client-side JavaScript.

**Architecture:** Make the Laravel controller negotiate its failure response: JSON for AJAX clients and a redirect with session errors for normal forms. Keep the Blade form as the reliable baseline, then progressively enhance it with the browser-native `fetch` API so Login no longer depends on the Axios CDN.

**Tech Stack:** Laravel 11, Blade, Pest/PHPUnit, browser-native JavaScript

## Global Constraints

- Never flash or render the submitted password.
- Keep authentication failure messages generic so employee-ID existence is not disclosed.
- Preserve existing successful-login redirects, role routing, account-status policy, and rate limits.
- Preserve existing JSON payloads and status codes for AJAX clients.
- Do not add a new JavaScript dependency.

---

## File Structure

- `app/Http/Controllers/Auth/AuthController.php`: authenticate users and select JSON or redirect responses for Login failures.
- `resources/views/user/management/loginForm.blade.php`: render the baseline form, old employee ID, and accessible server-side errors without external Axios.
- `resources/views/user/management/partials/login-form-script.blade.php`: progressively enhance form submission with native `fetch`.
- `tests/Feature/User/AuthenticateTest.php`: integration coverage for plain-form and AJAX authentication responses.
- `tests/Feature/LoginFormTest.php`: rendered-page coverage for the self-contained Login form and accessible errors.

### Task 1: Negotiate authentication failure responses

**Files:**

- Modify: `tests/Feature/User/AuthenticateTest.php`
- Modify: `app/Http/Controllers/Auth/AuthController.php:25-76`

**Interfaces:**

- Consumes: `Illuminate\Http\Request::expectsJson()`, `Request::ajax()`, and Laravel redirect/error-session APIs.
- Produces: `authenticationFailure(Request $request, string $message, int $status): JsonResponse|RedirectResponse`.

- [ ] **Step 1: Add a failing regression test for an invalid plain-form submission**

Add this test after `test_user_can_login_with_valid_credentials_via_plain_form_post`:

```php
public function test_wrong_password_via_plain_form_returns_to_login_with_a_safe_error()
{
    $department = DepartmentFactory::new()->create();
    $position = PositionFactory::new()->create();
    User::factory()->create([
        'employee_id' => 'EMP001',
        'password' => bcrypt('password123'),
        'status' => 'active',
        'department_id' => $department->id,
        'position_id' => $position->id,
    ]);

    $response = $this->post('/login', $this->loginPayload([
        'password' => 'wrongpassword',
    ]));

    $response
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors([
            'employee_id' => 'กรุณากรอกหมายเลขประจำตัวและรหัสผ่านให้ถูกต้อง',
        ]);
    $response->assertSessionHasInput('employee_id', 'EMP001');
    $this->assertNull(session()->getOldInput('password'));
    $this->assertGuest();
}
```

- [ ] **Step 2: Run the regression test and verify RED**

Run:

```powershell
.\vendor\bin\pest.bat tests/Feature/User/AuthenticateTest.php --filter="wrong password via plain form"
```

Expected: FAIL because the response status is `401` JSON instead of a redirect to `/login`.

- [ ] **Step 3: Add plain-form coverage for inactive and rate-limited accounts**

Add two tests that use the same real request path:

```php
public function test_inactive_user_via_plain_form_returns_to_login_with_an_error()
{
    $department = DepartmentFactory::new()->create();
    $position = PositionFactory::new()->create();
    User::factory()->create([
        'employee_id' => 'EMP001',
        'password' => bcrypt('password123'),
        'status' => 'inactive',
        'department_id' => $department->id,
        'position_id' => $position->id,
    ]);

    $response = $this->post('/login', $this->loginPayload());

    $response
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors([
            'employee_id' => 'บัญชีของคุณถูกระงับการใช้งาน กรุณาติดต่อผู้ดูแลระบบ',
        ]);
    $this->assertGuest();
}

public function test_rate_limited_plain_form_returns_to_login_with_an_error()
{
    RateLimiter::clear('emp001|127.0.0.1');
    RateLimiter::hit('emp001|127.0.0.1', 60);
    RateLimiter::hit('emp001|127.0.0.1', 60);
    RateLimiter::hit('emp001|127.0.0.1', 60);
    RateLimiter::hit('emp001|127.0.0.1', 60);
    RateLimiter::hit('emp001|127.0.0.1', 60);

    $response = $this->post('/login', $this->loginPayload([
        'password' => 'wrongpassword',
    ]));

    $response
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors([
            'employee_id' => 'คุณพยายามเข้าสู่ระบบมากเกินไป กรุณารอ 1 นาทีแล้วลองใหม่อีกครั้ง.',
        ]);
    $this->assertGuest();
}
```

- [ ] **Step 4: Run all three plain-form failure tests and verify RED**

Run:

```powershell
.\vendor\bin\pest.bat tests/Feature/User/AuthenticateTest.php --filter="plain form"
```

Expected: the new failure-response tests FAIL on JSON status codes; the existing successful plain-form test remains PASS.

- [ ] **Step 5: Implement one response-negotiation boundary**

Import the response types:

```php
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
```

Add this private method to `AuthController`:

```php
private function authenticationFailure(
    Request $request,
    string $message,
    int $status,
): JsonResponse|RedirectResponse {
    if ($request->expectsJson() || $request->ajax()) {
        return response()->json(['message' => $message], $status);
    }

    return redirect()
        ->route('login')
        ->withErrors(['employee_id' => $message])
        ->withInput($request->only('employee_id'));
}
```

Replace each unconditional failure JSON response with the matching exact helper call:

```php
return $this->authenticationFailure(
    $request,
    'คุณพยายามเข้าสู่ระบบมากเกินไป กรุณารอ 1 นาทีแล้วลองใหม่อีกครั้ง.',
    429,
);

return $this->authenticationFailure(
    $request,
    'กรุณากรอกหมายเลขประจำตัวและรหัสผ่านให้ถูกต้อง',
    401,
);

return $this->authenticationFailure(
    $request,
    'บัญชีของคุณถูกระงับการใช้งาน กรุณาติดต่อผู้ดูแลระบบ',
    413,
);
```

- [ ] **Step 6: Run authentication tests and verify GREEN**

Run:

```powershell
.\vendor\bin\pest.bat tests/Feature/User/AuthenticateTest.php
```

Expected: all tests PASS, including the existing AJAX JSON assertions and the new plain-form redirects.

- [ ] **Step 7: Format and commit the backend slice**

Run:

```powershell
.\vendor\bin\pint.bat app/Http/Controllers/Auth/AuthController.php tests/Feature/User/AuthenticateTest.php
git add app/Http/Controllers/Auth/AuthController.php tests/Feature/User/AuthenticateTest.php
git commit -m "fix: return login errors to plain forms"
```

### Task 2: Make the Login form self-contained and progressively enhanced

**Files:**

- Modify: `tests/Feature/LoginFormTest.php`
- Modify: `resources/views/user/management/loginForm.blade.php:19,838-884`
- Modify: `resources/views/user/management/partials/login-form-script.blade.php`

**Interfaces:**

- Consumes: the controller's `errors.employee_id` session entry, `old('employee_id')`, normal form action/method, and AJAX `{message}` / `{redirect}` JSON payloads.
- Produces: a Login form that works without JavaScript and uses `window.fetch` only as an optional enhancement.

- [ ] **Step 1: Add a failing rendered-view test for the fallback contract**

Create the session error and old input, render the real Blade view, and assert the consumer-visible HTML:

```php
test('login form renders a server error and keeps only the employee id', function () {
    session()->flash('_old_input', ['employee_id' => 'EMP001']);
    session()->flash('errors', new \Illuminate\Support\ViewErrorBag([
        'default' => new \Illuminate\Support\MessageBag([
            'employee_id' => ['กรุณากรอกหมายเลขประจำตัวและรหัสผ่านให้ถูกต้อง'],
        ]),
    ]));

    $siteSetting = (object) [
        'faculty' => 'คณะทดสอบ',
        'university' => 'มหาวิทยาลัยทดสอบ',
        'logo_url' => asset('favicon-msu.png'),
        'background_url' => asset('images/workload-background.jpg'),
        'use_white_background' => false,
    ];

    $html = view('user.management.loginForm', compact('siteSetting'))->render();

    expect($html)
        ->toContain('role="alert"')
        ->toContain('aria-live="assertive"')
        ->toContain('กรุณากรอกหมายเลขประจำตัวและรหัสผ่านให้ถูกต้อง')
        ->toContain('value="EMP001"')
        ->not->toContain('value="wrongpassword"');
});
```

- [ ] **Step 2: Run the rendered-view test and verify RED**

Run:

```powershell
.\vendor\bin\pest.bat tests/Feature/LoginFormTest.php --filter="server error"
```

Expected: FAIL because the employee ID has no old-input value and the error div is empty/hidden.

- [ ] **Step 3: Render baseline errors and old employee ID in Blade**

Set the employee input value:

```blade
value="{{ old('employee_id') }}"
```

Replace the empty error element with:

```blade
<div
    id="error"
    class="{{ $errors->has('employee_id') ? '' : 'hidden' }}"
    role="alert"
    aria-live="assertive">
    {{ $errors->first('employee_id') }}
</div>
```

- [ ] **Step 4: Run the rendered-view test and verify GREEN**

Run:

```powershell
.\vendor\bin\pest.bat tests/Feature/LoginFormTest.php --filter="server error"
```

Expected: PASS.

- [ ] **Step 5: Add a failing test that guards against the external JavaScript dependency**

Extend `test_login_form_renders_a_data_hook_based_password_toggle` with the rendered-page contract:

```php
expect($html)
    ->not->toContain('cdn.jsdelivr.net/npm/axios')
    ->not->toContain('window.axios');
```

- [ ] **Step 6: Run the dependency test and verify RED**

Run:

```powershell
.\vendor\bin\pest.bat tests/Feature/LoginFormTest.php --filter="password toggle"
```

Expected: FAIL because the rendered Login page still references the Axios CDN and `window.axios`.

- [ ] **Step 7: Replace Axios with native progressive enhancement**

Remove this tag from `loginForm.blade.php`:

```html
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
```

Replace `login-form-script.blade.php` with a handler that is installed only when `window.fetch` exists:

```html
<script>
    const form = document.getElementById('loginForm');
    const errorDiv = document.getElementById('error');
    const fallbackMessage = 'เข้าสู่ระบบไม่สำเร็จ';

    if (form && window.fetch) {
        form.addEventListener('submit', async function (event) {
            event.preventDefault();
            errorDiv?.classList.add('hidden');

            let message = fallbackMessage;

            try {
                const response = await window.fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                const data = await response.json();

                if (!response.ok) {
                    message = data.message || fallbackMessage;
                    throw new Error(message);
                }

                if (typeof data.redirect !== 'string') {
                    throw new Error(fallbackMessage);
                }

                window.location.assign(data.redirect);
            } catch {
                if (errorDiv) {
                    errorDiv.textContent = message;
                    errorDiv.classList.remove('hidden');
                }
            }
        });
    }
</script>
```

- [ ] **Step 8: Run all Login form tests and verify GREEN**

Run:

```powershell
.\vendor\bin\pest.bat tests/Feature/LoginFormTest.php
```

Expected: all tests PASS.

- [ ] **Step 9: Format and commit the frontend slice**

Run:

```powershell
.\vendor\bin\pint.bat tests/Feature/LoginFormTest.php
git add resources/views/user/management/loginForm.blade.php resources/views/user/management/partials/login-form-script.blade.php tests/Feature/LoginFormTest.php
git commit -m "fix: make login form resilient without axios"
```

### Task 3: Verify the complete Login flow

**Files:**

- Verify: `app/Http/Controllers/Auth/AuthController.php`
- Verify: `resources/views/user/management/loginForm.blade.php`
- Verify: `resources/views/user/management/partials/login-form-script.blade.php`
- Verify: `tests/Feature/Auth/AuthenticationTest.php`
- Verify: `tests/Feature/User/AuthenticateTest.php`
- Verify: `tests/Feature/LoginFormTest.php`
- Verify: `tests/Feature/AuditLoggingTest.php`

**Interfaces:**

- Consumes: completed backend and frontend slices.
- Produces: fresh evidence that plain-form fallback, AJAX behavior, successful authentication, and audit logging remain compatible.

- [ ] **Step 1: Run the focused PHP regression suite**

Run:

```powershell
.\vendor\bin\pest.bat tests/Feature/Auth/AuthenticationTest.php tests/Feature/User/AuthenticateTest.php tests/Feature/LoginFormTest.php tests/Feature/AuditLoggingTest.php
```

Expected: all focused tests PASS with zero failures.

- [ ] **Step 2: Run formatting verification**

Run:

```powershell
.\vendor\bin\pint.bat --test app/Http/Controllers/Auth/AuthController.php tests/Feature/User/AuthenticateTest.php tests/Feature/LoginFormTest.php
git diff --check
```

Expected: both commands exit `0` with no PHP-formatting or whitespace errors.

- [ ] **Step 3: Inspect the final scoped diff**

Run:

```powershell
git diff --check HEAD~2..HEAD
git status --short
```

Expected: no whitespace errors; only pre-existing unrelated workspace changes remain uncommitted.
