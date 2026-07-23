# Support Evidence Inline Links Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Replace support evidence count buttons with the actual saved evidence URLs as safe, directly clickable links.

**Architecture:** The shared Blade table renders the same URL list in its desktop and mobile summaries. The existing client-side row refresh rebuilds those lists with anchors after an item is saved, while the management modal remains available only through the management button.

**Tech Stack:** Laravel Blade, vanilla JavaScript, Tailwind CSS, Pest

## Global Constraints

- Apply only to support-criteria evidence in the shared support table.
- Show every non-empty URL in saved order using the complete URL as visible text.
- Open URLs with `target="_blank"` and `rel="noopener noreferrer"`.
- Preserve the existing management button and modal for editing.
- Preserve evidence validation, storage, and score calculations.
- Keep the empty label `ไม่มีหลักฐาน`.
- Do not change quantity or quality evidence displays.

---

## File Structure

- `resources/views/components/support-criteria-table.blade.php` — render inline evidence anchors for desktop and mobile.
- `resources/views/components/support-criteria-table-script.blade.php` — rebuild inline anchors after modal saves and remove the obsolete evidence-modal click path.
- `tests/Feature/SupportCriteriaEvaluationViewTest.php` — protect initial rendering, live-refresh source contracts, safe attributes, and removal of count buttons.

### Task 1: Render and refresh support evidence as direct links

**Files:**
- Modify: `tests/Feature/SupportCriteriaEvaluationViewTest.php:90-128`
- Modify: `tests/Feature/SupportCriteriaEvaluationViewTest.php:253-326`
- Modify: `resources/views/components/support-criteria-table.blade.php:38-168`
- Modify: `resources/views/components/support-criteria-table-script.blade.php:455-583`

**Interfaces:**
- Consumes: each support item's normalized `evidence_links` array and the live values collected by `updateSupportRow(item)`.
- Produces: desktop and mobile containers named `data-support-evidence-list="<id>"`, each containing zero or more external anchors.

- [ ] **Step 1: Write the failing rendering and script-contract assertions**

In the editable component test, replace the count-button assertions with:

```php
expect($html)
    ->toContain('data-support-evidence-list="7"')
    ->toContain('href="https://example.com/evidence"')
    ->toContain('target="_blank"')
    ->toContain('rel="noopener noreferrer"')
    ->not->toContain('data-support-evidence-open')
    ->not->toContain('1 ลิงก์');

expect(substr_count($html, 'data-support-evidence-list="7"'))->toBe(2);
expect(substr_count($html, 'href="https://example.com/evidence"'))->toBe(2);
```

Update the read-only tests to require the real link and reject the old trigger:

```php
expect($html)
    ->not->toContain('data-support-evidence-open="7"')
    ->toContain('href="https://example.com/evidence"')
    ->toContain('target="_blank"')
    ->toContain('rel="noopener noreferrer"');
```

In the shared-script contract test, replace the old evidence trigger assertion with:

```php
expect($script)
    ->toContain("document.createElement('a')")
    ->toContain("anchor.target = '_blank'")
    ->toContain("anchor.rel = 'noopener noreferrer'")
    ->toContain('anchor.textContent = evidenceUrl')
    ->toContain('data-support-evidence-list')
    ->not->toContain("'[data-support-evidence-open]'");
```

- [ ] **Step 2: Run the focused test and verify RED**

Run:

```powershell
vendor\bin\pest tests/Feature/SupportCriteriaEvaluationViewTest.php
```

Expected: failures show that `data-support-evidence-list`, inline anchors, and
anchor creation are absent while `data-support-evidence-open` is still present.

- [ ] **Step 3: Render inline anchors in both Blade layouts**

In each item setup block, replace the evidence count with filtered links:

```blade
@php
    $evidenceLinks = array_values(array_filter($item['evidence_links'] ?? []));
    $evidenceCount = count($evidenceLinks);
    $activityNameText = \App\Support\SafeHtml::plainText($item['activity_name'] ?? '');
@endphp
```

Replace the desktop evidence cell content with:

```blade
<div class="space-y-1 text-left" data-support-evidence-list="{{ $item['id'] }}">
    @forelse ($evidenceLinks as $link)
        <a href="{{ $link }}" target="_blank" rel="noopener noreferrer"
            class="block break-all text-sm font-semibold text-blue-700 underline decoration-blue-300 underline-offset-4 focus:outline-none focus:ring-2 focus:ring-blue-400">
            {{ $link }}
        </a>
    @empty
        <span class="text-slate-400">ไม่มีหลักฐาน</span>
    @endforelse
</div>
```

Replace the mobile evidence content with:

```blade
<div class="mt-1 space-y-1" data-support-evidence-list="{{ $item['id'] }}">
    @forelse ($evidenceLinks as $link)
        <a href="{{ $link }}" target="_blank" rel="noopener noreferrer"
            class="block break-all text-sm font-semibold text-blue-700 underline decoration-blue-300 underline-offset-4 focus:outline-none focus:ring-2 focus:ring-blue-400">
            {{ $link }}
        </a>
    @empty
        <span class="text-sm text-slate-400">ไม่มีหลักฐาน</span>
    @endforelse
</div>
```

Keep `$evidenceCount` only for deciding whether the management action says
`กรอกข้อมูล` or `แก้ไขข้อมูล`.

- [ ] **Step 4: Rebuild anchors after an editable item is saved**

Replace the evidence-summary refresh inside `updateSupportRow(item)` with:

```javascript
document.querySelectorAll(`[data-support-evidence-list="${id}"]`).forEach((container) => {
    container.replaceChildren();
    if (evidenceLinks.length === 0) {
        const empty = document.createElement('span');
        empty.className = 'text-slate-400';
        empty.textContent = 'ไม่มีหลักฐาน';
        container.appendChild(empty);
        return;
    }

    evidenceLinks.forEach((evidenceUrl) => {
        const anchor = document.createElement('a');
        anchor.href = evidenceUrl;
        anchor.target = '_blank';
        anchor.rel = 'noopener noreferrer';
        anchor.className = 'block break-all text-sm font-semibold text-blue-700 underline decoration-blue-300 underline-offset-4 focus:outline-none focus:ring-2 focus:ring-blue-400';
        anchor.textContent = evidenceUrl;
        container.appendChild(anchor);
    });
});
```

Delete the click-handler branch that finds `[data-support-evidence-open]` and
calls `openSupportModal(..., 'evidence')`. Keep the
`[data-support-manage-open]` branch unchanged.

- [ ] **Step 5: Run focused tests and verify GREEN**

Run:

```powershell
vendor\bin\pest tests/Feature/SupportCriteriaEvaluationViewTest.php
```

Expected: all support criteria view tests pass.

- [ ] **Step 6: Run formatting, JavaScript tests, and production build**

Run:

```powershell
vendor\bin\pint --test tests/Feature/SupportCriteriaEvaluationViewTest.php
npm run test:js
npm run build
```

Expected: Pint passes, all JavaScript tests pass, and Vite builds successfully.

- [ ] **Step 7: Commit the implementation**

```powershell
git add -- resources/views/components/support-criteria-table.blade.php resources/views/components/support-criteria-table-script.blade.php tests/Feature/SupportCriteriaEvaluationViewTest.php
git commit -m "fix: show support evidence links inline"
```
