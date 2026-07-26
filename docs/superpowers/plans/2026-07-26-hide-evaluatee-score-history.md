# Hide Evaluatee Score History Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Keep every score-change audit record in the database while excluding evaluatee-authored records from every user-facing score history.

**Architecture:** Add one pure visibility policy that identifies evaluatee-authored and legacy `null/null` history records. Apply that policy in the three existing read paths before history models are mapped into view arrays: evaluatee dashboard data, reviewer report data, and the support criteria read model.

**Tech Stack:** PHP 8, Laravel, Eloquent collections, PHPUnit/Pest

## Global Constraints

- Cover quantity, quality, support score, and support activity-entry histories.
- Hide records for every viewer role and include existing records.
- A record is hidden when its modifier user matches the report evaluatee, or when both modifier user and modifier role are `null`.
- Do not delete or modify existing history records.
- Do not stop creating new history records.
- Do not change the database schema, edit permissions, or reviewer-reason validation.
- Preserve unrelated existing worktree changes.

---

### Task 1: Central history visibility policy

**Files:**
- Create: `app/Support/ScoreHistoryVisibility.php`
- Create: `tests/Unit/Support/ScoreHistoryVisibilityTest.php`

**Interfaces:**
- Consumes: nullable modifier user ID, nullable modifier role, nullable report evaluatee ID.
- Produces: `ScoreHistoryVisibility::shouldDisplay(?int $modifierUserId, ?string $modifierRole, ?int $evaluateeId): bool`.

- [ ] **Step 1: Write the failing policy tests**

Create `tests/Unit/Support/ScoreHistoryVisibilityTest.php`:

```php
<?php

use App\Support\ScoreHistoryVisibility;

test('it hides history written by the report evaluatee', function () {
    expect(ScoreHistoryVisibility::shouldDisplay(10, null, 10))->toBeFalse();
});

test('it hides legacy history without a modifier or role', function () {
    expect(ScoreHistoryVisibility::shouldDisplay(null, null, 10))->toBeFalse();
});

test('it shows reviewer history', function () {
    expect(ScoreHistoryVisibility::shouldDisplay(20, 'ผู้ประเมิน', 10))->toBeTrue();
});

test('it shows retained reviewer history after its user is deleted', function () {
    expect(ScoreHistoryVisibility::shouldDisplay(null, 'ผู้ประเมิน', 10))->toBeTrue();
});
```

Production mutation caught: returning `true` for evaluatee or `null/null` records, or returning `false` for reviewer records.

- [ ] **Step 2: Run the tests and verify RED**

Run:

```powershell
php artisan test tests/Unit/Support/ScoreHistoryVisibilityTest.php
```

Expected: FAIL because `App\Support\ScoreHistoryVisibility` does not exist.

- [ ] **Step 3: Implement the minimal policy**

Create `app/Support/ScoreHistoryVisibility.php`:

```php
<?php

namespace App\Support;

final class ScoreHistoryVisibility
{
    public static function shouldDisplay(
        ?int $modifierUserId,
        ?string $modifierRole,
        ?int $evaluateeId
    ): bool {
        if ($modifierUserId === null && $modifierRole === null) {
            return false;
        }

        return $evaluateeId === null || $modifierUserId !== $evaluateeId;
    }
}
```

- [ ] **Step 4: Run the policy tests and verify GREEN**

Run:

```powershell
php artisan test tests/Unit/Support/ScoreHistoryVisibilityTest.php
```

Expected: 4 tests pass with no warnings or errors.

- [ ] **Step 5: Commit the policy**

```powershell
git add -- app/Support/ScoreHistoryVisibility.php tests/Unit/Support/ScoreHistoryVisibilityTest.php
git commit -m "feat: define visible score history policy"
```

### Task 2: Filter quantity and quality histories in both general read paths

**Files:**
- Modify: `app/Services/ReportDataService.php`
- Modify: `app/Http/Controllers/Evaluatee/DashboardEvaluateeController.php`
- Create: `tests/Feature/Evaluation/ScoreHistoryVisibilityReadModelTest.php`

**Interfaces:**
- Consumes: `ScoreHistoryVisibility::shouldDisplay(...)` from Task 1 and `assignments.evaluatee_id` for the report.
- Produces: `categoryItems[*].evaluation_lists[*].quantity_items[*].sub_criterias[*].score_histories` and the corresponding quality histories with evaluatee records removed.

- [ ] **Step 1: Write a failing reviewer read-model test**

Create a feature test using `RefreshDatabase` that builds one report, one assignment, one quantity criterion, and one quality criterion. Insert, for each history table:

```php
[
    [
        'modifier_user_id' => $evaluatee->id,
        'modifier_role' => null,
        'reason' => null,
    ],
    [
        'modifier_user_id' => null,
        'modifier_role' => null,
        'reason' => null,
    ],
    [
        'modifier_user_id' => $reviewer->id,
        'modifier_role' => 'ผู้ประเมิน',
        'reason' => 'แก้ตามหลักฐาน',
    ],
]
```

Call:

```php
$data = app(\App\Services\ReportDataService::class)->getReportData($report->id);
$list = $data['categoryItems'][0]['evaluation_lists'][0];

$quantityHistories = $list['quantity_items'][0]['sub_criterias'][0]['score_histories'];
$qualityHistories = $list['quality_items'][0]['sub_criterias'][0]['score_histories'];

expect($quantityHistories)->toHaveCount(1)
    ->and($quantityHistories[0]['modified_by_name'])->toBe($reviewer->display_name)
    ->and($qualityHistories)->toHaveCount(1)
    ->and($qualityHistories[0]['modified_by_name'])->toBe($reviewer->display_name);

$this->assertDatabaseCount('quantity_score_histories', 3);
$this->assertDatabaseCount('quality_score_histories', 3);
```

Production mutation caught: omitting either filter from `ReportDataService`, or deleting audit records instead of filtering the returned arrays.

- [ ] **Step 2: Write a failing evaluatee-page integration test**

Build the same fixture through the evaluatee route used by `DashboardEvaluateeController`, authenticate as the report evaluatee, and capture the view data:

```php
$response = $this->actingAs($evaluatee)
    ->get(route('evaluation.show', ['id' => $report->id]));

$response->assertOk();
$categoryItems = $response->viewData('categoryItems');
$list = $categoryItems[0]['evaluation_lists'][0];

expect($list['quantity_items'][0]['sub_criterias'][0]['score_histories'])->toHaveCount(1)
    ->and($list['quality_items'][0]['sub_criterias'][0]['score_histories'])->toHaveCount(1);
```

Create the assignment with `Assignments::factory()->create(['report_id' => $report->id, 'evaluatee_id' => $evaluatee->id])`; do not mock the controller or view.

Production mutation caught: filtering the reviewer service but leaving the evaluatee controller read path unchanged.

- [ ] **Step 3: Run the two tests and verify RED**

Run:

```powershell
php artisan test tests/Feature/Evaluation/ScoreHistoryVisibilityReadModelTest.php
```

Expected: FAIL because all three history records are returned from each read path.

- [ ] **Step 4: Filter `ReportDataService` collections**

Import `App\Support\ScoreHistoryVisibility`. After resolving the report assignment, calculate:

```php
$evaluateeId = $assignment?->evaluatee_id;
```

Apply this filter to both history collections before `groupBy(...)`:

```php
->get()
->filter(fn ($history) => ScoreHistoryVisibility::shouldDisplay(
    $history->modifier_user_id,
    $history->modifier_role,
    $evaluateeId
))
->groupBy('quantity_sub_criteria_id');
```

Use the same policy for quality histories and group by `quality_sub_criteria_id`.

- [ ] **Step 5: Filter `DashboardEvaluateeController` collections**

Import `App\Support\ScoreHistoryVisibility`. Use the authorized assignment's `evaluatee_id`, then apply the same collection filter before grouping both quantity and quality histories:

```php
->get()
->filter(fn ($history) => ScoreHistoryVisibility::shouldDisplay(
    $history->modifier_user_id,
    $history->modifier_role,
    $assignment->evaluatee_id
))
```

- [ ] **Step 6: Run the read-model tests and verify GREEN**

Run:

```powershell
php artisan test tests/Feature/Evaluation/ScoreHistoryVisibilityReadModelTest.php
```

Expected: reviewer and evaluatee read paths each return only the reviewer history while all audit rows remain in the database.

- [ ] **Step 7: Commit the general read-path change**

```powershell
git add -- app/Services/ReportDataService.php app/Http/Controllers/Evaluatee/DashboardEvaluateeController.php tests/Feature/Evaluation/ScoreHistoryVisibilityReadModelTest.php
git commit -m "feat: hide evaluatee score histories from views"
```

### Task 3: Filter support score and activity histories

**Files:**
- Modify: `app/Support/SupportCriteriaReadModel.php`
- Modify: `tests/Feature/Evaluation/SupportCriteriaReadModelTest.php`

**Interfaces:**
- Consumes: `ScoreHistoryVisibility::shouldDisplay(...)` and `Reports::assignments.evaluatee_id`.
- Produces: support criterion `histories` and activity-entry `histories` arrays containing reviewer records only.

- [ ] **Step 1: Write a failing support-history regression test**

Add a test that creates a report assignment, support criterion, activity entry, and three records in each support history table:

```php
$evaluateeHistory = [
    'modifier_user_id' => $evaluatee->id,
    'modifier_role' => null,
];
$legacyHistory = [
    'modifier_user_id' => null,
    'modifier_role' => null,
];
$reviewerHistory = [
    'modifier_user_id' => $reviewer->id,
    'modifier_role' => 'ผู้ประเมิน',
];
```

Use `modified_by` and `modified_by_role` for `SupportActivityEntryHistory`. Call the real read model and assert:

```php
$item = app(SupportCriteriaReadModel::class)->forReport($report)[$evaluationList->id][0];

expect($item['histories'])->toHaveCount(1)
    ->and($item['histories'][0]['modified_by_name'])->toBe($reviewer->display_name)
    ->and($item['activity_entries'][0]['histories'])->toHaveCount(1)
    ->and($item['activity_entries'][0]['histories'][0]['modified_by_name'])
    ->toBe($reviewer->display_name);

$this->assertDatabaseCount('support_score_histories', 3);
$this->assertDatabaseCount('support_activity_entry_histories', 3);
```

Production mutation caught: missing either the support score filter or the activity-entry filter, and deleting audit rows instead of filtering presentation data.

- [ ] **Step 2: Run the support read-model test and verify RED**

Run:

```powershell
php artisan test tests/Feature/Evaluation/SupportCriteriaReadModelTest.php --filter=history
```

Expected: FAIL because each returned history array contains three records.

- [ ] **Step 3: Apply the policy in `SupportCriteriaReadModel`**

Load the assignment alongside report data:

```php
$report->loadMissing('reportData', 'assignments:id,report_id,evaluatee_id');
$evaluateeId = $report->assignments?->evaluatee_id;
```

Import `ScoreHistoryVisibility` and filter support score histories before grouping:

```php
->get()
->filter(fn (SupportScoreHistory $history) => ScoreHistoryVisibility::shouldDisplay(
    $history->modifier_user_id,
    $history->modifier_role,
    $evaluateeId
))
```

Filter each activity entry's already eager-loaded history collection before mapping:

```php
->filter(fn (SupportActivityEntryHistory $history) => ScoreHistoryVisibility::shouldDisplay(
    $history->modified_by,
    $history->modified_by_role,
    $evaluateeId
))
```

- [ ] **Step 4: Run support tests and verify GREEN**

Run:

```powershell
php artisan test tests/Feature/Evaluation/SupportCriteriaReadModelTest.php
```

Expected: all support read-model tests pass; only reviewer histories appear in returned arrays and all six audit records remain stored.

- [ ] **Step 5: Commit the support read-path change**

```powershell
git add -- app/Support/SupportCriteriaReadModel.php tests/Feature/Evaluation/SupportCriteriaReadModelTest.php
git commit -m "feat: hide evaluatee support histories"
```

### Task 4: Full verification

**Files:**
- Verify only; no planned production changes.

**Interfaces:**
- Consumes: completed Tasks 1–3.
- Produces: fresh evidence that the feature and surrounding score-history behavior pass.

- [ ] **Step 1: Run focused score-history tests**

```powershell
php artisan test tests/Unit/Support/ScoreHistoryVisibilityTest.php tests/Feature/Evaluation/ScoreHistoryVisibilityReadModelTest.php tests/Feature/Evaluation/SupportCriteriaReadModelTest.php tests/Feature/Evaluation/QuantityScoreHistoryRecorderTest.php tests/Feature/Evaluation/QualityScoreHistoryFlowTest.php tests/Feature/Evaluation/SupportCriteriaReviewerFlowTest.php
php artisan test tests/Feature/Evaluation/EvaluateeTest.php --filter=evaluatee_score_change_creates_quantity_score_history
```

Expected: all focused tests pass with zero failures.

- [ ] **Step 2: Run formatting validation**

```powershell
vendor/bin/pint --test app/Support/ScoreHistoryVisibility.php app/Services/ReportDataService.php app/Http/Controllers/Evaluatee/DashboardEvaluateeController.php app/Support/SupportCriteriaReadModel.php tests/Unit/Support/ScoreHistoryVisibilityTest.php tests/Feature/Evaluation/ScoreHistoryVisibilityReadModelTest.php tests/Feature/Evaluation/SupportCriteriaReadModelTest.php
```

Expected: exit code 0.

- [ ] **Step 3: Run the full automated test suite**

```powershell
php artisan test
```

Expected: zero failed tests. If an unrelated pre-existing failure occurs, record the exact test and failure without modifying unrelated user work.

- [ ] **Step 4: Inspect the final diff**

```powershell
git status --short
git diff --check HEAD~3..HEAD
git diff --stat HEAD~3..HEAD
```

Expected: only the visibility policy, the three read paths, and their tests are part of the implementation commits; unrelated dirty files remain untouched.
