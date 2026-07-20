# Support Criteria Modal Management Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** เพิ่มคอลัมน์หลักฐานและจัดการในตารางเกณฑ์สายสนับสนุน แล้วใช้ Modal กลางหนึ่งชุดแทนฟอร์มการ์ดใต้ตาราง โดยยังบันทึกผ่านฟอร์มหลักตาม Flow เดิม

**Architecture:** `support-criteria-table.blade.php` ยังคง render input จริงหนึ่งชุดต่อเกณฑ์ไว้ใน editor store ที่ซ่อนอยู่ และมี Modal shell กลางเพียงหนึ่งชุด เมื่อเลือกแถว JavaScript จะย้าย editor ของรายการนั้นเข้า Modal, snapshot ค่าเพื่อรองรับยกเลิก, และย้ายกลับเมื่อปิด โดยไม่เปลี่ยน payload หรือเรียก Server จาก Modal

**Tech Stack:** Laravel 11, Blade components, Tailwind CSS, JavaScript DOM APIs, Pest/PHPUnit, Vite

## Global Constraints

- ทำงานบน branch `feat/support` โดยตรงและไม่สร้าง worktree
- Modal เก็บค่าไว้บนหน้าจอเท่านั้น ฐานข้อมูลเปลี่ยนเมื่อกด `บันทึกฉบับร่าง` หรือ `ยืนยันส่ง` ของหน้าหลัก
- คงชื่อ input `support_list[<id>][support_criteria_id|achieved_score|evidence_links|modification_reason]` เดิม
- ใช้ Modal shell กลางหนึ่งชุด ไม่สร้าง Modal แยกต่อรายการ
- โหมดอ่านอย่างเดียวไม่มีคอลัมน์จัดการ แต่กดจำนวนหลักฐานเพื่อดู URL ได้
- คะแนนต้องไม่น้อยกว่า 0 และมีทศนิยมไม่เกิน 2 ตำแหน่ง
- หลักฐานต้องเป็น HTTP/HTTPS URL และรายการบังคับหลักฐานต้องมีอย่างน้อยหนึ่ง URL ทั้ง Draft และ Submit
- Reviewer ที่เปลี่ยนคะแนนต้องใส่เหตุผลและยังเห็นประวัติตาม Flow เดิม
- ไม่เพิ่ม API, route, schema หรือการคำนวณคะแนนฝั่ง Server ใหม่
- ใช้ amber/slate palette และรูปแบบ typography เดิมของเกณฑ์สายสนับสนุน

---

## File Structure

- `resources/views/components/support-criteria-table.blade.php` — ตารางเดสก์ท็อป การ์ดมือถือ editor store และ Modal shell
- `resources/views/components/support-criteria-table-script.blade.php` — lifecycle ของ Modal, snapshot/restore, validation, evidence count และการคำนวณ
- `tests/Feature/SupportCriteriaEvaluationViewTest.php` — contract ของ markup, สิทธิ์แต่ละโหมด และ contract ของ shared script

### Task 1: Table Actions, Evidence Count, and Single Modal Shell

**Files:**
- Modify: `tests/Feature/SupportCriteriaEvaluationViewTest.php`
- Modify: `resources/views/components/support-criteria-table.blade.php`

**Interfaces:**
- Produces: `[data-support-manage-open="<id>"]`, `[data-support-evidence-open="<id>"]`, `[data-support-evidence-count="<id>"]`
- Produces: `[data-support-editor-store]`, `[data-support-item][data-support-id="<id>"]`
- Produces: `[data-support-evidence-section]` inside each item so evidence triggers can focus the correct section
- Produces: one `[data-support-modal]` with `[data-support-modal-body]`, `[data-support-modal-errors]`, `[data-support-modal-save]`, and `[data-support-modal-cancel]`
- Preserves: all existing `support_list[...]` input names exactly

- [ ] **Step 1: Write failing view-contract tests**

Add assertions to the editable test:

```php
expect($html)
    ->toContain('>หลักฐาน<')
    ->toContain('>จัดการ<')
    ->toContain('data-support-evidence-count="7"')
    ->toContain('data-support-evidence-open="7"')
    ->toContain('data-support-manage-open="7"')
    ->toContain('data-support-editor-store')
    ->toContain('data-support-modal')
    ->toContain('data-support-modal-body')
    ->toContain('data-support-modal-save')
    ->not->toContain('border-t border-amber-200 bg-white p-4 sm:p-5');

expect(substr_count($html, 'data-support-modal role="dialog"'))->toBe(1);
expect(substr_count($html, 'name="support_list[7][achieved_score]"'))->toBe(1);
```

Add a read-only contract:

```php
test('read only support table shows evidence count without management controls', function () {
    $html = view('components.support-criteria-table', [
        'items' => [supportViewItem()],
        'readonly' => true,
        'evidenceEditable' => false,
        'requireReason' => false,
    ])->render();

    expect($html)
        ->toContain('data-support-evidence-count="7"')
        ->toContain('data-support-evidence-open="7"')
        ->not->toContain('data-support-manage-open="7"')
        ->not->toContain('>จัดการ<')
        ->not->toContain('data-support-modal-save');
});
```

- [ ] **Step 2: Run the tests and verify RED**

Run:

```powershell
vendor\bin\pest tests\Feature\SupportCriteriaEvaluationViewTest.php --compact
```

Expected: FAIL because evidence/manage columns and Modal data attributes do not exist.

- [ ] **Step 3: Add evidence and management cells to desktop and mobile summaries**

Use a per-item count and conditional buttons:

```blade
@php($evidenceCount = count(array_filter($item['evidence_links'] ?? [])))

<td class="px-3 py-4 text-center">
    <span data-support-evidence-count="{{ $item['id'] }}">
        @if ($evidenceCount > 0)
            <button type="button"
                data-support-evidence-open="{{ $item['id'] }}"
                class="font-semibold text-blue-700 underline decoration-blue-300 underline-offset-4 focus:outline-none focus:ring-2 focus:ring-blue-400">
                {{ $evidenceCount }} ลิงก์
            </button>
        @else
            <span class="text-slate-400">ไม่มีหลักฐาน</span>
        @endif
    </span>
</td>

@if (!$readonly)
    <td class="px-3 py-4 text-center">
        <button type="button" data-support-manage-open="{{ $item['id'] }}"
            class="rounded-lg bg-amber-100 px-3 py-2 font-semibold text-amber-900 hover:bg-amber-200 focus:outline-none focus:ring-2 focus:ring-amber-400">
            {{ filled($item['achieved_score']) || $evidenceCount > 0 ? 'แก้ไขข้อมูล' : 'กรอกข้อมูล' }}
        </button>
    </td>
@endif
```

Mirror the same evidence count and management controls in each mobile card.

- [ ] **Step 4: Replace the visible editor-card section with a hidden editor store and one Modal shell**

Wrap the existing per-item `<article data-support-item>` elements in:

```blade
<div class="hidden" data-support-editor-store aria-hidden="true">
    {{-- Move the complete current @foreach ($items as $item) block that starts
         with <article class="rounded-xl border border-slate-200 bg-slate-50 p-4 sm:p-5"
         data-support-item> from the visible trailing section
         into this store. Do not change its input names or conditional Role sections. --}}
</div>
```

Delete the old visible wrapper whose exact opening tag is
`<div class="space-y-4 border-t border-amber-200 bg-white p-4 sm:p-5">` after moving its complete `@foreach` block into the store.
Add `data-support-sequence="{{ $item['sequence'] }}"` to each editor article and
`data-support-evidence-section` to the existing evidence section whose opening class is `mt-4`.

Add one shell after the store:

```blade
<div class="fixed inset-0 z-[1100] hidden items-center justify-center bg-slate-950/60 p-4"
    data-support-modal role="dialog" aria-modal="true" aria-labelledby="support-modal-title">
    <div class="flex max-h-[90vh] w-full max-w-3xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl"
        data-support-modal-panel>
        <header class="flex items-start justify-between border-b border-slate-200 px-5 py-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-amber-700" data-support-modal-sequence></p>
                <h4 id="support-modal-title" class="mt-1 text-lg font-bold text-slate-950" data-support-modal-title></h4>
            </div>
            <button type="button" data-support-modal-cancel aria-label="ปิดหน้าต่าง"
                class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-amber-400">×</button>
        </header>
        <div class="hidden border-b border-red-200 bg-red-50 px-5 py-3 text-sm text-red-700"
            data-support-modal-errors role="alert"></div>
        <div class="overflow-y-auto p-5" data-support-modal-body></div>
        <footer class="flex justify-end gap-3 border-t border-slate-200 px-5 py-4">
            <button type="button" data-support-modal-cancel class="rounded-lg border border-slate-300 px-4 py-2 font-semibold text-slate-700">{{ $readonly ? 'ปิด' : 'ยกเลิก' }}</button>
            @if (!$readonly)
                <button type="button" data-support-modal-save class="rounded-lg bg-amber-500 px-4 py-2 font-semibold text-white hover:bg-amber-600">บันทึก</button>
            @endif
        </footer>
    </div>
</div>
```

Do not duplicate any achieved-score input outside the editor store.

- [ ] **Step 5: Run focused tests and format the Blade cache**

Run:

```powershell
vendor\bin\pest tests\Feature\SupportCriteriaEvaluationViewTest.php --compact
php artisan view:cache
```

Expected: all view tests PASS and Blade templates cache successfully.

- [ ] **Step 6: Commit Task 1**

```powershell
git add -- resources/views/components/support-criteria-table.blade.php tests/Feature/SupportCriteriaEvaluationViewTest.php
git commit -m "feat: add support criteria management modal"
```

### Task 2: Modal Lifecycle, Snapshot, and Evidence Count Behavior

**Files:**
- Modify: `tests/Feature/SupportCriteriaEvaluationViewTest.php`
- Modify: `resources/views/components/support-criteria-table-script.blade.php`

**Interfaces:**
- Consumes: Task 1 data attributes and existing `[data-support-score]`, `[data-support-evidence-input]`, `[data-support-reason]`
- Produces: `openSupportModal(criterionId, section)`, `closeSupportModal({ restore })`, `snapshotSupportItem(item)`, `restoreSupportItem(item, snapshot)`, and `updateSupportRow(item)` inside the shared closure
- Preserves: `window.recalculateSupportScores()` and `window.validateSupportCriteria()` globals

- [ ] **Step 1: Write failing script-contract tests**

Extend the shared-script test:

```php
expect($script)
    ->toContain('const openSupportModal =')
    ->toContain('const closeSupportModal =')
    ->toContain('const snapshotSupportItem =')
    ->toContain('const restoreSupportItem =')
    ->toContain('const updateSupportRow =')
    ->toContain("'[data-support-manage-open]'")
    ->toContain("'[data-support-evidence-open]'")
    ->toContain("'[data-support-modal-save]'")
    ->toContain("event.key === 'Escape'")
    ->toContain("document.body.style.overflow = 'hidden'")
    ->toContain('previouslyFocusedElement.focus()');
```

- [ ] **Step 2: Run the test and verify RED**

Run:

```powershell
vendor\bin\pest tests\Feature\SupportCriteriaEvaluationViewTest.php --filter="shared support script" --compact
```

Expected: FAIL because the modal lifecycle functions are absent.

- [ ] **Step 3: Add snapshot and restore helpers**

Implement structured state so dynamic evidence rows can be restored:

```js
const snapshotSupportItem = (item) => ({
    score: item.querySelector('[data-support-score]')?.value ?? '',
    reason: item.querySelector('[data-support-reason]')?.value ?? '',
    evidenceLinks: Array.from(item.querySelectorAll('[data-support-evidence-input]'))
        .map((input) => input.value),
});

const restoreSupportItem = (item, snapshot) => {
    const score = item.querySelector('[data-support-score]');
    const reason = item.querySelector('[data-support-reason]');
    if (score) score.value = snapshot.score;
    if (reason) reason.value = snapshot.reason;

    const container = item.querySelector('[data-support-evidence-container]');
    if (container) {
        container.replaceChildren(...(snapshot.evidenceLinks.length > 0
            ? snapshot.evidenceLinks
            : ['']).map((link) => {
                const row = createEvidenceRow(item.dataset.supportId);
                row.querySelector('[data-support-evidence-input]').value = link;
                return row;
            }));
    }
};
```

- [ ] **Step 4: Add open/close lifecycle and delegated controls**

Track `activeItem`, `activeSnapshot`, `previouslyFocusedElement`, and `previousBodyOverflow`. On open, move the selected item into `[data-support-modal-body]`, update title/sequence, reveal the modal with `flex`, lock body scroll, and focus the score field or first evidence link. On close, optionally restore the snapshot, move the item back to `[data-support-editor-store]`, hide the modal, restore body overflow, recalculate, and restore focus.

Use the exact handler branches:

```js
const manageButton = event.target.closest('[data-support-manage-open]');
if (manageButton) return openSupportModal(manageButton.dataset.supportManageOpen, 'score');

const evidenceButton = event.target.closest('[data-support-evidence-open]');
if (evidenceButton) return openSupportModal(evidenceButton.dataset.supportEvidenceOpen, 'evidence');

if (event.target.closest('[data-support-modal-cancel]')) {
    closeSupportModal({ restore: true });
    return;
}
```

Add overlay and keyboard cancellation:

```js
modal?.addEventListener('click', (event) => {
    if (event.target === modal) closeSupportModal({ restore: true });
});

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && activeItem) closeSupportModal({ restore: true });
});
```

- [ ] **Step 5: Update table/mobile display after Modal save**

Implement `updateSupportRow(item)` to calculate non-empty evidence links, rebuild each evidence-count control, and update management copy:

```js
const updateSupportRow = (item) => {
    const id = item.dataset.supportId;
    const score = item.querySelector('[data-support-score]')?.value.trim() || '';
    const evidenceLinks = Array.from(item.querySelectorAll('[data-support-evidence-input]'))
        .map((input) => input.value.trim())
        .filter(Boolean);

    document.querySelectorAll(`[data-support-evidence-count="${id}"]`).forEach((container) => {
        container.replaceChildren();
        if (evidenceLinks.length === 0) {
            const empty = document.createElement('span');
            empty.className = 'text-slate-400';
            empty.textContent = 'ไม่มีหลักฐาน';
            container.appendChild(empty);
            return;
        }

        const button = document.createElement('button');
        button.type = 'button';
        button.dataset.supportEvidenceOpen = id;
        button.className = 'font-semibold text-blue-700 underline decoration-blue-300 underline-offset-4 focus:outline-none focus:ring-2 focus:ring-blue-400';
        button.textContent = `${evidenceLinks.length} ลิงก์`;
        container.appendChild(button);
    });

    document.querySelectorAll(`[data-support-manage-open="${id}"]`).forEach((button) => {
        button.textContent = score !== '' || evidenceLinks.length > 0 ? 'แก้ไขข้อมูล' : 'กรอกข้อมูล';
    });
};
```

The Modal save handler calls `updateSupportRow(activeItem)`, `window.recalculateSupportScores()`, then `closeSupportModal({ restore: false })`.

- [ ] **Step 6: Run focused tests and frontend build**

Run:

```powershell
vendor\bin\pest tests\Feature\SupportCriteriaEvaluationViewTest.php --compact
npm run build
```

Expected: view/script contracts PASS and Vite build exits 0.

- [ ] **Step 7: Commit Task 2**

```powershell
git add -- resources/views/components/support-criteria-table-script.blade.php tests/Feature/SupportCriteriaEvaluationViewTest.php
git commit -m "feat: manage support criteria in modal"
```

### Task 3: Item Validation, Error Focus, and Role Regression

**Files:**
- Modify: `tests/Feature/SupportCriteriaEvaluationViewTest.php`
- Modify: `resources/views/components/support-criteria-table-script.blade.php`

**Interfaces:**
- Produces: `validateSupportItem(item)` returning `{ errors: string[], firstInvalid: HTMLElement|null }`
- Preserves: `window.validateSupportCriteria()` returning `string[]` for existing evaluatee/reviewer form scripts
- Consumes: `openSupportModal(id, 'error')` and `[data-support-modal-errors]` from Tasks 1–2

- [ ] **Step 1: Write failing validation and role-contract tests**

Add script assertions:

```php
expect($script)
    ->toContain('const validateSupportItem =')
    ->toContain('firstInvalid')
    ->toContain("openSupportModal(firstInvalidItem.dataset.supportId, 'error')")
    ->toContain('data-support-modal-errors');
```

Strengthen reviewer and read-only render tests:

```php
expect($reviewerHtml)
    ->toContain('support_list[7][modification_reason]')
    ->toContain('data-support-manage-open="7"')
    ->toContain('data-support-evidence-open="7"')
    ->toContain('target="_blank"')
    ->toContain('rel="noopener noreferrer"');

expect($readOnlyHtml)
    ->not->toContain('data-support-score')
    ->not->toContain('data-support-modal-save')
    ->toContain('data-support-evidence-open="7"');
```

- [ ] **Step 2: Run tests and verify RED**

Run:

```powershell
vendor\bin\pest tests\Feature\SupportCriteriaEvaluationViewTest.php --compact
```

Expected: FAIL because item-level validation and invalid-item Modal opening are absent.

- [ ] **Step 3: Extract item-level validation from the existing global validator**

Return both messages and the first invalid control:

```js
const validateSupportItem = (item) => {
    const errors = [];
    let firstInvalid = null;
    const rememberInvalid = (element) => { firstInvalid ||= element; };
    const input = item.querySelector('[data-support-score]');
    if (!input) return { errors, firstInvalid };

    const activity = item.dataset.supportActivity || 'เกณฑ์สายสนับสนุน';
    const value = input.value.trim();
    if (value !== '' && (!/^\d+(\.\d{1,2})?$/.test(value) || Number(value) < 0)) {
        errors.push(`ค่าคะแนนที่ได้ของ "${activity}" ต้องเป็นเลขตั้งแต่ 0 และมีทศนิยมไม่เกิน 2 ตำแหน่ง`);
        rememberInvalid(input);
    }

    const evidenceInputs = Array.from(item.querySelectorAll('[data-support-evidence-input]'));
    const evidenceLinks = evidenceInputs.map((field) => field.value.trim()).filter(Boolean);
    const invalidEvidenceInput = evidenceInputs.find((field) => {
        const link = field.value.trim();
        if (link === '') return false;
        try {
            return !['http:', 'https:'].includes(new URL(link).protocol);
        } catch (error) {
            return true;
        }
    });
    if (invalidEvidenceInput) {
        errors.push(`ลิงก์หลักฐานของ "${activity}" ต้องเป็น URL ที่ขึ้นต้นด้วย http:// หรือ https://`);
        rememberInvalid(invalidEvidenceInput);
    }

    if (item.dataset.supportRequired === '1' && evidenceLinks.length === 0) {
        errors.push(`กรุณาแนบหลักฐานสำหรับ "${activity}"`);
        rememberInvalid(item.querySelector('[data-add-support-evidence]') || input);
    }

    const reason = item.querySelector('[data-support-reason]');
    const scoreChanged = normalizeScore(value) !== normalizeScore(input.dataset.supportOriginalScore);
    if (item.dataset.supportRequireReason === '1' && scoreChanged && !reason?.value.trim()) {
        errors.push(`กรุณาระบุเหตุผลการแก้คะแนนของ "${activity}"`);
        rememberInvalid(reason || input);
    }

    return { errors, firstInvalid };
};
```

Modal save renders unique errors, keeps the Modal open, and focuses the invalid control:

```js
const renderModalErrors = (errors) => {
    const container = modal?.querySelector('[data-support-modal-errors]');
    if (!container) return;
    container.replaceChildren();
    container.classList.toggle('hidden', errors.length === 0);
    if (errors.length === 0) return;

    const list = document.createElement('ul');
    list.className = 'list-inside list-disc space-y-1';
    [...new Set(errors)].forEach((message) => {
        const entry = document.createElement('li');
        entry.textContent = message;
        list.appendChild(entry);
    });
    container.appendChild(list);
};

modal?.querySelector('[data-support-modal-save]')?.addEventListener('click', () => {
    if (!activeItem) return;
    const result = validateSupportItem(activeItem);
    renderModalErrors(result.errors);
    if (result.errors.length > 0) {
        result.firstInvalid?.focus();
        return;
    }
    updateSupportRow(activeItem);
    window.recalculateSupportScores();
    closeSupportModal({ restore: false });
});
```

- [ ] **Step 4: Keep the global validation contract and open the first invalid item**

Use:

```js
window.validateSupportCriteria = function validateSupportCriteria() {
    const errors = [];
    let firstInvalidItem = null;

    document.querySelectorAll('[data-support-item]').forEach((item) => {
        const result = validateSupportItem(item);
        errors.push(...result.errors);
        if (!firstInvalidItem && result.errors.length > 0) firstInvalidItem = item;
    });

    if (firstInvalidItem) openSupportModal(firstInvalidItem.dataset.supportId, 'error');
    return errors;
};
```

This keeps both callers in `evaluation-form-script.blade.php` and `evaluatee-evaluation-script.blade.php` unchanged.

- [ ] **Step 5: Run support-flow and role tests**

Run:

```powershell
vendor\bin\pest tests\Feature\SupportCriteriaEvaluationViewTest.php tests\Feature\Evaluation\SupportScoreServiceTest.php tests\Feature\Evaluation\SupportCriteriaReviewerFlowTest.php tests\Feature\Evaluation\EvaluateeTest.php --compact
php artisan view:cache
```

Expected: all listed tests PASS and Blade cache succeeds.

- [ ] **Step 6: Run full verification**

Run:

```powershell
vendor\bin\pest --compact
vendor\bin\pint --test tests\Feature\SupportCriteriaEvaluationViewTest.php
npm run build
git diff --check
```

Expected: Pest has 0 failures, Pint passes, Vite exits 0, and `git diff --check` prints no output.

- [ ] **Step 7: Manually verify the interaction matrix**

Check one report in each mode:

```text
Evaluatee editable: manage button -> edit score/evidence -> save Modal -> table updates -> save draft persists
Evaluatee read-only: no management column -> evidence count opens read-only links
Evaluator/Director/Manager editable: score and reason editable -> evidence read-only -> history visible
Cancel/close/overlay/Esc: score, reason, and dynamic evidence rows return to their pre-open values
Keyboard: focus enters Modal, Esc closes, focus returns to the trigger
Mobile: no trailing form cards; evidence count and management button remain available
```

- [ ] **Step 8: Commit Task 3**

```powershell
git add -- resources/views/components/support-criteria-table-script.blade.php tests/Feature/SupportCriteriaEvaluationViewTest.php
git commit -m "fix: focus invalid support criteria modal"
```

## Completion Criteria

- ตารางและการ์ดมือถือไม่มีฟอร์มยาวต่อท้าย
- หลักฐานแสดงเป็นจำนวนและเปิดดูรายการได้
- คอลัมน์จัดการปรากฏเฉพาะโหมดแก้ไข
- Modal กลางบันทึกเฉพาะค่าในหน้าและไม่ส่ง request เอง
- ยกเลิกทุกวิธีคืนค่าเดิม
- Main form ส่ง payload รูปแบบเดิมและ Server validation เดิมยังทำงาน
- Full PHP suite, Blade cache, Pint ของไฟล์ที่แตะ, Vite build และ diff check ผ่าน
