# Support Achievement Score Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Persist and display both the support weighted-score total and the support achievement score calculated with a fixed target-level count of five.

**Architecture:** Add a small PHP calculator as the single backend owner of the fixed divisor and formula. `SupportScoreService` persists both report-level values, `EvaluationScoreSummary` exposes the derived value to the shared summary partial, and a testable JavaScript helper updates the new card live using the divisor emitted by PHP.

**Tech Stack:** PHP 8, Laravel migrations and Blade, Pest/PHPUnit, browser JavaScript, Node test runner

## Global Constraints

- Change only support scoring; Quantity and Quality behavior must remain unchanged.
- `support_achievement_score = support_score_total / 5` and the target-level count is fixed at `5`.
- Store and display both values with two decimal places.
- “คะแนนรวมทั้งหมด” continues to include only the support weighted-score total, not the achievement score.
- Never trust an achievement score sent by the client; the backend recalculates it.
- Hide both support result rows when the evaluation has no support criteria.

---

### Task 1: Add the backend calculator and report persistence

**Files:**
- Create: `app/Support/SupportAchievementScore.php`
- Create: `database/migrations/2026_07_23_000001_add_support_achievement_score_to_reports_table.php`
- Modify: `app/Models/Reports.php`
- Modify: `app/Services/SupportScoreService.php`
- Test: `tests/Feature/Evaluation/SupportScoreServiceTest.php`

**Interfaces:**
- Produces: `SupportAchievementScore::TARGET_LEVEL_COUNT = 5`
- Produces: `SupportAchievementScore::calculate(float $weightedTotal): float`
- Extends `SupportScoreService::persist()` result with `support_achievement_score: float`
- Persists `reports.support_achievement_score` as `decimal(20, 2)` with default `0`

- [ ] **Step 1: Write the failing service test**

Add to `SupportScoreServiceTest`:

```php
public function test_it_persists_the_support_achievement_score_using_five_target_levels(): void
{
    $this->criterion->update(['weight' => 100]);

    $result = app(SupportScoreService::class)->persist($this->report, [[
        'support_criteria_id' => $this->criterion->id,
        'achieved_score' => 4.30,
        'evidence_links' => ['https://example.com/evidence'],
        'support_achievement_score' => 99,
    ]], $this->evaluatee, null, false);

    $this->assertSame(4.30, $result['support_score_total']);
    $this->assertSame(0.86, $result['support_achievement_score']);
    $this->assertDatabaseHas('reports', [
        'id' => $this->report->id,
        'support_score_total' => '4.30',
        'support_achievement_score' => '0.86',
    ]);
}
```

The client field intentionally contains `99`; the expected backend value proves it is ignored.

- [ ] **Step 2: Run the test and verify RED**

```powershell
php .\vendor\bin\pest tests/Feature/Evaluation/SupportScoreServiceTest.php --filter="persists the support achievement"
```

Expected: FAIL because `support_achievement_score` is absent from the result and reports table.

- [ ] **Step 3: Add the calculator**

Create:

```php
<?php

namespace App\Support;

final class SupportAchievementScore
{
    public const TARGET_LEVEL_COUNT = 5;

    public static function calculate(float $weightedTotal): float
    {
        return round($weightedTotal / self::TARGET_LEVEL_COUNT, 2);
    }
}
```

- [ ] **Step 4: Add the migration and report fields**

Create the migration:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->decimal('support_achievement_score', 20, 2)
                ->default(0)
                ->after('support_score_total');
        });
    }

    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropColumn('support_achievement_score');
        });
    }
};
```

Add the field beside `support_score_total` in `Reports`:

```php
protected $fillable = [
    // existing fields remain unchanged
    'support_score_total',
    'support_achievement_score',
];

protected $casts = [
    // existing casts remain unchanged
    'support_score_total' => 'decimal:2',
    'support_achievement_score' => 'decimal:2',
];
```

- [ ] **Step 5: Persist and return both support values**

Import `App\Support\SupportAchievementScore`. After calculating `$supportScoreTotal`, calculate and update atomically:

```php
$supportAchievementScore = SupportAchievementScore::calculate($supportScoreTotal);

$report->update([
    'support_score_total' => $supportScoreTotal,
    'support_achievement_score' => $supportAchievementScore,
]);
```

Return:

```php
'support_score_total' => $supportScoreTotal,
'support_achievement_score' => $supportAchievementScore,
```

Update the method PHPDoc return shape with the new float field.

- [ ] **Step 6: Run the support service suite and verify GREEN**

```powershell
php .\vendor\bin\pest tests/Feature/Evaluation/SupportScoreServiceTest.php
```

Expected: all support service tests PASS.

- [ ] **Step 7: Commit backend persistence**

```powershell
git add app/Support/SupportAchievementScore.php database/migrations/2026_07_23_000001_add_support_achievement_score_to_reports_table.php app/Models/Reports.php app/Services/SupportScoreService.php tests/Feature/Evaluation/SupportScoreServiceTest.php
git commit -m "feat: persist support achievement score"
```

---

### Task 2: Expose and render the second support result

**Files:**
- Modify: `app/Support/EvaluationScoreSummary.php`
- Modify: `resources/views/partials/evaluator-score-summary.blade.php`
- Modify: `tests/Feature/ScoreServiceTest.php`
- Modify: `tests/Feature/EvaluationScoreSummaryViewTest.php`

**Interfaces:**
- Consumes: `SupportAchievementScore::calculate(float): float` and `TARGET_LEVEL_COUNT`
- Produces summary keys: `support_achievement: float`, `support_target_level_count: int`
- Produces DOM element: `#support-achievement-summary` with `data-support-target-level-count="5"`

- [ ] **Step 1: Write failing summary and view tests**

In `ScoreServiceTest`, add:

```php
test('evaluation summary derives support achievement without changing the overall total', function () {
    $summary = EvaluationScoreSummary::fromCategoryItems([[
        'evaluation_lists' => [[
            'quantity_items' => [],
            'quality_items' => [],
            'support_items' => [['weighted_score' => 4.30]],
        ]],
    ]]);

    expect($summary['support'])->toBe(4.3)
        ->and($summary['support_achievement'])->toBe(0.86)
        ->and($summary['support_target_level_count'])->toBe(5)
        ->and($summary['total'])->toBe(4.3);
});
```

In `EvaluationScoreSummaryViewTest`, extend the support-only fixture with `support_achievement => 0.86` and `support_target_level_count => 5`, then assert:

```php
->toContain('ผลรวมคะแนนถ่วงน้ำหนัก')
->toContain('id="support-achievement-summary"')
->toContain('data-support-target-level-count="5"')
->toContain('ผลรวมคะแนนถ่วงน้ำหนัก ÷ จำนวนระดับค่าเป้าหมาย: 5')
```

Extend the quantity-only test with:

```php
->not->toContain('id="support-achievement-summary"')
```

- [ ] **Step 2: Run both test files and verify RED**

```powershell
php .\vendor\bin\pest tests/Feature/ScoreServiceTest.php tests/Feature/EvaluationScoreSummaryViewTest.php --filter="support achievement|only categories"
```

Expected: FAIL because the summary keys and achievement card do not exist.

- [ ] **Step 3: Add derived fields to `EvaluationScoreSummary`**

Import `SupportAchievementScore` and add to the returned array:

```php
'support_achievement' => SupportAchievementScore::calculate($cappedSupportScore),
'support_target_level_count' => SupportAchievementScore::TARGET_LEVEL_COUNT,
```

Do not modify the existing `quantity`, `quality`, `support`, or `total` expressions.

- [ ] **Step 4: Render both support values under the existing `has_support` condition**

Replace only the support block in the shared partial:

```blade
@if ($scoreSummary['has_support'] ?? false)
    <div class="flex items-center justify-between">
        <span class="text-base">ผลรวมคะแนนถ่วงน้ำหนัก</span>
        <span id="support-summary" class="font-semibold text-blue-900">{{ number_format($scoreSummary['support'] ?? 0, 2) }}</span>
    </div>
    <div class="rounded-xl border border-blue-200 bg-white p-4 shadow-inner">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="text-base font-semibold text-blue-800">คะแนนผลสัมฤทธิ์ของงาน</div>
                <div class="text-sm text-blue-600">ผลรวมคะแนนถ่วงน้ำหนัก ÷ {{ $scoreSummary['support_target_level_count'] }}</div>
            </div>
            <span
                id="support-achievement-summary"
                data-support-target-level-count="{{ $scoreSummary['support_target_level_count'] }}"
                class="text-2xl font-bold text-blue-900">
                {{ number_format($scoreSummary['support_achievement'] ?? 0, 2) }}
            </span>
        </div>
    </div>
@endif
```

- [ ] **Step 5: Run summary tests and verify GREEN**

```powershell
php .\vendor\bin\pest tests/Feature/ScoreServiceTest.php tests/Feature/EvaluationScoreSummaryViewTest.php
```

Expected: all tests PASS and existing Quantity/Quality assertions remain unchanged.

- [ ] **Step 6: Commit summary output**

```powershell
git add app/Support/EvaluationScoreSummary.php resources/views/partials/evaluator-score-summary.blade.php tests/Feature/ScoreServiceTest.php tests/Feature/EvaluationScoreSummaryViewTest.php
git commit -m "feat: show support achievement score"
```

---

### Task 3: Update the achievement card live in the browser

**Files:**
- Create: `resources/js/support-score-calculator.js`
- Create: `tests/js/support-score-calculator.test.mjs`
- Modify: `resources/js/app.ts`
- Modify: `resources/views/components/support-criteria-table-script.blade.php`
- Modify: `tests/Feature/SupportCriteriaEvaluationViewTest.php`

**Interfaces:**
- Produces: `calculateSupportAchievement(weightedTotal, targetLevelCount): number`
- Exposes: `window.SupportScoreCalculator.calculateSupportAchievement`
- Consumes: `#support-achievement-summary[data-support-target-level-count]`

- [ ] **Step 1: Write the failing JavaScript unit test**

Create:

```js
import test from 'node:test';
import assert from 'node:assert/strict';

import { calculateSupportAchievement } from '../../resources/js/support-score-calculator.js';

test('calculates support achievement from the weighted total and fixed level count', () => {
    assert.equal(calculateSupportAchievement(4.3, 5), 0.86);
    assert.equal(calculateSupportAchievement(0, 5), 0);
});
```

- [ ] **Step 2: Run the JavaScript test and verify RED**

```powershell
node --test tests/js/support-score-calculator.test.mjs
```

Expected: FAIL because the module does not exist.

- [ ] **Step 3: Implement and expose the pure calculator**

Create:

```js
export function calculateSupportAchievement(weightedTotal, targetLevelCount) {
    const total = Number(weightedTotal);
    const levels = Number(targetLevelCount);
    if (!Number.isFinite(total) || !Number.isFinite(levels) || levels <= 0) return 0;
    return Math.round((total / levels + Number.EPSILON) * 100) / 100;
}

if (typeof window !== 'undefined') {
    window.SupportScoreCalculator = { calculateSupportAchievement };
}
```

Import it in `resources/js/app.ts`:

```ts
import './support-score-calculator';
```

- [ ] **Step 4: Write the failing Blade script contract test**

Add to `SupportCriteriaEvaluationViewTest` assertions that the script contains:

```php
->toContain('support-achievement-summary')
->toContain('supportTargetLevelCount')
->toContain('calculateSupportAchievement')
```

Run:

```powershell
php .\vendor\bin\pest tests/Feature/SupportCriteriaEvaluationViewTest.php --filter="shared support script"
```

Expected: FAIL because live achievement updates are absent.

- [ ] **Step 5: Update the inline recalculation without changing total-score inputs**

After updating `support-summary`, add:

```js
const supportAchievementSummary = document.getElementById('support-achievement-summary');
if (supportAchievementSummary) {
    const supportTargetLevelCount = Number(supportAchievementSummary.dataset.supportTargetLevelCount);
    const calculateSupportAchievement = window.SupportScoreCalculator?.calculateSupportAchievement
        || ((total, levels) => Math.round((total / levels + Number.EPSILON) * 100) / 100);
    supportAchievementSummary.textContent = calculateSupportAchievement(
        cappedSupport,
        supportTargetLevelCount,
    ).toFixed(2);
}
```

Keep the existing total expression unchanged:

```js
(quantity + quality + cappedSupport).toFixed(2)
```

- [ ] **Step 6: Run JavaScript and Blade contract tests and verify GREEN**

```powershell
node --test tests/js/support-score-calculator.test.mjs
php .\vendor\bin\pest tests/Feature/SupportCriteriaEvaluationViewTest.php
npm run build
```

Expected: JavaScript tests, Blade tests, and Vite build PASS.

- [ ] **Step 7: Commit live updates**

```powershell
git add resources/js/support-score-calculator.js tests/js/support-score-calculator.test.mjs resources/js/app.ts resources/views/components/support-criteria-table-script.blade.php tests/Feature/SupportCriteriaEvaluationViewTest.php
git commit -m "feat: update support achievement score live"
```

---

### Task 4: Final verification

**Files:**
- Verify only; no planned production changes

**Interfaces:**
- Consumes: Tasks 1–3
- Produces: evidence that support has two consistent values and other scoring remains unchanged

- [ ] **Step 1: Run the focused support and summary suites**

```powershell
php .\vendor\bin\pest tests/Feature/Evaluation/SupportScoreServiceTest.php tests/Feature/ScoreServiceTest.php tests/Feature/EvaluationScoreSummaryViewTest.php tests/Feature/SupportCriteriaEvaluationViewTest.php
node --test tests/js/support-score-calculator.test.mjs
```

Expected: all focused tests PASS.

- [ ] **Step 2: Run the complete test and build checks**

```powershell
php .\vendor\bin\pest
npm run test:js
npm run build
```

Expected: all PHP tests, all JavaScript tests, and the production build PASS.

- [ ] **Step 3: Check migration reversibility and whitespace**

```powershell
php artisan migrate --env=testing --force
php artisan migrate:rollback --env=testing --step=1 --force
php artisan migrate --env=testing --force
git diff --check HEAD~3..HEAD
```

Expected: migration, rollback, re-migration, and diff checks exit successfully.

- [ ] **Step 4: Inspect the scoped diff**

```powershell
git diff --stat HEAD~3..HEAD -- app/Support/SupportAchievementScore.php app/Support/EvaluationScoreSummary.php app/Models/Reports.php app/Services/SupportScoreService.php database/migrations/2026_07_23_000001_add_support_achievement_score_to_reports_table.php resources/views/partials/evaluator-score-summary.blade.php resources/views/components/support-criteria-table-script.blade.php resources/js/support-score-calculator.js resources/js/app.ts tests/Feature/Evaluation/SupportScoreServiceTest.php tests/Feature/ScoreServiceTest.php tests/Feature/EvaluationScoreSummaryViewTest.php tests/Feature/SupportCriteriaEvaluationViewTest.php tests/js/support-score-calculator.test.mjs
```

Expected: only support achievement persistence, display, live calculation, and their tests; no Quantity or Quality implementation changes.
