# Admin Dashboard Query Pipeline Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Harden and improve the admin `/dashboard` evaluation query pipeline without changing the visible dashboard behavior.

**Architecture:** Keep `DashboardController@index` thin. Put dashboard data assembly behind `App\Support\AdminDashboardQuery`, return a stable `AdminDashboardData` view-data contract, then optimize internals behind tests. Do not touch non-admin dashboard flows in this plan.

**Tech Stack:** Laravel, Pest, SQLite test database at `database/database.sqlite`, Eloquent, Blade dashboard view.

---

## Operating Mode For GPT-5.4 Mini

Use this plan in `single-context` mode. Do one task at a time. After each task, run the exact verification command before moving on.

Do not run Pest commands in parallel. This repo uses a shared SQLite file in `phpunit.xml`; parallel test processes can collide during migrations and throw `table "sessions" already exists`.

Keep every change scoped to admin `/dashboard`, `AdminDashboardQuery`, dashboard query tests, and setup docs unless the task explicitly says otherwise.

---

## Current Files And Responsibilities

- `app/Http/Controllers/DashboardController.php`
  - Must keep `index(Request $request, AdminDashboardQuery $dashboardQuery)` as a thin controller method.
  - Must not regain dashboard query assembly logic.

- `app/Support/AdminDashboardQuery.php`
  - Owns the admin dashboard read pipeline.
  - Reads filters from request.
  - Gets assignments through `EvaluationService`.
  - Builds counts, graph data, follow-up list, reports with scores, pagination, and view data.

- `app/Support/AdminDashboardData.php`
  - Small value object that exposes `toViewData(): array`.

- `tests/Feature/AdminDashboardQueryTest.php`
  - Locks the view-data contract and any behavior-preserving refactors.

- `app/Services/EvaluationService.php`
  - Current source for report assignment loading, mapping, filtering, and sorting.
  - Do not rewrite this broadly in this plan.

- `app/Services/ScoreService.php`
  - Current source for average, quantity, and quality score calculations.
  - Optimization can add bulk helpers, but keep existing public methods working.

---

### Task 1: Lock Thai Dashboard Labels And Encoding

**Files:**
- Modify: `app/Support/AdminDashboardQuery.php`
- Modify: `tests/Feature/AdminDashboardQueryTest.php`

- [ ] **Step 1: Add contract assertions for exact Thai labels**

In `tests/Feature/AdminDashboardQueryTest.php`, extend the existing test after `$viewData = ...`:

```php
expect(array_keys($viewData['statusCounts']))->toBe([
    'ทั้งหมด',
    'มอบหมาย',
    'เริ่มกรอกข้อมูล',
    'กำลังดำเนินการ',
    'ประเมินเสร็จสิ้น',
]);

expect(array_keys($viewData['overviewEvaluateeStatusCounts']))->toBe([
    'มอบหมาย',
    'เริ่มกรอกข้อมูล',
    'กำลังดำเนินการ',
    'ประเมินเสร็จสิ้น',
]);
```

- [ ] **Step 2: Run test to verify label contract**

Run:

```powershell
vendor\bin\pest tests\Feature\AdminDashboardQueryTest.php
```

Expected before fix if encoding is broken: FAIL showing mismatched Thai label strings.

- [ ] **Step 3: Fix labels in `AdminDashboardQuery`**

Make these exact strings appear in `app/Support/AdminDashboardQuery.php`:

```php
$statusCounts = [
    'ทั้งหมด' => $evaluations->count(),
    'มอบหมาย' => $this->countByStatus($evaluations, ['Assigned']),
    'เริ่มกรอกข้อมูล' => $this->countByStatus($evaluations, ['Draft']),
    'กำลังดำเนินการ' => $this->countByStatus($evaluations, ['Pending', 'Evaluator_draft', 'Director_assigned', 'Director_draft', 'Manager_draft', 'Manager_assign']),
    'ประเมินเสร็จสิ้น' => $this->countByStatus($evaluations, ['Completed']),
];
```

Make the overview counts use these exact keys:

```php
$counts = [
    'มอบหมาย' => 0,
    'เริ่มกรอกข้อมูล' => 0,
    'กำลังดำเนินการ' => 0,
    'ประเมินเสร็จสิ้น' => 0,
];
```

Make completed overview lookup use:

```php
$overviewCompletedEvaluatees = $overviewEvaluateeStatusCounts['ประเมินเสร็จสิ้น'] ?? 0;
```

Make evaluator fallback name use:

```php
('ตำแหน่ง: '.$report->evaluator_position_name)
```

- [ ] **Step 4: Verify Task 1**

Run:

```powershell
php -l app\Support\AdminDashboardQuery.php
vendor\bin\pest tests\Feature\AdminDashboardQueryTest.php
```

Expected: syntax OK and `1 passed`.

- [ ] **Step 5: Commit Task 1**

```powershell
git add app\Support\AdminDashboardQuery.php tests\Feature\AdminDashboardQueryTest.php
git commit -m "test: lock admin dashboard label contract"
```

---

### Task 2: Strengthen The View-Data Contract Test

**Files:**
- Modify: `tests/Feature/AdminDashboardQueryTest.php`

- [ ] **Step 1: Assert stable default values**

Add these assertions to the existing empty database test:

```php
expect($viewData['averageScore'])->toBe(0)
    ->and($viewData['totalEvaluations'])->toBe(0)
    ->and($viewData['totalEvaluatees'])->toBe(0)
    ->and($viewData['totalUsers'])->toBe(0)
    ->and($viewData['completedEvaluatees'])->toBe(0)
    ->and($viewData['completedEvaluateesPercent'])->toBe(0)
    ->and($viewData['startedEvaluateesPercent'])->toBe(0)
    ->and($viewData['overviewCompletedEvaluatees'])->toBe(0)
    ->and($viewData['overviewCompletedEvaluateesPercent'])->toBe(0)
    ->and($viewData['notStartedCount'])->toBe(0)
    ->and($viewData['draftCount'])->toBe(0)
    ->and($viewData['inReviewCount'])->toBe(0)
    ->and($viewData['completedCount'])->toBe(0)
    ->and($viewData['startedCount'])->toBe(0)
    ->and($viewData['progressPercent'])->toBe(0)
    ->and($viewData['startedPercent'])->toBe(0)
    ->and($viewData['reports'])->toBe([])
    ->and($viewData['evaluationPeriod'])->toBe('All Periods');
```

- [ ] **Step 2: Assert pagination is stable**

Add:

```php
expect($viewData['evaluations'])->toBeInstanceOf(LengthAwarePaginator::class)
    ->and($viewData['evaluations']->perPage())->toBe(10)
    ->and($viewData['evaluations']->total())->toBe(0);
```

- [ ] **Step 3: Verify Task 2**

Run:

```powershell
vendor\bin\pest tests\Feature\AdminDashboardQueryTest.php
vendor\bin\pest tests\Feature\DashboardTest.php tests\Feature\AdminDashboardQueryTest.php
```

Expected: all tests pass.

- [ ] **Step 4: Commit Task 2**

```powershell
git add tests\Feature\AdminDashboardQueryTest.php
git commit -m "test: harden admin dashboard view data contract"
```

---

### Task 3: Extract Status Summary Logic

**Files:**
- Create: `app/Support/AdminDashboardStatusSummary.php`
- Modify: `app/Support/AdminDashboardQuery.php`
- Create: `tests/Unit/AdminDashboardStatusSummaryTest.php`

- [ ] **Step 1: Write unit tests for status buckets**

Create `tests/Unit/AdminDashboardStatusSummaryTest.php`:

```php
<?php

use App\Support\AdminDashboardStatusSummary;
use Illuminate\Support\Collection;

function dashboardAssignment(string $status, int $evaluateeId): object
{
    return (object) [
        'report' => (object) ['status' => $status],
        'evaluateeUser' => (object) ['id' => $evaluateeId],
    ];
}

test('counts assignments by admin dashboard status groups', function () {
    $summary = new AdminDashboardStatusSummary;

    $evaluations = collect([
        dashboardAssignment('Assigned', 1),
        dashboardAssignment('Draft', 2),
        dashboardAssignment('Pending', 3),
        dashboardAssignment('Manager_assign', 4),
        dashboardAssignment('Completed', 5),
    ]);

    expect($summary->statusCounts($evaluations))->toBe([
        'ทั้งหมด' => 5,
        'มอบหมาย' => 1,
        'เริ่มกรอกข้อมูล' => 1,
        'กำลังดำเนินการ' => 2,
        'ประเมินเสร็จสิ้น' => 1,
    ]);
});

test('summarizes evaluatee overview by final group', function () {
    $summary = new AdminDashboardStatusSummary;

    $evaluations = collect([
        dashboardAssignment('Assigned', 1),
        dashboardAssignment('Draft', 2),
        dashboardAssignment('Assigned', 2),
        dashboardAssignment('Pending', 3),
        dashboardAssignment('Completed', 4),
        dashboardAssignment('Completed', 4),
    ]);

    expect($summary->overviewStatusCounts($evaluations))->toBe([
        'มอบหมาย' => 1,
        'เริ่มกรอกข้อมูล' => 1,
        'กำลังดำเนินการ' => 1,
        'ประเมินเสร็จสิ้น' => 1,
    ]);
});
```

- [ ] **Step 2: Run unit test to verify it fails**

Run:

```powershell
vendor\bin\pest tests\Unit\AdminDashboardStatusSummaryTest.php
```

Expected: FAIL because `App\Support\AdminDashboardStatusSummary` does not exist.

- [ ] **Step 3: Create status summary class**

Create `app/Support/AdminDashboardStatusSummary.php`:

```php
<?php

namespace App\Support;

class AdminDashboardStatusSummary
{
    public function statusCounts($evaluations): array
    {
        return [
            'ทั้งหมด' => $evaluations->count(),
            'มอบหมาย' => $this->countByStatus($evaluations, ['Assigned']),
            'เริ่มกรอกข้อมูล' => $this->countByStatus($evaluations, ['Draft']),
            'กำลังดำเนินการ' => $this->countByStatus($evaluations, ['Pending', 'Evaluator_draft', 'Director_assigned', 'Director_draft', 'Manager_draft', 'Manager_assign']),
            'ประเมินเสร็จสิ้น' => $this->countByStatus($evaluations, ['Completed']),
        ];
    }

    public function overviewStatusCounts($evaluations): array
    {
        $counts = [
            'มอบหมาย' => 0,
            'เริ่มกรอกข้อมูล' => 0,
            'กำลังดำเนินการ' => 0,
            'ประเมินเสร็จสิ้น' => 0,
        ];

        $evaluations
            ->filter(fn ($assignment) => $assignment->evaluateeUser)
            ->groupBy('evaluateeUser.id')
            ->each(function ($assignments) use (&$counts) {
                $statuses = $assignments
                    ->map(fn ($assignment) => optional($assignment->report)->status ?? 'Assigned')
                    ->filter()
                    ->values();

                if ($statuses->isEmpty()) {
                    $counts['มอบหมาย']++;

                    return;
                }

                $allCompleted = $statuses->every(fn ($status) => $status === 'Completed');
                $allAssigned = $statuses->every(fn ($status) => in_array($status, ['Assigned', 'Manager_assign'], true));
                $hasDraftOnly = $statuses->contains('Draft')
                    && $statuses->every(fn ($status) => in_array($status, ['Assigned', 'Manager_assign', 'Draft'], true));

                if ($allCompleted) {
                    $counts['ประเมินเสร็จสิ้น']++;
                } elseif ($allAssigned) {
                    $counts['มอบหมาย']++;
                } elseif ($hasDraftOnly) {
                    $counts['เริ่มกรอกข้อมูล']++;
                } else {
                    $counts['กำลังดำเนินการ']++;
                }
            });

        return $counts;
    }

    public function countByStatus($evaluations, array $statuses): int
    {
        return $evaluations->filter(function ($assignment) use ($statuses) {
            $reportStatus = optional($assignment->report)->status ?? 'Assigned';

            return in_array($reportStatus, $statuses, true);
        })->count();
    }
}
```

- [ ] **Step 4: Inject summary class into query**

In `app/Support/AdminDashboardQuery.php`, change constructor to:

```php
public function __construct(
    private readonly EvaluationService $evaluationService,
    private readonly AdminDashboardStatusSummary $statusSummary,
) {}
```

Replace status counts:

```php
$statusCounts = $this->statusSummary->statusCounts($evaluations);
```

Replace repeated count calls:

```php
$notStartedCount = $this->statusSummary->countByStatus($evaluations, ['Assigned', 'Manager_assign']);
$draftCount = $this->statusSummary->countByStatus($evaluations, ['Draft']);
$inReviewCount = $this->statusSummary->countByStatus($evaluations, ['Pending', 'Evaluator_draft', 'Director_assigned', 'Director_draft', 'Manager_draft']);
$completedCount = $this->statusSummary->countByStatus($evaluations, ['Completed']);
```

Replace overview summary:

```php
$overviewEvaluateeStatusCounts = $this->statusSummary->overviewStatusCounts($evaluations);
```

Remove private methods `countByStatus()` and `summarizeEvaluateeOverviewStatuses()` from `AdminDashboardQuery`.

- [ ] **Step 5: Verify Task 3**

Run:

```powershell
php -l app\Support\AdminDashboardStatusSummary.php
php -l app\Support\AdminDashboardQuery.php
vendor\bin\pest tests\Unit\AdminDashboardStatusSummaryTest.php
vendor\bin\pest tests\Feature\AdminDashboardQueryTest.php tests\Feature\DashboardTest.php
```

Expected: all tests pass.

- [ ] **Step 6: Commit Task 3**

```powershell
git add app\Support\AdminDashboardStatusSummary.php app\Support\AdminDashboardQuery.php tests\Unit\AdminDashboardStatusSummaryTest.php
git commit -m "refactor: extract admin dashboard status summary"
```

---

### Task 4: Add Query Count Budget For Empty Dashboard

**Files:**
- Modify: `tests/Feature/AdminDashboardQueryTest.php`

- [ ] **Step 1: Add query count test**

Append this test:

```php
use Illuminate\Support\Facades\DB;

test('admin dashboard query stays within empty dashboard query budget', function () {
    DB::flushQueryLog();
    DB::enableQueryLog();

    $query = app(AdminDashboardQuery::class);
    $request = Request::create('/dashboard', 'GET');

    $query->handle($request)->toViewData();

    $queries = DB::getQueryLog();
    DB::disableQueryLog();

    expect(count($queries))->toBeLessThanOrEqual(8);
});
```

- [ ] **Step 2: Run test**

Run:

```powershell
vendor\bin\pest tests\Feature\AdminDashboardQueryTest.php
```

Expected: pass if current empty dashboard remains lightweight. If it fails, inspect query count before changing code.

- [ ] **Step 3: Commit Task 4**

```powershell
git add tests\Feature\AdminDashboardQueryTest.php
git commit -m "test: add admin dashboard query budget"
```

---

### Task 5: Remove Per-Report Quantity Score Queries

**Files:**
- Modify: `app/Support/AdminDashboardQuery.php`
- Modify: `tests/Feature/AdminDashboardQueryTest.php`

- [ ] **Step 1: Add a focused method expectation through existing dashboard query test**

Seed two reports with quantity scores only if factories exist for these models. If factories do not exist, skip this task and move to Task 6 after writing a note in the final response.

Use model factories only. Do not hand-insert rows unless the existing test suite already does that for these tables.

- [ ] **Step 2: Replace quantity score per-report query with one grouped query**

In `reportsWithScores($reports)`, collect report ids first:

```php
$reportIds = $reports->pluck('report_id')->filter()->unique()->values();
$quantityScores = QuantityScore::query()
    ->whereIn('report_id', $reportIds)
    ->selectRaw('report_id, SUM(score_D) as total_score')
    ->groupBy('report_id')
    ->pluck('total_score', 'report_id');
```

Inside the loop, replace:

```php
$quantityScore = QuantityScore::where('report_id', $report->report_id)
    ->sum('score_D') ?? 0;
```

with:

```php
$quantityScore = (float) ($quantityScores[$report->report_id] ?? 0);
```

- [ ] **Step 3: Verify Task 5**

Run:

```powershell
php -l app\Support\AdminDashboardQuery.php
vendor\bin\pest tests\Feature\AdminDashboardQueryTest.php tests\Feature\DashboardTest.php
```

Expected: all tests pass.

- [ ] **Step 4: Commit Task 5**

```powershell
git add app\Support\AdminDashboardQuery.php tests\Feature\AdminDashboardQueryTest.php
git commit -m "perf: batch admin dashboard quantity scores"
```

---

### Task 6: Document Remaining Performance Risk

**Files:**
- Modify: `docs/agents/domain.md`
- Create: `docs/superpowers/plans/2026-06-15-admin-dashboard-query-pipeline-notes.md`

- [ ] **Step 1: Record the known remaining N+1 risk**

Create `docs/superpowers/plans/2026-06-15-admin-dashboard-query-pipeline-notes.md`:

```markdown
# Admin Dashboard Query Pipeline Notes

## Remaining Risk

`ScoreService::calculateAverageScore()` and `AdminDashboardQuery::reportsWithScores()` still call `ScoreService::calculateQualityScoreRaw($reportId)` per report.

This is a likely N+1 query source because `calculateQualityScoreRaw()` reads:
- `reports` joined to `report_datas`
- `quality_scores`
- `quality_sub_criterias`
- `evaluation_lists`

Do not rewrite this without a data-backed test fixture. The next safe step is to add factories or a fixture for completed reports with quality scores, then introduce a bulk quality score calculator that returns `report_id => score`.
```

- [ ] **Step 2: Add domain note**

Append to `docs/agents/domain.md`:

```markdown
## Admin Dashboard Query Pipeline

The admin dashboard is a read model assembled from reports, assignments, users, departments, positions, status groups, graph data, and score summaries.

The controller should remain thin. Query assembly belongs in `App\Support\AdminDashboardQuery`. Status grouping belongs in `App\Support\AdminDashboardStatusSummary`.

Performance work must preserve the existing Blade view-data contract and should be covered by `tests/Feature/AdminDashboardQueryTest.php`.
```

- [ ] **Step 3: Verify Task 6**

Run:

```powershell
vendor\bin\pest tests\Feature\DashboardTest.php tests\Feature\AdminDashboardQueryTest.php
```

Expected: all tests pass.

- [ ] **Step 4: Commit Task 6**

```powershell
git add docs\agents\domain.md docs\superpowers\plans\2026-06-15-admin-dashboard-query-pipeline-notes.md
git commit -m "docs: record admin dashboard query pipeline notes"
```

---

### Task 7: Final Verification

**Files:**
- No code changes unless verification finds a real issue.

- [ ] **Step 1: Run syntax checks**

```powershell
php -l app\Http\Controllers\DashboardController.php
php -l app\Support\AdminDashboardData.php
php -l app\Support\AdminDashboardQuery.php
php -l app\Support\AdminDashboardStatusSummary.php
```

Expected: all print `No syntax errors detected`.

- [ ] **Step 2: Run relevant tests**

```powershell
vendor\bin\pest tests\Unit\AdminDashboardStatusSummaryTest.php
vendor\bin\pest tests\Feature\DashboardTest.php tests\Feature\AdminDashboardQueryTest.php
```

Expected: all tests pass.

- [ ] **Step 3: Inspect diff for scope drift**

```powershell
git status --short
git diff --stat
```

Expected changed files only include:

```text
app/Http/Controllers/DashboardController.php
app/Support/AdminDashboardData.php
app/Support/AdminDashboardQuery.php
app/Support/AdminDashboardStatusSummary.php
tests/Feature/AdminDashboardQueryTest.php
tests/Unit/AdminDashboardStatusSummaryTest.php
AGENTS.md
docs/agents/issue-tracker.md
docs/agents/triage-labels.md
docs/agents/domain.md
docs/superpowers/plans/2026-06-15-admin-dashboard-query-pipeline.md
docs/superpowers/plans/2026-06-15-admin-dashboard-query-pipeline-notes.md
```

If extra files appear, inspect them. Do not revert user work. Only remove accidental generated files created by this plan.

---

## Self-Review

Spec coverage:
- Starts from admin `/dashboard` only.
- Preserves existing behavior through view-data contract tests.
- Addresses UI confusion risk through exact Thai labels.
- Addresses performance risk through query-budget test and batching quantity score queries.
- Documents the remaining bulk quality-score optimization instead of guessing.

Placeholder scan:
- No task uses `TBD`, `TODO`, or undefined future work as an implementation step.

Type consistency:
- `AdminDashboardQuery::handle(Request $request): AdminDashboardData`
- `AdminDashboardData::toViewData(): array`
- `AdminDashboardStatusSummary::statusCounts($evaluations): array`
- `AdminDashboardStatusSummary::overviewStatusCounts($evaluations): array`
- `AdminDashboardStatusSummary::countByStatus($evaluations, array $statuses): int`
