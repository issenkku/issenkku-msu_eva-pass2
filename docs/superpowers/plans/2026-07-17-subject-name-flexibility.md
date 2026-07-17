# Subject Name Flexibility Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Allow subjects to have a Thai name, an English name, or both, without the application's former 255-character validation limit, across manual forms and Excel import.

**Architecture:** Introduce one `SubjectName` domain helper for normalization, the at-least-one invariant, and display fallback. Widen both database columns to nullable `TEXT`, enforce the invariant at manual and import write boundaries, and route all user-facing displays through the same fallback behavior.

**Tech Stack:** Laravel 11, PHP 8.3, Eloquent, Blade, vanilla JavaScript, Pest, SQLite test database, MySQL-compatible migrations, PhpSpreadsheet/Maatwebsite Excel.

## Global Constraints

- A subject must have at least one non-blank value in `name_th` or `name_en` after trimming.
- Do not detect or restrict the language used in either field.
- Do not apply `max:255` or any other application-level character-count limit to subject names.
- Store each value only in the language field supplied by the user; never copy between `name_th` and `name_en`.
- Blank or whitespace-only name values normalize to `null`.
- Prefer `name_th` for display; fall back to `name_en` when the Thai name is absent.
- Excel export preserves the two stored language fields without filling the blank field.
- Do not change subject-code, credit, workbook-header, file-size, or row-count rules.
- Preserve all unrelated dirty files in the working tree.

---

## File Map

- Create `app/Support/Subjects/SubjectName.php`: normalize names, check the at-least-one invariant, expose the shared error message, and select the display name.
- Create `database/migrations/2026_07_17_000002_expand_subject_names.php`: change both name columns to nullable `TEXT` and guard rollback from data loss.
- Modify `app/Models/Subject.php`: normalize both stored name attributes and expose `display_name`.
- Modify `app/Http/Requests/Workload/StoreSubjectRequest.php`: normalize manual-create names and enforce at least one.
- Modify `app/Http/Requests/Workload/UpdateSubjectRequest.php`: apply the same rule while preserving partial-update semantics.
- Modify `app/Data/Subjects/SubjectImportRow.php`: allow nullable Thai and English names.
- Modify `app/Services/Subjects/SubjectImportRowValidator.php`: accept either name, remove the 255-character checks, and report one combined name error.
- Modify `app/Exports/Subjects/SubjectInstructionsSheet.php`: document the new name rule.
- Modify `app/Http/Controllers/Workload/SubjectController.php`: sort by the available display name.
- Modify `resources/views/components/subject-modal.blade.php`: remove the Thai-only required marker and add group guidance/error semantics.
- Modify `resources/views/subjects/partials/index-script.blade.php`: validate the two name inputs as a group.
- Modify `resources/views/subjects/partials/index-table-section.blade.php`: display and announce the fallback name without duplicating English.
- Modify `resources/views/subjects/imports/partials/tables.blade.php`: show English-only rows in preview.
- Modify `app/Support/EvaluateeWorkloadViewData.php`: use the fallback name in existing workload rows.
- Modify `app/Support/EvaluateeWorkloadModalData.php`: provide primary and secondary display names to the subject picker.
- Modify `resources/views/evaluatee/partials/workload-entry-modal-subject-section.blade.php`: render those display fields without duplication.
- Create `tests/Feature/Subjects/SubjectNameFlexibilityTest.php`: cover schema, model, manual HTTP, list, sort, and workload-picker behavior.
- Modify `tests/Unit/Subjects/SubjectImportRowValidatorTest.php`: cover English-only, long, and missing names.
- Modify `tests/Unit/Subjects/SubjectWorkbookExportTest.php`: cover the updated workbook instructions.
- Modify `tests/Feature/Subjects/SubjectImportEndToEndTest.php`: cover preview and confirm for an English-only long name.
- Modify `tests/Feature/Subjects/SubjectImportUiTest.php`: cover preview fallback.
- Modify `tests/Feature/CreateModalContractTest.php`: cover the modal's group validation contract.

---

### Task 1: Name Domain and Database Schema

**Files:**
- Create: `app/Support/Subjects/SubjectName.php`
- Create: `database/migrations/2026_07_17_000002_expand_subject_names.php`
- Modify: `app/Models/Subject.php`
- Create: `tests/Feature/Subjects/SubjectNameFlexibilityTest.php`

**Interfaces:**
- Produces: `SubjectName::normalize(mixed $value): ?string`
- Produces: `SubjectName::hasAtLeastOne(mixed $nameTh, mixed $nameEn): bool`
- Produces: `SubjectName::display(mixed $nameTh, mixed $nameEn): string`
- Produces: `SubjectName::REQUIRED_MESSAGE`
- Produces: Eloquent attribute `$subject->display_name`

- [ ] **Step 1: Write the failing schema and model test**

Create `tests/Feature/Subjects/SubjectNameFlexibilityTest.php`:

```php
<?php

use App\Models\Subject;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

function flexibleSubjectPayload(array $overrides = []): array
{
    return array_replace([
        'code' => 'FLEX101',
        'name_th' => 'ชื่อไทย',
        'name_en' => 'English name',
        'credits' => 3,
        'lecture_credits' => 2,
        'lab_credits' => 1,
        'self_study_credits' => 0,
        'is_active' => true,
    ], $overrides);
}

function flexibleSubjectAdmin(): User
{
    Role::findOrCreate('admin');
    $admin = User::factory()->create(['status' => 'active']);
    $admin->assignRole('admin');

    return $admin;
}

test('subject names use nullable text storage and an English display fallback', function () {
    $longEnglishName = trim(str_repeat('Environmental Health ', 20));
    $subject = Subject::create(flexibleSubjectPayload([
        'name_th' => '   ',
        'name_en' => "  {$longEnglishName}  ",
    ]));

    expect(Schema::getColumnType('subjects', 'name_th'))->toBe('text')
        ->and(Schema::getColumnType('subjects', 'name_en'))->toBe('text')
        ->and($subject->fresh()->name_th)->toBeNull()
        ->and($subject->fresh()->name_en)->toBe($longEnglishName)
        ->and($subject->fresh()->display_name)->toBe($longEnglishName);
});
```

- [ ] **Step 2: Run the test to verify it fails**

Run:

```powershell
vendor\bin\pest tests/Feature/Subjects/SubjectNameFlexibilityTest.php --compact
```

Expected: FAIL because `name_th` is still a non-null `VARCHAR(255)` and `display_name` does not exist.

- [ ] **Step 3: Add the shared subject-name helper**

Create `app/Support/Subjects/SubjectName.php`:

```php
<?php

namespace App\Support\Subjects;

final class SubjectName
{
    public const REQUIRED_MESSAGE = 'กรุณากรอกชื่อรายวิชาภาษาไทยหรือภาษาอังกฤษอย่างน้อยหนึ่งช่อง';

    public static function normalize(mixed $value): ?string
    {
        if ($value !== null && ! is_string($value) && ! is_numeric($value)) {
            return null;
        }

        $normalized = trim((string) ($value ?? ''));

        return $normalized === '' ? null : $normalized;
    }

    public static function hasAtLeastOne(mixed $nameTh, mixed $nameEn): bool
    {
        return self::normalize($nameTh) !== null || self::normalize($nameEn) !== null;
    }

    public static function display(mixed $nameTh, mixed $nameEn): string
    {
        return self::normalize($nameTh) ?? self::normalize($nameEn) ?? '';
    }
}
```

- [ ] **Step 4: Add the widening migration with a lossless rollback guard**

Create `database/migrations/2026_07_17_000002_expand_subject_names.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->text('name_th')->nullable()->change();
            $table->text('name_en')->nullable()->change();
        });
    }

    public function down(): void
    {
        $cannotRestoreOldSchema = DB::table('subjects')
            ->select(['name_th', 'name_en'])
            ->cursor()
            ->contains(fn (object $subject): bool => $subject->name_th === null
                || mb_strlen((string) $subject->name_th) > 255
                || mb_strlen((string) ($subject->name_en ?? '')) > 255);

        if ($cannotRestoreOldSchema) {
            throw new RuntimeException('Cannot shrink subject names without losing English-only or long names.');
        }

        Schema::table('subjects', function (Blueprint $table) {
            $table->string('name_th', 255)->nullable(false)->change();
            $table->string('name_en', 255)->nullable()->change();
        });
    }
};
```

- [ ] **Step 5: Normalize names and expose the display accessor on the model**

Import `App\Support\Subjects\SubjectName` in `app/Models/Subject.php`, then add these accessors before `casts()`:

```php
protected function nameTh(): Attribute
{
    return Attribute::make(set: fn (mixed $value): ?string => SubjectName::normalize($value));
}

protected function nameEn(): Attribute
{
    return Attribute::make(set: fn (mixed $value): ?string => SubjectName::normalize($value));
}

protected function displayName(): Attribute
{
    return Attribute::get(
        fn (): string => SubjectName::display($this->name_th, $this->name_en),
    );
}
```

- [ ] **Step 6: Run the focused test**

Run:

```powershell
vendor\bin\pest tests/Feature/Subjects/SubjectNameFlexibilityTest.php --compact
```

Expected: `1 passed`.

- [ ] **Step 7: Commit the domain and schema change**

```powershell
git add app/Support/Subjects/SubjectName.php app/Models/Subject.php database/migrations/2026_07_17_000002_expand_subject_names.php tests/Feature/Subjects/SubjectNameFlexibilityTest.php
git commit -m "feat: support flexible subject name storage"
```

---

### Task 2: Manual Create and Update Validation

**Files:**
- Modify: `app/Http/Requests/Workload/StoreSubjectRequest.php`
- Modify: `app/Http/Requests/Workload/UpdateSubjectRequest.php`
- Modify: `tests/Feature/Subjects/SubjectNameFlexibilityTest.php`

**Interfaces:**
- Consumes: `SubjectName::normalize()` and `SubjectName::hasAtLeastOne()` from Task 1.
- Produces: identical server-side name rules for create and update requests.

- [ ] **Step 1: Append failing manual-write tests**

Append to `tests/Feature/Subjects/SubjectNameFlexibilityTest.php`:

```php
test('manual create accepts an English-only name longer than 255 characters', function () {
    $longEnglishName = trim(str_repeat('Public Health Administration ', 15));

    $this->actingAs(flexibleSubjectAdmin(), 'web')
        ->post(route('subjects.store'), flexibleSubjectPayload([
            'name_th' => ' ',
            'name_en' => " {$longEnglishName} ",
        ]))
        ->assertSessionDoesntHaveErrors();

    $subject = Subject::where('code', 'FLEX101')->firstOrFail();
    expect($subject->name_th)->toBeNull()
        ->and($subject->name_en)->toBe($longEnglishName);
});

test('manual create rejects a subject with both names blank', function () {
    $this->actingAs(flexibleSubjectAdmin(), 'web')
        ->post(route('subjects.store'), flexibleSubjectPayload([
            'name_th' => ' ',
            'name_en' => '',
        ]))
        ->assertSessionHasErrors([
            'name_th' => 'กรุณากรอกชื่อรายวิชาภาษาไทยหรือภาษาอังกฤษอย่างน้อยหนึ่งช่อง',
        ]);

    expect(Subject::where('code', 'FLEX101')->exists())->toBeFalse();
});

test('manual update can replace a Thai name with an English-only long name', function () {
    $subject = Subject::create(flexibleSubjectPayload());
    $longEnglishName = trim(str_repeat('Environmental and Occupational Health ', 10));

    $this->actingAs(flexibleSubjectAdmin(), 'web')
        ->put(route('subjects.update', $subject->id), [
            'name_th' => '',
            'name_en' => $longEnglishName,
        ])
        ->assertSessionDoesntHaveErrors();

    expect($subject->fresh()->name_th)->toBeNull()
        ->and($subject->fresh()->name_en)->toBe($longEnglishName);
});
```

- [ ] **Step 2: Run the tests to verify the old rules fail**

Run:

```powershell
vendor\bin\pest tests/Feature/Subjects/SubjectNameFlexibilityTest.php --compact
```

Expected: the English-only create/update cases fail on `name_th`, and the long name fails `max:255`.

- [ ] **Step 3: Update create-request normalization and rules**

In `StoreSubjectRequest.php`, import `App\Support\Subjects\SubjectName` and `Illuminate\Validation\Validator`. Add both normalized names to the existing `$normalized` array:

```php
'name_th' => is_scalar($this->input('name_th')) || $this->input('name_th') === null
    ? SubjectName::normalize($this->input('name_th'))
    : $this->input('name_th'),
'name_en' => is_scalar($this->input('name_en')) || $this->input('name_en') === null
    ? SubjectName::normalize($this->input('name_en'))
    : $this->input('name_en'),
```

Replace the two name rules with:

```php
'name_th' => ['nullable', 'string'],
'name_en' => ['nullable', 'string'],
```

Add this method below `rules()`:

```php
public function after(): array
{
    return [function (Validator $validator): void {
        if (! SubjectName::hasAtLeastOne($this->input('name_th'), $this->input('name_en'))) {
            $validator->errors()->add('name_th', SubjectName::REQUIRED_MESSAGE);
        }
    }];
}
```

- [ ] **Step 4: Update partial-update normalization and validation**

In `UpdateSubjectRequest.php`, import `App\Models\Subject`, `App\Support\Subjects\SubjectName`, and `Illuminate\Validation\Validator`. In `prepareForValidation()`, normalize each name only when that key was supplied:

```php
foreach (['name_th', 'name_en'] as $field) {
    if ($this->exists($field)) {
        $value = $this->input($field);
        $normalized[$field] = is_scalar($value) || $value === null
            ? SubjectName::normalize($value)
            : $value;
    }
}
```

Replace the name rules with:

```php
'name_th' => ['sometimes', 'nullable', 'string'],
'name_en' => ['sometimes', 'nullable', 'string'],
```

Add an `after()` hook that uses stored values for omitted fields:

```php
public function after(): array
{
    return [function (Validator $validator): void {
        $subject = Subject::find($this->route('id'));
        $nameTh = $this->exists('name_th') ? $this->input('name_th') : $subject?->name_th;
        $nameEn = $this->exists('name_en') ? $this->input('name_en') : $subject?->name_en;

        if (! SubjectName::hasAtLeastOne($nameTh, $nameEn)) {
            $validator->errors()->add('name_th', SubjectName::REQUIRED_MESSAGE);
        }
    }];
}
```

- [ ] **Step 5: Run manual validation and existing code-invariant tests**

Run:

```powershell
vendor\bin\pest tests/Feature/Subjects/SubjectNameFlexibilityTest.php tests/Feature/Subjects/SubjectCodeInvariantTest.php --compact
```

Expected: all tests pass; normalized code behavior remains unchanged.

- [ ] **Step 6: Commit manual validation**

```powershell
git add app/Http/Requests/Workload/StoreSubjectRequest.php app/Http/Requests/Workload/UpdateSubjectRequest.php tests/Feature/Subjects/SubjectNameFlexibilityTest.php
git commit -m "feat: validate either subject name on manual writes"
```

---

### Task 3: Excel Row Validation and Workbook Instructions

**Files:**
- Modify: `app/Data/Subjects/SubjectImportRow.php`
- Modify: `app/Services/Subjects/SubjectImportRowValidator.php`
- Modify: `app/Exports/Subjects/SubjectInstructionsSheet.php`
- Modify: `tests/Unit/Subjects/SubjectImportRowValidatorTest.php`
- Modify: `tests/Unit/Subjects/SubjectWorkbookExportTest.php`

**Interfaces:**
- Consumes: `SubjectName` from Task 1.
- Produces: `SubjectImportRow::$nameTh` and `$nameEn` as `?string`.
- Produces: exactly one combined name error when both workbook cells are blank.

- [ ] **Step 1: Add failing import-validator tests**

Append to `tests/Unit/Subjects/SubjectImportRowValidatorTest.php`:

```php
test('validator accepts an English-only name longer than 255 characters', function () {
    $longEnglishName = trim(str_repeat('Environmental Health ', 20));
    $result = (new SubjectImportRowValidator)->validate(2, validSubjectImportValues([
        1 => ' ',
        2 => " {$longEnglishName} ",
    ]));

    expect($result['errors'])->toBe([])
        ->and($result['row']->nameTh)->toBeNull()
        ->and($result['row']->nameEn)->toBe($longEnglishName);
});

test('validator reports one combined error when both names are blank', function () {
    $result = (new SubjectImportRowValidator)->validate(7, validSubjectImportValues([
        1 => ' ',
        2 => null,
    ]));

    expect($result['row'])->toBeNull()
        ->and($result['errors'])->toHaveCount(1)
        ->and($result['errors'][0]->column)->toBe('ชื่อรายวิชา (ไทย/อังกฤษ)')
        ->and($result['errors'][0]->message)
        ->toBe('กรุณากรอกชื่อรายวิชาภาษาไทยหรือภาษาอังกฤษอย่างน้อยหนึ่งช่อง');
});
```

In `tests/Unit/Subjects/SubjectWorkbookExportTest.php`, extend the blank-template test:

```php
$instructions = collect($sheets[1]->array())->flatten()->implode(' ');

expect($instructions)
    ->toContain('อย่างน้อยหนึ่งช่อง')
    ->not->toContain('255 ตัวอักษร');
```

Also append an export-preservation test:

```php
test('current workbook preserves an English-only long name in its original column', function () {
    $longEnglishName = trim(str_repeat('Environmental Health ', 20));
    $subject = new Subject([
        'code' => 'ENV101', 'name_th' => null, 'name_en' => $longEnglishName,
        'credits' => 3, 'lecture_credits' => 2, 'lab_credits' => 1,
        'self_study_credits' => 0,
    ]);

    $sheet = (new SubjectWorkbookFactory)->current(collect([$subject]))->sheets()[0];

    expect($sheet->array())->toBe([
        ['ENV101', null, $longEnglishName, 3, 2, 1, 0],
    ]);
});
```

- [ ] **Step 2: Run the unit tests to verify failure**

Run:

```powershell
vendor\bin\pest tests/Unit/Subjects/SubjectImportRowValidatorTest.php tests/Unit/Subjects/SubjectWorkbookExportTest.php --compact
```

Expected: FAIL because Thai is required, both names are capped at 255, and the instructions contain the old rule.

- [ ] **Step 3: Make both DTO name properties nullable**

Change the two constructor properties in `SubjectImportRow.php` to:

```php
public ?string $nameTh,
public ?string $nameEn,
```

Keep `attributes()` and `toArray()` unchanged so `null` is preserved in previews and commits.

- [ ] **Step 4: Replace the validator's separate name checks with the shared invariant**

Import `App\Support\Subjects\SubjectName` in `SubjectImportRowValidator.php`. Replace the current name extraction and 255-character checks with:

```php
$nameTh = SubjectName::normalize($values[1] ?? null);
$nameEn = SubjectName::normalize($values[2] ?? null);

if (! SubjectName::hasAtLeastOne($nameTh, $nameEn)) {
    $errors[] = new SubjectImportError(
        $excelRow,
        $code === '' ? null : $code,
        'ชื่อรายวิชา (ไทย/อังกฤษ)',
        null,
        SubjectName::REQUIRED_MESSAGE,
    );
}
```

Construct the valid DTO with `$nameTh` and `$nameEn` directly:

```php
return ['row' => new SubjectImportRow(
    $excelRow, $code, $nameTh, $nameEn,
    $numbers[3], $numbers[4], $numbers[5], $numbers[6],
), 'errors' => []];
```

- [ ] **Step 5: Rewrite the two instruction rows**

In `SubjectInstructionsSheet::array()`, use these rules for the Thai and English rows:

```php
['ชื่อรายวิชา (ไทย)', 'วิทยาการคอมพิวเตอร์', 'ไม่บังคับแยกช่อง; ต้องมีชื่อไทยหรืออังกฤษอย่างน้อยหนึ่งช่อง และรองรับข้อความทุกภาษา'],
['ชื่อรายวิชา (อังกฤษ)', 'Computer Science', 'ไม่บังคับแยกช่อง; ต้องมีชื่อไทยหรืออังกฤษอย่างน้อยหนึ่งช่อง และช่องว่างหมายถึงล้างค่าเดิม'],
```

- [ ] **Step 6: Run validator, reader, and export unit tests**

Run:

```powershell
vendor\bin\pest tests/Unit/Subjects/SubjectImportRowValidatorTest.php tests/Unit/Subjects/SubjectWorkbookReaderTest.php tests/Unit/Subjects/SubjectWorkbookExportTest.php --compact
```

Expected: all subject workbook unit tests pass; credit validation remains unchanged.

- [ ] **Step 7: Commit Excel name validation**

```powershell
git add app/Data/Subjects/SubjectImportRow.php app/Services/Subjects/SubjectImportRowValidator.php app/Exports/Subjects/SubjectInstructionsSheet.php tests/Unit/Subjects/SubjectImportRowValidatorTest.php tests/Unit/Subjects/SubjectWorkbookExportTest.php
git commit -m "feat: accept either subject name in workbooks"
```

---

### Task 4: Excel Preview and Confirm Workflow

**Files:**
- Modify: `resources/views/subjects/imports/partials/tables.blade.php`
- Modify: `tests/Feature/Subjects/SubjectImportEndToEndTest.php`
- Modify: `tests/Feature/Subjects/SubjectImportUiTest.php`

**Interfaces:**
- Consumes: nullable names in `SubjectImportRow::toArray()` from Task 3.
- Produces: preview display fallback `name_th ?: name_en`.
- Verifies: existing `SubjectImportCommitter` persists an English-only long name without copying it.

- [ ] **Step 1: Add a failing end-to-end import test**

Append to `SubjectImportEndToEndTest.php`:

```php
test('English-only long names survive preview and confirm without copying languages', function () {
    $admin = e2eAdmin();
    $longEnglishName = trim(str_repeat('Environmental Health and Safety ', 12));
    $response = $this->actingAs($admin, 'web')->post(route('subjects.import.preview.store'), [
        'import_file' => e2eUpload([['ENV101', '', $longEnglishName, 3, 2, 1, 0]]),
    ])->assertRedirect();
    $token = tokenFromRedirect($response);

    $this->actingAs($admin, 'web')
        ->get(route('subjects.import.preview.show', $token))
        ->assertOk()
        ->assertSee($longEnglishName);

    $this->actingAs($admin, 'web')
        ->post(route('subjects.import.confirm', $token), ['selected_codes' => []])
        ->assertRedirect(route('subjects.index'));

    $subject = Subject::where('code', 'ENV101')->firstOrFail();
    expect($subject->name_th)->toBeNull()
        ->and($subject->name_en)->toBe($longEnglishName);
});
```

- [ ] **Step 2: Make the preview contract test use an English-only row**

In `SubjectImportUiTest.php`, add this item to the `new` preview rows:

```php
['row' => ['excel_row' => 6, 'code' => 'EN100', 'name_th' => null, 'name_en' => 'English Only']],
```

Add `'EN100', 'English Only'` to the final `toContain(...)` expectation.

- [ ] **Step 3: Run the workflow tests to verify the blank preview cell**

Run:

```powershell
vendor\bin\pest tests/Feature/Subjects/SubjectImportEndToEndTest.php tests/Feature/Subjects/SubjectImportUiTest.php --compact
```

Expected: the end-to-end test fails because the preview renders only `name_th`.

- [ ] **Step 4: Render the available name in new and unchanged preview tables**

Replace the preview name cell in `resources/views/subjects/imports/partials/tables.blade.php` with:

```blade
<td>{{ $item['row']['name_th'] ?: $item['row']['name_en'] }}</td>
```

Do not alter diff rendering: it must continue to show each language field independently, including `(ว่าง)` when clearing one.

- [ ] **Step 5: Run import service and workflow regression tests**

Run:

```powershell
vendor\bin\pest tests/Feature/Subjects/SubjectImportPreviewTest.php tests/Feature/Subjects/SubjectImportCommitterTest.php tests/Feature/Subjects/SubjectImportEndToEndTest.php tests/Feature/Subjects/SubjectImportUiTest.php --compact
```

Expected: all tests pass.

- [ ] **Step 6: Commit preview and confirm support**

```powershell
git add resources/views/subjects/imports/partials/tables.blade.php tests/Feature/Subjects/SubjectImportEndToEndTest.php tests/Feature/Subjects/SubjectImportUiTest.php
git commit -m "feat: preview English-only subject imports"
```

---

### Task 5: Manual Form Group Validation

**Files:**
- Modify: `resources/views/components/subject-modal.blade.php`
- Modify: `resources/views/subjects/partials/index-script.blade.php`
- Modify: `tests/Feature/CreateModalContractTest.php`

**Interfaces:**
- Consumes: the same Thai error copy as `SubjectName::REQUIRED_MESSAGE`.
- Produces: `#subjectNameHelp` and `#subjectNameError` as the accessible group guidance and error targets.
- Produces: client-side validity when either `#name_th` or `#name_en` is non-blank.

- [ ] **Step 1: Extend the modal contract test**

After rendering `$modalHtml` in the subject-modal test, add:

```php
expect($modalHtml)
    ->toContain('id="subjectNameHelp"')
    ->toContain('id="subjectNameError"')
    ->toContain('กรอกชื่อรายวิชาภาษาไทยหรือภาษาอังกฤษอย่างน้อยหนึ่งช่อง')
    ->not->toMatch('/id="name_(?:th|en)"[^>]*\srequired/');

$subjectScript = file_get_contents(resource_path('views/subjects/partials/index-script.blade.php'));
expect($subjectScript)
    ->toContain("document.getElementById('name_en')")
    ->toContain("document.getElementById('subjectNameError')")
    ->toContain("nameThValue === '' && nameEnValue === ''");
```

- [ ] **Step 2: Run the modal test to verify it fails**

Run:

```powershell
vendor\bin\pest tests/Feature/CreateModalContractTest.php --filter="subjects index" --compact
```

Expected: FAIL because Thai is still individually required and no group error target exists.

- [ ] **Step 3: Update the modal markup**

In `subject-modal.blade.php`, remove the required asterisk and `required` attribute from `name_th`. Keep both inputs as text fields, add `aria-describedby="subjectNameHelp subjectNameError"` to each, and place this guidance after the English input:

```blade
<div id="subjectNameHelp" class="form-text">
    กรอกชื่อรายวิชาภาษาไทยหรือภาษาอังกฤษอย่างน้อยหนึ่งช่อง
</div>
<div class="text-red-500 text-sm mt-1 hidden" id="subjectNameError" role="alert">
    กรุณากรอกชื่อรายวิชาภาษาไทยหรือภาษาอังกฤษอย่างน้อยหนึ่งช่อง
</div>
```

Remove the old `#nameThError` element; neither name input receives `maxlength`.

- [ ] **Step 4: Validate both name inputs as one group in JavaScript**

In `validateForm()`, retrieve `nameEnInput` and `subjectNameError`, include them in the early null guard, and calculate:

```javascript
const nameThValue = nameThInput.value.trim();
const nameEnValue = nameEnInput.value.trim();

if (nameThValue === '' && nameEnValue === '') {
    nameThInput.classList.add('is-invalid');
    nameEnInput.classList.add('is-invalid');
    subjectNameError.style.display = 'block';
    subjectNameError.textContent = 'กรุณากรอกชื่อรายวิชาภาษาไทยหรือภาษาอังกฤษอย่างน้อยหนึ่งช่อง';
    isValid = false;
} else {
    nameThInput.classList.remove('is-invalid');
    nameEnInput.classList.remove('is-invalid');
    subjectNameError.style.display = 'none';
}
```

Replace `nameThError` with `subjectNameError` in `resetForm()`. Register `input` and `blur` listeners for both name inputs:

```javascript
[nameThInput, nameEnInput].forEach((input) => {
    if (!input) return;
    input.addEventListener('input', validateForm);
    input.addEventListener('blur', validateForm);
});
```

- [ ] **Step 5: Run modal, accessibility-contract, and HTTP tests**

Run:

```powershell
vendor\bin\pest tests/Feature/CreateModalContractTest.php tests/Feature/Subjects/SubjectNameFlexibilityTest.php --compact
```

Expected: all tests pass and no inline handlers are introduced.

- [ ] **Step 6: Commit the form behavior**

```powershell
git add resources/views/components/subject-modal.blade.php resources/views/subjects/partials/index-script.blade.php tests/Feature/CreateModalContractTest.php
git commit -m "feat: validate subject names as a form group"
```

---

### Task 6: Display, Search, Sort, and Workload Fallbacks

**Files:**
- Modify: `app/Http/Controllers/Workload/SubjectController.php`
- Modify: `resources/views/subjects/partials/index-table-section.blade.php`
- Modify: `app/Support/EvaluateeWorkloadViewData.php`
- Modify: `app/Support/EvaluateeWorkloadModalData.php`
- Modify: `resources/views/evaluatee/partials/workload-entry-modal-subject-section.blade.php`
- Modify: `tests/Feature/Subjects/SubjectNameFlexibilityTest.php`

**Interfaces:**
- Consumes: `$subject->display_name` from Task 1.
- Produces: workload-modal array keys `display_name` and `secondary_name`.
- Keeps: search across `code`, `name_th`, and `name_en`.

- [ ] **Step 1: Add failing list and workload-picker tests**

Append to `SubjectNameFlexibilityTest.php` and import `App\Support\EvaluateeWorkloadModalData`:

```php
test('subject index displays searches and sorts English-only names', function () {
    Subject::create(flexibleSubjectPayload([
        'code' => 'FLEX-Z', 'name_th' => null, 'name_en' => 'Zulu Health',
    ]));
    Subject::create(flexibleSubjectPayload([
        'code' => 'FLEX-A', 'name_th' => null, 'name_en' => 'Alpha Health',
    ]));
    $admin = flexibleSubjectAdmin();

    $this->actingAs($admin, 'web')
        ->get(route('subjects.index', ['sort' => 'name_asc']))
        ->assertOk()
        ->assertSeeInOrder(['Alpha Health', 'Zulu Health']);

    $this->actingAs($admin, 'web')
        ->get(route('subjects.index', ['search' => 'Zulu']))
        ->assertOk()
        ->assertSee('Zulu Health')
        ->assertDontSee('Alpha Health');
});

test('workload picker exposes one primary English fallback without duplication', function () {
    $subject = new Subject(flexibleSubjectPayload([
        'name_th' => null,
        'name_en' => 'English Only',
    ]));
    $subject->id = 99;

    $modal = EvaluateeWorkloadModalData::build(
        (object) ['groups' => []],
        collect(),
        collect([$subject]),
    );

    expect($modal['subjects'][0]['display_name'])->toBe('English Only')
        ->and($modal['subjects'][0]['secondary_name'])->toBeNull()
        ->and($modal['subjects'][0]['search'])->toContain('english only');
});
```

- [ ] **Step 2: Run the focused tests to verify sort and picker failures**

Run:

```powershell
vendor\bin\pest tests/Feature/Subjects/SubjectNameFlexibilityTest.php --compact
```

Expected: FAIL because name sorting uses only `name_th`, the table renders it directly, and workload payloads lack the new keys.

- [ ] **Step 3: Sort by the first available name**

In `SubjectController::index()`, define the portable expression before the `match`:

```php
$displayNameExpression = "COALESCE(NULLIF(name_th, ''), name_en)";
```

Replace the name sort arms with:

```php
'name_asc' => $subjects->orderByRaw("{$displayNameExpression} ASC")->orderBy('code'),
'name_desc' => $subjects->orderByRaw("{$displayNameExpression} DESC")->orderBy('code'),
```

Leave the existing search closure unchanged because it already searches both fields.

- [ ] **Step 4: Render the fallback in the subject table**

At the start of each row in `index-table-section.blade.php`, derive values that also keep object-based view-contract tests working:

```blade
@php
    $displayName = $subject->display_name ?? $subject->name_th ?? $subject->name_en ?? '';
    $secondaryName = !empty($subject->name_th) ? ($subject->name_en ?? null) : null;
@endphp
```

Use `$displayName` in the checkbox `aria-label` and primary `<strong>` text. Render the secondary line only when `$secondaryName` is non-empty:

```blade
<strong>{{ $subject->code }}: {{ $displayName }}</strong>
@if (!empty($secondaryName))
    <div class="text-muted text-sm">{{ $secondaryName }}</div>
@endif
```

- [ ] **Step 5: Add fallback fields to workload view data**

In `EvaluateeWorkloadModalData`, add these keys to each subject payload:

```php
'display_name' => $subject->display_name,
'secondary_name' => $subject->name_th !== null ? $subject->name_en : null,
```

Keep `name_th`, `name_en`, and the existing combined search string for compatibility.

In `EvaluateeWorkloadViewData`, replace the direct Thai name in `$subjectDisplay` with:

```php
$itemEntry->subject->display_name
    ?? $itemEntry->subject->name_th
    ?? $itemEntry->subject->name_en
    ?? null,
```

- [ ] **Step 6: Render workload picker primary and secondary names once**

Replace the picker name span in `workload-entry-modal-subject-section.blade.php` with:

```blade
<span class="workload-subject-option-name">
    {{ $subjectView['code'] }} {{ $subjectView['display_name'] }}{{ !empty($subjectView['secondary_name']) ? ' ' . $subjectView['secondary_name'] : '' }}
</span>
```

- [ ] **Step 7: Run subject display and evaluatee regression tests**

Run:

```powershell
vendor\bin\pest tests/Feature/Subjects/SubjectNameFlexibilityTest.php tests/Feature/CreateModalContractTest.php tests/Feature/Evaluation/EvaluateeTest.php tests/Feature/Evaluation/PreviousWorkloadImportTest.php --compact
```

Expected: all tests pass; English-only names appear once and can be searched and sorted.

- [ ] **Step 8: Commit display fallbacks**

```powershell
git add app/Http/Controllers/Workload/SubjectController.php resources/views/subjects/partials/index-table-section.blade.php app/Support/EvaluateeWorkloadViewData.php app/Support/EvaluateeWorkloadModalData.php resources/views/evaluatee/partials/workload-entry-modal-subject-section.blade.php tests/Feature/Subjects/SubjectNameFlexibilityTest.php
git commit -m "feat: display available subject names consistently"
```

---

### Task 7: Verification and Local Migration

**Files:**
- Verify only; do not stage or alter unrelated dirty files.

**Interfaces:**
- Consumes all deliverables from Tasks 1–6.
- Produces verification evidence for the merged feature.

- [ ] **Step 1: Run all subject-focused tests**

```powershell
vendor\bin\pest tests/Feature/Subjects tests/Unit/Subjects tests/Feature/CreateModalContractTest.php tests/Feature/Evaluation/EvaluateeTest.php tests/Feature/Evaluation/PreviousWorkloadImportTest.php --compact
```

Expected: all focused tests pass.

- [ ] **Step 2: Run code formatting checks on changed PHP files**

```powershell
$changedPhp = @(git diff --name-only 4151aa6..HEAD -- app database tests | Where-Object { $_.EndsWith('.php') })
vendor\bin\pint --test @changedPhp
```

Expected: `{"tool":"pint","result":"passed"}`. If it fails, run `vendor\bin\pint @changedPhp`, inspect the formatting-only diff, and rerun the check.

- [ ] **Step 3: Run the complete PHP and JavaScript suites**

```powershell
vendor\bin\pest --compact
npm run test:js
```

Expected: all PHP tests and all JavaScript tests pass.

- [ ] **Step 4: Build production assets and check the feature diff**

```powershell
npm run build
git diff 4151aa6..HEAD --check
git status --short
```

Expected: Vite build succeeds, `git diff --check` prints nothing, and only the user's pre-existing unrelated files remain dirty.

- [ ] **Step 5: Apply the migration to the local application database**

First confirm the application is local, then migrate and clear caches:

```powershell
php artisan about --only=environment
php artisan migrate --force
php artisan optimize:clear
php artisan migrate:status | Select-String '2026_07_17_000002'
```

Expected: environment is `local`, migration `2026_07_17_000002_expand_subject_names` is `Ran`, and all caches clear successfully. Do not run the migration automatically if the environment reports `production`.

- [ ] **Step 6: Review commit boundaries and hand off**

```powershell
git log --oneline 4151aa6..HEAD
git status --short
```

Expected: one focused commit per implementation task, no feature files left uncommitted, and the user's unrelated dirty files remain untouched.
