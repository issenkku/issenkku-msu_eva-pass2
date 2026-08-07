# Responsive People List Modals Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Keep reviewer and evaluatee modal controls visible while dynamic people lists scroll inside the available viewport.

**Architecture:** Preserve the five custom Tailwind modal identities and JavaScript selectors. Apply one flex-column layout contract to the four reviewer modals, then replace the assignment modal's fixed top offset with a centered viewport-constrained panel whose header and search stay outside the scrolling list.

**Tech Stack:** Laravel Blade, Tailwind CSS utilities, Pest, Node test runner, Vite.

## Global Constraints

- Preserve every existing modal ID, body ID, data hook, list renderer, search behavior, loading state, and open/close script.
- Reviewer and evaluatee panels use `max-h-[calc(100dvh-2rem)]` with a one-rem viewport inset.
- Headers and the evaluatee search control never shrink or scroll away.
- Only dynamic list bodies own vertical scrolling and contained overscroll.
- Do not consolidate the four reviewer modals into a shared component.

---

### Task 1: Constrain the four reviewer list modals

**Files:**
- Modify: `tests/Feature/EvaluationControlsTest.php`
- Modify: `resources/views/dashboard/partials/index-reviewer-modal.blade.php`
- Modify: `resources/views/components/evaluation-summary-reviewer-modal.blade.php`
- Modify: `resources/views/components/director-table.blade.php`
- Modify: `resources/views/components/manager-table.blade.php`

**Interfaces:**
- Consumes: `reviewerModal`, `evaluateeReviewerModal`, `directorReviewerModal`, `managerReviewerModal` and their existing body/close selectors.
- Produces: shared `data-reviewer-list-modal-panel`, `data-reviewer-list-modal-header`, and `data-reviewer-list-modal-body` layout hooks without changing IDs.

- [ ] **Step 1: Write the failing reviewer modal contract test**

Append this test to `tests/Feature/EvaluationControlsTest.php`:

```php
test('reviewer list modals keep headers visible while their lists scroll', function () {
    $modalHtml = [
        view('dashboard.partials.index-reviewer-modal')->render(),
        view('components.evaluation-summary-reviewer-modal')->render(),
        view('components.director-table', [
            'evaluations' => evaluationPaginator([evaluationAssignment()], '/director'),
            'statusCounts' => [],
            'years' => collect([2025]),
        ])->render(),
        view('components.manager-table', [
            'evaluations' => evaluationPaginator([evaluationAssignment()], '/manager'),
            'statusCounts' => [],
            'years' => collect([2025]),
        ])->render(),
    ];

    foreach ($modalHtml as $html) {
        expect($html)
            ->toContain('data-reviewer-list-modal-panel')
            ->toContain('data-reviewer-list-modal-header')
            ->toContain('data-reviewer-list-modal-body')
            ->toContain('max-h-[calc(100dvh-2rem)]')
            ->toContain('flex-none')
            ->toContain('min-h-0 flex-1')
            ->toContain('overflow-y-auto overscroll-contain');
    }
});
```

The test catches any reviewer modal that loses viewport containment, a fixed header, or list-only scrolling.

- [ ] **Step 2: Run the reviewer test to verify RED**

```powershell
$env:DB_DATABASE=':memory:'
php vendor/bin/pest tests/Feature/EvaluationControlsTest.php --filter="reviewer list modals keep"
```

Expected: FAIL because the shared layout hooks and viewport classes are absent.

- [ ] **Step 3: Apply the shared reviewer modal layout contract**

In each of the four Blade files, preserve all current IDs and apply these changes to the matching outer wrapper, centering wrapper, panel, header, and body:

```blade
<div class="hidden fixed inset-0 z-50 bg-slate-900/50 p-4">
    <div class="flex h-full min-h-0 items-center justify-center">
        <div data-reviewer-list-modal-panel
            class="flex max-h-[calc(100dvh-2rem)] w-full max-w-lg flex-col overflow-hidden rounded-2xl bg-white shadow-xl">
            <div data-reviewer-list-modal-header
                class="flex flex-none items-center justify-between border-b border-slate-200 px-5 py-4">
                ...existing title and close button...
            </div>
            <div data-reviewer-list-modal-body
                id="EXISTING_BODY_ID"
                class="min-h-0 flex-1 space-y-3 overflow-y-auto overscroll-contain px-5 py-5"></div>
        </div>
    </div>
</div>
```

Use each file's existing outer and body IDs exactly; do not change close-button IDs or data attributes.

- [ ] **Step 4: Run the reviewer test to verify GREEN**

```powershell
$env:DB_DATABASE=':memory:'
php vendor/bin/pest tests/Feature/EvaluationControlsTest.php --filter="reviewer list modals keep"
```

Expected: PASS.

- [ ] **Step 5: Commit Task 1**

```powershell
git add -- tests/Feature/EvaluationControlsTest.php resources/views/dashboard/partials/index-reviewer-modal.blade.php resources/views/components/evaluation-summary-reviewer-modal.blade.php resources/views/components/director-table.blade.php resources/views/components/manager-table.blade.php
git commit -m "fix: constrain reviewer list modals"
```

---

### Task 2: Center and constrain the assignment evaluatee list modal

**Files:**
- Modify: `tests/Feature/EvaluationControlsTest.php`
- Modify: `resources/views/assignment-data/partials/index-evaluatees-modal.blade.php`

**Interfaces:**
- Consumes: `#evaluateesModal`, `#evaluateesSearch`, `#evaluateesContent`, and `[data-modal-close]`.
- Produces: `data-evaluatees-modal-panel` and `data-evaluatees-modal-controls` layout hooks without changing the search or list interfaces.

- [ ] **Step 1: Write the failing evaluatee modal contract test**

Append this test to `tests/Feature/EvaluationControlsTest.php`:

```php
test('assignment evaluatee modal keeps its title and search above the scrolling list', function () {
    $html = view('assignment-data.partials.index-evaluatees-modal')->render();

    expect($html)
        ->toContain('data-evaluatees-modal-panel')
        ->toContain('data-evaluatees-modal-controls')
        ->toContain('max-h-[calc(100dvh-2rem)]')
        ->toContain('min-h-0 flex-1 overflow-y-auto overscroll-contain')
        ->toContain('id="evaluateesSearch"')
        ->toContain('id="evaluateesContent"')
        ->not->toContain('top-20');
});
```

The test catches restoration of the fixed top offset, loss of fixed controls, or loss of list-only scrolling.

- [ ] **Step 2: Run the evaluatee test to verify RED**

```powershell
$env:DB_DATABASE=':memory:'
php vendor/bin/pest tests/Feature/EvaluationControlsTest.php --filter="assignment evaluatee modal keeps"
```

Expected: FAIL because the panel/control hooks are absent and `top-20` is still present.

- [ ] **Step 3: Restructure the assignment evaluatee modal**

In `resources/views/assignment-data/partials/index-evaluatees-modal.blade.php`:

- Change the outer overlay to:

```blade
<div id="evaluateesModal" class="hidden fixed inset-0 z-50 flex items-center justify-center overflow-hidden bg-gray-600 bg-opacity-50 p-4">
```

- Change the panel to:

```blade
<div data-evaluatees-modal-panel
    class="flex max-h-[calc(100dvh-2rem)] min-h-0 w-full flex-col overflow-hidden rounded-md border bg-white p-5 shadow-lg md:w-2/3 lg:w-1/2">
```

- Wrap the existing title row and search block together in:

```blade
<div data-evaluatees-modal-controls class="flex-none">
    ...existing title row and search block...
</div>
```

- Change `#evaluateesContent` to:

```blade
<div id="evaluateesContent" class="min-h-0 flex-1 overflow-y-auto overscroll-contain"></div>
```

- [ ] **Step 4: Run the evaluatee test to verify GREEN**

```powershell
$env:DB_DATABASE=':memory:'
php vendor/bin/pest tests/Feature/EvaluationControlsTest.php --filter="assignment evaluatee modal keeps"
```

Expected: PASS.

- [ ] **Step 5: Run regression tests**

```powershell
$env:DB_DATABASE=':memory:'
php vendor/bin/pest tests/Feature/EvaluationControlsTest.php tests/Feature/DashboardTest.php tests/Feature/Report/AssignmentDataTest.php
npm run test:js
```

Expected: all selected PHP tests and all JavaScript tests PASS.

- [ ] **Step 6: Build assets and validate the diff**

```powershell
npm run build
git diff --check -- tests/Feature/EvaluationControlsTest.php resources/views/dashboard/partials/index-reviewer-modal.blade.php resources/views/components/evaluation-summary-reviewer-modal.blade.php resources/views/components/director-table.blade.php resources/views/components/manager-table.blade.php resources/views/assignment-data/partials/index-evaluatees-modal.blade.php
```

Expected: build exits successfully and `git diff --check` prints no errors.

- [ ] **Step 7: Commit Task 2**

```powershell
git add -- tests/Feature/EvaluationControlsTest.php resources/views/assignment-data/partials/index-evaluatees-modal.blade.php
git commit -m "fix: constrain assignment evaluatee modal"
```
