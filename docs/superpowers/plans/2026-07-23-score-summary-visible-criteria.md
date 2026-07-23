# Criteria-Aware Score Summary Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Show only score categories that have criteria in the current evaluation while preserving zero scores for categories that do exist.

**Architecture:** `EvaluationScoreSummary::fromCategoryItems()` remains the single score-summary read model and gains three boolean visibility fields. The shared Blade partial renders rows from those fields, and the director component delegates its duplicated summary calculation and markup to the same class and partial so every role follows one rule.

**Tech Stack:** PHP 8, Laravel Blade, Pest, PHPUnit

## Global Constraints

- A category is visible only when at least one corresponding `quantity_items`, `quality_items`, or `support_items` entry exists.
- A visible category remains visible when its score is `0.00`.
- “คะแนนรวมทั้งหมด” always remains visible and keeps the existing score formulas and caps.
- Apply the same behavior to evaluatee, evaluator, and approval/director screens.
- Do not change score persistence, criteria structure, role permissions, or unrelated UI copy.

---

### Task 1: Add criterion-presence metadata to the score summary

**Files:**
- Modify: `app/Support/EvaluationScoreSummary.php`
- Test: `tests/Feature/ScoreServiceTest.php`

**Interfaces:**
- Consumes: `EvaluationScoreSummary::fromCategoryItems(array $categoryItems): array`
- Produces: existing numeric keys `quantity`, `quality`, `support`, `total` plus boolean keys `has_quantity`, `has_quality`, `has_support`

- [ ] **Step 1: Write the failing metadata test**

Add this test after the existing support-cap test:

```php
test('evaluation summary exposes criterion presence independently from zero scores', function () {
    $summary = EvaluationScoreSummary::fromCategoryItems([[
        'evaluation_lists' => [[
            'quantity_items' => [[
                'sub_criterias' => [['score_d' => 0]],
            ]],
            'quality_items' => [],
            'support_items' => [[
                'weighted_score' => null,
            ]],
            'sum_score' => 0,
        ]],
    ]]);

    expect($summary)
        ->hasAll(['has_quantity', 'has_quality', 'has_support'])
        ->and($summary['has_quantity'])->toBeTrue()
        ->and($summary['has_quality'])->toBeFalse()
        ->and($summary['has_support'])->toBeTrue()
        ->and($summary['quantity'])->toBe(0.0)
        ->and($summary['support'])->toBe(0.0);
});
```

- [ ] **Step 2: Run the focused test and verify RED**

Run:

```powershell
php artisan test tests/Feature/ScoreServiceTest.php --filter="criterion presence"
```

Expected: FAIL because the three `has_*` keys do not exist.

- [ ] **Step 3: Add presence tracking to the read model**

Initialize the flags beside the score totals:

```php
$hasQuantity = false;
$hasQuality = false;
$hasSupport = false;
```

Inside each evaluation-list iteration, before calculating category scores, set them from actual item arrays:

```php
$hasQuantity = $hasQuantity || ! empty($evaluationList['quantity_items']);
$hasQuality = $hasQuality || ! empty($evaluationList['quality_items']);
$hasSupport = $hasSupport || ! empty($evaluationList['support_items']);
```

Return them with the existing numeric fields:

```php
return [
    'quantity' => $totalQuantityScore,
    'quality' => $totalQualityScore,
    'support' => $cappedSupportScore,
    'total' => $totalQuantityScore + $totalQualityScore + $cappedSupportScore,
    'has_quantity' => $hasQuantity,
    'has_quality' => $hasQuality,
    'has_support' => $hasSupport,
];
```

- [ ] **Step 4: Run score-summary tests and verify GREEN**

Run:

```powershell
php artisan test tests/Feature/ScoreServiceTest.php --filter="evaluation summary"
```

Expected: both evaluation-summary tests PASS.

- [ ] **Step 5: Commit the read-model change**

```powershell
git add app/Support/EvaluationScoreSummary.php tests/Feature/ScoreServiceTest.php
git commit -m "feat: expose score category presence"
```

---

### Task 2: Conditionally render the shared score-summary rows

**Files:**
- Create: `tests/Feature/EvaluationScoreSummaryViewTest.php`
- Modify: `resources/views/partials/evaluator-score-summary.blade.php`

**Interfaces:**
- Consumes: `$scoreSummary` with numeric score fields and boolean `has_quantity`, `has_quality`, `has_support`
- Produces: optional DOM elements `#quantity-summary`, `#quality-summary`, and `#support-summary`; always produces `#total-summary`

- [ ] **Step 1: Write failing view behavior tests**

Create the test file:

```php
<?php

test('score summary shows only categories that have criteria', function () {
    $html = view('partials.evaluator-score-summary', [
        'scoreSummary' => [
            'quantity' => 0.0,
            'quality' => 0.0,
            'support' => 1.0,
            'total' => 1.0,
            'has_quantity' => false,
            'has_quality' => false,
            'has_support' => true,
        ],
    ])->render();

    expect($html)
        ->not->toContain('id="quantity-summary"')
        ->not->toContain('id="quality-summary"')
        ->toContain('id="support-summary"')
        ->toContain('>1.00</span>')
        ->toContain('id="total-summary"');
});

test('score summary keeps a zero score visible when its category has criteria', function () {
    $html = view('partials.evaluator-score-summary', [
        'scoreSummary' => [
            'quantity' => 0.0,
            'quality' => 0.0,
            'support' => 0.0,
            'total' => 0.0,
            'has_quantity' => true,
            'has_quality' => false,
            'has_support' => false,
        ],
    ])->render();

    expect($html)
        ->toContain('id="quantity-summary"')
        ->toContain('>0.00</span>')
        ->not->toContain('id="quality-summary"')
        ->not->toContain('id="support-summary"')
        ->toContain('id="total-summary"');
});
```

- [ ] **Step 2: Run the view tests and verify RED**

Run:

```powershell
php artisan test tests/Feature/EvaluationScoreSummaryViewTest.php
```

Expected: FAIL because all three category rows are currently rendered unconditionally.

- [ ] **Step 3: Guard each category row in the shared partial**

Wrap the existing rows without changing their labels or score formatting:

```blade
@if ($scoreSummary['has_quantity'] ?? false)
    <div class="flex items-center justify-between">
        <span class="text-base">คะแนนด้านปริมาณ (Quantity)</span>
        <span id="quantity-summary" class="font-semibold text-blue-900">{{ number_format($scoreSummary['quantity'] ?? 0, 2) }}</span>
    </div>
@endif

@if ($scoreSummary['has_quality'] ?? false)
    <div class="flex items-center justify-between">
        <span class="text-base">คะแนนด้านคุณภาพ (Quality)</span>
        <span id="quality-summary" class="font-semibold text-blue-900">{{ number_format($scoreSummary['quality'] ?? 0, 2) }}</span>
    </div>
@endif

@if ($scoreSummary['has_support'] ?? false)
    <div class="flex items-center justify-between">
        <span class="text-base">คะแนนสายสนับสนุน</span>
        <span id="support-summary" class="font-semibold text-blue-900">{{ number_format($scoreSummary['support'] ?? 0, 2) }}</span>
    </div>
@endif
```

Keep the `#total-summary` block outside these conditions.

- [ ] **Step 4: Run the view and score-summary tests and verify GREEN**

Run:

```powershell
php artisan test tests/Feature/EvaluationScoreSummaryViewTest.php tests/Feature/ScoreServiceTest.php --filter="score summary|evaluation summary"
```

Expected: all selected tests PASS.

- [ ] **Step 5: Commit the shared view behavior**

```powershell
git add resources/views/partials/evaluator-score-summary.blade.php tests/Feature/EvaluationScoreSummaryViewTest.php
git commit -m "feat: hide unused score summary categories"
```

---

### Task 3: Reuse the shared summary on approval and director screens

**Files:**
- Modify: `tests/Feature/EvaluationScoreSummaryViewTest.php`
- Modify: `resources/views/components/unified-director.blade.php`

**Interfaces:**
- Consumes: `$categoryItems` component prop and `EvaluationScoreSummary::fromCategoryItems(array): array`
- Produces: the same `partials.evaluator-score-summary` markup used by evaluatee and evaluator screens

- [ ] **Step 1: Write a failing director integration contract test**

Append:

```php
test('director component delegates score summary rendering to the shared read model and partial', function () {
    $source = file_get_contents(resource_path('views/components/unified-director.blade.php'));

    expect($source)
        ->toContain('EvaluationScoreSummary::fromCategoryItems($categoryItems)')
        ->toContain("@include('partials.evaluator-score-summary', ['scoreSummary' => \$scoreSummary])")
        ->not->toContain('$totalQuantityScore = 0')
        ->not->toContain('<span id="quantity-summary"');
});
```

- [ ] **Step 2: Run the director contract test and verify RED**

Run:

```powershell
php artisan test tests/Feature/EvaluationScoreSummaryViewTest.php --filter="director component"
```

Expected: FAIL because the director component still calculates and renders all score rows inline.

- [ ] **Step 3: Replace the duplicated director summary with the shared read model and partial**

Replace the inline summary calculation and card near the end of `unified-director.blade.php` with:

```blade
@php
    $scoreSummary = \App\Support\EvaluationScoreSummary::fromCategoryItems($categoryItems);
@endphp

@include('partials.evaluator-score-summary', ['scoreSummary' => $scoreSummary])
```

Leave the component style and script includes after it unchanged. Existing JavaScript already uses null-safe element lookup for category rows that are not rendered.

- [ ] **Step 4: Run all focused tests and verify GREEN**

Run:

```powershell
php artisan test tests/Feature/EvaluationScoreSummaryViewTest.php tests/Feature/ScoreServiceTest.php tests/Feature/SupportCriteriaEvaluationViewTest.php
```

Expected: all tests PASS with no warnings or errors.

- [ ] **Step 5: Commit the director integration**

```powershell
git add resources/views/components/unified-director.blade.php tests/Feature/EvaluationScoreSummaryViewTest.php
git commit -m "refactor: share criteria-aware score summary"
```

---

### Task 4: Final verification

**Files:**
- Verify only; no planned production changes

**Interfaces:**
- Consumes: completed Tasks 1–3
- Produces: evidence that the implementation meets the approved spec without formatting errors

- [ ] **Step 1: Run the complete PHP test suite**

```powershell
php artisan test
```

Expected: all tests PASS.

- [ ] **Step 2: Check formatting and unintended whitespace errors**

```powershell
git diff --check HEAD~3..HEAD
```

Expected: no output and exit code 0.

- [ ] **Step 3: Inspect the final scoped diff**

```powershell
git diff HEAD~3..HEAD -- app/Support/EvaluationScoreSummary.php resources/views/partials/evaluator-score-summary.blade.php resources/views/components/unified-director.blade.php tests/Feature/ScoreServiceTest.php tests/Feature/EvaluationScoreSummaryViewTest.php
```

Expected: only criterion-presence metadata, conditional rows, shared director rendering, and their tests.
