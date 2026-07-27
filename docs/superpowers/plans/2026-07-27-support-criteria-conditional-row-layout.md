# Support Criteria Conditional Row Layout Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Render support-criteria subrows only for values owned by each activity entry, while keeping admin-defined and criterion-level values in one shared cell.

**Architecture:** Keep the read model, request payloads, services, and score formulas unchanged. Make the Blade component select shared or per-entry cells from `allow_evaluatee_indicator` and `allow_evaluatee_weight`, reuse the existing evidence-summary component for one grouped evidence cell, and narrow the JavaScript row synchronizer to fields that actually remain inside entry rows.

**Tech Stack:** Laravel 11, Blade, Pest/PHPUnit feature tests, Tailwind CSS, vanilla JavaScript.

## Global Constraints

- The admin-created `activity_name` is a bold black row-group heading with no badge, icon, or “หัวข้อที่แอดมินกำหนด” label.
- `allow_evaluatee_indicator` alone splits activity and indicator cells only.
- `allow_evaluatee_weight` additionally splits weight and achieved-score cells by activity entry.
- Sequence, target, total weighted score, history, grouped evidence, and manage action remain shared across the row group.
- Existing score formulas, permissions, request payloads, database schema, and APIs must not change.
- Activity evidence must remain complete and attributable to its activity.
- Desktop and mobile views must preserve accessible names and keyboard behavior.

---

## File Map

- Modify `tests/Feature/SupportCriteriaEvaluationViewTest.php`: add fixtures and assertions for indicator-only, evaluatee-weighted, evidence, mobile, and heading behavior.
- Modify `resources/views/components/support-criteria-table.blade.php`: render conditional desktop cells and the approved row-group hierarchy.
- Modify `resources/views/components/support-activity-display.blade.php`: make the admin heading and activity-entry hierarchy consistent in the reusable/mobile presentation.
- Modify `resources/views/components/support-criteria-table-script.blade.php`: stop treating grouped evidence as a per-row cell and update only per-entry fields enabled for that criterion.

### Task 1: Conditional desktop row cells

**Files:**
- Modify: `tests/Feature/SupportCriteriaEvaluationViewTest.php`
- Modify: `resources/views/components/support-criteria-table.blade.php`

**Interfaces:**
- Consumes: item keys `allow_activity_entries`, `allow_evaluatee_indicator`, `allow_evaluatee_weight`, `activity_entries`, `weight`, `achieved_score`, and `weighted_score`.
- Produces: `data-support-entry-row`, `data-support-entry-activity-cell`, `data-support-entry-indicator-cell`, `data-support-entry-weight-list`, `data-support-entry-score-list`, and shared cells marked with `data-support-shared-cell`.

- [ ] **Step 1: Add an indicator-only fixture and failing desktop test**

Add this fixture next to `supportEvaluateeWeightedViewItem()`:

```php
function supportEvaluateeIndicatorOnlyViewItem(): array
{
    return array_replace(supportActivityViewItem(), [
        'activity_name' => '<p>หัวข้อจากแอดมิน</p>',
        'indicator' => null,
        'allow_evaluatee_indicator' => true,
        'allow_evaluatee_weight' => false,
        'weight' => '20.00',
        'achieved_score' => '80.00',
        'weighted_score' => '16.00',
        'activity_entries' => [
            [
                'id' => 41,
                'sequence' => 1,
                'content' => '<p>กิจกรรมหนึ่ง</p>',
                'indicator' => '<p>ตัวชี้วัดหนึ่ง</p>',
                'weight' => null,
                'achieved_score' => null,
                'weighted_score' => null,
                'evidence_links' => ['https://example.com/one'],
                'histories' => [],
            ],
            [
                'id' => 42,
                'sequence' => 2,
                'content' => '<p>กิจกรรมสอง</p>',
                'indicator' => '<p>ตัวชี้วัดสอง</p>',
                'weight' => null,
                'achieved_score' => null,
                'weighted_score' => null,
                'evidence_links' => ['https://example.com/two'],
                'histories' => [],
            ],
        ],
    ]);
}
```

Add the test:

```php
test('indicator-only entry rows keep criterion scores in shared cells', function () {
    $html = view('components.support-criteria-table', [
        'items' => [supportEvaluateeIndicatorOnlyViewItem()],
        'readonly' => false,
        'evidenceEditable' => true,
        'requireReason' => false,
    ])->render();

    preg_match('/<table class="hidden.*?<\/table>/su', $html, $desktopTable);
    $desktop = $desktopTable[0];

    expect(substr_count($desktop, 'data-support-entry-row="7"'))->toBe(2)
        ->and(substr_count($desktop, 'data-support-entry-activity-cell'))->toBe(2)
        ->and(substr_count($desktop, 'data-support-entry-indicator-cell'))->toBe(2)
        ->and(substr_count($desktop, 'data-support-entry-weight-list="7"'))->toBe(0)
        ->and(substr_count($desktop, 'data-support-entry-score-list="7"'))->toBe(0)
        ->and(substr_count($desktop, 'data-support-shared-weight="7"'))->toBe(1)
        ->and(substr_count($desktop, 'data-support-shared-score="7"'))->toBe(1)
        ->and($desktop)->toContain('rowspan="2"')
        ->and($desktop)->toContain('>20.00<')
        ->and($desktop)->toContain('>80.00<');
});
```

- [ ] **Step 2: Run the test and verify the current view fails**

Run:

```bash
php artisan test tests/Feature/SupportCriteriaEvaluationViewTest.php --filter="indicator-only entry rows"
```

Expected: FAIL because the current aligned-row branch emits two per-entry weight and score cells containing `-`.

- [ ] **Step 3: Add a failing test for evaluatee-owned weight and score**

Extend the existing `desktop support table renders evaluatee owned values as aligned entry rows` test:

```php
expect(substr_count($desktopTable[0], 'data-support-entry-weight-list="7"'))->toBe(2)
    ->and(substr_count($desktopTable[0], 'data-support-entry-score-list="7"'))->toBe(2)
    ->and(substr_count($desktopTable[0], 'data-support-shared-weight="7"'))->toBe(0)
    ->and(substr_count($desktopTable[0], 'data-support-shared-score="7"'))->toBe(0)
    ->and($desktopTable[0])->toContain('>40.00<')
    ->and($desktopTable[0])->toContain('>50.00<')
    ->and($desktopTable[0])->toContain('>80.00<')
    ->and($desktopTable[0])->toContain('>20.00<');
```

- [ ] **Step 4: Run both desktop tests and confirm the indicator-only case still fails**

Run:

```bash
php artisan test tests/Feature/SupportCriteriaEvaluationViewTest.php --filter="entry rows"
```

Expected: the evaluatee-weighted assertions pass against existing behavior; the indicator-only shared-cell assertions fail.

- [ ] **Step 5: Implement conditional desktop cells**

In the per-item `@php` block, define explicit presentation flags:

```php
$splitsIndicatorByEntry = !empty($item['allow_evaluatee_indicator']);
$splitsWeightByEntry = !empty($item['allow_evaluatee_weight']);
$alignedEntryRows = !empty($item['allow_activity_entries'])
    && ($splitsIndicatorByEntry || $splitsWeightByEntry)
        ? array_values($item['activity_entries'] ?? [])
        : [];
$alignedRowCount = max(count($alignedEntryRows), 1);
```

Keep the existing full-width `<th scope="rowgroup">` heading and black `text-slate-900` content. Do not add a heading caption or icon.

Inside the entry loop:

1. Render the activity cell for every entry.
2. Render the indicator cell for every entry only when `$splitsIndicatorByEntry`; otherwise render one `rowspan` cell from `$item['indicator']`.
3. Render weight and achieved score for every entry only when `$splitsWeightByEntry`.
4. When `$splitsWeightByEntry` is false, render these shared cells only on the first entry:

```blade
<td rowspan="{{ $alignedRowCount }}"
    data-support-shared-weight="{{ $item['id'] }}"
    class="px-2 py-4 text-right align-middle tabular-nums">
    {{ filled($item['weight']) ? $item['weight'] : '-' }}
</td>
<td rowspan="{{ $alignedRowCount }}"
    data-support-shared-score="{{ $item['id'] }}"
    data-support-achieved-display="{{ $item['id'] }}"
    class="px-2 py-4 text-right align-middle font-semibold tabular-nums">
    {{ filled($item['achieved_score']) ? $item['achieved_score'] : '-' }}
</td>
```

Add `data-support-shared-cell` to sequence, target, total weighted score, history, evidence, and manage cells, and keep `align-middle`. Restrict the row-divider class to per-entry cells instead of applying `border-t` to the entire `<tr>`.

- [ ] **Step 6: Run desktop view tests**

Run:

```bash
php artisan test tests/Feature/SupportCriteriaEvaluationViewTest.php --filter="entry rows"
```

Expected: PASS.

- [ ] **Step 7: Commit the conditional desktop rendering**

```bash
git add tests/Feature/SupportCriteriaEvaluationViewTest.php resources/views/components/support-criteria-table.blade.php
git commit -m "feat: render support values by entry ownership"
```

### Task 2: One grouped evidence cell and safe JavaScript synchronization

**Files:**
- Modify: `tests/Feature/SupportCriteriaEvaluationViewTest.php`
- Modify: `resources/views/components/support-criteria-table.blade.php`
- Modify: `resources/views/components/support-criteria-table-script.blade.php`

**Interfaces:**
- Consumes: `<x-support-evidence-summary :item="$item" />`, `data-support-evidence-list="<criterion id>"`, and editor entries under `data-support-activity-entry`.
- Produces: one desktop evidence cell per criterion, grouped links per activity, and `syncDesktopEntryRows(item)` that updates activity/indicator/weight/score without owning evidence rendering.

- [ ] **Step 1: Write the failing grouped-evidence test**

Add:

```php
test('aligned desktop rows use one grouped evidence cell without losing activity links', function () {
    $html = view('components.support-criteria-table', [
        'items' => [supportEvaluateeIndicatorOnlyViewItem()],
        'readonly' => false,
        'evidenceEditable' => true,
        'requireReason' => false,
    ])->render();

    preg_match('/<table class="hidden.*?<\/table>/su', $html, $desktopTable);
    $desktop = $desktopTable[0];

    expect(substr_count($desktop, 'data-support-shared-evidence="7"'))->toBe(1)
        ->and(substr_count($desktop, 'data-support-evidence-list="7"'))->toBe(1)
        ->and($desktop)->toContain('rowspan="2"')
        ->and($desktop)->toContain('href="https://example.com/one"')
        ->and($desktop)->toContain('href="https://example.com/two"')
        ->and($desktop)->toContain('รายการ 1')
        ->and($desktop)->toContain('รายการ 2');
});
```

- [ ] **Step 2: Run the evidence test and verify it fails**

Run:

```bash
php artisan test tests/Feature/SupportCriteriaEvaluationViewTest.php --filter="one grouped evidence cell"
```

Expected: FAIL because the current desktop branch creates one evidence `<td>` per entry.

- [ ] **Step 3: Render the reusable evidence summary in one row-spanned cell**

Replace the per-entry desktop evidence cell with a first-entry-only shared cell:

```blade
@if ($entryIndex === 0)
    <td rowspan="{{ $alignedRowCount }}"
        data-support-shared-cell
        data-support-shared-evidence="{{ $item['id'] }}"
        class="px-2 py-4 align-middle">
        <x-support-evidence-summary :item="$item" />
    </td>
@endif
```

This component already groups activity links as “รายการ 1”, “รายการ 2”, preserves each `data-support-activity-evidence-list`, and emits accessible link names.

- [ ] **Step 4: Add a source-level regression test for JavaScript ownership**

Add:

```php
test('desktop row sync leaves grouped evidence to the shared evidence renderer', function () {
    $script = file_get_contents(resource_path('views/components/support-criteria-table-script.blade.php'));
    preg_match('/const syncDesktopEntryRows = .*?^        };$/ms', $script, $function);

    expect($function[0])
        ->toContain("row.querySelector('[data-support-entry-activity-value]')")
        ->toContain("row.querySelector('[data-support-entry-indicator-list]')")
        ->toContain("row.querySelector('[data-support-entry-weight-list]')")
        ->toContain("row.querySelector('[data-support-entry-score-list]')")
        ->not->toContain('data-support-entry-evidence-list');

    expect($script)
        ->toContain('document.querySelectorAll(`[data-support-evidence-list="${id}"]`)')
        ->toContain('activityEvidenceGroups.forEach((group) =>');
});
```

- [ ] **Step 5: Run the JavaScript ownership test and verify it fails**

Run:

```bash
php artisan test tests/Feature/SupportCriteriaEvaluationViewTest.php --filter="row sync leaves grouped evidence"
```

Expected: FAIL because `syncDesktopEntryRows()` currently rewrites an evidence container inside each row.

- [ ] **Step 6: Remove per-row evidence mutation from `syncDesktopEntryRows`**

Delete the block beginning with:

```js
const evidenceDisplay = row.querySelector('[data-support-entry-evidence-list]');
```

and ending after the `evidenceLinks.forEach` loop that appends anchors. Keep activity, indicator, weight, and score updates null-safe:

```js
if (activityDisplay) activityDisplay.textContent = activityTools().activityHtmlPlainText(activity) || '-';
if (indicatorDisplay) indicatorDisplay.textContent = activityTools().activityHtmlPlainText(indicator) || '-';
if (weightDisplay) weightDisplay.textContent = normalizeScore(weight) || '-';
if (scoreDisplay) scoreDisplay.textContent = normalizeScore(achievedScore) || '-';
```

The existing `document.querySelectorAll([data-support-evidence-list])` block remains the only renderer for grouped evidence and continues to run before `syncDesktopEntryRows(item)`.

- [ ] **Step 7: Run evidence and JavaScript tests**

Run:

```bash
php artisan test tests/Feature/SupportCriteriaEvaluationViewTest.php --filter="evidence|row sync"
```

Expected: PASS.

- [ ] **Step 8: Commit grouped evidence synchronization**

```bash
git add tests/Feature/SupportCriteriaEvaluationViewTest.php resources/views/components/support-criteria-table.blade.php resources/views/components/support-criteria-table-script.blade.php
git commit -m "fix: keep support evidence grouped by criterion"
```

### Task 3: Responsive hierarchy and full regression verification

**Files:**
- Modify: `tests/Feature/SupportCriteriaEvaluationViewTest.php`
- Modify: `resources/views/components/support-activity-display.blade.php`
- Modify: `resources/views/components/support-criteria-table.blade.php`

**Interfaces:**
- Consumes: `support-activity-display` item prop and the same conditional flags used by the desktop table.
- Produces: a black admin heading, visually subordinate numbered activities, criterion-level summary values once, and per-entry weight/score lists only when enabled.

- [ ] **Step 1: Write the failing hierarchy and label-removal test**

Add:

```php
test('admin heading is black and has no explanatory badge', function () {
    $html = view('components.support-criteria-table', [
        'items' => [supportEvaluateeIndicatorOnlyViewItem()],
        'readonly' => false,
        'evidenceEditable' => true,
        'requireReason' => false,
    ])->render();

    expect($html)
        ->toContain('data-support-criterion-heading="7"')
        ->toContain('data-support-admin-heading')
        ->toContain('font-semibold text-slate-900')
        ->not->toContain('หัวข้อที่แอดมินกำหนด');
});
```

Add a mobile assertion:

```php
test('mobile criterion card shows shared score once unless evaluatee weight is enabled', function () {
    $indicatorOnly = view('components.support-criteria-table', [
        'items' => [supportEvaluateeIndicatorOnlyViewItem()],
        'readonly' => false,
        'evidenceEditable' => true,
        'requireReason' => false,
    ])->render();
    preg_match('/<div class="space-y-3 p-4 lg:hidden">.*?<div class="hidden" data-support-editor-store/su', $indicatorOnly, $mobile);

    expect(substr_count($mobile[0], 'data-support-entry-weight-list="7"'))->toBe(0)
        ->and(substr_count($mobile[0], 'data-support-entry-score-list="7"'))->toBe(0)
        ->and($mobile[0])->toContain('20.00')
        ->and($mobile[0])->toContain('80.00');

    $weighted = view('components.support-criteria-table', [
        'items' => [supportEvaluateeWeightedViewItem()],
        'readonly' => false,
        'evidenceEditable' => true,
        'requireReason' => false,
    ])->render();
    preg_match('/<div class="space-y-3 p-4 lg:hidden">.*?<div class="hidden" data-support-editor-store/su', $weighted, $weightedMobile);

    expect(substr_count($weightedMobile[0], 'data-support-entry-weight-list="7"'))->toBe(1)
        ->and(substr_count($weightedMobile[0], 'data-support-entry-score-list="7"'))->toBe(1);
});
```

- [ ] **Step 2: Run the hierarchy and mobile tests**

Run:

```bash
php artisan test tests/Feature/SupportCriteriaEvaluationViewTest.php --filter="admin heading|mobile criterion"
```

Expected: FAIL because the approved admin-heading hook and responsive hierarchy have not been added yet.

- [ ] **Step 3: Apply the approved responsive visual hierarchy**

In `support-activity-display.blade.php`:

- Add `data-support-admin-heading` and keep the admin `activity_name` in `font-semibold text-slate-900`.
- Render ungrouped activity entries with an explicit circular index and amber text rather than browser list markers:

```blade
<div class="space-y-3">
    @foreach ($entries as $entryIndex => $entry)
        <div class="flex gap-2">
            <span class="inline-flex size-5 shrink-0 items-center justify-center rounded-full bg-amber-100 text-xs font-semibold text-amber-800">
                {{ $entryIndex + 1 }}
            </span>
            <div class="support-criteria-rich-text min-w-0 break-words text-sm font-normal text-amber-800">
                {!! \App\Support\SafeHtml::richText($entry['content'] ?? '') !!}
            </div>
        </div>
    @endforeach
</div>
```

In the mobile card, keep the existing conditional branches:

```blade
@if (!empty($item['allow_evaluatee_weight']))
    <ol class="space-y-2" data-support-entry-weight-list="{{ $item['id'] }}">
        @forelse ($item['activity_entries'] ?? [] as $entry)
            <li>{{ filled($entry['weight'] ?? null) ? $entry['weight'] : '-' }}</li>
        @empty
            <li class="text-slate-400">-</li>
        @endforelse
    </ol>
@else
    {{ $item['weight'] }}
@endif
```

Keep the achieved-score branch explicit:

```blade
@if (!empty($item['allow_evaluatee_weight']))
    <ol class="space-y-2" data-support-entry-score-list="{{ $item['id'] }}">
        @forelse ($item['activity_entries'] ?? [] as $entry)
            <li>{{ filled($entry['achieved_score'] ?? null) ? $entry['achieved_score'] : '-' }}</li>
        @empty
            <li class="text-slate-400">-</li>
        @endforelse
    </ol>
@else
    {{ filled($item['achieved_score']) ? $item['achieved_score'] : '-' }}
@endif
```

Do not introduce per-entry lists when `allow_evaluatee_weight` is false.

- [ ] **Step 4: Run the complete component test file**

Run:

```bash
php artisan test tests/Feature/SupportCriteriaEvaluationViewTest.php
```

Expected: PASS with no warnings or errors.

- [ ] **Step 5: Run related read-model and score tests**

Run:

```bash
php artisan test tests/Feature/Evaluation/SupportCriteriaReadModelTest.php tests/Feature/Evaluation/SupportScoreServiceTest.php tests/Feature/Evaluation/SupportActivityEntryServiceTest.php
```

Expected: PASS, confirming that the presentation change did not alter score persistence or calculations.

- [ ] **Step 6: Build frontend assets**

Run:

```bash
npm run build
```

Expected: exit code 0 with no Blade/Vite asset errors.

- [ ] **Step 7: Commit responsive hierarchy changes**

```bash
git add tests/Feature/SupportCriteriaEvaluationViewTest.php resources/views/components/support-activity-display.blade.php resources/views/components/support-criteria-table.blade.php
git commit -m "style: clarify support criterion row hierarchy"
```

- [ ] **Step 8: Perform final verification**

Run:

```bash
php artisan test tests/Feature/SupportCriteriaEvaluationViewTest.php tests/Feature/Evaluation/SupportCriteriaReadModelTest.php tests/Feature/Evaluation/SupportScoreServiceTest.php tests/Feature/Evaluation/SupportActivityEntryServiceTest.php
npm run build
git status --short
```

Expected: all selected tests pass, the build exits successfully, and `git status --short` contains only pre-existing unrelated user changes.
