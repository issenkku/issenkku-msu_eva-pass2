# Evaluatee Dashboard Compact Layout Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Constrain the evaluatee dashboard to a centered 1280-pixel content width and remove forced vertical stretching from its status cards.

**Architecture:** Keep the existing Blade partial structure and data flow unchanged. Express the layout correction entirely through standard Tailwind utility classes, protected by a focused source-contract regression test.

**Tech Stack:** Laravel Blade, Tailwind CSS 4, Pest/PHPUnit, Vite

## Global Constraints

- Apply the change only to `/evaluatee-dashboard`.
- Use Tailwind's standard `max-w-7xl` width and retain `mx-auto`.
- Preserve the three-column desktop overview and its `2xl` breakpoint, using 300-pixel deadline columns so the status overview cannot overlap them.
- Preserve dashboard data, counts, filters, charts, actions, colors, typography, copy, animation, and components.
- Do not change manager, director, or evaluator dashboards.
- Do not refactor the global Tailwind, Bootstrap, or CDN setup.

---

## File Structure

- Create `tests/Feature/EvaluateeDashboardLayoutTest.php`: source-contract regression coverage for the evaluatee dashboard's width, card alignment, and content-driven heights.
- Modify `resources/views/evaluatee/dashboard.blade.php`: set the supported 1280-pixel wrapper width.
- Modify `resources/views/evaluatee/partials/unfinished-assignments.blade.php`: align the panel edges with the other top-level cards.
- Modify `resources/views/evaluatee/partials/overview-panel.blade.php`: top-align the overview columns and remove forced full-height sizing.

### Task 1: Compact Evaluatee Dashboard Layout

**Files:**

- Create: `tests/Feature/EvaluateeDashboardLayoutTest.php`
- Modify: `resources/views/evaluatee/dashboard.blade.php:8`
- Modify: `resources/views/evaluatee/partials/unfinished-assignments.blade.php:1`
- Modify: `resources/views/evaluatee/partials/overview-panel.blade.php:13-14,70,100`

**Interfaces:**

- Consumes: Existing Blade view composition and standard Tailwind utilities.
- Produces: A centered `max-w-7xl mx-auto` evaluatee dashboard whose top-level panels share edges and whose overview columns use content-driven height.

- [ ] **Step 1: Write the failing layout regression tests**

Create `tests/Feature/EvaluateeDashboardLayoutTest.php`:

```php
<?php

it('constrains the evaluatee dashboard to a centered standard width', function () {
    $dashboard = file_get_contents(resource_path('views/evaluatee/dashboard.blade.php'));

    expect($dashboard)
        ->toContain('class="max-w-7xl mx-auto space-y-6"')
        ->not->toContain('max-w-8xl');
});

it('aligns evaluatee dashboard cards without forced stretching', function () {
    $unfinishedAssignments = file_get_contents(
        resource_path('views/evaluatee/partials/unfinished-assignments.blade.php')
    );
    $overview = file_get_contents(
        resource_path('views/evaluatee/partials/overview-panel.blade.php')
    );

    expect($unfinishedAssignments)->not->toContain('mx-5');

    expect($overview)
        ->toContain('grid grid-cols-1 items-start gap-6 2xl:grid-cols-[minmax(0,1fr),300px,300px]')
        ->not->toContain('2xl:grid-cols-[minmax(0,1fr),340px,340px]')
        ->not->toContain('items-stretch')
        ->not->toContain('h-full');
});
```

- [ ] **Step 2: Run the focused test and verify RED**

Run:

```powershell
php artisan test tests/Feature/EvaluateeDashboardLayoutTest.php
```

Expected: two failing tests. The first reports that `max-w-7xl` is missing; the second reports that `mx-5`, `items-stretch`, or `h-full` is still present.

- [ ] **Step 3: Implement the minimal Blade utility changes**

In `resources/views/evaluatee/dashboard.blade.php`, change the wrapper to:

```blade
<div class="max-w-7xl mx-auto space-y-6">
```

In `resources/views/evaluatee/partials/unfinished-assignments.blade.php`, change the outer panel to:

```blade
<div class="rounded-2xl border px-10 pb-6 pt-6 shadow-md" style="background: linear-gradient(135deg, #f5f3ff 0%, #fff 50%, #fdf2f8 100%); border-color: #ede9fe;">
```

In `resources/views/evaluatee/partials/overview-panel.blade.php`, change the overview section and its three direct cards to:

```blade
<section class="mt-5 grid grid-cols-1 items-start gap-6 2xl:grid-cols-[minmax(0,1fr),300px,300px]">
    <div class="min-w-0 rounded-2xl border border-slate-200 bg-slate-50/70 p-5">
```

```blade
<div class="min-w-0 flex flex-col rounded-2xl border border-amber-200 bg-amber-50/70 p-6 shadow-sm">
```

```blade
<div class="min-w-0 flex flex-col rounded-2xl border border-rose-200 bg-rose-50/70 p-6 shadow-sm">
```

Do not change the nested chart grid, list markup, data attributes, or action links.

- [ ] **Step 4: Run the focused test and verify GREEN**

Run:

```powershell
php artisan test tests/Feature/EvaluateeDashboardLayoutTest.php
```

Expected: 2 tests pass with 0 failures.

- [ ] **Step 5: Run relevant evaluatee regression tests**

Run:

```powershell
php artisan test tests/Feature/Evaluation/EvaluateeTest.php tests/Feature/EvaluateeDashboardLayoutTest.php
```

Expected: all tests pass with 0 failures.

- [ ] **Step 6: Build the frontend assets**

Run:

```powershell
npm run build
```

Expected: Vite exits with code 0 and produces the production bundle without build errors.

- [ ] **Step 7: Inspect the scoped diff**

Run:

```powershell
git diff --check
git diff -- tests/Feature/EvaluateeDashboardLayoutTest.php resources/views/evaluatee/dashboard.blade.php resources/views/evaluatee/partials/unfinished-assignments.blade.php resources/views/evaluatee/partials/overview-panel.blade.php
```

Expected: no whitespace errors; the diff contains only the regression test and four Tailwind utility changes described above.

- [ ] **Step 8: Commit the implementation**

Run:

```powershell
git add tests/Feature/EvaluateeDashboardLayoutTest.php resources/views/evaluatee/dashboard.blade.php resources/views/evaluatee/partials/unfinished-assignments.blade.php resources/views/evaluatee/partials/overview-panel.blade.php
git commit -m "fix: compact evaluatee dashboard layout"
```

Expected: one commit containing only the focused test and evaluatee dashboard layout changes.
