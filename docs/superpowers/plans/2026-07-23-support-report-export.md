# Support Report Export Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Export Support scores, achievement scores, criteria details, activities, evidence links, and role comments with the same score policy as the evaluation UI while enforcing report-export authorization.

**Architecture:** Introduce `ReportScoreSummary` as the shared score-policy boundary, then make `EvaluationScoreSummary`, dashboard exports, and single-report exports consume it. Reuse `SupportCriteriaReadModel` for detailed Support rows, and enforce export scope/status in `FileExportController` before workbook creation and audit logging.

**Tech Stack:** Laravel 11, PHP 8.2+, Eloquent, Maatwebsite Excel/PhpSpreadsheet, Spatie Activity Log, Pest 3

## Global Constraints

- Keep Quantity and Quality formulas unchanged.
- Keep the Support target-level count fixed at `5`.
- Preserve raw `support_score_total` in its own Export field.
- Calculate `support_achievement` from raw Support total divided by `5`.
- Cap only the Support component used by the grand total at `100`.
- Preserve the legacy combined-comment field and add all three role-specific comments.
- Show Support rows in a single-report workbook only when Support criteria exist.
- Keep dashboard workbook columns stable across every report.
- Convert Support rich text to plain text before writing it to Excel.
- Do not add external dependencies.
- Use TDD for every production-code change.

---

## File Structure

- `app/Support/ReportScoreSummary.php` — own the cross-screen/export score policy.
- `app/Support/EvaluationScoreSummary.php` — retain criterion-presence logic and delegate score policy.
- `app/Exports/ReportsExport.php` — export dashboard Support values, role comments, and corrected totals.
- `app/Exports/SingleReportExport.php` — export conditional Support summary and detailed Support rows.
- `app/Http/Controllers/FileExportController.php` — enforce export scope/status and write single-report audit logs.
- `routes/web.php` — restrict the admin export endpoint to admin and manager roles.
- `tests/Unit/Support/ReportScoreSummaryTest.php` — verify raw/capped/achievement/grand-total rules.
- `tests/Feature/ScoreServiceTest.php` — verify `EvaluationScoreSummary` delegates without changing criterion visibility.
- `tests/Feature/ReportsExportTest.php` — verify dashboard and single-report workbook content.
- `tests/Feature/ReportExportAuthorizationTest.php` — verify route, assignment, status, and audit behavior.

### Task 1: Centralize the report score policy

**Files:**
- Create: `app/Support/ReportScoreSummary.php`
- Create: `tests/Unit/Support/ReportScoreSummaryTest.php`
- Modify: `app/Support/EvaluationScoreSummary.php`
- Modify: `tests/Feature/ScoreServiceTest.php`

**Interfaces:**
- Produces: `ReportScoreSummary::fromTotals(float $quantity, float $quality, float $supportRaw): array{quantity:float,quality:float,support_raw:float,support:float,support_achievement:float,total:float}`.
- Consumes: `SupportAchievementScore::calculate(float $weightedTotal): float`.
- Preserves: `EvaluationScoreSummary::fromCategoryItems(array $categoryItems): array`, including all `has_*` and `support_target_level_count` keys.

- [ ] **Step 1: Write failing unit tests for the central score policy**

Create `tests/Unit/Support/ReportScoreSummaryTest.php`:

```php
<?php

use App\Support\ReportScoreSummary;

test('report score summary caps only the support value used by the grand total', function () {
    $summary = ReportScoreSummary::fromTotals(2.0, 3.0, 112.5);

    expect($summary)->toMatchArray([
        'quantity' => 2.0,
        'quality' => 3.0,
        'support_raw' => 112.5,
        'support' => 100.0,
        'support_achievement' => 22.5,
        'total' => 105.0,
    ]);
});

test('report score summary rounds exported values to two decimal places', function () {
    $summary = ReportScoreSummary::fromTotals(1.234, 2.345, 4.306);

    expect($summary)->toMatchArray([
        'quantity' => 1.23,
        'quality' => 2.35,
        'support_raw' => 4.31,
        'support' => 4.31,
        'support_achievement' => 0.86,
        'total' => 7.89,
    ]);
});
```

Extend the existing over-cap test in `tests/Feature/ScoreServiceTest.php`:

```php
expect($summary['support_raw'])->toBe(112.5)
    ->and($summary['support'])->toBe(100.0)
    ->and($summary['support_achievement'])->toBe(22.5)
    ->and($summary['total'])->toBe(100.0);
```

- [ ] **Step 2: Run the focused tests and verify RED**

Run:

```powershell
vendor\bin\pest tests/Unit/Support/ReportScoreSummaryTest.php tests/Feature/ScoreServiceTest.php
```

Expected: the unit test fails because `ReportScoreSummary` does not exist, and the feature assertion fails because `support_raw` is absent.

- [ ] **Step 3: Implement the central score policy**

Create `app/Support/ReportScoreSummary.php`:

```php
<?php

namespace App\Support;

final class ReportScoreSummary
{
    public const SUPPORT_TOTAL_CAP = 100.0;

    /**
     * @return array{quantity: float, quality: float, support_raw: float, support: float, support_achievement: float, total: float}
     */
    public static function fromTotals(float $quantity, float $quality, float $supportRaw): array
    {
        $support = min($supportRaw, self::SUPPORT_TOTAL_CAP);

        return [
            'quantity' => round($quantity, 2),
            'quality' => round($quality, 2),
            'support_raw' => round($supportRaw, 2),
            'support' => round($support, 2),
            'support_achievement' => SupportAchievementScore::calculate($supportRaw),
            'total' => round($quantity + $quality + $support, 2),
        ];
    }
}
```

In `EvaluationScoreSummary::fromCategoryItems()`, replace the local cap/total calculation with:

```php
$scores = ReportScoreSummary::fromTotals(
    $totalQuantityScore,
    $totalQualityScore,
    $totalSupportScore,
);

return [
    ...$scores,
    'support_target_level_count' => SupportAchievementScore::TARGET_LEVEL_COUNT,
    'has_quantity' => $hasQuantity,
    'has_quality' => $hasQuality,
    'has_support' => $hasSupport,
];
```

- [ ] **Step 4: Run focused tests and verify GREEN**

Run:

```powershell
vendor\bin\pest tests/Unit/Support/ReportScoreSummaryTest.php tests/Feature/ScoreServiceTest.php
```

Expected: all tests pass.

- [ ] **Step 5: Commit the score-policy boundary**

```powershell
git add -- app/Support/ReportScoreSummary.php app/Support/EvaluationScoreSummary.php tests/Unit/Support/ReportScoreSummaryTest.php tests/Feature/ScoreServiceTest.php
git commit -m "refactor: centralize report score totals"
```

### Task 2: Add Support scores and role comments to dashboard exports

**Files:**
- Modify: `app/Exports/ReportsExport.php`
- Create: `tests/Feature/ReportsExportTest.php`

**Interfaces:**
- Consumes: `ReportScoreSummary::fromTotals()` and the persisted `Reports::$support_score_total`.
- Consumes: `ScoreService::calculateQuantityScoresRawByReportIds()` and `ScoreService::calculateQualityScoresRawByReportIds()`.
- Produces: a stable eighteen-column dashboard workbook from columns `A:R`.

- [ ] **Step 1: Write failing dashboard-export tests**

Create `tests/Feature/ReportsExportTest.php` with `RefreshDatabase`, factories for one completed report and assignment, and these assertions:

```php
$report = Reports::factory()->create([
    'status' => 'Completed',
    'support_score_total' => 112.50,
    'support_achievement_score' => 22.50,
    'comment' => 'ความเห็นรวม',
    'evaluator_comment' => 'ความเห็นผู้ประเมิน',
    'director_comment' => 'ความเห็นกรรมการ',
    'manager_comment' => 'ความเห็นผู้บริหาร',
]);
$assignment = Assignments::factory()->create(['report_id' => $report->id]);

$export = new ReportsExport(
    Assignments::query()->whereKey($assignment->id)
);
$headings = $export->headings();
$row = $export->collection()->first()->all();

expect($headings)
    ->toContain('ผลรวมคะแนนถ่วงน้ำหนักสายสนับสนุน')
    ->toContain('คะแนนผลสัมฤทธิ์ของงาน')
    ->toContain('ความเห็นผู้ประเมิน')
    ->toContain('ความเห็นกรรมการ')
    ->toContain('ความเห็นผู้บริหาร')
    ->and($row[6])->toBe(100.0)
    ->and($row[9])->toBe(112.5)
    ->and($row[10])->toBe(22.5)
    ->and($row)->toContain('ความเห็นผู้ประเมิน', 'ความเห็นกรรมการ', 'ความเห็นผู้บริหาร');
```

Add a second test with `support_score_total = 4.30` and quantity/quality score fixtures, asserting the total equals `quantity + quality + 4.30`.

- [ ] **Step 2: Run the dashboard-export tests and verify RED**

Run:

```powershell
vendor\bin\pest tests/Feature/ReportsExportTest.php
```

Expected: failures identify missing Support/comment headings and a total that excludes Support.

- [ ] **Step 3: Batch score lookup and map rows through the central policy**

Refactor `ReportsExport::collection()` to load assignments once, collect report IDs, and batch scores:

```php
$assignments = $this->query->get();
$reportIds = $assignments->pluck('report_id')->filter()->unique()->values();
$quantityScores = ScoreService::calculateQuantityScoresRawByReportIds($reportIds);
$qualityScores = ScoreService::calculateQualityScoresRawByReportIds($reportIds);

return $assignments->map(function ($assignment, $index) use ($quantityScores, $qualityScores) {
    $report = $assignment->report;
    $scores = ReportScoreSummary::fromTotals(
        (float) ($quantityScores[$report?->id] ?? 0),
        (float) ($qualityScores[$report?->id] ?? 0),
        (float) ($report?->support_score_total ?? 0),
    );
```

Map score and comment columns in this order:

```php
$scores['total'],
$scores['quantity'],
$scores['quality'],
$scores['support_raw'],
$scores['support_achievement'],
$report?->comment,
$report?->evaluator_comment,
$report?->director_comment,
$report?->manager_comment,
```

Add matching headings and extend column widths/styles through column `R`.

- [ ] **Step 4: Run focused tests and verify GREEN**

Run:

```powershell
vendor\bin\pest tests/Feature/ReportsExportTest.php tests/Feature/ScoreServiceTest.php
```

Expected: all tests pass and exported totals include capped Support.

- [ ] **Step 5: Commit the dashboard export**

```powershell
git add -- app/Exports/ReportsExport.php tests/Feature/ReportsExportTest.php
git commit -m "feat: export support score summaries"
```

### Task 3: Add Support summary and details to single-report exports

**Files:**
- Modify: `app/Exports/SingleReportExport.php`
- Modify: `tests/Feature/ReportsExportTest.php`

**Interfaces:**
- Consumes: `SupportCriteriaReadModel::forReport(Reports $report): array<int,array<int,array<string,mixed>>>`.
- Consumes: `SafeHtml::plainText(?string $html): string`.
- Produces: `support_items` on each single-report `evaluation_lists` entry.
- Produces: conditional Support rows in `SummarySheet` and detailed Support rows in `CategorySheet`.

- [ ] **Step 1: Write failing single-report Summary tests**

In `tests/Feature/ReportsExportTest.php`, add:

```php
$categoryItems = [[
    'sequence' => 1,
    'main_categories' => 'หมวด',
    'sub_categories' => 'ย่อย',
    'evaluation_lists' => [[
        'name' => 'รายการ',
        'sum_score' => 0,
        'quantity_items' => [],
        'quality_items' => [],
        'support_items' => [['weighted_score' => 4.30]],
    ]],
]];

$rows = (new SingleReportExport($assignment, $categoryItems))
    ->sheets()[0]
    ->array();

expect($rows)
    ->toContain(['ผลรวมคะแนนถ่วงน้ำหนักสายสนับสนุน', 4.3])
    ->toContain(['คะแนนผลสัมฤทธิ์ของงาน', 0.86])
    ->toContain(['ความเห็นผู้ประเมิน', 'ความเห็นผู้ประเมิน']);
```

Add the same construction with `support_items => []` and assert the two Support labels are absent.

- [ ] **Step 2: Write failing Category-sheet tests**

Add a category fixture containing:

```php
'support_items' => [[
    'activity_name' => '<p>โครงการหนึ่ง</p>',
    'indicator' => '<p>ตัวชี้วัดหนึ่ง</p>',
    'target_value' => '5.00',
    'weight' => '20.00',
    'achieved_score' => '5.00',
    'weighted_score' => '1.00',
    'activity_entries' => [['content' => '<p>โครงการเพิ่มเติม</p>']],
    'evidence_links' => ['https://example.com/evidence'],
]],
```

Assert the Category sheet contains plain-text labels/values, the real URL, and a list total of `1.00`.

- [ ] **Step 3: Run the single-report tests and verify RED**

Run:

```powershell
vendor\bin\pest tests/Feature/ReportsExportTest.php
```

Expected: failures identify missing conditional Summary rows, `support_items`, Support detail rows, and Support list totals.

- [ ] **Step 4: Attach Support read-model data to evaluation lists**

In `SingleReportExport::getCategoryItems()` load once:

```php
$supportItemsByList = app(SupportCriteriaReadModel::class)->forReport($report);
```

Add to every evaluation-list array:

```php
'support_items' => $supportItemsByList[$list->id] ?? [],
```

In `sheets()`, derive Support presence from `$this->categoryItems` and pass it to `SummarySheet`:

```php
$hasSupport = collect($this->categoryItems)
    ->flatMap(fn ($category) => $category['evaluation_lists'] ?? [])
    ->contains(fn ($list) => ! empty($list['support_items']));

$sheets[] = new SummarySheet($this->assignment, $hasSupport);
```

- [ ] **Step 5: Render the conditional Summary rows**

Update `SummarySheet` to accept `bool $hasSupport`, calculate values with `ReportScoreSummary`, keep the legacy comment row, and append all role-comment rows. Insert these rows only when `$hasSupport` is true:

```php
['ผลรวมคะแนนถ่วงน้ำหนักสายสนับสนุน', $scores['support_raw']],
['คะแนนผลสัมฤทธิ์ของงาน', $scores['support_achievement']],
```

Use full-column style ranges (`A:A` and `B:B`) so dynamic row counts remain styled.

- [ ] **Step 6: Render Support details and include Support in list totals**

In `CategorySheet::array()`:

```php
$supportListScore = collect($evaluationList['support_items'] ?? [])
    ->sum(fn ($item) => (float) ($item['weighted_score'] ?? 0));
$totalListScore += $supportListScore;
```

After Quantity and Quality rows, append Support rows. Use `SafeHtml::plainText()` for `activity_name`, `indicator`, and activity-entry `content`. Write each evidence link unchanged in a row labelled `หลักฐาน`.

- [ ] **Step 7: Run focused tests and verify GREEN**

Run:

```powershell
vendor\bin\pest tests/Feature/ReportsExportTest.php tests/Feature/Evaluation/SupportCriteriaReadModelTest.php
```

Expected: all tests pass.

- [ ] **Step 8: Commit the single-report workbook**

```powershell
git add -- app/Exports/SingleReportExport.php tests/Feature/ReportsExportTest.php
git commit -m "feat: export support report details"
```

### Task 4: Enforce export authorization, state, and audit logging

**Files:**
- Modify: `routes/web.php`
- Modify: `app/Http/Controllers/FileExportController.php`
- Create: `tests/Feature/ReportExportAuthorizationTest.php`
- Modify: `tests/Feature/AuditLoggingTest.php`

**Interfaces:**
- Consumes: `User::assignmentsForDashboard()` for evaluator-scoped report access.
- Produces: HTTP `403` for unauthorized exports, `404` for missing report/assignment, and `409` for a non-Completed single report.
- Produces: audit description `ส่งออกรายงานรายบุคคล` with `export_type`, `report_id`, and `assignment_id`.

- [ ] **Step 1: Write failing route and authorization tests**

Create helpers in `tests/Feature/ReportExportAuthorizationTest.php` to create roles/users and assignment graphs. Add tests:

```php
test('evaluator cannot use the admin dashboard export endpoint', function () {
    $this->actingAs(exportUser('ผู้ประเมิน'), 'web')
        ->get(route('admin.export.reports'))
        ->assertForbidden();
});

test('evaluator cannot export an unassigned report', function () {
    Excel::fake();
    [$evaluator, $report] = unassignedCompletedReport();

    $this->actingAs($evaluator, 'web')
        ->get(route('single.reports.export', $report->id))
        ->assertForbidden();
});

test('single report export rejects reports that are not completed', function () {
    Excel::fake();
    [$evaluator, $report] = assignedReport('Draft');

    $this->actingAs($evaluator, 'web')
        ->get(route('single.reports.export', $report->id))
        ->assertStatus(409);
});
```

Add successful assigned-evaluator and privileged-role cases that assert `Excel::assertDownloaded()`.

- [ ] **Step 2: Write a failing single-export audit test**

Extend `tests/Feature/AuditLoggingTest.php` with a completed report/assignment and:

```php
$activity = latestAudit('ส่งออกรายงานรายบุคคล');

expect($activity)->not->toBeNull()
    ->and($activity->properties->get('export_type'))->toBe('single_report')
    ->and($activity->properties->get('report_id'))->toBe($report->id)
    ->and($activity->properties->get('assignment_id'))->toBe($assignment->id);
```

- [ ] **Step 3: Run authorization/audit tests and verify RED**

Run:

```powershell
vendor\bin\pest tests/Feature/ReportExportAuthorizationTest.php tests/Feature/AuditLoggingTest.php
```

Expected: evaluator can currently reach the admin endpoint, unassigned report export is not forbidden, Draft export is not rejected, and no single-export audit exists.

- [ ] **Step 4: Restrict the admin route**

Move `admin.export.reports` into the existing middleware group:

```php
Route::middleware(['auth:sanctum', 'role:admin|ผู้บริหาร'])->group(function () {
    Route::get('/admin/export/reports', [FileExportController::class, 'adminExportDashboard'])
        ->name('admin.export.reports');
});
```

Keep `export.reports` and `single.reports.export` in the broader authorized-role group.

- [ ] **Step 5: Enforce single-report scope and state**

Change the action signature to:

```php
public function exportSingleReport(Request $request, $id)
```

After loading the report and assignment:

```php
$user = $request->user();
$canExportAll = $user->hasAnyRole(['admin', 'ผู้บริหาร', 'กรรมการ']);

if (! $canExportAll) {
    $isAssignedEvaluator = $user->assignmentsForDashboard()
        ->whereKey($assignment->id)
        ->exists();

    abort_unless($isAssignedEvaluator, 403);
}

abort_unless($report->status === 'Completed', 409, 'Report is not completed.');
```

Keep missing reports and missing assignments as controlled `404` responses.

- [ ] **Step 6: Record successful single-report exports**

Immediately before returning the download:

```php
AuditLog::record('ส่งออกข้อมูล', 'ส่งออกรายงานรายบุคคล', [
    'export_type' => 'single_report',
    'report_id' => $report->id,
    'assignment_id' => $assignment->id,
], $report, $user);
```

- [ ] **Step 7: Run authorization/audit tests and verify GREEN**

Run:

```powershell
vendor\bin\pest tests/Feature/ReportExportAuthorizationTest.php tests/Feature/AuditLoggingTest.php
```

Expected: all tests pass.

- [ ] **Step 8: Commit authorization and audit changes**

```powershell
git add -- routes/web.php app/Http/Controllers/FileExportController.php tests/Feature/ReportExportAuthorizationTest.php tests/Feature/AuditLoggingTest.php
git commit -m "fix: secure report exports"
```

### Task 5: Verify the complete export workflow

**Files:**
- Verify only; do not modify unrelated dirty files.

**Interfaces:**
- Consumes all earlier tasks.
- Produces a verified branch with focused and full-suite evidence.

- [ ] **Step 1: Run all focused PHP tests**

```powershell
vendor\bin\pest tests/Unit/Support/ReportScoreSummaryTest.php tests/Feature/ScoreServiceTest.php tests/Feature/ReportsExportTest.php tests/Feature/ReportExportAuthorizationTest.php tests/Feature/AuditLoggingTest.php tests/Feature/Evaluation/SupportCriteriaReadModelTest.php
```

Expected: all focused tests pass.

- [ ] **Step 2: Run formatting checks**

```powershell
vendor\bin\pint --test app/Support/ReportScoreSummary.php app/Support/EvaluationScoreSummary.php app/Exports/ReportsExport.php app/Exports/SingleReportExport.php app/Http/Controllers/FileExportController.php tests/Unit/Support/ReportScoreSummaryTest.php tests/Feature/ScoreServiceTest.php tests/Feature/ReportsExportTest.php tests/Feature/ReportExportAuthorizationTest.php tests/Feature/AuditLoggingTest.php
```

Expected: Pint reports success.

- [ ] **Step 3: Run frontend regression checks**

```powershell
npm run test:js
npm run build
```

Expected: JavaScript tests pass and Vite completes a production build.

- [ ] **Step 4: Run the complete PHP suite once**

```powershell
composer test
```

Expected: the complete suite passes with zero failures.

- [ ] **Step 5: Inspect the final diff and status**

```powershell
git diff --check
git status --short --branch
git log -5 --oneline
```

Expected: no whitespace errors; only pre-existing unrelated workspace changes remain uncommitted.
