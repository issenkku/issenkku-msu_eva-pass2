# Quantity Criteria Activation Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Let administrators disable quantity criteria without deleting saved configuration or historical data, restore them by re-enabling, and permanently delete unused quantity criteria through a separate guarded action.

**Architecture:** Persist `quantity_enabled` on each `evaluation_lists` row and use it as the single runtime activation source. Keep the unfiltered Eloquent relationship for administrator configuration, add a shared active scope for runtime projections, and use a dedicated validation rule for score writes. Put permanent-deletion dependency checks and transactional cleanup in a focused service invoked by an admin-only controller action.

**Tech Stack:** PHP 8.2, Laravel 11, Eloquent, Blade/vanilla JavaScript, Pest/PHPUnit feature tests, Spatie Activitylog.

## Global Constraints

- Clearing “เกณฑ์ด้านปริมาณ” preserves saved quantity configuration, workload configuration, scores, and score histories.
- Disabled quantity criteria must not appear to evaluatees, evaluators, directors, managers, active score summaries, calculations, or exports.
- Re-enabling restores the same configuration and applicable saved scores.
- Permanent deletion is separate from the checkbox and has no force-delete option.
- Permanent deletion returns HTTP 409 when workload forms, quantity scores, or quantity score histories reference any targeted sub-criterion.
- A disabled quantity sub-criterion must be rejected by every score-write endpoint even when its ID is submitted manually.
- Existing evaluation lists with quantity sub-criteria are backfilled enabled; those without quantity sub-criteria are disabled.
- Quality and support criteria behavior must remain unchanged.
- Preserve unrelated worktree changes, especially the existing change in `app/Http/Controllers/ReportStructureController.php`.

---

## File Responsibility Map

### New files

- `database/migrations/2026_07_25_000001_add_quantity_enabled_to_evaluation_lists.php`
  - Adds and backfills the persisted activation flag.
- `app/Rules/ActiveQuantitySubCriteria.php`
  - Rejects score writes for missing, wrong-version, or disabled quantity sub-criteria.
- `app/Exceptions/QuantityCriteriaInUse.php`
  - Carries permanent-deletion dependency counts from the transaction to the HTTP boundary.
- `app/Services/QuantityCriteriaDeletionService.php`
  - Counts dependencies and permanently deletes one evaluation list’s unused quantity configuration transactionally.
- `tests/Feature/Report/QuantityCriteriaActivationTest.php`
  - Covers persistence, preservation, restore behavior, runtime scope, and administrator response contracts.
- `tests/Feature/Report/QuantityCriteriaRuntimeVisibilityTest.php`
  - Covers role projections, summaries/exports, and score-write rejection.
- `tests/Feature/Report/QuantityCriteriaDeletionTest.php`
  - Covers route authorization, ownership, dependency conflicts, successful cleanup, shared-main safety, and audit logging.

### Existing files

- `app/Models/EvaluationList.php`
  - Owns `quantity_enabled`, its cast, and the unfiltered administrator relationship.
- `app/Models/QuantitySubCriteria.php`
  - Provides the reusable `active()` Eloquent scope.
- `app/Http/Controllers/ReportStructureController.php`
  - Reads/writes the flag, preserves criteria while disabled, and exposes the permanent-delete action.
- `routes/report.php`
  - Adds the admin-only nested permanent-delete route.
- `resources/views/criteria_config/partials/script-edit-populate-helpers.blade.php`
  - Initializes the checkbox from `quantity_enabled` instead of child-row presence.
- `resources/views/criteria_config/partials/script-edit-collect-form-data.blade.php`
  - Sends `quantity_enabled` explicitly and preserves meaningful saved blocks while disabled.
- `resources/views/criteria_config/partials/script-edit-criteria-type-handler.blade.php`
  - Toggles visibility without deleting DOM state.
- `resources/views/criteria_config/partials/edit-evaluation-template.blade.php`
  - Adds the guarded permanent-delete button and status copy.
- `resources/views/criteria_config/partials/script-edit-event-listeners.blade.php`
  - Dispatches the permanent-delete confirmation/request.
- `resources/views/criteria_config/partials/script-edit-quantity-handlers.blade.php`
  - Handles dependency conflicts and successful block reset.
- `resources/views/criteria_config/partials/create-script.blade.php`
  - Sends the explicit activation state when creating a criteria version.
- `app/Http/Controllers/Evaluatee/DashboardEvaluateeController.php`
- `app/Http/Controllers/Evaluatee/EvaluationWorkloadController.php`
- `app/Http/Controllers/EvaluatorController.php`
- `app/Http/Controllers/DashboardController.php`
- `app/Http/Controllers/FileExportController.php`
  - Apply the shared active scope to runtime quantity reads.
- `app/Http/Controllers/Evaluatee/EvaluationScoreController.php`
- `app/Http/Controllers/EvaluatorScoreController.php`
- `app/Http/Controllers/Director/DirectorScoreController.php`
- `app/Http/Controllers/Manager/ManagerScoreController.php`
  - Use the shared validation rule and avoid deleting/replacing disabled quantity scores.
- `app/Support/EvaluationScoreSummary.php`
  - Receives only active quantity items; add a defensive enabled-state guard to array projections.

---

### Task 1: Persist and expose quantity activation

**Files:**
- Create: `database/migrations/2026_07_25_000001_add_quantity_enabled_to_evaluation_lists.php`
- Modify: `app/Models/EvaluationList.php`
- Modify: `app/Http/Controllers/ReportStructureController.php`
- Create: `tests/Feature/Report/QuantityCriteriaActivationTest.php`
- Test: `tests/Feature/Report/SupportCriteriaTemplateTest.php`

**Interfaces:**
- Produces: `evaluation_lists.quantity_enabled:boolean`
- Produces: `EvaluationList::$quantity_enabled` cast to boolean
- Produces: report-structure payload field `categories.*.evaluation_lists.*.quantity_enabled`
- Consumes: existing `EvaluationList::quantitySubCriterias()` unfiltered administrator relationship

- [ ] **Step 1: Write failing activation persistence tests**

Create a feature-test fixture containing one evaluation list with quantity
criteria and one without. Assert the administrator `show` response exposes an
independent boolean, the migration backfills the two rows differently, and
update validation requires the field.

```php
public function test_show_exposes_quantity_enabled_independently_from_saved_rows(): void
{
    [$version, $evaluationList] = $this->createQuantityStructure();
    $evaluationList->forceFill(['quantity_enabled' => false])->save();

    $this->getJson(route('report-structure.show', $version->id))
        ->assertOk()
        ->assertJsonPath(
            'data.categories.0.evaluation_lists.0.quantity_enabled',
            false,
        )
        ->assertJsonCount(
            1,
            'data.categories.0.evaluation_lists.0.quantity_main_criterias',
        );
}

public function test_update_requires_boolean_quantity_enabled(): void
{
    [$version, $evaluationList, $payload] = $this->quantityUpdateFixture();
    unset($payload['categories'][0]['evaluation_lists'][0]['quantity_enabled']);

    $this->putJson(route('report-structure.update', $version->id), $payload)
        ->assertUnprocessable()
        ->assertJsonValidationErrors(
            'categories.0.evaluation_lists.0.quantity_enabled',
        );
}

public function test_migration_backfills_only_lists_with_quantity_rows(): void
{
    [$withQuantity, $withoutQuantity] =
        $this->createEvaluationListsForBackfill();

    $migration = require database_path(
        'migrations/2026_07_25_000001_add_quantity_enabled_to_evaluation_lists.php'
    );
    $migration->down();
    $migration->up();

    $this->assertDatabaseHas('evaluation_lists', [
        'id' => $withQuantity->id,
        'quantity_enabled' => true,
    ]);
    $this->assertDatabaseHas('evaluation_lists', [
        'id' => $withoutQuantity->id,
        'quantity_enabled' => false,
    ]);
}
```

- [ ] **Step 2: Run the focused tests and verify red**

Run:

```powershell
php artisan test tests/Feature/Report/QuantityCriteriaActivationTest.php
```

Expected: failures because the database column and response field do not exist.

- [ ] **Step 3: Add and backfill the database column**

Create the migration with an explicit data backfill:

```php
Schema::table('evaluation_lists', function (Blueprint $table) {
    $table->boolean('quantity_enabled')->default(false)->after('annotation');
});

DB::table('evaluation_lists')
    ->whereExists(function ($query) {
        $query->selectRaw('1')
            ->from('quantity_sub_criterias')
            ->whereColumn(
                'quantity_sub_criterias.evaluation_list_id',
                'evaluation_lists.id',
            );
    })
    ->update(['quantity_enabled' => true]);
```

The `down()` method drops only `quantity_enabled`.

- [ ] **Step 4: Add model and report-structure contracts**

Add `quantity_enabled` to `EvaluationList::$fillable` and `$casts`:

```php
protected $casts = [
    'quantity_enabled' => 'boolean',
];
```

In `store()` and `update()`, validate:

```php
'categories.*.evaluation_lists.*.quantity_enabled' => 'required|boolean',
```

Pass the value to both `EvaluationList::create()` and
`$evaluationList->update()`:

```php
'quantity_enabled' => (bool) $evalListData['quantity_enabled'],
```

In `show()`, serialize the persisted value next to `annotation`:

```php
'quantity_enabled' => (bool) $evalList->quantity_enabled,
```

- [ ] **Step 5: Run activation tests and adjacent report tests**

Run:

```powershell
php artisan test tests/Feature/Report/QuantityCriteriaActivationTest.php
php artisan test tests/Feature/Report/ReportStructureTest.php
php artisan test tests/Feature/Report/SupportCriteriaTemplateTest.php
```

Expected: PASS after updating existing report-structure and support-template
test payloads with explicit `quantity_enabled` values.

- [ ] **Step 6: Commit the persistence slice**

```powershell
git add database/migrations/2026_07_25_000001_add_quantity_enabled_to_evaluation_lists.php app/Models/EvaluationList.php app/Http/Controllers/ReportStructureController.php tests/Feature/Report/QuantityCriteriaActivationTest.php tests/Feature/Report/ReportStructureTest.php tests/Feature/Report/SupportCriteriaTemplateTest.php
git commit -m "feat: persist quantity criteria activation"
```

---

### Task 2: Preserve quantity configuration while disabled in the editor

**Files:**
- Modify: `app/Http/Controllers/ReportStructureController.php`
- Modify: `resources/views/criteria_config/partials/script-edit-populate-helpers.blade.php`
- Modify: `resources/views/criteria_config/partials/script-edit-collect-form-data.blade.php`
- Modify: `resources/views/criteria_config/partials/script-edit-criteria-type-handler.blade.php`
- Modify: `resources/views/criteria_config/partials/script-edit-quantity-handlers.blade.php`
- Modify: `resources/views/criteria_config/partials/create-script.blade.php`
- Test: `tests/Feature/Report/QuantityCriteriaActivationTest.php`
- Test: `tests/Feature/CreateModalContractTest.php`

**Interfaces:**
- Consumes: `quantity_enabled:boolean` from Task 1
- Produces: update semantics in which disabled/omitted quantity arrays preserve rows
- Produces: editor payload `{ quantity_enabled, quantity_main_criterias }`

- [ ] **Step 1: Write failing preservation and restore tests**

Add tests that create a main criterion, formula, sub-criterion, workload form,
quantity score, and history; disable the evaluation list; then assert every row
still exists. Re-enable using the same IDs and assert the configuration is
returned unchanged.

```php
public function test_disabling_quantity_preserves_configuration_and_history(): void
{
    [$version, $evaluationList, $payload, $records] =
        $this->quantityUpdateFixtureWithDependencies();

    $payload['categories'][0]['evaluation_lists'][0]['quantity_enabled'] = false;
    unset($payload['categories'][0]['evaluation_lists'][0]['quantity_main_criterias']);

    $this->putJson(route('report-structure.update', $version->id), $payload)
        ->assertOk();

    $this->assertDatabaseHas('evaluation_lists', [
        'id' => $evaluationList->id,
        'quantity_enabled' => false,
    ]);
    $this->assertDatabaseHas('quantity_main_criterias', ['id' => $records['main']->id]);
    $this->assertDatabaseHas('quantity_sub_criterias', ['id' => $records['sub']->id]);
    $this->assertDatabaseHas('formulas', ['id' => $records['formula']->id]);
    $this->assertDatabaseHas('workload_forms', ['id' => $records['form']->id]);
    $this->assertDatabaseHas('quantity_scores', ['id' => $records['score']->id]);
    $this->assertDatabaseHas('quantity_score_histories', ['id' => $records['history']->id]);
}
```

- [ ] **Step 2: Run the test and verify current deletion behavior**

Run:

```powershell
php artisan test tests/Feature/Report/QuantityCriteriaActivationTest.php --filter=disabling
```

Expected: FAIL because update cleanup currently treats the absent quantity
array as deletion.

- [ ] **Step 3: Make update cleanup activation-aware**

Track enabled evaluation-list IDs while processing the payload:

```php
$quantitySyncEvaluationIds = [];

if ((bool) $evalListData['quantity_enabled']) {
    $quantitySyncEvaluationIds[] = $evaluationList->id;
}
```

Restrict quantity sub/main removal to enabled evaluation lists being actively
synced. Do not add rows from a disabled evaluation list to the deletion query.
When deleting main criteria after enabled-list synchronization, delete only
main rows with no remaining quantity sub-criteria:

```php
$version->quantityMainCriterias()
    ->whereDoesntHave('quantitySubCriterias')
    ->delete();
```

This replaces version-wide omission cleanup for quantity criteria. Quality and
support cleanup remains unchanged.

- [ ] **Step 4: Initialize and collect the checkbox independently**

In `populateEvaluationList()`, replace child-row inference:

```javascript
const hasQuantity = Boolean(evalData.quantity_enabled);
quantityCheckbox.checked = hasQuantity;
```

In `collectFormData()`, always include:

```javascript
quantity_enabled: evalBlock.querySelector('.quantity_criteria_type').checked,
```

Serialize complete existing quantity blocks even while disabled, but filter an
untouched blank template with no ID and no meaningful field values:

```javascript
const hasSavedId = Boolean(
    quantBlock.querySelector('.quantity_main_id_value')?.value
);
const hasMeaningfulValue = Boolean(
    quantBlock.querySelector('.quant_name').value.trim()
);

if (!quantityEnabled && !hasSavedId && !hasMeaningfulValue) {
    return;
}
```

Keep `handleCriteriaTypeChange()` limited to the `hidden` class toggle. It must
not clear inputs, IDs, or child nodes.

In the create-page collector, add the explicit state when constructing
`evalList`:

```javascript
quantity_enabled: quantityChecked,
```

- [ ] **Step 5: Add Blade/JavaScript contract tests**

Assert the editor code references the explicit flag and no longer derives the
checkbox solely from array length:

```php
$script = file_get_contents(resource_path(
    'views/criteria_config/partials/script-edit-populate-helpers.blade.php'
));

$this->assertStringContainsString(
    'Boolean(evalData.quantity_enabled)',
    $script,
);

$createScript = file_get_contents(resource_path(
    'views/criteria_config/partials/create-script.blade.php'
));

$this->assertStringContainsString(
    'quantity_enabled: quantityChecked',
    $createScript,
);
```

- [ ] **Step 6: Run editor and persistence tests**

Run:

```powershell
php artisan test tests/Feature/Report/QuantityCriteriaActivationTest.php
php artisan test tests/Feature/CreateModalContractTest.php
```

Expected: PASS.

- [ ] **Step 7: Commit the preservation slice**

```powershell
git add app/Http/Controllers/ReportStructureController.php resources/views/criteria_config/partials/script-edit-populate-helpers.blade.php resources/views/criteria_config/partials/script-edit-collect-form-data.blade.php resources/views/criteria_config/partials/script-edit-criteria-type-handler.blade.php resources/views/criteria_config/partials/script-edit-quantity-handlers.blade.php resources/views/criteria_config/partials/create-script.blade.php tests/Feature/Report/QuantityCriteriaActivationTest.php tests/Feature/CreateModalContractTest.php
git commit -m "feat: preserve disabled quantity criteria"
```

---

### Task 3: Hide disabled quantity criteria from runtime reads and summaries

**Files:**
- Modify: `app/Models/QuantitySubCriteria.php`
- Modify: `app/Http/Controllers/Evaluatee/DashboardEvaluateeController.php`
- Modify: `app/Http/Controllers/Evaluatee/EvaluationWorkloadController.php`
- Modify: `app/Http/Controllers/EvaluatorController.php`
- Modify: `app/Http/Controllers/DashboardController.php`
- Modify: `app/Http/Controllers/FileExportController.php`
- Modify: `app/Support/EvaluationScoreSummary.php`
- Create: `tests/Feature/Report/QuantityCriteriaRuntimeVisibilityTest.php`

**Interfaces:**
- Consumes: `evaluation_lists.quantity_enabled` from Task 1
- Produces: `QuantitySubCriteria::scopeActive(Builder $query): Builder`
- Produces: runtime projections with empty quantity collections for disabled lists

- [ ] **Step 1: Write failing active-scope and role projection tests**

Create one enabled and one disabled evaluation list under the same criteria
version. Assert the scope returns only the enabled sub-criterion and assert the
evaluatee evaluation response does not contain the disabled criterion name.

```php
public function test_active_scope_excludes_disabled_evaluation_lists(): void
{
    [$enabledSub, $disabledSub] = $this->createMixedActivationCriteria();

    $ids = QuantitySubCriteria::query()->active()->pluck('id');

    $this->assertTrue($ids->contains($enabledSub->id));
    $this->assertFalse($ids->contains($disabledSub->id));
}
```

Add focused assertions for the evaluator/director data builders and export
input collection using their existing route fixtures.

- [ ] **Step 2: Run runtime visibility tests and verify red**

Run:

```powershell
php artisan test tests/Feature/Report/QuantityCriteriaRuntimeVisibilityTest.php
```

Expected: FAIL because all current relationships load surviving rows regardless
of activation.

- [ ] **Step 3: Add the shared active scope**

In `QuantitySubCriteria`:

```php
use Illuminate\Database\Eloquent\Builder;

public function scopeActive(Builder $query): Builder
{
    return $query->whereHas(
        'evaluationList',
        fn (Builder $evaluationQuery) => $evaluationQuery
            ->where('quantity_enabled', true),
    );
}
```

- [ ] **Step 4: Apply the scope to every runtime eager load**

Keep the relation name `quantitySubCriterias` so existing Blade components do
not need parallel property names:

```php
'quantitySubCriterias' => fn ($query) => $query
    ->active()
    ->with('mainCriteria'),
```

Apply the closure in:

- `DashboardEvaluateeController` category-item assembly and report loading;
- `EvaluationWorkloadController` selectable workload criteria;
- both evaluator-loading paths in `EvaluatorController`;
- admin/director runtime assembly in `DashboardController`; and
- the criteria relationship passed to `FileExportController`.

For direct query-builder joins in `DashboardController`, join
`evaluation_lists` and add:

```php
->join('evaluation_lists as el', 'el.id', '=', 'qs.evaluation_list_id')
->where('el.quantity_enabled', true)
```

- [ ] **Step 5: Add a defensive score-summary guard**

When category-item arrays carry the flag, skip disabled quantity arrays even if
a caller accidentally passes stale items:

```php
$quantityEnabled = (bool) ($evaluationList['quantity_enabled'] ?? true);
$quantityItems = $quantityEnabled
    ? ($evaluationList['quantity_items'] ?? [])
    : [];
```

Use `$quantityItems` for `has_quantity` and quantity total accumulation.

- [ ] **Step 6: Run runtime, summary, and export tests**

Run:

```powershell
php artisan test tests/Feature/Report/QuantityCriteriaRuntimeVisibilityTest.php
php artisan test tests/Feature/EvaluationScoreSummaryViewTest.php
php artisan test tests/Feature/ScoreServiceTest.php
php artisan test tests/Feature/ReportExportAuthorizationTest.php
```

Expected: PASS with disabled criteria absent and enabled criteria unchanged.

- [ ] **Step 7: Commit the runtime visibility slice**

```powershell
git add app/Models/QuantitySubCriteria.php app/Http/Controllers/Evaluatee/DashboardEvaluateeController.php app/Http/Controllers/Evaluatee/EvaluationWorkloadController.php app/Http/Controllers/EvaluatorController.php app/Http/Controllers/DashboardController.php app/Http/Controllers/FileExportController.php app/Support/EvaluationScoreSummary.php tests/Feature/Report/QuantityCriteriaRuntimeVisibilityTest.php
git commit -m "feat: hide disabled quantity criteria"
```

---

### Task 4: Reject disabled criteria at score-write boundaries

**Files:**
- Create: `app/Rules/ActiveQuantitySubCriteria.php`
- Modify: `app/Http/Controllers/Evaluatee/EvaluationScoreController.php`
- Modify: `app/Http/Controllers/EvaluatorScoreController.php`
- Modify: `app/Http/Controllers/Director/DirectorScoreController.php`
- Modify: `app/Http/Controllers/Manager/ManagerScoreController.php`
- Test: `tests/Feature/Report/QuantityCriteriaRuntimeVisibilityTest.php`

**Interfaces:**
- Consumes: `QuantitySubCriteria::active()` from Task 3
- Produces: `new ActiveQuantitySubCriteria(int $criteriaVersionId)`
- Produces: validation failure for disabled/wrong-version sub-criterion IDs

- [ ] **Step 1: Write failing forged-request tests for all four roles**

Use each controller’s existing authenticated fixture and submit a disabled
sub-criterion ID:

```php
$response->assertUnprocessable()
    ->assertJsonValidationErrors(
        'quantity_list.'.$disabledSub->id.'.quantity_sub_criteria_id',
    );

$this->assertDatabaseMissing('quantity_scores', [
    'report_id' => $report->id,
    'quantity_sub_criteria_id' => $disabledSub->id,
]);
```

Also seed an existing enabled score and verify that submitting a form with no
active quantity fields does not erase preserved disabled scores.

- [ ] **Step 2: Run the four-role forged-request tests and verify red**

Run:

```powershell
php artisan test tests/Feature/Report/QuantityCriteriaRuntimeVisibilityTest.php --filter=disabled_quantity
```

Expected: FAIL because current `Rule::exists()` checks only criteria-version
ownership.

- [ ] **Step 3: Implement the reusable validation rule**

```php
final class ActiveQuantitySubCriteria implements ValidationRule
{
    public function __construct(
        private readonly int $criteriaVersionId,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $exists = QuantitySubCriteria::query()
            ->active()
            ->where('criteria_version_id', $this->criteriaVersionId)
            ->whereKey($value)
            ->exists();

        if (! $exists) {
            $fail('เกณฑ์ด้านปริมาณนี้ไม่ได้เปิดใช้งาน');
        }
    }
}
```

- [ ] **Step 4: Replace the four duplicated exists rules**

In every score controller use:

```php
'quantity_list.*.quantity_sub_criteria_id' => [
    'nullable',
    'integer',
    new ActiveQuantitySubCriteria((int) $criteriaVersionId),
],
```

Before deleting/replacing quantity scores, derive active IDs and limit mutation:

```php
$activeQuantitySubIds = QuantitySubCriteria::query()
    ->active()
    ->where('criteria_version_id', $criteriaVersionId)
    ->pluck('id');

QuantityScore::where('report_id', $reportId)
    ->whereIn('quantity_sub_criteria_id', $activeQuantitySubIds)
    ->delete();
```

This preserves scores belonging to disabled criteria.

- [ ] **Step 5: Run score controller tests**

Run:

```powershell
php artisan test tests/Feature/Report/QuantityCriteriaRuntimeVisibilityTest.php
php artisan test tests/Feature --filter=EvaluationScore
php artisan test tests/Feature --filter=EvaluatorScore
php artisan test tests/Feature --filter=DirectorScore
php artisan test tests/Feature --filter=ManagerScore
```

Expected: PASS.

- [ ] **Step 6: Commit the score-boundary slice**

```powershell
git add app/Rules/ActiveQuantitySubCriteria.php app/Http/Controllers/Evaluatee/EvaluationScoreController.php app/Http/Controllers/EvaluatorScoreController.php app/Http/Controllers/Director/DirectorScoreController.php app/Http/Controllers/Manager/ManagerScoreController.php tests/Feature/Report/QuantityCriteriaRuntimeVisibilityTest.php
git commit -m "fix: reject disabled quantity scores"
```

---

### Task 5: Add guarded permanent deletion

**Files:**
- Create: `app/Exceptions/QuantityCriteriaInUse.php`
- Create: `app/Services/QuantityCriteriaDeletionService.php`
- Modify: `app/Http/Controllers/ReportStructureController.php`
- Modify: `routes/report.php`
- Modify: `resources/views/criteria_config/partials/edit-evaluation-template.blade.php`
- Modify: `resources/views/criteria_config/partials/script-edit-event-listeners.blade.php`
- Modify: `resources/views/criteria_config/partials/script-edit-quantity-handlers.blade.php`
- Create: `tests/Feature/Report/QuantityCriteriaDeletionTest.php`

**Interfaces:**
- Produces: `new QuantityCriteriaInUse(array $dependencies)`
- Produces: `QuantityCriteriaInUse::dependencies(): array{workload_forms:int,quantity_scores:int,quantity_score_histories:int}`
- Produces: `QuantityCriteriaDeletionService::dependencyCounts(EvaluationList $evaluationList): array{workload_forms:int,quantity_scores:int,quantity_score_histories:int}`
- Produces: `QuantityCriteriaDeletionService::delete(EvaluationList $evaluationList, User $actor): array{main_criteria:int,sub_criteria:int}`
- Produces: `DELETE report-version/{criteriaVersion}/evaluation-lists/{evaluationList}/quantity-criteria`
- Consumes: `quantity_enabled = false` from Task 1

- [ ] **Step 1: Write failing route/service behavior tests**

Cover:

```php
public function test_permanent_delete_requires_disabled_evaluation_list(): void
{
    [$version, $evaluationList] = $this->createQuantityStructure(enabled: true);

    $this->deleteJson(route('report-structure.quantity-criteria.destroy', [
        'criteriaVersion' => $version,
        'evaluationList' => $evaluationList,
    ]))->assertUnprocessable();
}

public function test_permanent_delete_returns_dependency_counts_without_deleting(): void
{
    [$version, $evaluationList, $records] =
        $this->createDisabledQuantityStructureWithDependencies();

    $this->deleteJson(route('report-structure.quantity-criteria.destroy', [
        'criteriaVersion' => $version,
        'evaluationList' => $evaluationList,
    ]))
        ->assertStatus(409)
        ->assertJsonPath('dependencies.workload_forms', 1)
        ->assertJsonPath('dependencies.quantity_scores', 1)
        ->assertJsonPath('dependencies.quantity_score_histories', 1);

    $this->assertDatabaseHas('quantity_sub_criterias', ['id' => $records['sub']->id]);
}
```

Also test 404 for a mismatched version/list pair, 403 for non-admin, successful
deletion, and preservation of a main criterion that still has a sub-criterion
in another evaluation list.

- [ ] **Step 2: Run deletion tests and verify red**

Run:

```powershell
php artisan test tests/Feature/Report/QuantityCriteriaDeletionTest.php
```

Expected: route-not-found failures.

- [ ] **Step 3: Implement dependency counting and transactional deletion**

The service gets target IDs once:

```php
$subIds = $evaluationList->quantitySubCriterias()->pluck('id');

return [
    'workload_forms' => WorkloadForm::whereIn(
        'quantity_sub_criteria_id',
        $subIds,
    )->count(),
    'quantity_scores' => QuantityScore::whereIn(
        'quantity_sub_criteria_id',
        $subIds,
    )->count(),
    'quantity_score_histories' => QuantityScoreHistory::whereIn(
        'quantity_sub_criteria_id',
        $subIds,
    )->count(),
];
```

`delete()` throws a dedicated domain exception containing counts when any count
is non-zero. Otherwise it captures main IDs, deletes target sub-criteria, then
deletes only orphaned main criteria:

```php
if (array_sum($dependencies) > 0) {
    throw new QuantityCriteriaInUse($dependencies);
}

QuantityMainCriteria::whereIn('id', $mainIds)
    ->whereDoesntHave('quantitySubCriterias')
    ->delete();
```

Record the action:

```php
AuditLog::record(
    'report_structure',
    'ลบข้อมูลเกณฑ์ปริมาณทั้งหมด',
    [
        'evaluation_list_id' => $evaluationList->id,
        'deleted_main_criteria' => $deletedMainCount,
        'deleted_sub_criteria' => $deletedSubCount,
    ],
    $evaluationList,
    $actor,
);
```

- [ ] **Step 4: Add the nested controller action and route**

Route:

```php
Route::delete(
    '/{criteriaVersion}/evaluation-lists/{evaluationList}/quantity-criteria',
    [ReportStructureController::class, 'destroyQuantityCriteria'],
)->name('quantity-criteria.destroy');
```

The controller verifies ownership, requires disabled state, maps the dependency
exception to HTTP 409, and returns deleted counts on success:

```php
abort_unless(
    $evaluationList->criteria_version_id === $criteriaVersion->id,
    404,
);

if ($evaluationList->quantity_enabled) {
    return response()->json([
        'message' => 'กรุณาปิดเกณฑ์ด้านปริมาณก่อนลบถาวร',
    ], 422);
}
```

- [ ] **Step 5: Add the administrator button and request handling**

Render the destructive button inside each quantity container:

```html
<button
    type="button"
    class="delete_all_quantity_criteria_btn hidden text-sm text-red-700"
>
    ลบข้อมูลเกณฑ์ปริมาณทั้งหมด
</button>
```

Show it only when the checkbox is off and the evaluation list has an ID plus
saved quantity IDs. Confirm using the evaluation-list name. On HTTP 409, format
the three dependency counts in the validation modal. On success, remove saved
quantity blocks, append one clean template, clear stored IDs, and keep the
checkbox off.

- [ ] **Step 6: Run deletion, audit, and route tests**

Run:

```powershell
php artisan test tests/Feature/Report/QuantityCriteriaDeletionTest.php
php artisan test tests/Feature/AuditLoggingTest.php
php artisan route:list --name=report-structure.quantity-criteria.destroy
```

Expected: PASS and exactly one admin-only DELETE route.

- [ ] **Step 7: Commit the permanent-deletion slice**

```powershell
git add app/Exceptions/QuantityCriteriaInUse.php app/Services/QuantityCriteriaDeletionService.php app/Http/Controllers/ReportStructureController.php routes/report.php resources/views/criteria_config/partials/edit-evaluation-template.blade.php resources/views/criteria_config/partials/script-edit-event-listeners.blade.php resources/views/criteria_config/partials/script-edit-quantity-handlers.blade.php tests/Feature/Report/QuantityCriteriaDeletionTest.php
git commit -m "feat: guard permanent quantity deletion"
```

---

### Task 6: Regression verification and cleanup

**Files:**
- Modify only files identified by failing relevant tests
- Test: all files added or changed in Tasks 1–5

**Interfaces:**
- Consumes: all activation, runtime visibility, validation, and deletion contracts
- Produces: verified end-to-end feature with no debug instrumentation

- [ ] **Step 1: Run formatting checks**

Run:

```powershell
vendor\bin\pint --test
npm run build
```

Expected: PASS. If Pint reports only files changed by this feature, run
`vendor\bin\pint` on those exact paths and re-run `--test`. Do not reformat
unrelated user-modified files.

- [ ] **Step 2: Run all focused feature tests together**

Run:

```powershell
php artisan test tests/Feature/Report/QuantityCriteriaActivationTest.php tests/Feature/Report/QuantityCriteriaRuntimeVisibilityTest.php tests/Feature/Report/QuantityCriteriaDeletionTest.php tests/Feature/Report/ReportStructureTest.php
```

Expected: PASS.

- [ ] **Step 3: Run the complete automated suite**

Run:

```powershell
php artisan test
```

Expected: PASS. Investigate failures caused by newly required
`quantity_enabled` payloads; update only fixtures that construct evaluation-list
payloads.

- [ ] **Step 4: Verify migration behavior against a disposable test database**

Run:

```powershell
php artisan migrate:fresh --env=testing
php artisan test tests/Feature/Report/QuantityCriteriaActivationTest.php
```

Expected: migrations complete and activation tests pass.

- [ ] **Step 5: Perform the browser smoke test**

Using a local test administrator and disposable records:

1. Open an evaluation list with saved quantity criteria.
2. Clear the checkbox and save.
3. Reload the editor and confirm the checkbox is off.
4. Confirm the evaluatee, evaluator, and director forms omit the quantity block.
5. Select the checkbox, save, reload, and confirm the original values return.
6. Disable again and attempt permanent deletion with a workload form; confirm
   the dependency message.
7. Repeat with an unused fixture; confirm permanent deletion succeeds.

- [ ] **Step 6: Remove temporary diagnostics and inspect the final diff**

Run:

```powershell
rg -n "\[DEBUG-" app resources tests
git diff --check
git status --short
```

Expected: no debug tags, no whitespace errors, and only intentional feature
changes plus the user’s pre-existing worktree changes.

- [ ] **Step 7: Commit final test/format adjustments if any**

```powershell
git add -- app/Models/EvaluationList.php app/Models/QuantitySubCriteria.php app/Rules/ActiveQuantitySubCriteria.php app/Exceptions/QuantityCriteriaInUse.php app/Services/QuantityCriteriaDeletionService.php app/Http/Controllers/ReportStructureController.php app/Http/Controllers/Evaluatee/DashboardEvaluateeController.php app/Http/Controllers/Evaluatee/EvaluationWorkloadController.php app/Http/Controllers/Evaluatee/EvaluationScoreController.php app/Http/Controllers/EvaluatorController.php app/Http/Controllers/EvaluatorScoreController.php app/Http/Controllers/Director/DirectorScoreController.php app/Http/Controllers/Manager/ManagerScoreController.php app/Http/Controllers/DashboardController.php app/Http/Controllers/FileExportController.php app/Support/EvaluationScoreSummary.php routes/report.php resources/views/criteria_config/partials/script-edit-populate-helpers.blade.php resources/views/criteria_config/partials/script-edit-collect-form-data.blade.php resources/views/criteria_config/partials/script-edit-criteria-type-handler.blade.php resources/views/criteria_config/partials/script-edit-quantity-handlers.blade.php resources/views/criteria_config/partials/script-edit-event-listeners.blade.php resources/views/criteria_config/partials/edit-evaluation-template.blade.php resources/views/criteria_config/partials/create-script.blade.php tests/Feature/Report/QuantityCriteriaActivationTest.php tests/Feature/Report/QuantityCriteriaRuntimeVisibilityTest.php tests/Feature/Report/QuantityCriteriaDeletionTest.php tests/Feature/Report/ReportStructureTest.php tests/Feature/Report/SupportCriteriaTemplateTest.php tests/Feature/CreateModalContractTest.php database/migrations/2026_07_25_000001_add_quantity_enabled_to_evaluation_lists.php
git commit -m "test: verify quantity criteria activation"
```

Skip this commit when Step 1–6 required no file changes.

---

## Plan Self-Review

- **Spec coverage:** Tasks 1–2 cover persisted activation and preservation;
  Task 3 covers runtime roles, summaries, and export; Task 4 covers write-side
  enforcement and score preservation; Task 5 covers separate guarded deletion,
  ownership, dependency counts, shared-main safety, and audit logging; Task 6
  covers regression and manual role verification.
- **Boundary consistency:** `quantity_enabled` is owned by `EvaluationList`;
  runtime reads share `QuantitySubCriteria::active()`; score writes share
  `ActiveQuantitySubCriteria`; deletion logic is isolated in
  `QuantityCriteriaDeletionService`.
- **Data safety:** Checkbox updates never cascade-delete configuration.
  Permanent deletion has no force path and cannot remove workload or scoring
  history.
- **Scope:** Quality/support activation and historical-data deletion remain
  outside this plan.
