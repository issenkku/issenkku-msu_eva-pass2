# Nullable Admin Support Weight Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Allow Admin to leave the criterion-level support weight empty when the evaluatee will provide each project weight.

**Architecture:** Make `support_criterias.weight` nullable and enforce the business invariant at the HTTP boundary: weight is `NULL` when `allow_evaluatee_weight` is true, otherwise it is required in `(0, 100]`. Keep create and edit JavaScript synchronized with the server by disabling and clearing the Admin input and serializing `null`.

**Tech Stack:** Laravel 12, PHP 8.2+, Eloquent, MySQL/SQLite migrations, Blade, browser JavaScript, Pest/PHPUnit

## Global Constraints

- A criterion with `allow_evaluatee_weight = true` stores its Admin weight as `NULL`.
- A criterion with `allow_evaluatee_weight = false` requires an Admin weight greater than `0` and no greater than `100`.
- Existing support activity entries, scores, evidence, and histories remain unchanged.
- Enabling evaluatee-owned weight continues to require `allow_activity_entries = true`.
- Evaluation-list score capping remains unchanged.
- Preserve unrelated dirty-worktree changes, especially the existing `HasRichText` import reorder.

---

### Task 1: Make criterion weight nullable with conditional server validation

**Files:**
- Create: `database/migrations/2026_07_25_000004_make_support_criteria_weight_nullable.php`
- Modify: `app/Http/Controllers/ReportStructureController.php:449,627,729,1088`
- Modify: `tests/Feature/Evaluation/SupportEvaluationSchemaTest.php`
- Modify: `tests/Feature/Report/SupportCriteriaTemplateTest.php`

**Interfaces:**
- Consumes: `allow_evaluatee_weight: bool` and `weight: float|null` in each support criterion payload.
- Produces: `support_criterias.weight = NULL` in evaluatee-owned mode; otherwise a decimal value in `(0, 100]`.

- [ ] **Step 1: Add failing schema and HTTP behavior tests**

In `SupportEvaluationSchemaTest`, add a test that reads
`Schema::getColumns('support_criterias')`, locates `weight`, and asserts its
`nullable` metadata is true.

In `SupportCriteriaTemplateTest`, add:

```php
public function test_admin_can_create_evaluatee_weighted_criterion_without_admin_weight(): void
{
    $this->postJson(route('report-structure.store'), $this->payload([[
        'sequence' => 1,
        'activity_name' => '<p>งาน</p>',
        'indicator' => '<p>เกณฑ์</p>',
        'target_value' => 100,
        'weight' => null,
        'allow_activity_entries' => true,
        'allow_evaluatee_weight' => true,
    ]]))->assertCreated();

    $this->assertDatabaseHas('support_criterias', [
        'allow_evaluatee_weight' => true,
        'weight' => null,
    ]);
}
```

Extend
`test_admin_can_change_evaluatee_owned_field_mode_after_report_data_exists`
so its update payload sends `'weight' => null`, asserts the criterion weight is
`null`, and keeps the exact existing activity-entry preservation assertion.

Add a dataset-backed test that sends `null`, `0`, `-1`, and `101` while
`allow_evaluatee_weight` is false and asserts the support criterion `weight`
validation key is returned.

- [ ] **Step 2: Run the focused tests and verify RED**

Run:

```powershell
php -d memory_limit=512M vendor\bin\pest tests\Feature\Evaluation\SupportEvaluationSchemaTest.php tests\Feature\Report\SupportCriteriaTemplateTest.php
```

Expected: failures because the schema is non-nullable and controller validation
always requires `weight`.

- [ ] **Step 3: Add the nullable migration**

Create a migration whose `up()` changes the existing column without changing
its precision:

```php
Schema::table('support_criterias', function (Blueprint $table): void {
    $table->decimal('weight', 5, 2)->nullable()->change();
});
```

For rollback, first update `NULL` rows to `0` so the non-nullable schema change
cannot fail, then restore the original column:

```php
DB::table('support_criterias')->whereNull('weight')->update(['weight' => 0]);
Schema::table('support_criterias', function (Blueprint $table): void {
    $table->decimal('weight', 5, 2)->nullable(false)->change();
});
```

- [ ] **Step 4: Make store and update validation conditional**

Replace both unconditional support-weight rules with:

```php
'categories.*.evaluation_lists.*.support_criterias.*.weight' => [
    'exclude_if:categories.*.evaluation_lists.*.support_criterias.*.allow_evaluatee_weight,true',
    'required',
    'numeric',
    'gt:0',
    'max:100',
],
```

This follows the wildcard-dependent rule pattern already used by the report
structure validator: evaluatee-owned mode excludes the Admin weight before
`required` is evaluated, while Admin-owned mode runs every numeric rule.

- [ ] **Step 5: Normalize persistence to `NULL`**

In both create and update support-criterion persistence arrays, replace the
direct weight assignment with:

```php
'weight' => (bool) ($supportData['allow_evaluatee_weight'] ?? false)
    ? null
    : $supportData['weight'],
```

This ensures a stale or malicious Admin weight is discarded whenever
evaluatee-owned weight is enabled.

- [ ] **Step 6: Run Task 1 tests and commit**

Run:

```powershell
php -d memory_limit=512M vendor\bin\pest tests\Feature\Evaluation\SupportEvaluationSchemaTest.php tests\Feature\Report\SupportCriteriaTemplateTest.php
vendor\bin\pint --test database\migrations\2026_07_25_000004_make_support_criteria_weight_nullable.php tests\Feature\Evaluation\SupportEvaluationSchemaTest.php tests\Feature\Report\SupportCriteriaTemplateTest.php
php -l app\Http\Controllers\ReportStructureController.php
git diff --check
```

Expected: all focused tests pass, new/modified PHP files pass formatting, the
controller has valid syntax, and no whitespace errors exist.

Stage the migration and tests normally. For
`ReportStructureController.php`, stage only Task 1 hunks and preserve the
unrelated import reorder. Commit without a pathspec after inspecting the index:

```powershell
git diff --cached
git commit -m "feat: allow nullable admin support weight"
```

---

### Task 2: Synchronize create and edit Admin UI behavior

**Files:**
- Modify: `resources/views/criteria_config/partials/support-criteria-template.blade.php`
- Modify: `resources/views/criteria_config/partials/create-script.blade.php`
- Modify: `resources/views/criteria_config/partials/script-edit-support-handlers.blade.php`
- Modify: `resources/views/criteria_config/partials/script-edit-collect-form-data.blade.php`
- Modify: `tests/Feature/SupportCriteriaTemplateViewTest.php`

**Interfaces:**
- Consumes: `.support_allow_evaluatee_weight` checkbox state.
- Produces: disabled/empty `.support_weight`, hidden required marker, and `weight: null` when checked; enabled/required numeric input when unchecked.

- [ ] **Step 1: Add a failing create/edit view contract test**

Extend `support template exposes and serializes evaluatee owned field controls`
to require:

```php
expect($html)->toContain('data-support-weight-required');
expect($createScript)
    ->toContain("const weightInput = block.querySelector('.support_weight')")
    ->toContain('weightInput.disabled = evaluateeOwnsWeight')
    ->toContain("if (evaluateeOwnsWeight) weightInput.value = ''")
    ->toContain('weight: evaluateeOwnsWeight ? null : Number(weight)');
expect($editSupportHandler.$editCollector)
    ->toContain("const weightInput = block.querySelector('.support_weight')")
    ->toContain('weightInput.disabled = evaluateeOwnsWeight')
    ->toContain('weight: evaluateeOwnsWeight ? null : Number(weight)');
```

Also require the create and edit collectors to check blank/range errors only
when `evaluateeOwnsWeight` is false.

- [ ] **Step 2: Run the view test and verify RED**

Run:

```powershell
php -d memory_limit=512M vendor\bin\pest tests\Feature\SupportCriteriaTemplateViewTest.php
```

Expected: failure because the weight input remains required and collectors
always reject an empty value.

- [ ] **Step 3: Mark and toggle the Admin weight field**

In the shared support template, add `data-support-weight-required` to the red
asterisk beside the Admin weight label.

In both `toggleSupportIndicatorMode()` implementations:

```javascript
const weightInput = block.querySelector('.support_weight');
const evaluateeOwnsWeight = Boolean(allowEvaluateeWeight?.checked);
if (weightInput) {
    if (evaluateeOwnsWeight) weightInput.value = '';
    weightInput.disabled = evaluateeOwnsWeight;
}
block.querySelector('[data-support-weight-required]')
    ?.classList.toggle('hidden', evaluateeOwnsWeight);
```

Keep the existing logic that clears and disables the evaluatee-owned checkbox
when activity entries are disabled.

- [ ] **Step 4: Make create and edit collection conditional**

In each collector, define:

```javascript
const evaluateeOwnsWeight =
    supportBlock.querySelector('.support_allow_evaluatee_weight')?.checked || false;
```

Change completeness validation to reject `weight === ''` only when
`!evaluateeOwnsWeight`. Change range validation to execute only when
`!evaluateeOwnsWeight`. Serialize:

```javascript
weight: evaluateeOwnsWeight ? null : Number(weight),
allow_evaluatee_weight: evaluateeOwnsWeight,
```

- [ ] **Step 5: Run UI and server regression verification**

Run:

```powershell
php -d memory_limit=512M vendor\bin\pest tests\Feature\SupportCriteriaTemplateViewTest.php tests\Feature\Report\SupportCriteriaTemplateTest.php
npm run test:js
npm run build
git diff --check
```

Expected: both PHP files pass, 19 JavaScript tests pass, the production build
succeeds, and there are no whitespace errors.

- [ ] **Step 6: Commit Task 2**

Inspect and stage only the five Task 2 files:

```powershell
git diff -- resources/views/criteria_config/partials/support-criteria-template.blade.php resources/views/criteria_config/partials/create-script.blade.php resources/views/criteria_config/partials/script-edit-support-handlers.blade.php resources/views/criteria_config/partials/script-edit-collect-form-data.blade.php tests/Feature/SupportCriteriaTemplateViewTest.php
git add -- resources/views/criteria_config/partials/support-criteria-template.blade.php resources/views/criteria_config/partials/create-script.blade.php resources/views/criteria_config/partials/script-edit-support-handlers.blade.php resources/views/criteria_config/partials/script-edit-collect-form-data.blade.php tests/Feature/SupportCriteriaTemplateViewTest.php
git commit -m "fix: make admin support weight conditional"
```
