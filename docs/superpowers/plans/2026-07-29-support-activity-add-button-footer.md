# Support Activity Add Button Footer Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Move the ungrouped `+ เพิ่มกิจกรรม/โครงการ` control below the final activity card and its evidence fields without changing permissions or behavior.

**Architecture:** Keep the existing Blade component and JavaScript contract. Change only the ungrouped modal markup so the existing button is rendered after `data-support-activity-container`; cover its document order with a focused Pest regression test.

**Tech Stack:** Laravel 11, Blade, Pest 3, Tailwind CSS 4, Vite 6

## Global Constraints

- Keep the button text, colors, size, hover state, focus state, and `data-add-support-activity` attribute unchanged.
- Show the button only when `$canEditActivities && $activityEntryRole === 'evaluatee'`.
- Do not change payloads, validation, saving, deletion, or JavaScript behavior.
- Do not change the grouped `+ เพิ่มโครงการในข้อ ...` buttons.

---

### Task 1: Render the Ungrouped Add Button After the Activity List

**Files:**
- Modify: `tests/Feature/SupportCriteriaEvaluationViewTest.php:309-332`
- Modify: `resources/views/components/support-criteria-table.blade.php:687-715`

**Interfaces:**
- Consumes: Blade variables `$canEditActivities`, `$activityEntryRole`, `$item['id']`, and the existing JavaScript selector `[data-add-support-activity]`.
- Produces: The same `button[data-add-support-activity="<criterion id>"]`, positioned after `div[data-support-activity-container]` in the rendered ungrouped activity section.

- [ ] **Step 1: Write the failing document-order regression test**

Extend `evaluatee can add edit and delete optional support activity entries` immediately after rendering `$html`:

```php
$activityContainerPosition = strpos($html, 'data-support-activity-container');
$activityEvidencePosition = strpos($html, 'value="https://example.com/activity-proof"');
$addActivityButtonPosition = strpos($html, 'data-add-support-activity="7"');

expect($activityContainerPosition)->not->toBeFalse()
    ->and($activityEvidencePosition)->not->toBeFalse()
    ->and($addActivityButtonPosition)->not->toBeFalse()
    ->and($addActivityButtonPosition)->toBeGreaterThan($activityContainerPosition)
    ->and($addActivityButtonPosition)->toBeGreaterThan($activityEvidencePosition);
```

This asserts real rendered order. The current markup must fail because the add button precedes both the activity container and its evidence input.

- [ ] **Step 2: Run the focused test and verify RED**

Run:

```powershell
php artisan test tests/Feature/SupportCriteriaEvaluationViewTest.php --filter="evaluatee can add edit and delete optional support activity entries"
```

Expected: FAIL on `toBeGreaterThan`, showing the current add-button position is smaller than the container or evidence position.

- [ ] **Step 3: Move the existing Blade button after the container**

In the ungrouped `@else` branch, simplify the heading to:

```blade
<div class="mb-3">
    <h5 class="font-semibold text-slate-800">กิจกรรม/โครงการเพิ่มเติม</h5>
    <p class="mt-1 text-xs text-slate-500">รายการนี้เป็นข้อมูลเพิ่มเติมจากหัวข้อที่ Admin กำหนด</p>
</div>
```

Remove the button from this heading block. Immediately after the closing tag of `div[data-support-activity-container]`, render the unchanged button inside a spacing wrapper:

```blade
@if ($canEditActivities && $activityEntryRole === 'evaluatee')
    <div class="mt-4">
        <button type="button" data-add-support-activity="{{ $item['id'] }}"
            class="rounded-lg bg-amber-100 px-3 py-2 text-sm font-semibold text-amber-900 transition hover:bg-amber-200 focus:outline-none focus:ring-2 focus:ring-amber-400">
            + เพิ่มกิจกรรม/โครงการ
        </button>
    </div>
@endif
```

Do not edit the grouped-indicator branch or `support-criteria-table-script.blade.php`.

- [ ] **Step 4: Run the focused test and verify GREEN**

Run:

```powershell
php artisan test tests/Feature/SupportCriteriaEvaluationViewTest.php --filter="evaluatee can add edit and delete optional support activity entries"
```

Expected: PASS with one test and no failures.

- [ ] **Step 5: Run the complete component regression test**

Run:

```powershell
php artisan test tests/Feature/SupportCriteriaEvaluationViewTest.php
```

Expected: All tests in the file pass, including grouped-project and read-only permission coverage.

- [ ] **Step 6: Verify Blade compilation and frontend build**

Run:

```powershell
php artisan view:cache
npm run build
git diff --check
```

Expected: Each command exits with code `0`; Vite reports a successful production build; `git diff --check` prints no whitespace errors.

- [ ] **Step 7: Review the scoped diff and commit**

Run:

```powershell
git diff -- tests/Feature/SupportCriteriaEvaluationViewTest.php resources/views/components/support-criteria-table.blade.php
git add -- tests/Feature/SupportCriteriaEvaluationViewTest.php resources/views/components/support-criteria-table.blade.php
git commit -m "fix: move support activity add button below entries"
```

Expected: The diff contains only the regression assertion and the ungrouped button relocation; the commit excludes unrelated existing workspace changes.
