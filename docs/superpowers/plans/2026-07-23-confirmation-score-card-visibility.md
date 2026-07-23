# Confirmation Score Card Visibility Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Make the evaluatee confirmation modal show only score categories that have criteria while retaining visible `0.00` cards for present categories.

**Architecture:** The modal consumes the existing `EvaluationScoreSummary` flags already passed to the evaluatee page. Blade conditionally renders category cards and selects a responsive static grid class; the existing JavaScript continues updating whichever card elements exist.

**Tech Stack:** Laravel Blade, Tailwind CSS, Pest

## Global Constraints

- Use `has_quantity`, `has_quality`, and `has_support` as the only category visibility rules.
- Never use a numeric score to decide whether a category card is visible.
- Keep Total visible unconditionally.
- Modify only the evaluatee confirmation modal that already contains score cards.
- Do not add score cards to evaluator, manager, or director confirmation modals.
- Do not change calculations, validation, submission behavior, or the detail sections below the score cards.

---

## File Structure

- `resources/views/partials/evaluatee-confirmation-modal.blade.php` — conditionally render score cards and select the responsive column count.
- `tests/Feature/EvaluateeConfirmationModalTest.php` — render the modal with representative summary flags and protect zero-score visibility.

### Task 1: Apply shared category-presence rules to confirmation cards

**Files:**
- Modify: `tests/Feature/EvaluateeConfirmationModalTest.php:1-33`
- Modify: `resources/views/partials/evaluatee-confirmation-modal.blade.php:18-38`

**Interfaces:**
- Consumes: `$scoreSummary['has_quantity']`, `$scoreSummary['has_quality']`, and `$scoreSummary['has_support']`.
- Produces: zero to three category cards plus the always-present Total card, with the existing modal element IDs unchanged.

- [ ] **Step 1: Add failing render tests**

Append these tests to `tests/Feature/EvaluateeConfirmationModalTest.php`:

```php
it('shows only score categories that have criteria in the confirmation modal', function () {
    $html = view('partials.evaluatee-confirmation-modal', [
        'categoryItems' => [],
        'scoreSummary' => [
            'has_quantity' => false,
            'has_quality' => false,
            'has_support' => true,
        ],
    ])->render();

    expect($html)
        ->not->toContain('id="modal-quantity-summary"')
        ->not->toContain('id="modal-quality-summary"')
        ->toContain('id="modal-support-summary"')
        ->toContain('id="modal-total-summary"')
        ->toContain('sm:grid-cols-2');
});

it('keeps a zero score card visible when its category has criteria', function () {
    $html = view('partials.evaluatee-confirmation-modal', [
        'categoryItems' => [],
        'scoreSummary' => [
            'quantity' => 0.0,
            'has_quantity' => true,
            'has_quality' => false,
            'has_support' => false,
        ],
    ])->render();

    expect($html)
        ->toContain('id="modal-quantity-summary"')
        ->toMatch('/id="modal-quantity-summary"[^>]*>0\.00<\/div>/')
        ->not->toContain('id="modal-quality-summary"')
        ->not->toContain('id="modal-support-summary"')
        ->toContain('id="modal-total-summary"')
        ->toContain('sm:grid-cols-2');
});
```

- [ ] **Step 2: Run the focused tests and verify RED**

Run:

```powershell
vendor\bin\pest tests/Feature/EvaluateeConfirmationModalTest.php
```

Expected: the two new tests fail because all three category cards are still
rendered unconditionally.

- [ ] **Step 3: Compute the visible card count and responsive grid class**

Immediately before the score-card grid, add:

```blade
@php
    $visibleScoreCardCount = 1
        + (int) ($scoreSummary['has_quantity'] ?? false)
        + (int) ($scoreSummary['has_quality'] ?? false)
        + (int) ($scoreSummary['has_support'] ?? false);

    $scoreCardGridClass = match ($visibleScoreCardCount) {
        1 => 'sm:grid-cols-1',
        2 => 'sm:grid-cols-2',
        3 => 'sm:grid-cols-3',
        default => 'sm:grid-cols-4',
    };
@endphp
```

Change the grid opening tag to:

```blade
<div class="mb-3 grid grid-cols-1 gap-2 sm:gap-3 {{ $scoreCardGridClass }}">
```

- [ ] **Step 4: Conditionally render each category card**

Wrap the existing Quantity card with:

```blade
@if ($scoreSummary['has_quantity'] ?? false)
    <div class="rounded-lg border border-blue-100 bg-white p-3">
        <div class="text-sm text-gray-500">คะแนนด้านปริมาณ</div>
        <div id="modal-quantity-summary" class="mt-1 text-lg font-bold text-blue-900 sm:text-xl">0.00</div>
    </div>
@endif
```

Wrap the existing Quality card with:

```blade
@if ($scoreSummary['has_quality'] ?? false)
    <div class="rounded-lg border border-purple-100 bg-white p-3">
        <div class="text-sm text-gray-500">คะแนนด้านคุณภาพ</div>
        <div id="modal-quality-summary" class="mt-1 text-lg font-bold text-purple-900 sm:text-xl">0.00</div>
    </div>
@endif
```

Wrap the existing Support card with:

```blade
@if ($scoreSummary['has_support'] ?? false)
    <div class="rounded-lg border border-amber-100 bg-white p-3">
        <div class="text-sm text-gray-500">คะแนนสายสนับสนุน</div>
        <div id="modal-support-summary" class="mt-1 text-lg font-bold text-amber-900 sm:text-xl">0.00</div>
    </div>
@endif
```

Leave the existing Total card outside all conditions.

- [ ] **Step 5: Run focused tests and verify GREEN**

Run:

```powershell
vendor\bin\pest tests/Feature/EvaluateeConfirmationModalTest.php
```

Expected: 5 tests pass.

- [ ] **Step 6: Run formatting and production build**

Run:

```powershell
vendor\bin\pint --test tests/Feature/EvaluateeConfirmationModalTest.php
npm run build
```

Expected: Pint passes and Vite builds successfully.

- [ ] **Step 7: Commit the implementation**

```powershell
git add -- resources/views/partials/evaluatee-confirmation-modal.blade.php tests/Feature/EvaluateeConfirmationModalTest.php
git commit -m "fix: hide unused confirmation score cards"
```
