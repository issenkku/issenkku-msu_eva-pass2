# Responsive Workload Entry Modal Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Keep the workload entry modal header and action buttons visible at every viewport size while only its form body scrolls.

**Architecture:** Retain the existing Bootstrap modal and add scoped dialog/form classes that establish one viewport-constrained flex column. Bootstrap supplies centered, scrollable, and small-screen fullscreen behavior; scoped CSS makes the form body the only vertical scroll owner without changing workload data or JavaScript behavior.

**Tech Stack:** Laravel Blade, Bootstrap 5 modal utilities, scoped CSS, Pest, Node test runner, Vite.

## Global Constraints

- Update `#workloadAddModal` only.
- Preserve existing fields, validation, asynchronous submission, and modal lifecycle.
- Desktop and tablet retain a one-rem viewport margin.
- Screens up to `575.98px` use a fullscreen dialog with no outer margin.
- Header and footer remain visible; only `.workload-modal-body` scrolls.
- Do not create a global modal abstraction or change unrelated modals.

---

### Task 1: Make the workload entry modal viewport-safe

**Files:**
- Modify: `tests/Feature/EvaluationControlsTest.php`
- Modify: `resources/views/evaluatee/partials/workload-entry-modal.blade.php`
- Modify: `resources/views/evaluatee/partials/workload-styles.blade.php`

**Interfaces:**
- Consumes: Bootstrap modal classes and the existing `#workloadAddModal`, `.workload-modal-header`, `.workload-modal-body`, and `.workload-modal-footer` elements.
- Produces: `.workload-modal-dialog` and `.workload-modal-form` layout hooks; no request or JavaScript interface changes.

- [ ] **Step 1: Write the failing Blade contract test**

Append this test to `tests/Feature/EvaluationControlsTest.php`:

```php
test('workload entry modal keeps its actions visible within the viewport', function () {
    $modalHtml = view('evaluatee.partials.workload-entry-modal', [
        'readonly' => false,
        'reportId' => 10,
        'workloadModal' => [
            'requires_evidence' => false,
            'requires_subject' => false,
            'workload_item_options' => [],
            'subjects' => [],
            'forms' => [],
        ],
    ])->render();
    $stylesHtml = view('evaluatee.partials.workload-styles')->render();

    expect($modalHtml)
        ->toContain('modal-dialog-scrollable')
        ->toContain('modal-fullscreen-sm-down')
        ->toContain('workload-modal-dialog')
        ->toContain('workload-modal-form');

    expect($stylesHtml)
        ->toContain('#workloadAddModal .workload-modal-dialog')
        ->toContain('height: calc(100vh - 2rem)')
        ->toContain('height: calc(100dvh - 2rem)')
        ->toContain('#workloadAddModal .workload-modal-form')
        ->toContain('flex-shrink: 0')
        ->toContain('overflow-y: auto')
        ->toContain('overscroll-behavior: contain')
        ->toContain('@media (max-width: 575.98px)');
});
```

The production mutations this test catches are removal of the responsive dialog utilities, flex form hook, viewport constraint, fixed action regions, or body scroll ownership.

- [ ] **Step 2: Run the test to verify RED**

Run:

```powershell
$env:DB_DATABASE=':memory:'
php vendor/bin/pest tests/Feature/EvaluationControlsTest.php --filter="workload entry modal keeps"
```

Expected: FAIL because `modal-dialog-scrollable`, `modal-fullscreen-sm-down`, `workload-modal-dialog`, and `workload-modal-form` are absent.

- [ ] **Step 3: Add the responsive layout hooks**

In `resources/views/evaluatee/partials/workload-entry-modal.blade.php`, change the dialog and form openings to:

```blade
<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-fullscreen-sm-down modal-lg workload-modal-dialog">
    <div class="modal-content workload-modal-content">
        <form class="workload-modal-form" method="POST" id="workloadEntryForm" action="{{ route('evaluatee.workload-entries.store') }}" data-store-url="{{ route('evaluatee.workload-entries.store') }}" data-update-url="{{ route('evaluatee.workload-entries.update', '__id__') }}">
```

- [ ] **Step 4: Make the body the only scroll owner**

Add these scoped rules immediately before the existing `.workload-modal-content` rule in `resources/views/evaluatee/partials/workload-styles.blade.php`:

```css
    #workloadAddModal .workload-modal-dialog {
        height: calc(100vh - 2rem);
        height: calc(100dvh - 2rem);
        margin: 1rem auto !important;
    }

    #workloadAddModal .workload-modal-content {
        height: 100%;
        max-height: 100%;
    }

    #workloadAddModal .workload-modal-form {
        display: flex;
        flex-direction: column;
        height: 100%;
        min-height: 0;
    }

    #workloadAddModal .workload-modal-header,
    #workloadAddModal .workload-modal-footer {
        flex-shrink: 0;
    }

    #workloadAddModal .workload-modal-body {
        flex: 1 1 auto;
        min-height: 0;
        overflow-y: auto;
        overscroll-behavior: contain;
    }

    @media (max-width: 575.98px) {
        #workloadAddModal .workload-modal-dialog {
            height: 100vh;
            height: 100dvh;
            margin: 0 !important;
        }
    }
```

- [ ] **Step 5: Run the focused test to verify GREEN**

Run:

```powershell
$env:DB_DATABASE=':memory:'
php vendor/bin/pest tests/Feature/EvaluationControlsTest.php --filter="workload entry modal keeps"
```

Expected: PASS.

- [ ] **Step 6: Run workload regression tests**

Run:

```powershell
$env:DB_DATABASE=':memory:'
php vendor/bin/pest tests/Feature/EvaluationControlsTest.php tests/Feature/Evaluation/WorkloadPageAsyncMutationTest.php
npm run test:js
```

Expected: all selected PHP tests and all JavaScript tests PASS.

- [ ] **Step 7: Build assets and validate the diff**

Run:

```powershell
npm run build
git diff --check -- resources/views/evaluatee/partials/workload-entry-modal.blade.php resources/views/evaluatee/partials/workload-styles.blade.php tests/Feature/EvaluationControlsTest.php
```

Expected: build exits successfully and `git diff --check` prints no errors.

- [ ] **Step 8: Commit the implementation**

```powershell
git add -- resources/views/evaluatee/partials/workload-entry-modal.blade.php resources/views/evaluatee/partials/workload-styles.blade.php tests/Feature/EvaluationControlsTest.php
git commit -m "fix: keep workload modal actions visible"
```
