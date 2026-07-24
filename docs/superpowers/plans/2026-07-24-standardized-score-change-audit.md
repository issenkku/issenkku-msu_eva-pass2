# Standardized Score Change Audit Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Make quantity, quality, and support score changes use the same per-item reason and structured-history rules while preserving each score type's existing input UI.

**Architecture:** Add one stateless score-change policy for normalization and reason validation, while retaining separate history tables and recorders for each score type. Controllers snapshot old and new values inside their existing transactions; read models expose normalized history data; Blade components keep the current quantity, quality, and support editors but share history presentation conventions.

**Tech Stack:** Laravel/PHP, Eloquent, SQLite/MySQL migrations, Blade, vanilla JavaScript, Pest, Node test runner

## Global Constraints

- Evaluatees do not need a reason in Assigned/Draft, but every add, change, or removal must create history.
- Evaluators, directors, and managers must supply a non-blank per-item reason for every add, change, or removal.
- Reasons are limited to 2,000 characters.
- No history row is created when the normalized value and related editable data are unchanged.
- Score persistence, evidence, comments, report status, and history remain in one transaction.
- Quantity stays inline, quality stays checkbox-driven, and support stays modal-driven.
- The support history indicator is exclusive to the support table and must work on desktop, mobile, editable, and read-only views.
- The support achievement frontend/backend cap discrepancy remains out of scope.

---

## File Structure

### New files

- `app/Support/ScoreChangePolicy.php` — shared normalization and reason enforcement.
- `app/Support/QualityScoreHistoryRecorder.php` — compares quality snapshots and writes structured history.
- `app/Models/QualityScoreHistory.php` — Eloquent model for quality history.
- `database/migrations/2026_07_24_000001_standardize_score_change_histories.php` — adds quantity reason and creates quality history.
- `resources/views/components/score-change-history-list.blade.php` — shared read-only history presentation for quantity and quality.
- `resources/views/components/score-change-reason-script.blade.php` — client-side per-item reason validation contract.
- `tests/Unit/Support/ScoreChangePolicyTest.php` — shared-policy unit tests.
- `tests/Feature/Evaluation/ScoreChangeHistorySchemaTest.php` — migration/model contract tests.
- `tests/Feature/Evaluation/QualityScoreHistoryFlowTest.php` — quality add/change/remove history across roles.
- `tests/Feature/Evaluation/ScoreChangeTransactionTest.php` — rollback and foreign-criterion coverage.

### Existing files to modify

- `app/Support/QuantityScoreHistoryRecorder.php`
- `app/Models/QuantityScoreHistory.php`
- `app/Services/SupportScoreService.php`
- `app/Services/ReportDataService.php`
- `app/Http/Controllers/Evaluatee/DashboardEvaluateeController.php`
- `app/Http/Controllers/Evaluatee/EvaluationScoreController.php`
- `app/Http/Controllers/EvaluatorScoreController.php`
- `app/Http/Controllers/Director/DirectorScoreController.php`
- `app/Http/Controllers/Manager/ManagerScoreController.php`
- `resources/views/components/unified-evaluation.blade.php`
- `resources/views/components/unified-evaluator.blade.php`
- `resources/views/components/unified-director.blade.php`
- `resources/views/components/support-criteria-table.blade.php`
- `resources/views/components/support-criteria-table-script.blade.php`
- `resources/views/partials/evaluatee-evaluation-script.blade.php`
- `resources/views/partials/evaluation-form-script.blade.php`
- `tests/Feature/Evaluation/EvaluateeTest.php`
- `tests/Feature/Evaluation/EvaluatorTest.php`
- `tests/Feature/Evaluation/DirectorTest.php`
- `tests/Feature/Evaluation/ManagerTest.php`
- `tests/Feature/Evaluation/SupportScoreServiceTest.php`
- `tests/Feature/Evaluation/SupportCriteriaReviewerFlowTest.php`
- `tests/Feature/SupportCriteriaEvaluationViewTest.php`

---

### Task 1: Shared Score Change Policy

**Files:**
- Create: `app/Support/ScoreChangePolicy.php`
- Create: `tests/Unit/Support/ScoreChangePolicyTest.php`

**Interfaces:**
- Produces: `ScoreChangePolicy::numbersDiffer(mixed $before, mixed $after): bool`
- Produces: `ScoreChangePolicy::textsDiffer(mixed $before, mixed $after): bool`
- Produces: `ScoreChangePolicy::validatedReason(bool $changed, mixed $reason, bool $required, string $errorKey): ?string`
- Consumed by: quantity history recorder, quality history recorder, and `SupportScoreService`.

- [ ] **Step 1: Write failing normalization and reason tests**

```php
<?php

use App\Support\ScoreChangePolicy;
use Illuminate\Validation\ValidationException;

test('numeric comparison treats equivalent values as unchanged', function () {
    expect(ScoreChangePolicy::numbersDiffer('4', '4.00'))->toBeFalse()
        ->and(ScoreChangePolicy::numbersDiffer(null, ''))->toBeFalse()
        ->and(ScoreChangePolicy::numbersDiffer(null, 4))->toBeTrue()
        ->and(ScoreChangePolicy::numbersDiffer(4, null))->toBeTrue();
});

test('text comparison trims values before comparison', function () {
    expect(ScoreChangePolicy::textsDiffer(' note ', 'note'))->toBeFalse()
        ->and(ScoreChangePolicy::textsDiffer(null, ''))->toBeFalse()
        ->and(ScoreChangePolicy::textsDiffer('old', 'new'))->toBeTrue();
});

test('reviewer change requires a non blank reason', function () {
    try {
        ScoreChangePolicy::validatedReason(true, ' ', true, 'quantity_list.7.modification_reason');
        $this->fail('Expected ValidationException');
    } catch (ValidationException $exception) {
        expect($exception->errors())->toHaveKey('quantity_list.7.modification_reason');
    }
});

test('evaluatee change may omit reason and unchanged input discards reason', function () {
    expect(ScoreChangePolicy::validatedReason(true, null, false, 'x'))->toBeNull()
        ->and(ScoreChangePolicy::validatedReason(false, 'unused', true, 'x'))->toBeNull();
});
```

- [ ] **Step 2: Run the tests and verify RED**

Run:

```powershell
php vendor/bin/pest tests/Unit/Support/ScoreChangePolicyTest.php
```

Expected: FAIL because `App\Support\ScoreChangePolicy` does not exist.

- [ ] **Step 3: Implement the stateless policy**

```php
<?php

namespace App\Support;

use Illuminate\Validation\ValidationException;

final class ScoreChangePolicy
{
    public static function numbersDiffer(mixed $before, mixed $after): bool
    {
        return self::normalizeNumber($before) !== self::normalizeNumber($after);
    }

    public static function textsDiffer(mixed $before, mixed $after): bool
    {
        return self::normalizeText($before) !== self::normalizeText($after);
    }

    public static function validatedReason(
        bool $changed,
        mixed $reason,
        bool $required,
        string $errorKey
    ): ?string {
        if (! $changed) {
            return null;
        }

        $normalized = self::normalizeText($reason);

        if ($required && $normalized === null) {
            throw ValidationException::withMessages([
                $errorKey => ['กรุณาระบุเหตุผลที่แก้ไขคะแนน'],
            ]);
        }

        if ($normalized !== null && mb_strlen($normalized) > 2000) {
            throw ValidationException::withMessages([
                $errorKey => ['เหตุผลที่แก้ไขคะแนนต้องมีความยาวไม่เกิน 2,000 ตัวอักษร'],
            ]);
        }

        return $normalized;
    }

    private static function normalizeNumber(mixed $value): ?string
    {
        return $value === null || $value === ''
            ? null
            : number_format((float) $value, 2, '.', '');
    }

    private static function normalizeText(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value === '' ? null : $value;
    }
}
```

- [ ] **Step 4: Run the tests and verify GREEN**

Run:

```powershell
php vendor/bin/pest tests/Unit/Support/ScoreChangePolicyTest.php
```

Expected: 4 tests pass.

- [ ] **Step 5: Commit**

```powershell
git add app/Support/ScoreChangePolicy.php tests/Unit/Support/ScoreChangePolicyTest.php
git commit -m "feat: add shared score change policy"
```

---

### Task 2: Quantity and Quality History Schema

**Files:**
- Create: `database/migrations/2026_07_24_000001_standardize_score_change_histories.php`
- Create: `app/Models/QualityScoreHistory.php`
- Modify: `app/Models/QuantityScoreHistory.php`
- Create: `tests/Feature/Evaluation/ScoreChangeHistorySchemaTest.php`

**Interfaces:**
- Produces: nullable `quantity_score_histories.reason`.
- Produces: `quality_score_histories` with nullable old/new score and reason.
- Produces: `QualityScoreHistory::modifierUser()`.

- [ ] **Step 1: Write the failing schema/model test**

```php
<?php

use App\Models\QualityScoreHistory;
use App\Models\QuantityScoreHistory;
use Illuminate\Support\Facades\Schema;

test('score change history schema stores per item reasons', function () {
    expect(Schema::hasColumn('quantity_score_histories', 'reason'))->toBeTrue()
        ->and(Schema::hasTable('quality_score_histories'))->toBeTrue();

    foreach ([
        'report_id',
        'quality_sub_criteria_id',
        'previous_score',
        'new_score',
        'reason',
        'modifier_user_id',
        'modifier_role',
    ] as $column) {
        expect(Schema::hasColumn('quality_score_histories', $column))->toBeTrue();
    }

    expect((new QuantityScoreHistory)->getFillable())->toContain('reason')
        ->and((new QualityScoreHistory)->getFillable())->toContain('previous_score', 'new_score', 'reason');
});
```

- [ ] **Step 2: Run the test and verify RED**

Run:

```powershell
php vendor/bin/pest tests/Feature/Evaluation/ScoreChangeHistorySchemaTest.php
```

Expected: FAIL because the new column/table/model do not exist.

- [ ] **Step 3: Add the migration**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quantity_score_histories', function (Blueprint $table) {
            $table->text('reason')->nullable()->after('new_description');
        });

        Schema::create('quality_score_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained('reports')->cascadeOnDelete();
            $table->foreignId('quality_sub_criteria_id')->constrained('quality_sub_criterias')->cascadeOnDelete();
            $table->decimal('previous_score', 10, 2)->nullable();
            $table->decimal('new_score', 10, 2)->nullable();
            $table->text('reason')->nullable();
            $table->foreignId('modifier_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('modifier_role')->nullable();
            $table->timestamps();
            $table->index(['report_id', 'quality_sub_criteria_id'], 'qlsh_report_sub_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quality_score_histories');
        Schema::table('quantity_score_histories', function (Blueprint $table) {
            $table->dropColumn('reason');
        });
    }
};
```

- [ ] **Step 4: Add model contracts**

Implement `QualityScoreHistory` with fillable fields from the migration, decimal casts for both scores, and `belongsTo` relations to `Reports`, `QualitySubCriteria`, and `User` as `modifierUser`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QualityScoreHistory extends Model
{
    protected $fillable = [
        'report_id',
        'quality_sub_criteria_id',
        'previous_score',
        'new_score',
        'reason',
        'modifier_user_id',
        'modifier_role',
    ];

    protected $casts = [
        'previous_score' => 'decimal:2',
        'new_score' => 'decimal:2',
    ];

    public function report(): BelongsTo
    {
        return $this->belongsTo(Reports::class, 'report_id');
    }

    public function subCriteria(): BelongsTo
    {
        return $this->belongsTo(QualitySubCriteria::class, 'quality_sub_criteria_id');
    }

    public function modifierUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'modifier_user_id');
    }
}
```

Add `'reason'` to `QuantityScoreHistory::$fillable`.

- [ ] **Step 5: Run schema tests and migrations**

Run:

```powershell
php vendor/bin/pest tests/Feature/Evaluation/ScoreChangeHistorySchemaTest.php
```

Expected: PASS.

- [ ] **Step 6: Commit**

```powershell
git add database/migrations/2026_07_24_000001_standardize_score_change_histories.php app/Models/QualityScoreHistory.php app/Models/QuantityScoreHistory.php tests/Feature/Evaluation/ScoreChangeHistorySchemaTest.php
git commit -m "feat: add standardized score history schema"
```

---

### Task 3: Quantity Add, Change, and Removal History

**Files:**
- Modify: `app/Support/QuantityScoreHistoryRecorder.php`
- Modify: the four score controllers listed in File Structure
- Modify: `tests/Feature/Evaluation/EvaluateeTest.php`
- Modify: `tests/Feature/Evaluation/EvaluatorTest.php`
- Modify: `tests/Feature/Evaluation/DirectorTest.php`
- Modify: `tests/Feature/Evaluation/ManagerTest.php`

**Interfaces:**
- Consumes: `ScoreChangePolicy`.
- Produces:

```php
QuantityScoreHistoryRecorder::record(
    int $reportId,
    Collection $oldScores,
    array $newSnapshots,
    array $reasonsBySubCriteriaId,
    ?int $modifierUserId,
    ?string $modifierRole,
    bool $requireReason
): void
```

- `newSnapshots` includes every submitted quantity criterion, including null scores, so removals can be detected.

- [ ] **Step 1: Extend role tests with add/change/remove cases**

For reviewer tests, seed an old score and post each changed item first without a reason:

```php
'quantity_list' => [[
    'quantity_sub_criteria_id' => $subCriteria->id,
    'score_C' => 9,
    'description' => 'ปรับตามผลงานจริง',
    'modification_reason' => '',
]],
```

Assert the response has:

```php
$response->assertSessionHasErrors(
    'quantity_list.0.modification_reason'
);
```

Then post with `modification_reason => 'ตรวจสอบหลักฐานแล้ว'` and assert:

```php
$this->assertDatabaseHas('quantity_score_histories', [
    'report_id' => $report->id,
    'quantity_sub_criteria_id' => $subCriteria->id,
    'previous_score_c' => '7.00',
    'new_score_c' => '9.00',
    'reason' => 'ตรวจสอบหลักฐานแล้ว',
]);
```

Add equivalent tests for `null → value` and `value → null`. Evaluatee tests assert the same history rows with `reason => null`.

- [ ] **Step 2: Run quantity role tests and verify RED**

Run each file separately to avoid the repository's shared SQLite lock:

```powershell
php vendor/bin/pest tests/Feature/Evaluation/EvaluateeTest.php
php vendor/bin/pest tests/Feature/Evaluation/EvaluatorTest.php
php vendor/bin/pest tests/Feature/Evaluation/DirectorTest.php
php vendor/bin/pest tests/Feature/Evaluation/ManagerTest.php
```

Expected: new reason/add/remove assertions fail.

- [ ] **Step 3: Refactor the quantity recorder**

Build old and new maps, iterate their union, compare both score C and description, validate the reason, and create history only for changed entries:

```php
$oldById = $oldScores->keyBy('quantity_sub_criteria_id');
$newById = collect($newSnapshots)->keyBy('subCriteriaId');
$ids = $oldById->keys()->merge($newById->keys())->unique();

foreach ($ids as $subCriteriaId) {
    $old = $oldById->get($subCriteriaId);
    $new = $newById->get($subCriteriaId);
    $changed = ScoreChangePolicy::numbersDiffer($old?->score_C, $new['scoreC'] ?? null)
        || ScoreChangePolicy::textsDiffer($old?->description, $new['description'] ?? null);
    $reason = ScoreChangePolicy::validatedReason(
        $changed,
        $reasonsBySubCriteriaId[$subCriteriaId] ?? null,
        $requireReason,
        "quantity_list.{$subCriteriaId}.modification_reason"
    );

    if (! $changed) {
        continue;
    }

    QuantityScoreHistory::create([
        'report_id' => $reportId,
        'quantity_sub_criteria_id' => $subCriteriaId,
        'previous_score_c' => $old?->score_C,
        'new_score_c' => $new['scoreC'] ?? null,
        'previous_description' => $old?->description,
        'new_description' => $new['description'] ?? null,
        'reason' => $reason,
        'modifier_user_id' => $modifierUserId,
        'modifier_role' => $modifierRole,
    ]);
}
```

- [ ] **Step 4: Update all controller payload rules and snapshots**

Add:

```php
'quantity_list.*.modification_reason' => ['nullable', 'string', 'max:2000'],
```

Build `$quantityReasons` keyed by sub-criterion ID. Add each item to `$newQuantityScores` before skipping null persistence, so a null snapshot represents removal. Pass `false` for the evaluatee controller and `true` for evaluator/director/manager.

Preserve each controller's existing transaction and status logic.

- [ ] **Step 5: Run quantity tests and verify GREEN**

Run the four commands from Step 2. Expected: all quantity history/reason assertions pass; any existing SQLite lock is reported separately and rerun by test name.

- [ ] **Step 6: Commit**

```powershell
git add app/Support/QuantityScoreHistoryRecorder.php app/Http/Controllers/Evaluatee/EvaluationScoreController.php app/Http/Controllers/EvaluatorScoreController.php app/Http/Controllers/Director/DirectorScoreController.php app/Http/Controllers/Manager/ManagerScoreController.php tests/Feature/Evaluation/EvaluateeTest.php tests/Feature/Evaluation/EvaluatorTest.php tests/Feature/Evaluation/DirectorTest.php tests/Feature/Evaluation/ManagerTest.php
git commit -m "feat: standardize quantity score change history"
```

---

### Task 4: Quality Add, Change, and Removal History

**Files:**
- Create: `app/Support/QualityScoreHistoryRecorder.php`
- Modify: the four score controllers
- Create: `tests/Feature/Evaluation/QualityScoreHistoryFlowTest.php`

**Interfaces:**
- Consumes: `ScoreChangePolicy`.
- Produces:

```php
QualityScoreHistoryRecorder::record(
    int $reportId,
    Collection $oldScores,
    array $newSnapshots,
    array $reasonsBySubCriteriaId,
    ?int $modifierUserId,
    ?string $modifierRole,
    bool $requireReason
): void
```

- [ ] **Step 1: Write dataset tests for evaluatee and reviewer roles**

Use a dataset for evaluatee/evaluator/director/manager endpoints. Cover:

```php
[
    'add' => [null, 4.0],
    'change' => [3.0, 4.0],
    'remove' => [3.0, null],
]
```

Reviewer requests without:

```php
'modification_reason' => ' '
```

must return an error at `quality_list.<criterion-id>.modification_reason`. Requests with `ปรับตามผลการตรวจ` must create a row with the expected nullable old/new values. Evaluatee requests create the row with a null reason.

- [ ] **Step 2: Run the new test and verify RED**

```powershell
php vendor/bin/pest tests/Feature/Evaluation/QualityScoreHistoryFlowTest.php
```

Expected: FAIL because recorder/history persistence does not exist.

- [ ] **Step 3: Implement `QualityScoreHistoryRecorder`**

Mirror the union-based quantity algorithm, but compare only the final persisted quality score:

```php
$changed = ScoreChangePolicy::numbersDiffer(
    $old?->score,
    $new['score'] ?? null
);
```

Write `QualityScoreHistory` with old/new score, validated reason, actor, and role.

- [ ] **Step 4: Update controller validation and calls**

Add:

```php
'quality_list.*.modification_reason' => ['nullable', 'string', 'max:2000'],
```

Build a snapshot for every submitted quality sub-criterion after applying the controller's existing per-item/list caps. A blank or unchecked item must remain in the snapshot with `score => null`; only non-null values are persisted to `quality_scores`.

Call the recorder before committing the controller transaction. Pass evaluatee `requireReason=false`; all reviewer controllers pass `true`.

- [ ] **Step 5: Run quality and existing score tests**

```powershell
php vendor/bin/pest tests/Feature/Evaluation/QualityScoreHistoryFlowTest.php
php vendor/bin/pest tests/Feature/ScoreServiceTest.php tests/Unit/Support/ReportScoreSummaryTest.php
```

Expected: all tests pass and score totals remain unchanged.

- [ ] **Step 6: Commit**

```powershell
git add app/Support/QualityScoreHistoryRecorder.php app/Http/Controllers/Evaluatee/EvaluationScoreController.php app/Http/Controllers/EvaluatorScoreController.php app/Http/Controllers/Director/DirectorScoreController.php app/Http/Controllers/Manager/ManagerScoreController.php tests/Feature/Evaluation/QualityScoreHistoryFlowTest.php
git commit -m "feat: record quality score change history"
```

---

### Task 5: Apply the Shared Policy to Support Scores

**Files:**
- Modify: `app/Services/SupportScoreService.php`
- Modify: `tests/Feature/Evaluation/SupportScoreServiceTest.php`
- Modify: `tests/Feature/Evaluation/SupportCriteriaReviewerFlowTest.php`

**Interfaces:**
- Consumes: `ScoreChangePolicy::numbersDiffer()` and `validatedReason()`.
- Preserves: existing `SupportScoreService::persist()` signature and return shape.

- [ ] **Step 1: Add support add/remove regression cases**

Add tests proving:

- evaluatee `null → value` and `value → null` create history without reason;
- reviewer `null → value` and `value → null` require a reason;
- unchanged values create no history.

Assert achieved and weighted old/new values as well as reason/actor/role.

- [ ] **Step 2: Run support tests and verify the new cases fail**

```powershell
php vendor/bin/pest tests/Feature/Evaluation/SupportScoreServiceTest.php tests/Feature/Evaluation/SupportCriteriaReviewerFlowTest.php
```

Expected: add/remove cases expose the old `existingScore !== null` comparison behavior.

- [ ] **Step 3: Replace local comparison/reason logic**

```php
$scoreChanged = ScoreChangePolicy::numbersDiffer(
    $existingScore?->achieved_score,
    $achievedScore
);
$reason = ScoreChangePolicy::validatedReason(
    $scoreChanged,
    $item['modification_reason'] ?? null,
    $requireReasonForChanges,
    "support_list.{$index}.modification_reason"
);
```

Remove duplicate reason validation in `normalizeAndValidateItems()` so one policy is authoritative. Preserve evidence and activity-entry validation.

- [ ] **Step 4: Run support tests and verify GREEN**

Run the command from Step 2. Expected: all tests pass.

- [ ] **Step 5: Commit**

```powershell
git add app/Services/SupportScoreService.php tests/Feature/Evaluation/SupportScoreServiceTest.php tests/Feature/Evaluation/SupportCriteriaReviewerFlowTest.php
git commit -m "refactor: share support score change policy"
```

---

### Task 6: Quantity and Quality Reason Inputs and History Presentation

**Files:**
- Create: `resources/views/components/score-change-history-list.blade.php`
- Create: `resources/views/components/score-change-reason-script.blade.php`
- Modify: `app/Services/ReportDataService.php`
- Modify: `app/Http/Controllers/Evaluatee/DashboardEvaluateeController.php`
- Modify: the three unified components
- Modify: both evaluation form scripts
- Modify: `tests/Feature/SupportCriteriaEvaluationViewTest.php`

**Interfaces:**
- Produces: normalized history entries containing `previous_value`, `new_value`, `reason`, `modified_by_name`, `modified_by_role`, and `created_at`.
- Produces: `window.validateScoreChangeReasons(): string[]`.
- Consumed by: evaluatee and reviewer submit scripts.

- [ ] **Step 1: Add failing view-contract tests**

Assert reviewer components contain:

```php
->toContain('quantity_list[')
->toContain('[modification_reason]')
->toContain('quality_list[')
->toContain('data-score-change-reason')
->toContain('score-change-history-list')
```

Assert the evaluatee component renders history but no reason textarea. Assert both form scripts call `window.validateScoreChangeReasons`.

- [ ] **Step 2: Run the view tests and verify RED**

```powershell
php vendor/bin/pest tests/Feature/SupportCriteriaEvaluationViewTest.php
```

Expected: new contracts are absent.

- [ ] **Step 3: Expose normalized quantity and quality histories**

In both read-model assembly locations, map quantity and quality histories to:

```php
[
    'previous_value' => $history->previous_score_c, // or previous_score
    'new_value' => $history->new_score_c,           // or new_score
    'reason' => $history->reason,
    'modified_by_name' => $history->modifierUser?->display_name
        ?? $history->modifierUser?->name
        ?? '',
    'modified_by_role' => $history->modifier_role ?? '',
    'created_at' => optional($history->created_at)->format('d/m/Y H:i'),
]
```

Eager-load modifier users and group histories by sub-criterion to avoid per-row queries.

- [ ] **Step 4: Add the shared history component**

Render a collapsed details block. Use “ไม่มีคะแนน” for null:

```blade
<details class="mt-3 rounded-lg border border-slate-200 bg-white p-3">
    <summary class="cursor-pointer text-sm font-semibold text-slate-700">
        ประวัติการแก้ไข ({{ count($histories) }})
    </summary>
    @foreach ($histories as $history)
        <div class="mt-3 rounded-lg bg-slate-50 p-3 text-sm">
            <p>{{ $history['previous_value'] ?? 'ไม่มีคะแนน' }} → {{ $history['new_value'] ?? 'ไม่มีคะแนน' }}</p>
            <p>เหตุผล: {{ $history['reason'] ?? '-' }}</p>
            <p>แก้ไขโดย {{ $history['modified_by_name'] ?: '-' }} @if($history['modified_by_role']) ({{ $history['modified_by_role'] }}) @endif · {{ $history['created_at'] }}</p>
        </div>
    @endforeach
</details>
```

- [ ] **Step 5: Add per-item reviewer reason inputs**

Quantity textarea:

```blade
<textarea
    name="quantity_list[{{ $subCriteria['id'] }}][modification_reason]"
    data-score-change-reason
    data-score-change-type="quantity"
    data-original-value="{{ $subCriteria['tor_compliant'] ?? '' }}"
    maxlength="2000"></textarea>
```

Quality textarea uses the quality sub-criterion ID and original selected score. Keep both hidden until the associated item changes, then reveal with JavaScript. Do not render them in evaluatee/read-only views.

- [ ] **Step 6: Implement client validation and form integration**

`window.validateScoreChangeReasons()` compares current and original values, reveals changed-item reason containers, and returns Thai error messages for blank reasons. Update both form scripts:

```js
const errors = [
    ...(window.validateScoreChangeReasons?.() ?? []),
    ...(window.validateSupportCriteria?.() ?? []),
];
```

On error, open the nearest `<details>` and focus the first invalid reason.

- [ ] **Step 7: Run view tests and relevant JS tests**

```powershell
php vendor/bin/pest tests/Feature/SupportCriteriaEvaluationViewTest.php
node --test tests/js/support-score-calculator.test.mjs
```

Expected: PASS.

- [ ] **Step 8: Commit**

```powershell
git add resources/views/components/score-change-history-list.blade.php resources/views/components/score-change-reason-script.blade.php app/Services/ReportDataService.php app/Http/Controllers/Evaluatee/DashboardEvaluateeController.php resources/views/components/unified-evaluation.blade.php resources/views/components/unified-evaluator.blade.php resources/views/components/unified-director.blade.php resources/views/partials/evaluatee-evaluation-script.blade.php resources/views/partials/evaluation-form-script.blade.php tests/Feature/SupportCriteriaEvaluationViewTest.php
git commit -m "feat: show standardized score reasons and history"
```

---

### Task 7: Support History Column, Badge, and Modal

**Files:**
- Modify: `resources/views/components/support-criteria-table.blade.php`
- Modify: `resources/views/components/support-criteria-table-script.blade.php`
- Modify: `tests/Feature/SupportCriteriaEvaluationViewTest.php`

**Interfaces:**
- Produces: `data-support-history-open="<criterion-id>"` Badge.
- Produces: one `#support-history-modal` shared modal.
- Consumes: existing `items[*].histories` read-model data.

- [ ] **Step 1: Add failing desktop/mobile/read-only tests**

Assert:

```php
->toContain('ประวัติการแก้ไข')
->toContain('data-support-history-open="7"')
->toContain('3 ครั้ง')
->toContain('id="support-history-modal"')
->toContain('data-support-history-payload')
```

Render an item without histories and assert its cell/card contains “–” and no open button. Render read-only mode and assert the Badge remains available while score management controls do not.

- [ ] **Step 2: Run the view test and verify RED**

```powershell
php vendor/bin/pest tests/Feature/SupportCriteriaEvaluationViewTest.php
```

Expected: history column/modal contracts are absent.

- [ ] **Step 3: Add the desktop column and rebalance widths**

Use these fixed desktop widths, totaling 100% in editable mode:

- sequence 5%
- activity 17%
- indicator 25%
- target 8%
- weight 7%
- achieved 8%
- weighted 9%
- history 7%
- evidence 6%
- management 8%

The history cell renders:

```blade
@if (count($item['histories']) > 0)
    <button type="button"
        data-support-history-open="{{ $item['id'] }}"
        class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">
        {{ count($item['histories']) }} ครั้ง
    </button>
@else
    <span aria-label="ไม่มีประวัติการแก้ไข">–</span>
@endif
```

- [ ] **Step 4: Add the mobile history row and one shared modal**

Add a “ประวัติการแก้ไข” row to each support card. Store each item's history as escaped JSON in a `data-support-history-payload` script element or hidden template keyed by criterion ID. Render only one modal shell after the table/card markup.

The modal includes title, scrollable list, close button, `role="dialog"`, `aria-modal="true"`, and an `aria-labelledby` title.

- [ ] **Step 5: Implement accessible modal behavior**

Add:

```js
let supportHistoryTrigger = null;
let supportHistoryPreviousOverflow = '';

const openSupportHistoryModal = (criterionId, trigger) => {
    const payloadNode = document.querySelector(
        `[data-support-history-payload="${CSS.escape(String(criterionId))}"]`
    );
    if (!supportHistoryModal || !supportHistoryList || !payloadNode) return;

    const histories = JSON.parse(payloadNode.textContent || '[]');
    supportHistoryList.replaceChildren();
    histories.forEach((history) => {
        const item = document.createElement('article');
        item.className = 'rounded-lg bg-slate-50 p-3 text-sm text-slate-700';

        [
            `ค่าคะแนน: ${history.previous_achieved_score ?? 'ไม่มีคะแนน'} → ${history.new_achieved_score ?? 'ไม่มีคะแนน'}`,
            `คะแนนถ่วงน้ำหนัก: ${history.previous_weighted_score ?? 'ไม่มีคะแนน'} → ${history.new_weighted_score ?? 'ไม่มีคะแนน'}`,
            `เหตุผล: ${history.reason ?? '-'}`,
            `แก้ไขโดย ${history.modified_by_name || '-'}${history.modified_by_role ? ` (${history.modified_by_role})` : ''} · ${history.created_at || '-'}`,
        ].forEach((value) => {
            const line = document.createElement('p');
            line.textContent = value;
            item.appendChild(line);
        });
        supportHistoryList.appendChild(item);
    });

    supportHistoryTrigger = trigger;
    supportHistoryPreviousOverflow = document.body.style.overflow;
    document.body.style.overflow = 'hidden';
    supportHistoryModal.classList.remove('hidden');
    supportHistoryModal.classList.add('flex');
    supportHistoryModal.querySelector('[data-support-history-close]')?.focus();
};

const closeSupportHistoryModal = () => {
    if (!supportHistoryModal) return;
    supportHistoryModal.classList.add('hidden');
    supportHistoryModal.classList.remove('flex');
    document.body.style.overflow = supportHistoryPreviousOverflow;
    supportHistoryTrigger?.focus();
    supportHistoryTrigger = null;
};

const trapSupportHistoryModalFocus = (event) => {
    if (!supportHistoryModal || event.key !== 'Tab') return;
    const focusable = Array.from(supportHistoryModal.querySelectorAll(
        'button:not([disabled]), [href], input:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])'
    ));
    if (focusable.length === 0) return;
    const first = focusable[0];
    const last = focusable[focusable.length - 1];
    if (event.shiftKey && document.activeElement === first) {
        event.preventDefault();
        last.focus();
    } else if (!event.shiftKey && document.activeElement === last) {
        event.preventDefault();
        first.focus();
    }
};
```

Bind Badge clicks, close button, backdrop, Escape, and Tab. Render values with `textContent`, never `innerHTML`.

- [ ] **Step 6: Remove duplicate history details from the hidden editor store**

Delete the old inline support score history `<details>` from each hidden editor article. Keep activity-entry history unchanged. Confirm the modal is the single score-history detail renderer.

- [ ] **Step 7: Run view tests**

```powershell
php vendor/bin/pest tests/Feature/SupportCriteriaEvaluationViewTest.php
```

Expected: desktop/mobile/edit/read-only/history-empty/accessibility contracts pass.

- [ ] **Step 8: Commit**

```powershell
git add resources/views/components/support-criteria-table.blade.php resources/views/components/support-criteria-table-script.blade.php tests/Feature/SupportCriteriaEvaluationViewTest.php
git commit -m "feat: add support score history column"
```

---

### Task 8: Transaction, Security, and Full Regression Verification

**Files:**
- Create: `tests/Feature/Evaluation/ScoreChangeTransactionTest.php`
- Modify: `docs/superpowers/specs/2026-07-24-standardized-score-change-audit-design.md` only if implementation reveals an approved clarification; do not broaden scope.

**Interfaces:**
- Verifies all interfaces produced by Tasks 1–7.

- [ ] **Step 1: Add rollback and foreign-ID tests**

For each score type, submit one valid change and one changed reviewer item with a blank reason. Assert:

- no score rows changed;
- no history rows were added;
- evidence/comment/status remain unchanged.

Submit a quantity/quality/support criterion from another criteria version and assert validation rejects the item without persistence.

- [ ] **Step 2: Run focused test files individually**

```powershell
php vendor/bin/pest tests/Unit/Support/ScoreChangePolicyTest.php
php vendor/bin/pest tests/Feature/Evaluation/ScoreChangeHistorySchemaTest.php
php vendor/bin/pest tests/Feature/Evaluation/QualityScoreHistoryFlowTest.php
php vendor/bin/pest tests/Feature/Evaluation/SupportScoreServiceTest.php
php vendor/bin/pest tests/Feature/Evaluation/SupportCriteriaReviewerFlowTest.php
php vendor/bin/pest tests/Feature/Evaluation/EvaluateeTest.php
php vendor/bin/pest tests/Feature/Evaluation/EvaluatorTest.php
php vendor/bin/pest tests/Feature/Evaluation/DirectorTest.php
php vendor/bin/pest tests/Feature/Evaluation/ManagerTest.php
php vendor/bin/pest tests/Feature/SupportCriteriaEvaluationViewTest.php
```

Expected: every command exits 0. If SQLite reports `database is locked`, stop other PHP processes using the test DB and rerun the affected file; do not classify an infrastructure failure as a passing test.

- [ ] **Step 3: Run JS and broader score regression suites**

```powershell
node --test tests/js/support-score-calculator.test.mjs tests/js/support-activity-entries.test.mjs
php vendor/bin/pest tests/Feature/ScoreServiceTest.php tests/Unit/Support/ReportScoreSummaryTest.php tests/Feature/ReportsExportTest.php
```

Expected: all tests pass; score formulas and exports remain unchanged.

- [ ] **Step 4: Run formatting and diff checks**

```powershell
vendor/bin/pint --test
git diff --check
git status --short
```

Expected: Pint and diff checks exit 0. Review `git status` and ensure only intended files are part of this feature; preserve all pre-existing user changes.

- [ ] **Step 5: Commit final integration tests**

```powershell
git add tests/Feature/Evaluation tests/Feature/SupportCriteriaEvaluationViewTest.php
git commit -m "test: verify standardized score change audit"
```

- [ ] **Step 6: Final evidence summary**

Record:

- exact passing test commands and test counts;
- any SQLite/environment failure separately;
- migration names;
- commits produced by each task;
- confirmation that no support score formula or assignment-flow behavior changed.
