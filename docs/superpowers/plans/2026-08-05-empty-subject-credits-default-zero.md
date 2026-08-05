# Empty Subject Credits Default to Zero Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Persist the integer `0` for every empty subject-credit input while retaining validation for negative and non-integer values.

**Architecture:** Normalize the four credit fields inside both Laravel form requests so JSON, native, create, and update paths share one server-side default. Align both existing modal validation scripts and the Blade inputs with that contract so empty fields no longer block submission and are converted to `0` before FormData is created.

**Tech Stack:** Laravel FormRequest, Blade, vanilla JavaScript, Pest/PHPUnit, Node test runner.

## Global Constraints

- Work on branch `Jui` and preserve unrelated dirty-worktree changes.
- Cover `credits`, `lecture_credits`, `lab_credits`, and `self_study_credits`.
- Empty, missing, and `null` credit values become integer `0`.
- Explicit zero remains valid; negative and non-integer values remain invalid.
- Apply the same behavior to subject management and evaluatee workload subject creation.
- Do not change the database schema or subject credit calculations.

---

### Task 1: Normalize subject credits in Laravel requests

**Files:**
- Modify: `app/Http/Requests/Workload/StoreSubjectRequest.php`
- Modify: `app/Http/Requests/Workload/UpdateSubjectRequest.php`
- Modify: `tests/Feature/AsyncMasterDataMutationTest.php`

**Interfaces:**
- Consumes request fields `credits`, `lecture_credits`, `lab_credits`, and `self_study_credits`.
- Produces validated integer values with empty or missing fields represented as `0`.

- [ ] **Step 1: Write failing feature tests**

Add create and update JSON tests that submit all four credit fields as empty strings or omit them and assert the persisted model contains zero for each field. Retain an assertion that `-1` receives status `422`.

- [ ] **Step 2: Run tests to verify RED**

Run: `php -d memory_limit=512M vendor/bin/pest tests/Feature/AsyncMasterDataMutationTest.php --compact`

Expected: empty/missing credit tests fail with validation status `422`.

- [ ] **Step 3: Implement request normalization**

In each request's `prepareForValidation()`, merge `0` for a credit field when its value is missing, `null`, or an empty string. Keep non-empty values unchanged so the existing `integer|min:0` rules validate them. Change create rules to `integer|min:0`; change update rules to `sometimes|integer|min:0` after normalization supplies the four fields.

- [ ] **Step 4: Run tests to verify GREEN**

Run: `php -d memory_limit=512M vendor/bin/pest tests/Feature/AsyncMasterDataMutationTest.php --compact`

Expected: all tests pass.

---

### Task 2: Align both subject modal validation paths

**Files:**
- Modify: `resources/views/components/subject-modal.blade.php`
- Modify: `resources/views/subjects/partials/index-script.blade.php`
- Modify: `resources/views/evaluatee/partials/workload-script-subject-form.blade.php`
- Modify: `tests/js/workload-subject-form.test.mjs`

**Interfaces:**
- Consumes the four subject modal credit input elements.
- Produces input values of string `'0'` for empty credit fields before async or native submission.

- [ ] **Step 1: Write a failing JavaScript test**

Add a test that initializes all four credit elements with empty values, invokes the evaluatee subject submit handler, and asserts each element contains `'0'` when the async request begins.

- [ ] **Step 2: Run the test to verify RED**

Run: `node --test tests/js/workload-subject-form.test.mjs`

Expected: FAIL because empty credit values currently stop validation.

- [ ] **Step 3: Implement minimal client behavior**

Remove `required` attributes and required asterisks from all four credit inputs. In each client validation path, normalize an empty credit input to `'0'` before numeric validation. Continue rejecting non-numeric and negative values.

- [ ] **Step 4: Run focused tests to verify GREEN**

Run: `node --test tests/js/workload-subject-form.test.mjs`

Run: `php -d memory_limit=512M vendor/bin/pest tests/Feature/AsyncMasterDataMutationTest.php tests/Feature/Evaluation/WorkloadPageAsyncMutationTest.php --compact`

Expected: all tests pass.

---

### Task 3: Regression verification and commit

**Files:**
- Modify only files required to resolve regressions introduced by Tasks 1-2.

**Interfaces:**
- Verifies the subject default-zero behavior and existing async no-refresh behavior.

- [ ] **Step 1: Run JavaScript regression tests**

Run: `npm run test:js`

Expected: all tests pass.

- [ ] **Step 2: Run focused PHP regression tests**

Run: `php -d memory_limit=512M vendor/bin/pest tests/Feature/AsyncMasterDataMutationTest.php tests/Feature/Evaluation/WorkloadPageAsyncMutationTest.php tests/Feature/Settings/UniversitySettingTest.php --compact`

Expected: all tests pass.

- [ ] **Step 3: Build and inspect the diff**

Run: `npm run build`

Run: `git diff --check`

Expected: both commands exit with status `0`.

- [ ] **Step 4: Commit only in-scope changes**

Stage the two form requests, subject modal/scripts, and their tests by literal path. Inspect `git diff --cached --check`, then commit with:

```bash
git commit -m "fix: default empty subject credits to zero"
```
