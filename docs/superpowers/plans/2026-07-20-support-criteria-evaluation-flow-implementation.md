# Support Criteria Evaluation Flow Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** ให้ผู้ถูกประเมินกรอกคะแนนและหลักฐานของเกณฑ์สายสนับสนุน แล้วให้ผู้ตรวจแต่ละ Role แก้คะแนนพร้อมเหตุผลและประวัติตาม Assignment Flow เดิม

**Architecture:** เพิ่มตารางคะแนนและประวัติของสายสนับสนุนแยกจาก Template ใช้ `SupportScoreService` เป็นจุดเดียวสำหรับตรวจหลักฐาน คำนวณ บันทึก และสร้างประวัติ และใช้ `SupportCriteriaReadModel` กับ Blade component ร่วมกันในทุก Role คะแนนรวมจริงเก็บในรายงาน ส่วน UI ใช้ `min(ผลรวมจริง, 100)` โดยไม่แก้คะแนนรายข้อ

**Tech Stack:** Laravel 12, PHP 8.2+, Eloquent, Blade, Tailwind CSS, JavaScript, Pest/PHPUnit, MySQL/SQLite

## Global Constraints

- ทำงานบน branch `feat/support` โดยตรง และไม่สร้าง worktree
- `ค่าคะแนนที่ได้` เว้นว่างได้ ค่าต่ำสุด 0 ไม่มี business maximum และมีทศนิยมไม่เกิน 2 ตำแหน่ง
- สูตรคือ `weighted_score = weight * achieved_score / 100`
- เก็บผลรวมจริง แต่ผลรวมเฉพาะสายสนับสนุนที่แสดงต้องไม่เกิน `100.00`
- หากแอดมินบังคับหลักฐาน ต้องมี HTTP/HTTPS URL อย่างน้อยหนึ่งรายการก่อนบันทึกทั้ง Draft และ Submit แม้คะแนนว่าง
- ผู้ถูกประเมินแก้ได้เฉพาะสถานะเดิมของ Flow; หลังส่งเป็น read-only
- ผู้ประเมิน กรรมการ และผู้บริหารเปลี่ยนคะแนนเดิมได้เฉพาะขั้นของตน และต้องให้เหตุผลเมื่อค่าเปลี่ยน
- Comment ใช้ `evaluator_comment`, `director_comment`, `manager_comment` เดิมระดับรายงาน
- หลักฐานสายสนับสนุนต้องไม่ลบหลักฐาน Quality หรือ Workload
- ใช้ TDD: ทุก behavior ใหม่ต้องมี failing test ก่อน production code
- ใช้ `vendor\bin\pest` แทน `php artisan test`

---

### Task 1: Support Evaluation Persistence

**Files:**
- Create: `database/migrations/2026_07_20_000002_create_support_evaluation_tables.php`
- Create: `app/Models/SupportScore.php`
- Create: `app/Models/SupportScoreHistory.php`
- Modify: `app/Models/SupportCriteria.php`
- Modify: `app/Models/Reports.php`
- Modify: `app/Models/EvidenceAnswer.php`
- Test: `tests/Feature/Evaluation/SupportEvaluationSchemaTest.php`

**Interfaces:**
- Produces: `SupportCriteria::scores()`, `SupportCriteria::scoreHistories()`, `SupportCriteria::evidenceAnswers()`
- Produces: `Reports::supportScores()` and decimal cast `support_score_total`
- Produces: `EvidenceAnswer::supportCriteria()`
- Produces: unique current score per `(report_id, support_criteria_id)`

- [ ] **Step 1: Write the failing schema/model test**

```php
<?php

namespace Tests\Feature\Evaluation;

use App\Models\EvidenceAnswer;
use App\Models\Reports;
use App\Models\SupportCriteria;
use App\Models\SupportScore;
use App\Models\SupportScoreHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SupportEvaluationSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_support_evaluation_schema_and_relations_exist(): void
    {
        $this->assertTrue(Schema::hasColumn('support_criterias', 'require_evidence'));
        $this->assertTrue(Schema::hasTable('support_scores'));
        $this->assertTrue(Schema::hasTable('support_score_histories'));
        $this->assertTrue(Schema::hasColumn('evidence_answers', 'support_criteria_id'));
        $this->assertTrue(Schema::hasColumn('reports', 'support_score_total'));

        $this->assertInstanceOf(SupportScore::class, (new SupportCriteria)->scores()->getModel());
        $this->assertInstanceOf(SupportScoreHistory::class, (new SupportCriteria)->scoreHistories()->getModel());
        $this->assertInstanceOf(EvidenceAnswer::class, (new SupportCriteria)->evidenceAnswers()->getModel());
        $this->assertInstanceOf(SupportScore::class, (new Reports)->supportScores()->getModel());
    }
}
```

- [ ] **Step 2: Run the test and confirm RED**

Run: `vendor\bin\pest tests/Feature/Evaluation/SupportEvaluationSchemaTest.php --compact`

Expected: FAIL because the columns, tables, and model classes do not exist.

- [ ] **Step 3: Create the migration**

Implement `up()` in this exact order:

```php
Schema::table('support_criterias', function (Blueprint $table) {
    $table->boolean('require_evidence')->default(false)->after('weight');
});

Schema::create('support_scores', function (Blueprint $table) {
    $table->id();
    $table->foreignId('report_id')->constrained('reports')->cascadeOnDelete();
    $table->foreignId('support_criteria_id')->constrained('support_criterias')->cascadeOnDelete();
    $table->decimal('achieved_score', 20, 2)->nullable();
    $table->decimal('weighted_score', 20, 2)->nullable();
    $table->text('modification_reason')->nullable();
    $table->foreignId('modifier_user_id')->nullable()->constrained('users')->nullOnDelete();
    $table->string('modifier_role')->nullable();
    $table->timestamps();
    $table->unique(['report_id', 'support_criteria_id'], 'support_scores_report_criteria_unique');
});

Schema::create('support_score_histories', function (Blueprint $table) {
    $table->id();
    $table->foreignId('report_id')->constrained('reports')->cascadeOnDelete();
    $table->foreignId('support_criteria_id')->constrained('support_criterias')->cascadeOnDelete();
    $table->decimal('previous_achieved_score', 20, 2)->nullable();
    $table->decimal('new_achieved_score', 20, 2)->nullable();
    $table->decimal('previous_weighted_score', 20, 2)->nullable();
    $table->decimal('new_weighted_score', 20, 2)->nullable();
    $table->text('reason')->nullable();
    $table->foreignId('modifier_user_id')->nullable()->constrained('users')->nullOnDelete();
    $table->string('modifier_role')->nullable();
    $table->timestamps();
    $table->index(['report_id', 'support_criteria_id'], 'support_history_report_criteria_index');
});

Schema::table('evidence_answers', function (Blueprint $table) {
    $table->foreignId('support_criteria_id')->nullable()->after('quality_main_criteria_id')
        ->constrained('support_criterias')->cascadeOnDelete();
});

Schema::table('reports', function (Blueprint $table) {
    $table->decimal('support_score_total', 20, 2)->default(0)->after('score');
});
```

Implement `down()` in reverse dependency order: drop `reports.support_score_total`, drop the `evidence_answers.support_criteria_id` foreign key and column, drop history, drop scores, then drop `support_criterias.require_evidence`.

- [ ] **Step 4: Add models, casts, fillable fields, and relations**

`SupportScore` must cast both scores as `decimal:2` and belong to `Reports`, `SupportCriteria`, and `User` through `modifier_user_id`. `SupportScoreHistory` must cast all four score fields as `decimal:2` and expose the same three `BelongsTo` relations. Add these exact relations:

```php
// SupportCriteria.php
public function scores(): HasMany
{
    return $this->hasMany(SupportScore::class);
}

public function scoreHistories(): HasMany
{
    return $this->hasMany(SupportScoreHistory::class)->latest();
}

public function evidenceAnswers(): HasMany
{
    return $this->hasMany(EvidenceAnswer::class);
}

// Reports.php
public function supportScores(): HasMany
{
    return $this->hasMany(SupportScore::class, 'report_id');
}

// EvidenceAnswer.php
public function supportCriteria(): BelongsTo
{
    return $this->belongsTo(SupportCriteria::class);
}
```

Add `require_evidence` to `SupportCriteria::$fillable` and cast it to boolean. Add `support_score_total` to `Reports::$fillable` and cast it to `decimal:2`. Add `support_criteria_id` to `EvidenceAnswer::$fillable`.

- [ ] **Step 5: Verify GREEN and migration rollback safety**

Run:

```powershell
vendor\bin\pest tests/Feature/Evaluation/SupportEvaluationSchemaTest.php --compact
php artisan migrate:fresh --env=testing --force
```

Expected: test PASS and all migrations finish with `DONE`.

- [ ] **Step 6: Commit**

```powershell
git add database/migrations/2026_07_20_000002_create_support_evaluation_tables.php app/Models/SupportScore.php app/Models/SupportScoreHistory.php app/Models/SupportCriteria.php app/Models/Reports.php app/Models/EvidenceAnswer.php tests/Feature/Evaluation/SupportEvaluationSchemaTest.php
git commit -m "feat: add support evaluation persistence"
```

### Task 2: Admin Evidence Requirement

**Files:**
- Modify: `resources/views/criteria_config/partials/support-criteria-template.blade.php`
- Modify: `resources/views/criteria_config/partials/create-script.blade.php`
- Modify: `resources/views/criteria_config/partials/script-edit-collect-form-data.blade.php`
- Modify: `resources/views/criteria_config/partials/script-edit-populate-helpers.blade.php`
- Modify: `app/Http/Controllers/ReportStructureController.php`
- Modify: `tests/Feature/Report/SupportCriteriaTemplateTest.php`
- Modify: `tests/Feature/SupportCriteriaTemplateViewTest.php`

**Interfaces:**
- Consumes: `support_criterias.require_evidence`
- Produces: `support_criterias[*].require_evidence: boolean` in create, show, and update payloads

- [ ] **Step 1: Add failing API and view assertions**

Extend the existing support Template tests with a payload containing:

```php
'support_criterias' => [[
    'sequence' => 1,
    'activity_name' => 'จัดทำรายงาน',
    'indicator' => 'ส่งตรงเวลา',
    'target_value' => 12,
    'weight' => 20,
    'require_evidence' => true,
]],
```

Assert the database and show endpoint:

```php
$this->assertDatabaseHas('support_criterias', [
    'activity_name' => 'จัดทำรายงาน',
    'require_evidence' => true,
]);

$response->assertJsonPath('data.categories.0.evaluation_lists.0.support_criterias.0.require_evidence', true);
```

In `SupportCriteriaTemplateViewTest`, assert the partial and both collectors contain `support_require_evidence` and `require_evidence`.

- [ ] **Step 2: Run focused tests and confirm RED**

Run: `vendor\bin\pest tests/Feature/Report/SupportCriteriaTemplateTest.php tests/Feature/SupportCriteriaTemplateViewTest.php --compact`

Expected: FAIL because the flag is neither rendered nor persisted.

- [ ] **Step 3: Add the admin checkbox and payload collection**

Inside each `.support_criteria_block`, add:

```blade
<label class="mt-4 flex items-center gap-2 text-sm font-medium text-gray-700">
    <input type="checkbox"
        class="support_require_evidence h-4 w-4 rounded border-gray-300 text-amber-600 focus:ring-amber-500">
    <span>บังคับแนบหลักฐาน</span>
</label>
```

Add this property to both create and edit collectors:

```javascript
require_evidence: supportBlock.querySelector('.support_require_evidence')?.checked || false
```

Populate edit state with:

```javascript
newSupportBlock.querySelector('.support_require_evidence').checked = Boolean(supportCriteria.require_evidence);
```

- [ ] **Step 4: Persist and serialize the flag**

Add the validation rule to both store and update rules:

```php
'categories.*.evaluation_lists.*.support_criterias.*.require_evidence' => 'nullable|boolean',
```

Add this field to show serialization and both create/update attribute arrays:

```php
'require_evidence' => (bool) ($supportData['require_evidence'] ?? false),
```

- [ ] **Step 5: Verify GREEN**

Run: `vendor\bin\pest tests/Feature/Report/SupportCriteriaTemplateTest.php tests/Feature/SupportCriteriaTemplateViewTest.php --compact`

Expected: all support Template tests PASS.

- [ ] **Step 6: Commit**

```powershell
git add resources/views/criteria_config/partials/support-criteria-template.blade.php resources/views/criteria_config/partials/create-script.blade.php resources/views/criteria_config/partials/script-edit-collect-form-data.blade.php resources/views/criteria_config/partials/script-edit-populate-helpers.blade.php app/Http/Controllers/ReportStructureController.php tests/Feature/Report/SupportCriteriaTemplateTest.php tests/Feature/SupportCriteriaTemplateViewTest.php
git commit -m "feat: configure required support evidence"
```

### Task 3: Central Support Score Save Service

**Files:**
- Create: `app/Support/SupportScoreRules.php`
- Create: `app/Services/SupportScoreService.php`
- Test: `tests/Feature/Evaluation/SupportScoreServiceTest.php`

**Interfaces:**
- Produces: `SupportScoreRules::validation(): array`
- Produces: `SupportScoreService::persist(Reports $report, array $items, ?User $actor, ?string $modifierRole, bool $requireReasonForChanges): array`
- Return shape: `['old_scores' => Collection, 'new_scores' => Collection, 'support_score_total' => float]`

- [ ] **Step 1: Write failing service tests**

Create fixtures for one report whose CriteriaVersion contains a required-evidence `SupportCriteria` with weight `20.00`. Add three tests:

```php
public function test_it_calculates_and_keeps_the_uncapped_total(): void
{
    $result = app(SupportScoreService::class)->persist($this->report, [[
        'support_criteria_id' => $this->criterion->id,
        'achieved_score' => '125.50',
        'modification_reason' => null,
        'evidence_links' => ['https://example.com/evidence'],
    ]], $this->evaluatee, null, false);

    $this->assertDatabaseHas('support_scores', [
        'report_id' => $this->report->id,
        'support_criteria_id' => $this->criterion->id,
        'achieved_score' => '125.50',
        'weighted_score' => '25.10',
    ]);
    $this->assertSame(25.10, $result['support_score_total']);
}

public function test_required_evidence_is_required_even_when_score_is_blank(): void
{
    $this->expectException(ValidationException::class);

    app(SupportScoreService::class)->persist($this->report, [[
        'support_criteria_id' => $this->criterion->id,
        'achieved_score' => null,
        'evidence_links' => [],
    ]], $this->evaluatee, null, false);
}

public function test_reviewer_change_requires_reason_and_records_history(): void
{
    SupportScore::create([
        'report_id' => $this->report->id,
        'support_criteria_id' => $this->criterion->id,
        'achieved_score' => 80,
        'weighted_score' => 16,
    ]);

    try {
        app(SupportScoreService::class)->persist($this->report, [[
            'support_criteria_id' => $this->criterion->id,
            'achieved_score' => 90,
            'evidence_links' => ['https://example.com/evidence'],
        ]], $this->evaluator, 'ผู้ประเมิน', true);
        $this->fail('Expected validation failure');
    } catch (ValidationException $exception) {
        $this->assertArrayHasKey('support_list.0.modification_reason', $exception->errors());
    }

    app(SupportScoreService::class)->persist($this->report, [[
        'support_criteria_id' => $this->criterion->id,
        'achieved_score' => 90,
        'modification_reason' => 'ปรับตามหลักฐาน',
        'evidence_links' => ['https://example.com/evidence'],
    ]], $this->evaluator, 'ผู้ประเมิน', true);

    $this->assertDatabaseHas('support_score_histories', [
        'previous_achieved_score' => '80.00',
        'new_achieved_score' => '90.00',
        'reason' => 'ปรับตามหลักฐาน',
        'modifier_role' => 'ผู้ประเมิน',
    ]);
}
```

- [ ] **Step 2: Run and confirm RED**

Run: `vendor\bin\pest tests/Feature/Evaluation/SupportScoreServiceTest.php --compact`

Expected: FAIL because rules and service do not exist.

- [ ] **Step 3: Implement reusable validation rules**

```php
final class SupportScoreRules
{
    public static function validation(): array
    {
        return [
            'support_list' => ['nullable', 'array'],
            'support_list.*.support_criteria_id' => ['required', 'integer', 'exists:support_criterias,id'],
            'support_list.*.achieved_score' => ['nullable', 'numeric', 'min:0', 'decimal:0,2'],
            'support_list.*.modification_reason' => ['nullable', 'string', 'max:2000'],
            'support_list.*.evidence_links' => ['nullable', 'array'],
            'support_list.*.evidence_links.*' => ['nullable', 'url:http,https'],
        ];
    }
}
```

- [ ] **Step 4: Implement `SupportScoreService`**

The implementation must:

```php
public function persist(
    Reports $report,
    array $items,
    ?User $actor,
    ?string $modifierRole,
    bool $requireReasonForChanges
): array
```

Use the report's `reportData.criteria_version_id` to query the allowed criteria. Reject IDs outside that version with `ValidationException::withMessages(['support_list' => ['พบเกณฑ์สายสนับสนุนที่ไม่อยู่ในรายงานนี้']])`. Normalize links with `trim`, remove blanks, and make them unique. For every allowed criterion with `require_evidence = true`, reject an empty link collection using the item index key.

For each submitted item, calculate:

```php
$achievedScore = $item['achieved_score'] === null || $item['achieved_score'] === ''
    ? null
    : round((float) $item['achieved_score'], 2);
$weightedScore = $achievedScore === null
    ? null
    : round(((float) $criterion->weight * $achievedScore) / 100, 2);
```

When an existing score's normalized achieved value changes, require a non-empty reason if `$requireReasonForChanges` is true, then create `SupportScoreHistory` before `updateOrCreate`. Sync evidence only with:

```php
EvidenceAnswer::where('report_id', $report->id)
    ->where('support_criteria_id', $criterion->id)
    ->delete();
```

Create each new evidence row with `evaluation_list_id`, `support_criteria_id`, `report_id`, and `link`. Recalculate the raw total with `SupportScore::where('report_id', $report->id)->sum('weighted_score')`, save it to `reports.support_score_total`, and return old/new collections plus the float total.

- [ ] **Step 5: Verify GREEN and no evidence cross-deletion**

Add one assertion that a pre-existing `EvidenceAnswer` with `quality_main_criteria_id` remains after `persist()`. Then run:

`vendor\bin\pest tests/Feature/Evaluation/SupportScoreServiceTest.php --compact`

Expected: all service tests PASS.

- [ ] **Step 6: Commit**

```powershell
git add app/Support/SupportScoreRules.php app/Services/SupportScoreService.php tests/Feature/Evaluation/SupportScoreServiceTest.php
git commit -m "feat: save support scores and evidence"
```

### Task 4: Shared Support Read Model and Score Summary

**Files:**
- Create: `app/Support/SupportCriteriaReadModel.php`
- Modify: `app/Services/ReportDataService.php`
- Modify: `app/Http/Controllers/Evaluatee/DashboardEvaluateeController.php`
- Modify: `app/Support/EvaluationScoreSummary.php`
- Modify: `resources/views/partials/evaluator-score-summary.blade.php`
- Modify: `resources/views/partials/evaluation-approval-form.blade.php`
- Test: `tests/Feature/Evaluation/SupportCriteriaReadModelTest.php`
- Modify: `tests/Feature/ScoreServiceTest.php`

**Interfaces:**
- Produces: `SupportCriteriaReadModel::forReport(Reports $report): array`, keyed by `evaluation_list_id`
- Adds `support_items` to every evaluation-list item
- Adds `support` to `EvaluationScoreSummary::fromCategoryItems()` and includes capped support in `total`

- [ ] **Step 1: Write failing read-model and summary tests**

Assert one returned support item has this shape:

```php
[
    'id' => $criterion->id,
    'sequence' => 1,
    'activity_name' => 'จัดทำรายงาน',
    'indicator' => 'ส่งตรงเวลา',
    'target_value' => '12.00',
    'weight' => '20.00',
    'require_evidence' => true,
    'achieved_score' => '125.50',
    'weighted_score' => '25.10',
    'modification_reason' => null,
    'evidence_links' => ['https://example.com/evidence'],
]
```

Add a summary assertion:

```php
$summary = EvaluationScoreSummary::fromCategoryItems([[
    'evaluation_lists' => [[
        'quantity_items' => [],
        'quality_items' => [],
        'support_items' => [
            ['weighted_score' => 70],
            ['weighted_score' => 42.5],
        ],
    ]],
]]);

$this->assertSame(100.0, $summary['support']);
$this->assertSame(100.0, $summary['total']);
```

- [ ] **Step 2: Run and confirm RED**

Run: `vendor\bin\pest tests/Feature/Evaluation/SupportCriteriaReadModelTest.php tests/Feature/ScoreServiceTest.php --compact`

Expected: FAIL because `support_items` and summary support are missing.

- [ ] **Step 3: Implement `SupportCriteriaReadModel`**

Load all criteria in the report's CriteriaVersion, current `SupportScore` rows keyed by criterion, histories with `modifierUser`, and support evidence grouped by criterion. Return items grouped by `evaluation_list_id`. Each history item must contain previous/new achieved and weighted scores, reason, modifier name, modifier role, and `created_at` formatted `d/m/Y H:i`.

The public method signature must be:

```php
public function forReport(Reports $report): array
```

- [ ] **Step 4: Feed the shared read model into both data builders**

Inject `SupportCriteriaReadModel` into `ReportDataService`. In `getReportData()`, compute `$supportItemsByList` once and pass it into `processCategoryItems()`. Add this key when each evaluation-list array is built:

```php
'support_items' => $supportItemsByList[$list->id] ?? [],
```

In `DashboardEvaluateeController::evaluation()`, inject the read model as an action argument, compute it once after loading the report, eager-load `evaluationLists.supportCriterias`, and add the same key to its local evaluation-list array.

After building `$categoryItems`, make `ReportDataService::getReportData()` return:

```php
'scoreSummary' => EvaluationScoreSummary::fromCategoryItems($categoryItems),
```

- [ ] **Step 5: Extend the summary**

Initialize `$totalSupportScore = 0.0`, sum all non-null `weighted_score` values, cap only this component with `min($totalSupportScore, 100.0)`, and return:

```php
return [
    'quantity' => $totalQuantityScore,
    'quality' => $totalQualityScore,
    'support' => min($totalSupportScore, 100.0),
    'total' => $totalQuantityScore + $totalQualityScore + min($totalSupportScore, 100.0),
];
```

Add a `คะแนนสายสนับสนุน` row with `id="support-summary"` to `evaluator-score-summary.blade.php`.

Add the existing score-summary partial below `<x-unified-director>` in `evaluation-approval-form.blade.php`:

```blade
@include('partials.evaluator-score-summary', ['scoreSummary' => $scoreSummary])
```

- [ ] **Step 6: Verify GREEN**

Run: `vendor\bin\pest tests/Feature/Evaluation/SupportCriteriaReadModelTest.php tests/Feature/ScoreServiceTest.php --compact`

Expected: all tests PASS.

- [ ] **Step 7: Commit**

```powershell
git add app/Support/SupportCriteriaReadModel.php app/Services/ReportDataService.php app/Http/Controllers/Evaluatee/DashboardEvaluateeController.php app/Support/EvaluationScoreSummary.php resources/views/partials/evaluator-score-summary.blade.php resources/views/partials/evaluation-approval-form.blade.php tests/Feature/Evaluation/SupportCriteriaReadModelTest.php tests/Feature/ScoreServiceTest.php
git commit -m "feat: expose support scores in evaluation views"
```

### Task 5: Responsive Shared Support Score UI

**Files:**
- Create: `resources/views/components/support-criteria-table.blade.php`
- Create: `resources/views/components/support-criteria-table-script.blade.php`
- Modify: `resources/views/components/unified-evaluation.blade.php`
- Modify: `resources/views/components/unified-evaluator.blade.php`
- Modify: `resources/views/components/unified-director.blade.php`
- Modify: `resources/views/components/unified-evaluation-script.blade.php`
- Modify: `resources/views/components/unified-evaluator-script.blade.php`
- Modify: `resources/views/components/unified-director-script.blade.php`
- Modify: `resources/views/partials/evaluatee-evaluation-script.blade.php`
- Modify: `resources/views/partials/evaluation-form-script.blade.php`
- Test: `tests/Feature/SupportCriteriaEvaluationViewTest.php`

**Interfaces:**
- Consumes: `support_items` from Task 4
- Produces: `support_list[criterion_id][support_criteria_id|achieved_score|modification_reason|evidence_links][]`
- Produces: `window.validateSupportCriteria(): string[]` and `window.recalculateSupportScores(): void`

- [ ] **Step 1: Write failing view-contract tests**

Render the component with one required-evidence item and assert:

```php
$html = view('components.support-criteria-table', [
    'items' => [[
        'id' => 7,
        'sequence' => 1,
        'activity_name' => 'จัดทำรายงาน',
        'indicator' => 'ส่งตรงเวลา',
        'target_value' => '12.00',
        'weight' => '20.00',
        'require_evidence' => true,
        'achieved_score' => '125.50',
        'weighted_score' => '25.10',
        'evidence_links' => ['https://example.com/evidence'],
        'histories' => [],
    ]],
    'readonly' => false,
    'evidenceEditable' => true,
    'requireReason' => false,
])->render();

$this->assertStringContainsString('กิจกรรม/โครงการ/งาน', $html);
$this->assertStringContainsString('ค่าคะแนนที่ได้', $html);
$this->assertStringContainsString('support_list[7][achieved_score]', $html);
$this->assertStringContainsString('support_list[7][evidence_links][]', $html);
$this->assertStringContainsString('บังคับแนบหลักฐาน', $html);
```

Also assert the reviewer rendering contains `modification_reason` and the read-only rendering contains no editable number input.

- [ ] **Step 2: Run and confirm RED**

Run: `vendor\bin\pest tests/Feature/SupportCriteriaEvaluationViewTest.php --compact`

Expected: FAIL because the component does not exist.

- [ ] **Step 3: Build the responsive component**

Use a semantic table with columns `ลำดับ`, `กิจกรรม/โครงการ/งาน`, `ตัวชี้วัด/เกณฑ์การประเมิน`, `ระดับค่าเป้าหมาย`, `น้ำหนัก`, `ค่าคะแนนที่ได้`, and `คะแนนถ่วงน้ำหนัก`. Use `hidden md:table` for the desktop layout and `md:hidden` cards for mobile. Render one canonical form control per item outside the duplicated display markup, then associate desktop/mobile labels to avoid posting duplicate fields.

Editable score input requirements:

```blade
<input type="number" min="0" step="0.01"
    name="support_list[{{ $item['id'] }}][achieved_score]"
    value="{{ $item['achieved_score'] }}"
    data-support-score
    data-support-id="{{ $item['id'] }}"
    data-support-weight="{{ $item['weight'] }}"
    data-support-original-score="{{ $item['achieved_score'] }}">
```

Render an evidence editor for the evaluatee, anchor links plus hidden preservation inputs for active reviewers, and anchors only for read-only mode. Render the reason textarea only when `requireReason` is true and the page is editable. Render every history with all approved fields.

- [ ] **Step 4: Add shared calculation and validation JavaScript**

`window.recalculateSupportScores()` must calculate each row, update its display, sum the raw values, set `support-summary` to `Math.min(rawTotal, 100).toFixed(2)`, and update `total-summary` using quantity + quality + capped support.

`window.validateSupportCriteria()` must return errors for:

```javascript
if (value !== '' && (!/^\d+(\.\d{1,2})?$/.test(value) || Number(value) < 0)) {
    errors.push(`ค่าคะแนนที่ได้ของ "${activity}" ต้องเป็นเลขตั้งแต่ 0 และมีทศนิยมไม่เกิน 2 ตำแหน่ง`);
}

if (required && evidenceLinks.length === 0) {
    errors.push(`กรุณาแนบหลักฐานสำหรับ "${activity}"`);
}

if (requireReason && normalizedCurrent !== normalizedOriginal && reason === '') {
    errors.push(`กรุณาระบุเหตุผลการแก้คะแนนของ "${activity}"`);
}
```

Bind add/remove evidence buttons and score input events once with a window guard.

- [ ] **Step 5: Integrate component and validation into all shared forms**

Inside each evaluation-list loop:

```blade
@if (!empty($evaluationList['support_items']))
    <x-support-criteria-table
        :items="$evaluationList['support_items']"
        :readonly="$readonly"
        :evidence-editable="true"
        :require-reason="false" />
@endif
```

Use `evidence-editable="false"` and `require-reason="true"` in `unified-evaluator` and `unified-director`. Include the shared script once in each unified component. Append `window.validateSupportCriteria?.() ?? []` to evaluatee `validateForm()`. In `evaluation-form-script`, prevent both Draft submit and confirmation modal opening when shared support validation returns errors. Update all three unified summary scripts so `total-summary` includes `support-summary`.

- [ ] **Step 6: Verify GREEN and compile views**

Run:

```powershell
vendor\bin\pest tests/Feature/SupportCriteriaEvaluationViewTest.php --compact
php artisan view:cache
```

Expected: view tests PASS and `Blade templates cached successfully`.

- [ ] **Step 7: Commit**

```powershell
git add resources/views/components/support-criteria-table.blade.php resources/views/components/support-criteria-table-script.blade.php resources/views/components/unified-evaluation.blade.php resources/views/components/unified-evaluator.blade.php resources/views/components/unified-director.blade.php resources/views/components/unified-evaluation-script.blade.php resources/views/components/unified-evaluator-script.blade.php resources/views/components/unified-director-script.blade.php resources/views/partials/evaluatee-evaluation-script.blade.php resources/views/partials/evaluation-form-script.blade.php tests/Feature/SupportCriteriaEvaluationViewTest.php
git commit -m "feat: add responsive support score form"
```

### Task 6: Evaluatee Save and Submit Flow

**Files:**
- Modify: `app/Http/Controllers/Evaluatee/EvaluationScoreController.php`
- Modify: `tests/Feature/Evaluation/EvaluateeTest.php`

**Interfaces:**
- Consumes: `SupportScoreRules::validation()` and `SupportScoreService::persist()`
- Preserves: existing Draft/Pending status behavior and quality/workload evidence

- [ ] **Step 1: Add failing evaluatee feature tests**

Add tests proving:

1. Draft saves `125.50`, weighted `25.10`, evidence URL, and raw report total.
2. Pending submit succeeds with some support scores blank.
3. Draft and Pending both fail validation when any required support criterion has no URL.
4. A user cannot save support scores after the report leaves `Assigned`/`Draft`.
5. Saving support evidence does not delete quality or workload evidence.

Use this payload shape:

```php
'support_list' => [
    $criterion->id => [
        'support_criteria_id' => $criterion->id,
        'achieved_score' => '125.50',
        'modification_reason' => null,
        'evidence_links' => ['https://example.com/evidence'],
    ],
],
```

- [ ] **Step 2: Run and confirm RED**

Run: `vendor\bin\pest tests/Feature/Evaluation/EvaluateeTest.php --filter=support --compact`

Expected: FAIL because the controller ignores `support_list`.

- [ ] **Step 3: Validate and persist inside the existing transaction**

Merge the shared rules into `$request->validate()`:

```php
$validated = $request->validate([
    ...SupportScoreRules::validation(),
]);
```

Insert the spread entry into the controller's current validation array without removing its quantity, quality, evidence, status, or comment rules.

Before changing report status, call:

```php
$supportResult = $this->supportScoreService->persist(
    $report,
    $validated['support_list'] ?? [],
    $request->user(),
    null,
    false
);
```

Inject `SupportScoreService` through the controller constructor. Change the broad evidence deletion to exclude support evidence:

```php
EvidenceAnswer::where('report_id', $reportId)
    ->whereNull('support_criteria_id')
    ->delete();
```

Add old/new support data and raw total to the existing activity-log properties.

- [ ] **Step 4: Preserve validation responses**

Before the existing broad exception catch, add:

```php
} catch (ValidationException $exception) {
    DB::rollBack();
    throw $exception;
} catch (Exception $exception) {
```

This ensures missing evidence returns normal Laravel validation instead of HTTP 500.

- [ ] **Step 5: Verify GREEN**

Run: `vendor\bin\pest tests/Feature/Evaluation/EvaluateeTest.php --compact`

Expected: all evaluatee tests PASS, including existing quantity/quality flow.

- [ ] **Step 6: Commit**

```powershell
git add app/Http/Controllers/Evaluatee/EvaluationScoreController.php tests/Feature/Evaluation/EvaluateeTest.php
git commit -m "feat: save evaluatee support scores"
```

### Task 7: Reviewer Role Editing, Reasons, History, and Comments

**Files:**
- Modify: `app/Http/Controllers/EvaluatorScoreController.php`
- Modify: `app/Http/Controllers/Director/DirectorScoreController.php`
- Modify: `app/Http/Controllers/Manager/ManagerScoreController.php`
- Create: `tests/Feature/Evaluation/SupportCriteriaReviewerFlowTest.php`

**Interfaces:**
- Consumes: shared support rules/service
- Preserves: current per-Role authorization, statuses, role comments, notifications, and activity logs
- Requires: non-empty `modification_reason` when an existing achieved score changes

- [ ] **Step 1: Write failing parameterized reviewer-flow tests**

Create one fixture with evaluatee score `80.00`, weighted `16.00`, and evidence. Exercise these exact cases:

```php
yield 'evaluator' => ['ผู้ประเมิน', 'Pending', 'Evaluator_draft', 'evaluator_score.store', 'evaluator_id'];
yield 'director' => ['กรรมการ', 'Director_assigned', 'Director_draft', 'director_score.store', 'director_id'];
yield 'manager' => ['ผู้บริหาร', 'Manager_assign', 'Manager_draft', 'manager_score.store', 'manager_id'];
```

For each Role, first POST score `90` without reason and assert session errors for `support_list.<id>.modification_reason`. Then POST with `ปรับตามหลักฐาน`, assert current score `90.00`, weighted `18.00`, history Role/reason, and that the Role's existing report comment field saves unchanged.

Add a test that posting the same score twice does not add another history row.

- [ ] **Step 2: Run and confirm RED**

Run: `vendor\bin\pest tests/Feature/Evaluation/SupportCriteriaReviewerFlowTest.php --compact`

Expected: FAIL because reviewer controllers ignore support scores.

- [ ] **Step 3: Integrate the evaluator controller**

Inject `SupportScoreService`, merge `SupportScoreRules::validation()` into validation, and call inside the existing transaction:

```php
$supportResult = $this->supportScoreService->persist(
    $report,
    $validated['support_list'] ?? [],
    $request->user(),
    $modifierRole,
    true
);
```

Add old/new support values and the raw total to activity-log properties. Re-throw `ValidationException` before the generic catch.

- [ ] **Step 4: Integrate the director controller**

Apply the same shared validation and service call, using its existing `$modifierRole` and status transaction. Keep `director_comment` behavior unchanged. Re-throw `ValidationException` before the generic catch.

- [ ] **Step 5: Integrate the manager controller**

Apply the same shared validation and service call, using its existing `$modifierRole` and status transaction. Keep `manager_comment` and completed-email behavior unchanged. Re-throw `ValidationException` before the generic catch.

- [ ] **Step 6: Verify GREEN across all Roles**

Run:

```powershell
vendor\bin\pest tests/Feature/Evaluation/SupportCriteriaReviewerFlowTest.php tests/Feature/Evaluation/EvaluatorTest.php tests/Feature/Evaluation/DirectorTest.php tests/Feature/Evaluation/ManagerTest.php --compact
```

Expected: reviewer support tests and all existing Role tests PASS.

- [ ] **Step 7: Commit**

```powershell
git add app/Http/Controllers/EvaluatorScoreController.php app/Http/Controllers/Director/DirectorScoreController.php app/Http/Controllers/Manager/ManagerScoreController.php tests/Feature/Evaluation/SupportCriteriaReviewerFlowTest.php
git commit -m "feat: review support scores by role"
```

### Task 8: End-to-End Regression and Delivery Check

**Files:**
- Modify only files identified by failures directly caused by Tasks 1–7

**Interfaces:**
- Verifies the complete approved spec without expanding scope

- [ ] **Step 1: Run all support-focused tests**

```powershell
vendor\bin\pest tests/Feature/Report/SupportCriteriaTemplateTest.php tests/Feature/SupportCriteriaTemplateViewTest.php tests/Feature/Evaluation/SupportEvaluationSchemaTest.php tests/Feature/Evaluation/SupportScoreServiceTest.php tests/Feature/Evaluation/SupportCriteriaReadModelTest.php tests/Feature/SupportCriteriaEvaluationViewTest.php tests/Feature/Evaluation/SupportCriteriaReviewerFlowTest.php tests/Feature/Evaluation/EvaluateeTest.php --compact
```

Expected: all focused tests PASS.

- [ ] **Step 2: Run the full PHP suite**

Run: `vendor\bin\pest --compact`

Expected: all tests PASS with zero failures.

- [ ] **Step 3: Verify migrations, views, formatting, and frontend build**

```powershell
php artisan migrate:fresh --env=testing --force
php artisan view:cache
vendor\bin\pint --test
npm run build
git diff --check
```

Expected: migrations complete, Blade cache succeeds, Pint reports no style errors, Vite build succeeds, and `git diff --check` has no output.

- [ ] **Step 4: Inspect the final worktree without touching unrelated files**

Run:

```powershell
git status --short
git log --oneline --decorate -12
```

Expected: feature files are committed; the existing Jui/UAT files and `.superpowers/` mockup directory remain uncommitted and untouched.

If verification exposes a regression caused by Tasks 1–7, return to the responsible task, add a failing regression test, apply the smallest scoped correction, rerun that task's focused tests, and use that task's explicit `git add` file list. Never stage the existing kickoff, UAT, generator, or `.superpowers/` files.
