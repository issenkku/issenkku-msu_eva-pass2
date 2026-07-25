# Support Evaluatee-Defined Fields Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Allow evaluatees to enter an indicator, weight, achieved score, and evidence for each support activity entry while preserving the existing modal and capping each evaluation list at `sum_score`.

**Architecture:** Keep template-owned configuration on `support_criterias` and report-owned values on `support_activity_entries`. Compute entry weighted scores on the server, expose criterion aggregates through `SupportCriteriaReadModel`, and centralize evaluation-list capping so forms, dashboards, and exports use the same rule.

**Tech Stack:** PHP 8.2, Laravel 11, Eloquent, Blade, vanilla JavaScript, Tailwind CSS, Pest/PHPUnit, Node test runner.

## Global Constraints

- Preserve the existing modal, indicator-level groups, add-project buttons, and per-project evidence controls.
- `allow_evaluatee_indicator` and `allow_evaluatee_weight` default to `false`.
- Evaluatee-entered data belongs to `support_activity_entries`; never overwrite template values in `support_criterias`.
- Entry weight must be greater than `0` and at most `100`; achieved score must be from `0` through `100`; both allow at most two decimal places.
- Do not require entry weights to total `100` or `evaluation_lists.sum_score`.
- Browser calculations are previews; the server always recalculates `weighted_score`.
- When `allow_evaluatee_weight` is enabled, do not create or update the criterion-level `support_scores` row.
- Evaluation-list final score is `min(quantity + quality + support, evaluation_lists.sum_score)` when `sum_score > 0`.
- Existing criteria and reports with both new flags disabled must behave exactly as before.
- Block changing either new flag after the criterion has report-owned scores or activity entries.

---

## File Structure

- `database/migrations/2026_07_25_000003_add_evaluatee_defined_fields_to_support_entries.php`: add template flags, entry values, and history values.
- `app/Models/SupportCriteria.php`: expose and cast the two template flags.
- `app/Models/SupportActivityEntry.php`: expose and cast indicator, weight, achieved score, and weighted score.
- `app/Models/SupportActivityEntryHistory.php`: expose and cast previous/new entry values.
- `app/Support/SupportWeightedScore.php`: one server-side weighted-score formula.
- `app/Support/SupportScoreTotal.php`: combine legacy criterion scores and entry-level scores without double counting.
- `app/Support/EvaluationListScore.php`: calculate raw component totals and the capped final total for one evaluation list.
- `app/Support/EvaluationScoreSummary.php`: aggregate list summaries for views and confirmation flows.
- `app/Support/ReportScoreSummary.php`: format raw component totals while accepting the already-capped report total.
- `app/Support/ReportEvaluationScoreQuery.php`: batch evaluation-list totals for dashboard statistics and exports.
- `app/Http/Controllers/ReportStructureController.php`: validate, persist, read, clone, and lock template flags.
- `app/Support/SupportScoreRules.php`: accept entry fields at the payload boundary.
- `app/Services/SupportActivityEntryService.php`: authorize, validate, calculate, persist, and audit entry fields.
- `app/Services/SupportScoreService.php`: switch between legacy criterion scoring and entry scoring.
- `app/Support/SupportCriteriaReadModel.php`: return flags, entry values, histories, and criterion aggregates.
- `resources/views/criteria_config/partials/*.blade.php`: Admin flag controls and create/edit serialization.
- `resources/views/components/support-activity-entry-editor.blade.php`: render the extra per-project inputs.
- `resources/views/components/support-criteria-table.blade.php`: pass scoring mode to the modal and hide the legacy score controls.
- `resources/views/components/support-criteria-table-script.blade.php`: create, reindex, validate, calculate, and summarize entry inputs.
- `resources/js/support-score-calculator.js`: browser-side pure entry and list calculation functions.
- `app/Exports/SingleReportExport.php` and `app/Exports/ReportsExport.php`: export entry details and capped totals.

---

### Task 1: Persist Template Flags and Per-Entry Score Fields

**Files:**
- Create: `database/migrations/2026_07_25_000003_add_evaluatee_defined_fields_to_support_entries.php`
- Modify: `app/Models/SupportCriteria.php`
- Modify: `app/Models/SupportActivityEntry.php`
- Modify: `app/Models/SupportActivityEntryHistory.php`
- Test: `tests/Feature/Evaluation/SupportEvaluationSchemaTest.php`

**Interfaces:**
- Produces: boolean properties `SupportCriteria::$allow_evaluatee_indicator` and `SupportCriteria::$allow_evaluatee_weight`.
- Produces: nullable decimal properties `SupportActivityEntry::$weight`, `::$achieved_score`, and `::$weighted_score`, plus nullable string `::$indicator`.
- Produces: matching `previous_*` and `new_*` history properties.

- [ ] **Step 1: Write the failing schema and model-cast test**

Add assertions:

```php
$this->assertTrue(Schema::hasColumns('support_criterias', [
    'allow_evaluatee_indicator',
    'allow_evaluatee_weight',
]));
$this->assertTrue(Schema::hasColumns('support_activity_entries', [
    'indicator', 'weight', 'achieved_score', 'weighted_score',
]));
$this->assertTrue(Schema::hasColumns('support_activity_entry_histories', [
    'previous_indicator', 'new_indicator',
    'previous_weight', 'new_weight',
    'previous_achieved_score', 'new_achieved_score',
    'previous_weighted_score', 'new_weighted_score',
]));

$criterion = new SupportCriteria([
    'allow_evaluatee_indicator' => 1,
    'allow_evaluatee_weight' => 1,
]);
$this->assertTrue($criterion->allow_evaluatee_indicator);
$this->assertTrue($criterion->allow_evaluatee_weight);
```

- [ ] **Step 2: Run the schema test and verify it fails**

Run:

```powershell
php artisan test tests/Feature/Evaluation/SupportEvaluationSchemaTest.php
```

Expected: FAIL because the new columns do not exist.

- [ ] **Step 3: Add the migration and model fields**

Use nullable values for backward compatibility:

```php
Schema::table('support_criterias', function (Blueprint $table): void {
    $table->boolean('allow_evaluatee_indicator')->default(false)
        ->after('allow_activity_entries');
    $table->boolean('allow_evaluatee_weight')->default(false)
        ->after('allow_evaluatee_indicator');
});

Schema::table('support_activity_entries', function (Blueprint $table): void {
    $table->text('indicator')->nullable()->after('content');
    $table->decimal('weight', 5, 2)->nullable()->after('indicator');
    $table->decimal('achieved_score', 5, 2)->nullable()->after('weight');
    $table->decimal('weighted_score', 20, 2)->nullable()->after('achieved_score');
});
```

Add nullable previous/new text and decimals to `support_activity_entry_histories`. Add every field to `$fillable`; cast flags to `boolean` and numeric values to `decimal:2`. The migration `down()` must drop only the new columns.

- [ ] **Step 4: Run the schema test**

Run:

```powershell
php artisan test tests/Feature/Evaluation/SupportEvaluationSchemaTest.php
```

Expected: PASS.

- [ ] **Step 5: Commit the schema boundary**

```powershell
git add database/migrations/2026_07_25_000003_add_evaluatee_defined_fields_to_support_entries.php app/Models/SupportCriteria.php app/Models/SupportActivityEntry.php app/Models/SupportActivityEntryHistory.php tests/Feature/Evaluation/SupportEvaluationSchemaTest.php
git commit -m "feat: store evaluatee-defined support fields"
```

---

### Task 2: Configure and Protect Evaluatee-Owned Fields in Templates

**Files:**
- Modify: `app/Http/Controllers/ReportStructureController.php`
- Modify: `resources/views/criteria_config/partials/support-criteria-template.blade.php`
- Modify: `resources/views/criteria_config/partials/create-script.blade.php`
- Modify: `resources/views/criteria_config/partials/script-edit-collect-form-data.blade.php`
- Modify: `resources/views/criteria_config/partials/script-edit-support-handlers.blade.php`
- Modify: `resources/views/criteria_config/partials/script-edit-populate-helpers.blade.php`
- Test: `tests/Feature/Report/SupportCriteriaTemplateTest.php`

**Interfaces:**
- Consumes: Task 1 template flags.
- Produces: template payload keys `allow_evaluatee_indicator: bool` and `allow_evaluatee_weight: bool`.
- Produces: controller validation that requires `allow_activity_entries === true` for either new flag.

- [ ] **Step 1: Write failing create/read/dependency/locking tests**

Add tests that create a criterion with both flags, retrieve it through `report-structure.show`, and assert both JSON paths are true. Add this invalid payload case:

```php
$payload = $this->payload([[
    'sequence' => 1,
    'activity_name' => '<p>งาน</p>',
    'indicator' => '<p>เกณฑ์</p>',
    'target_value' => 100,
    'weight' => 20,
    'allow_activity_entries' => false,
    'allow_evaluatee_indicator' => true,
    'allow_evaluatee_weight' => true,
]]);

$this->postJson(route('report-structure.store'), $payload)
    ->assertUnprocessable()
    ->assertJsonValidationErrors([
        'categories.0.evaluation_lists.0.support_criterias.0.allow_evaluatee_indicator',
        'categories.0.evaluation_lists.0.support_criterias.0.allow_evaluatee_weight',
    ]);
```

Add an update test that creates a `SupportScore` or `SupportActivityEntry`, changes either flag, and expects HTTP 422 while leaving the stored flags unchanged.

- [ ] **Step 2: Run the template test and verify it fails**

Run:

```powershell
php artisan test tests/Feature/Report/SupportCriteriaTemplateTest.php
```

Expected: FAIL on missing JSON fields and dependency validation.

- [ ] **Step 3: Extend controller validation and persistence**

Add both `sometimes|boolean` rules to store and update. Extend `validateSupportIndicatorConfiguration()`:

```php
$allowActivities = (bool) ($supportData['allow_activity_entries'] ?? false);
$allowIndicator = (bool) ($supportData['allow_evaluatee_indicator'] ?? false);
$allowWeight = (bool) ($supportData['allow_evaluatee_weight'] ?? false);

if (! $allowActivities && $allowIndicator) {
    $errors["{$base}.allow_evaluatee_indicator"][] =
        'ต้องเปิดให้ผู้ถูกประเมินเพิ่มกิจกรรมหรือโครงการก่อน';
}
if (! $allowActivities && $allowWeight) {
    $errors["{$base}.allow_evaluatee_weight"][] =
        'ต้องเปิดให้ผู้ถูกประเมินเพิ่มกิจกรรมหรือโครงการก่อน';
}
```

Include both flags in eager-load selects, response mapping, create attributes, update attributes, and clone attributes. Before updating an existing criterion, compare old and new flags; when either changes and `scores()->exists()` or `activityEntries()->exists()`, throw a path-specific `ValidationException`.

- [ ] **Step 4: Add Admin controls and serialization**

Add two disabled-by-default checkboxes below `support_allow_activity_entries`. Update the create and edit handlers so disabling activities clears and disables both new fields. Collect and populate both booleans:

```javascript
allow_evaluatee_indicator:
    supportBlock.querySelector('.support_allow_evaluatee_indicator')?.checked || false,
allow_evaluatee_weight:
    supportBlock.querySelector('.support_allow_evaluatee_weight')?.checked || false,
```

- [ ] **Step 5: Run template and formatting tests**

Run:

```powershell
php artisan test tests/Feature/Report/SupportCriteriaTemplateTest.php
npm run format:check
```

Expected: both commands PASS.

- [ ] **Step 6: Commit template configuration**

```powershell
git add app/Http/Controllers/ReportStructureController.php resources/views/criteria_config/partials tests/Feature/Report/SupportCriteriaTemplateTest.php
git commit -m "feat: configure evaluatee-owned support fields"
```

---

### Task 3: Calculate and Persist Evaluatee Entry Scores

**Files:**
- Create: `app/Support/SupportWeightedScore.php`
- Create: `app/Support/SupportScoreTotal.php`
- Modify: `app/Support/SupportScoreRules.php`
- Modify: `app/Services/SupportActivityEntryService.php`
- Modify: `app/Services/SupportScoreService.php`
- Test: `tests/Unit/Support/SupportWeightedScoreTest.php`
- Test: `tests/Feature/Evaluation/SupportActivityEntryServiceTest.php`
- Test: `tests/Feature/Evaluation/SupportScoreServiceTest.php`

**Interfaces:**
- Produces: `SupportWeightedScore::calculate(float $weight, float $achievedScore): float`.
- Produces: `SupportScoreTotal::forReport(Reports $report): float`.
- Preserves: `SupportActivityEntryService::persist(...): void`.

- [ ] **Step 1: Write failing formula and persistence tests**

Create the unit test:

```php
use App\Support\SupportWeightedScore;

test('it calculates and rounds a support entry weighted score', function () {
    expect(SupportWeightedScore::calculate(40, 80))->toBe(32.0)
        ->and(SupportWeightedScore::calculate(33.33, 66.67))->toBe(22.22);
});
```

In `SupportActivityEntryServiceTest`, enable both flags and persist:

```php
[
    'content' => '<p>โครงการหนึ่ง</p>',
    'indicator' => '<p>ผ่านความเห็นชอบ</p>',
    'weight' => 40,
    'achieved_score' => 80,
]
```

Assert stored values `40.00`, `80.00`, and `32.00`. Add data-provider cases for weight `0`, `-1`, `100.01`, achieved score `-0.01`, `100.01`, empty Rich Text, and unauthorized fields when their flags are false.

- [ ] **Step 2: Run focused tests and verify they fail**

Run:

```powershell
php artisan test tests/Unit/Support/SupportWeightedScoreTest.php tests/Feature/Evaluation/SupportActivityEntryServiceTest.php tests/Feature/Evaluation/SupportScoreServiceTest.php
```

Expected: FAIL because entry values are neither validated nor persisted.

- [ ] **Step 3: Implement the formula and payload boundary**

Implement:

```php
final class SupportWeightedScore
{
    public static function calculate(float $weight, float $achievedScore): float
    {
        return round(($weight * $achievedScore) / 100, 2);
    }
}
```

Add nullable payload rules for `indicator`, `weight`, and `achieved_score`, including `decimal:0,2`. Keep criterion-dependent required/forbidden checks inside `SupportActivityEntryService`, where the loaded `SupportCriteria` is authoritative.

- [ ] **Step 4: Validate, normalize, and persist entry fields**

Add a private method with this contract:

```php
/**
 * @return array{indicator:?string,weight:?float,achieved_score:?float,weighted_score:?float}
 */
private function scoreAttributes(
    SupportCriteria $criterion,
    array $entryData,
    int|string $itemIndex,
    int $entryIndex
): array
```

When `allow_evaluatee_indicator` is true, require `indicator` and validate it with `HasRichText`; otherwise reject a filled value. When `allow_evaluatee_weight` is true, require weight and achieved score in their exact ranges and calculate `weighted_score`; otherwise reject filled numeric values. Merge the returned attributes into both create and update operations.

- [ ] **Step 5: Switch SupportScoreService between scoring modes**

For criteria with `allow_evaluatee_weight === true`, require parent `achieved_score` to be null, skip `SupportScore::updateOrCreate()`, and let `SupportActivityEntryService` persist entry scores. For legacy criteria, retain the current path.

Implement `SupportScoreTotal::forReport()` as the sum of:

```php
$legacy = SupportScore::query()
    ->where('report_id', $report->id)
    ->whereHas('supportCriteria', fn ($query) =>
        $query->where('allow_evaluatee_weight', false))
    ->sum('weighted_score');

$entries = SupportActivityEntry::query()
    ->where('report_id', $report->id)
    ->whereHas('supportCriteria', fn ($query) =>
        $query->where('allow_evaluatee_weight', true))
    ->sum('weighted_score');
```

Use that total when updating `reports.support_score_total` and `support_achievement_score`.

- [ ] **Step 6: Run focused tests**

Run:

```powershell
php artisan test tests/Unit/Support/SupportWeightedScoreTest.php tests/Feature/Evaluation/SupportActivityEntryServiceTest.php tests/Feature/Evaluation/SupportScoreServiceTest.php
```

Expected: PASS, including legacy scoring tests.

- [ ] **Step 7: Commit the entry scoring service**

```powershell
git add app/Support/SupportWeightedScore.php app/Support/SupportScoreTotal.php app/Support/SupportScoreRules.php app/Services/SupportActivityEntryService.php app/Services/SupportScoreService.php tests/Unit/Support/SupportWeightedScoreTest.php tests/Feature/Evaluation/SupportActivityEntryServiceTest.php tests/Feature/Evaluation/SupportScoreServiceTest.php
git commit -m "feat: calculate support scores per activity entry"
```

---

### Task 4: Audit Reviewer Changes and Expose Entry Aggregates

**Files:**
- Modify: `app/Services/SupportActivityEntryService.php`
- Modify: `app/Support/SupportCriteriaReadModel.php`
- Test: `tests/Feature/Evaluation/SupportActivityEntryServiceTest.php`
- Test: `tests/Feature/Evaluation/SupportCriteriaReadModelTest.php`
- Test: `tests/Feature/Evaluation/SupportCriteriaReviewerFlowTest.php`

**Interfaces:**
- Consumes: Task 1 history columns and Task 3 formula.
- Produces: each read-model activity entry includes `indicator`, `weight`, `achieved_score`, `weighted_score`, and full old/new history.
- Produces: each self-weighted criterion exposes `weighted_score` as the sum of entry weighted scores and `achieved_score` as `null`.

- [ ] **Step 1: Write failing reviewer and read-model tests**

Create two entries with weighted scores `32.00` and `54.00`; assert:

```php
$item = app(SupportCriteriaReadModel::class)->forReport($report)[$evaluationList->id][0];

$this->assertTrue($item['allow_evaluatee_indicator']);
$this->assertTrue($item['allow_evaluatee_weight']);
$this->assertNull($item['achieved_score']);
$this->assertSame('86.00', $item['weighted_score']);
$this->assertSame('40.00', $item['activity_entries'][0]['weight']);
$this->assertSame('32.00', $item['activity_entries'][0]['weighted_score']);
```

Add reviewer tests that change only indicator, only weight, and only achieved score. Each change must require a reason and create one history row containing all previous/new fields.

- [ ] **Step 2: Run focused tests and verify they fail**

Run:

```powershell
php artisan test tests/Feature/Evaluation/SupportActivityEntryServiceTest.php tests/Feature/Evaluation/SupportCriteriaReadModelTest.php tests/Feature/Evaluation/SupportCriteriaReviewerFlowTest.php
```

Expected: FAIL on missing read-model fields and incomplete change detection.

- [ ] **Step 3: Expand reviewer change detection and history**

Compare a normalized snapshot:

```php
$previous = [
    'content' => $entry->content,
    'indicator' => $entry->indicator,
    'weight' => $entry->weight,
    'achieved_score' => $entry->achieved_score,
    'weighted_score' => $entry->weighted_score,
];
$next = ['content' => $content, ...$scoreAttributes];
$changed = $previous !== $next;
```

If changed, require `modification_reason`, create one `SupportActivityEntryHistory` with every old/new field, then update the entry. Preserve the current rule that reviewers cannot add, remove, reorder, or move entries across indicator groups.

- [ ] **Step 4: Extend SupportCriteriaReadModel**

Include both flags in criterion output, all four entry fields, and every old/new history field. For self-weighted criteria, aggregate loaded entries in memory:

```php
$criterionEntries = $activityEntries->get($criterion->id) ?? collect();
$entryWeightedTotal = round((float) $criterionEntries->sum('weighted_score'), 2);
```

Return the legacy `SupportScore` values only when `allow_evaluatee_weight` is false. Keep eager loading batched.

- [ ] **Step 5: Run focused tests**

Run:

```powershell
php artisan test tests/Feature/Evaluation/SupportActivityEntryServiceTest.php tests/Feature/Evaluation/SupportCriteriaReadModelTest.php tests/Feature/Evaluation/SupportCriteriaReviewerFlowTest.php
```

Expected: PASS.

- [ ] **Step 6: Commit audit and read model**

```powershell
git add app/Services/SupportActivityEntryService.php app/Support/SupportCriteriaReadModel.php tests/Feature/Evaluation/SupportActivityEntryServiceTest.php tests/Feature/Evaluation/SupportCriteriaReadModelTest.php tests/Feature/Evaluation/SupportCriteriaReviewerFlowTest.php
git commit -m "feat: audit support entry scores"
```

---

### Task 5: Add Entry Fields to the Existing Support Modal

**Files:**
- Modify: `resources/js/support-score-calculator.js`
- Modify: `resources/views/components/support-activity-entry-editor.blade.php`
- Modify: `resources/views/components/support-criteria-table.blade.php`
- Modify: `resources/views/components/support-criteria-table-script.blade.php`
- Test: `tests/js/support-score-calculator.test.mjs`
- Test: `tests/Feature/SupportCriteriaEvaluationViewTest.php`

**Interfaces:**
- Consumes: Task 4 read-model flags and fields.
- Produces: `window.SupportScoreCalculator.calculateEntryWeightedScore(weight, achievedScore)`.
- Produces: activity input names `indicator`, `weight`, and `achieved_score`; `weighted_score` is display-only.

- [ ] **Step 1: Write failing JavaScript and Blade rendering tests**

Add:

```javascript
assert.equal(calculateEntryWeightedScore(40, 80), 32);
assert.equal(calculateEntryWeightedScore(33.33, 66.67), 22.22);
assert.equal(calculateEntryWeightedScore('', 80), null);
```

Render a self-weighted item and assert the HTML contains:

```text
support_list[7][activity_entries][0][indicator]
support_list[7][activity_entries][0][weight]
support_list[7][activity_entries][0][achieved_score]
data-support-entry-weighted
```

Assert it does not contain the criterion-level `support_list[7][achieved_score]` input. Retain existing assertions for the legacy item.

- [ ] **Step 2: Run tests and verify they fail**

Run:

```powershell
npm run test:js
php artisan test tests/Feature/SupportCriteriaEvaluationViewTest.php
```

Expected: FAIL because the modal has only activity content and criterion-level scoring.

- [ ] **Step 3: Render conditional per-entry fields**

Pass the criterion flags to `support-activity-entry-editor`. Render indicator Rich Text only when allowed. Render weight and achieved score inputs plus a read-only weighted score card only when `allow_evaluatee_weight` is true. Add reviewer reason copy that names all editable project fields.

Wrap the existing criterion score grid in:

```blade
@if (!$item['allow_evaluatee_weight'])
    {{-- existing criterion score controls --}}
@endif
```

- [ ] **Step 4: Reindex and validate dynamic entry fields**

Extend `reindexActivityEntries()` to rename each new input with `activityEntryFieldName()`. Extend add-entry cloning to clear values and weighted displays. Validate visible Rich Text, weight `(0, 100]`, achieved score `[0, 100]`, and two-decimal input before closing the modal.

- [ ] **Step 5: Calculate each entry and criterion preview**

Implement:

```javascript
export function calculateEntryWeightedScore(weight, achievedScore) {
    if (weight === '' || achievedScore === '') return null;
    const result = (Number(weight) * Number(achievedScore)) / 100;
    return Number.isFinite(result) ? Math.round(result * 100) / 100 : null;
}
```

On `input` for entry weight or score, update its `data-support-entry-weighted` display, sum all entries for the active criterion, update the table row, then call `window.recalculateSupportScores()`.

- [ ] **Step 6: Run JavaScript, view, and formatting tests**

Run:

```powershell
npm run test:js
php artisan test tests/Feature/SupportCriteriaEvaluationViewTest.php
npm run format:check
```

Expected: PASS.

- [ ] **Step 7: Commit the modal change**

```powershell
git add resources/js/support-score-calculator.js resources/views/components/support-activity-entry-editor.blade.php resources/views/components/support-criteria-table.blade.php resources/views/components/support-criteria-table-script.blade.php tests/js/support-score-calculator.test.mjs tests/Feature/SupportCriteriaEvaluationViewTest.php
git commit -m "feat: edit support scores per project"
```

---

### Task 6: Cap Scores at the Evaluation-List Boundary

**Files:**
- Create: `app/Support/EvaluationListScore.php`
- Create: `app/Support/ReportEvaluationScoreQuery.php`
- Modify: `app/Support/EvaluationScoreSummary.php`
- Modify: `app/Support/ReportScoreSummary.php`
- Modify: `app/Support/AdminDashboardQuery.php`
- Modify: `app/Services/ScoreService.php`
- Modify: `resources/js/support-score-calculator.js`
- Modify: `resources/views/components/support-criteria-table-script.blade.php`
- Test: `tests/Unit/Support/EvaluationListScoreTest.php`
- Test: `tests/Unit/Support/ReportScoreSummaryTest.php`
- Test: `tests/Feature/ScoreServiceTest.php`
- Test: `tests/Feature/AdminDashboardQueryTest.php`
- Test: `tests/js/support-score-calculator.test.mjs`

**Interfaces:**
- Produces: `EvaluationListScore::fromArray(array $evaluationList): array{quantity:float,quality:float,support_raw:float,raw_total:float,total:float}`.
- Produces: `ReportEvaluationScoreQuery::summaries(iterable $reportIds): Collection<int,array{quantity:float,quality:float,support_raw:float,raw_total:float,total:float}>`.
- Changes: `ReportScoreSummary::fromTotals(float $quantity, float $quality, float $supportRaw, ?float $finalTotal = null): array`.

- [ ] **Step 1: Write failing list-cap tests**

Test a list with quantity `20`, quality `25`, support `41`, and `sum_score = 75`:

```php
$result = EvaluationListScore::fromArray($list);

expect($result)->toMatchArray([
    'quantity' => 20.0,
    'quality' => 25.0,
    'support_raw' => 41.0,
    'raw_total' => 86.0,
    'total' => 75.0,
]);
```

Add a below-cap case and `sum_score = 0` case, where zero means no positive cap and total remains raw for backward compatibility. Update `ReportScoreSummaryTest` so component totals remain raw while `total` accepts the capped override.

- [ ] **Step 2: Run score tests and verify they fail**

Run:

```powershell
php artisan test tests/Unit/Support/EvaluationListScoreTest.php tests/Unit/Support/ReportScoreSummaryTest.php tests/Feature/ScoreServiceTest.php tests/Feature/AdminDashboardQueryTest.php
```

Expected: FAIL because capping currently applies only to quality and the global support value.

- [ ] **Step 3: Implement the pure list calculator**

Move the existing quantity and quality traversal into `EvaluationListScore::fromArray()`, sum support criterion aggregates, and calculate:

```php
$rawTotal = $quantity + $quality + $supportRaw;
$max = (float) ($evaluationList['sum_score'] ?? 0);
$total = $max > 0 ? min($rawTotal, $max) : $rawTotal;
```

Do not proportionally reduce component totals; they remain raw explanatory values. `ReportScoreSummary` returns raw components and uses `$finalTotal ?? ($quantity + $quality + $supportRaw)` for `total`. Remove the independent 100-point support cap.

- [ ] **Step 4: Use the calculator in loaded-array summaries**

Refactor `EvaluationScoreSummary::fromCategoryItems()` to call `EvaluationListScore::fromArray()` once per list, sum raw components for the three cards, and sum each list's capped `total` for the grand total.

- [ ] **Step 5: Add a batched database query for dashboard statistics**

Implement `ReportEvaluationScoreQuery::summaries()` by batching quantity, quality, legacy support, and entry support grouped by `report_id + evaluation_list_id`. Merge the four collections with evaluation-list `sum_score`, apply the same pure cap, and return one summary per report. Replace `ScoreService::calculateAverageScore()` and `calculateHighestScore()` component queries with this service so dashboard values cannot bypass the list cap.

Inject the same query into `AdminDashboardQuery` and replace the quantity/quality-only calculation in `reportsWithScores()`. Populate `quantity_score`, `quality_score`, `support_score`, and capped `score` from the returned report summary. Add an `AdminDashboardQueryTest` case whose raw mixed score is `86` and whose configured list maximum is `75`.

- [ ] **Step 6: Add the browser list-cap helper**

Export:

```javascript
export function capEvaluationListScore(rawTotal, maxScore) {
    const raw = Number(rawTotal) || 0;
    const max = Number(maxScore) || 0;
    return max > 0 ? Math.min(raw, max) : raw;
}
```

Expose `sum_score` as a data attribute on each evaluation-list container and apply the helper when recalculating the displayed grand total. Keep raw support and support-achievement displays unchanged.

- [ ] **Step 7: Run score and JavaScript tests**

Run:

```powershell
php artisan test tests/Unit/Support/EvaluationListScoreTest.php tests/Unit/Support/ReportScoreSummaryTest.php tests/Feature/ScoreServiceTest.php tests/Feature/AdminDashboardQueryTest.php
npm run test:js
```

Expected: PASS with the mixed-type `86 → 75` scenario.

- [ ] **Step 8: Commit list-level capping**

```powershell
git add app/Support/EvaluationListScore.php app/Support/ReportEvaluationScoreQuery.php app/Support/EvaluationScoreSummary.php app/Support/ReportScoreSummary.php app/Support/AdminDashboardQuery.php app/Services/ScoreService.php resources/js/support-score-calculator.js resources/views/components/support-criteria-table-script.blade.php tests/Unit/Support/EvaluationListScoreTest.php tests/Unit/Support/ReportScoreSummaryTest.php tests/Feature/ScoreServiceTest.php tests/Feature/AdminDashboardQueryTest.php tests/js/support-score-calculator.test.mjs
git commit -m "fix: cap scores per evaluation list"
```

---

### Task 7: Update Confirmation Views, Dashboards, and Exports

**Files:**
- Modify: `resources/views/partials/evaluatee-confirmation-modal.blade.php`
- Modify: `resources/views/partials/evaluator-score-summary.blade.php`
- Modify: `app/Exports/SingleReportExport.php`
- Modify: `app/Exports/ReportsExport.php`
- Test: `tests/Feature/EvaluateeConfirmationModalTest.php`
- Test: `tests/Feature/EvaluationScoreSummaryViewTest.php`
- Test: `tests/Feature/ReportsExportTest.php`

**Interfaces:**
- Consumes: Task 4 entry read model and Task 6 raw/capped score summaries.
- Produces: UI and export output that distinguishes computed raw score from capped final score.

- [ ] **Step 1: Write failing presentation and export tests**

Add entry export data:

```php
'activity_entries' => [[
    'content' => '<p>จัดทำแผน</p>',
    'indicator' => '<p>แผนผ่านความเห็นชอบ</p>',
    'weight' => '40.00',
    'achieved_score' => '80.00',
    'weighted_score' => '32.00',
    'evidence_links' => ['https://example.com/plan'],
]],
```

Assert the detailed sheet contains rows for the project indicator, weight, achieved score, weighted score, and evidence. Add summary assertions that raw score `86.00` and final score `75.00` are both visible when capped.

- [ ] **Step 2: Run presentation tests and verify they fail**

Run:

```powershell
php artisan test tests/Feature/EvaluateeConfirmationModalTest.php tests/Feature/EvaluationScoreSummaryViewTest.php tests/Feature/ReportsExportTest.php
```

Expected: FAIL because entry details and capped totals are not rendered.

- [ ] **Step 3: Render raw and final totals consistently**

Keep the three component cards as raw explanatory values. In the confirmation and evaluator summary, label the grand total as the capped final total. When `raw_total > total`, add:

```blade
<p class="text-xs text-slate-500">
    คะแนนที่คำนวณได้ {{ number_format($scoreSummary['raw_total'], 2) }}
    ถูกจำกัดตามคะแนนรวมของรายการประเมิน
</p>
```

Extend `EvaluationScoreSummary` output with `raw_total`.

- [ ] **Step 4: Export project-level details and capped list totals**

In `SingleReportExport`, use `EvaluationListScore::fromArray($evaluationList)` for each “หัวข้อ” row. Under each self-weighted activity entry emit:

```php
$data[] = ['    ตัวชี้วัด', SafeHtml::plainText($activityEntry['indicator'] ?? '')];
$data[] = ['    น้ำหนัก', (float) ($activityEntry['weight'] ?? 0)];
$data[] = ['    คะแนนที่ทำได้', (float) ($activityEntry['achieved_score'] ?? 0)];
$data[] = ['    คะแนนถ่วงน้ำหนัก', (float) ($activityEntry['weighted_score'] ?? 0)];
```

Use `ReportEvaluationScoreQuery` in `ReportsExport` so dashboard export totals use the same list caps as the UI.

- [ ] **Step 5: Run presentation and export tests**

Run:

```powershell
php artisan test tests/Feature/EvaluateeConfirmationModalTest.php tests/Feature/EvaluationScoreSummaryViewTest.php tests/Feature/ReportsExportTest.php
```

Expected: PASS.

- [ ] **Step 6: Commit user-visible summaries**

```powershell
git add resources/views/partials/evaluatee-confirmation-modal.blade.php resources/views/partials/evaluator-score-summary.blade.php app/Exports/SingleReportExport.php app/Exports/ReportsExport.php tests/Feature/EvaluateeConfirmationModalTest.php tests/Feature/EvaluationScoreSummaryViewTest.php tests/Feature/ReportsExportTest.php
git commit -m "feat: show support entry score details"
```

---

### Task 8: Run Regression, Formatting, and Build Verification

**Files:**
- Modify only files needed to correct failures found by the commands below.

**Interfaces:**
- Consumes: all previous tasks.
- Produces: a verified implementation with no known regression in template, evaluatee, reviewer, scoring, or export flows.

- [ ] **Step 1: Run the complete support feature suite**

Run:

```powershell
php artisan test tests/Feature/Report/SupportCriteriaTemplateTest.php tests/Feature/Evaluation/SupportEvaluationSchemaTest.php tests/Feature/Evaluation/SupportActivityEntryServiceTest.php tests/Feature/Evaluation/SupportCriteriaReadModelTest.php tests/Feature/Evaluation/SupportScoreServiceTest.php tests/Feature/Evaluation/SupportCriteriaReviewerFlowTest.php tests/Feature/SupportCriteriaEvaluationViewTest.php tests/Feature/EvaluateeConfirmationModalTest.php tests/Feature/ReportsExportTest.php
```

Expected: PASS.

- [ ] **Step 2: Run all PHP tests**

Run:

```powershell
php artisan test
```

Expected: PASS with no failed or risky tests.

- [ ] **Step 3: Run frontend tests, formatting, and production build**

Run:

```powershell
npm run test:js
npm run format:check
npm run build
```

Expected: all commands PASS.

- [ ] **Step 4: Run PHP formatting in check mode**

Run:

```powershell
vendor/bin/pint --test
```

Expected: PASS. If Pint reports only files changed by this feature, run `vendor/bin/pint` on those explicit paths and repeat `--test`; do not reformat unrelated user changes.

- [ ] **Step 5: Inspect the final diff**

Run:

```powershell
git diff --check
git status --short
git diff --stat
```

Expected: no whitespace errors; only feature files and pre-existing user changes appear. Correct a failed check in the task that owns the affected file, rerun that task's focused tests, and amend that task's commit before declaring the implementation complete.
