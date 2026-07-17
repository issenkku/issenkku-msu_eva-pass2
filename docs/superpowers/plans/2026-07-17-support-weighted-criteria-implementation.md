# Support Weighted Criteria Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** เพิ่ม template เกณฑ์สายสนับสนุนที่รับคะแนนดิบจากผู้ประเมินหรือ Workload แล้วคำนวณคะแนนถ่วงน้ำหนักด้วยกติกาเดียวกันทุกบทบาท โดยไม่เปลี่ยนผลของเกณฑ์เดิม

**Architecture:** เก็บเกณฑ์สายสนับสนุนบน `EvaluationList -> QuantityMainCriteria -> QuantitySubCriteria` เดิม และใช้ `criteria_mode` แยก semantics จาก legacy อย่างชัดเจน แต่ละ support evaluation list มีหนึ่งกิจกรรมและหนึ่งตัวชี้วัดเพื่อให้รายละเอียดและหลักฐานเดิมที่ผูกกับ evaluation list ยังมีความหมายต่อหนึ่งเกณฑ์ ใช้ `QuantityScoreCalculator` และ `WorkloadQuantityScoreService` เป็นจุดคำนวณกลาง ส่วน Blade แยก partial ของ support ออกจาก UI quantity เดิม

**Tech Stack:** PHP 8.2, Laravel 11, Eloquent, Blade, Tailwind CSS, vanilla JavaScript, Pest 3, SQLite test database

## Global Constraints

- แสดงตัวเลือก “เกณฑ์สายสนับสนุน” เฉพาะเมื่อ `assessment_type` เท่ากับ `กลุ่มสนับสนุน`
- ประเภท quantity, quality และ support เลือกได้เพียงหนึ่งประเภทต่อ evaluation list โดยใช้ native radio controls
- `criteria_mode` รองรับ `legacy` และ `support_weighted`; ข้อมูลเดิมใช้ default `legacy` โดยไม่ backfill รายแถว
- `score_source` รองรับ `manual` และ `workload` เท่านั้น
- สูตร support คือ `score_D = score_a * score_C / 100` โดย `score_a` คือน้ำหนักและ `score_C` คือคะแนนดิบ
- คะแนนดิบ support ต้องอยู่ในช่วง `0..raw_score_max`; Workload ต้องรวมค่าที่ไม่ติดลบแล้ว cap ฝั่ง server
- ผลรวมน้ำหนัก support ทั้ง CriteriaVersion ต้องเท่ากับ `100.00` เมื่อบันทึก
- ผู้ถูกประเมินไม่กรอกคะแนน manual; ผู้ประเมิน/ผู้อนุมัติกรอก manual เท่านั้น; Workload แสดงแบบอ่านอย่างเดียว
- ห้ามบันทึก Pending หาก description, evidence หรือ Workload formula ที่ถูกกำหนดเป็น required ยังไม่ครบ
- CriteriaVersion แบบ support ที่มี report แล้วแก้โครงเกณฑ์ไม่ได้ และ Workload formula ของ version นั้นแก้ไม่ได้
- URL และ assignment/report workflow เดิมต้องไม่เปลี่ยน
- เกณฑ์ปี 2568 และ legacy quantity/quality ต้องให้ผลเดิม
- ทุก control ใหม่ต้องมี label, focus state และ error/helper text ที่เชื่อมด้วย `aria-describedby`; weight summary ใช้ `aria-live="polite"`

---

## File Map

**Domain metadata**

- Create `database/migrations/2026_07_17_000001_add_support_weighted_metadata.php` — เพิ่ม `criteria_mode`, `score_source`, `raw_score_max` และ `require_description`
- Modify `app/Models/EvaluationList.php` — constants, fillable, cast และ helper สำหรับ support mode
- Modify `app/Models/QuantitySubCriteria.php` — constants, fillable, casts, helpers และ Workload relation
- Modify `database/factories/EvaluationListFactory.php` และ `database/factories/QuantitySubCriteriaFactory.php` — defaults/states สำหรับ test

**Scoring and validation**

- Create `app/Services/QuantityScoreCalculator.php` — คำนวณ legacy/manual/workload และ enforce source/cap
- Create `app/Services/WorkloadQuantityScoreService.php` — รวม `calculated_score` และ persist `QuantityScore`
- Create `app/Services/SupportCriteriaSubmissionValidator.php` — ตรวจ description, evidence และ Workload readiness ก่อน Pending
- Modify `app/Http/Controllers/ReportController.php` — ให้ admin quantity score API ใช้ calculator และไม่รับ `score_D` จาก client

**Criteria API and admin UI**

- Modify `app/Http/Controllers/ReportStructureController.php` — validate, persist, serialize metadata และ lock support version ที่เริ่มใช้งาน
- Create `resources/views/criteria_config/partials/support-criteria-template.blade.php` — form เฉพาะสายสนับสนุน
- Modify create/edit criteria Blade/JavaScript partials — radio selection, support payload, populate, weight summary และ Workload status
- Create `tests/Feature/Report/SupportWeightedCriteriaTest.php` — API persistence/validation/lock
- Create `tests/Feature/SupportCriteriaUiTest.php` — Blade contract และ accessibility semantics

**Workload**

- Modify `app/Http/Controllers/Workload/WorkloadConfigController.php` — เปิด config เฉพาะ support source=workload และ lock หลังมี report
- Modify `app/Http/Controllers/Evaluatee/EvaluationWorkloadController.php` — ใช้ aggregate service กลาง
- Create `tests/Feature/SupportWorkloadScoreTest.php` — aggregation, cap, source guard และ lock

**Role submission and presentation**

- Modify `app/Services/ReportDataService.php` และ `app/Http/Controllers/Evaluatee/DashboardEvaluateeController.php` — ส่ง metadata ไป view model
- Create `resources/views/components/partials/support-weighted-evaluatee.blade.php` — evaluatee form
- Create `resources/views/components/partials/support-weighted-review.blade.php` — evaluator/manager/director form
- Modify `resources/views/components/unified-evaluation.blade.php`, `unified-evaluator.blade.php` และ `unified-director.blade.php` — route support lists ไป partial ใหม่
- Modify four score controllers — ใช้ calculator, รักษาคะแนน Workload และ reject forged source
- Create `tests/Feature/Evaluation/SupportWeightedEvaluationTest.php` — role behavior, pending validation และ summary consistency

---

### Task 1: Persist support metadata without changing legacy rows

**Files:**
- Create: `database/migrations/2026_07_17_000001_add_support_weighted_metadata.php`
- Modify: `app/Models/EvaluationList.php:17-24`
- Modify: `app/Models/QuantitySubCriteria.php:17-33`
- Modify: `database/factories/EvaluationListFactory.php:19-28`
- Modify: `database/factories/QuantitySubCriteriaFactory.php:20-30`
- Test: `tests/Feature/Report/SupportWeightedCriteriaTest.php`

**Interfaces:**
- Produces: `EvaluationList::MODE_LEGACY`, `EvaluationList::MODE_SUPPORT_WEIGHTED`, `EvaluationList::isSupportWeighted(): bool`
- Produces: `QuantitySubCriteria::SOURCE_MANUAL`, `SOURCE_WORKLOAD`, `isManualSource(): bool`, `isWorkloadSource(): bool`
- Produces columns `evaluation_lists.criteria_mode` and `quantity_sub_criterias.{score_source,raw_score_max,require_description}`

- [ ] **Step 1: Write the failing schema/default test**

```php
<?php

use App\Models\EvaluationList;
use App\Models\QuantitySubCriteria;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('legacy metadata defaults preserve existing criteria semantics', function () {
    $list = EvaluationList::factory()->create();
    $criterion = QuantitySubCriteria::factory()->create([
        'evaluation_list_id' => $list->id,
        'criteria_version_id' => $list->criteria_version_id,
    ]);

    expect($list->fresh()->criteria_mode)->toBe(EvaluationList::MODE_LEGACY)
        ->and($criterion->fresh()->score_source)->toBe(QuantitySubCriteria::SOURCE_MANUAL)
        ->and((float) $criterion->fresh()->raw_score_max)->toBe(5.0)
        ->and($criterion->fresh()->require_description)->toBeFalse();
});

test('support factory state exposes support helpers', function () {
    $list = EvaluationList::factory()->supportWeighted()->create();
    $criterion = QuantitySubCriteria::factory()->workloadSource()->create([
        'evaluation_list_id' => $list->id,
        'criteria_version_id' => $list->criteria_version_id,
    ]);

    expect($list->isSupportWeighted())->toBeTrue()
        ->and($criterion->isWorkloadSource())->toBeTrue();
});
```

- [ ] **Step 2: Run the test and verify the missing columns/constants fail**

Run: `vendor/bin/pest.bat tests/Feature/Report/SupportWeightedCriteriaTest.php`
Expected: FAIL because `criteria_mode` and model constants do not exist.

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
        Schema::table('evaluation_lists', function (Blueprint $table) {
            $table->string('criteria_mode', 32)
                ->default('legacy')
                ->after('annotation')
                ->index();
        });

        Schema::table('quantity_sub_criterias', function (Blueprint $table) {
            $table->string('score_source', 16)->default('manual')->after('description');
            $table->decimal('raw_score_max', 5, 2)->default(5)->after('score_source');
            $table->boolean('require_description')->default(false)->after('raw_score_max');
        });
    }

    public function down(): void
    {
        Schema::table('quantity_sub_criterias', function (Blueprint $table) {
            $table->dropColumn(['score_source', 'raw_score_max', 'require_description']);
        });

        Schema::table('evaluation_lists', function (Blueprint $table) {
            $table->dropIndex(['criteria_mode']);
            $table->dropColumn('criteria_mode');
        });
    }
};
```

- [ ] **Step 4: Add constants, casts, helpers, and factory states**

Add to `EvaluationList`:

```php
public const MODE_LEGACY = 'legacy';
public const MODE_SUPPORT_WEIGHTED = 'support_weighted';

protected $fillable = [
    'name',
    'sum_score',
    'sequence',
    'annotation',
    'criteria_mode',
    'categorie_id',
    'criteria_version_id',
];

public function isSupportWeighted(): bool
{
    return $this->criteria_mode === self::MODE_SUPPORT_WEIGHTED;
}
```

Add to `QuantitySubCriteria`:

```php
public const SOURCE_MANUAL = 'manual';
public const SOURCE_WORKLOAD = 'workload';

protected $fillable = [
    'name',
    'sequence',
    'score_a',
    'score_b',
    'quantity_main_criteria_id',
    'criteria_version_id',
    'evaluation_list_id',
    'description',
    'score_source',
    'raw_score_max',
    'require_description',
    'require_evidence',
    'require_subject',
];

protected $casts = [
    'score_a' => 'decimal:2',
    'score_b' => 'decimal:2',
    'raw_score_max' => 'decimal:2',
    'require_description' => 'boolean',
    'require_evidence' => 'boolean',
    'require_subject' => 'boolean',
];

public function workloadForms()
{
    return $this->hasMany(WorkloadForm::class, 'quantity_sub_criteria_id');
}

public function isManualSource(): bool
{
    return $this->score_source === self::SOURCE_MANUAL;
}

public function isWorkloadSource(): bool
{
    return $this->score_source === self::SOURCE_WORKLOAD;
}

public function isSupportWeighted(): bool
{
    return $this->evaluationList?->isSupportWeighted() ?? false;
}
```

Add factory states:

```php
public function supportWeighted(): static
{
    return $this->state(fn () => [
        'criteria_mode' => \App\Models\EvaluationList::MODE_SUPPORT_WEIGHTED,
    ]);
}
```

```php
public function workloadSource(): static
{
    return $this->state(fn () => [
        'score_source' => \App\Models\QuantitySubCriteria::SOURCE_WORKLOAD,
        'raw_score_max' => 5,
    ]);
}
```

- [ ] **Step 5: Run the metadata tests**

Run: `vendor/bin/pest.bat tests/Feature/Report/SupportWeightedCriteriaTest.php`
Expected: PASS, 2 tests.

- [ ] **Step 6: Commit**

```powershell
git add database/migrations/2026_07_17_000001_add_support_weighted_metadata.php app/Models/EvaluationList.php app/Models/QuantitySubCriteria.php database/factories/EvaluationListFactory.php database/factories/QuantitySubCriteriaFactory.php tests/Feature/Report/SupportWeightedCriteriaTest.php
git commit -m "feat: add support criteria metadata"
```

---

### Task 2: Centralize legacy and support score calculation

**Files:**
- Create: `app/Services/QuantityScoreCalculator.php`
- Create: `app/Services/WorkloadQuantityScoreService.php`
- Create: `tests/Feature/SupportWorkloadScoreTest.php`
- Modify: `tests/Feature/ScoreServiceTest.php`

**Interfaces:**
- Produces: `QuantityScoreCalculator::fromManual(QuantitySubCriteria $criterion, mixed $raw): array{score_C: float, score_D: ?float}`
- Produces: `QuantityScoreCalculator::fromWorkload(QuantitySubCriteria $criterion, iterable $entryScores): array{score_C: float, score_D: ?float}`
- Produces: `WorkloadQuantityScoreService::recalculate(int $reportId, QuantitySubCriteria $criterion): QuantityScore`

- [ ] **Step 1: Write failing calculator tests**

```php
<?php

use App\Models\EvaluationList;
use App\Models\QuantitySubCriteria;
use App\Services\QuantityScoreCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

test('manual support score is weighted and bounded', function () {
    $list = EvaluationList::factory()->supportWeighted()->create();
    $criterion = QuantitySubCriteria::factory()->create([
        'evaluation_list_id' => $list->id,
        'criteria_version_id' => $list->criteria_version_id,
        'score_a' => 30,
        'score_b' => 100,
        'score_source' => QuantitySubCriteria::SOURCE_MANUAL,
        'raw_score_max' => 5,
    ])->load('evaluationList');

    expect(app(QuantityScoreCalculator::class)->fromManual($criterion, 4))
        ->toBe(['score_C' => 4.0, 'score_D' => 1.2]);

    expect(fn () => app(QuantityScoreCalculator::class)->fromManual($criterion, 5.01))
        ->toThrow(ValidationException::class);
});

test('workload support score sums non-negative entries and caps raw score', function () {
    $list = EvaluationList::factory()->supportWeighted()->create();
    $criterion = QuantitySubCriteria::factory()->workloadSource()->create([
        'evaluation_list_id' => $list->id,
        'criteria_version_id' => $list->criteria_version_id,
        'score_a' => 40,
        'raw_score_max' => 5,
    ])->load('evaluationList');

    expect(app(QuantityScoreCalculator::class)->fromWorkload($criterion, [2, 4, -3]))
        ->toBe(['score_C' => 5.0, 'score_D' => 2.0]);
});

test('legacy calculation keeps A times C divided by B without support cap', function () {
    $list = EvaluationList::factory()->create();
    $criterion = QuantitySubCriteria::factory()->create([
        'evaluation_list_id' => $list->id,
        'criteria_version_id' => $list->criteria_version_id,
        'score_a' => 10,
        'score_b' => 5,
        'raw_score_max' => 5,
    ])->load('evaluationList');

    expect(app(QuantityScoreCalculator::class)->fromManual($criterion, 8))
        ->toBe(['score_C' => 8.0, 'score_D' => 16.0]);
});
```

- [ ] **Step 2: Run tests and verify the service is missing**

Run: `vendor/bin/pest.bat tests/Feature/SupportWorkloadScoreTest.php`
Expected: FAIL with class `App\Services\QuantityScoreCalculator` not found.

- [ ] **Step 3: Implement the calculator**

```php
<?php

namespace App\Services;

use App\Models\QuantitySubCriteria;
use Illuminate\Validation\ValidationException;

final class QuantityScoreCalculator
{
    public function fromManual(QuantitySubCriteria $criterion, mixed $raw): array
    {
        $value = max(0, (float) $raw);

        if (! $criterion->isSupportWeighted()) {
            return $this->legacy($criterion, $value);
        }

        if (! $criterion->isManualSource()) {
            throw ValidationException::withMessages([
                'quantity_list' => ['รายการ Workload ไม่รับคะแนนที่กรอกด้วยมือ'],
            ]);
        }

        $maximum = (float) $criterion->raw_score_max;
        if ($value > $maximum) {
            throw ValidationException::withMessages([
                'quantity_list' => ["คะแนนดิบของ {$criterion->name} ต้องไม่เกิน {$maximum}"],
            ]);
        }

        return $this->support($criterion, $value);
    }

    public function fromWorkload(QuantitySubCriteria $criterion, iterable $entryScores): array
    {
        $raw = collect($entryScores)->sum(
            fn ($score) => max(0, (float) $score)
        );

        if (! $criterion->isSupportWeighted()) {
            return $this->legacy($criterion, $raw);
        }

        if (! $criterion->isWorkloadSource()) {
            throw ValidationException::withMessages([
                'quantity_sub_criteria_id' => ['รายการ manual ไม่สามารถคำนวณจาก Workload'],
            ]);
        }

        $capped = min($raw, (float) $criterion->raw_score_max);

        return $this->support($criterion, $capped);
    }

    private function support(QuantitySubCriteria $criterion, float $raw): array
    {
        return [
            'score_C' => round($raw, 2),
            'score_D' => round(((float) $criterion->score_a * $raw) / 100, 2),
        ];
    }

    private function legacy(QuantitySubCriteria $criterion, float $raw): array
    {
        $base = (float) $criterion->score_b;

        return [
            'score_C' => round($raw, 2),
            'score_D' => $base === 0.0
                ? null
                : round(((float) $criterion->score_a * $raw) / $base, 2),
        ];
    }
}
```

- [ ] **Step 4: Write the failing persistence test for Workload aggregation**

Create report, workload form and three entries with `calculated_score` values `2`, `4` and `-1`, then assert:

```php
$score = app(\App\Services\WorkloadQuantityScoreService::class)
    ->recalculate($report->id, $criterion->load('evaluationList'));

expect((float) $score->score_C)->toBe(5.0)
    ->and((float) $score->score_D)->toBe(2.0);

$this->assertDatabaseHas('quantity_scores', [
    'report_id' => $report->id,
    'quantity_sub_criteria_id' => $criterion->id,
    'score_C' => 5,
    'score_D' => 2,
]);
```

Run: `vendor/bin/pest.bat tests/Feature/SupportWorkloadScoreTest.php`
Expected: FAIL because `WorkloadQuantityScoreService` does not exist.

- [ ] **Step 5: Implement Workload aggregation and persistence**

```php
<?php

namespace App\Services;

use App\Models\QuantityScore;
use App\Models\QuantitySubCriteria;
use App\Models\WorkloadEntry;
use App\Models\WorkloadForm;

final class WorkloadQuantityScoreService
{
    public function __construct(
        private readonly QuantityScoreCalculator $calculator
    ) {
    }

    public function recalculate(int $reportId, QuantitySubCriteria $criterion): QuantityScore
    {
        $criterion->loadMissing('evaluationList');

        $formIds = WorkloadForm::query()
            ->where('quantity_sub_criteria_id', $criterion->id)
            ->pluck('id');

        $entryScores = WorkloadEntry::query()
            ->where('report_id', $reportId)
            ->whereIn('workload_form_id', $formIds)
            ->pluck('calculated_score');

        $result = $this->calculator->fromWorkload($criterion, $entryScores);

        return QuantityScore::updateOrCreate(
            [
                'report_id' => $reportId,
                'quantity_sub_criteria_id' => $criterion->id,
            ],
            $result
        );
    }
}
```

- [ ] **Step 6: Run calculator, aggregation, and legacy summary tests**

Run: `vendor/bin/pest.bat tests/Feature/SupportWorkloadScoreTest.php tests/Feature/ScoreServiceTest.php`
Expected: PASS and the existing legacy totals remain unchanged.

- [ ] **Step 7: Commit**

```powershell
git add app/Services/QuantityScoreCalculator.php app/Services/WorkloadQuantityScoreService.php tests/Feature/SupportWorkloadScoreTest.php tests/Feature/ScoreServiceTest.php
git commit -m "feat: centralize weighted quantity scoring"
```

---

### Task 3: Store, return, and lock support criteria through the criteria API

**Files:**
- Modify: `app/Http/Controllers/ReportStructureController.php:99-518`
- Modify: `app/Http/Controllers/ReportStructureController.php:540-1019`
- Modify: `tests/Feature/Report/SupportWeightedCriteriaTest.php`

**Interfaces:**
- Consumes model constants from Task 1
- Accepts `evaluation_lists.*.criteria_mode` and quantity sub metadata
- Returns `criteria_mode`, `description`, `score_source`, `raw_score_max`, `require_description` and `workload_configured`

- [ ] **Step 1: Add failing store/show validation tests**

Use a payload with one support list containing one main and one sub:

```php
$payload = [
    'created_by' => $this->admin->id,
    'report_datas' => [[
        'report_title' => 'เกณฑ์สายสนับสนุน',
        'assessment_type' => 'กลุ่มสนับสนุน',
    ]],
    'categories' => [[
        'main_categories' => 'ผลสัมฤทธิ์ของงาน',
        'sub_categories' => 'สายสนับสนุน',
        'sequence' => 1,
        'evaluation_lists' => [[
            'name' => 'งานพัฒนาระบบ',
            'sum_score' => 2,
            'sequence' => 1,
            'criteria_mode' => 'support_weighted',
            'quantity_main_criterias' => [[
                'name' => 'งานพัฒนาระบบ',
                'tooltips' => null,
                'description' => null,
                'formula' => null,
                'quantity_sub_criterias' => [[
                    'name' => 'ส่งมอบตามแผน',
                    'sequence' => 1,
                    'score_a' => 40,
                    'score_b' => 100,
                    'description' => '5 = ครบตามแผน',
                    'score_source' => 'manual',
                    'raw_score_max' => 5,
                    'require_description' => true,
                    'require_evidence' => true,
                ]],
            ]],
            'quality_main_criterias' => [],
        ]],
    ], [
        'main_categories' => 'พัฒนาตนเอง',
        'sub_categories' => 'สายสนับสนุน',
        'sequence' => 2,
        'evaluation_lists' => [[
            'name' => 'การอบรม',
            'sum_score' => 3,
            'sequence' => 1,
            'criteria_mode' => 'support_weighted',
            'quantity_main_criterias' => [[
                'name' => 'การอบรม',
                'tooltips' => null,
                'description' => null,
                'formula' => null,
                'quantity_sub_criterias' => [[
                    'name' => 'จำนวนชั่วโมงอบรม',
                    'sequence' => 1,
                    'score_a' => 60,
                    'score_b' => 100,
                    'description' => null,
                    'score_source' => 'workload',
                    'raw_score_max' => 5,
                    'require_description' => false,
                    'require_evidence' => false,
                ]],
            ]],
            'quality_main_criterias' => [],
        ]],
    ]],
];

$created = $this->postJson(route('report-structure.store'), $payload)
    ->assertCreated();

$versionId = $created->json('data.id');
$this->getJson(route('report-structure.show', $versionId))
    ->assertOk()
    ->assertJsonPath('categories.0.evaluation_lists.0.criteria_mode', 'support_weighted')
    ->assertJsonPath('categories.0.evaluation_lists.0.quantity_main_criterias.0.quantity_sub_criterias.0.score_source', 'manual')
    ->assertJsonPath('categories.0.evaluation_lists.0.quantity_main_criterias.0.quantity_sub_criterias.0.require_description', true);
```

Add three negative cases:

```php
it('rejects support weights that do not total one hundred', function () use ($payload) {
    $payload['categories'][1]['evaluation_lists'][0]['quantity_main_criterias'][0]
        ['quantity_sub_criterias'][0]['score_a'] = 59;

    $this->postJson(route('report-structure.store'), $payload)
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['support_weight_total']);
});

it('rejects a support list containing quality criteria', function () use ($payload) {
    $payload['categories'][0]['evaluation_lists'][0]['quality_main_criterias'] = [[
        'name' => 'คุณภาพ',
        'ratio' => 100,
        'sequence' => 1,
        'quality_sub_criterias' => [],
    ]];

    $this->postJson(route('report-structure.store'), $payload)
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['criteria_type']);
});

it('rejects more than one support sub criterion in an evaluation list', function () use ($payload) {
    $sub = $payload['categories'][0]['evaluation_lists'][0]
        ['quantity_main_criterias'][0]['quantity_sub_criterias'][0];
    $payload['categories'][0]['evaluation_lists'][0]
        ['quantity_main_criterias'][0]['quantity_sub_criterias'][] = $sub;

    $this->postJson(route('report-structure.store'), $payload)
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['support_structure']);
});
```

- [ ] **Step 2: Run the API tests**

Run: `vendor/bin/pest.bat tests/Feature/Report/SupportWeightedCriteriaTest.php`
Expected: FAIL because metadata is stripped and support invariants are not validated.

- [ ] **Step 3: Add request rules and support invariant validation**

Add these rules to both store and update rule arrays:

```php
'categories.*.evaluation_lists.*.criteria_mode' => 'sometimes|in:legacy,support_weighted',
'categories.*.evaluation_lists.*.quantity_main_criterias.*.description' => 'nullable|string',
'categories.*.evaluation_lists.*.quantity_main_criterias.*.quantity_sub_criterias.*.description' => 'nullable|string',
'categories.*.evaluation_lists.*.quantity_main_criterias.*.quantity_sub_criterias.*.score_source' => 'sometimes|in:manual,workload',
'categories.*.evaluation_lists.*.quantity_main_criterias.*.quantity_sub_criterias.*.raw_score_max' => 'sometimes|numeric|gt:0',
'categories.*.evaluation_lists.*.quantity_main_criterias.*.quantity_sub_criterias.*.require_description' => 'nullable|boolean',
```

Call this private method immediately after request validation:

```php
private function validateSupportStructure(array $validated): void
{
    $supportWeight = 0.0;

    foreach ($validated['categories'] as $category) {
        foreach ($category['evaluation_lists'] ?? [] as $list) {
            if (($list['criteria_mode'] ?? EvaluationList::MODE_LEGACY)
                !== EvaluationList::MODE_SUPPORT_WEIGHTED) {
                continue;
            }

            if (! empty($list['quality_main_criterias'])) {
                throw ValidationException::withMessages([
                    'criteria_type' => ['เกณฑ์สายสนับสนุนเลือกพร้อมเกณฑ์คุณภาพไม่ได้'],
                ]);
            }

            $mainCriteria = $list['quantity_main_criterias'] ?? [];
            $subCriteria = collect($mainCriteria)
                ->flatMap(fn ($main) => $main['quantity_sub_criterias'] ?? []);

            if (count($mainCriteria) !== 1 || $subCriteria->count() !== 1) {
                throw ValidationException::withMessages([
                    'support_structure' => ['หนึ่งรายการประเมินสายสนับสนุนต้องมีหนึ่งกิจกรรมและหนึ่งตัวชี้วัด'],
                ]);
            }

            if ((float) $subCriteria->first()['score_a'] <= 0) {
                throw ValidationException::withMessages([
                    'support_weight' => ['น้ำหนักเกณฑ์สายสนับสนุนต้องมากกว่า 0'],
                ]);
            }

            $supportWeight += (float) $subCriteria->first()['score_a'];
        }
    }

    if ($supportWeight > 0 && abs($supportWeight - 100.0) > 0.001) {
        throw ValidationException::withMessages([
            'support_weight_total' => ['ผลรวมน้ำหนักเกณฑ์สายสนับสนุนต้องเท่ากับ 100'],
        ]);
    }
}
```

- [ ] **Step 4: Persist and serialize every support field**

Include `criteria_mode` in both `EvaluationList::create` calls, and include these values in both `QuantitySubCriteria::create` paths:

```php
'criteria_mode' => $evalListData['criteria_mode'] ?? EvaluationList::MODE_LEGACY,
```

```php
'description' => $qSub['description'] ?? null,
'score_source' => $qSub['score_source'] ?? QuantitySubCriteria::SOURCE_MANUAL,
'raw_score_max' => $qSub['raw_score_max'] ?? 5,
'require_description' => (bool) ($qSub['require_description'] ?? false),
```

Extend show selects and formatted response:

```php
'categories.evaluationLists' => function ($query) {
    $query->select(
        'id',
        'categorie_id',
        'criteria_version_id',
        'name',
        'sum_score',
        'sequence',
        'annotation',
        'criteria_mode'
    )->orderBy('sequence');
},
```

```php
'criteria_mode' => $evalList->criteria_mode,
'description' => $qSub->description,
'score_source' => $qSub->score_source,
'raw_score_max' => (float) $qSub->raw_score_max,
'require_description' => (bool) $qSub->require_description,
'workload_configured' => $qSub->isWorkloadSource()
    && $qSub->workloadForms->isNotEmpty()
    && $qSub->workloadForms->every(
        fn ($form) => trim((string) $form->formula_logic) !== ''
    ),
```

Eager load `categories.evaluationLists.quantitySubCriterias.workloadForms` in `show()`.

- [ ] **Step 5: Add and implement the in-use support version lock**

Test:

```php
$reportData = \App\Models\ReportData::where('criteria_version_id', $versionId)->firstOrFail();
\App\Models\Reports::factory()->create(['report_data_id' => $reportData->id]);

$this->putJson(route('report-structure.update', $versionId), $payload)
    ->assertStatus(409)
    ->assertJsonPath('success', false);
```

Controller guard before destructive update:

```php
$supportVersionInUse = $version->evaluationLists()
    ->where('criteria_mode', EvaluationList::MODE_SUPPORT_WEIGHTED)
    ->exists()
    && $version->reportDatas()
        ->whereHas('reports')
        ->exists();

if ($supportVersionInUse) {
    return response()->json([
        'success' => false,
        'message' => 'เกณฑ์สายสนับสนุนรุ่นนี้เริ่มใช้งานแล้ว กรุณาสร้าง CriteriaVersion ใหม่',
    ], 409);
}
```

- [ ] **Step 6: Run API and existing report structure tests**

Run: `vendor/bin/pest.bat tests/Feature/Report/SupportWeightedCriteriaTest.php tests/Feature/Report/ReportStructureTest.php`
Expected: PASS; existing create/edit/delete tests remain green.

- [ ] **Step 7: Commit**

```powershell
git add app/Http/Controllers/ReportStructureController.php tests/Feature/Report/SupportWeightedCriteriaTest.php
git commit -m "feat: persist support weighted criteria"
```

---

### Task 4: Add accessible create/edit UI for the support template

**Files:**
- Create: `resources/views/criteria_config/partials/support-criteria-template.blade.php`
- Modify: `resources/views/criteria_config/create.blade.php`
- Modify: `resources/views/criteria_config/edit.blade.php`
- Modify: `resources/views/criteria_config/partials/create-evaluation-template.blade.php:73-93`
- Modify: `resources/views/criteria_config/partials/edit-evaluation-template.blade.php:51-66`
- Modify: `resources/views/criteria_config/partials/create-script.blade.php:303-1236`
- Modify: `resources/views/criteria_config/partials/script-edit-criteria-type-handler.blade.php`
- Modify: `resources/views/criteria_config/partials/script-edit-collect-form-data.blade.php`
- Modify: `resources/views/criteria_config/partials/script-edit-populate-helpers.blade.php`
- Modify: `resources/views/criteria_config/partials/script-edit-event-listeners.blade.php`
- Create: `tests/Feature/SupportCriteriaUiTest.php`

**Interfaces:**
- Produces DOM hooks `.support_criteria_type`, `.support_criteria_container`, `.support_activity_name`, `.support_indicator`, `.support_weight`, `.support_raw_score_max`, `.support_score_source`
- Produces payload shape consumed by Task 3

- [ ] **Step 1: Write failing Blade contract tests**

```php
<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('create criteria view contains accessible exclusive support controls', function () {
    $html = view('criteria_config.create')->render();

    expect($html)
        ->toContain('support_criteria_type')
        ->toContain('type="radio"')
        ->toContain('support_weight_summary')
        ->toContain('aria-live="polite"')
        ->toContain('support_score_source')
        ->toContain('raw_score_max');
});

test('edit criteria view can populate support metadata and workload status', function () {
    $html = view('criteria_config.edit', ['id' => 1])->render();

    expect($html)
        ->toContain('criteria_mode')
        ->toContain('workload_configured')
        ->toContain('support_weight_total')
        ->toContain('toggleSupportTypeAvailability');
});
```

- [ ] **Step 2: Run UI contract tests**

Run: `vendor/bin/pest.bat tests/Feature/SupportCriteriaUiTest.php`
Expected: FAIL because the support controls do not exist.

- [ ] **Step 3: Replace criteria type checkboxes with a radio group and add the support option**

Use this structure in create and edit templates; `updateEvaluationSequences` assigns a unique `name` such as `criteria_type_1_2` to the three radios in each cloned block:

```blade
<fieldset class="mb-6 criteria_type_fieldset">
    <legend class="block text-sm font-medium text-gray-700 mb-2">ประเภทเกณฑ์</legend>
    <div class="criteria_type_check_group flex flex-wrap gap-6 text-gray-900">
        <label class="flex items-center gap-2">
            <input type="radio" class="criteria_type quantity_criteria_type h-5 w-5"
                value="quantity">
            <span class="text-sm">เกณฑ์ด้านปริมาณ</span>
        </label>
        <label class="flex items-center gap-2">
            <input type="radio" class="criteria_type quality_criteria_type h-5 w-5"
                value="quality">
            <span class="text-sm">เกณฑ์ด้านคุณภาพ</span>
        </label>
        <label class="support_criteria_option hidden flex items-center gap-2">
            <input type="radio" class="criteria_type support_criteria_type h-5 w-5"
                value="support" aria-describedby="support-type-help">
            <span class="text-sm">เกณฑ์สายสนับสนุน</span>
        </label>
    </div>
    <p id="support-type-help" class="support_type_help mt-2 text-xs text-gray-600">
        แสดงสำหรับประเภทการประเมินกลุ่มสนับสนุน และเลือกพร้อมเกณฑ์ชนิดอื่นไม่ได้
    </p>
</fieldset>
```

- [ ] **Step 4: Create the dedicated support form partial**

```blade
<section class="support_criteria_container hidden rounded-lg border-l-4 border-blue-500 bg-blue-50 p-5"
    aria-hidden="true">
    <h3 class="text-lg font-bold text-blue-900">เกณฑ์สายสนับสนุน</h3>
    <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
        <label class="block">
            <span class="text-sm font-medium text-gray-700">ชื่อกิจกรรม/โครงการ/งาน *</span>
            <input type="text" class="support_activity_name mt-2 block w-full rounded-lg border-gray-300"
                required disabled>
        </label>
        <label class="block">
            <span class="text-sm font-medium text-gray-700">ตัวชี้วัดหรือเกณฑ์การประเมิน *</span>
            <input type="text" class="support_indicator mt-2 block w-full rounded-lg border-gray-300"
                required disabled>
        </label>
        <label class="block">
            <span class="text-sm font-medium text-gray-700">น้ำหนัก (%) *</span>
            <input type="number" min="0.01" max="100" step="0.01"
                class="support_weight mt-2 block w-full rounded-lg border-gray-300"
                aria-describedby="support-weight-help" required disabled>
        </label>
        <label class="block">
            <span class="text-sm font-medium text-gray-700">คะแนนดิบสูงสุด *</span>
            <input type="number" min="0.01" step="0.01" value="5"
                class="support_raw_score_max mt-2 block w-full rounded-lg border-gray-300"
                required disabled>
        </label>
        <label class="block">
            <span class="text-sm font-medium text-gray-700">แหล่งคะแนน *</span>
            <select class="support_score_source mt-2 block w-full rounded-lg border-gray-300"
                required disabled>
                <option value="manual">ผู้ประเมินกรอกคะแนน</option>
                <option value="workload">คำนวณจาก Workload</option>
            </select>
        </label>
        <div class="support_workload_status rounded-lg border border-blue-200 bg-white p-3 text-sm"
            aria-live="polite">
            เลือก Workload แล้วบันทึกโครงเกณฑ์เพื่อไปตั้งค่าสูตร
        </div>
        <a class="support_workload_config_link hidden rounded-md bg-blue-700 px-3 py-2 text-center text-sm font-semibold text-white focus:ring-2"
            href="/workload-config">
            ตั้งค่าสูตร Workload
        </a>
    </div>
    <label class="mt-4 block">
        <span class="text-sm font-medium text-gray-700">คำอธิบายระดับหรือแนวทางให้คะแนน</span>
        <textarea class="support_description mt-2 block w-full rounded-lg border-gray-300"
            rows="4" disabled></textarea>
    </label>
    <div class="mt-4 flex flex-wrap gap-6">
        <label class="flex items-center gap-2">
            <input type="checkbox" class="support_require_evidence" disabled>
            <span class="text-sm">บังคับแนบหลักฐาน</span>
        </label>
        <label class="flex items-center gap-2">
            <input type="checkbox" class="support_require_description" disabled>
            <span class="text-sm">บังคับกรอกรายละเอียดผลงาน</span>
        </label>
    </div>
    <p id="support-weight-help" class="support_weight_help mt-4 text-sm text-gray-700">
        น้ำหนักรวมของเกณฑ์สายสนับสนุนทั้งฉบับต้องเท่ากับ 100
    </p>
    <p class="support_weight_summary mt-2 font-semibold text-blue-900" aria-live="polite">
        น้ำหนักรวม 0.00 / 100.00
    </p>
</section>
```

Include this partial after quantity/quality sections in both evaluation templates.

- [ ] **Step 5: Implement selection, visibility, disabled state, unique radio names, and weight summary**

Add these functions to create script and corresponding edit partials:

```javascript
const SUPPORT_ASSESSMENT_TYPE = 'กลุ่มสนับสนุน';

function setContainerEnabled(container, enabled) {
    container.classList.toggle('hidden', !enabled);
    container.setAttribute('aria-hidden', enabled ? 'false' : 'true');
    container.querySelectorAll('input, select, textarea').forEach((field) => {
        field.disabled = !enabled;
    });
}

function applyCriteriaType(evaluationBlock) {
    setContainerEnabled(
        evaluationBlock.querySelector('.quantity_main_criterias_container'),
        evaluationBlock.querySelector('.quantity_criteria_type').checked
    );
    setContainerEnabled(
        evaluationBlock.querySelector('.quality_main_criterias_container'),
        evaluationBlock.querySelector('.quality_criteria_type').checked
    );
    setContainerEnabled(
        evaluationBlock.querySelector('.support_criteria_container'),
        evaluationBlock.querySelector('.support_criteria_type').checked
    );
}

function toggleSupportTypeAvailability() {
    const enabled = document.getElementById('assessment_type').value === SUPPORT_ASSESSMENT_TYPE;

    document.querySelectorAll('.evaluation_list_block').forEach((block) => {
        const option = block.querySelector('.support_criteria_option');
        const radio = block.querySelector('.support_criteria_type');
        option.classList.toggle('hidden', !enabled);

        if (!enabled && radio.checked) {
            const confirmed = window.confirm(
                'การเปลี่ยนประเภทการประเมินจะล้างข้อมูลเกณฑ์สายสนับสนุนในแบบฟอร์มนี้'
            );
            if (confirmed) {
                radio.checked = false;
                applyCriteriaType(block);
            } else {
                document.getElementById('assessment_type').value = SUPPORT_ASSESSMENT_TYPE;
                option.classList.remove('hidden');
            }
        }
    });
}

function updateSupportWeightSummary() {
    const total = Array.from(document.querySelectorAll(
        '.support_criteria_type:checked'
    )).reduce((sum, radio) => {
        const block = radio.closest('.evaluation_list_block');
        return sum + Number(block.querySelector('.support_weight').value || 0);
    }, 0);

    document.querySelectorAll('.support_weight_summary').forEach((summary) => {
        summary.textContent = 'น้ำหนักรวม ' + total.toFixed(2) + ' / 100.00';
        summary.classList.toggle('text-red-700', Math.abs(total - 100) > 0.001);
    });

    return total;
}
```

In the sequence updater:

```javascript
evaluationBlock.querySelectorAll('.criteria_type').forEach((radio) => {
    radio.name = 'criteria_type_' + categoryIndex + '_' + evaluationIndex;
});

const supportTypeHelp = evaluationBlock.querySelector('.support_type_help');
const supportWeightHelp = evaluationBlock.querySelector('.support_weight_help');
supportTypeHelp.id = 'support-type-help-' + categoryIndex + '-' + evaluationIndex;
supportWeightHelp.id = 'support-weight-help-' + categoryIndex + '-' + evaluationIndex;
evaluationBlock.querySelector('.support_criteria_type')
    .setAttribute('aria-describedby', supportTypeHelp.id);
evaluationBlock.querySelector('.support_weight')
    .setAttribute('aria-describedby', supportWeightHelp.id);
```

Bind `change` on `assessment_type` and criteria radios, and `input` on `support_weight`. After clone/populate/reset, call all four functions.

Add one page-level error summary immediately above the form actions in create and edit pages:

```blade
<div id="support-form-errors" class="hidden rounded-lg border border-red-300 bg-red-50 p-4 text-red-900"
    role="alert" aria-live="assertive" tabindex="-1"></div>
```

Use this helper for client and HTTP 422 support errors:

```javascript
function showSupportFormErrors(messages) {
    const summary = document.getElementById('support-form-errors');
    summary.textContent = messages.join(' ');
    summary.classList.remove('hidden');
    summary.focus();
}
```

When weight total is invalid, set `aria-invalid="true"` on every active support weight input and append `support-form-errors` to its `aria-describedby`. Clear both when the total becomes 100.

- [ ] **Step 6: Serialize and populate the support payload**

When support is selected, set:

```javascript
const supportChecked = evalBlock.querySelector('.support_criteria_type').checked;
const evalData = {
    name: evalBlock.querySelector('.eval_name').value.trim(),
    sum_score: Number(evalBlock.querySelector('.sum_score').value),
    sequence: evalIndex + 1,
    annotation: evalBlock.querySelector('.annotation').value.trim() || null,
    criteria_mode: supportChecked ? 'support_weighted' : 'legacy',
    quantity_main_criterias: [],
    quality_main_criterias: []
};

if (supportChecked) {
    const weight = Number(evalBlock.querySelector('.support_weight').value);
    const rawMaximum = Number(evalBlock.querySelector('.support_raw_score_max').value);
    evalData.sum_score = Number(((weight * rawMaximum) / 100).toFixed(2));
    evalData.quantity_main_criterias.push({
        name: evalBlock.querySelector('.support_activity_name').value.trim(),
        tooltips: null,
        description: null,
        formula: null,
        quantity_sub_criterias: [{
            name: evalBlock.querySelector('.support_indicator').value.trim(),
            sequence: 1,
            score_a: weight,
            score_b: 100,
            description: evalBlock.querySelector('.support_description').value.trim() || null,
            score_source: evalBlock.querySelector('.support_score_source').value,
            raw_score_max: rawMaximum,
            require_description: evalBlock.querySelector('.support_require_description').checked,
            require_evidence: evalBlock.querySelector('.support_require_evidence').checked
        }]
    });
}
```

Before showing the confirmation modal, reject a support total other than 100, focus the first weight field, set `aria-invalid="true"`, and leave the server validation as the authority.

For edit populate, read the first main/sub from `quantity_main_criterias` when `criteria_mode === 'support_weighted'`, fill all fields, and render:

```javascript
status.textContent = sub.workload_configured
    ? 'ตั้งค่าสูตร Workload แล้ว'
    : 'ยังไม่ได้ตั้งค่าสูตร Workload';

const configLink = newBlock.querySelector('.support_workload_config_link');
const usesWorkload = sub.score_source === 'workload';
configLink.classList.toggle('hidden', !usesWorkload);
configLink.href = '/workload-config?quant_sub_criteria_id=' +
    encodeURIComponent(sub.quantity_sub_criteria_id);
```

- [ ] **Step 7: Run UI and API tests**

Run: `vendor/bin/pest.bat tests/Feature/SupportCriteriaUiTest.php tests/Feature/Report/SupportWeightedCriteriaTest.php`
Expected: PASS.

- [ ] **Step 8: Commit**

```powershell
git add resources/views/criteria_config tests/Feature/SupportCriteriaUiTest.php
git commit -m "feat: add support criteria template UI"
```

---

### Task 5: Restrict and calculate support Workload through the existing builder

**Files:**
- Modify: `app/Http/Controllers/Workload/WorkloadConfigController.php:31-250`
- Modify: `app/Http/Controllers/Evaluatee/EvaluationWorkloadController.php:146-196`
- Modify: `tests/Feature/WorkloadConfigControllerTest.php`
- Modify: `tests/Feature/SupportWorkloadScoreTest.php`

**Interfaces:**
- Consumes `QuantitySubCriteria::isWorkloadSource()`
- Consumes `WorkloadQuantityScoreService::recalculate()`

- [ ] **Step 1: Write failing source and lock tests**

```php
it('does not expose a manual support criterion in workload navigation', function () {
    $list = EvaluationList::factory()->supportWeighted()->create();
    $criterion = QuantitySubCriteria::factory()->create([
        'evaluation_list_id' => $list->id,
        'criteria_version_id' => $list->criteria_version_id,
        'score_source' => QuantitySubCriteria::SOURCE_MANUAL,
    ]);

    $this->actingAs($this->admin)
        ->getJson(route('workload-config.quantity-sub-criterias', [
            'quant_sub_criteria_id' => $criterion->id,
        ]))
        ->assertUnprocessable();
});

it('locks support workload formula after a report exists', function () {
    $reportData = ReportData::factory()->create([
        'criteria_version_id' => $criterion->criteria_version_id,
    ]);
    Reports::factory()->create(['report_data_id' => $reportData->id]);

    $this->actingAs($this->admin)
        ->postJson(route('workload-config.save'), $validPayload)
        ->assertStatus(409);
});
```

- [ ] **Step 2: Run targeted tests**

Run: `vendor/bin/pest.bat tests/Feature/WorkloadConfigControllerTest.php tests/Feature/SupportWorkloadScoreTest.php`
Expected: FAIL because manual support items are accepted and formula changes are not locked.

- [ ] **Step 3: Guard navigation, blocks, and save**

Add:

```php
private function ensureWorkloadConfigurable(QuantitySubCriteria $criterion): void
{
    $criterion->loadMissing('evaluationList');

    if ($criterion->isSupportWeighted() && ! $criterion->isWorkloadSource()) {
        throw ValidationException::withMessages([
            'quant_sub_criteria_id' => ['เกณฑ์ manual ไม่ใช้การตั้งค่า Workload'],
        ]);
    }
}

private function supportVersionHasReports(QuantitySubCriteria $criterion): bool
{
    if (! $criterion->isSupportWeighted()) {
        return false;
    }

    return \App\Models\ReportData::query()
        ->where('criteria_version_id', $criterion->criteria_version_id)
        ->whereHas('reports')
        ->exists();
}
```

Call `ensureWorkloadConfigurable()` after loading the criterion in all three endpoints. In `save()`, return HTTP 409 before `DB::transaction` when `supportVersionHasReports()` is true.

When building sibling navigation for a support list, build the query before calling `get()`:

```php
$itemsQuery = QuantitySubCriteria::query()
    ->where('evaluation_list_id', $active->evaluation_list_id)
    ->orderBy('sequence');

if ($active->isSupportWeighted()) {
    $itemsQuery->where('score_source', QuantitySubCriteria::SOURCE_WORKLOAD);
}

$items = $itemsQuery->get([
    'id',
    'name',
    'sequence',
    'quantity_main_criteria_id',
    'evaluation_list_id',
    'score_source',
]);
```

- [ ] **Step 4: Replace controller arithmetic with the aggregate service**

Change the action signature and body:

```php
public function storeWorkloadScore(
    Request $request,
    WorkloadQuantityScoreService $workloadScores
) {
    $validated = $request->validate([
        'report_id' => ['required', 'integer', 'exists:reports,id'],
        'quantity_sub_criteria_id' => ['required', 'integer', 'exists:quantity_sub_criterias,id'],
    ]);

    $report = Reports::findOrFail($validated['report_id']);
    abort_unless($this->canEditReport($report), 403);

    $criterion = QuantitySubCriteria::with('evaluationList')
        ->findOrFail($validated['quantity_sub_criteria_id']);

    abort_unless(
        $criterion->criteria_version_id === $report->reportData->criteria_version_id,
        403
    );

    $workloadScores->recalculate($report->id, $criterion);

    return redirect()->back()->with(
        'success',
        'บันทึกคะแนนภาระงานรวมเรียบร้อยแล้ว'
    );
}
```

- [ ] **Step 5: Run Workload regression tests**

Run: `vendor/bin/pest.bat tests/Feature/WorkloadConfigControllerTest.php tests/Feature/WorkloadFormulaEvaluatorTest.php tests/Feature/SupportWorkloadScoreTest.php tests/Feature/Evaluation/PreviousWorkloadImportTest.php`
Expected: PASS.

- [ ] **Step 6: Commit**

```powershell
git add app/Http/Controllers/Workload/WorkloadConfigController.php app/Http/Controllers/Evaluatee/EvaluationWorkloadController.php tests/Feature/WorkloadConfigControllerTest.php tests/Feature/SupportWorkloadScoreTest.php
git commit -m "feat: integrate support criteria with workload"
```

---

### Task 6: Validate evaluatee submissions and present the support-specific evaluatee UI

**Files:**
- Create: `app/Services/SupportCriteriaSubmissionValidator.php`
- Modify: `app/Http/Controllers/Evaluatee/EvaluationScoreController.php:39-330`
- Modify: `app/Services/ReportDataService.php:210-323`
- Modify: `app/Http/Controllers/Evaluatee/DashboardEvaluateeController.php:319-479`
- Create: `resources/views/components/partials/support-weighted-evaluatee.blade.php`
- Modify: `resources/views/components/unified-evaluation.blade.php:137-260`
- Modify: `resources/views/components/unified-evaluator.blade.php:118-350`
- Create: `tests/Feature/Evaluation/SupportWeightedEvaluationTest.php`

**Interfaces:**
- Produces: `SupportCriteriaSubmissionValidator::validatePending(Reports $report, array $quantityItems, array $supportEvidence): void`
- View model adds `criteria_mode`, `score_source`, `raw_score_max` and `require_description`
- Form payload adds `support_evidence_list[quantity_sub_criteria_id][evaluation_list_id|links][]`

- [ ] **Step 1: Write failing evaluatee behavior tests**

Cover these exact cases:

```php
it('evaluatee can save manual work description without posting a raw score', function () {
    $response = $this->actingAs($evaluatee)->post(
        route('evaluation_score.store', $report->id),
        [
            'quantity_list' => [
                $manual->id => [
                    'quantity_sub_criteria_id' => $manual->id,
                    'description' => 'จัดทำระบบรับสมัครและส่งมอบแล้ว',
                ],
            ],
            'status' => 'Draft',
        ]
    );

    $response->assertRedirect();
    $this->assertDatabaseHas('quantity_scores', [
        'report_id' => $report->id,
        'quantity_sub_criteria_id' => $manual->id,
        'description' => 'จัดทำระบบรับสมัครและส่งมอบแล้ว',
        'score_C' => null,
        'score_D' => null,
    ]);
});

it('rejects pending when required support description is missing', function () {
    $this->actingAs($evaluatee)
        ->post(route('evaluation_score.store', $report->id), [
            'quantity_list' => [
                $manual->id => ['quantity_sub_criteria_id' => $manual->id],
            ],
            'status' => 'Pending',
        ])
        ->assertSessionHasErrors('support_description');
});

it('rejects pending when required support evidence is missing', function () {
    $this->actingAs($evaluatee)
        ->post(route('evaluation_score.store', $report->id), $pendingPayload)
        ->assertSessionHasErrors('support_evidence');
});

it('rejects pending when a workload support formula is incomplete', function () {
    WorkloadForm::create([
        'quantity_sub_criteria_id' => $workload->id,
        'formula_logic' => '',
    ]);

    $this->actingAs($evaluatee)
        ->post(route('evaluation_score.store', $report->id), $pendingPayload)
        ->assertSessionHasErrors('support_workload');
});
```

Render assertions:

```php
$response->assertSee('รายละเอียดผลงาน')
    ->assertDontSee('คะแนน A')
    ->assertDontSee('คะแนน B')
    ->assertDontSee('ด้านปริมาณ');
```

- [ ] **Step 2: Run evaluatee tests**

Run: `vendor/bin/pest.bat tests/Feature/Evaluation/SupportWeightedEvaluationTest.php`
Expected: FAIL because support submission validation and UI do not exist.

- [ ] **Step 3: Implement Pending validation**

```php
<?php

namespace App\Services;

use App\Models\EvaluationList;
use App\Models\QuantitySubCriteria;
use App\Models\Reports;
use Illuminate\Validation\ValidationException;

final class SupportCriteriaSubmissionValidator
{
    public function validatePending(
        Reports $report,
        array $quantityItems,
        array $supportEvidence
    ): void {
        $report->loadMissing('reportData');

        $criteria = QuantitySubCriteria::query()
            ->with(['evaluationList', 'workloadForms'])
            ->where('criteria_version_id', $report->reportData->criteria_version_id)
            ->whereHas('evaluationList', fn ($query) => $query->where(
                'criteria_mode',
                EvaluationList::MODE_SUPPORT_WEIGHTED
            ))
            ->get();

        $items = collect($quantityItems)->keyBy(
            fn ($item) => (int) ($item['quantity_sub_criteria_id'] ?? 0)
        );
        $evidence = collect($supportEvidence)->keyBy(
            fn ($item) => (int) ($item['quantity_sub_criteria_id'] ?? 0)
        );

        $missingDescriptions = $criteria
            ->filter(fn ($criterion) => $criterion->require_description && $criterion->isManualSource())
            ->filter(function ($criterion) use ($items) {
                return trim((string) ($items->get($criterion->id)['description'] ?? '')) === '';
            })
            ->pluck('name');

        $missingWorkloadDescriptions = $criteria
            ->filter(fn ($criterion) => $criterion->require_description && $criterion->isWorkloadSource())
            ->filter(function ($criterion) use ($report) {
                return ! \App\Models\WorkloadEntry::query()
                    ->where('report_id', $report->id)
                    ->whereIn('workload_form_id', $criterion->workloadForms->pluck('id'))
                    ->exists();
            })
            ->pluck('name');

        $missingDescriptions = $missingDescriptions
            ->merge($missingWorkloadDescriptions)
            ->unique()
            ->values();

        $missingEvidence = $criteria
            ->filter(fn ($criterion) => $criterion->require_evidence && $criterion->isManualSource())
            ->filter(function ($criterion) use ($evidence) {
                return collect($evidence->get($criterion->id)['links'] ?? [])
                    ->filter(fn ($link) => trim((string) $link) !== '')
                    ->isEmpty();
            })
            ->pluck('name');

        $incompleteWorkload = $criteria
            ->filter(fn ($criterion) => $criterion->isWorkloadSource())
            ->filter(function ($criterion) {
                return $criterion->workloadForms->isEmpty()
                    || $criterion->workloadForms->contains(
                        fn ($form) => trim((string) $form->formula_logic) === ''
                    );
            })
            ->pluck('name');

        $missingWorkloadEvidence = $criteria
            ->filter(fn ($criterion) => $criterion->require_evidence && $criterion->isWorkloadSource())
            ->filter(function ($criterion) use ($report) {
                $entryIds = \App\Models\WorkloadEntry::query()
                    ->where('report_id', $report->id)
                    ->whereIn('workload_form_id', $criterion->workloadForms->pluck('id'))
                    ->pluck('id');

                if ($entryIds->isEmpty()) {
                    return true;
                }

                $coveredEntryIds = \App\Models\EvidenceAnswer::query()
                    ->where('report_id', $report->id)
                    ->whereIn('workload_entry_id', $entryIds)
                    ->whereNotNull('link')
                    ->distinct()
                    ->pluck('workload_entry_id');

                return $coveredEntryIds->count() !== $entryIds->count();
            })
            ->pluck('name');

        $errors = [];
        if ($missingDescriptions->isNotEmpty()) {
            $errors['support_description'] = [
                'กรุณากรอกรายละเอียดผลงาน: '.$missingDescriptions->join(', '),
            ];
        }
        if ($missingEvidence->isNotEmpty()) {
            $errors['support_evidence'] = [
                'กรุณาแนบหลักฐาน: '.$missingEvidence->join(', '),
            ];
        }
        if ($missingWorkloadEvidence->isNotEmpty()) {
            $errors['support_workload_evidence'] = [
                'กรุณาแนบหลักฐาน Workload ให้ครบ: '.$missingWorkloadEvidence->join(', '),
            ];
        }
        if ($incompleteWorkload->isNotEmpty()) {
            $errors['support_workload'] = [
                'ยังไม่ได้ตั้งค่า Workload ให้ครบ: '.$incompleteWorkload->join(', '),
            ];
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }
}
```

- [ ] **Step 4: Save evaluatee descriptions/evidence without accepting manual scores**

Add validation rules:

```php
'support_evidence_list' => 'nullable|array',
'support_evidence_list.*.quantity_sub_criteria_id' => 'required|integer|exists:quantity_sub_criterias,id',
'support_evidence_list.*.evaluation_list_id' => 'required|integer|exists:evaluation_lists,id',
'support_evidence_list.*.links' => 'nullable|array',
'support_evidence_list.*.links.*' => 'nullable|string',
```

When status is Pending, call the validator and recalculate every support Workload criterion before beginning the transaction:

```php
if (($validated['status'] ?? 'Draft') === 'Pending') {
    $supportValidator->validatePending(
        $report,
        $validated['quantity_list'] ?? [],
        $validated['support_evidence_list'] ?? []
    );

    QuantitySubCriteria::query()
        ->with('evaluationList')
        ->where('criteria_version_id', $report->reportData->criteria_version_id)
        ->where('score_source', QuantitySubCriteria::SOURCE_WORKLOAD)
        ->whereHas('evaluationList', fn ($query) => $query->where(
            'criteria_mode',
            EvaluationList::MODE_SUPPORT_WEIGHTED
        ))
        ->each(fn ($criterion) => $workloadScores->recalculate($report->id, $criterion));
}
```

Inject `SupportCriteriaSubmissionValidator $supportValidator` and `WorkloadQuantityScoreService $workloadScores` into the action. For support mode, do not delete all quantity rows. Use:

```php
QuantityScore::updateOrCreate(
    [
        'report_id' => $reportId,
        'quantity_sub_criteria_id' => $subCriteriaId,
    ],
    [
        'description' => $description,
        'modifier_user_id' => null,
        'modifier_role' => null,
    ]
);
```

Ignore any posted `score_C` for support manual/workload criteria. Keep the existing delete-and-create branch unchanged for legacy lists.

In the legacy branch, replace the inline `A * C / B` arithmetic with:

```php
$result = $quantityScoreCalculator->fromManual($subCriteria->load('evaluationList'), $scoreC);
$scoreC = $result['score_C'];
$scoreD = $result['score_D'];
```

When clearing ordinary form evidence, preserve evidence attached to Workload entries:

```php
EvidenceAnswer::where('report_id', $reportId)
    ->whereNull('workload_entry_id')
    ->delete();
```

Save support evidence with `quality_main_criteria_id => null` and the submitted `evaluation_list_id`. The one-support-criterion-per-list invariant from Task 3 makes this mapping unambiguous.

- [ ] **Step 5: Add support metadata to both view-model builders**

In each evaluation list array:

```php
'criteria_mode' => $list->criteria_mode,
```

In each quantity sub array:

```php
'score_source' => $subCriteria->score_source,
'raw_score_max' => (float) $subCriteria->raw_score_max,
'require_description' => (bool) $subCriteria->require_description,
```

- [ ] **Step 6: Create and route the evaluatee support partial**

At the start of the quantity section in `unified-evaluation.blade.php`:

```blade
@if(($evaluationList['criteria_mode'] ?? 'legacy') === 'support_weighted')
    @include('components.partials.support-weighted-evaluatee', [
        'evaluationList' => $evaluationList,
        'report' => $report,
        'workloadMap' => $workloadMap,
    ])
@elseif(count($evaluationList['quantity_items']) > 0)
    {{-- existing legacy quantity section remains here --}}
@endif
```

The new partial extracts the only support sub criterion and renders:

```blade
@php
    $main = collect($evaluationList['quantity_items'] ?? [])->first();
    $criterion = collect($main['sub_criterias'] ?? [])->first();
    $isWorkload = ($criterion['score_source'] ?? 'manual') === 'workload';
@endphp

<section class="rounded-xl border border-blue-200 bg-blue-50 p-5">
    <h3 class="text-lg font-bold text-blue-900">{!! \App\Support\SafeHtml::richText($main['name']) !!}</h3>
    <div class="mt-2 text-gray-800">{!! \App\Support\SafeHtml::richText($criterion['name']) !!}</div>
    @if(!empty($criterion['description']))
        <div class="mt-3 rounded-lg bg-white p-3 text-sm text-gray-700">
            {!! \App\Support\SafeHtml::richText($criterion['description']) !!}
        </div>
    @endif

    @if($isWorkload)
        <div class="mt-4 flex flex-wrap items-center gap-3">
            <span>คะแนนดิบ {{ $criterion['tor_compliant'] === '' ? '-' : $criterion['tor_compliant'] }}
                / {{ $criterion['raw_score_max'] }}</span>
            <span>คะแนนถ่วงน้ำหนัก {{ $criterion['score_d'] === '' ? '-' : $criterion['score_d'] }}</span>
            <a href="{{ route('evaluatee.workload', [
                'report_id' => $report->id,
                'quantity_sub_criteria_id' => $criterion['id'],
            ]) }}" class="rounded-md bg-blue-700 px-3 py-2 text-sm font-semibold text-white focus:ring-2">
                จัดการข้อมูล Workload
            </a>
        </div>
    @else
        <input type="hidden"
            name="quantity_list[{{ $criterion['id'] }}][quantity_sub_criteria_id]"
            value="{{ $criterion['id'] }}">
        <label class="mt-4 block" for="support-description-{{ $criterion['id'] }}">
            <span class="text-sm font-semibold">รายละเอียดผลงาน</span>
            <textarea id="support-description-{{ $criterion['id'] }}"
                name="quantity_list[{{ $criterion['id'] }}][description]"
                class="mt-2 block w-full rounded-lg border-gray-300"
                @required($criterion['require_description'])>{{ $criterion['score_description'] }}</textarea>
        </label>
        <input type="hidden"
            name="support_evidence_list[{{ $criterion['id'] }}][quantity_sub_criteria_id]"
            value="{{ $criterion['id'] }}">
        <input type="hidden"
            name="support_evidence_list[{{ $criterion['id'] }}][evaluation_list_id]"
            value="{{ $evaluationList['id'] }}">
        <label class="mt-4 block" for="support-evidence-{{ $criterion['id'] }}">
            <span class="text-sm font-semibold">ลิงก์หลักฐาน</span>
            <input id="support-evidence-{{ $criterion['id'] }}" type="url"
                name="support_evidence_list[{{ $criterion['id'] }}][links][]"
                class="mt-2 block w-full rounded-lg border-gray-300"
                @required($criterion['require_evidence'])>
        </label>
    @endif
</section>
```

Route support mode in the readonly evaluator component to the review partial created in Task 7, so a submitted evaluatee never sees legacy A/B/C/D labels.

- [ ] **Step 7: Run evaluatee and legacy tests**

Run: `vendor/bin/pest.bat tests/Feature/Evaluation/SupportWeightedEvaluationTest.php tests/Feature/Evaluation/EvaluateeTest.php`
Expected: PASS.

- [ ] **Step 8: Commit**

```powershell
git add app/Services/SupportCriteriaSubmissionValidator.php app/Http/Controllers/Evaluatee/EvaluationScoreController.php app/Services/ReportDataService.php app/Http/Controllers/Evaluatee/DashboardEvaluateeController.php resources/views/components tests/Feature/Evaluation/SupportWeightedEvaluationTest.php
git commit -m "feat: add support evaluatee workflow"
```

---

### Task 7: Score manual support criteria in evaluator and approval roles

**Files:**
- Create: `resources/views/components/partials/support-weighted-review.blade.php`
- Modify: `resources/views/components/unified-evaluator.blade.php`
- Modify: `resources/views/components/unified-director.blade.php`
- Modify: `app/Http/Controllers/EvaluatorScoreController.php:72-153`
- Modify: `app/Http/Controllers/Manager/ManagerScoreController.php:73-146`
- Modify: `app/Http/Controllers/Director/DirectorScoreController.php:70-143`
- Modify: `app/Http/Controllers/ReportController.php:117-213`
- Modify: `tests/Feature/Evaluation/SupportWeightedEvaluationTest.php`
- Modify: `tests/Feature/Evaluation/EvaluatorTest.php`
- Modify: `tests/Feature/Evaluation/ManagerTest.php`
- Modify: `tests/Feature/Evaluation/DirectorTest.php`

**Interfaces:**
- Consumes `QuantityScoreCalculator::fromManual()`
- Support reviewer form posts `quantity_list[id][score_C]` only for `score_source=manual`
- Workload support rows submit no score fields and preserve their stored `QuantityScore`

- [ ] **Step 1: Write failing reviewer/approver tests**

```php
it('evaluator scores manual support and preserves workload score', function () {
    QuantityScore::factory()->create([
        'report_id' => $report->id,
        'quantity_sub_criteria_id' => $workload->id,
        'score_C' => 5,
        'score_D' => 3,
    ]);

    $this->actingAs($evaluator)->post(
        route('evaluator.evaluator_score.store', $report->id),
        [
            'quantity_list' => [
                $manual->id => [
                    'quantity_sub_criteria_id' => $manual->id,
                    'score_C' => 4,
                    'description' => 'ผลงานครบถ้วน',
                ],
            ],
            'status' => 'Evaluator_draft',
        ]
    )->assertRedirect();

    $this->assertDatabaseHas('quantity_scores', [
        'report_id' => $report->id,
        'quantity_sub_criteria_id' => $manual->id,
        'score_C' => 4,
        'score_D' => 1.6,
    ]);
    $this->assertDatabaseHas('quantity_scores', [
        'report_id' => $report->id,
        'quantity_sub_criteria_id' => $workload->id,
        'score_C' => 5,
        'score_D' => 3,
    ]);
});

it('rejects forged manual score for workload support', function () {
    $payload['quantity_list'][$workload->id] = [
        'quantity_sub_criteria_id' => $workload->id,
        'score_C' => 1,
    ];

    $this->actingAs($evaluator)
        ->post(route('evaluator.evaluator_score.store', $report->id), $payload)
        ->assertSessionHasErrors('quantity_list');
});

it('rejects manual support score above raw maximum', function () {
    $payload['quantity_list'][$manual->id]['score_C'] = 5.01;

    $this->actingAs($evaluator)
        ->post(route('evaluator.evaluator_score.store', $report->id), $payload)
        ->assertSessionHasErrors('quantity_list');
});
```

Repeat one successful weighted assertion through manager and director routes to prove all three controllers use identical semantics.

- [ ] **Step 2: Run role tests**

Run: `vendor/bin/pest.bat tests/Feature/Evaluation/SupportWeightedEvaluationTest.php`
Expected: FAIL because controllers still delete all quantity scores and use legacy arithmetic.

- [ ] **Step 3: Inject and use the calculator in all reviewer controllers**

Add constructor dependency beside `ReportDataService`:

```php
public function __construct(
    ReportDataService $reportDataService,
    private readonly QuantityScoreCalculator $quantityScoreCalculator
) {
    $this->reportDataService = $reportDataService;
}
```

Detect whether the report version contains support lists. For support mode, skip the global `QuantityScore::where('report_id', $reportId)->delete()` call and process only submitted items:

```php
$subCriteria = QuantitySubCriteria::with('evaluationList')->findOrFail($subCriteriaId);
$result = $this->quantityScoreCalculator->fromManual($subCriteria, $item['score_C']);

QuantityScore::updateOrCreate(
    [
        'quantity_sub_criteria_id' => $subCriteriaId,
        'report_id' => $reportId,
    ],
    [
        'score_C' => $result['score_C'],
        'score_D' => $result['score_D'],
        'description' => array_key_exists('description', $item)
            ? ($description !== '' ? $description : null)
            : QuantityScore::where('report_id', $reportId)
                ->where('quantity_sub_criteria_id', $subCriteriaId)
                ->value('description'),
        'modifier_user_id' => $request->user()?->id,
        'modifier_role' => $modifierRole,
    ]
);
```

Keep the current delete/create behavior for versions containing only `criteria_mode=legacy`. Pass the resulting values into `QuantityScoreHistoryRecorder`.

Because the calculator raises `ValidationException` for an out-of-range or forged-source score, add this catch before the existing generic catch in evaluator, manager, and director controllers:

```php
} catch (ValidationException $exception) {
    DB::rollBack();

    if ($request->expectsJson()) {
        return response()->json([
            'message' => 'Validation failed',
            'errors' => $exception->errors(),
        ], 422);
    }

    return back()->withErrors($exception->errors())->withInput();
} catch (Exception $exception) {
```

Import `Illuminate\Validation\ValidationException` in each controller.

Apply the same calculator rule to `ReportController::addQuantityScores()` and `updateQuantityScores()`. Remove `quantity_list.*.score_D` from accepted input and compute both stored columns from server input:

```php
$criterion = QuantitySubCriteria::with('evaluationList')
    ->findOrFail($item['quantity_sub_criteria_id']);
$result = $this->quantityScoreCalculator->fromManual($criterion, $item['score_C']);

QuantityScore::updateOrCreate(
    [
        'report_id' => $reportId,
        'quantity_sub_criteria_id' => $criterion->id,
    ],
    $result
);
```

Add an API test that posts a forged `score_D => 99` for a support manual criterion and asserts the database stores the calculator result, not 99.

In both admin score API methods, catch `ValidationException` before the generic exception and return:

```php
return response()->json([
    'message' => 'Validation failed',
    'errors' => $exception->errors(),
], 422);
```

- [ ] **Step 4: Create the shared reviewer partial**

```blade
@php
    $main = collect($evaluationList['quantity_items'] ?? [])->first();
    $criterion = collect($main['sub_criterias'] ?? [])->first();
    $isManual = ($criterion['score_source'] ?? 'manual') === 'manual';
@endphp

<section class="rounded-xl border border-blue-200 bg-white p-5">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h3 class="text-lg font-bold text-blue-900">{!! \App\Support\SafeHtml::richText($main['name']) !!}</h3>
            <div class="mt-1 text-gray-800">{!! \App\Support\SafeHtml::richText($criterion['name']) !!}</div>
        </div>
        <div class="text-sm text-gray-700">
            น้ำหนัก {{ number_format((float) $criterion['score_a'], 2) }}%
        </div>
    </div>

    @if(!empty($criterion['description']))
        <div class="mt-3 rounded-lg bg-blue-50 p-3 text-sm">
            {!! \App\Support\SafeHtml::richText($criterion['description']) !!}
        </div>
    @endif

    <div class="mt-4 grid gap-4 md:grid-cols-2">
        @if($isManual && !$readonly)
            <input type="hidden"
                name="quantity_list[{{ $criterion['id'] }}][quantity_sub_criteria_id]"
                value="{{ $criterion['id'] }}">
            <label for="support-score-{{ $criterion['id'] }}">
                <span class="text-sm font-semibold">คะแนนดิบ</span>
                <input id="support-score-{{ $criterion['id'] }}" type="number"
                    min="0" max="{{ $criterion['raw_score_max'] }}" step="0.01"
                    name="quantity_list[{{ $criterion['id'] }}][score_C]"
                    value="{{ $criterion['tor_compliant'] }}"
                    class="mt-2 block w-full rounded-lg border-gray-300"
                    aria-describedby="support-score-help-{{ $criterion['id'] }}">
                <span id="support-score-help-{{ $criterion['id'] }}" class="mt-1 text-xs text-gray-600">
                    คะแนนสูงสุด {{ $criterion['raw_score_max'] }}
                </span>
            </label>
        @else
            <div>
                <div class="text-sm font-semibold">คะแนนดิบ</div>
                <div class="mt-2">{{ $criterion['tor_compliant'] === '' ? '-' : $criterion['tor_compliant'] }}
                    / {{ $criterion['raw_score_max'] }}</div>
            </div>
        @endif
        <div>
            <div class="text-sm font-semibold">คะแนนถ่วงน้ำหนัก</div>
            <div class="mt-2">{{ $criterion['score_d'] === '' ? '-' : $criterion['score_d'] }}</div>
        </div>
    </div>

    @if(!empty($criterion['score_description']))
        <div class="mt-4 text-sm">รายละเอียดผลงาน: {{ $criterion['score_description'] }}</div>
    @endif
    @if(!empty($criterion['evidence']))
        <ul class="mt-3 list-disc pl-5 text-sm">
            @foreach($criterion['evidence'] as $link)
                <li><a class="text-blue-700 underline" href="{{ $link }}" target="_blank"
                    rel="noopener noreferrer">ดูหลักฐาน</a></li>
            @endforeach
        </ul>
    @endif
</section>
```

Use this partial before legacy quantity markup in both unified reviewer components. For `unified-director`, pass the same `$readonly`; the controller route determines whether its manual input is editable.

- [ ] **Step 5: Ensure client-side score preview uses support semantics**

Existing A/C/B preview remains valid because support stores `score_b=100`. Add `max=raw_score_max` and `data-criteria-mode="support_weighted"` to inputs, and keep the server calculator authoritative. Do not render editable score fields for workload source.

- [ ] **Step 6: Run all role and score tests**

Run: `vendor/bin/pest.bat tests/Feature/Evaluation/SupportWeightedEvaluationTest.php tests/Feature/Evaluation/EvaluatorTest.php tests/Feature/Evaluation/ManagerTest.php tests/Feature/Evaluation/DirectorTest.php tests/Feature/ScoreServiceTest.php`
Expected: PASS.

- [ ] **Step 7: Commit**

```powershell
git add app/Http/Controllers/EvaluatorScoreController.php app/Http/Controllers/Manager/ManagerScoreController.php app/Http/Controllers/Director/DirectorScoreController.php app/Http/Controllers/ReportController.php resources/views/components tests/Feature/Evaluation tests/Feature/Report
git commit -m "feat: score support criteria across review roles"
```

---

### Task 8: Verify summaries, dashboards, accessibility, and legacy regression

**Files:**
- Modify: `app/Support/EvaluationScoreSummary.php`
- Modify: `resources/views/partials/evaluator-score-summary.blade.php`
- Modify: `resources/views/dashboard/partials/show-quantity-section.blade.php`
- Modify: `resources/views/evaluator_dashboard/partials/show-quantity-section.blade.php`
- Modify: `tests/Feature/Evaluation/SupportWeightedEvaluationTest.php`
- Modify: `tests/Feature/SupportCriteriaUiTest.php`

**Interfaces:**
- Consumes persisted `quantity_scores.score_D`; no role-specific scoring formula is allowed

- [ ] **Step 1: Add a cross-view total assertion**

Create one manual result `1.6` and one Workload result `3.0` for the same report:

```php
$summary = \App\Support\EvaluationScoreSummary::fromCategoryItems(
    app(\App\Services\ReportDataService::class)
        ->getReportData($report->id)['categoryItems']
);

expect($summary['quantity'])->toBe(4.6);

$this->actingAs($evaluator)
    ->get(route('evaluator.evaluator.show', $report->id))
    ->assertOk()
    ->assertSee('4.60');

$this->actingAs($admin)
    ->get(route('dashboard.show', $report->id))
    ->assertOk()
    ->assertSee('4.60');
```

- [ ] **Step 2: Add accessibility assertions for the new controls**

```php
expect($createHtml)
    ->toContain('<fieldset')
    ->toContain('<legend')
    ->toContain('aria-describedby="support-weight-help"')
    ->toContain('aria-live="polite"')
    ->not->toContain('tabindex="1"');

expect($evaluateeHtml)
    ->toContain('for="support-description-')
    ->toContain('type="url"');
```

- [ ] **Step 3: Add explicit support labels to summaries and dashboards**

Extend `EvaluationScoreSummary::fromCategoryItems()` with a mode flag:

```php
$hasSupportWeighted = collect($categoryItems)
    ->flatMap(fn ($category) => $category['evaluation_lists'] ?? [])
    ->contains(fn ($list) => ($list['criteria_mode'] ?? 'legacy') === 'support_weighted');

return [
    'quantity' => $totalQuantityScore,
    'quality' => $totalQualityScore,
    'total' => $totalQuantityScore + $totalQualityScore,
    'has_support_weighted' => $hasSupportWeighted,
];
```

In `evaluator-score-summary.blade.php`, render `คะแนนสายสนับสนุน` when the flag is true and retain `คะแนนด้านปริมาณ (Quantity)` otherwise. In both dashboard quantity partials, branch on `criteria_mode`: support renders `น้ำหนัก/คะแนนดิบ/คะแนนถ่วงน้ำหนัก`; legacy retains its current A/B/C/D table.

- [ ] **Step 4: Run focused tests**

Run: `vendor/bin/pest.bat tests/Feature/Evaluation/SupportWeightedEvaluationTest.php tests/Feature/SupportCriteriaUiTest.php tests/Feature/ScoreServiceTest.php tests/Feature/GraphDataServiceTest.php tests/Feature/AdminDashboardQueryTest.php`
Expected: PASS with support labels and legacy labels both asserted.

- [ ] **Step 5: Run formatting and the complete automated suite**

Run:

```powershell
vendor/bin/pint.bat --test
npm run test:js
npm run build
vendor/bin/pest.bat
```

Expected:

- Pint reports no style errors.
- Node tests pass.
- Vite production build exits 0.
- Pest suite passes with no failures.

- [ ] **Step 6: Perform the manual browser acceptance pass**

Run `composer run dev` and verify:

1. Open `/criteria-configs` as admin.
2. Select `กลุ่มวิชาการ`: support radio is absent.
3. Select `กลุ่มสนับสนุน`: support radio appears and is keyboard selectable.
4. Select support: quantity and quality sections hide; support fields enable.
5. Create two support evaluation lists with weights 40 manual and 60 Workload; total announces 100.
6. Save, open edit, and verify every field plus “ยังไม่ได้ตั้งค่าสูตร Workload”.
7. Configure the Workload item at `/workload-config`; return to edit and verify “ตั้งค่าสูตร Workload แล้ว”.
8. As evaluatee, enter manual work description/evidence and Workload entries; confirm there is no manual score field.
9. Submit Pending; incomplete required data is announced and focus moves to the first invalid field.
10. As evaluator, enter manual raw score 4; Workload raw is read-only and both weighted results are visible.
11. Continue through manager/director; totals remain identical.
12. Open one legacy 2568 report; labels, editable fields, and total match the pre-change behavior.

- [ ] **Step 7: Commit final integration adjustments**

```powershell
git add app resources/views tests
git commit -m "test: verify support criteria end to end"
```

---

## Final Verification Gate

Before claiming completion, collect and report:

```powershell
git diff --check
git status --short
git log --oneline -8
vendor/bin/pint.bat --test
npm run test:js
npm run build
vendor/bin/pest.bat
```

The implementation is complete only when the support create/edit template, Workload setup, evaluatee entry, reviewer scoring, approver display, dashboards, and all legacy regression tests pass together.
