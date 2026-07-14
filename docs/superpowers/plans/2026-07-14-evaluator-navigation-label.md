# Evaluator Navigation Label Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Rename the evaluator dashboard navigation to `หน้าประเมินผู้อื่น` in both desktop and mobile menus.

**Architecture:** Keep the existing role-based Blade structure intact and change only the visible evaluator labels. Extend the focused layout source test to prove the evaluator links changed while the administrator and evaluatee labels stayed unchanged.

**Tech Stack:** Laravel 11, Blade, Pest 3, PHP 8.2+

## Global Constraints

- The evaluator label must be exactly `หน้าประเมินผู้อื่น`.
- Both active `/evaluator-dashboard` links must use the new label.
- The existing `ผู้ประเมิน` role condition, route, icon, styling, and navigation order must remain unchanged.
- The administrator `/dashboard` link must remain `หน้าหลัก`.
- The evaluatee `/evaluatee-dashboard` link must remain `หน้าประเมินตนเอง`.
- Role names, role assignments, authorization, assignment checks, and routes must remain unchanged.

---

### Task 1: Rename the evaluator dashboard navigation

**Files:**
- Modify: `tests/Feature/LayoutAppShellTest.php`
- Modify: `resources/views/layouts/app.blade.php:67`
- Modify: `resources/views/layouts/app.blade.php:197`

**Interfaces:**
- Consumes: The existing desktop and mobile links whose `href` is `/evaluator-dashboard` and whose visibility is controlled by the `ผู้ประเมิน` role condition.
- Produces: Two evaluator navigation links labeled `หน้าประเมินผู้อื่น` without changing their destination or presentation structure.

- [ ] **Step 1: Write the failing view-level test**

Append this test to `tests/Feature/LayoutAppShellTest.php`:

```php
test('evaluator navigation distinguishes evaluation work from other dashboard links', function () {
    $layout = file_get_contents(resource_path('views/layouts/app.blade.php'));

    preg_match_all(
        '/<a\b[^>]*href="\/evaluator-dashboard"[^>]*>.*?หน้าประเมินผู้อื่น.*?<\/a>/su',
        $layout,
        $evaluatorLinks,
    );
    preg_match_all(
        '/<a\b[^>]*href="\/evaluator-dashboard"[^>]*>.*?หน้าหลัก.*?<\/a>/su',
        $layout,
        $oldEvaluatorLinks,
    );
    preg_match_all(
        '/<a\b[^>]*href="\/dashboard"[^>]*>.*?หน้าหลัก.*?<\/a>/su',
        $layout,
        $adminLinks,
    );
    preg_match_all(
        '/<a\b[^>]*href="\/evaluatee-dashboard"[^>]*>.*?หน้าประเมินตนเอง.*?<\/a>/su',
        $layout,
        $evaluateeLinks,
    );

    expect($evaluatorLinks[0])->toHaveCount(2)
        ->and($oldEvaluatorLinks[0])->toHaveCount(0)
        ->and($adminLinks[0])->toHaveCount(2)
        ->and($evaluateeLinks[0])->toHaveCount(2);
});
```

- [ ] **Step 2: Run the focused test and verify that it fails**

Run:

```bash
php vendor/bin/pest tests/Feature/LayoutAppShellTest.php
```

Expected: FAIL because the evaluator links still contain `หน้าหลัก`, so the new-label count is `0` instead of `2`.

- [ ] **Step 3: Make the minimal Blade copy change**

In the desktop evaluator navigation block in `resources/views/layouts/app.blade.php`, use:

```blade
<a class="nav-link text-white d-flex align-items-center gap-2" href="/evaluator-dashboard"><i class="fas fa-home"></i><span>หน้าประเมินผู้อื่น</span></a>
```

In the mobile evaluator navigation block, use:

```blade
<a href="/evaluator-dashboard" class="mobile-nav-item">
    <i class="fas fa-home" style="width: 20px; margin-right: 10px;"></i>
    หน้าประเมินผู้อื่น
</a>
```

Do not change the surrounding role conditions, icons, routes, or other navigation labels.

- [ ] **Step 4: Run the focused test and verify that it passes**

Run:

```bash
php vendor/bin/pest tests/Feature/LayoutAppShellTest.php
```

Expected: PASS for all three tests in `LayoutAppShellTest.php`.

- [ ] **Step 5: Run the full PHP test suite**

Run:

```bash
composer test
```

Expected: PASS with no failed tests.

- [ ] **Step 6: Check formatting and the final diff**

Run:

```bash
git diff --check
git diff -- tests/Feature/LayoutAppShellTest.php resources/views/layouts/app.blade.php
```

Expected: `git diff --check` exits successfully, and the diff contains one new focused test plus exactly two evaluator-label replacements.

- [ ] **Step 7: Commit the tested change**

```bash
git add tests/Feature/LayoutAppShellTest.php resources/views/layouts/app.blade.php
git commit -m "fix: clarify evaluator navigation label"
```
