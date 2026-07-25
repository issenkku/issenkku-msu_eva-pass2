# Support Evaluatee Mode Live Update Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Let Admin update the evaluatee-owned support indicator and weight flags after report activity data exists without deleting existing report data.

**Architecture:** Keep the existing request validation and update persistence unchanged. Remove only the report-data guard for these two flags, and replace its rejection test with a successful-update regression test that also proves existing activity data is preserved.

**Tech Stack:** Laravel 12, PHP 8.2+, Eloquent, Pest/PHPUnit

## Global Constraints

- Existing support scores, activity entries, evidence, and history must remain unchanged.
- Enabling either evaluatee-owned field still requires `allow_activity_entries`.
- Existing protections for grouped indicator items referenced by projects must remain unchanged.
- Preserve unrelated dirty-worktree changes.

---

### Task 1: Allow live updates to evaluatee-owned support field modes

**Files:**
- Modify: `tests/Feature/Report/SupportCriteriaTemplateTest.php:489`
- Modify: `app/Http/Controllers/ReportStructureController.php:129`
- Modify: `app/Http/Controllers/ReportStructureController.php:838`

**Interfaces:**
- Consumes: `PUT report-structure.update` with existing `support_criteria_id`, `allow_evaluatee_indicator`, and `allow_evaluatee_weight`.
- Produces: A successful update response that persists both flags while preserving existing `SupportActivityEntry` rows.

- [ ] **Step 1: Replace the rejection test with a failing successful-update test**

Rename the existing test to
`test_admin_can_change_evaluatee_owned_field_mode_after_report_data_exists`.
Keep its existing version, criterion, report, and activity-entry setup. Replace
the rejection assertions with:

```php
$this->putJson(route('report-structure.update', $version->id), $payload)
    ->assertOk();

$this->assertDatabaseHas('support_criterias', [
    'id' => $criterion->id,
    'allow_evaluatee_indicator' => true,
    'allow_evaluatee_weight' => true,
]);
$this->assertDatabaseHas('support_activity_entries', [
    'id' => $entry->id,
    'report_id' => $report->id,
    'support_criteria_id' => $criterion->id,
    'content' => '<p>โครงการเดิม</p>',
]);
```

Assign the created activity entry to `$entry` so the preservation assertion
uses its exact ID.

- [ ] **Step 2: Run the test and verify the current guard rejects it**

Run:

```powershell
php -d memory_limit=512M vendor\bin\pest tests\Feature\Report\SupportCriteriaTemplateTest.php --filter="admin can change evaluatee owned field mode"
```

Expected: FAIL because the response is HTTP 422 instead of HTTP 200, with the
validation message that the evaluatee input mode cannot change after report
data exists.

- [ ] **Step 3: Remove only the obsolete update-time guard**

Delete `validateSupportEvaluateeModeChanges()` from
`ReportStructureController`, and delete its call in `update()`.

Do not change `validateSupportIndicatorConfiguration()`, the flag persistence
array, `SupportIndicatorItemService`, or grouped-indicator deletion guards.

- [ ] **Step 4: Run focused verification**

Run:

```powershell
php -d memory_limit=512M vendor\bin\pest tests\Feature\Report\SupportCriteriaTemplateTest.php
```

Expected: all support template tests pass, including the live-update test and
the grouped-indicator protection tests.

- [ ] **Step 5: Run formatting and regression verification**

Run:

```powershell
vendor\bin\pint --test app\Http\Controllers\ReportStructureController.php tests\Feature\Report\SupportCriteriaTemplateTest.php
php -d memory_limit=512M vendor\bin\pest tests\Feature\Report\QuantityCriteriaActivationTest.php tests\Feature\Report\SupportCriteriaTemplateTest.php
git diff --check
```

Expected: Pint passes, both feature test files pass, and `git diff --check`
reports no whitespace errors.

- [ ] **Step 6: Commit only the task changes**

Because `ReportStructureController.php` already contains an unrelated unstaged
import-order change, inspect and stage only the removed guard hunks plus the
test change.

```powershell
git diff -- app/Http/Controllers/ReportStructureController.php tests/Feature/Report/SupportCriteriaTemplateTest.php
git add -p -- app/Http/Controllers/ReportStructureController.php
git add -- tests/Feature/Report/SupportCriteriaTemplateTest.php
git commit -m "fix: allow live support field mode updates"
```
