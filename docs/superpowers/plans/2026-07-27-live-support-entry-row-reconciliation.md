# Live Support Entry Row Reconciliation Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** ทำให้ตาราง desktop สร้างและลบแถวกิจกรรมของผู้ถูกประเมินทันทีหลังยืนยัน Modal โดยไม่ต้องบันทึกและรีเฟรชหน้า

**Architecture:** Blade จะ render base row shell สำหรับเกณฑ์ non-grouped เสมอและเตรียม `<template>` ของ additional row ไว้ ส่วน JavaScript จะ reconcile จำนวนแถวกับกิจกรรมใน Modal ปรับ `rowspan` และเติมค่าของแต่ละแถว ฟังก์ชันคำนวณจำนวนแถวจะอยู่ในโมดูล `support-activity-entries.js` เพื่อทดสอบแบบ pure unit test ได้

**Tech Stack:** Laravel 11, Blade components, JavaScript ES modules, Tailwind CSS 4, Pest, Node test runner

## Global Constraints

- แก้เฉพาะ live preview ของตารางแสดงผลแบบ desktop
- ไม่เปลี่ยน Modal, payload, persistence หรือ Validation
- ไม่เปลี่ยน grouped-indicator layout หรือ mobile layout
- เส้นคั่นอยู่เฉพาะ entry cells และไม่ตัดผ่าน shared cells ที่ใช้ `rowspan`
- การยกเลิก Modal ต้องคืนจำนวนแถวและค่าเดิม
- การบันทึกและ reload ต้องให้ผลเหมือน live preview
- รักษาไฟล์งานอื่นที่ค้างอยู่ใน worktree และ stage เฉพาะไฟล์ของงานนี้

---

## File Map

- Modify: `resources/views/components/support-criteria-table.blade.php` — render base row shell และ additional-row template สำหรับ non-grouped criteria
- Modify: `resources/js/support-activity-entries.js` — pure row-count reconciler ที่เพิ่ม/ลด row collection
- Modify: `resources/views/components/support-criteria-table-script.blade.php` — clone/remove rows, update values, separators และ shared `rowspan`
- Modify: `tests/Feature/SupportCriteriaEvaluationViewTest.php` — Blade behavior ของ zero-entry shell, template และ existing rows
- Modify: `tests/js/support-activity-entries.test.mjs` — row reconciliation สำหรับ `0 → 2`, `2 → 1` และ `1 → 0`

---

### Task 1: Render a reusable empty desktop row shell

**Files:**
- Modify: `resources/views/components/support-criteria-table.blade.php:40-190`
- Test: `tests/Feature/SupportCriteriaEvaluationViewTest.php`

**Interfaces:**
- Consumes: item read-model keys `id`, `allow_evaluatee_indicator`, `allow_evaluatee_weight`, and `activity_entries`
- Produces: `[data-support-entry-row]`, `[data-support-entry-cell]`, `[data-support-entry-number]`, and `<template data-support-entry-row-template="{criterionId}">`

- [ ] **Step 1: Add a failing zero-entry view test**

Add beside `desktop support table renders evaluatee owned values as aligned entry rows`:

```php
test('empty non grouped support mode renders a live desktop row shell', function () {
    $item = supportEvaluateeWeightedViewItem();
    $item['activity_entries'] = [];

    $html = view('components.support-criteria-table', [
        'items' => [$item],
        'readonly' => false,
        'evidenceEditable' => true,
        'requireReason' => false,
        'activityEntryRole' => 'evaluatee',
    ])->render();

    preg_match('/<table class="hidden.*?<\/table>/su', $html, $desktopTable);
    $desktop = $desktopTable[0];

    expect(substr_count($desktop, 'data-support-entry-row="7"'))->toBe(1)
        ->and($desktop)->toContain('data-support-entry-empty')
        ->and($desktop)->toContain('data-support-entry-cell')
        ->and($desktop)->toContain('data-support-entry-number')
        ->and($html)->toContain('data-support-entry-row-template="7"')
        ->and($desktop)->toContain('rowspan="1"')
        ->and($desktop)->toContain('ยังไม่มีกิจกรรม/โครงการเพิ่มเติม');
});
```

Update existing row-count assertions so they count only inside `$desktop`, not the additional-row template outside the table.

- [ ] **Step 2: Run the view test to verify RED**

Run:

```powershell
php vendor/bin/pest tests/Feature/SupportCriteriaEvaluationViewTest.php --filter="empty non grouped support mode" --compact
```

Expected: FAIL because the empty non-grouped criterion currently falls through to the legacy summary row and has no row template.

- [ ] **Step 3: Add stable hooks to the existing entry cells**

Keep the existing cell order in `support-criteria-table.blade.php`. Add `data-support-entry-cell` to the activity cell and to per-entry indicator, weight and score cells. Add `data-support-entry-number` to the circular number:

```blade
<td data-support-entry-cell
    class="{{ $entryIndex > 0 ? 'border-t border-slate-100 ' : '' }}break-words px-2 py-4 align-top"
    data-support-entry-activity-cell>
    <div class="flex gap-2">
        <span data-support-entry-number
            class="inline-flex size-5 shrink-0 items-center justify-center rounded-full bg-amber-100 text-xs font-semibold text-amber-800">
            {{ $entryIndex + 1 }}
        </span>
```

Use the same `data-support-entry-cell` hook on the other per-entry `<td>` elements. Do not add it to target, weighted score, history, evidence, management, shared indicator, shared weight or shared score cells.

- [ ] **Step 4: Make the non-grouped branch render at least one row and expose a clone template**

In `support-criteria-table.blade.php`, derive an explicit layout flag:

```blade
$usesAlignedEntryRows = $usesActivityRowGroup
    && empty($item['group_activity_entries_by_indicator']);
$alignedEntryRows = $usesAlignedEntryRows
    ? array_values($item['activity_entries'] ?? [])
    : [];
$renderedAlignedEntryRows = $usesAlignedEntryRows
    ? ($alignedEntryRows !== [] ? $alignedEntryRows : [null])
    : [];
$alignedRowCount = max(count($alignedEntryRows), 1);
```

Replace `@if ($alignedEntryRows !== [])` with `@if ($usesAlignedEntryRows)` and iterate `$renderedAlignedEntryRows`.

Add `data-support-entry-empty` to the base `<tr>` when `$entry === null`. In the existing entry cells, render the empty state safely:

```blade
<span data-support-entry-number
    class="... {{ is_array($entry) ? '' : 'hidden' }}">
    {{ $entryIndex + 1 }}
</span>
<div data-support-entry-activity-value
    class="support-criteria-rich-text min-w-0 break-words font-semibold {{ is_array($entry) ? 'text-amber-800' : 'text-slate-400' }}">
    @if (is_array($entry))
        {!! \App\Support\SafeHtml::richText($entry['content'] ?? '') !!}
    @else
        ยังไม่มีกิจกรรม/โครงการเพิ่มเติม
    @endif
</div>
```

Use `is_array($entry)` before rendering entry indicator, weight and score values; render `-` for the empty shell. Keep all shared cells and their existing `data-support-shared-cell` attributes and `rowspan="{{ $alignedRowCount }}"`.

After the desktop table, loop through `$items` again and render the clone template only for editable non-grouped items. The template keeps the same entry-cell order as a subsequent server row and deliberately omits all shared cells:

```blade
@if (!$readonly)
    @foreach ($items as $item)
        @php
            $templateUsesAlignedRows = !empty($item['allow_activity_entries'])
                && empty($item['group_activity_entries_by_indicator']);
            $templateSplitsIndicator = !empty($item['allow_evaluatee_indicator']);
            $templateSplitsWeight = !empty($item['allow_evaluatee_weight']);
        @endphp
        @if ($templateUsesAlignedRows)
            <template data-support-entry-row-template="{{ $item['id'] }}">
                <table><tbody><tr>
                    <td data-support-entry-cell data-support-entry-activity-cell
                        class="break-words px-2 py-4 align-top">
                        <div class="flex gap-2">
                            <span data-support-entry-number
                                class="inline-flex size-5 shrink-0 items-center justify-center rounded-full bg-amber-100 text-xs font-semibold text-amber-800"></span>
                            <div data-support-entry-activity-value
                                class="support-criteria-rich-text min-w-0 break-words font-semibold text-amber-800"></div>
                        </div>
                    </td>
                    @if ($templateSplitsIndicator)
                        <td data-support-entry-cell data-support-entry-indicator-cell
                            class="break-words px-2 py-4 align-top leading-6">
                            <div data-support-entry-indicator-list="{{ $item['id'] }}"></div>
                        </td>
                    @endif
                    @if ($templateSplitsWeight)
                        <td data-support-entry-cell
                            data-support-entry-weight-list="{{ $item['id'] }}"
                            class="px-2 py-4 text-right align-top tabular-nums"></td>
                        <td data-support-entry-cell
                            data-support-entry-score-list="{{ $item['id'] }}"
                            class="px-2 py-4 text-right align-top font-semibold tabular-nums"></td>
                    @endif
                </tr></tbody></table>
            </template>
        @endif
    @endforeach
@endif
```

- [ ] **Step 5: Run view tests to verify GREEN**

Run:

```powershell
php vendor/bin/pest tests/Feature/SupportCriteriaEvaluationViewTest.php --compact
```

Expected: all tests PASS, including existing grouped, indicator-only, activity-only, evidence and mobile tests.

- [ ] **Step 6: Commit Task 1**

```powershell
git add -- resources/views/components/support-criteria-table.blade.php tests/Feature/SupportCriteriaEvaluationViewTest.php
git commit -m "feat: render live support entry row shells"
```

---

### Task 2: Reconcile live desktop rows after Modal changes

**Files:**
- Modify: `resources/js/support-activity-entries.js`
- Modify: `resources/views/components/support-criteria-table-script.blade.php:578-606`
- Test: `tests/js/support-activity-entries.test.mjs`
- Test: `tests/Feature/SupportCriteriaEvaluationViewTest.php`

**Interfaces:**
- Consumes: `reconcileActivityEntryRows(currentRows, entryCount, createRow)`
- Produces: `{ rows: Array, rowCount: number, hasEntries: boolean }` and a live DOM whose row count equals `max(entryCount, 1)`

- [ ] **Step 1: Add failing unit tests for row reconciliation**

Import `reconcileActivityEntryRows` and add:

```javascript
class FakeRow {
    constructor(name) {
        this.name = name;
        this.removed = false;
    }

    remove() {
        this.removed = true;
    }
}

test('reconciles live desktop rows when entries are added and removed', () => {
    const base = new FakeRow('base');
    const created = [];
    const expanded = reconcileActivityEntryRows([base], 2, (index) => {
        const row = new FakeRow(`row-${index}`);
        created.push(row);
        return row;
    });

    assert.equal(expanded.rows.length, 2);
    assert.equal(expanded.rowCount, 2);
    assert.equal(expanded.hasEntries, true);
    assert.deepEqual(expanded.rows.map((row) => row.name), ['base', 'row-1']);

    const reduced = reconcileActivityEntryRows(expanded.rows, 0, () => {
        throw new Error('must not create a row while reducing');
    });

    assert.equal(reduced.rows.length, 1);
    assert.equal(reduced.rowCount, 1);
    assert.equal(reduced.hasEntries, false);
    assert.equal(created[0].removed, true);
});
```

- [ ] **Step 2: Run the JavaScript test to verify RED**

Run:

```powershell
node --test tests/js/support-activity-entries.test.mjs
```

Expected: FAIL because `reconcileActivityEntryRows` is not exported.

- [ ] **Step 3: Implement the pure reconciler**

Add to `resources/js/support-activity-entries.js`:

```javascript
export function reconcileActivityEntryRows(currentRows, entryCount, createRow) {
    const rows = [...currentRows];
    const normalizedEntryCount = Math.max(Number(entryCount) || 0, 0);
    const rowCount = Math.max(normalizedEntryCount, 1);

    while (rows.length < rowCount) {
        rows.push(createRow(rows.length));
    }

    while (rows.length > rowCount) {
        rows.pop()?.remove();
    }

    return {
        rows,
        rowCount,
        hasEntries: normalizedEntryCount > 0,
    };
}
```

Expose it through `window.SupportActivityEntries` beside the existing helpers.

- [ ] **Step 4: Run the JavaScript test to verify GREEN**

Run:

```powershell
node --test tests/js/support-activity-entries.test.mjs
```

Expected: PASS.

- [ ] **Step 5: Wire the reconciler into `syncDesktopEntryRows`**

Replace the beginning of `syncDesktopEntryRows` with:

```javascript
const syncDesktopEntryRows = (item) => {
    const id = item.dataset.supportId;
    const entries = Array.from(item.querySelectorAll('[data-support-activity-entry]'));
    const template = Array.from(document.querySelectorAll('[data-support-entry-row-template]'))
        .find((candidate) => candidate.dataset.supportEntryRowTemplate === String(id));
    const currentRows = Array.from(document.querySelectorAll(`[data-support-entry-row="${id}"]`));
    if (currentRows.length === 0 || !template) return;

    const state = activityTools().reconcileActivityEntryRows(
        currentRows,
        entries.length,
        (index) => {
            const fragment = template.content.cloneNode(true);
            const row = fragment.querySelector('tr');
            if (!row) throw new Error('Missing support entry row template');
            row.dataset.supportEntryRow = String(id);
            row.dataset.supportEntryIndex = String(index);
            stateAnchor.before(row);
            return row;
        },
    );
```

Before calling the reconciler, define `stateAnchor` as the first table row after this criterion’s current entry rows. Add a marker after the server-rendered rows:

```blade
<tr class="hidden" data-support-entry-row-end="{{ $item['id'] }}">
    <td colspan="{{ $readonly ? 9 : 10 }}"></td>
</tr>
```

Then use:

```javascript
const stateAnchor = document.querySelector(`[data-support-entry-row-end="${id}"]`);
if (currentRows.length === 0 || !template || !stateAnchor) return;
```

For every row returned by the reconciler:

```javascript
state.rows.forEach((row, index) => {
    const entry = entries[index] || null;
    row.dataset.supportEntryIndex = String(index);
    row.toggleAttribute('data-support-entry-empty', !entry);

    row.querySelectorAll('[data-support-entry-cell]').forEach((cell) => {
        cell.classList.toggle('border-t', index > 0);
        cell.classList.toggle('border-slate-100', index > 0);
    });

    const number = row.querySelector('[data-support-entry-number]');
    if (number) {
        number.textContent = String(index + 1);
        number.classList.toggle('hidden', !entry);
    }

    const activityDisplay = row.querySelector('[data-support-entry-activity-value]');
    const indicatorDisplay = row.querySelector('[data-support-entry-indicator-list]');
    const weightDisplay = row.querySelector('[data-support-entry-weight-list]');
    const scoreDisplay = row.querySelector('[data-support-entry-score-list]');

    if (activityDisplay) {
        activityDisplay.textContent = entry
            ? activityTools().activityHtmlPlainText(
                entry.querySelector('[data-support-activity-content]')?.value ?? '',
            ) || '-'
            : 'ยังไม่มีกิจกรรม/โครงการเพิ่มเติม';
        activityDisplay.classList.toggle('text-slate-400', !entry);
        activityDisplay.classList.toggle('text-amber-800', Boolean(entry));
    }
    if (indicatorDisplay) {
        indicatorDisplay.textContent = entry
            ? activityTools().activityHtmlPlainText(
                entry.querySelector('[data-support-entry-indicator]')?.value ?? '',
            ) || '-'
            : '-';
    }
    if (weightDisplay) {
        weightDisplay.textContent = entry
            ? normalizeScore(entry.querySelector('[data-support-entry-weight]')?.value ?? '') || '-'
            : '-';
    }
    if (scoreDisplay) {
        scoreDisplay.textContent = entry
            ? normalizeScore(entry.querySelector('[data-support-entry-score]')?.value ?? '') || '-'
            : '-';
    }
});

state.rows[0].querySelectorAll('[data-support-shared-cell]').forEach((cell) => {
    cell.rowSpan = state.rowCount;
});
```

Keep `updateSupportRow` calling `syncDesktopEntryRows(item)` after activity, entry-value and evidence updates. The existing cancel flow already restores the snapshot before `updateSupportRow(item)`, so the same reconciler removes newly created rows on cancel.

- [ ] **Step 6: Add a view contract assertion for the live wiring**

In the zero-entry feature test, read the script and assert:

```php
$script = file_get_contents(resource_path('views/components/support-criteria-table-script.blade.php'));

expect($script)
    ->toContain('reconcileActivityEntryRows')
    ->toContain('data-support-entry-row-template')
    ->toContain('data-support-entry-row-end')
    ->toContain("cell.classList.toggle('border-t', index > 0)")
    ->toContain('cell.rowSpan = state.rowCount');
```

- [ ] **Step 7: Run focused tests**

Run:

```powershell
node --test tests/js/support-activity-entries.test.mjs
php vendor/bin/pest tests/Feature/SupportCriteriaEvaluationViewTest.php --compact
```

Expected: all tests PASS.

- [ ] **Step 8: Commit Task 2**

```powershell
git add -- resources/js/support-activity-entries.js resources/views/components/support-criteria-table-script.blade.php resources/views/components/support-criteria-table.blade.php tests/js/support-activity-entries.test.mjs tests/Feature/SupportCriteriaEvaluationViewTest.php
git commit -m "fix: reconcile live support project rows"
```

---

### Task 3: Regression verification

**Files:**
- Verify only; no production changes expected

**Interfaces:**
- Consumes: completed Blade row shell and JavaScript reconciler
- Produces: evidence that the feature and existing support evaluation flows remain green

- [ ] **Step 1: Run support-focused PHP tests**

```powershell
php vendor/bin/pest tests/Feature/SupportCriteriaEvaluationViewTest.php tests/Feature/Evaluation/SupportActivityEntryServiceTest.php --compact
```

Expected: PASS with zero failures.

- [ ] **Step 2: Run all JavaScript tests**

```powershell
npm run test:js
```

Expected: PASS with zero failures.

- [ ] **Step 3: Build production assets**

```powershell
npm run build
```

Expected: Vite exits `0`.

- [ ] **Step 4: Run the full PHP suite**

```powershell
composer test
```

Expected: PASS with zero failures.

- [ ] **Step 5: Check the final diff and branch**

```powershell
git diff --check
git branch --show-current
git status --short
git log -6 --oneline
```

Expected:

- `git diff --check` exits `0`
- branch is `Jui`
- only pre-existing unrelated working-tree files remain unstaged
- Task 1 and Task 2 commits are present
