# Remove Duplicate Support Activity Button Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Remove the duplicate support activity management button so each support criterion has one editable entry point through the existing management column.

**Architecture:** Keep the existing shared support criterion Modal as the only editor for activity entries, scores, and evidence. Remove the duplicate Blade control and its JavaScript activity-focus path while preserving the management and evidence entry points.

**Tech Stack:** Laravel 11, Blade components, vanilla JavaScript, Pest, Vite

## Global Constraints

- Work directly on the existing `feat/support` branch; do not create a worktree.
- Keep `data-support-manage-open` as the editable entry point for each support criterion.
- Do not change payloads, validation, persistence, permissions, scoring, evidence, comments, reasons, history, draft saving, or submission flow.
- Preserve unrelated working-tree changes and stage only files listed in this plan.

---

### Task 1: Remove the duplicate activity entry point

**Files:**
- Modify: `tests/Feature/SupportCriteriaEvaluationViewTest.php:127-145`
- Modify: `resources/views/components/support-activity-display.blade.php:1-42`
- Modify: `resources/views/components/support-criteria-table.blade.php:47-49,101-103`
- Modify: `resources/views/components/support-criteria-table-script.blade.php:489-520`

**Interfaces:**
- Consumes: existing `data-support-manage-open="{support_criteria_id}"` controls and `openSupportModal(criterionId, section)` JavaScript function.
- Produces: one management entry point per editable support criterion; no `data-support-activity-open` markup or event handler remains.

- [ ] **Step 1: Write the failing regression test**

In `tests/Feature/SupportCriteriaEvaluationViewTest.php`, extend `evaluatee can add edit and delete optional support activity entries` with assertions that the management control remains and the duplicate selector is absent:

```php
expect($html)
    ->toContain('data-support-manage-open="7"')
    ->not->toContain('data-support-activity-open')
    ->toContain('data-add-support-activity="7"')
    ->toContain('data-remove-support-activity');
```

- [ ] **Step 2: Run the focused test and verify RED**

Run:

```powershell
vendor\bin\pest.bat tests/Feature/SupportCriteriaEvaluationViewTest.php --filter="evaluatee can add edit and delete optional support activity entries"
```

Expected: FAIL because the rendered component and shared script still contain `data-support-activity-open`.

- [ ] **Step 3: Remove the duplicate control and dead JavaScript path**

Change `resources/views/components/support-activity-display.blade.php` to accept only the item, render the Admin activity heading and saved activity list, and omit the editable button:

```blade
@props([
    'item',
])

@php
    $criterionId = $item['id'];
    $entries = $item['activity_entries'] ?? [];
@endphp
```

Delete the complete block that renders the button carrying `data-support-activity-open="{{ $criterionId }}"`.

Update both desktop and mobile calls in `resources/views/components/support-criteria-table.blade.php` so the component has no obsolete editing props:

```blade
<x-support-activity-display :item="$item" />
```

In `resources/views/components/support-criteria-table-script.blade.php`, delete the duplicate click branch:

```javascript
const activityButton = event.target.closest('[data-support-activity-open]');
if (activityButton) {
    openSupportModal(activityButton.dataset.supportActivityOpen, 'activity');
    return;
}
```

Then remove the unreachable activity-focus branch so Modal focus is selected only for evidence or the normal management form:

```javascript
const target = section === 'evidence'
    ? item.querySelector('[data-support-evidence-input], [data-support-evidence-section] a, [data-add-support-evidence]')
    : item.querySelector('[data-support-score], [data-support-evidence-section] a');
```

- [ ] **Step 4: Run focused and build verification**

Run:

```powershell
vendor\bin\pest.bat tests/Feature/SupportCriteriaEvaluationViewTest.php
npm.cmd run test:js
npm.cmd run build
php artisan view:clear
php artisan view:cache
git diff --check
```

Expected: all Pest and JavaScript tests pass, Vite completes successfully, Blade templates cache successfully, and `git diff --check` returns no output.

- [ ] **Step 5: Commit the implementation**

```powershell
git add -- tests/Feature/SupportCriteriaEvaluationViewTest.php resources/views/components/support-activity-display.blade.php resources/views/components/support-criteria-table.blade.php resources/views/components/support-criteria-table-script.blade.php
git diff --cached --check
git commit -m "fix: remove duplicate support activity button"
```
