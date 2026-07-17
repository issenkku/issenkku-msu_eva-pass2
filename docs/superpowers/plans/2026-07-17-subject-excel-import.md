# Subject Excel Import Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** เพิ่ม workflow ดาวน์โหลด Template/ข้อมูลปัจจุบัน อัปโหลด `.xlsx` ตรวจ Preview และเพิ่มหรืออัปเดตรายวิชาแบบ all-or-nothing

**Architecture:** Laravel อ่าน workbook ฝั่งเซิร์ฟเวอร์ด้วย PhpSpreadsheet ที่มากับ Maatwebsite Excel แล้วสร้าง Preview snapshot ใน `database` cache store ด้วย opaque token อายุ 30 นาที การยืนยัน claim token แบบ atomic ตรวจ fingerprint ใหม่ และเขียนข้อมูลพร้อม audit log ใน transaction เดียว UI reuse Blade components, Bootstrap modal และ data-hook conventions ที่มีอยู่

**Tech Stack:** PHP 8.2, Laravel 11, Pest 3, Maatwebsite Excel 3.1, PhpSpreadsheet, Spatie Activity Log, Blade, Bootstrap/Tailwind utility classes

## Global Constraints

- รองรับเฉพาะ `.xlsx` ขนาดไม่เกิน 10 MB และไม่เกิน 5,000 แถวข้อมูล
- `code` ต้อง trim และ Unicode-uppercase ก่อน validation, comparison และ persistence
- `name_th` บังคับ, `name_en` ว่างได้และค่าว่างล้างข้อมูลเดิม; ไม่มีข้อจำกัดชุดภาษา
- หน่วยกิตทั้งสี่ช่องเป็นจำนวนเต็มไม่ติดลบ และผลรวมสามช่องย่อยต้องเท่ากับหน่วยกิตรวม
- ข้อผิดพลาดหนึ่งแถวทำให้ยืนยันทั้งไฟล์ไม่ได้; การ commit เป็น all-or-nothing
- Changed rows เริ่มต้นไม่เลือกอัปเดต; new rows เพิ่มอัตโนมัติ; unchanged rows ไม่เขียนซ้ำ
- Import ไม่เปลี่ยน `is_active` หรือ `sort_order` ของข้อมูลเดิม; new rows ใช้ `is_active = true` และต่อท้ายลำดับตามไฟล์
- Preview ใช้ `database` cache store โดยตรง ไม่ใช้ default cache store หรือ session payload
- Preview token ผูก user อายุ 30 นาที single-use; result token ผูก user อายุ 10 นาทีและ pull ครั้งเดียว
- ทุก route ใหม่อยู่ใต้ `auth:sanctum` และ `role:admin`
- UI ต้องใช้ component/pattern เดิม มี keyboard/focus support และไม่มี inline event handler
- ห้ามแตะหรือ commit ไฟล์งานอื่นที่ dirty อยู่ก่อนเริ่มงาน

---

## File Map

### Domain invariant

- Create `app/Support/Subjects/SubjectCode.php` — normalize business identifier
- Modify `app/Models/Subject.php` — enforce normalization at model boundary
- Modify `app/Http/Requests/Workload/StoreSubjectRequest.php` — normalize and validate unique code
- Modify `app/Http/Requests/Workload/UpdateSubjectRequest.php` — normalize and validate unique code while ignoring current ID
- Create `database/migrations/2026_07_17_000001_normalize_and_unique_subject_codes.php` — canonicalize existing codes and add unique index

### Workbook and validation

- Create `app/Data/Subjects/SubjectImportRow.php` — normalized row DTO
- Create `app/Data/Subjects/SubjectImportError.php` — safe row error DTO
- Create `app/Data/Subjects/SubjectWorkbookReadResult.php` — reader result DTO
- Create `app/Exceptions/Subjects/SubjectWorkbookException.php` — file-level validation exception
- Create `app/Support/Subjects/SubjectWorkbookSchema.php` — shared sheet/header/limit constants
- Create `app/Services/Subjects/SubjectImportRowValidator.php` — field validation without I/O
- Create `app/Services/Subjects/SubjectWorkbookReader.php` — sheet/header/formula/row-limit reader
- Create `app/Exports/Subjects/SubjectWorkbookExport.php` — multi-sheet export root
- Create `app/Exports/Subjects/SubjectDataSheet.php` — safe string-bound data sheet
- Create `app/Exports/Subjects/SubjectInstructionsSheet.php` — instructions sheet
- Create `app/Services/Subjects/SubjectWorkbookFactory.php` — template/current-data workbook factory

### Preview, cache, and commit

- Create `app/Services/Subjects/SubjectImportPreviewService.php` — batch classification and diff/fingerprint creation
- Create `app/Services/Subjects/SubjectImportSnapshotStore.php` — owner-scoped 30-minute snapshot cache and atomic claim
- Create `app/Services/Subjects/SubjectImportResultStore.php` — owner-scoped 10-minute one-time result cache
- Create `app/Exceptions/Subjects/StaleSubjectImportException.php` — concurrency conflict
- Create `app/Services/Subjects/SubjectImportCommitter.php` — stale check, transaction, persistence, audit

### HTTP and UI

- Create `app/Http/Requests/Workload/StoreSubjectImportPreviewRequest.php` — `.xlsx` upload validation
- Create `app/Http/Requests/Workload/ConfirmSubjectImportRequest.php` — selected-code validation
- Create `app/Http/Controllers/Workload/SubjectImportController.php` — workflow orchestration
- Modify `app/Http/Controllers/Workload/SubjectController.php` — consume result token for index
- Modify `routes/report.php` — static import routes before `/subjects/{id}` plus token routes
- Modify `resources/views/subjects/index.blade.php` — import button/modal/result includes
- Create `resources/views/subjects/partials/import-modal.blade.php`
- Create `resources/views/subjects/partials/import-result.blade.php`
- Create `resources/views/subjects/partials/import-modal-script.blade.php`
- Create `resources/views/subjects/imports/show.blade.php`
- Create `resources/views/subjects/imports/partials/summary.blade.php`
- Create `resources/views/subjects/imports/partials/tables.blade.php`
- Create `resources/views/subjects/imports/partials/script.blade.php`

### Tests

- Create `tests/Feature/Subjects/SubjectCodeInvariantTest.php`
- Create `tests/Unit/Subjects/SubjectImportRowValidatorTest.php`
- Create `tests/Unit/Subjects/SubjectWorkbookReaderTest.php`
- Create `tests/Unit/Subjects/SubjectWorkbookExportTest.php`
- Create `tests/Feature/Subjects/SubjectImportPreviewTest.php`
- Create `tests/Feature/Subjects/SubjectImportCommitterTest.php`
- Create `tests/Feature/Subjects/SubjectImportHttpTest.php`
- Create `tests/Feature/Subjects/SubjectImportUiTest.php`
- Create `tests/Feature/Subjects/SubjectImportEndToEndTest.php`

---

### Task 1: Enforce the subject-code invariant everywhere

**Files:**
- Create: `app/Support/Subjects/SubjectCode.php`
- Create: `database/migrations/2026_07_17_000001_normalize_and_unique_subject_codes.php`
- Modify: `app/Models/Subject.php`
- Modify: `app/Http/Requests/Workload/StoreSubjectRequest.php`
- Modify: `app/Http/Requests/Workload/UpdateSubjectRequest.php`
- Test: `tests/Feature/Subjects/SubjectCodeInvariantTest.php`

**Interfaces:**
- Produces: `SubjectCode::normalize(mixed $value): string`
- Produces: model-level canonical `Subject::$code`
- Produces: unique database index `subjects_code_unique`

- [ ] **Step 1: Write failing invariant tests**

```php
<?php

use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\QueryException;
use Spatie\Permission\Models\Role;

function subjectPayload(array $overrides = []): array
{
    return array_replace([
        'code' => 'CS101',
        'name_th' => 'วิทยาการคอมพิวเตอร์',
        'name_en' => 'Computer Science',
        'credits' => 3,
        'lecture_credits' => 2,
        'lab_credits' => 1,
        'self_study_credits' => 0,
        'is_active' => true,
    ], $overrides);
}

function subjectAdmin(): User
{
    Role::findOrCreate('admin');
    $admin = User::factory()->create(['status' => 'active']);
    $admin->assignRole('admin');

    return $admin;
}

test('subject model stores a trimmed uppercase code', function () {
    $subject = Subject::create(subjectPayload(['code' => ' cs101 ']));

    expect($subject->fresh()->code)->toBe('CS101');
});

test('manual create rejects a normalized duplicate code', function () {
    Subject::create(subjectPayload());

    $this->actingAs(subjectAdmin(), 'web')
        ->post(route('subjects.store'), subjectPayload(['code' => ' cs101 ']))
        ->assertSessionHasErrors('code');

    expect(Subject::count())->toBe(1);
});

test('manual update ignores itself but rejects another normalized code', function () {
    $first = Subject::create(subjectPayload());
    $second = Subject::create(subjectPayload(['code' => 'CS102']));
    $admin = subjectAdmin();

    $this->actingAs($admin, 'web')
        ->put(route('subjects.update', $first->id), subjectPayload(['code' => ' cs101 ']))
        ->assertSessionDoesntHaveErrors('code');

    $this->actingAs($admin, 'web')
        ->put(route('subjects.update', $second->id), subjectPayload(['code' => 'cs101']))
        ->assertSessionHasErrors('code');
});

test('database unique index rejects direct duplicate writes', function () {
    Subject::create(subjectPayload());

    expect(fn () => Subject::query()->insert(subjectPayload(['code' => 'CS101'])))
        ->toThrow(QueryException::class);
});
```

- [ ] **Step 2: Run the tests and verify red**

Run: `php artisan test tests/Feature/Subjects/SubjectCodeInvariantTest.php`

Expected: FAIL because codes are not normalized and no unique validation/index exists.

- [ ] **Step 3: Add the normalizer, model mutator, request rules, and migration**

```php
<?php
// app/Support/Subjects/SubjectCode.php

namespace App\Support\Subjects;

final class SubjectCode
{
    public static function normalize(mixed $value): string
    {
        return mb_strtoupper(trim((string) $value), 'UTF-8');
    }
}
```

```php
<?php
// app/Models/Subject.php

namespace App\Models;

use App\Support\Subjects\SubjectCode;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'name_th', 'name_en', 'credits', 'lecture_credits',
        'lab_credits', 'self_study_credits', 'sort_order', 'is_active',
    ];

    protected function code(): Attribute
    {
        return Attribute::make(set: fn (mixed $value): string => SubjectCode::normalize($value));
    }
}
```

```php
<?php
// app/Http/Requests/Workload/StoreSubjectRequest.php

namespace App\Http\Requests\Workload;

use App\Support\Subjects\SubjectCode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSubjectRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $normalized = [
            'credits' => (int) ($this->input('credits') ?: 0),
            'lecture_credits' => (int) ($this->input('lecture_credits') ?: 0),
            'lab_credits' => (int) ($this->input('lab_credits') ?: 0),
            'self_study_credits' => (int) ($this->input('self_study_credits') ?: 0),
        ];
        if ($this->exists('code')) {
            $normalized['code'] = SubjectCode::normalize($this->input('code'));
        }
        $this->merge($normalized);
    }

    protected function getRedirectUrl()
    {
        return $this->input('redirect_to') ?: parent::getRedirectUrl();
    }

    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:255', Rule::unique('subjects', 'code')],
            'name_th' => ['required', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'credits' => ['required', 'integer', 'min:0'],
            'lecture_credits' => ['required', 'integer', 'min:0'],
            'lab_credits' => ['required', 'integer', 'min:0'],
            'self_study_credits' => ['required', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
```

```php
<?php
// app/Http/Requests/Workload/UpdateSubjectRequest.php

namespace App\Http\Requests\Workload;

use App\Support\Subjects\SubjectCode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSubjectRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $normalized = [
            'credits' => (int) ($this->input('credits') ?: 0),
            'lecture_credits' => (int) ($this->input('lecture_credits') ?: 0),
            'lab_credits' => (int) ($this->input('lab_credits') ?: 0),
            'self_study_credits' => (int) ($this->input('self_study_credits') ?: 0),
        ];
        if ($this->exists('code')) {
            $normalized['code'] = SubjectCode::normalize($this->input('code'));
        }
        $this->merge($normalized);
    }

    protected function getRedirectUrl()
    {
        return $this->input('redirect_to') ?: parent::getRedirectUrl();
    }

    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'code' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('subjects', 'code')->ignore($this->route('id'))],
            'name_th' => ['sometimes', 'required', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'credits' => ['sometimes', 'required', 'integer', 'min:0'],
            'lecture_credits' => ['sometimes', 'required', 'integer', 'min:0'],
            'lab_credits' => ['sometimes', 'required', 'integer', 'min:0'],
            'self_study_credits' => ['sometimes', 'required', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
```

```php
<?php
// database/migrations/2026_07_17_000001_normalize_and_unique_subject_codes.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $subjects = DB::table('subjects')->select(['id', 'code'])->orderBy('id')->get();
        $normalizedById = $subjects->mapWithKeys(fn ($subject) => [
            $subject->id => mb_strtoupper(trim((string) $subject->code), 'UTF-8'),
        ]);
        $duplicates = $normalizedById->groupBy(fn ($code) => $code)
            ->filter(fn ($ids) => $ids->count() > 1)
            ->keys();

        if ($duplicates->isNotEmpty()) {
            throw new RuntimeException('Duplicate normalized subject codes: '.$duplicates->implode(', '));
        }

        DB::transaction(function () use ($normalizedById): void {
            foreach ($normalizedById as $id => $code) {
                DB::table('subjects')->where('id', $id)->update(['code' => $code]);
            }
        });

        Schema::table('subjects', function (Blueprint $table): void {
            $table->unique('code', 'subjects_code_unique');
        });
    }

    public function down(): void
    {
        Schema::table('subjects', function (Blueprint $table): void {
            $table->dropUnique('subjects_code_unique');
        });
    }
};
```

- [ ] **Step 4: Run focused tests and format**

Run: `php artisan test tests/Feature/Subjects/SubjectCodeInvariantTest.php && vendor/bin/pint --dirty`

Expected: 4 tests PASS; Pint exits 0.

- [ ] **Step 5: Commit the invariant**

```bash
git add app/Support/Subjects/SubjectCode.php app/Models/Subject.php app/Http/Requests/Workload/StoreSubjectRequest.php app/Http/Requests/Workload/UpdateSubjectRequest.php database/migrations/2026_07_17_000001_normalize_and_unique_subject_codes.php tests/Feature/Subjects/SubjectCodeInvariantTest.php
git commit -m "feat: enforce unique subject codes"
```

### Task 2: Model and validate normalized import rows

**Files:**
- Create: `app/Data/Subjects/SubjectImportRow.php`
- Create: `app/Data/Subjects/SubjectImportError.php`
- Create: `app/Data/Subjects/SubjectWorkbookReadResult.php`
- Create: `app/Services/Subjects/SubjectImportRowValidator.php`
- Test: `tests/Unit/Subjects/SubjectImportRowValidatorTest.php`

**Interfaces:**
- Consumes: `SubjectCode::normalize(mixed): string`
- Produces: `SubjectImportRow::attributes(): array<string,mixed>`
- Produces: `SubjectImportRowValidator::validate(int $excelRow, array $values): array{row:?SubjectImportRow,errors:list<SubjectImportError>}`

- [ ] **Step 1: Write failing row-validation tests**

```php
<?php

use App\Services\Subjects\SubjectImportRowValidator;

function validSubjectImportValues(array $overrides = []): array
{
    return array_replace([' cs101 ', ' วิทยาการคอมพิวเตอร์ ', '', 3, 2, 1, 0], $overrides);
}

test('validator returns a normalized row', function () {
    $result = (new SubjectImportRowValidator)->validate(2, validSubjectImportValues());

    expect($result['errors'])->toBe([])
        ->and($result['row']->excelRow)->toBe(2)
        ->and($result['row']->code)->toBe('CS101')
        ->and($result['row']->nameTh)->toBe('วิทยาการคอมพิวเตอร์')
        ->and($result['row']->nameEn)->toBeNull()
        ->and($result['row']->attributes()['credits'])->toBe(3);
});

test('validator reports every invalid field in one pass', function () {
    $result = (new SubjectImportRowValidator)->validate(7, ['', '', null, 4, 2, 1, 0.5]);

    expect($result['row'])->toBeNull()
        ->and(collect($result['errors'])->pluck('column')->all())
        ->toContain('รหัสรายวิชา', 'ชื่อรายวิชา (ไทย)', 'หน่วยกิตศึกษาด้วยตนเอง');
});

test('validator rejects a total that does not equal the three component credits', function () {
    $result = (new SubjectImportRowValidator)->validate(8, validSubjectImportValues([3 => 4]));

    expect($result['row'])->toBeNull()
        ->and(collect($result['errors'])->pluck('column')->all())->toContain('หน่วยกิตรวม');
});

test('validator accepts unicode names without language restrictions', function () {
    $result = (new SubjectImportRowValidator)->validate(3, validSubjectImportValues([
        1 => '情報科学',
        2 => 'علوم الحاسوب',
    ]));

    expect($result['errors'])->toBe([])
        ->and($result['row']->nameTh)->toBe('情報科学')
        ->and($result['row']->nameEn)->toBe('علوم الحاسوب');
});
```

- [ ] **Step 2: Run the tests and verify red**

Run: `php artisan test tests/Unit/Subjects/SubjectImportRowValidatorTest.php`

Expected: FAIL with missing DTO/validator classes.

- [ ] **Step 3: Add DTOs and validator**

```php
<?php
// app/Data/Subjects/SubjectImportRow.php

namespace App\Data\Subjects;

final readonly class SubjectImportRow
{
    public function __construct(
        public int $excelRow,
        public string $code,
        public string $nameTh,
        public ?string $nameEn,
        public int $credits,
        public int $lectureCredits,
        public int $labCredits,
        public int $selfStudyCredits,
    ) {}

    public function attributes(): array
    {
        return [
            'code' => $this->code,
            'name_th' => $this->nameTh,
            'name_en' => $this->nameEn,
            'credits' => $this->credits,
            'lecture_credits' => $this->lectureCredits,
            'lab_credits' => $this->labCredits,
            'self_study_credits' => $this->selfStudyCredits,
        ];
    }

    public function toArray(): array
    {
        return ['excel_row' => $this->excelRow, ...$this->attributes()];
    }
}
```

```php
<?php
// app/Data/Subjects/SubjectImportError.php

namespace App\Data\Subjects;

final readonly class SubjectImportError
{
    public function __construct(
        public int $excelRow,
        public ?string $code,
        public string $column,
        public mixed $value,
        public string $message,
    ) {}

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
```

```php
<?php
// app/Data/Subjects/SubjectWorkbookReadResult.php

namespace App\Data\Subjects;

final readonly class SubjectWorkbookReadResult
{
    /** @param list<SubjectImportRow> $rows @param list<SubjectImportError> $errors */
    public function __construct(public array $rows, public array $errors) {}
}
```

```php
<?php
// app/Services/Subjects/SubjectImportRowValidator.php

namespace App\Services\Subjects;

use App\Data\Subjects\SubjectImportError;
use App\Data\Subjects\SubjectImportRow;
use App\Support\Subjects\SubjectCode;

final class SubjectImportRowValidator
{
    private const COLUMNS = [
        'รหัสรายวิชา', 'ชื่อรายวิชา (ไทย)', 'ชื่อรายวิชา (อังกฤษ)', 'หน่วยกิตรวม',
        'หน่วยกิตบรรยาย', 'หน่วยกิตปฏิบัติ', 'หน่วยกิตศึกษาด้วยตนเอง',
    ];

    public function validate(int $excelRow, array $values): array
    {
        $code = SubjectCode::normalize($values[0] ?? null);
        $nameTh = trim((string) ($values[1] ?? ''));
        $nameEnRaw = trim((string) ($values[2] ?? ''));
        $errors = [];

        if ($code === '' || mb_strlen($code) > 255) {
            $errors[] = $this->error($excelRow, $code, 0, $values[0] ?? null, 'ต้องกรอกรหัสไม่เกิน 255 ตัวอักษร');
        }
        if ($nameTh === '' || mb_strlen($nameTh) > 255) {
            $errors[] = $this->error($excelRow, $code, 1, $values[1] ?? null, 'ต้องกรอกชื่อไม่เกิน 255 ตัวอักษร');
        }
        if (mb_strlen($nameEnRaw) > 255) {
            $errors[] = $this->error($excelRow, $code, 2, $values[2] ?? null, 'ชื่อต้องไม่เกิน 255 ตัวอักษร');
        }

        $numbers = [];
        foreach ([3, 4, 5, 6] as $column) {
            $numbers[$column] = $this->integer($values[$column] ?? null);
            if ($numbers[$column] === null) {
                $errors[] = $this->error($excelRow, $code, $column, $values[$column] ?? null, 'ต้องเป็นจำนวนเต็มตั้งแต่ 0 ขึ้นไป');
            }
        }

        if ($numbers[3] !== null && $numbers[4] !== null && $numbers[5] !== null && $numbers[6] !== null
            && $numbers[3] !== $numbers[4] + $numbers[5] + $numbers[6]) {
            $errors[] = $this->error($excelRow, $code, 3, $values[3], 'หน่วยกิตรวมต้องเท่ากับผลรวมของหน่วยกิตย่อย');
        }

        if ($errors !== []) {
            return ['row' => null, 'errors' => $errors];
        }

        return ['row' => new SubjectImportRow(
            $excelRow, $code, $nameTh, $nameEnRaw === '' ? null : $nameEnRaw,
            $numbers[3], $numbers[4], $numbers[5], $numbers[6],
        ), 'errors' => []];
    }

    private function integer(mixed $value): ?int
    {
        if (is_int($value) && $value >= 0) {
            return $value;
        }
        if (is_float($value) && $value >= 0 && floor($value) === $value) {
            return (int) $value;
        }
        if (is_string($value) && preg_match('/^\d+$/', trim($value)) === 1) {
            return (int) trim($value);
        }

        return null;
    }

    private function error(int $row, ?string $code, int $column, mixed $value, string $message): SubjectImportError
    {
        return new SubjectImportError($row, $code ?: null, self::COLUMNS[$column], $value, $message);
    }
}
```

- [ ] **Step 4: Run unit tests and format**

Run: `php artisan test tests/Unit/Subjects/SubjectImportRowValidatorTest.php && vendor/bin/pint --dirty`

Expected: 4 tests PASS; Pint exits 0.

- [ ] **Step 5: Commit row validation**

```bash
git add app/Data/Subjects app/Services/Subjects/SubjectImportRowValidator.php tests/Unit/Subjects/SubjectImportRowValidatorTest.php
git commit -m "feat: validate subject import rows"
```

### Task 3: Read `.xlsx` safely and report all row errors

**Files:**
- Create: `app/Support/Subjects/SubjectWorkbookSchema.php`
- Create: `app/Exceptions/Subjects/SubjectWorkbookException.php`
- Create: `app/Services/Subjects/SubjectWorkbookReader.php`
- Test: `tests/Unit/Subjects/SubjectWorkbookReaderTest.php`

**Interfaces:**
- Consumes: `SubjectImportRowValidator::validate()`
- Produces: `SubjectWorkbookReader::read(string $path): SubjectWorkbookReadResult`
- Produces: `SubjectWorkbookSchema::HEADERS`, `DATA_SHEET`, `MAX_ROWS`

- [ ] **Step 1: Write failing workbook-reader tests**

```php
<?php

use App\Exceptions\Subjects\SubjectWorkbookException;
use App\Services\Subjects\SubjectImportRowValidator;
use App\Services\Subjects\SubjectWorkbookReader;
use App\Support\Subjects\SubjectWorkbookSchema;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

function subjectWorkbook(array $rows, ?array $headers = null): string
{
    $path = tempnam(sys_get_temp_dir(), 'subjects-').'.xlsx';
    $book = new Spreadsheet;
    $sheet = $book->getActiveSheet();
    $sheet->setTitle(SubjectWorkbookSchema::DATA_SHEET);
    $sheet->fromArray($headers ?? SubjectWorkbookSchema::HEADERS, null, 'A1');
    foreach ($rows as $offset => $row) {
        $sheet->fromArray($row, null, 'A'.($offset + 2));
    }
    (new Xlsx($book))->save($path);
    $book->disconnectWorksheets();

    return $path;
}

test('reader returns normalized rows and ignores empty trailing rows', function () {
    $path = subjectWorkbook([[' cs101 ', 'วิทยาการคอมพิวเตอร์', '', 3, 2, 1, 0], ['', '', '', '', '', '', '']]);
    $result = (new SubjectWorkbookReader(new SubjectImportRowValidator))->read($path);
    @unlink($path);

    expect($result->errors)->toBe([])
        ->and($result->rows)->toHaveCount(1)
        ->and($result->rows[0]->code)->toBe('CS101');
});

test('reader rejects an incorrect header contract', function () {
    $path = subjectWorkbook([], ['wrong']);

    expect(fn () => (new SubjectWorkbookReader(new SubjectImportRowValidator))->read($path))
        ->toThrow(SubjectWorkbookException::class, 'หัวคอลัมน์');
    @unlink($path);
});

test('reader reports formulas and duplicate normalized codes with every row number', function () {
    $path = subjectWorkbook([
        ['FORM', 'สูตร', '', '=1+2', 2, 1, 0],
        ['cs101', 'ชื่อหนึ่ง', '', 3, 2, 1, 0],
        [' CS101 ', 'ชื่อสอง', '', 3, 2, 1, 0],
    ]);
    $result = (new SubjectWorkbookReader(new SubjectImportRowValidator))->read($path);
    @unlink($path);

    expect(collect($result->errors)->pluck('message')->implode(' '))
        ->toContain('Formula')
        ->toContain('แถว 3, 4');
});

test('reader rejects more than five thousand non-empty rows', function () {
    $row = ['CS101', 'ชื่อ', '', 3, 2, 1, 0];
    $path = subjectWorkbook(array_fill(0, 5001, $row));

    expect(fn () => (new SubjectWorkbookReader(new SubjectImportRowValidator))->read($path))
        ->toThrow(SubjectWorkbookException::class, '5,000');
    @unlink($path);
});
```

- [ ] **Step 2: Run the tests and verify red**

Run: `php artisan test tests/Unit/Subjects/SubjectWorkbookReaderTest.php`

Expected: FAIL with missing schema/reader/exception classes.

- [ ] **Step 3: Implement the schema, exception, and reader**

```php
<?php
// app/Support/Subjects/SubjectWorkbookSchema.php

namespace App\Support\Subjects;

final class SubjectWorkbookSchema
{
    public const DATA_SHEET = 'ข้อมูลรายวิชา';
    public const INSTRUCTIONS_SHEET = 'คำแนะนำ';
    public const MAX_ROWS = 5000;
    public const HEADERS = [
        'รหัสรายวิชา', 'ชื่อรายวิชา (ไทย)', 'ชื่อรายวิชา (อังกฤษ)', 'หน่วยกิตรวม',
        'หน่วยกิตบรรยาย', 'หน่วยกิตปฏิบัติ', 'หน่วยกิตศึกษาด้วยตนเอง',
    ];
}
```

```php
<?php
// app/Exceptions/Subjects/SubjectWorkbookException.php

namespace App\Exceptions\Subjects;

use RuntimeException;

final class SubjectWorkbookException extends RuntimeException {}
```

```php
<?php
// app/Services/Subjects/SubjectWorkbookReader.php

namespace App\Services\Subjects;

use App\Data\Subjects\SubjectImportError;
use App\Data\Subjects\SubjectWorkbookReadResult;
use App\Exceptions\Subjects\SubjectWorkbookException;
use App\Support\Subjects\SubjectCode;
use App\Support\Subjects\SubjectWorkbookSchema;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Throwable;

final class SubjectWorkbookReader
{
    public function __construct(private SubjectImportRowValidator $validator) {}

    public function read(string $path): SubjectWorkbookReadResult
    {
        try {
            $reader = IOFactory::createReader('Xlsx');
            $reader->setReadDataOnly(false);
            $book = $reader->load($path);
        } catch (Throwable $exception) {
            throw new SubjectWorkbookException('ไม่สามารถเปิดไฟล์ Excel ได้', previous: $exception);
        }

        try {
            $sheet = $book->getSheetByName(SubjectWorkbookSchema::DATA_SHEET);
            if ($sheet === null) {
                throw new SubjectWorkbookException('ไม่พบชีต ข้อมูลรายวิชา');
            }

            $headers = array_map(
                fn (mixed $value): string => trim((string) $value),
                $sheet->rangeToArray('A1:G1', null, true, true, false)[0],
            );
            if ($headers !== SubjectWorkbookSchema::HEADERS || $sheet->getHighestDataColumn() !== 'G') {
                throw new SubjectWorkbookException('หัวคอลัมน์ต้องตรงกับ Template ทั้ง 7 คอลัมน์');
            }

            $rows = [];
            $errors = [];
            $codesByRow = [];
            $nonEmptyRows = 0;
            for ($excelRow = 2; $excelRow <= $sheet->getHighestDataRow(); $excelRow++) {
                $values = $sheet->rangeToArray("A{$excelRow}:G{$excelRow}", null, true, true, false)[0];
                if (collect($values)->every(fn ($value) => trim((string) $value) === '')) {
                    continue;
                }
                $nonEmptyRows++;
                if ($nonEmptyRows > SubjectWorkbookSchema::MAX_ROWS) {
                    throw new SubjectWorkbookException('ไฟล์ต้องมีข้อมูลไม่เกิน 5,000 แถว');
                }
                $codesByRow[$excelRow] = SubjectCode::normalize($values[0] ?? null);

                $formulaFound = false;
                foreach (range('A', 'G') as $offset => $column) {
                    $cell = $sheet->getCell("{$column}{$excelRow}");
                    if ($cell->getDataType() === DataType::TYPE_FORMULA) {
                        $formulaFound = true;
                        $errors[] = new SubjectImportError(
                            $excelRow,
                            trim((string) ($values[0] ?? '')) ?: null,
                            SubjectWorkbookSchema::HEADERS[$offset],
                            $cell->getValue(),
                            'ไม่อนุญาตให้ใช้ Excel Formula',
                        );
                    }
                }
                if ($formulaFound) {
                    continue;
                }

                $validated = $this->validator->validate($excelRow, $values);
                array_push($errors, ...$validated['errors']);
                if ($validated['row'] !== null) {
                    $rows[] = $validated['row'];
                }
            }

            foreach (collect($codesByRow)
                ->filter()
                ->groupBy(fn ($code) => $code, preserveKeys: true)
                ->filter(fn ($group) => $group->count() > 1) as $code => $duplicates) {
                $numbers = $duplicates->keys()->sort()->values()->all();
                foreach ($numbers as $duplicateRow) {
                    $errors[] = new SubjectImportError(
                        $duplicateRow,
                        $code,
                        'รหัสรายวิชา',
                        $code,
                        'รหัสซ้ำภายในไฟล์ที่แถว '.implode(', ', $numbers),
                    );
                }
            }

            return new SubjectWorkbookReadResult($rows, $errors);
        } finally {
            $book->disconnectWorksheets();
        }
    }
}
```

- [ ] **Step 4: Run reader and validator tests**

Run: `php artisan test tests/Unit/Subjects/SubjectWorkbookReaderTest.php tests/Unit/Subjects/SubjectImportRowValidatorTest.php && vendor/bin/pint --dirty`

Expected: 8 tests PASS; Pint exits 0.

- [ ] **Step 5: Commit workbook reading**

```bash
git add app/Support/Subjects/SubjectWorkbookSchema.php app/Exceptions/Subjects/SubjectWorkbookException.php app/Services/Subjects/SubjectWorkbookReader.php tests/Unit/Subjects/SubjectWorkbookReaderTest.php
git commit -m "feat: read and validate subject workbooks"
```

### Task 4: Generate the blank template and editable current-data workbook

**Files:**
- Create: `app/Exports/Subjects/SubjectWorkbookExport.php`
- Create: `app/Exports/Subjects/SubjectDataSheet.php`
- Create: `app/Exports/Subjects/SubjectInstructionsSheet.php`
- Create: `app/Services/Subjects/SubjectWorkbookFactory.php`
- Test: `tests/Unit/Subjects/SubjectWorkbookExportTest.php`

**Interfaces:**
- Consumes: `SubjectWorkbookSchema` and a `Collection<Subject>`
- Produces: `SubjectWorkbookFactory::template(): SubjectWorkbookExport`
- Produces: `SubjectWorkbookFactory::current(Collection $subjects): SubjectWorkbookExport`

- [ ] **Step 1: Write failing workbook-export tests**

```php
<?php

use App\Exports\Subjects\SubjectDataSheet;
use App\Models\Subject;
use App\Services\Subjects\SubjectWorkbookFactory;
use App\Support\Subjects\SubjectWorkbookSchema;

test('blank template has data and instructions sheets without sample data', function () {
    $sheets = (new SubjectWorkbookFactory)->template()->sheets();

    expect($sheets)->toHaveCount(2)
        ->and($sheets[0]->title())->toBe(SubjectWorkbookSchema::DATA_SHEET)
        ->and($sheets[0]->headings())->toBe(SubjectWorkbookSchema::HEADERS)
        ->and($sheets[0]->array())->toBe([])
        ->and($sheets[1]->title())->toBe(SubjectWorkbookSchema::INSTRUCTIONS_SHEET);
});

test('current workbook exports every editable field and excludes status and sort order', function () {
    $subject = new Subject([
        'code' => '=CS101', 'name_th' => 'ชื่อไทย', 'name_en' => 'English',
        'credits' => 3, 'lecture_credits' => 2, 'lab_credits' => 1,
        'self_study_credits' => 0, 'is_active' => false, 'sort_order' => 99,
    ]);
    $sheet = (new SubjectWorkbookFactory)->current(collect([$subject]))->sheets()[0];

    expect($sheet)->toBeInstanceOf(SubjectDataSheet::class)
        ->and($sheet->array())->toBe([['=CS101', 'ชื่อไทย', 'English', 3, 2, 1, 0]]);

    $book = new \PhpOffice\PhpSpreadsheet\Spreadsheet;
    $cell = $book->getActiveSheet()->getCell('A1');
    $sheet->bindValue($cell, '=CS101');
    expect($cell->getDataType())->toBe(\PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
    $book->disconnectWorksheets();
});
```

- [ ] **Step 2: Run the tests and verify red**

Run: `php artisan test tests/Unit/Subjects/SubjectWorkbookExportTest.php`

Expected: FAIL with missing export/factory classes.

- [ ] **Step 3: Implement the multi-sheet workbook**

```php
<?php
// app/Exports/Subjects/SubjectWorkbookExport.php

namespace App\Exports\Subjects;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

final readonly class SubjectWorkbookExport implements WithMultipleSheets
{
    public function __construct(private array $rows) {}

    public function sheets(): array
    {
        return [new SubjectDataSheet($this->rows), new SubjectInstructionsSheet];
    }
}
```

```php
<?php
// app/Exports/Subjects/SubjectDataSheet.php

namespace App\Exports\Subjects;

use App\Support\Subjects\SubjectWorkbookSchema;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

final class SubjectDataSheet extends DefaultValueBinder implements FromArray, WithColumnWidths, WithCustomValueBinder, WithHeadings, WithStyles, WithTitle
{
    public function __construct(private array $rows) {}
    public function array(): array { return $this->rows; }
    public function headings(): array { return SubjectWorkbookSchema::HEADERS; }
    public function title(): string { return SubjectWorkbookSchema::DATA_SHEET; }
    public function columnWidths(): array { return ['A' => 18, 'B' => 34, 'C' => 34, 'D' => 14, 'E' => 18, 'F' => 18, 'G' => 26]; }
    public function styles(Worksheet $sheet): array
    {
        $sheet->freezePane('A2');
        $sheet->setAutoFilter('A1:G1');
        return [1 => ['font' => ['bold' => true]]];
    }
    public function bindValue(Cell $cell, $value): bool
    {
        if (is_string($value)) {
            $cell->setValueExplicit($value, DataType::TYPE_STRING);
            return true;
        }
        return parent::bindValue($cell, $value);
    }
}
```

```php
<?php
// app/Exports/Subjects/SubjectInstructionsSheet.php

namespace App\Exports\Subjects;

use App\Support\Subjects\SubjectWorkbookSchema;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

final class SubjectInstructionsSheet implements FromArray, WithColumnWidths, WithStyles, WithTitle
{
    public function array(): array
    {
        return [
            ['ฟิลด์', 'ตัวอย่าง', 'กติกา'],
            ['รหัสรายวิชา', 'CS101', 'บังคับ; ระบบตัดช่องว่างและแปลงอักษรอังกฤษเป็นตัวพิมพ์ใหญ่'],
            ['ชื่อรายวิชา (ไทย)', 'วิทยาการคอมพิวเตอร์', 'บังคับ; รองรับ Unicode ไม่เกิน 255 ตัวอักษร'],
            ['ชื่อรายวิชา (อังกฤษ)', 'Computer Science', 'ไม่บังคับ; ช่องว่างหมายถึงล้างค่าเดิม'],
            ['หน่วยกิต', '3 / 2 / 1 / 0', 'จำนวนเต็มไม่ติดลบ; รวมต้องเท่ากับผลรวมสามช่องย่อย'],
            ['ข้อจำกัดไฟล์', '.xlsx', 'ไม่เกิน 10 MB และ 5,000 แถว; ห้ามใช้ Formula'],
        ];
    }
    public function title(): string { return SubjectWorkbookSchema::INSTRUCTIONS_SHEET; }
    public function columnWidths(): array { return ['A' => 28, 'B' => 28, 'C' => 72]; }
    public function styles(Worksheet $sheet): array { return [1 => ['font' => ['bold' => true]]]; }
}
```

```php
<?php
// app/Services/Subjects/SubjectWorkbookFactory.php

namespace App\Services\Subjects;

use App\Exports\Subjects\SubjectWorkbookExport;
use Illuminate\Support\Collection;

final class SubjectWorkbookFactory
{
    public function template(): SubjectWorkbookExport
    {
        return new SubjectWorkbookExport([]);
    }

    public function current(Collection $subjects): SubjectWorkbookExport
    {
        return new SubjectWorkbookExport($subjects->map(fn ($subject) => [
            $subject->code, $subject->name_th, $subject->name_en,
            $subject->credits, $subject->lecture_credits,
            $subject->lab_credits, $subject->self_study_credits,
        ])->all());
    }
}
```

- [ ] **Step 4: Run workbook tests and format**

Run: `php artisan test tests/Unit/Subjects/SubjectWorkbookExportTest.php tests/Unit/Subjects/SubjectWorkbookReaderTest.php && vendor/bin/pint --dirty`

Expected: 6 tests PASS; Pint exits 0.

- [ ] **Step 5: Commit workbook generation**

```bash
git add app/Exports/Subjects app/Services/Subjects/SubjectWorkbookFactory.php tests/Unit/Subjects/SubjectWorkbookExportTest.php
git commit -m "feat: generate subject import workbooks"
```

### Task 5: Classify Preview rows and persist owner-scoped tokens

**Files:**
- Create: `app/Services/Subjects/SubjectImportPreviewService.php`
- Create: `app/Services/Subjects/SubjectImportSnapshotStore.php`
- Create: `app/Services/Subjects/SubjectImportResultStore.php`
- Test: `tests/Feature/Subjects/SubjectImportPreviewTest.php`

**Interfaces:**
- Consumes: `SubjectWorkbookReadResult`
- Produces: `SubjectImportPreviewService::build(SubjectWorkbookReadResult): array`
- Produces: `SubjectImportPreviewService::fingerprint(Subject): string`
- Produces: `SubjectImportSnapshotStore::{put,getForUser,claimForUser,forget}`
- Produces: `SubjectImportResultStore::{put,pullForUser}`

- [ ] **Step 1: Write failing Preview and cache tests**

```php
<?php

use App\Data\Subjects\SubjectImportError;
use App\Data\Subjects\SubjectImportRow;
use App\Data\Subjects\SubjectWorkbookReadResult;
use App\Models\Subject;
use App\Services\Subjects\SubjectImportPreviewService;
use App\Services\Subjects\SubjectImportResultStore;
use App\Services\Subjects\SubjectImportSnapshotStore;
use Carbon\Carbon;

function previewSubjectPayload(array $overrides = []): array
{
    return array_replace([
        'code' => 'CS101', 'name_th' => 'เดิม', 'name_en' => null,
        'credits' => 3, 'lecture_credits' => 2, 'lab_credits' => 1,
        'self_study_credits' => 0, 'is_active' => true, 'sort_order' => 1,
    ], $overrides);
}

function importRow(string $code, string $name, int $row): SubjectImportRow
{
    return new SubjectImportRow($row, $code, $name, null, 3, 2, 1, 0);
}

test('preview classifies new changed unchanged and errors in one batch', function () {
    Subject::create(previewSubjectPayload(['code' => 'CS101', 'name_th' => 'เดิม']));
    Subject::create(previewSubjectPayload(['code' => 'CS102', 'name_th' => 'เหมือนเดิม']));
    $read = new SubjectWorkbookReadResult([
        importRow('CS100', 'ใหม่', 2),
        importRow('CS101', 'เปลี่ยนแล้ว', 3),
        importRow('CS102', 'เหมือนเดิม', 4),
    ], [new SubjectImportError(5, 'BAD', 'หน่วยกิตรวม', 'x', 'ต้องเป็นจำนวนเต็ม')]);

    $preview = (new SubjectImportPreviewService)->build($read);

    expect($preview['new'])->toHaveCount(1)
        ->and($preview['changed'])->toHaveCount(1)
        ->and($preview['changed'][0]['diff']['name_th'])->toBe(['old' => 'เดิม', 'new' => 'เปลี่ยนแล้ว'])
        ->and($preview['unchanged'])->toHaveCount(1)
        ->and($preview['errors'])->toHaveCount(1);
});

test('snapshot tokens are owner scoped expiring and single use', function () {
    $store = app(SubjectImportSnapshotStore::class);
    $token = $store->put(10, 'subjects.xlsx', ['new' => [], 'changed' => [], 'unchanged' => [], 'errors' => []]);

    expect($store->getForUser($token, 11))->toBeNull()
        ->and($store->getForUser($token, 10)['filename'])->toBe('subjects.xlsx')
        ->and($store->claimForUser($token, 10))->not->toBeNull()
        ->and($store->claimForUser($token, 10))->toBeNull();
});

test('result tokens are owner scoped and pulled once', function () {
    $store = app(SubjectImportResultStore::class);
    $token = $store->put(10, ['created' => ['CS100'], 'updated' => [], 'skipped' => [], 'unchanged' => []]);

    expect($store->pullForUser($token, 11))->toBeNull()
        ->and($store->pullForUser($token, 10)['created'])->toBe(['CS100'])
        ->and($store->pullForUser($token, 10))->toBeNull();

    Carbon::setTestNow('2026-07-17 10:00:00');
    $expired = $store->put(10, ['created' => [], 'updated' => [], 'skipped' => [], 'unchanged' => []]);
    Carbon::setTestNow('2026-07-17 10:11:00');
    expect($store->pullForUser($expired, 10))->toBeNull();
    Carbon::setTestNow();
});

test('snapshot expires after thirty minutes', function () {
    Carbon::setTestNow('2026-07-17 10:00:00');
    $store = app(SubjectImportSnapshotStore::class);
    $token = $store->put(10, 'subjects.xlsx', ['new' => [], 'changed' => [], 'unchanged' => [], 'errors' => []]);
    Carbon::setTestNow('2026-07-17 10:31:00');

    expect($store->getForUser($token, 10))->toBeNull();
    Carbon::setTestNow();
});
```

- [ ] **Step 2: Run tests and verify red**

Run: `php artisan test tests/Feature/Subjects/SubjectImportPreviewTest.php`

Expected: FAIL with missing Preview/cache services.

- [ ] **Step 3: Implement classification and fingerprints**

```php
<?php
// app/Services/Subjects/SubjectImportPreviewService.php

namespace App\Services\Subjects;

use App\Data\Subjects\SubjectWorkbookReadResult;
use App\Models\Subject;

final class SubjectImportPreviewService
{
    private const COMPARED = ['name_th', 'name_en', 'credits', 'lecture_credits', 'lab_credits', 'self_study_credits'];

    public function build(SubjectWorkbookReadResult $read): array
    {
        $existing = Subject::query()
            ->whereIn('code', collect($read->rows)->pluck('code')->all())
            ->get()
            ->keyBy('code');
        $preview = ['new' => [], 'changed' => [], 'unchanged' => [], 'errors' => []];

        foreach ($read->rows as $row) {
            $current = $existing->get($row->code);
            if ($current === null) {
                $preview['new'][] = ['row' => $row->toArray()];
                continue;
            }

            $incoming = $row->attributes();
            $currentValues = collect(self::COMPARED)->mapWithKeys(
                fn (string $key) => [$key => $current->{$key}],
            )->all();
            $diff = collect(self::COMPARED)->mapWithKeys(function (string $key) use ($currentValues, $incoming) {
                return $currentValues[$key] === $incoming[$key]
                    ? []
                    : [$key => ['old' => $currentValues[$key], 'new' => $incoming[$key]]];
            })->all();
            $item = [
                'row' => $row->toArray(),
                'current' => ['id' => $current->id, ...$currentValues],
                'fingerprint' => self::fingerprint($current),
                'diff' => $diff,
            ];
            $preview[$diff === [] ? 'unchanged' : 'changed'][] = $item;
        }

        $preview['errors'] = array_map(fn ($error) => $error->toArray(), $read->errors);

        return $preview;
    }

    public static function fingerprint(Subject $subject): string
    {
        return hash('sha256', json_encode([
            'id' => $subject->id,
            'code' => $subject->code,
            ...collect(self::COMPARED)->mapWithKeys(fn ($key) => [$key => $subject->{$key}])->all(),
            'is_active' => $subject->is_active,
            'sort_order' => $subject->sort_order,
            'updated_at' => $subject->updated_at?->toJSON(),
        ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
    }
}
```

- [ ] **Step 4: Implement database-backed snapshot and result stores**

```php
<?php
// app/Services/Subjects/SubjectImportSnapshotStore.php

namespace App\Services\Subjects;

use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

final class SubjectImportSnapshotStore
{
    private function cache(): Repository { return Cache::store('database'); }
    private function key(string $token): string { return "subject-import:snapshot:{$token}"; }

    public function put(int $userId, string $filename, array $preview): string
    {
        $token = Str::random(64);
        $this->cache()->put($this->key($token), [
            'user_id' => $userId,
            'filename' => basename($filename),
            'created_at' => now()->toJSON(),
            'preview' => $preview,
        ], now()->addMinutes(30));

        return $token;
    }

    public function getForUser(string $token, int $userId): ?array
    {
        $snapshot = $this->cache()->get($this->key($token));
        return is_array($snapshot) && $snapshot['user_id'] === $userId ? $snapshot : null;
    }

    public function claimForUser(string $token, int $userId): ?array
    {
        return $this->cache()->lock("subject-import:lock:{$token}", 10)->block(3, function () use ($token, $userId) {
            $snapshot = $this->getForUser($token, $userId);
            if ($snapshot !== null) {
                $this->cache()->forget($this->key($token));
            }
            return $snapshot;
        });
    }

    public function forget(string $token, int $userId): void
    {
        if ($this->getForUser($token, $userId) !== null) {
            $this->cache()->forget($this->key($token));
        }
    }
}
```

```php
<?php
// app/Services/Subjects/SubjectImportResultStore.php

namespace App\Services\Subjects;

use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

final class SubjectImportResultStore
{
    private function cache(): Repository { return Cache::store('database'); }
    private function key(string $token): string { return "subject-import:result:{$token}"; }

    public function put(int $userId, array $result): string
    {
        $token = Str::random(64);
        $this->cache()->put($this->key($token), ['user_id' => $userId, 'result' => $result], now()->addMinutes(10));
        return $token;
    }

    public function pullForUser(string $token, int $userId): ?array
    {
        return $this->cache()->lock("subject-import:result-lock:{$token}", 10)->block(3, function () use ($token, $userId) {
            $payload = $this->cache()->get($this->key($token));
            if (! is_array($payload) || $payload['user_id'] !== $userId) {
                return null;
            }
            $this->cache()->forget($this->key($token));
            return $payload['result'];
        });
    }
}
```

- [ ] **Step 5: Run Preview/cache tests**

Run: `php artisan test tests/Feature/Subjects/SubjectImportPreviewTest.php && vendor/bin/pint --dirty`

Expected: 4 tests PASS; cache values survive separate service calls, expire correctly, and tokens are single-use.

- [ ] **Step 6: Commit Preview infrastructure**

```bash
git add app/Services/Subjects/SubjectImportPreviewService.php app/Services/Subjects/SubjectImportSnapshotStore.php app/Services/Subjects/SubjectImportResultStore.php tests/Feature/Subjects/SubjectImportPreviewTest.php
git commit -m "feat: build subject import previews"
```

### Task 6: Commit selected changes atomically with stale-data protection

**Files:**
- Create: `app/Exceptions/Subjects/StaleSubjectImportException.php`
- Create: `app/Services/Subjects/SubjectImportCommitter.php`
- Test: `tests/Feature/Subjects/SubjectImportCommitterTest.php`

**Interfaces:**
- Consumes: snapshot shape from `SubjectImportPreviewService::build()`
- Consumes: normalized `list<string> $selectedCodes`
- Produces: `SubjectImportCommitter::commit(array $snapshot, array $selectedCodes, User $causer): array{created:list<string>,updated:list<string>,skipped:list<string>,unchanged:list<string>}`
- Throws: `StaleSubjectImportException` before any durable write when Preview no longer matches

- [ ] **Step 1: Write failing commit and stale-data tests**

```php
<?php

use App\Data\Subjects\SubjectImportRow;
use App\Data\Subjects\SubjectWorkbookReadResult;
use App\Exceptions\Subjects\StaleSubjectImportException;
use App\Models\Subject;
use App\Models\User;
use App\Services\Subjects\SubjectImportCommitter;
use App\Services\Subjects\SubjectImportPreviewService;
use Spatie\Activitylog\Models\Activity;

function committerSubject(array $overrides = []): Subject
{
    return Subject::create(array_replace([
        'code' => 'CS101', 'name_th' => 'เดิม', 'name_en' => 'Old',
        'credits' => 3, 'lecture_credits' => 2, 'lab_credits' => 1,
        'self_study_credits' => 0, 'is_active' => false, 'sort_order' => 5,
    ], $overrides));
}

test('committer creates new rows updates selected rows and preserves status and order', function () {
    $existing = committerSubject();
    $preview = (new SubjectImportPreviewService)->build(new SubjectWorkbookReadResult([
        new SubjectImportRow(2, 'CS100', 'ใหม่', null, 3, 2, 1, 0),
        new SubjectImportRow(3, 'CS101', 'เปลี่ยน', null, 3, 2, 1, 0),
    ], []));
    $result = (new SubjectImportCommitter)->commit(
        ['filename' => 'subjects.xlsx', 'token_hash' => 'hashed-token', 'preview' => $preview],
        ['CS101'],
        User::factory()->create(),
    );

    expect($result)->toMatchArray([
        'created' => ['CS100'], 'updated' => ['CS101'], 'skipped' => [], 'unchanged' => [],
    ])
        ->and($existing->fresh()->name_th)->toBe('เปลี่ยน')
        ->and($existing->fresh()->name_en)->toBeNull()
        ->and($existing->fresh()->is_active)->toBeFalse()
        ->and($existing->fresh()->sort_order)->toBe(5)
        ->and(Subject::where('code', 'CS100')->first()->is_active)->toBeTrue()
        ->and(Subject::where('code', 'CS100')->first()->sort_order)->toBe(6)
        ->and(Activity::where('description', 'นำเข้าข้อมูลรายวิชา')->count())->toBe(1)
        ->and(Activity::where('description', 'นำเข้าข้อมูลรายวิชา')->first()->properties->get('preview_token_hash'))->toBe('hashed-token');
});

test('committer leaves unselected changed rows untouched', function () {
    $existing = committerSubject();
    $preview = (new SubjectImportPreviewService)->build(new SubjectWorkbookReadResult([
        new SubjectImportRow(2, 'CS101', 'ไม่เลือก', null, 3, 2, 1, 0),
    ], []));
    $result = (new SubjectImportCommitter)->commit(
        ['filename' => 'subjects.xlsx', 'token_hash' => 'hashed-token', 'preview' => $preview], [], User::factory()->create(),
    );

    expect($existing->fresh()->name_th)->toBe('เดิม')
        ->and($result['skipped'])->toBe(['CS101']);
});

test('stale data rolls back the entire import before creating new rows', function () {
    $existing = committerSubject();
    $preview = (new SubjectImportPreviewService)->build(new SubjectWorkbookReadResult([
        new SubjectImportRow(2, 'CS100', 'ใหม่', null, 3, 2, 1, 0),
        new SubjectImportRow(3, 'CS101', 'จากไฟล์', null, 3, 2, 1, 0),
    ], []));
    $existing->update(['name_th' => 'แก้โดยผู้ใช้อื่น']);

    expect(fn () => (new SubjectImportCommitter)->commit(
        ['filename' => 'subjects.xlsx', 'token_hash' => 'hashed-token', 'preview' => $preview], ['CS101'], User::factory()->create(),
    ))->toThrow(StaleSubjectImportException::class);

    expect(Subject::where('code', 'CS100')->exists())->toBeFalse();
});
```

- [ ] **Step 2: Run tests and verify red**

Run: `php artisan test tests/Feature/Subjects/SubjectImportCommitterTest.php`

Expected: FAIL with missing committer/exception classes.

- [ ] **Step 3: Implement stale exception and transactional committer**

```php
<?php
// app/Exceptions/Subjects/StaleSubjectImportException.php

namespace App\Exceptions\Subjects;

use RuntimeException;

final class StaleSubjectImportException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('ข้อมูลรายวิชาถูกเปลี่ยนหลังสร้าง Preview กรุณาอัปโหลดไฟล์ใหม่');
    }
}
```

```php
<?php
// app/Services/Subjects/SubjectImportCommitter.php

namespace App\Services\Subjects;

use App\Exceptions\Subjects\StaleSubjectImportException;
use App\Models\Subject;
use App\Models\User;
use App\Support\AuditLog;
use App\Support\Subjects\SubjectCode;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

final class SubjectImportCommitter
{
    public function commit(array $snapshot, array $selectedCodes, User $causer): array
    {
        $preview = $snapshot['preview'];
        if ($preview['errors'] !== []) {
            throw new \InvalidArgumentException('Cannot commit a Preview with validation errors.');
        }

        $selected = collect($selectedCodes)->map(fn ($code) => SubjectCode::normalize($code))->unique()->values();
        $allowed = collect($preview['changed'])->pluck('row.code');
        if ($selected->diff($allowed)->isNotEmpty()) {
            throw new \InvalidArgumentException('Selected codes must be changed Preview rows.');
        }

        return DB::transaction(function () use ($snapshot, $preview, $selected, $causer): array {
            $allItems = collect($preview['changed'])->concat($preview['unchanged']);
            $allCodes = collect($preview['new'])->pluck('row.code')->concat($allItems->pluck('row.code'))->values();
            $current = Subject::query()->whereIn('code', $allCodes)->lockForUpdate()->get()->keyBy('code');

            foreach ($preview['new'] as $item) {
                if ($current->has($item['row']['code'])) {
                    throw new StaleSubjectImportException;
                }
            }
            foreach ($allItems as $item) {
                $subject = $current->get($item['row']['code']);
                if ($subject === null || SubjectImportPreviewService::fingerprint($subject) !== $item['fingerprint']) {
                    throw new StaleSubjectImportException;
                }
            }

            $created = [];
            $updated = [];
            $sortOrder = (int) (Subject::max('sort_order') ?? 0);
            foreach ($preview['new'] as $item) {
                $row = $item['row'];
                Subject::create([
                    ...Arr::except($row, ['excel_row']),
                    'is_active' => true,
                    'sort_order' => ++$sortOrder,
                ]);
                $created[] = $row['code'];
            }
            foreach ($preview['changed'] as $item) {
                $code = $item['row']['code'];
                if (! $selected->contains($code)) {
                    continue;
                }
                $current[$code]->update(Arr::except($item['row'], ['excel_row', 'code']));
                $updated[] = $code;
            }

            $result = [
                'created' => $created,
                'updated' => $updated,
                'skipped' => $allowed->diff($selected)->values()->all(),
                'unchanged' => collect($preview['unchanged'])->pluck('row.code')->values()->all(),
            ];
            AuditLog::record('จัดการรายวิชา', 'นำเข้าข้อมูลรายวิชา', [
                'filename' => basename($snapshot['filename']),
                'preview_token_hash' => $snapshot['token_hash'],
                'created_count' => count($result['created']), 'created_codes' => $result['created'],
                'updated_count' => count($result['updated']), 'updated_codes' => $result['updated'],
                'skipped_count' => count($result['skipped']), 'skipped_codes' => $result['skipped'],
                'unchanged_count' => count($result['unchanged']), 'unchanged_codes' => $result['unchanged'],
            ], causer: $causer);

            return $result;
        }, 3);
    }
}
```

- [ ] **Step 4: Run committer and Preview tests**

Run: `php artisan test tests/Feature/Subjects/SubjectImportCommitterTest.php tests/Feature/Subjects/SubjectImportPreviewTest.php && vendor/bin/pint --dirty`

Expected: 6 tests PASS; transaction and audit assertions pass.

- [ ] **Step 5: Commit atomic persistence**

```bash
git add app/Exceptions/Subjects/StaleSubjectImportException.php app/Services/Subjects/SubjectImportCommitter.php tests/Feature/Subjects/SubjectImportCommitterTest.php
git commit -m "feat: commit subject imports atomically"
```

### Task 7: Expose the import workflow through admin-only HTTP routes

**Files:**
- Create: `app/Http/Requests/Workload/StoreSubjectImportPreviewRequest.php`
- Create: `app/Http/Requests/Workload/ConfirmSubjectImportRequest.php`
- Create: `app/Http/Controllers/Workload/SubjectImportController.php`
- Modify: `app/Http/Controllers/Workload/SubjectController.php`
- Modify: `routes/report.php`
- Test: `tests/Feature/Subjects/SubjectImportHttpTest.php`

**Interfaces:**
- Consumes: workbook factory/reader, Preview service, snapshot/result stores, committer
- Produces: route names `subjects.import.template`, `subjects.import.export`, `subjects.import.preview.store`, `subjects.import.preview.show`, `subjects.import.confirm`, `subjects.import.cancel`
- Produces: view variables `preview`, `token`, and index variable `importResult`

- [ ] **Step 1: Write failing route/download/upload/confirm tests**

```php
<?php

use App\Data\Subjects\SubjectImportRow;
use App\Data\Subjects\SubjectWorkbookReadResult;
use App\Models\Subject;
use App\Models\User;
use App\Services\Subjects\SubjectImportPreviewService;
use App\Services\Subjects\SubjectImportSnapshotStore;
use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Spatie\Permission\Models\Role;

function importAdmin(): User
{
    Role::findOrCreate('admin');
    $user = User::factory()->create(['status' => 'active']);
    $user->assignRole('admin');
    return $user;
}

function httpSubjectPayload(array $overrides = []): array
{
    return array_replace([
        'code' => 'CS101', 'name_th' => 'ชื่อเดิม', 'name_en' => null,
        'credits' => 3, 'lecture_credits' => 2, 'lab_credits' => 1,
        'self_study_credits' => 0, 'is_active' => true, 'sort_order' => 1,
    ], $overrides);
}

function httpWorkbook(array $rows): string
{
    $path = tempnam(sys_get_temp_dir(), 'subject-http-').'.xlsx';
    $book = new Spreadsheet;
    $sheet = $book->getActiveSheet();
    $sheet->setTitle(\App\Support\Subjects\SubjectWorkbookSchema::DATA_SHEET);
    $sheet->fromArray(\App\Support\Subjects\SubjectWorkbookSchema::HEADERS, null, 'A1');
    foreach ($rows as $offset => $row) {
        $sheet->fromArray($row, null, 'A'.($offset + 2));
    }
    (new Xlsx($book))->save($path);
    $book->disconnectWorksheets();
    return $path;
}

test('subject import routes require admin', function () {
    $this->get(route('subjects.import.template'))->assertRedirect();
    $this->actingAs(User::factory()->create(), 'web')
        ->get(route('subjects.import.template'))
        ->assertForbidden();
});

test('admin downloads template and all-current-data workbooks', function () {
    Excel::fake();
    $admin = importAdmin();
    Subject::create(httpSubjectPayload());

    $this->actingAs($admin, 'web')->get(route('subjects.import.template'))->assertOk();
    Excel::assertDownloaded('subject_import_template.xlsx');
    $this->actingAs($admin, 'web')->get(route('subjects.import.export'))->assertOk();
    Excel::assertDownloaded('subjects_current.xlsx', fn ($export) => count($export->sheets()[0]->array()) === 1);
});

test('upload rejects non xlsx and files larger than ten megabytes in the named error bag', function () {
    $admin = importAdmin();
    $this->actingAs($admin, 'web')->post(route('subjects.import.preview.store'), [
        'import_file' => UploadedFile::fake()->create('subjects.csv', 10, 'text/csv'),
    ])->assertSessionHasErrorsIn('subjectImport', ['import_file']);
    $this->actingAs($admin, 'web')->post(route('subjects.import.preview.store'), [
        'import_file' => UploadedFile::fake()->create(
            'subjects.xlsx', 10241, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ),
    ])->assertSessionHasErrorsIn('subjectImport', ['import_file']);
});

test('admin uploads a valid workbook and receives a Preview redirect', function () {
    $path = httpWorkbook([['CS100', 'ใหม่', '', 3, 2, 1, 0]]);
    $file = new UploadedFile($path, 'subjects.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

    $response = $this->actingAs(importAdmin(), 'web')
        ->post(route('subjects.import.preview.store'), ['import_file' => $file]);

    $response->assertRedirectContains('/subject-imports/');
    @unlink($path);
});

test('confirm consumes a token and flashes only a result token', function () {
    $admin = importAdmin();
    $preview = (new SubjectImportPreviewService)->build(new SubjectWorkbookReadResult([
        new SubjectImportRow(2, 'CS100', 'ใหม่', null, 3, 2, 1, 0),
    ], []));
    $token = app(SubjectImportSnapshotStore::class)->put($admin->id, 'subjects.xlsx', $preview);

    $this->actingAs($admin, 'web')
        ->post(route('subjects.import.confirm', $token), ['selected_codes' => []])
        ->assertRedirect(route('subjects.index'))
        ->assertSessionHas('subject_import_result_token');

    expect(Subject::where('code', 'CS100')->exists())->toBeTrue()
        ->and(app(SubjectImportSnapshotStore::class)->getForUser($token, $admin->id))->toBeNull();
});
```

- [ ] **Step 2: Run tests and verify red**

Run: `php artisan test tests/Feature/Subjects/SubjectImportHttpTest.php`

Expected: FAIL because routes, requests, and controller do not exist.

- [ ] **Step 3: Add upload and confirm FormRequests**

```php
<?php
// app/Http/Requests/Workload/StoreSubjectImportPreviewRequest.php

namespace App\Http\Requests\Workload;

use Illuminate\Foundation\Http\FormRequest;

final class StoreSubjectImportPreviewRequest extends FormRequest
{
    protected $errorBag = 'subjectImport';
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return ['import_file' => ['required', 'file', 'mimes:xlsx', 'max:10240']];
    }
}
```

```php
<?php
// app/Http/Requests/Workload/ConfirmSubjectImportRequest.php

namespace App\Http\Requests\Workload;

use Illuminate\Foundation\Http\FormRequest;

final class ConfirmSubjectImportRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'selected_codes' => ['nullable', 'array', 'max:5000'],
            'selected_codes.*' => ['string', 'max:255', 'distinct'],
        ];
    }
}
```

- [ ] **Step 4: Add the workflow controller**

```php
<?php
// app/Http/Controllers/Workload/SubjectImportController.php

namespace App\Http\Controllers\Workload;

use App\Exceptions\Subjects\StaleSubjectImportException;
use App\Exceptions\Subjects\SubjectWorkbookException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Workload\ConfirmSubjectImportRequest;
use App\Http\Requests\Workload\StoreSubjectImportPreviewRequest;
use App\Models\Subject;
use App\Services\Subjects\SubjectImportCommitter;
use App\Services\Subjects\SubjectImportPreviewService;
use App\Services\Subjects\SubjectImportResultStore;
use App\Services\Subjects\SubjectImportSnapshotStore;
use App\Services\Subjects\SubjectWorkbookFactory;
use App\Services\Subjects\SubjectWorkbookReader;
use App\Support\Subjects\SubjectCode;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class SubjectImportController extends Controller
{
    public function __construct(
        private SubjectWorkbookFactory $workbooks,
        private SubjectWorkbookReader $reader,
        private SubjectImportPreviewService $previews,
        private SubjectImportSnapshotStore $snapshots,
        private SubjectImportResultStore $results,
        private SubjectImportCommitter $committer,
    ) {}

    public function template(): BinaryFileResponse
    {
        return Excel::download($this->workbooks->template(), 'subject_import_template.xlsx');
    }

    public function export(): BinaryFileResponse
    {
        $subjects = Subject::query()->orderBy('sort_order')->orderBy('id')->get();
        return Excel::download($this->workbooks->current($subjects), 'subjects_current.xlsx');
    }

    public function storePreview(StoreSubjectImportPreviewRequest $request): RedirectResponse
    {
        try {
            $file = $request->file('import_file');
            $read = $this->reader->read($file->getRealPath());
            $preview = $this->previews->build($read);
            $token = $this->snapshots->put($request->user()->id, $file->getClientOriginalName(), $preview);
            return redirect()->route('subjects.import.preview.show', $token);
        } catch (SubjectWorkbookException $exception) {
            return redirect()->route('subjects.index')->withErrors([
                'import_file' => $exception->getMessage(),
            ], 'subjectImport');
        }
    }

    public function showPreview(Request $request, string $token): View
    {
        $snapshot = $this->snapshots->getForUser($token, $request->user()->id);
        abort_if($snapshot === null, 404);
        return view('subjects.imports.show', ['token' => $token, 'preview' => $snapshot['preview']]);
    }

    public function confirm(ConfirmSubjectImportRequest $request, string $token): RedirectResponse
    {
        $snapshot = $this->snapshots->getForUser($token, $request->user()->id);
        abort_if($snapshot === null, 404);
        $selected = collect($request->validated('selected_codes', []))
            ->map(fn ($code) => SubjectCode::normalize($code))->unique()->values()->all();
        $allowed = collect($snapshot['preview']['changed'])->pluck('row.code');

        if ($snapshot['preview']['errors'] !== [] || collect($selected)->diff($allowed)->isNotEmpty()) {
            return back()->withErrors(['selected_codes' => 'Preview มีข้อผิดพลาดหรือรายการที่เลือกไม่ถูกต้อง']);
        }

        $claimed = $this->snapshots->claimForUser($token, $request->user()->id);
        abort_if($claimed === null, 409);
        $claimed['token_hash'] = hash('sha256', $token);

        try {
            $result = $this->committer->commit($claimed, $selected, $request->user());
        } catch (StaleSubjectImportException|QueryException $exception) {
            return redirect()->route('subjects.index')->with('error', $exception instanceof StaleSubjectImportException
                ? $exception->getMessage()
                : 'ไม่สามารถนำเข้าข้อมูลได้ กรุณาสร้าง Preview ใหม่');
        }

        $resultToken = $this->results->put($request->user()->id, $result);
        return redirect()->route('subjects.index')->with('subject_import_result_token', $resultToken);
    }

    public function cancel(Request $request, string $token): RedirectResponse
    {
        $this->snapshots->forget($token, $request->user()->id);
        return redirect()->route('subjects.index');
    }
}
```

- [ ] **Step 5: Register routes and expose one-time results to the index**

```php
// routes/report.php: import the controller and add static routes before Route::get('/{id}', ...).
use App\Http\Controllers\Workload\SubjectImportController;

Route::prefix('subjects')->name('subjects.')->group(function () {
    Route::get('/', [SubjectController::class, 'index'])->name('index');
    Route::get('/import/template', [SubjectImportController::class, 'template'])->name('import.template');
    Route::get('/import/export', [SubjectImportController::class, 'export'])->name('import.export');
    Route::post('/import/preview', [SubjectImportController::class, 'storePreview'])->name('import.preview.store');
    Route::get('/{id}', [SubjectController::class, 'show'])->name('show');
    Route::post('/', [SubjectController::class, 'store'])->name('store');
    Route::post('/reorder', [SubjectController::class, 'reorder'])->name('reorder');
    Route::delete('/bulk-destroy', [SubjectController::class, 'bulkDestroy'])->name('bulk-destroy');
    Route::put('/{id}', [SubjectController::class, 'update'])->name('update');
    Route::delete('/{id}', [SubjectController::class, 'destroy'])->name('destroy');
});

Route::prefix('subject-imports/{token}')->where(['token' => '[A-Za-z0-9]{64}'])->name('subjects.import.')->group(function () {
    Route::get('/', [SubjectImportController::class, 'showPreview'])->name('preview.show');
    Route::post('/confirm', [SubjectImportController::class, 'confirm'])->name('confirm');
    Route::delete('/', [SubjectImportController::class, 'cancel'])->name('cancel');
});
```

```php
// app/Http/Controllers/Workload/SubjectController.php
use App\Services\Subjects\SubjectImportResultStore;

public function index(Request $request, SubjectImportResultStore $results)
{
    if ($request->expectsJson()) {
        return response()->json(Subject::all());
    }

    $sort = $request->input('sort', 'manual');
    $status = $request->input('status');
    $hasSortOrder = $this->hasSortOrderColumn();
    $subjects = Subject::query()
        ->when($request->filled('search'), function ($query) use ($request) {
            $search = trim($request->input('search'));
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('code', 'like', "%{$search}%")
                    ->orWhere('name_th', 'like', "%{$search}%")
                    ->orWhere('name_en', 'like', "%{$search}%");
            });
        })
        ->when($status === 'active', fn ($query) => $query->where('is_active', true))
        ->when($status === 'inactive', fn ($query) => $query->where('is_active', false));

    match ($sort) {
        'manual' => $hasSortOrder
            ? $subjects->orderBy('sort_order')->orderBy('id')
            : $subjects->orderBy('id'),
        'latest' => $subjects->orderByDesc('id'),
        'oldest' => $subjects->orderBy('id'),
        'code_desc' => $subjects->orderByDesc('code'),
        'name_asc' => $subjects->orderBy('name_th'),
        'name_desc' => $subjects->orderByDesc('name_th'),
        default => $subjects->orderBy('code'),
    };

    $subjects = $subjects->paginate(10)->withQueryString();
    $resultToken = $request->session()->pull('subject_import_result_token');
    $importResult = is_string($resultToken)
        ? $results->pullForUser($resultToken, $request->user()->id)
        : null;

    return view('subjects.index', compact('subjects', 'importResult'));
}
```

- [ ] **Step 6: Run HTTP and domain tests**

Run: `php artisan test tests/Feature/Subjects/SubjectImportHttpTest.php tests/Feature/Subjects/SubjectImportCommitterTest.php tests/Feature/Subjects/SubjectCodeInvariantTest.php && vendor/bin/pint --dirty`

Expected: HTTP workflow tests PASS, existing subject routes remain green, Pint exits 0.

- [ ] **Step 7: Commit HTTP orchestration**

```bash
git add app/Http/Requests/Workload/StoreSubjectImportPreviewRequest.php app/Http/Requests/Workload/ConfirmSubjectImportRequest.php app/Http/Controllers/Workload/SubjectImportController.php app/Http/Controllers/Workload/SubjectController.php routes/report.php tests/Feature/Subjects/SubjectImportHttpTest.php
git commit -m "feat: add subject import endpoints"
```

### Task 8: Add the index import modal and one-time result summary

**Files:**
- Modify: `resources/views/subjects/index.blade.php`
- Create: `resources/views/subjects/partials/import-modal.blade.php`
- Create: `resources/views/subjects/partials/import-result.blade.php`
- Create: `resources/views/subjects/partials/import-modal-script.blade.php`
- Test: `tests/Feature/Subjects/SubjectImportUiTest.php`

**Interfaces:**
- Consumes: route names from Task 7 and nullable `$importResult`
- Produces: hooks `data-subject-import-open`, `data-subject-import-drop-zone`, `data-subject-import-file`, `data-subject-import-remove`

- [ ] **Step 1: Write failing index/modal accessibility tests**

```php
<?php

use Illuminate\Pagination\LengthAwarePaginator;

function emptySubjectPaginator(): LengthAwarePaginator
{
    return new LengthAwarePaginator([], 0, 10, 1, ['path' => url('/subjects')]);
}

test('subject index renders import actions and accessible modal hooks', function () {
    $html = view('subjects.index', [
        'subjects' => emptySubjectPaginator(),
        'importResult' => null,
    ])->render();

    expect($html)
        ->toContain('data-subject-import-open')
        ->toContain('id="subjectImportModal"')
        ->toContain('aria-labelledby="subjectImportModalLabel"')
        ->toContain('data-subject-import-drop-zone')
        ->toContain('data-subject-import-file')
        ->toContain(route('subjects.import.template'))
        ->toContain(route('subjects.import.export'))
        ->not->toContain('onclick=')
        ->not->toContain('ondrop=');
});

test('subject index renders complete import result groups', function () {
    $html = view('subjects.index', [
        'subjects' => emptySubjectPaginator(),
        'importResult' => [
            'created' => ['CS100'], 'updated' => ['CS101'],
            'skipped' => ['CS102'], 'unchanged' => ['CS103'],
        ],
    ])->render();

    expect($html)->toContain('CS100', 'CS101', 'CS102', 'CS103')
        ->toContain('นำเข้าข้อมูลรายวิชาสำเร็จ');
});
```

- [ ] **Step 2: Run UI tests and verify red**

Run: `php artisan test tests/Feature/Subjects/SubjectImportUiTest.php`

Expected: FAIL because import partials/hooks are absent.

- [ ] **Step 3: Add the import entry button and partial includes**

```blade
{{-- resources/views/subjects/index.blade.php: inside the existing action row, before เพิ่มรายวิชา --}}
<x-button
    type="secondary"
    buttonType="button"
    text="นำเข้าจาก Excel"
    icon="fas fa-file-import"
    data-subject-import-open />

{{-- Add after <x-subject-modal /> --}}
@include('subjects.partials.import-modal')
@include('subjects.partials.import-result', ['importResult' => $importResult ?? null])
@include('subjects.partials.import-modal-script')
```

- [ ] **Step 4: Create the modal and result partials**

```blade
{{-- resources/views/subjects/partials/import-modal.blade.php --}}
<div class="modal fade" id="subjectImportModal" tabindex="-1" aria-labelledby="subjectImportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title fs-5" id="subjectImportModalLabel">
                    <i class="fas fa-file-import me-2" aria-hidden="true"></i>นำเข้าข้อมูลรายวิชา
                </h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button>
            </div>
            <form method="POST" action="{{ route('subjects.import.preview.store') }}" enctype="multipart/form-data" data-subject-import-form>
                @csrf
                <div class="modal-body">
                    <div class="d-flex flex-wrap gap-2 mb-4">
                        <x-button type="secondary" text="ดาวน์โหลด Template เปล่า" icon="fas fa-download" :href="route('subjects.import.template')" />
                        <x-button type="secondary" text="ส่งออกข้อมูลรายวิชาปัจจุบัน" icon="fas fa-file-export" :href="route('subjects.import.export')" />
                    </div>

                    @if($errors->subjectImport->any())
                        <div class="alert alert-danger" role="alert">
                            @foreach($errors->subjectImport->all() as $message)<div>{{ $message }}</div>@endforeach
                        </div>
                    @endif

                    <label class="form-label fw-semibold" for="subjectImportFile">ไฟล์ Excel (.xlsx)</label>
                    <div class="border rounded p-4 text-center" tabindex="0" role="button"
                         aria-describedby="subjectImportHelp" data-subject-import-drop-zone>
                        <i class="fas fa-cloud-upload-alt fs-2 text-secondary" aria-hidden="true"></i>
                        <p class="mb-2">ลากไฟล์มาวาง หรือกดเพื่อเลือกไฟล์</p>
                        <input id="subjectImportFile" class="visually-hidden" type="file" name="import_file"
                               accept=".xlsx,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
                               data-subject-import-file required>
                        <div id="subjectImportHelp" class="form-text">ไม่เกิน 10 MB และ 5,000 แถว</div>
                    </div>
                    <div class="mt-3 d-none" data-subject-import-selection aria-live="polite">
                        <span data-subject-import-filename></span>
                        <button type="button" class="btn btn-link text-danger" data-subject-import-remove>นำไฟล์ออก</button>
                    </div>
                </div>
                <div class="modal-footer">
                    <x-button type="secondary" buttonType="button" text="ยกเลิก" data-bs-dismiss="modal" />
                    <x-button type="primary" buttonType="submit" text="ตรวจสอบข้อมูล" icon="fas fa-search" disabled data-subject-import-submit />
                </div>
            </form>
        </div>
    </div>
</div>
```

```blade
{{-- resources/views/subjects/partials/import-result.blade.php --}}
@if($importResult)
    <section class="alert alert-success" role="status" aria-labelledby="subjectImportResultTitle">
        <h2 id="subjectImportResultTitle" class="h5">นำเข้าข้อมูลรายวิชาสำเร็จ</h2>
        @foreach([
            'created' => 'เพิ่มใหม่', 'updated' => 'อัปเดต',
            'skipped' => 'ข้าม', 'unchanged' => 'ไม่เปลี่ยนแปลง',
        ] as $key => $label)
            <details class="mt-2">
                <summary>{{ $label }} {{ count($importResult[$key] ?? []) }} รายการ</summary>
                <ul class="mb-0 mt-2">
                    @foreach($importResult[$key] ?? [] as $code)<li>{{ $code }}</li>@endforeach
                </ul>
            </details>
        @endforeach
    </section>
@endif
```

- [ ] **Step 5: Add data-hook-only modal/drop-zone behavior**

```blade
{{-- resources/views/subjects/partials/import-modal-script.blade.php --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalElement = document.getElementById('subjectImportModal');
    const input = document.querySelector('[data-subject-import-file]');
    const zone = document.querySelector('[data-subject-import-drop-zone]');
    const submit = document.querySelector('[data-subject-import-submit]');
    const selection = document.querySelector('[data-subject-import-selection]');
    const filename = document.querySelector('[data-subject-import-filename]');
    if (!modalElement || !input || !zone || !submit || !selection || !filename) return;

    const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
    const setFile = function (file) {
        const valid = file && file.name.toLowerCase().endsWith('.xlsx') && file.size <= 10 * 1024 * 1024;
        submit.disabled = !valid;
        selection.classList.toggle('d-none', !valid);
        filename.textContent = valid ? `${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB)` : '';
        if (!valid && file) window.alert('กรุณาเลือกไฟล์ .xlsx ขนาดไม่เกิน 10 MB');
    };

    document.querySelectorAll('[data-subject-import-open]').forEach(function (button) {
        button.addEventListener('click', function () { modal.show(); });
    });
    zone.addEventListener('click', function () { input.click(); });
    zone.addEventListener('keydown', function (event) {
        if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); input.click(); }
    });
    ['dragenter', 'dragover'].forEach(function (name) {
        zone.addEventListener(name, function (event) { event.preventDefault(); zone.classList.add('border-primary'); });
    });
    ['dragleave', 'drop'].forEach(function (name) {
        zone.addEventListener(name, function (event) { event.preventDefault(); zone.classList.remove('border-primary'); });
    });
    zone.addEventListener('drop', function (event) {
        if (event.dataTransfer.files.length) {
            input.files = event.dataTransfer.files;
            setFile(event.dataTransfer.files[0]);
        }
    });
    input.addEventListener('change', function () { setFile(input.files[0]); });
    document.querySelector('[data-subject-import-remove]')?.addEventListener('click', function () {
        input.value = ''; setFile(null); input.focus();
    });

    @if($errors->subjectImport->any()) modal.show(); @endif
});
</script>
```

- [ ] **Step 6: Run UI and modal contract tests**

Run: `php artisan test tests/Feature/Subjects/SubjectImportUiTest.php tests/Feature/ImportModalTest.php tests/Feature/CreateModalContractTest.php`

Expected: new and existing UI contract tests PASS; no inline handlers are present.

- [ ] **Step 7: Commit index UI**

```bash
git add resources/views/subjects/index.blade.php resources/views/subjects/partials/import-modal.blade.php resources/views/subjects/partials/import-result.blade.php resources/views/subjects/partials/import-modal-script.blade.php tests/Feature/Subjects/SubjectImportUiTest.php
git commit -m "feat: add subject import modal"
```

### Task 9: Build the read-only Preview and conflict-selection page

**Files:**
- Create: `resources/views/subjects/imports/show.blade.php`
- Create: `resources/views/subjects/imports/partials/summary.blade.php`
- Create: `resources/views/subjects/imports/partials/tables.blade.php`
- Create: `resources/views/subjects/imports/partials/script.blade.php`
- Modify: `tests/Feature/Subjects/SubjectImportUiTest.php`

**Interfaces:**
- Consumes: `$token` and `$preview` from `SubjectImportController@showPreview`
- Posts: `selected_codes[]` to `subjects.import.confirm`
- Produces: hooks `data-subject-import-conflict`, `data-subject-import-select-all`, `data-subject-import-select-none`

- [ ] **Step 1: Add failing Preview rendering tests**

```php
test('Preview renders summaries diffs errors and safe selection hooks', function () {
    $preview = [
        'new' => [['row' => ['excel_row' => 2, 'code' => 'CS100', 'name_th' => 'ใหม่']]],
        'changed' => [[
            'row' => ['excel_row' => 3, 'code' => 'CS101', 'name_th' => 'ใหม่'],
            'current' => ['id' => 1, 'name_th' => 'เดิม'],
            'fingerprint' => 'hash',
            'diff' => ['name_th' => ['old' => 'เดิม', 'new' => 'ใหม่']],
        ]],
        'unchanged' => [['row' => ['excel_row' => 4, 'code' => 'CS102', 'name_th' => 'เหมือนเดิม']]],
        'errors' => [['excelRow' => 5, 'code' => 'BAD', 'column' => 'หน่วยกิตรวม', 'value' => 'x', 'message' => 'ต้องเป็นจำนวนเต็ม']],
    ];
    $html = view('subjects.imports.show', ['token' => 'token', 'preview' => $preview])->render();

    expect($html)->toContain('CS100', 'CS101', 'CS102', 'BAD')
        ->toContain('เดิม', 'ใหม่')
        ->toContain('data-subject-import-conflict')
        ->toContain('data-subject-import-select-all')
        ->toContain('data-subject-import-select-none')
        ->toContain('disabled')
        ->not->toContain('onclick=');
});
```

- [ ] **Step 2: Run Preview UI test and verify red**

Run: `php artisan test tests/Feature/Subjects/SubjectImportUiTest.php --filter=Preview`

Expected: FAIL because Preview views do not exist.

- [ ] **Step 3: Create the Preview page and summary cards**

```blade
{{-- resources/views/subjects/imports/show.blade.php --}}
@extends('layouts.app')
@section('title', 'ตรวจสอบข้อมูลรายวิชาก่อนนำเข้า')
@section('content')
<div class="container-fluid">
    <x-header title="ตรวจสอบข้อมูลก่อนนำเข้า" text="ตรวจรายการใหม่และเลือกรายการซ้ำที่ต้องการอัปเดต" icon="fas fa-file-import" />
    @include('subjects.imports.partials.summary')
    <form method="POST" action="{{ route('subjects.import.confirm', $token) }}" data-subject-import-confirm-form>
        @csrf
        @include('subjects.imports.partials.tables')
        <div class="d-flex justify-content-end gap-2 mt-4">
            <x-button type="primary" buttonType="submit" text="ยืนยันการนำเข้า" icon="fas fa-check"
                :disabled="count($preview['errors']) > 0" />
        </div>
    </form>
    <form method="POST" action="{{ route('subjects.import.cancel', $token) }}" class="mt-2 text-end">
        @csrf @method('DELETE')
        <x-button type="secondary" buttonType="submit" text="ยกเลิกและกลับหน้ารายวิชา" icon="fas fa-times" />
    </form>
</div>
@include('subjects.imports.partials.script')
@endsection
```

```blade
{{-- resources/views/subjects/imports/partials/summary.blade.php --}}
<div class="row g-3 mb-4" aria-label="สรุปผลการตรวจสอบ">
    @foreach([
        ['เพิ่มใหม่', count($preview['new']), 'text-bg-success'],
        ['ข้อมูลซ้ำที่เปลี่ยน', count($preview['changed']), 'text-bg-warning'],
        ['ไม่เปลี่ยนแปลง', count($preview['unchanged']), 'text-bg-secondary'],
        ['ข้อผิดพลาด', count($preview['errors']), 'text-bg-danger'],
    ] as [$label, $count, $class])
        <div class="col-12 col-md-6 col-xl-3"><div class="card {{ $class }}"><div class="card-body">
            <div class="fw-semibold">{{ $label }}</div><div class="fs-3">{{ $count }}</div>
        </div></div></div>
    @endforeach
</div>
```

- [ ] **Step 4: Create new/changed/unchanged/error tables**

```blade
{{-- resources/views/subjects/imports/partials/tables.blade.php --}}
@php
$fieldLabels = [
    'name_th' => 'ชื่อรายวิชา (ไทย)', 'name_en' => 'ชื่อรายวิชา (อังกฤษ)',
    'credits' => 'หน่วยกิตรวม', 'lecture_credits' => 'หน่วยกิตบรรยาย',
    'lab_credits' => 'หน่วยกิตปฏิบัติ', 'self_study_credits' => 'หน่วยกิตศึกษาด้วยตนเอง',
];
@endphp

@if($preview['errors'])
<section class="card border-danger mb-4" aria-labelledby="importErrorsTitle">
    <div class="card-header text-bg-danger"><h2 id="importErrorsTitle" class="h5 mb-0">ข้อผิดพลาด</h2></div>
    <div class="table-responsive"><table class="table mb-0"><thead><tr>
        <th>แถว</th><th>รหัส</th><th>คอลัมน์</th><th>ค่าที่พบ</th><th>สาเหตุ</th>
    </tr></thead><tbody>
    @foreach($preview['errors'] as $error)<tr>
        <td>{{ $error['excelRow'] }}</td><td>{{ $error['code'] ?? '-' }}</td><td>{{ $error['column'] }}</td>
        <td>{{ is_scalar($error['value']) ? $error['value'] : '-' }}</td><td>{{ $error['message'] }}</td>
    </tr>@endforeach
    </tbody></table></div>
</section>
@endif

<section class="card mb-4" aria-labelledby="changedTitle">
    <div class="card-header d-flex flex-wrap justify-content-between gap-2">
        <h2 id="changedTitle" class="h5 mb-0">ข้อมูลซ้ำที่เปลี่ยนแปลง</h2>
        <div><button type="button" class="btn btn-sm btn-outline-primary" data-subject-import-select-all>เลือกทั้งหมด</button>
        <button type="button" class="btn btn-sm btn-outline-secondary" data-subject-import-select-none>ไม่เลือกทั้งหมด</button></div>
    </div>
    <div class="table-responsive"><table class="table mb-0"><thead><tr><th>อัปเดต</th><th>แถว</th><th>รหัส</th><th>ค่าที่เปลี่ยน</th></tr></thead><tbody>
    @forelse($preview['changed'] as $item)<tr>
        <td><input class="form-check-input" type="checkbox" name="selected_codes[]" value="{{ $item['row']['code'] }}"
            aria-label="อัปเดตรายวิชา {{ $item['row']['code'] }}" data-subject-import-conflict></td>
        <td>{{ $item['row']['excel_row'] }}</td><td>{{ $item['row']['code'] }}</td><td><ul class="mb-0">
            @foreach($item['diff'] as $field => $change)<li><strong>{{ $fieldLabels[$field] }}</strong>:
                <del>{{ $change['old'] ?? '(ว่าง)' }}</del> → <ins>{{ $change['new'] ?? '(ว่าง)' }}</ins></li>@endforeach
        </ul></td>
    </tr>@empty<tr><td colspan="4" class="text-center">ไม่มีรายการ</td></tr>@endforelse
    </tbody></table></div>
</section>

@foreach([['เพิ่มใหม่', 'new'], ['ไม่เปลี่ยนแปลง', 'unchanged']] as [$title, $key])
<section class="card mb-4"><div class="card-header"><h2 class="h5 mb-0">{{ $title }}</h2></div>
<div class="table-responsive"><table class="table mb-0"><thead><tr><th>แถว</th><th>รหัส</th><th>ชื่อรายวิชา</th></tr></thead><tbody>
@forelse($preview[$key] as $item)<tr><td>{{ $item['row']['excel_row'] }}</td><td>{{ $item['row']['code'] }}</td><td>{{ $item['row']['name_th'] }}</td></tr>
@empty<tr><td colspan="3" class="text-center">ไม่มีรายการ</td></tr>@endforelse
</tbody></table></div></section>
@endforeach
```

- [ ] **Step 5: Add bulk checkbox behavior without inline handlers**

```blade
{{-- resources/views/subjects/imports/partials/script.blade.php --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const conflicts = Array.from(document.querySelectorAll('[data-subject-import-conflict]'));
    document.querySelector('[data-subject-import-select-all]')?.addEventListener('click', function () {
        conflicts.forEach(function (checkbox) { checkbox.checked = true; });
    });
    document.querySelector('[data-subject-import-select-none]')?.addEventListener('click', function () {
        conflicts.forEach(function (checkbox) { checkbox.checked = false; });
    });
});
</script>
```

- [ ] **Step 6: Run UI tests and build frontend assets**

Run: `php artisan test tests/Feature/Subjects/SubjectImportUiTest.php && npm run build`

Expected: all SubjectImportUi tests PASS; Vite build exits 0.

- [ ] **Step 7: Commit Preview UI**

```bash
git add resources/views/subjects/imports tests/Feature/Subjects/SubjectImportUiTest.php
git commit -m "feat: add subject import preview"
```

### Task 10: Lock the complete workflow with end-to-end regression tests

**Files:**
- Create: `tests/Feature/Subjects/SubjectImportEndToEndTest.php`

**Interfaces:**
- Consumes: public HTTP routes only
- Verifies: invalid, stale, owner, cancel, successful result, and one-time result behavior across request boundaries

- [ ] **Step 1: Write end-to-end workflow tests**

```php
<?php

use App\Models\Subject;
use App\Models\User;
use App\Services\Subjects\SubjectImportSnapshotStore;
use App\Support\Subjects\SubjectWorkbookSchema;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Spatie\Permission\Models\Role;

function e2eAdmin(): User
{
    Role::findOrCreate('admin');
    $user = User::factory()->create(['status' => 'active']);
    $user->assignRole('admin');
    return $user;
}

function e2eUpload(array $rows): UploadedFile
{
    $path = tempnam(sys_get_temp_dir(), 'subject-e2e-').'.xlsx';
    $book = new Spreadsheet;
    $sheet = $book->getActiveSheet();
    $sheet->setTitle(SubjectWorkbookSchema::DATA_SHEET);
    $sheet->fromArray(SubjectWorkbookSchema::HEADERS, null, 'A1');
    foreach ($rows as $offset => $row) {
        $sheet->fromArray($row, null, 'A'.($offset + 2));
    }
    (new Xlsx($book))->save($path);
    $book->disconnectWorksheets();

    return new UploadedFile(
        $path,
        'subjects.xlsx',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        null,
        true,
    );
}

function tokenFromRedirect($response): string
{
    return basename(parse_url($response->headers->get('Location'), PHP_URL_PATH));
}

test('invalid row shows every error and cannot be confirmed', function () {
    $admin = e2eAdmin();
    $response = $this->actingAs($admin, 'web')->post(route('subjects.import.preview.store'), [
        'import_file' => e2eUpload([['BAD', '', '', 4, 2, 1, 0]]),
    ])->assertRedirect();
    $token = tokenFromRedirect($response);

    $this->actingAs($admin, 'web')->get(route('subjects.import.preview.show', $token))
        ->assertOk()->assertSee('ข้อผิดพลาด')->assertSee('ชื่อรายวิชา (ไทย)')->assertSee('หน่วยกิตรวม');
    $this->actingAs($admin, 'web')->post(route('subjects.import.confirm', $token), ['selected_codes' => []])
        ->assertSessionHasErrors('selected_codes');
    expect(Subject::count())->toBe(0);
});

test('another admin cannot view or cancel an owned Preview', function () {
    $owner = e2eAdmin();
    $other = e2eAdmin();
    $token = app(SubjectImportSnapshotStore::class)->put($owner->id, 'subjects.xlsx', [
        'new' => [], 'changed' => [], 'unchanged' => [], 'errors' => [],
    ]);

    $this->actingAs($other, 'web')->get(route('subjects.import.preview.show', $token))->assertNotFound();
    $this->actingAs($other, 'web')->delete(route('subjects.import.cancel', $token))->assertRedirect(route('subjects.index'));
    expect(app(SubjectImportSnapshotStore::class)->getForUser($token, $owner->id))->not->toBeNull();
});

test('owner can cancel a Preview and an expired Preview returns not found', function () {
    Carbon::setTestNow('2026-07-17 10:00:00');
    $owner = e2eAdmin();
    $store = app(SubjectImportSnapshotStore::class);
    $cancelToken = $store->put($owner->id, 'cancel.xlsx', ['new' => [], 'changed' => [], 'unchanged' => [], 'errors' => []]);
    $this->actingAs($owner, 'web')->delete(route('subjects.import.cancel', $cancelToken))
        ->assertRedirect(route('subjects.index'));
    expect($store->getForUser($cancelToken, $owner->id))->toBeNull();

    $expiredToken = $store->put($owner->id, 'expired.xlsx', ['new' => [], 'changed' => [], 'unchanged' => [], 'errors' => []]);
    Carbon::setTestNow('2026-07-17 10:31:00');
    $this->actingAs($owner, 'web')->get(route('subjects.import.preview.show', $expiredToken))->assertNotFound();
    Carbon::setTestNow();
});

test('stale Preview prevents every write and consumes the token', function () {
    $admin = e2eAdmin();
    $existing = Subject::create([
        'code' => 'CS101', 'name_th' => 'เดิม', 'name_en' => null,
        'credits' => 3, 'lecture_credits' => 2, 'lab_credits' => 1,
        'self_study_credits' => 0, 'is_active' => true, 'sort_order' => 1,
    ]);
    $response = $this->actingAs($admin, 'web')->post(route('subjects.import.preview.store'), [
        'import_file' => e2eUpload([
            ['CS100', 'ใหม่', '', 3, 2, 1, 0],
            ['CS101', 'จากไฟล์', '', 3, 2, 1, 0],
        ]),
    ])->assertRedirect();
    $token = tokenFromRedirect($response);
    $existing->update(['name_th' => 'แก้ล่าสุด']);

    $this->actingAs($admin, 'web')->post(route('subjects.import.confirm', $token), [
        'selected_codes' => ['CS101'],
    ])->assertRedirect(route('subjects.index'))->assertSessionHas('error');

    expect(Subject::where('code', 'CS100')->exists())->toBeFalse()
        ->and(app(SubjectImportSnapshotStore::class)->getForUser($token, $admin->id))->toBeNull();
});

test('successful import result appears once after redirect', function () {
    $admin = e2eAdmin();
    $response = $this->actingAs($admin, 'web')->post(route('subjects.import.preview.store'), [
        'import_file' => e2eUpload([['CS100', 'ใหม่', '', 3, 2, 1, 0]]),
    ])->assertRedirect();
    $token = tokenFromRedirect($response);

    $this->actingAs($admin, 'web')->post(route('subjects.import.confirm', $token), ['selected_codes' => []])
        ->assertRedirect(route('subjects.index'));
    $this->actingAs($admin, 'web')->get(route('subjects.index'))
        ->assertOk()->assertSee('นำเข้าข้อมูลรายวิชาสำเร็จ')->assertSee('CS100');
    $this->actingAs($admin, 'web')->get(route('subjects.index'))
        ->assertOk()->assertDontSee('นำเข้าข้อมูลรายวิชาสำเร็จ');
});
```

- [ ] **Step 2: Run end-to-end tests**

Run: `php artisan test tests/Feature/Subjects/SubjectImportEndToEndTest.php`

Expected: 5 tests PASS across separate HTTP requests using the database cache store.

- [ ] **Step 3: Run every subject-import test together to catch helper or state leakage**

Run: `php artisan test tests/Unit/Subjects tests/Feature/Subjects`

Expected: all tests PASS with no duplicate global helper functions or order dependency.

- [ ] **Step 4: Run relevant existing regressions**

Run: `php artisan test tests/Feature/CreateModalContractTest.php tests/Feature/ImportModalTest.php tests/Feature/DeleteActionTest.php tests/Feature/Settings/BulkSettingDeleteTest.php tests/Feature/AuditLoggingTest.php`

Expected: all existing tests PASS; subject CRUD, modal, delete, and audit behavior remains intact.

- [ ] **Step 5: Run full verification**

Run: `vendor/bin/pint --test && php artisan test && npm run test:js && npm run build && git log --check --oneline -10`

Expected: Pint exits 0; full PHP and JS suites PASS; Vite build succeeds; the ten feature commits contain no whitespace errors.

- [ ] **Step 6: Commit end-to-end coverage**

```bash
git add tests/Feature/Subjects/SubjectImportEndToEndTest.php
git commit -m "test: cover subject import workflow"
```

---

## Execution Notes

- Execute tasks in order; later tasks rely on exact interfaces introduced earlier.
- Keep each task's commit isolated and stage only the paths listed for that task.
- Do not use the existing user import class as a base: it writes while reading and cannot provide all-or-nothing Preview semantics.
- Use `database` cache store explicitly in both stores even though tests configure `CACHE_STORE=array`.
- Read the framework-managed `UploadedFile` temporary path directly and never call `store()`/`storeAs()`; no raw workbook remains after the request ends.
- If the production migration reports normalized duplicate codes, stop deployment and clean those records; never auto-delete a subject.
