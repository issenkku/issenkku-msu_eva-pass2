# Modal-Only Evaluation Completeness Validation Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Let users save drafts and submit evaluations with incomplete data or missing administrator-required evidence while preserving validation inside the data-entry modal.

**Architecture:** Move completeness enforcement to the existing item-level modal validator by removing whole-form calls to support completeness validation. Remove server-side required-evidence completeness gates while retaining request shape, URL, authorization, workflow, and criterion-membership validation.

**Tech Stack:** Laravel 11, PHP 8.2, Blade, vanilla JavaScript, Pest 3

## Global Constraints

- Both draft and final submission accept missing evaluation values and missing administrator-required evidence.
- Modal Save continues to reject incomplete active-item data, missing required evidence, invalid supplied values, and missing required change reasons.
- Existing confirmation dialogs, workflow permissions, score calculations, and modal snapshot restoration remain unchanged.
- Preserve unrelated changes already present in the working tree.

---

### Task 1: Isolate client-side completeness validation to the modal

**Files:**
- Modify: `tests/Feature/SupportCriteriaEvaluationViewTest.php`
- Modify: `tests/Feature/EvaluationControlsTest.php`
- Modify: `resources/views/partials/evaluation-form-script.blade.php`
- Modify: `resources/views/partials/evaluatee-evaluation-script.blade.php`
- Modify: `resources/views/components/support-criteria-table-script.blade.php`

**Interfaces:**
- Consumes: `validateSupportItem(item): { errors: string[], firstInvalid: HTMLElement|null }`
- Produces: modal Save as the only caller of `validateSupportItem`; outer form scripts no longer call `window.validateSupportCriteria()`

- [ ] **Step 1: Write failing view-contract tests**

Add assertions proving both outer form scripts omit
`window.validateSupportCriteria?.()` and the evaluatee script omits the
whole-form `data-require-evidence="1"` completeness loop. Keep an assertion
that the support modal Save handler contains:

```js
const result = validateSupportItem(activeItem);
renderModalErrors(result.errors);
if (result.errors.length > 0) {
    result.firstInvalid?.focus();
    return;
}
```

- [ ] **Step 2: Run the focused tests and verify RED**

Run:

```powershell
php artisan test tests/Feature/SupportCriteriaEvaluationViewTest.php tests/Feature/EvaluationControlsTest.php
```

Expected: FAIL because outer scripts still call the whole-form validator and
the evaluatee script still contains the required-evidence loop.

- [ ] **Step 3: Implement the minimal client-side boundary**

Remove `window.validateSupportCriteria?.()` from the submit and confirmation
handlers in both outer form scripts. Remove the evaluatee whole-form
required-evidence block. Delete the now-unused whole-form
`window.validateSupportCriteria` function while retaining
`validateSupportItem` and its modal Save caller.

- [ ] **Step 4: Run focused tests and verify GREEN**

Run:

```powershell
php artisan test tests/Feature/SupportCriteriaEvaluationViewTest.php tests/Feature/EvaluationControlsTest.php
```

Expected: PASS with zero failures.

- [ ] **Step 5: Commit the client-side boundary**

```powershell
git add tests/Feature/SupportCriteriaEvaluationViewTest.php tests/Feature/EvaluationControlsTest.php resources/views/partials/evaluation-form-script.blade.php resources/views/partials/evaluatee-evaluation-script.blade.php resources/views/components/support-criteria-table-script.blade.php
git commit -m "fix: limit evaluation completeness validation to modal"
```

### Task 2: Allow missing required evidence in draft and final requests

**Files:**
- Modify: `tests/Feature/Evaluation/EvaluateeTest.php`
- Modify: `app/Services/SupportScoreService.php`
- Modify: `app/Http/Controllers/Evaluatee/EvaluationScoreController.php`

**Interfaces:**
- Consumes: existing `SupportScoreService::persist(Reports, array, ?User, ?string, bool): array`
- Produces: the same service interface without a global configured-evidence completeness gate

- [ ] **Step 1: Replace the obsolete rejection test with acceptance tests**

Create separate tests for Draft and Pending. Each test configures
`require_evidence = true`, submits a support activity without evidence, asserts
redirect success, and asserts the report status advances to the requested
workflow state. Add a Pending test with a selected quality score whose main
criterion requires evidence but whose evidence list is empty.

- [ ] **Step 2: Run the focused tests and verify RED**

Run:

```powershell
php artisan test tests/Feature/Evaluation/EvaluateeTest.php
```

Expected: the new acceptance tests FAIL with validation errors for
`support_list.*.activity_entries` or `evidence_list`.

- [ ] **Step 3: Remove only server completeness gates**

In `SupportScoreService::normalizeAndValidateItems`, remove the loop over
`$allowedCriteria->where('require_evidence', true)`. Retain normalization,
criterion allow-list checks, URL rules, numeric rules, and change-reason rules.

In `EvaluationScoreController::storeEvaluationScores`, remove the Pending-only
quality required-evidence query and exception. Retain validation of every
supplied evidence item and all report workflow checks.

- [ ] **Step 4: Run focused tests and verify GREEN**

Run:

```powershell
php artisan test tests/Feature/Evaluation/EvaluateeTest.php
```

Expected: PASS with zero failures.

- [ ] **Step 5: Run regression verification**

Run:

```powershell
php artisan test tests/Feature/SupportCriteriaEvaluationViewTest.php tests/Feature/EvaluationControlsTest.php tests/Feature/Evaluation/EvaluateeTest.php tests/Feature/Evaluation/EvaluatorTest.php tests/Feature/Evaluation/DirectorTest.php tests/Feature/Evaluation/ManagerTest.php
npm run test:js
```

Expected: all selected PHP and JavaScript tests pass with zero failures.

- [ ] **Step 6: Check formatting and diff**

Run:

```powershell
vendor/bin/pint --test app/Services/SupportScoreService.php app/Http/Controllers/Evaluatee/EvaluationScoreController.php tests/Feature/Evaluation/EvaluateeTest.php tests/Feature/SupportCriteriaEvaluationViewTest.php tests/Feature/EvaluationControlsTest.php
git diff --check
git diff --stat
```

Expected: formatting and whitespace checks pass; diff contains only the
approved validation-boundary changes.

- [ ] **Step 7: Commit the server behavior**

```powershell
git add tests/Feature/Evaluation/EvaluateeTest.php app/Services/SupportScoreService.php app/Http/Controllers/Evaluatee/EvaluationScoreController.php
git commit -m "fix: allow incomplete evaluation submission"
```
