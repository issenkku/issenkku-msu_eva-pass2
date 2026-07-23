# Login Viewport Responsive Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Keep the complete login composition visible within common browser viewport heights without scrolling or removing content.

**Architecture:** Keep the existing single Blade view and visual identity. Introduce height-responsive CSS custom properties that fluidly compress spacing and component dimensions, constrain both desktop columns to the dynamic viewport, and add one short-height refinement breakpoint. Protect the behavior with a rendered-view contract test before changing production CSS.

**Tech Stack:** Laravel Blade, inline CSS, Pest feature tests, Vite

## Global Constraints

- Change only the presentation of `resources/views/user/management/loginForm.blade.php`.
- Preserve all current text, fields, actions, colors, typography, decorative elements, branding settings, and authentication behavior.
- Keep the desktop two-column layout and the existing single-column mobile layout.
- Do not hide login or left-panel content to make the page fit.
- Do not use whole-page `transform: scale()` or browser-specific `zoom`.
- Preserve practical input and button target sizes.

---

### Task 1: Protect the viewport-responsive login contract

**Files:**
- Modify: `tests/Feature/LoginFormTest.php`

**Interfaces:**
- Consumes: rendered Blade view `user.management.loginForm`
- Produces: regression coverage for dynamic viewport height, fluid sizing tokens, short-height refinement, and reduced motion

- [ ] **Step 1: Add the failing responsive-layout test**

Append this Pest test:

```php
test('login form adapts its complete layout to the viewport height', function () {
    $html = view('user.management.loginForm', [
        'siteSetting' => null,
    ])->render();

    expect($html)
        ->toContain('height: 100dvh')
        ->toContain('--login-page-padding-y: clamp(')
        ->toContain('--login-logo-size: clamp(')
        ->toContain('--login-card-padding: clamp(')
        ->toContain('@media (max-height: 760px)')
        ->toContain('@media (prefers-reduced-motion: reduce)')
        ->not->toContain('zoom:');
});
```

- [ ] **Step 2: Run the focused test and verify RED**

Run:

```powershell
vendor\bin\pest tests\Feature\LoginFormTest.php
```

Expected: FAIL because the login view does not yet contain `height: 100dvh` or the responsive tokens.

- [ ] **Step 3: Commit the failing regression test**

```powershell
git add -- tests/Feature/LoginFormTest.php
git commit -m "test: define responsive login viewport"
```

---

### Task 2: Make the login composition fluid by viewport height

**Files:**
- Modify: `resources/views/user/management/loginForm.blade.php:27-657`
- Test: `tests/Feature/LoginFormTest.php`

**Interfaces:**
- Consumes: CSS custom properties declared on `:root`
- Produces: a two-column viewport shell and fluid dimensions used by the existing login elements

- [ ] **Step 1: Add height-responsive design tokens**

Add the following properties inside the existing `:root` block:

```css
--login-page-padding-y: clamp(0.75rem, 3.5vh, 3rem);
--login-page-padding-x: clamp(1.25rem, 3vw, 3.5rem);
--login-section-gap: clamp(0.65rem, 2vh, 2rem);
--login-logo-size: clamp(56px, 8.5vh, 82px);
--login-logo-image-size: clamp(38px, 5.6vh, 54px);
--login-card-padding: clamp(1rem, 2.8vh, 2rem);
--login-field-gap: clamp(0.65rem, 1.6vh, 1.1rem);
--login-control-height: clamp(42px, 5.6vh, 46px);
--login-button-height: clamp(44px, 6vh, 50px);
--login-feature-height: clamp(82px, 12vh, 120px);
--login-feature-padding: clamp(0.65rem, 1.8vh, 1rem);
```

- [ ] **Step 2: Constrain the grid and both columns to the dynamic viewport**

Replace the body height and overflow declarations with:

```css
body {
    min-height: 0;
    height: 100vh;
    height: 100dvh;
    grid-template-rows: minmax(0, 1fr);
    overflow: hidden;
}
```

Apply the shared viewport constraints:

```css
.left,
.right {
    min-height: 0;
    height: 100%;
}

.left {
    padding: var(--login-page-padding-y) var(--login-page-padding-x);
}

.right {
    padding: var(--login-page-padding-y) clamp(1.25rem, 3vw, 3rem);
    overflow: hidden;
}
```

- [ ] **Step 3: Connect the existing components to the fluid tokens**

Update the current declarations without changing selectors or markup:

```css
.univ-badge,
.left-subtitle,
.feature-grid,
.logo-area {
    margin-bottom: var(--login-section-gap);
}

.feature-grid {
    gap: clamp(0.5rem, 1.4vh, 0.75rem);
}

.feature-card {
    min-height: var(--login-feature-height);
    padding: var(--login-feature-padding) clamp(0.75rem, 1.4vw, 1.1rem);
}

.stat-item {
    padding: clamp(0.55rem, 1.5vh, 1rem);
}

.logo-ring {
    width: var(--login-logo-size);
    height: var(--login-logo-size);
    margin-bottom: clamp(0.45rem, 1.4vh, 1rem);
}

.logo-ring img {
    width: var(--login-logo-image-size);
    height: var(--login-logo-image-size);
}

.form-card {
    padding: var(--login-card-padding);
}

.form-eyebrow,
.divider-line {
    margin-bottom: clamp(0.75rem, 2vh, 1.75rem);
}

.field {
    margin-bottom: var(--login-field-gap);
}

.input-box input {
    height: var(--login-control-height);
}

.form-meta {
    margin-bottom: clamp(0.75rem, 1.8vh, 1.5rem);
}

.btn-submit {
    height: var(--login-button-height);
}

.form-footer-note {
    margin-top: clamp(0.6rem, 1.8vh, 1.5rem);
}
```

- [ ] **Step 4: Add short-height and reduced-motion refinements**

Add these rules before the existing width breakpoint:

```css
@media (max-height: 760px) and (min-width: 881px) {
    :root {
        --login-page-padding-y: clamp(0.5rem, 1.8vh, 0.9rem);
        --login-section-gap: clamp(0.45rem, 1.2vh, 0.75rem);
        --login-card-padding: clamp(0.85rem, 2vh, 1.25rem);
        --login-feature-height: clamp(72px, 10.5vh, 88px);
        --login-feature-padding: clamp(0.45rem, 1.1vh, 0.7rem);
    }

    .left-desc {
        line-height: 1.55;
    }

    .feature-icon {
        width: 30px;
        height: 30px;
        margin-bottom: 0.3rem;
    }

    .feature-desc,
    .footer-text,
    .form-footer-note {
        line-height: 1.4;
    }
}

@media (prefers-reduced-motion: reduce) {
    *,
    *::before,
    *::after {
        scroll-behavior: auto !important;
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
    }
}
```

Keep the mobile breakpoint height-constrained:

```css
@media (max-width: 880px) {
    body {
        grid-template-columns: 1fr;
        overflow: hidden;
    }

    .right {
        min-height: 0;
        height: 100%;
        padding: var(--login-page-padding-y) 1.25rem;
    }
}
```

- [ ] **Step 5: Run the focused test and verify GREEN**

Run:

```powershell
vendor\bin\pest tests\Feature\LoginFormTest.php
```

Expected: PASS.

- [ ] **Step 6: Run authentication regression tests**

Run:

```powershell
vendor\bin\pest tests\Feature\Auth\AuthenticationTest.php tests\Feature\User\AuthenticateTest.php
```

Expected: all tests PASS.

- [ ] **Step 7: Commit the responsive implementation**

```powershell
git add -- resources/views/user/management/loginForm.blade.php
git commit -m "fix: fit login page to viewport height"
```

---

### Task 3: Verify frontend output and repository integrity

**Files:**
- Verify: `resources/views/user/management/loginForm.blade.php`
- Verify: `tests/Feature/LoginFormTest.php`

**Interfaces:**
- Consumes: completed responsive login implementation
- Produces: verification evidence for formatting, tests, build, and scoped git changes

- [ ] **Step 1: Capture the rendered login page at representative viewport sizes**

Run a local server and capture screenshots with Chrome:

```powershell
$loginViewportDir = Join-Path ([System.IO.Path]::GetTempPath()) 'msu-login-viewport'
New-Item -ItemType Directory -Force -Path $loginViewportDir | Out-Null
$loginServer = Start-Process php -ArgumentList @('artisan', 'serve', '--host=127.0.0.1', '--port=8010') -PassThru -WindowStyle Hidden
Start-Sleep -Seconds 2
$chrome = 'C:\Program Files\Google\Chrome\Application\chrome.exe'
& $chrome --headless --disable-gpu --hide-scrollbars --window-size=1366,768 "--screenshot=$loginViewportDir\login-1366x768.png" http://127.0.0.1:8010/login
& $chrome --headless --disable-gpu --hide-scrollbars --window-size=1920,1080 "--screenshot=$loginViewportDir\login-1920x1080.png" http://127.0.0.1:8010/login
& $chrome --headless --disable-gpu --hide-scrollbars --window-size=390,844 "--screenshot=$loginViewportDir\login-390x844.png" http://127.0.0.1:8010/login
Stop-Process -Id $loginServer.Id
```

Inspect all three PNG files with the image viewer.

Expected:

- desktop screenshots show both complete columns;
- the faculty logo, login card, submit button, and footer note are visible;
- the mobile screenshot shows the complete single-column login panel;
- no content is clipped at the bottom edge.

- [ ] **Step 2: Run PHP formatting check**

```powershell
vendor\bin\pint --test tests\Feature\LoginFormTest.php
```

Expected: PASS with no formatting changes required.

- [ ] **Step 3: Run the complete JavaScript suite**

```powershell
npm run test:js
```

Expected: all JavaScript tests PASS.

- [ ] **Step 4: Build production assets**

```powershell
npm run build
```

Expected: Vite exits with code 0.

- [ ] **Step 5: Check the scoped diff**

```powershell
git diff --check
git status --short
git log --oneline -4
```

Expected: no whitespace errors; only pre-existing unrelated workspace changes remain uncommitted; the responsive test and implementation commits appear in history.
