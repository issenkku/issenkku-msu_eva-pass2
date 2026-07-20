# Support Score Confirmation Summary Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add a fourth support-score card and per-project support rows to the evaluatee confirmation modal while preserving the existing total score.

**Architecture:** Keep the existing Blade/JavaScript split. Blade renders support summary rows from each evaluation list's `support_items` and exposes support id/name/weight data attributes; the existing evaluatee summary script reads the live `[data-support-score]` inputs from the support editor store, computes weighted support scores, and updates only the new support summary elements. The existing quantity, quality, and total calculations remain unchanged.

**Tech Stack:** Laravel Blade, vanilla JavaScript, Pest feature/view contract tests, Tailwind utility classes.

## Global Constraints

- The support card is the fourth summary card and must not alter `modal-total-summary`.
- Support rows use `support_items` from `$categoryItems`; do not scrape names from rendered table HTML.
- Empty or non-numeric support score values display `ยังไม่มีข้อมูล` and `0.00`.
- Numeric support scores >= 0 display two decimal places and weighted score `weight * achieved / 100`.
- Evaluation lists without support criteria do not render a Support section.
- Do not modify unrelated dirty files in the working tree.

---

### Task 1: Add support summary markup and view contracts

**Files:**
- Modify: `resources/views/partials/evaluatee-confirmation-modal.blade.php:18-130`
- Create: `tests/Feature/EvaluateeConfirmationModalTest.php`

**Interfaces:**
- Consumes: `$categoryItems` evaluation lists with `support_items`; each item has `id`, `activity_name`, and `weight`.
- Produces: `#modal-support-summary`, `[data-summary-support-main]`, `#summary-support-score-{id}`, and `#summary-support-status-{id}` elements consumed by Task 2.

- [ ] **Step 1: Write the failing view contract tests**

```php
<?php

it('renders a support score card and support project rows in the confirmation modal', function () {
    $source = file_get_contents(resource_path('views/partials/evaluatee-confirmation-modal.blade.php'));

    expect($source)
        ->toContain('คะแนนสายสนับสนุน')
        ->toContain('id="modal-support-summary"')
        ->toContain('data-summary-support-main')
        ->toContain('data-support-id')
        ->toContain('summary-support-score-')
        ->toContain('summary-support-status-')
        ->toContain("support_items");
});

it('does not render a support section when an evaluation list has no support items', function () {
    $source = file_get_contents(resource_path('views/partials/evaluatee-confirmation-modal.blade.php'));

    expect($source)->toContain("count($evaluationList['support_items']) > 0");
});
```

- [ ] **Step 2: Run the new tests to verify they fail**

Run: `php vendor/bin/pest tests/Feature/EvaluateeConfirmationModalTest.php --compact`

Expected: FAIL because the modal does not contain the support card or support row data attributes.

- [ ] **Step 3: Add the fourth card and per-list Support section**

Add a fourth card beside the existing three cards:

```blade
<div class="rounded-lg border border-amber-100 bg-white p-3">
    <div class="text-sm text-gray-500">คะแนนสายสนับสนุน</div>
    <div id="modal-support-summary" class="mt-1 text-lg font-bold text-amber-900 sm:text-xl">0.00</div>
</div>
```

Inside each `data-summary-list` block, after the Quality section, add:

```blade
@if (count($evaluationList['support_items']) > 0)
    <div class="mt-3">
        <div class="mb-2 text-xs font-bold uppercase tracking-wide text-amber-700">Support</div>
        <div class="space-y-2">
            @foreach ($evaluationList['support_items'] as $supportItem)
                <div class="flex flex-col gap-2 rounded-lg border border-amber-100 bg-amber-50/60 px-3 py-2 sm:flex-row sm:items-center sm:justify-between"
                    data-summary-support-main
                    data-list-id="{{ $evaluationList['id'] }}"
                    data-support-id="{{ $supportItem['id'] }}"
                    data-support-weight="{{ $supportItem['weight'] ?? 0 }}">
                    <div class="min-w-0">
                        <div class="text-sm font-medium text-gray-800">{{ \App\Support\SafeHtml::plainText($supportItem['activity_name'] ?? '') }}</div>
                        <div id="summary-support-status-{{ $supportItem['id'] }}" class="mt-1 text-xs text-gray-500">ยังไม่มีข้อมูล</div>
                    </div>
                    <div class="text-sm font-semibold text-amber-800">
                        <span id="summary-support-score-{{ $supportItem['id'] }}">0.00</span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif
```

- [ ] **Step 4: Run the view tests to verify they pass**

Run: `php vendor/bin/pest tests/Feature/EvaluateeConfirmationModalTest.php --compact`

Expected: PASS with 2 tests.

- [ ] **Step 5: Commit the markup and view tests**

```bash
git add resources/views/partials/evaluatee-confirmation-modal.blade.php tests/Feature/EvaluateeConfirmationModalTest.php
git commit -m "feat: add support score confirmation summary"
```

### Task 2: Compute and update support summary values

**Files:**
- Modify: `resources/views/partials/evaluatee-evaluation-script.blade.php:130-245`
- Modify: `tests/Feature/EvaluateeConfirmationModalTest.php`

**Interfaces:**
- Consumes: Task 1's `data-summary-support-main`, `data-support-id`, and `data-support-weight` elements plus existing `[data-support-score]` inputs.
- Produces: `#modal-support-summary` and each support row's status/score text; leaves `#modal-total-summary` untouched.

- [ ] **Step 1: Extend the failing script contract test**

```php
it('updates support summary values without changing the existing total summary', function () {
    $source = file_get_contents(resource_path('views/partials/evaluatee-evaluation-script.blade.php'));

    expect($source)
        ->toContain('modal-support-summary')
        ->toContain('data-summary-support-main')
        ->toContain('data-support-weight')
        ->toContain('summary-support-status-')
        ->toContain('summary-support-score-')
        ->toContain('supportTotal')
        ->toContain('modal-total-summary');
});
```

- [ ] **Step 2: Run the script test to verify it fails**

Run: `php vendor/bin/pest tests/Feature/EvaluateeConfirmationModalTest.php --compact`

Expected: FAIL because the script has no support summary selectors or calculation.

- [ ] **Step 3: Implement the minimal support calculation in `updateSubmissionSummary`**

After the existing quantity/quality summary values are read, add:

```js
const modalSupport = document.getElementById('modal-support-summary');
let supportTotal = 0;

document.querySelectorAll('[data-summary-support-main]').forEach((row) => {
    const supportId = row.dataset.supportId;
    const input = document.querySelector(`[data-support-item][data-support-id="${supportId}"] [data-support-score]`);
    const rawValue = input?.value?.trim() || '';
    const achieved = rawValue !== '' && Number.isFinite(Number(rawValue)) && Number(rawValue) >= 0
        ? Number(rawValue)
        : null;
    const weight = Number(row.dataset.supportWeight || 0) || 0;
    const weighted = achieved === null ? 0 : (weight * achieved) / 100;
    const statusEl = document.getElementById(`summary-support-status-${supportId}`);
    const scoreEl = document.getElementById(`summary-support-score-${supportId}`);

    if (statusEl) {
        statusEl.textContent = achieved === null ? 'ยังไม่มีข้อมูล' : 'มีข้อมูลแล้ว';
        statusEl.className = achieved === null ? 'mt-1 text-xs text-gray-500' : 'mt-1 text-xs text-emerald-700';
    }
    if (scoreEl) scoreEl.textContent = weighted.toFixed(2);
    supportTotal += weighted;
});

if (modalSupport) modalSupport.textContent = supportTotal.toFixed(2);
```

Do not change the existing `modalTotal` assignment.

- [ ] **Step 4: Run the focused modal tests**

Run: `php vendor/bin/pest tests/Feature/EvaluateeConfirmationModalTest.php --compact`

Expected: PASS with all modal markup and script contract tests.

- [ ] **Step 5: Commit the script and test changes**

```bash
git add resources/views/partials/evaluatee-evaluation-script.blade.php tests/Feature/EvaluateeConfirmationModalTest.php
git commit -m "feat: calculate support score in confirmation modal"
```

### Task 3: Regression verification

**Files:**
- Test: `tests/Feature/EvaluateeConfirmationModalTest.php`
- Test: existing evaluatee/support view tests

- [ ] **Step 1: Run the combined focused regression suite**

Run:

```bash
php vendor/bin/pest tests/Feature/EvaluateeConfirmationModalTest.php tests/Feature/Evaluation/EvaluateeTest.php tests/Feature/SupportCriteriaEvaluationViewTest.php tests/Feature/SupportCriteriaRichTextEditorTest.php tests/Unit/Support/SafeHtmlTest.php --compact
```

Expected: all tests pass with zero failures.

- [ ] **Step 2: Run formatting and whitespace checks**

Run:

```bash
vendor/bin/pint --test tests/Feature/EvaluateeConfirmationModalTest.php
git diff --check
```

Expected: Pint reports `passed` and `git diff --check` is silent.

- [ ] **Step 3: Review the final diff and preserve unrelated changes**

Run: `git status --short; git diff --stat HEAD~2..HEAD`

Confirm only the two feature commits and intended files are part of this change; leave unrelated dirty files untouched.
