# Confirmation Support Achievement Card Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Show the current Support achievement score in the evaluatee submission-confirmation modal.

**Architecture:** Blade renders an achievement card beside the Support weighted-total card whenever Support criteria exist. The existing modal refresh function copies the already-calculated value from `support-achievement-summary`, avoiding duplicate calculation logic.

**Tech Stack:** Laravel Blade, vanilla JavaScript, Tailwind CSS, Pest

## Global Constraints

- Render the achievement card only when `has_support` is true.
- Use the label `คะแนนผลสัมฤทธิ์ของงาน`.
- Use the helper copy `ผลรวมคะแนนถ่วงน้ำหนัก ÷ จำนวนระดับค่าเป้าหมาย: 5`.
- Read the live value from `support-achievement-summary`; do not recalculate the divide-by-five rule in the modal script.
- Preserve all existing category visibility, calculation, submission, and validation behavior.
- Modify only the evaluatee confirmation modal and its existing update script.

---

## File Structure

- `resources/views/partials/evaluatee-confirmation-modal.blade.php` — render the Support achievement card and account for it in the responsive grid.
- `resources/views/partials/evaluatee-evaluation-script.blade.php` — copy the live achievement value into the modal.
- `tests/Feature/EvaluateeConfirmationModalTest.php` — protect visibility, helper copy, grid sizing, and the value-copy contract.

### Task 1: Add the live Support achievement card

**Files:**
- Modify: `tests/Feature/EvaluateeConfirmationModalTest.php:1-70`
- Modify: `resources/views/partials/evaluatee-confirmation-modal.blade.php:18-55`
- Modify: `resources/views/partials/evaluatee-evaluation-script.blade.php:128-140`

**Interfaces:**
- Consumes: `$scoreSummary['has_support']` and the live DOM value in `support-achievement-summary`.
- Produces: `modal-support-achievement-summary`, updated whenever `updateSubmissionSummary()` runs.

- [ ] **Step 1: Write the failing card and script assertions**

Extend the first modal source contract test:

```php
expect($source)
    ->toContain('id="modal-support-summary"')
    ->toContain('id="modal-support-achievement-summary"')
    ->toContain('คะแนนผลสัมฤทธิ์ของงาน')
    ->toContain('ผลรวมคะแนนถ่วงน้ำหนัก ÷ จำนวนระดับค่าเป้าหมาย: 5');
```

Extend the submission-script source contract test:

```php
expect($source)
    ->toContain("'support-achievement-summary'")
    ->toContain("'modal-support-achievement-summary'")
    ->toContain('supportAchievementSummary.toFixed(2)');
```

Update the Support-only render test to require the additional card and
three-column layout:

```php
expect($html)
    ->not->toContain('id="modal-quantity-summary"')
    ->not->toContain('id="modal-quality-summary"')
    ->toContain('id="modal-support-summary"')
    ->toContain('id="modal-support-achievement-summary"')
    ->toContain('id="modal-total-summary"')
    ->toContain('sm:grid-cols-3');
```

Extend the no-Support render assertion:

```php
expect($html)
    ->not->toContain('id="modal-support-summary"')
    ->not->toContain('id="modal-support-achievement-summary"');
```

Add an all-category layout test:

```php
it('uses the five-card layout when every score category has criteria', function () {
    $html = view('partials.evaluatee-confirmation-modal', [
        'categoryItems' => [],
        'scoreSummary' => [
            'has_quantity' => true,
            'has_quality' => true,
            'has_support' => true,
        ],
    ])->render();

    expect($html)
        ->toContain('id="modal-quantity-summary"')
        ->toContain('id="modal-quality-summary"')
        ->toContain('id="modal-support-summary"')
        ->toContain('id="modal-support-achievement-summary"')
        ->toContain('id="modal-total-summary"')
        ->toContain('sm:grid-cols-2 lg:grid-cols-5');
});
```

- [ ] **Step 2: Run focused tests and verify RED**

Run:

```powershell
vendor\bin\pest tests/Feature/EvaluateeConfirmationModalTest.php
```

Expected: failures report the missing achievement card, missing script update,
and outdated Support-only grid count.

- [ ] **Step 3: Count the achievement card in the responsive grid**

Update the visible-card count so Support contributes two cards:

```blade
@php
    $hasSupportScore = $scoreSummary['has_support'] ?? false;
    $visibleScoreCardCount = 1
        + (int) ($scoreSummary['has_quantity'] ?? false)
        + (int) ($scoreSummary['has_quality'] ?? false)
        + (2 * (int) $hasSupportScore);

    $scoreCardGridClass = match ($visibleScoreCardCount) {
        1 => 'sm:grid-cols-1',
        2 => 'sm:grid-cols-2',
        3 => 'sm:grid-cols-3',
        4 => 'sm:grid-cols-4',
        default => 'sm:grid-cols-2 lg:grid-cols-5',
    };
@endphp
```

Use `$hasSupportScore` for the existing Support condition.

- [ ] **Step 4: Render the achievement card after the Support card**

Inside the Support condition, immediately after the existing Support card,
add:

```blade
<div class="rounded-lg border border-amber-100 bg-amber-50/50 p-3">
    <div class="text-sm text-gray-500">คะแนนผลสัมฤทธิ์ของงาน</div>
    <div class="mt-1 text-xs text-amber-700">ผลรวมคะแนนถ่วงน้ำหนัก ÷ จำนวนระดับค่าเป้าหมาย: 5</div>
    <div id="modal-support-achievement-summary" class="mt-1 text-lg font-bold text-amber-900 sm:text-xl">0.00</div>
</div>
```

- [ ] **Step 5: Copy the live achievement value into the modal**

At the start of `updateSubmissionSummary()`, read both elements:

```javascript
const supportAchievementSummary = parseFloat(
    document.getElementById('support-achievement-summary')?.textContent || '0',
) || 0;
const modalSupportAchievement = document.getElementById('modal-support-achievement-summary');
```

With the other guarded modal assignments, add:

```javascript
if (modalSupportAchievement) {
    modalSupportAchievement.textContent = supportAchievementSummary.toFixed(2);
}
```

- [ ] **Step 6: Run focused tests and verify GREEN**

Run:

```powershell
vendor\bin\pest tests/Feature/EvaluateeConfirmationModalTest.php
```

Expected: 6 tests pass.

- [ ] **Step 7: Run formatting, JavaScript tests, and production build**

Run:

```powershell
vendor\bin\pint --test tests/Feature/EvaluateeConfirmationModalTest.php
npm run test:js
npm run build
```

Expected: Pint passes, all JavaScript tests pass, and Vite builds successfully.

- [ ] **Step 8: Commit the implementation**

```powershell
git add -- resources/views/partials/evaluatee-confirmation-modal.blade.php resources/views/partials/evaluatee-evaluation-script.blade.php tests/Feature/EvaluateeConfirmationModalTest.php
git commit -m "fix: show achievement score in confirmation"
```
