# Workload Formula Preview Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** เพิ่มพรีวิวสูตรภาระงานที่แสดงชื่อภาษาไทยแทนรหัสตัวแปร โดยไม่เปลี่ยนสูตรจริงที่บันทึกและส่งไปคำนวณ

**Architecture:** แยกการแปลงสูตรเป็น ES module แบบ pure function เพื่อทดสอบด้วย Node built-in test runner แล้ว import ผ่าน Vite entry เดิมเพื่อเปิด API บน `window`. Blade workload builder จะรวบรวม mapping จากรายการตัวแปรใน sub-card และเรียก formatter ทุกครั้งที่สูตรหรือ metadata ของตัวแปรเปลี่ยน

**Tech Stack:** Laravel 11, Blade, browser JavaScript, Vite 6, Node `node:test`, Pest 3

## Global Constraints

- `formula_logic` และ backend evaluator ต้องไม่เปลี่ยน
- แทน identifier แบบทั้งคำและไม่แยก `num_1` ผิดเป็นส่วนหนึ่งของ `num_10`
- identifier ที่ไม่มี mapping ต้องคงข้อความเดิม
- พรีวิวต้องมี label ที่มองเห็นและ `aria-live="polite"`
- ไม่เพิ่ม npm dependency ใหม่
- ไม่แก้ไฟล์งานเดิมที่ไม่เกี่ยวข้องใน working tree

---

### Task 1: Pure formula preview formatter

**Files:**
- Create: `tests/js/workload-formula-preview.test.mjs`
- Create: `resources/js/workload-formula-preview.js`
- Modify: `resources/js/app.ts`
- Modify: `package.json`

**Interfaces:**
- Consumes: สูตรรหัส `string` และ custom label map ชนิด `Record<string, string>`
- Produces: `formatWorkloadFormulaPreview(formula, customLabels): string` และ `window.WorkloadFormulaPreview.format`

- [ ] **Step 1: Write the failing formatter test**

Create `tests/js/workload-formula-preview.test.mjs`:

```js
import test from 'node:test';
import assert from 'node:assert/strict';

import { formatWorkloadFormulaPreview } from '../../resources/js/workload-formula-preview.js';

test('formats built-in and custom variables with readable operators', () => {
    const preview = formatWorkloadFormulaPreview('(item_star*num_1*num_3)/num_2', {
        num_1: 'หน่วยกิต',
        num_2: 'จำนวนนักศึกษา',
        num_3: 'จำนวนผู้สอน',
    });

    assert.equal(preview, '(ค่าภารงาน×หน่วยกิต×จำนวนผู้สอน)÷จำนวนนักศึกษา');
});

test('matches complete identifiers case-insensitively and preserves unknown identifiers', () => {
    const preview = formatWorkloadFormulaPreview('SUM(NUM_1,num_10,unknown)', {
        num_1: 'หน่วยกิต',
        num_10: 'จำนวนกลุ่ม',
    });

    assert.equal(preview, 'SUM(หน่วยกิต,จำนวนกลุ่ม,unknown)');
});

test('formats subject credit variables and returns guidance for an empty formula', () => {
    assert.equal(
        formatWorkloadFormulaPreview('credits+lecture_credits+lab_credits+self_study_credits'),
        'หน่วยกิตรวม+หน่วยกิตบรรยาย+หน่วยกิตปฏิบัติ+หน่วยกิตศึกษาด้วยตนเอง',
    );
    assert.equal(formatWorkloadFormulaPreview('  '), 'พรีวิวจะแสดงเมื่อระบุสูตรการคำนวณ');
});
```

Add a script to `package.json`:

```json
"test:js": "node --test tests/js/*.test.mjs"
```

- [ ] **Step 2: Run the formatter test and verify RED**

Run:

```powershell
npm run test:js
```

Expected: FAIL with `ERR_MODULE_NOT_FOUND` for `resources/js/workload-formula-preview.js`.

- [ ] **Step 3: Implement the minimal pure formatter**

Create `resources/js/workload-formula-preview.js`:

```js
const builtInLabels = Object.freeze({
    item_star: 'ค่าภารงาน',
    credits: 'หน่วยกิตรวม',
    lecture_credits: 'หน่วยกิตบรรยาย',
    lab_credits: 'หน่วยกิตปฏิบัติ',
    self_study_credits: 'หน่วยกิตศึกษาด้วยตนเอง',
});

export function formatWorkloadFormulaPreview(formula, customLabels = {}) {
    const source = String(formula ?? '');
    if (source.trim() === '') {
        return 'พรีวิวจะแสดงเมื่อระบุสูตรการคำนวณ';
    }

    const labels = Object.fromEntries(
        Object.entries({ ...builtInLabels, ...customLabels }).map(([name, label]) => [
            name.toLowerCase(),
            String(label),
        ]),
    );

    return source
        .replace(/[A-Za-z_][A-Za-z0-9_]*/g, (identifier) => labels[identifier.toLowerCase()] ?? identifier)
        .replaceAll('*', '×')
        .replaceAll('/', '÷');
}

if (typeof window !== 'undefined') {
    window.WorkloadFormulaPreview = {
        format: formatWorkloadFormulaPreview,
    };
}
```

Import the side-effect module at the top of `resources/js/app.ts`:

```ts
import './workload-formula-preview';
```

- [ ] **Step 4: Run formatter tests and frontend build to verify GREEN**

Run:

```powershell
npm run test:js
npm run build
```

Expected: all three Node tests PASS and Vite build exits `0` without TypeScript or bundling errors.

- [ ] **Step 5: Commit the formatter**

```powershell
git add -- package.json resources/js/app.ts resources/js/workload-formula-preview.js tests/js/workload-formula-preview.test.mjs
git commit -m "feat: add workload formula preview formatter"
```

---

### Task 2: Workload builder preview UI and live updates

**Files:**
- Modify: `tests/Feature/WorkloadBuilderAccessibilityTest.php`
- Modify: `resources/views/workload/app.blade.php`
- Modify: `resources/views/workload/partials/app-styles.blade.php`
- Modify: `resources/views/workload/partials/app-script.blade.php`

**Interfaces:**
- Consumes: `window.WorkloadFormulaPreview.format(formula, labels)` from Task 1 and existing `.formula-item` rows
- Produces: `.workload-formula-preview-value` text beneath each formula textarea

- [ ] **Step 1: Write the failing view/accessibility test**

Extend the first expectation chain in `tests/Feature/WorkloadBuilderAccessibilityTest.php`:

```php
        ->and(preg_match_all('/<div[^>]*class="[^"]*workload-formula-preview-value[^"]*"[^>]*aria-live="polite"[^>]*aria-atomic="true"[^>]*>/u', $html))->toBe(1)
        ->and(str_contains($html, 'พรีวิวสูตร'))->toBeTrue();
```

Extend the script assertions:

```php
        ->and($html)
        ->toContain('updateFormulaPreview')
        ->and($html)
        ->toContain("formulaText.addEventListener('input', updateFormulaPreview)");
```

- [ ] **Step 2: Run the feature test and verify RED**

Run:

```powershell
php vendor/bin/pest tests/Feature/WorkloadBuilderAccessibilityTest.php
```

Expected: FAIL because the response does not contain `.workload-formula-preview-value`.

- [ ] **Step 3: Add accessible preview markup and styles**

Immediately after the formula textarea in `resources/views/workload/app.blade.php`, add:

```blade
<div class="workload-formula-preview mt-2">
    <div class="workload-formula-preview-label">พรีวิวสูตร</div>
    <div class="workload-formula-preview-value" aria-live="polite" aria-atomic="true">
        พรีวิวจะแสดงเมื่อระบุสูตรการคำนวณ
    </div>
</div>
```

After `.formula-text` in `resources/views/workload/partials/app-styles.blade.php`, add:

```css
.workload-formula-preview {
    border: 1px solid #dbe4f0;
    border-radius: 10px;
    background: #f8fafc;
    padding: 0.75rem 0.9rem;
}

.workload-formula-preview-label {
    color: #64748b;
    font-size: 0.78rem;
    font-weight: 600;
    margin-bottom: 0.25rem;
}

.workload-formula-preview-value {
    color: #0f172a;
    font-size: 0.95rem;
    line-height: 1.5;
    overflow-wrap: anywhere;
}
```

- [ ] **Step 4: Wire live preview updates into each sub-card**

In `bindSubCard`, next to `formulaText`, capture:

```js
const formulaPreview = card.querySelector('.workload-formula-preview-value');
```

After the variable input declarations, define:

```js
const updateFormulaPreview = () => {
    if (!formulaPreview || !window.WorkloadFormulaPreview) {
        return;
    }

    const labels = {};
    formulaList?.querySelectorAll('.formula-item').forEach((row) => {
        const variableName = row.querySelector('.formula-value')?.textContent.trim().toLowerCase();
        const label = row.querySelector('.formula-item-label')?.textContent.trim();
        if (variableName && label) {
            labels[variableName] = label;
        }
    });

    formulaPreview.textContent = window.WorkloadFormulaPreview.format(formulaText?.value ?? '', labels);
};

if (formulaText) {
    formulaText.addEventListener('input', updateFormulaPreview);
}
```

Make token insertion emit the same input event as typing. In `insertToken`, immediately after `textarea.value` and cursor updates, add:

```js
textarea.dispatchEvent(new Event('input', { bubbles: true }));
```

This covers every existing operator, function, built-in-variable, workload-value, and custom-variable click because all of those handlers already call `insertToken`.

Add direct `updateFormulaPreview()` calls at these four metadata mutation points:

```js
// End of addFormulaItem(), immediately after syncVariableChips()
updateFormulaPreview();

// Inside the formula-item remove button callback, immediately after syncVariableChips()
updateFormulaPreview();

// Inside the delegated .workload-variable-remove handler, immediately after syncVariableChips()
updateFormulaPreview();

// Inside the editingFormulaRow branch, immediately before resetVariableForm()
updateFormulaPreview();
```

At the end of `bindSubCard`, immediately after `syncVariableChips()`, call `updateFormulaPreview()` once so a new empty card receives the guidance text.

In the existing-data population block, call `updateFormulaPreview` through the card API after fields and `formula_logic` have been populated. Add it to the API:

```js
card.__workload = {
    addFormulaItem,
    syncVariableChips,
    updateFormulaPreview,
    updateItemSequence,
};
```

Then after the loop that calls `api.addFormulaItem(...)`, call:

```js
api?.updateFormulaPreview?.();
```

- [ ] **Step 5: Run focused tests and verify GREEN**

Run:

```powershell
npm run test:js
php vendor/bin/pest tests/Feature/WorkloadBuilderAccessibilityTest.php tests/Feature/WorkloadConfigControllerTest.php
npm run build
```

Expected: Node tests, both Pest feature files, and Vite build all PASS.

- [ ] **Step 6: Review generated diff and commit the UI**

Run:

```powershell
git diff --check
git diff -- resources/views/workload/app.blade.php resources/views/workload/partials/app-styles.blade.php resources/views/workload/partials/app-script.blade.php tests/Feature/WorkloadBuilderAccessibilityTest.php
```

Confirm the diff does not alter the formula payload at `formula_logic: formulaText ? formulaText.value.trim() : ''`.

Commit:

```powershell
git add -- resources/views/workload/app.blade.php resources/views/workload/partials/app-styles.blade.php resources/views/workload/partials/app-script.blade.php tests/Feature/WorkloadBuilderAccessibilityTest.php
git commit -m "feat: show readable workload formula preview"
```

---

### Task 3: Final regression verification

**Files:**
- Verify only; no source changes expected

**Interfaces:**
- Consumes: formatter and workload builder UI from Tasks 1–2
- Produces: recorded verification evidence for handoff

- [ ] **Step 1: Run complete relevant verification**

```powershell
npm run test:js
php vendor/bin/pest tests/Feature/WorkloadFormulaEvaluatorTest.php tests/Feature/WorkloadBuilderAccessibilityTest.php tests/Feature/WorkloadConfigControllerTest.php
npm run build
git diff --check HEAD~2..HEAD
git status --short
```

Expected:

- All Node formatter tests PASS
- All selected Pest tests PASS
- Vite build exits `0`
- `git diff --check` returns no output
- Only pre-existing unrelated untracked UAT files remain in `git status --short`

- [ ] **Step 2: Manually inspect the example mapping in a browser when available**

Enter:

```text
(item_star*num_1*num_3)/num_2
```

with labels `หน่วยกิต`, `จำนวนนักศึกษา`, and `จำนวนผู้สอน`. Confirm the visible preview is:

```text
(ค่าภารงาน×หน่วยกิต×จำนวนผู้สอน)÷จำนวนนักศึกษา
```

Confirm saving still sends the original formula containing `item_star`, `num_1`, `num_2`, and `num_3`.
