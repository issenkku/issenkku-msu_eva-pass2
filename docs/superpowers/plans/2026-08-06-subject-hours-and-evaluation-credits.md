# Subject Hours and Evaluation Credits Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Store subject hours independently from evaluation credits, render a shared hours-first tuple with credit fallback, update the complete Excel pipeline, and allow 10/25/50/100-row subject bulk selection.

**Architecture:** Add three non-null hour columns and expose one `Subject` display accessor that chooses the complete hour or credit tuple. Keep workload formula inputs unchanged. Extend the existing request normalization, shared subject modal, import DTO/services, and pagination query without introducing an automatic hours-to-credits conversion.

**Tech Stack:** PHP 8.2, Laravel 11, Eloquent, Blade, Pest 3, vanilla JavaScript, Node test runner, Maatwebsite Excel / PhpSpreadsheet.

## Global Constraints

- The seven numeric subject values are independent non-negative integers.
- Blank or missing numeric form and workbook values normalize to `0`.
- Workload formulas continue to use only `credits`, `lecture_credits`, `lab_credits`, and `self_study_credits`.
- The tuple uses all three hours when any hour is greater than zero; otherwise it uses all three split-credit values.
- Do not label the rendered tuple as hours or credits.
- Reject legacy seven-column workbooks and require the new ten-column template.
- Page sizes are exactly `10`, `25`, `50`, and `100`; the default is `10`.
- Bulk selection and deletion remain scoped to submitted IDs from the current page.
- Preserve unrelated dirty-worktree changes and stage only files belonging to each task.

---

### Task 1: Persist Hours and Define the Shared Tuple Contract

**Files:**
- Create: `database/migrations/2026_08_06_000001_add_hours_to_subjects_table.php`
- Modify: `app/Models/Subject.php`
- Create: `tests/Feature/Subjects/SubjectHoursTest.php`

**Interfaces:**
- Consumes: Existing `subjects` table and `Subject` split-credit fields.
- Produces: Integer model attributes `lecture_hours`, `lab_hours`, `self_study_hours` and array accessor `display_component_values`.

- [ ] **Step 1: Write failing persistence and display-contract tests**

```php
<?php

use App\Models\Subject;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('subjects default all hour fields to zero', function () {
    $subject = Subject::create([
        'code' => 'ZERO-HOURS', 'name_th' => 'Zero Hours', 'credits' => 3,
        'lecture_credits' => 2, 'lab_credits' => 1, 'self_study_credits' => 0,
    ]);

    expect($subject->fresh()->only(['lecture_hours', 'lab_hours', 'self_study_hours']))
        ->toMatchArray(['lecture_hours' => 0, 'lab_hours' => 0, 'self_study_hours' => 0]);
});

test('component display values use the complete hour set when any hour is positive', function () {
    $subject = new Subject([
        'lecture_credits' => 1, 'lab_credits' => 1, 'self_study_credits' => 1,
        'lecture_hours' => 2, 'lab_hours' => 0, 'self_study_hours' => 0,
    ]);

    expect($subject->display_component_values)->toBe([2, 0, 0]);
});

test('component display values use the complete credit set when every hour is zero', function () {
    $subject = new Subject([
        'lecture_credits' => 3, 'lab_credits' => 0, 'self_study_credits' => 1,
        'lecture_hours' => 0, 'lab_hours' => 0, 'self_study_hours' => 0,
    ]);

    expect($subject->display_component_values)->toBe([3, 0, 1]);
});
```

- [ ] **Step 2: Run the test and verify RED**

Run: `php vendor/bin/pest tests/Feature/Subjects/SubjectHoursTest.php`

Expected: FAIL because the hour columns and `display_component_values` do not exist.

- [ ] **Step 3: Add the migration and minimal model contract**

```php
Schema::table('subjects', function (Blueprint $table) {
    $table->integer('lecture_hours')->default(0)->after('self_study_credits');
    $table->integer('lab_hours')->default(0)->after('lecture_hours');
    $table->integer('self_study_hours')->default(0)->after('lab_hours');
});
```

Add the three fields to `$fillable`, cast all seven numeric values to integers, append `display_component_values`, and implement:

```php
protected function displayComponentValues(): Attribute
{
    return Attribute::get(function (): array {
        $hours = [$this->lecture_hours, $this->lab_hours, $this->self_study_hours];

        return collect($hours)->contains(fn ($value) => (int) $value > 0)
            ? array_map('intval', $hours)
            : [
                (int) $this->lecture_credits,
                (int) $this->lab_credits,
                (int) $this->self_study_credits,
            ];
    });
}
```

The migration `down()` drops only the three new columns.

- [ ] **Step 4: Run the focused test and verify GREEN**

Run: `php vendor/bin/pest tests/Feature/Subjects/SubjectHoursTest.php`

Expected: PASS, 3 tests.

- [ ] **Step 5: Commit the isolated data-model change**

```powershell
git add app/Models/Subject.php database/migrations/2026_08_06_000001_add_hours_to_subjects_table.php tests/Feature/Subjects/SubjectHoursTest.php
git commit -m "feat: store independent subject hours"
```

### Task 2: Accept Seven Independent Numeric Values in Manual Forms

**Files:**
- Modify: `app/Http/Requests/Workload/StoreSubjectRequest.php`
- Modify: `app/Http/Requests/Workload/UpdateSubjectRequest.php`
- Modify: `resources/views/components/subject-modal.blade.php`
- Modify: `resources/views/subjects/partials/index-script.blade.php`
- Modify: `resources/views/evaluatee/partials/workload-script-subject-form.blade.php`
- Modify: `tests/Feature/Subjects/SubjectCreditIndependenceTest.php`
- Modify: `tests/Feature/Evaluation/WorkloadPageAsyncMutationTest.php`
- Modify: `tests/Feature/CreateModalContractTest.php`
- Modify: `tests/js/workload-subject-form.test.mjs`

**Interfaces:**
- Consumes: The hour attributes from Task 1 and the existing shared subject modal.
- Produces: Create/update JSON and form contracts that normalize and persist all seven numeric values.

- [ ] **Step 1: Extend feature tests for independent hour persistence and validation**

Add hour values to `independentCreditPayload()` and assert create/update preservation:

```php
'lecture_hours' => 2,
'lab_hours' => 3,
'self_study_hours' => 1,
```

Extend the invalid-input dataset:

```php
'lecture hours text' => ['lecture_hours', 'two'],
'lab hours decimal' => ['lab_hours', '1.5'],
'self study hours negative' => ['self_study_hours', '-1'],
```

Extend the blank-input test to assert all seven numeric fields equal zero. Update the evaluatee JSON-create test to submit and assert the three raw hour fields.

- [ ] **Step 2: Extend Blade/JavaScript contract tests**

Require the shared modal to contain `lecture_hours`, `lab_hours`, and `self_study_hours`, groups marked `data-subject-credit-fields` and `data-subject-hour-fields`, no `required` attribute on numeric inputs, and client normalization of all seven fields. Extend `tests/js/workload-subject-form.test.mjs` with hour fake elements and assertions that blanks become `'0'` before request submission.

- [ ] **Step 3: Run the affected PHP and JavaScript tests and verify RED**

Run:

```powershell
php vendor/bin/pest tests/Feature/Subjects/SubjectCreditIndependenceTest.php tests/Feature/Evaluation/WorkloadPageAsyncMutationTest.php tests/Feature/CreateModalContractTest.php
node --test tests/js/workload-subject-form.test.mjs
```

Expected: FAIL because requests, modal inputs, scripts, and response data do not yet support hour fields.

- [ ] **Step 4: Expand server request normalization and rules**

Use one identical field list in both requests:

```php
foreach ([
    'credits', 'lecture_credits', 'lab_credits', 'self_study_credits',
    'lecture_hours', 'lab_hours', 'self_study_hours',
] as $field) {
    if ($this->input($field) === null || $this->input($field) === '') {
        $normalized[$field] = 0;
    }
}
```

Add `integer|min:0` rules for all three hour fields, matching the create/update semantics of the four credit fields.

- [ ] **Step 5: Group and wire the shared modal fields**

Render a `data-subject-credit-fields` section containing total and three split-credit inputs, followed by a `data-subject-hour-fields` section containing:

```html
<input type="number" id="lecture_hours" name="lecture_hours" min="0" step="1" value="0">
<input type="number" id="lab_hours" name="lab_hours" min="0" step="1" value="0">
<input type="number" id="self_study_hours" name="self_study_hours" min="0" step="1" value="0">
```

Add distinct error elements. Extend both scripts' element lookup, blank-to-zero normalization, integer validation, error routing, edit-button dataset loading, reset handling, and listeners to all three fields. Do not add any cross-field calculation.

- [ ] **Step 6: Run the affected tests and verify GREEN**

Run:

```powershell
php vendor/bin/pest tests/Feature/Subjects/SubjectCreditIndependenceTest.php tests/Feature/Evaluation/WorkloadPageAsyncMutationTest.php tests/Feature/CreateModalContractTest.php
node --test tests/js/workload-subject-form.test.mjs
```

Expected: PASS with zero validation failures.

- [ ] **Step 7: Commit the manual-form change**

```powershell
git add app/Http/Requests/Workload/StoreSubjectRequest.php app/Http/Requests/Workload/UpdateSubjectRequest.php resources/views/components/subject-modal.blade.php resources/views/subjects/partials/index-script.blade.php resources/views/evaluatee/partials/workload-script-subject-form.blade.php tests/Feature/Subjects/SubjectCreditIndependenceTest.php tests/Feature/Evaluation/WorkloadPageAsyncMutationTest.php tests/Feature/CreateModalContractTest.php tests/js/workload-subject-form.test.mjs
git commit -m "feat: capture subject hours in manual forms"
```

### Task 3: Render the Shared Hours-First Tuple Everywhere

**Files:**
- Modify: `app/Support/EvaluateeWorkloadModalData.php`
- Modify: `resources/views/subjects/partials/index-table-section.blade.php`
- Modify: `resources/views/subjects/partials/index-table-row.blade.php`
- Modify: `resources/views/evaluatee/partials/workload-entry-modal-subject-section.blade.php`
- Modify: `resources/views/evaluatee/partials/workload-script-entry-modal.blade.php`
- Modify: `tests/Feature/DeleteActionTest.php`
- Modify: `tests/Feature/CreateModalContractTest.php`
- Create: `tests/Unit/EvaluateeWorkloadModalDataTest.php`

**Interfaces:**
- Consumes: `Subject::$display_component_values` from Task 1.
- Produces: `display_component_values: array{0:int,1:int,2:int}` in modal data and JSON-created subject payloads.

- [ ] **Step 1: Write failing renderer and modal-data tests**

Create subjects where credits and hours differ, then assert:

```php
expect($hoursHtml)->toContain('( 2 / 0 / 0 )')
    ->not->toContain('( 1 / 1 / 1 )');

expect($fallbackHtml)->toContain('( 3 / 0 / 1 )');
```

For `EvaluateeWorkloadModalData::build()`, assert the subject element includes:

```php
'display_component_values' => [2, 0, 0],
```

and retains raw `lecture_credits`, `lab_credits`, and `self_study_credits` for formula autofill.

- [ ] **Step 2: Run focused renderer tests and verify RED**

Run:

```powershell
php vendor/bin/pest tests/Feature/DeleteActionTest.php tests/Feature/CreateModalContractTest.php tests/Unit/EvaluateeWorkloadModalDataTest.php
```

Expected: FAIL because all current tuple renderers use split-credit fields directly.

- [ ] **Step 3: Route every tuple renderer through the shared accessor**

In PHP renderers, destructure once:

```php
@php([$lectureDisplay, $labDisplay, $selfStudyDisplay] = $subject->display_component_values)
```

Use those values in both full-table and async-row partials. In modal data, add raw hours and `display_component_values`. In the evaluatee subject option, render the three display values while keeping credit data attributes unchanged for workload autofill.

For subjects created asynchronously, build the tuple from `subject.display_component_values` returned by Eloquent rather than recalculating a second fallback rule in JavaScript.

- [ ] **Step 4: Run focused renderer tests and verify GREEN**

Run:

```powershell
php vendor/bin/pest tests/Feature/DeleteActionTest.php tests/Feature/CreateModalContractTest.php tests/Unit/EvaluateeWorkloadModalDataTest.php
```

Expected: PASS and raw credit attributes remain present.

- [ ] **Step 5: Commit the display-contract change**

```powershell
git add app/Support/EvaluateeWorkloadModalData.php resources/views/subjects/partials/index-table-section.blade.php resources/views/subjects/partials/index-table-row.blade.php resources/views/evaluatee/partials/workload-entry-modal-subject-section.blade.php resources/views/evaluatee/partials/workload-script-entry-modal.blade.php tests/Feature/DeleteActionTest.php tests/Feature/CreateModalContractTest.php tests/Unit/EvaluateeWorkloadModalDataTest.php
git commit -m "feat: display subject hours with credit fallback"
```

### Task 4: Expand the Excel Contract to Ten Columns

**Files:**
- Modify: `app/Support/Subjects/SubjectWorkbookSchema.php`
- Modify: `app/Data/Subjects/SubjectImportRow.php`
- Modify: `app/Services/Subjects/SubjectImportRowValidator.php`
- Modify: `app/Services/Subjects/SubjectWorkbookReader.php`
- Modify: `app/Services/Subjects/SubjectWorkbookFactory.php`
- Modify: `app/Services/Subjects/SubjectImportPreviewService.php`
- Modify: `resources/views/subjects/imports/partials/tables.blade.php`
- Modify: `tests/Unit/Subjects/SubjectWorkbookReaderTest.php`
- Modify: `tests/Unit/Subjects/SubjectWorkbookExportTest.php`
- Modify: `tests/Unit/Subjects/SubjectImportRowValidatorTest.php`
- Modify: `tests/Feature/Subjects/SubjectImportPreviewTest.php`
- Modify: `tests/Feature/Subjects/SubjectImportCommitterTest.php`
- Modify: `tests/Feature/Subjects/SubjectImportEndToEndTest.php`
- Modify: `tests/Feature/Subjects/SubjectImportHttpTest.php`
- Modify: `tests/Feature/Subjects/SubjectImportUiTest.php`

**Interfaces:**
- Consumes: Seven independent `Subject` numeric fields.
- Produces: A strict ten-column workbook row ordered as code, Thai name, English name, four credit fields, and three hour fields.

- [ ] **Step 1: Update workbook test fixtures to the wished-for ten-column contract**

Use rows shaped as:

```php
['CS101', 'ชื่อไทย', 'English', 3, 2, 1, 0, 3, 2, 1]
```

Assert `SubjectImportRow::attributes()` and exported data include:

```php
'lecture_hours' => 3,
'lab_hours' => 2,
'self_study_hours' => 1,
```

Add validator cases proving blank numeric cells become zero and negative/decimal hour values produce errors. Add a reader test that supplies the exact legacy seven headers and expects `SubjectWorkbookException` text directing the user to the new template.

- [ ] **Step 2: Run the workbook and import tests and verify RED**

Run:

```powershell
php vendor/bin/pest tests/Unit/Subjects tests/Feature/Subjects/SubjectImportPreviewTest.php tests/Feature/Subjects/SubjectImportCommitterTest.php tests/Feature/Subjects/SubjectImportEndToEndTest.php tests/Feature/Subjects/SubjectImportHttpTest.php tests/Feature/Subjects/SubjectImportUiTest.php
```

Expected: FAIL because the reader accepts only columns A:G and import objects omit hours.

- [ ] **Step 3: Define the strict ten-column schema and legacy-header error**

Append the three Thai hour headers to `SubjectWorkbookSchema::HEADERS`. Read `A1:J1`, require highest data column `J`, scan formulas across `A:J`, and read each data row from `A:J`.

Before returning the generic header error, detect the exact former seven-header contract and throw a message equivalent to:

```text
ไฟล์นี้เป็น Template รูปแบบเก่า กรุณาดาวน์โหลด Template ใหม่ที่มีคอลัมน์ชั่วโมงครบ 3 ช่อง
```

- [ ] **Step 4: Carry hours through validation, DTOs, preview, commit, and export**

Expand the numeric validator indexes to `3..9`. Its integer parser returns `0` for `null` and trimmed empty strings, while rejecting decimals, negative values, booleans, arrays, and non-numeric text.

Extend `SubjectImportRow` with:

```php
public int $lectureHours,
public int $labHours,
public int $selfStudyHours,
```

Add the three keys to `attributes()`, `SubjectImportPreviewService::COMPARED`, fingerprinting, field labels, and factory export rows. The existing committer then persists them through `Arr::except()` without a separate write path.

- [ ] **Step 5: Run workbook and import tests and verify GREEN**

Run:

```powershell
php vendor/bin/pest tests/Unit/Subjects tests/Feature/Subjects/SubjectImportPreviewTest.php tests/Feature/Subjects/SubjectImportCommitterTest.php tests/Feature/Subjects/SubjectImportEndToEndTest.php tests/Feature/Subjects/SubjectImportHttpTest.php tests/Feature/Subjects/SubjectImportUiTest.php
```

Expected: PASS; exports contain ten columns and legacy workbooks are rejected.

- [ ] **Step 6: Commit the workbook change**

```powershell
git add app/Support/Subjects/SubjectWorkbookSchema.php app/Data/Subjects/SubjectImportRow.php app/Services/Subjects/SubjectImportRowValidator.php app/Services/Subjects/SubjectWorkbookReader.php app/Services/Subjects/SubjectWorkbookFactory.php app/Services/Subjects/SubjectImportPreviewService.php resources/views/subjects/imports/partials/tables.blade.php tests/Unit/Subjects tests/Feature/Subjects/SubjectImportPreviewTest.php tests/Feature/Subjects/SubjectImportCommitterTest.php tests/Feature/Subjects/SubjectImportEndToEndTest.php tests/Feature/Subjects/SubjectImportHttpTest.php tests/Feature/Subjects/SubjectImportUiTest.php
git commit -m "feat: import and export subject hours"
```

### Task 5: Add Page-Size Selection and Prove Bulk Deletion Beyond Ten Rows

**Files:**
- Modify: `app/Http/Controllers/Workload/SubjectController.php`
- Modify: `resources/views/subjects/index.blade.php`
- Modify: `resources/views/subjects/partials/index-table-section.blade.php`
- Create: `tests/Feature/Subjects/SubjectPaginationTest.php`
- Modify: `tests/Feature/Settings/BulkSettingDeleteTest.php`

**Interfaces:**
- Consumes: `per_page` query parameter and current search/status/sort parameters.
- Produces: `LengthAwarePaginator` with 10, 25, 50, or 100 rows and current-page-only checkbox submission.

- [ ] **Step 1: Write failing pagination tests**

Create 120 subjects and assert:

```php
$this->actingAs($admin)->get(route('subjects.index', ['per_page' => 25]))
    ->assertOk()
    ->assertViewHas('subjects', fn ($subjects) => $subjects->perPage() === 25 && $subjects->count() === 25);
```

Use a dataset for `10`, `25`, `50`, and `100`. Add invalid values `0`, `11`, `101`, and `'all'`, each expected to resolve to `10`. Assert generated pagination URLs preserve `per_page`, `search`, `status`, and `sort`.

- [ ] **Step 2: Write a failing bulk-delete test with twelve selected IDs**

Submit twelve subject IDs plus one unselected control subject, then assert the response reports twelve deleted IDs and the control subject remains.

- [ ] **Step 3: Run pagination and bulk tests and verify RED**

Run:

```powershell
php vendor/bin/pest tests/Feature/Subjects/SubjectPaginationTest.php tests/Feature/Settings/BulkSettingDeleteTest.php
```

Expected: Pagination assertions FAIL because the controller always uses ten. The bulk endpoint test may already pass; if so, retain it as characterization evidence and do not alter the endpoint unnecessarily.

- [ ] **Step 4: Implement the allow-listed page size and selector**

In `SubjectController::index()`:

```php
$allowedPageSizes = [10, 25, 50, 100];
$requestedPageSize = filter_var($request->input('per_page'), FILTER_VALIDATE_INT);
$perPage = in_array($requestedPageSize, $allowedPageSizes, true) ? $requestedPageSize : 10;

$subjects = $subjects->paginate($perPage)->withQueryString();
```

Render a `per_page` select with exactly four options. Submit it through the existing filter GET form or a dedicated GET form that preserves search, status, and sort as hidden inputs. Keep `data-bulk-select-all` scoped to the rendered page and retain the selected-count modal behavior.

- [ ] **Step 5: Run pagination and bulk tests and verify GREEN**

Run:

```powershell
php vendor/bin/pest tests/Feature/Subjects/SubjectPaginationTest.php tests/Feature/Settings/BulkSettingDeleteTest.php tests/Feature/DeleteActionTest.php
```

Expected: PASS; selected page size works and more than ten submitted IDs delete in one request.

- [ ] **Step 6: Commit the pagination change**

```powershell
git add app/Http/Controllers/Workload/SubjectController.php resources/views/subjects/index.blade.php resources/views/subjects/partials/index-table-section.blade.php tests/Feature/Subjects/SubjectPaginationTest.php tests/Feature/Settings/BulkSettingDeleteTest.php
git commit -m "feat: choose subject page size for bulk actions"
```

### Task 6: Guard Formula Behavior and Run Full Verification

**Files:**
- Create: `tests/Feature/Evaluation/WorkloadSubjectCreditFormulaTest.php`

**Interfaces:**
- Consumes: Subjects whose hour and credit values deliberately differ.
- Produces: Regression evidence that workload formulas use credits exclusively.

- [ ] **Step 1: Write a formula regression test with divergent values**

Create a subject with `lab_hours = 3` and `lab_credits = 1`, then submit/evaluate a practical workload form whose formula uses `lab_credits`. Assert the stored field value and calculated score use `1`, never `3`. Add a second test with `self_study_hours = 6`, `self_study_credits = 2`, and a formula using `self_study_credits`; assert the calculated score is `2`.

- [ ] **Step 2: Run the regression test**

Run: `php vendor/bin/pest tests/Feature/Evaluation/WorkloadSubjectCreditFormulaTest.php`

Expected: PASS because current merge logic reads only credit fields. This is a characterization test for behavior that the feature must preserve.

- [ ] **Step 3: Inspect the production mapping and confirm no hour field is referenced**

The subject field map must remain:

```php
$normalized['credits'] = match ($creditType) {
    'lecture_credits' => (float) $subject->lecture_credits,
    'lab_credits' => (float) $subject->lab_credits,
    default => (float) $subject->credits,
};
$normalized['lecture_credits'] = (float) $subject->lecture_credits;
$normalized['lab_credits'] = (float) $subject->lab_credits;
$normalized['self_study_credits'] = (float) $subject->self_study_credits;
```

Do not reference any `*_hours` field in workload formula controllers.

If the characterization test fails or either controller references an hour field, stop execution and revise this plan before changing formula production code; that would expose a contradiction with the inspected baseline rather than an expected part of this feature.

- [ ] **Step 4: Run focused subject, import, and JavaScript suites**

Run:

```powershell
php vendor/bin/pest tests/Feature/Subjects tests/Unit/Subjects tests/Feature/Settings/BulkSettingDeleteTest.php tests/Feature/DeleteActionTest.php tests/Feature/CreateModalContractTest.php tests/Feature/Evaluation/WorkloadPageAsyncMutationTest.php tests/Feature/Evaluation/WorkloadSubjectCreditFormulaTest.php tests/Unit/EvaluateeWorkloadModalDataTest.php
npm run test:js
```

Expected: All selected PHP and JavaScript tests pass with zero failures.

- [ ] **Step 5: Format changed PHP files and verify formatting**

Run:

```powershell
vendor/bin/pint app/Models/Subject.php app/Support/EvaluateeWorkloadModalData.php app/Support/Subjects app/Data/Subjects app/Services/Subjects app/Http/Requests/Workload app/Http/Controllers/Workload/SubjectController.php database/migrations/2026_08_06_000001_add_hours_to_subjects_table.php tests/Feature/Subjects tests/Unit/Subjects tests/Unit/EvaluateeWorkloadModalDataTest.php
git diff --check
```

Expected: Pint exits `0`; `git diff --check` prints no errors.

- [ ] **Step 6: Run full project verification**

Run:

```powershell
php vendor/bin/pest
npm run test:js
npm run build
```

Expected: Each command exits `0` with no test failures or build errors. If an unrelated pre-existing dirty-worktree change causes failure, record the exact failing test or build error and rerun all feature-focused commands to separate feature status from unrelated workspace status.

- [ ] **Step 7: Review the implementation against every spec requirement**

Confirm all thirteen testing requirements and every out-of-scope constraint in `docs/superpowers/specs/2026-08-06-subject-hours-and-evaluation-credits-design.md`. Inspect `git diff --stat` and `git status --short` to ensure no unrelated user files were staged or modified by this work.

- [ ] **Step 8: Commit final regression coverage or fixes**

```powershell
git add tests/Feature/Evaluation/WorkloadSubjectCreditFormulaTest.php
git commit -m "test: guard subject credit formula inputs"
```
