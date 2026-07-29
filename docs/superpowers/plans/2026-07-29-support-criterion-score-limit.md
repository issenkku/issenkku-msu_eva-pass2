# Support Score Limit and Inline Feedback Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Enforce whole-number achieved scores from 1 through 5, capped by the criterion target, for both criterion-level and activity-entry score fields; preserve invalid input, show immediate inline feedback, and disable modal Save until all score fields are valid.

**Architecture:** Reuse the existing pure JavaScript score validator for both score-field types, while Blade supplies each input's target and accessible help/error elements. The modal script owns immediate visual state and Save-button eligibility, and retains full validation on Save as a fallback. Laravel validation independently enforces the same integer/range/target rules before calculation or persistence.

**Tech Stack:** Laravel 11, PHP 8.2, Blade, Pest/PHPUnit, JavaScript ES modules, Node.js test runner, Tailwind CSS

## Global Constraints

- Apply the score rule to both `support_list[*][achieved_score]` and `support_list[*][activity_entries][*][achieved_score]`.
- A non-empty achieved score must be a whole number from `1` through `5` and must not exceed the parent `support_criterias.target_value`.
- Criterion-level achieved score remains nullable; an activity-entry achieved score remains required when `allow_evaluatee_weight` is enabled.
- Preserve an invalid value such as `7`; do not clamp, clear, round, or otherwise rewrite it.
- Show an invalid score with a red border and an inline error immediately on `input`.
- Disable the modal Save button while any score in the active criterion is invalid; retain click-time and form-submit validation as fallbacks.
- Always display: `กรอกเฉพาะจำนวนเต็มตั้งแต่ 1–5 และต้องไม่เกินระดับค่าเป้าหมาย X`, where `X` is the parent criterion target.
- Keep activity weight validation unchanged: greater than `0`, no more than `100`, and no more than two decimal places.
- Keep weighted-score calculation unchanged: `(weight * achieved score) / 100`.
- Keep existing activity content, indicator, evidence, history, and modification-reason behavior unchanged.
- Do not add a data migration or silently alter existing persisted values.
- Run PHP test files sequentially because they share `database/testing.sqlite`.
- The full PHP suite has three pre-existing `SupportScoreServiceTest` failures concerning required evidence/omitted criteria; do not expand this change to fix them.

---

## File Structure

- `resources/js/support-score-calculator.js` — existing pure `isCriterionScoreValid(value, targetValue): boolean`, reused for both criterion and activity-entry scores.
- `tests/js/support-score-calculator.test.mjs` — pure rule coverage for empty, integer, global maximum, fractional, and target-limited values.
- `resources/views/components/support-activity-entry-editor.blade.php` — activity score constraints, target metadata, permanent guidance, and inline error element.
- `resources/views/components/support-criteria-table.blade.php` — criterion score inline error element and disabled-state contract for modal Save.
- `resources/views/components/support-criteria-table-script.blade.php` — immediate score-field feedback, Save eligibility, modal-open initialization, and click-time fallback validation.
- `tests/Feature/SupportCriteriaEvaluationViewTest.php` — rendered Blade and shared-script contracts for both score-field types.
- `app/Support/SupportScoreRules.php` — request-shape validation for activity scores as nullable integers from 1 through 5.
- `app/Services/SupportActivityEntryService.php` — required/prohibited handling, target-cap validation, integer normalization, and persistence.
- `tests/Feature/Evaluation/SupportActivityEntryServiceTest.php` — valid activity score persistence/calculation and rejection cases.
- `tests/Feature/Evaluation/SupportCriteriaReadModelTest.php` — update current valid activity-score fixtures and totals to the new scale.
- `tests/Feature/SupportCriteriaEvaluationViewTest.php` — update valid display fixtures from the old 0–100 scale.

---

### Task 1: Activity Score Server Validation

**Files:**

- Modify: `app/Support/SupportScoreRules.php`
- Modify: `app/Services/SupportActivityEntryService.php`
- Test: `tests/Feature/Evaluation/SupportActivityEntryServiceTest.php`

**Interfaces:**

- Consumes: `SupportCriteria $criterion`, including its authoritative `target_value`.
- Consumes: `support_list.{item}.activity_entries.{entry}.achieved_score`.
- Produces: an integer `achieved_score` and a weighted score calculated by `SupportWeightedScore::calculate(float $weight, int $achievedScore): float`.
- Throws: `ValidationException` keyed to `support_list.{item}.activity_entries.{entry}.achieved_score`.

- [ ] **Step 1: Replace old 0–100 happy-path fixtures and add failing boundary tests**

In `tests/Feature/Evaluation/SupportActivityEntryServiceTest.php`, change the happy-path activity score from `80` to `4` and expected persisted values from `80.00` / `32.00` to `4.00` / `1.60`.

Add a focused target-cap test:

```php
public function test_activity_score_must_be_a_whole_number_from_one_to_five_within_the_criterion_target(): void
{
    $this->criterion->update([
        'target_value' => 3.5,
        'weight' => null,
        'allow_evaluatee_indicator' => true,
        'allow_evaluatee_weight' => true,
    ]);

    foreach ([0, 3.5, 4, 6] as $score) {
        try {
            $this->persist([[
                'content' => '<p>โครงการ</p>',
                'indicator' => '<p>ตัวชี้วัด</p>',
                'weight' => 40,
                'achieved_score' => $score,
            ]]);
            $this->fail("Expected score {$score} validation failure");
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey(
                'support_list.0.activity_entries.0.achieved_score',
                $exception->errors()
            );
        }
    }

    $this->persist([[
        'content' => '<p>โครงการ</p>',
        'indicator' => '<p>ตัวชี้วัด</p>',
        'weight' => 40,
        'achieved_score' => 3,
    ]]);

    $this->assertDatabaseHas('support_activity_entries', [
        'support_criteria_id' => $this->criterion->id,
        'achieved_score' => '3.00',
        'weighted_score' => '1.20',
    ]);
}
```

Update the invalid-case table so activity scores cover `0`, `6`, and `3.5`, while weight cases continue using a valid score such as `4`.

- [ ] **Step 2: Run the focused test and verify RED**

Run:

```powershell
php -d memory_limit=512M vendor/bin/pest tests/Feature/Evaluation/SupportActivityEntryServiceTest.php
```

Expected: FAIL because activity scores still accept decimals and values through 100, and do not compare against the criterion target.

- [ ] **Step 3: Implement the request-shape and service rules**

In `SupportScoreRules::validation()`, replace the activity score rule with:

```php
'support_list.*.activity_entries.*.achieved_score' => [
    'nullable',
    'integer',
    'between:1,5',
],
```

In `SupportActivityEntryService::scoreAttributes()`, replace the enabled activity score rule with:

```php
"{$base}.achieved_score" => $criterion->allow_evaluatee_weight
    ? ['required', 'integer', 'between:1,5', 'max:'.$criterion->target_value]
    : ['prohibited'],
```

Normalize the enabled activity score without rounding:

```php
$achievedScore = $criterion->allow_evaluatee_weight
    ? (int) $entryData['achieved_score']
    : null;
```

Update the return-type PHPDoc to:

```php
/**
 * @param  array<string, mixed>  $entryData
 * @return array{indicator:?string,weight:?float,achieved_score:?int,weighted_score:?float}
 */
```

- [ ] **Step 4: Run the focused test and verify GREEN**

Run:

```powershell
php -d memory_limit=512M vendor/bin/pest tests/Feature/Evaluation/SupportActivityEntryServiceTest.php
```

Expected: PASS.

- [ ] **Step 5: Commit server validation**

```powershell
git add app/Support/SupportScoreRules.php app/Services/SupportActivityEntryService.php tests/Feature/Evaluation/SupportActivityEntryServiceTest.php
git commit -m "feat: validate support activity scores"
```

---

### Task 2: Activity Score Input Contract and Guidance

**Files:**

- Modify: `resources/views/components/support-activity-entry-editor.blade.php:52-79`
- Modify: `resources/views/components/support-criteria-table.blade.php:726-745,848-857`
- Test: `tests/Feature/SupportCriteriaEvaluationViewTest.php`

**Interfaces:**

- Consumes: `$item['target_value']`, `$item['id']`, and `$entryIndex`.
- Produces: `data-support-entry-target`, `data-support-score-help`, and `data-support-score-error` hooks.
- Produces: unique help/error IDs used by each input's `aria-describedby`.
- Produces: `min="1"`, `max="{{ min(5, target_value) }}"`, and `step="1"` for both score types.

- [ ] **Step 1: Add failing rendered-view assertions**

Add a test using `supportEvaluateeWeightedViewItem()` with target `4.00` and an activity score of `4.00`:

```php
test('activity score input uses the one-to-five target-limited contract', function () {
    $item = array_replace(supportEvaluateeWeightedViewItem(), [
        'target_value' => '4.00',
    ]);
    $item['activity_entries'][0]['achieved_score'] = '4.00';
    $item['activity_entries'][0]['weighted_score'] = '1.60';

    $html = view('components.support-criteria-table', [
        'items' => [$item],
        'readonly' => false,
        'evidenceEditable' => true,
        'requireReason' => false,
        'activityEntryRole' => 'evaluatee',
    ])->render();

    expect($html)
        ->toContain('type="number" min="1" max="4" step="1"')
        ->toContain('data-support-entry-target="4.00"')
        ->toContain('data-support-score-help')
        ->toContain('data-support-score-error')
        ->toContain('กรอกเฉพาะจำนวนเต็มตั้งแต่ 1–5 และต้องไม่เกินระดับค่าเป้าหมาย 4.00')
        ->toContain('aria-describedby="support-entry-score-help-7-0 support-entry-score-error-7-0"');
});
```

Extend the criterion input test to assert the same help/error hooks and two-ID `aria-describedby`. Extend the modal contract test to assert `data-support-modal-save`.

- [ ] **Step 2: Run the view test and verify RED**

Run:

```powershell
php -d memory_limit=512M vendor/bin/pest tests/Feature/SupportCriteriaEvaluationViewTest.php
```

Expected: FAIL because the activity input still uses `0–100`, lacks target metadata, and lacks accessible help/error elements.

- [ ] **Step 3: Render the activity score contract**

In `support-activity-entry-editor.blade.php`, calculate:

```blade
@php
    $entryScoreMaximum = min(5, (float) $item['target_value']);
    $entryScoreHelpId = "support-entry-score-help-{$item['id']}-{$entryIndex}";
    $entryScoreErrorId = "support-entry-score-error-{$item['id']}-{$entryIndex}";
@endphp
```

Replace the activity score input and append its guidance/error:

```blade
<input type="number" min="1" max="{{ $entryScoreMaximum }}" step="1"
    name="support_list[{{ $item['id'] }}][activity_entries][{{ $entryIndex }}][achieved_score]"
    value="{{ $entry['achieved_score'] ?? '' }}"
    aria-describedby="{{ $entryScoreHelpId }} {{ $entryScoreErrorId }}"
    data-support-entry-score
    data-support-entry-target="{{ $item['target_value'] }}"
    data-original-score="{{ $entry['achieved_score'] ?? '' }}"
    class="mt-2 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-slate-900">
<span id="{{ $entryScoreHelpId }}" data-support-score-help
    class="mt-1 block text-xs font-normal text-slate-500">
    กรอกเฉพาะจำนวนเต็มตั้งแต่ 1–5 และต้องไม่เกินระดับค่าเป้าหมาย {{ $item['target_value'] }}
</span>
<span id="{{ $entryScoreErrorId }}" data-support-score-error
    class="mt-1 hidden text-xs font-normal text-red-600" aria-live="polite"></span>
```

Add the same `data-support-score-help`, `data-support-score-error`, unique error ID, and two-ID `aria-describedby` contract to the criterion-level input, without changing its nullable behavior.

- [ ] **Step 4: Add a stable disabled-state style to modal Save**

Append disabled classes to `[data-support-modal-save]`:

```blade
disabled:cursor-not-allowed disabled:bg-slate-300 disabled:text-slate-500
```

Do not render it disabled by default; JavaScript derives the state whenever the modal opens or a score changes.

- [ ] **Step 5: Run the view test and verify GREEN**

Run:

```powershell
php -d memory_limit=512M vendor/bin/pest tests/Feature/SupportCriteriaEvaluationViewTest.php
```

Expected: PASS.

- [ ] **Step 6: Commit the rendered input contract**

```powershell
git add resources/views/components/support-activity-entry-editor.blade.php resources/views/components/support-criteria-table.blade.php tests/Feature/SupportCriteriaEvaluationViewTest.php
git commit -m "feat: constrain support activity score inputs"
```

---

### Task 3: Immediate Red Feedback and Disabled Save

**Files:**

- Modify: `resources/views/components/support-criteria-table-script.blade.php:36-49,121-230,920-952,1042-1053`
- Test: `tests/Feature/SupportCriteriaEvaluationViewTest.php`
- Test: `tests/js/support-score-calculator.test.mjs`

**Interfaces:**

- Consumes: `isCriterionScoreValid(value, targetValue): boolean`.
- Consumes: `[data-support-score]` with `data-support-target` and `[data-support-entry-score]` with `data-support-entry-target`.
- Produces: `setScoreFieldValidity(input, { required }): boolean`.
- Produces: `updateModalScoreValidity(item): boolean`, which sets `[data-support-modal-save].disabled`.
- Preserves: the input's exact string value.

- [ ] **Step 1: Strengthen pure validator tests and add failing script-contract assertions**

In `tests/js/support-score-calculator.test.mjs`, retain the nullable criterion cases and ensure these cases are present:

```js
assert.equal(isCriterionScoreValid('1', 5), true);
assert.equal(isCriterionScoreValid('5', 5), true);
assert.equal(isCriterionScoreValid('0', 5), false);
assert.equal(isCriterionScoreValid('7', 5), false);
assert.equal(isCriterionScoreValid('3.5', 5), false);
assert.equal(isCriterionScoreValid('4', 3.5), false);
```

In the shared-script contract test, assert:

```php
expect($script)
    ->toContain('setScoreFieldValidity')
    ->toContain('updateModalScoreValidity')
    ->toContain("input.classList.toggle('border-red-500', !valid)")
    ->toContain("input.setAttribute('aria-invalid', 'true')")
    ->toContain('saveButton.disabled = !allValid')
    ->toContain('data-support-entry-target')
    ->toContain("addEventListener('input'");
```

- [ ] **Step 2: Run JS and view tests and verify RED**

Run sequentially:

```powershell
node --test tests/js/support-score-calculator.test.mjs
php -d memory_limit=512M vendor/bin/pest tests/Feature/SupportCriteriaEvaluationViewTest.php
```

Expected: JavaScript pure tests pass, while the Blade script-contract test fails because immediate feedback and Save-state functions do not exist yet.

- [ ] **Step 3: Implement score-field visual state without rewriting values**

Add beside `isCriterionScoreValid`:

```js
const scoreValidationMessage = (targetValue) =>
    `กรอกเฉพาะจำนวนเต็มตั้งแต่ 1–5 และต้องไม่เกินระดับค่าเป้าหมาย ${targetValue}`;

const setScoreFieldValidity = (input, { required = false } = {}) => {
    if (!input) return true;

    const value = input.value.trim();
    const targetValue = input.dataset.supportTarget
        ?? input.dataset.supportEntryTarget
        ?? '';
    const valid = (!required && value === '')
        || (value !== '' && isCriterionScoreValid(value, targetValue));
    const error = input.parentElement?.querySelector('[data-support-score-error]');

    input.classList.toggle('border-red-500', !valid);
    input.classList.toggle('focus:border-red-500', !valid);
    input.classList.toggle('focus:ring-red-200', !valid);
    if (valid) {
        input.removeAttribute('aria-invalid');
    } else {
        input.setAttribute('aria-invalid', 'true');
    }
    if (error) {
        error.textContent = valid ? '' : scoreValidationMessage(targetValue);
        error.classList.toggle('hidden', valid);
    }

    return valid;
};

const updateModalScoreValidity = (item) => {
    if (!item) return true;
    const criterionScore = item.querySelector('[data-support-score]');
    const criterionValid = setScoreFieldValidity(criterionScore);
    const entryValidities = Array.from(item.querySelectorAll('[data-support-entry-score]'))
        .map((input) => setScoreFieldValidity(input, { required: true }));
    const allValid = criterionValid && entryValidities.every(Boolean);
    const saveButton = modal?.querySelector('[data-support-modal-save]');
    if (saveButton) saveButton.disabled = !allValid;
    return allValid;
};
```

The functions must never assign to `input.value`; therefore an entered `7` remains visible.

- [ ] **Step 4: Wire immediate validation into modal lifecycle**

After `initializeActivityEditors(item)` in `openSupportModal()`, call:

```js
updateModalScoreValidity(item);
```

Extend the document `input` handler:

```js
document.addEventListener('input', (event) => {
    if (event.target.closest('[data-support-score], [data-support-entry-weight], [data-support-entry-score]')) {
        window.recalculateSupportScores();
    }
    if (activeItem && event.target.closest('[data-support-score], [data-support-entry-score]')) {
        updateModalScoreValidity(activeItem);
    }
});
```

Call `updateModalScoreValidity(activeItem)` after adding, removing, reindexing, or restoring activity entries so a new required blank score disables Save and a removed invalid row no longer does.

- [ ] **Step 5: Replace old activity 0–100 click-time validation**

In `validateSupportItem()`, replace the activity-score decimal/range branch with:

```js
if (achievedScore && (
    achievedScore.value.trim() === ''
    || !isCriterionScoreValid(
        achievedScore.value,
        achievedScore.dataset.supportEntryTarget ?? '',
    )
)) {
    errors.push(
        `ค่าคะแนนที่ได้รายการที่ ${entryIndex + 1} ของ "${activity}" `
        + scoreValidationMessage(achievedScore.dataset.supportEntryTarget ?? ''),
    );
    rememberInvalid(achievedScore);
}
```

Keep the existing weight `decimalPattern` validation unchanged. Before returning from `validateSupportItem()`, call `updateModalScoreValidity(item)` so click-time validation and immediate state cannot diverge.

- [ ] **Step 6: Run focused tests and verify GREEN**

Run sequentially:

```powershell
node --test tests/js/support-score-calculator.test.mjs
php -d memory_limit=512M vendor/bin/pest tests/Feature/SupportCriteriaEvaluationViewTest.php
```

Expected: PASS.

- [ ] **Step 7: Build assets**

Run:

```powershell
npm run build
```

Expected: Vite exits successfully with no syntax or bundling errors.

- [ ] **Step 8: Commit immediate feedback**

```powershell
git add resources/views/components/support-criteria-table-script.blade.php tests/Feature/SupportCriteriaEvaluationViewTest.php tests/js/support-score-calculator.test.mjs
git commit -m "feat: show support score errors immediately"
```

---

### Task 4: Align Valid Fixtures and Verify End to End

**Files:**

- Modify: `tests/Feature/Evaluation/SupportActivityEntryServiceTest.php`
- Modify: `tests/Feature/Evaluation/SupportCriteriaReadModelTest.php`
- Modify: `tests/Feature/SupportCriteriaEvaluationViewTest.php`

**Interfaces:**

- Activity fixture examples use scores `1–5`.
- Weighted fixture values remain `(weight * score) / 100`.
- Historical persisted values remain readable; no production migration is introduced.

- [ ] **Step 1: Update current-valid activity fixtures**

Replace old current-valid examples such as:

```php
['indicator' => '<p>ตัวชี้วัดหนึ่ง</p>', 'weight' => 40, 'achieved_score' => 80, 'weighted_score' => 32],
['indicator' => '<p>ตัวชี้วัดสอง</p>', 'weight' => 60, 'achieved_score' => 90, 'weighted_score' => 54],
```

with:

```php
['indicator' => '<p>ตัวชี้วัดหนึ่ง</p>', 'weight' => 40, 'achieved_score' => 4, 'weighted_score' => 1.6],
['indicator' => '<p>ตัวชี้วัดสอง</p>', 'weight' => 60, 'achieved_score' => 5, 'weighted_score' => 3],
```

Update corresponding assertions to `4.00`, `1.60`, `5.00`, `3.00`, and aggregate `4.60`. Apply the same conversion to active service/view fixtures and reviewer-history change cases. Do not rewrite a test whose explicit purpose is verifying that an already-persisted historical value can still be read.

- [ ] **Step 2: Run the three affected PHP files sequentially**

Run:

```powershell
php -d memory_limit=512M vendor/bin/pest tests/Feature/Evaluation/SupportActivityEntryServiceTest.php
php -d memory_limit=512M vendor/bin/pest tests/Feature/Evaluation/SupportCriteriaReadModelTest.php
php -d memory_limit=512M vendor/bin/pest tests/Feature/SupportCriteriaEvaluationViewTest.php
```

Expected: PASS for all three files.

- [ ] **Step 3: Manually verify the exact modal behavior**

Open an editable criterion with target `5`, then:

1. Type `7` in an activity-entry “ค่าคะแนนที่ได้” field.
2. Confirm `7` remains visible.
3. Confirm the field border and inline message are red.
4. Confirm guidance states `กรอกเฉพาะจำนวนเต็มตั้งแต่ 1–5 และต้องไม่เกินระดับค่าเป้าหมาย 5.00`.
5. Confirm modal Save is disabled.
6. Replace `7` with `5`; confirm the red state clears and Save is enabled if other activity score fields are valid.
7. Set the criterion target to `3.5` in test data and enter `4`; confirm the same invalid state and disabled Save behavior.

- [ ] **Step 4: Run the full JavaScript and PHP suites**

Run sequentially:

```powershell
node --test tests/js/*.test.mjs
npm run build
php -d memory_limit=512M vendor/bin/pest
```

Expected:

- All JavaScript tests pass.
- Vite build passes.
- All changed and related PHP tests pass.
- If the three documented baseline `SupportScoreServiceTest` failures remain unchanged, record them explicitly rather than changing unrelated evidence/submission behavior.

- [ ] **Step 5: Review scoped diff and commit fixture alignment**

Run:

```powershell
git diff --check
git status --short
git diff -- tests/Feature/Evaluation/SupportActivityEntryServiceTest.php tests/Feature/Evaluation/SupportCriteriaReadModelTest.php tests/Feature/SupportCriteriaEvaluationViewTest.php
```

Stage only the three scoped test files:

```powershell
git add tests/Feature/Evaluation/SupportActivityEntryServiceTest.php tests/Feature/Evaluation/SupportCriteriaReadModelTest.php tests/Feature/SupportCriteriaEvaluationViewTest.php
git commit -m "test: align support activity score fixtures"
```
