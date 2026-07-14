# Evaluatee Navigation Label Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Replace the informal evaluatee navigation wording with `หน้าประเมินตนเอง` in both desktop and mobile menus.

**Architecture:** Keep the existing Blade layout structure, route, authorization condition, styling, and icons intact. Add a focused source-level view test to lock down both evaluatee links and prevent the informal wording from returning.

**Tech Stack:** Laravel 11, Blade, Pest 3, PHP 8.2+

## Global Constraints

- The visible label must be exactly `หน้าประเมินตนเอง`.
- Only the two active `/evaluatee-dashboard` navigation labels may change.
- The `$showEvaluateeNavigation` visibility condition must remain unchanged.
- Manager, evaluator, director, and administrator navigation labels must remain unchanged.
- The desktop route, mobile route, styling, and icons must remain unchanged.

---

### Task 1: Update the evaluatee navigation wording

**Files:**
- Modify: `tests/Feature/LayoutAppShellTest.php`
- Modify: `resources/views/layouts/app.blade.php:82`
- Modify: `resources/views/layouts/app.blade.php:211`

**Interfaces:**
- Consumes: The existing Blade links whose `href` is `/evaluatee-dashboard` and whose visibility is controlled by `$showEvaluateeNavigation`.
- Produces: Two active navigation links labeled `หน้าประเมินตนเอง`, one for desktop and one for mobile.

- [ ] **Step 1: Write the failing view-level test**

Append this test to `tests/Feature/LayoutAppShellTest.php`:

```php
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
```

- [ ] **Step 2: Run the focused test and verify that it fails**

Run:

```bash
php artisan test tests/Feature/LayoutAppShellTest.php
```

Expected: FAIL because zero links currently contain `หน้าประเมินตนเอง`; the existing layout contains `หน้าประเมินตัวเอง` twice.

- [ ] **Step 3: Make the minimal Blade copy change**

In the desktop evaluatee navigation block in `resources/views/layouts/app.blade.php`, use:

```blade
<a class="nav-link text-white" href="/evaluatee-dashboard">หน้าประเมินตนเอง</a>
```

In the mobile evaluatee navigation block, use:

```blade
<a href="/evaluatee-dashboard" class="mobile-nav-item">
    <i class="fas fa-user-check" style="width: 20px; margin-right: 10px;"></i>
    หน้าประเมินตนเอง
</a>
```

Do not change the surrounding `$showEvaluateeNavigation` conditions or any other navigation label.

- [ ] **Step 4: Run the focused test and verify that it passes**

Run:

```bash
php artisan test tests/Feature/LayoutAppShellTest.php
```

Expected: PASS for both tests in `LayoutAppShellTest.php`.

- [ ] **Step 5: Check formatting and the final diff**

Run:

```bash
git diff --check
git diff -- tests/Feature/LayoutAppShellTest.php resources/views/layouts/app.blade.php
```

Expected: `git diff --check` exits successfully, and the diff contains one new test plus exactly two visible-label replacements.

- [ ] **Step 6: Commit the tested change**

```bash
git add tests/Feature/LayoutAppShellTest.php resources/views/layouts/app.blade.php
git commit -m "fix: clarify evaluatee navigation label"
```
