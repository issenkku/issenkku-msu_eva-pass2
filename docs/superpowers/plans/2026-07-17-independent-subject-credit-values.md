# Independent Subject Credit Values Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Make all four subject credit values independent integers stored exactly as entered, without cross-field validation or automatic summation.

**Architecture:** Keep the existing database columns and per-field validation. Remove the Excel cross-field invariant and both Blade scripts that overwrite `credits`; preserve submitted values through the existing request and controller paths.

**Tech Stack:** Laravel 11, PHP 8.3, Pest, Blade, vanilla JavaScript, Maatwebsite Excel/PhpSpreadsheet

## Global Constraints

- `credits`, `lecture_credits`, `lab_credits`, and `self_study_credits` are independent.
- Every credit field is required and must be an integer greater than or equal to `0`.
- Do not calculate, compare, warn about, or reconcile the four values.
- Do not change the database schema, workbook headers, workload formulas, or existing records.
- Preserve unrelated working-tree changes.

---

## File Map

- `SubjectImportRowValidator.php`: remove the only cross-field credit rule.
- `SubjectInstructionsSheet.php`: replace the obsolete sum instruction.
- `StoreSubjectRequest.php` and `UpdateSubjectRequest.php`: leave credits intact for Laravel validation.
- Both subject-form Blade scripts: stop overwriting `credits`.
- Subject import, request, persistence, workbook, and Blade contract tests: lock the new behavior.

### Task 1: Accept independent values through the Excel workflow

**Files:**
- Modify: `tests/Unit/Subjects/SubjectImportRowValidatorTest.php:29-35`
- Modify: `tests/Unit/Subjects/SubjectWorkbookExportTest.php:9-21`
- Modify: `tests/Feature/Subjects/SubjectImportEndToEndTest.php`
- Modify: `app/Services/Subjects/SubjectImportRowValidator.php:45-49`
- Modify: `app/Exports/Subjects/SubjectInstructionsSheet.php:21`

**Interfaces:**
- Consumes: `SubjectImportRowValidator::validate(int $excelRow, array $values): array` and the existing seven workbook columns.
- Produces: a valid row when all four credit cells are non-negative integers, irrespective of their sum.

- [ ] **Step 1: Replace the obsolete unit test**

Replace `validator rejects a total that does not equal the three component credits` with:

```php
test('validator accepts independent credit values', function () {
    $result = (new SubjectImportRowValidator)->validate(8, validSubjectImportValues([
        3 => 3, 4 => 3, 5 => 0, 6 => 6,
    ]));

    expect($result['errors'])->toBe([])
        ->and($result['row'])->not->toBeNull()
        ->and($result['row']->attributes())->toMatchArray([
            'credits' => 3,
            'lecture_credits' => 3,
            'lab_credits' => 0,
            'self_study_credits' => 6,
        ]);
});
```

- [ ] **Step 2: Add failing template and end-to-end coverage**

Extend the first expectation chain in `SubjectWorkbookExportTest.php`:

```php
        ->and($instructions)->toContain('ทั้งสี่ค่าเป็นอิสระต่อกัน')
        ->and($instructions)->not->toContain('รวมต้องเท่ากับผลรวมสามช่องย่อย')
```

Append to `SubjectImportEndToEndTest.php`:

```php
test('independent credit values survive preview and confirm', function () {
    $admin = e2eAdmin();
    $response = $this->actingAs($admin, 'web')->post(route('subjects.import.preview.store'), [
        'import_file' => e2eUpload([['ENV301', 'อนามัยสิ่งแวดล้อม', '', 3, 3, 0, 6]]),
    ])->assertRedirect();
    $token = tokenFromRedirect($response);

    $this->actingAs($admin, 'web')
        ->get(route('subjects.import.preview.show', $token))
        ->assertOk()->assertSee('ENV301')
        ->assertDontSee('หน่วยกิตรวมต้องเท่ากับผลรวมของหน่วยกิตย่อย');

    $this->actingAs($admin, 'web')
        ->post(route('subjects.import.confirm', $token), ['selected_codes' => []])
        ->assertRedirect(route('subjects.index'));

    $subject = Subject::where('code', 'ENV301')->firstOrFail();
    expect((int) $subject->credits)->toBe(3)
        ->and((int) $subject->lecture_credits)->toBe(3)
        ->and((int) $subject->lab_credits)->toBe(0)
        ->and((int) $subject->self_study_credits)->toBe(6);
});
```

- [ ] **Step 3: Verify RED**

```powershell
php artisan test --compact tests/Unit/Subjects/SubjectImportRowValidatorTest.php tests/Unit/Subjects/SubjectWorkbookExportTest.php tests/Feature/Subjects/SubjectImportEndToEndTest.php
```

Expected: FAIL on the sum error, old instructions, and blocked confirmation.

- [ ] **Step 4: Remove the cross-field invariant**

Delete only this block from `SubjectImportRowValidator::validate()`:

```php
        if ($numbers[3] !== null && $numbers[4] !== null && $numbers[5] !== null && $numbers[6] !== null
            && $numbers[3] !== $numbers[4] + $numbers[5] + $numbers[6]) {
            $errors[] = $this->error($excelRow, $code, 3, $values[3], 'หน่วยกิตรวมต้องเท่ากับผลรวมของหน่วยกิตย่อย');
        }
```

Keep the per-column `integer()` loop unchanged.

- [ ] **Step 5: Correct the workbook instruction**

Replace its credit row with:

```php
            ['หน่วยกิต', '3 / 3 / 0 / 6', 'จำนวนเต็มตั้งแต่ 0 ขึ้นไป; ทั้งสี่ค่าเป็นอิสระต่อกัน'],
```

- [ ] **Step 6: Verify GREEN and commit**

Run Step 3 again. Expected: PASS.

```powershell
git add app/Services/Subjects/SubjectImportRowValidator.php app/Exports/Subjects/SubjectInstructionsSheet.php tests/Unit/Subjects/SubjectImportRowValidatorTest.php tests/Unit/Subjects/SubjectWorkbookExportTest.php tests/Feature/Subjects/SubjectImportEndToEndTest.php
git commit -m "feat: accept independent subject credit values"
```

### Task 2: Preserve and validate manual credit input on the server

**Files:**
- Create: `tests/Feature/Subjects/SubjectCreditIndependenceTest.php`
- Modify: `app/Http/Requests/Workload/StoreSubjectRequest.php:13-31`
- Modify: `app/Http/Requests/Workload/UpdateSubjectRequest.php:14-24`

**Interfaces:**
- Consumes: POST `subjects.store` and PUT `subjects.update` payloads using the existing four credit field names.
- Produces: independently persisted integers; invalid strings, blanks, decimals, and negatives remain visible to Laravel validation instead of being coerced.

- [ ] **Step 1: Create the failing feature tests**

Create `tests/Feature/Subjects/SubjectCreditIndependenceTest.php`:

```php
<?php

use App\Models\Subject;
use App\Models\User;
use Spatie\Permission\Models\Role;

function independentCreditAdmin(): User
{
    Role::findOrCreate('admin');
    $admin = User::factory()->create(['status' => 'active']);
    $admin->assignRole('admin');

    return $admin;
}

function independentCreditPayload(array $overrides = []): array
{
    return array_replace([
        'code' => 'CREDIT101',
        'name_th' => 'วิชาทดสอบหน่วยกิต',
        'name_en' => '',
        'credits' => 3,
        'lecture_credits' => 3,
        'lab_credits' => 0,
        'self_study_credits' => 6,
        'is_active' => true,
    ], $overrides);
}

test('manual create and update preserve independent credit values', function () {
    $admin = independentCreditAdmin();
    $this->actingAs($admin, 'web')
        ->post(route('subjects.store'), independentCreditPayload())
        ->assertSessionDoesntHaveErrors();

    $subject = Subject::where('code', 'CREDIT101')->firstOrFail();
    expect((int) $subject->credits)->toBe(3)
        ->and((int) $subject->lecture_credits)->toBe(3)
        ->and((int) $subject->lab_credits)->toBe(0)
        ->and((int) $subject->self_study_credits)->toBe(6);

    $this->actingAs($admin, 'web')
        ->put(route('subjects.update', $subject->id), independentCreditPayload([
            'credits' => 4,
            'lecture_credits' => 1,
            'lab_credits' => 2,
            'self_study_credits' => 8,
        ]))->assertSessionDoesntHaveErrors();

    $subject->refresh();
    expect((int) $subject->credits)->toBe(4)
        ->and((int) $subject->lecture_credits)->toBe(1)
        ->and((int) $subject->lab_credits)->toBe(2)
        ->and((int) $subject->self_study_credits)->toBe(8);
});

test('manual subject writes reject invalid credit input', function (string $field, mixed $value) {
    $payload = independentCreditPayload([
        'code' => 'BAD'.strtoupper(substr(md5($field.serialize($value)), 0, 8)),
        $field => $value,
    ]);

    $this->actingAs(independentCreditAdmin(), 'web')
        ->post(route('subjects.store'), $payload)
        ->assertSessionHasErrors($field);

    expect(Subject::where('code', $payload['code'])->exists())->toBeFalse();
})->with([
    'total text' => ['credits', 'three'],
    'lecture blank' => ['lecture_credits', ''],
    'lab decimal' => ['lab_credits', '1.5'],
    'self study negative' => ['self_study_credits', '-1'],
]);
```

- [ ] **Step 2: Verify RED**

```powershell
php artisan test --compact tests/Feature/Subjects/SubjectCreditIndependenceTest.php
```

Expected: the persistence case passes, while text, blank, or decimal data sets FAIL because `prepareForValidation()` coerces them.

- [ ] **Step 3: Stop coercing StoreSubjectRequest credits**

Replace the beginning of `prepareForValidation()` through the `$normalized` declaration with:

```php
    protected function prepareForValidation(): void
    {
        $normalized = [
            'name_th' => is_scalar($this->input('name_th')) || $this->input('name_th') === null
                ? SubjectName::normalize($this->input('name_th'))
                : $this->input('name_th'),
            'name_en' => is_scalar($this->input('name_en')) || $this->input('name_en') === null
                ? SubjectName::normalize($this->input('name_en'))
                : $this->input('name_en'),
        ];
```

Keep conditional code normalization and `$this->merge($normalized);`. Do not add credit fields to `$normalized`; the existing `required`, `integer`, and `min:0` rules must see original input.

- [ ] **Step 4: Stop coercing UpdateSubjectRequest credits**

Replace the first part of `prepareForValidation()` with:

```php
    protected function prepareForValidation(): void
    {
        $normalized = [];

        foreach (['name_th', 'name_en'] as $field) {
```

Keep the name loop, conditional code normalization, and merge. Do not merge missing credit fields as zero.

- [ ] **Step 5: Verify GREEN and neighboring behavior**

```powershell
php artisan test --compact tests/Feature/Subjects/SubjectCreditIndependenceTest.php tests/Feature/Subjects/SubjectNameFlexibilityTest.php tests/Feature/Subjects/SubjectCodeInvariantTest.php
```

Expected: PASS, including partial name updates that retain existing credits.

- [ ] **Step 6: Commit the request behavior**

```powershell
git add app/Http/Requests/Workload/StoreSubjectRequest.php app/Http/Requests/Workload/UpdateSubjectRequest.php tests/Feature/Subjects/SubjectCreditIndependenceTest.php
git commit -m "fix: preserve manual subject credit input"
```

### Task 3: Remove automatic summation from both subject forms

**Files:**
- Modify: `tests/Feature/CreateModalContractTest.php`
- Modify: `resources/views/subjects/partials/index-script.blade.php:4-23,319-341`
- Modify: `resources/views/evaluatee/partials/workload-script-subject-form.blade.php:17-31,90-99`

**Interfaces:**
- Consumes: the existing four number inputs in `components.subject-modal`.
- Produces: scripts that validate and submit entered values without assigning component sums to `#credits`.

- [ ] **Step 1: Add a failing script contract test**

Append to `tests/Feature/CreateModalContractTest.php`:

```php
test('subject forms do not calculate total credits from component fields', function () {
    $subjectScript = file_get_contents(resource_path('views/subjects/partials/index-script.blade.php'));
    $evaluateeScript = file_get_contents(resource_path('views/evaluatee/partials/workload-script-subject-form.blade.php'));

    expect($subjectScript)
        ->not->toContain('function updateTotalCredits')
        ->not->toContain('updateTotalCredits()')
        ->not->toMatch('/totalInput\.value\s*=/');

    expect($evaluateeScript)
        ->not->toContain('function updateSubjectCreditTotal')
        ->not->toContain("addEventListener('input', updateSubjectCreditTotal)")
        ->not->toMatch('/totalInput\.value\s*=/');
});
```

- [ ] **Step 2: Verify RED**

```powershell
php artisan test --compact tests/Feature/CreateModalContractTest.php
```

Expected: FAIL because both scripts currently assign to `totalInput.value`.

- [ ] **Step 3: Remove summation from the admin form**

Delete the complete `updateTotalCredits()` function. Replace the component-credit listener block with:

```javascript
        [lectureCreditsInput, labCreditsInput, selfStudyCreditsInput].forEach((input) => {
            if (!input) return;

            input.addEventListener('input', validateForm);
            input.addEventListener('blur', validateForm);

            input.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    if (validateForm()) {
                        submitForm();
                    }
                }
            });
        });
```

This keeps immediate validation and Enter submission without writing to `credits`.

- [ ] **Step 4: Remove summation from the evaluatee form**

Delete the complete `updateSubjectCreditTotal()` function and this listener block:

```javascript
        ['lecture_credits', 'lab_credits', 'self_study_credits'].forEach(function (id) {
            const input = document.getElementById(id);
            if (!input) {
                return;
            }

            input.addEventListener('input', updateSubjectCreditTotal);
        });
```

Keep `validateSubjectForm()` and `window.submitForm`; they validate and submit each field independently.

- [ ] **Step 5: Verify GREEN and commit**

Run Step 2 again. Expected: PASS.

```powershell
git add resources/views/subjects/partials/index-script.blade.php resources/views/evaluatee/partials/workload-script-subject-form.blade.php tests/Feature/CreateModalContractTest.php
git commit -m "fix: stop calculating subject credit totals"
```

### Task 4: Run final verification

**Files:**
- Verify only; this task introduces no production files.

**Interfaces:**
- Consumes: Tasks 1–3.
- Produces: evidence that focused behavior and the full application remain green.

- [ ] **Step 1: Confirm obsolete rules are absent**

```powershell
rg -n "หน่วยกิตรวมต้องเท่ากับผลรวมของหน่วยกิตย่อย|รวมต้องเท่ากับผลรวมสามช่องย่อย|updateTotalCredits|updateSubjectCreditTotal" app resources
```

Expected: no matches and exit code 1. Regression assertions under `tests/` and historical documents under `docs/` are intentionally excluded.

- [ ] **Step 2: Run focused PHP tests**

```powershell
php artisan test --compact tests/Unit/Subjects/SubjectImportRowValidatorTest.php tests/Unit/Subjects/SubjectWorkbookExportTest.php tests/Feature/Subjects/SubjectImportEndToEndTest.php tests/Feature/Subjects/SubjectCreditIndependenceTest.php tests/Feature/CreateModalContractTest.php tests/Feature/Subjects/SubjectNameFlexibilityTest.php tests/Feature/Subjects/SubjectCodeInvariantTest.php
```

Expected: PASS with no failures or errors.

- [ ] **Step 3: Run the complete PHP suite**

```powershell
php artisan test --compact
```

Expected: PASS with no failures or errors.

- [ ] **Step 4: Run JavaScript, formatting, and build gates**

```powershell
npm run test:js
vendor\bin\pint --test
npm run build
```

Expected: JavaScript tests pass, Pint reports no violations, and Vite builds successfully.

- [ ] **Step 5: Review the final workspace**

```powershell
git status --short
git diff --check
git log -4 --oneline
```

Expected: no whitespace errors; planned commits are present; unrelated pre-existing changes remain uncommitted and unchanged.
