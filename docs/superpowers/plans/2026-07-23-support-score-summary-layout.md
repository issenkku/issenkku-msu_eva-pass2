# Support Score Summary Layout Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Group the support weighted-score total and derived achievement score into the approved compact two-row card.

**Architecture:** Keep the existing score-summary read model, values, and JavaScript hooks unchanged. Reshape only the support branch of the shared Blade partial and protect its structure with the focused Blade view test.

**Tech Stack:** Laravel Blade, Tailwind CSS, Pest

## Global Constraints

- Apply only when `has_support` is true.
- Keep `support-summary`, `support-achievement-summary`, and `total-summary`.
- Keep the copy `ผลรวมคะแนนถ่วงน้ำหนัก ÷ จำนวนระดับค่าเป้าหมาย: 5`.
- Keep the target-level count fixed at 5.
- Do not alter quantity, quality, director, or other scoring behavior.
- Keep the overall-total card separate from the grouped support card.

---

## File Structure

- `resources/views/partials/evaluator-score-summary.blade.php` — render the shared score summary and the new support-only two-row card.
- `tests/Feature/EvaluationScoreSummaryViewTest.php` — verify the support grouping and preserve category visibility behavior.

### Task 1: Group the support metrics in one compact card

**Files:**
- Modify: `tests/Feature/EvaluationScoreSummaryViewTest.php:18-27`
- Modify: `resources/views/partials/evaluator-score-summary.blade.php:23-42`

**Interfaces:**
- Consumes: the existing `$scoreSummary` keys `has_support`, `support`, `support_achievement`, and `support_target_level_count`.
- Produces: one `data-testid="support-score-pair"` container retaining the existing live-update element IDs.

- [ ] **Step 1: Write the failing structure assertion**

Add the grouped-container assertion to the first view test:

```php
expect($html)
    ->not->toContain('id="quantity-summary"')
    ->not->toContain('id="quality-summary"')
    ->toContain('data-testid="support-score-pair"')
    ->toContain('id="support-summary"')
    ->toContain('ผลรวมคะแนนถ่วงน้ำหนัก')
    ->toContain('id="support-achievement-summary"')
    ->toContain('data-support-target-level-count="5"')
    ->toContain('ผลรวมคะแนนถ่วงน้ำหนัก ÷ จำนวนระดับค่าเป้าหมาย: 5')
    ->toContain('>4.30</span>')
    ->toContain('id="total-summary"');
```

- [ ] **Step 2: Run the focused test and verify the new assertion fails**

Run:

```powershell
vendor\bin\pest tests/Feature/EvaluationScoreSummaryViewTest.php
```

Expected: one failure because `data-testid="support-score-pair"` is absent.

- [ ] **Step 3: Replace the support branch with the approved two-row card**

Use this support-only Blade structure:

```blade
@if ($scoreSummary['has_support'] ?? false)
    <div data-testid="support-score-pair" class="overflow-hidden rounded-xl border border-blue-200 bg-white shadow-inner">
        <div class="flex flex-col gap-2 p-4 sm:flex-row sm:items-center sm:justify-between">
            <span class="text-base font-semibold text-blue-800">ผลรวมคะแนนถ่วงน้ำหนัก</span>
            <span id="support-summary" class="text-xl font-bold text-blue-900">{{ number_format($scoreSummary['support'] ?? 0, 2) }}</span>
        </div>
        <div class="flex flex-col gap-2 border-t border-blue-100 bg-blue-50/40 p-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="text-base font-semibold text-blue-800">คะแนนผลสัมฤทธิ์ของงาน</div>
                <div class="text-sm text-blue-600">ผลรวมคะแนนถ่วงน้ำหนัก ÷ จำนวนระดับค่าเป้าหมาย: {{ $scoreSummary['support_target_level_count'] }}</div>
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

- [ ] **Step 4: Run the focused view test**

Run:

```powershell
vendor\bin\pest tests/Feature/EvaluationScoreSummaryViewTest.php
```

Expected: 3 tests pass.

- [ ] **Step 5: Verify formatting and the production asset build**

Run:

```powershell
vendor\bin\pint --test tests/Feature/EvaluationScoreSummaryViewTest.php
npm run build
```

Expected: Pint reports no formatting errors and Vite completes successfully.

- [ ] **Step 6: Commit the implementation**

```powershell
git add -- resources/views/partials/evaluator-score-summary.blade.php tests/Feature/EvaluationScoreSummaryViewTest.php
git commit -m "fix: group support score summary metrics"
```
