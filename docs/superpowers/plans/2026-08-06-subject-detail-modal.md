# Subject Detail Modal Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add a read-only subject detail modal that exposes all credit and hour values and explains the tuple shown in the subject table.

**Architecture:** Each subject row carries a complete detail dataset and opens one shared Bootstrap modal through a delegated click handler. A focused Blade partial owns the modal markup, while the existing subject index script populates it without another HTTP request and applies the model's existing hours-first display rule.

**Tech Stack:** Laravel 11, Blade, Bootstrap 5 modal API, Font Awesome, Pest/PHPUnit, Node.js built-in test runner.

## Global Constraints

- Action order is exactly `รายละเอียด`, `แก้ไข`, `ลบ`.
- The detail modal is read-only and contains no save action or editable form controls.
- If any hour is greater than zero, the table-source message uses all three hour values; otherwise it uses all three split-credit values.
- The initial full table and asynchronously rendered row partial must expose the same trigger and dataset.
- Use `bootstrap.Modal.getOrCreateInstance` and delegated event handling so replaced rows continue to work.
- Preserve existing table filters, pagination, reordering, selection, edit, and delete behavior.

---

### Task 1: Render detail actions and the shared read-only modal

**Files:**
- Create: `resources/views/subjects/partials/detail-modal.blade.php`
- Modify: `resources/views/subjects/index.blade.php`
- Modify: `resources/views/subjects/partials/index-table-section.blade.php`
- Modify: `resources/views/subjects/partials/index-table-row.blade.php`
- Modify: `tests/Feature/Subjects/SubjectHoursTest.php`

**Interfaces:**
- Consumes: `Subject::display_component_values` returning `[int, int, int]`.
- Produces: row triggers matching `[data-role="subject-detail-trigger"]` with datasets `code`, `display-name`, `secondary-name`, `credits`, `lecture-credits`, `lab-credits`, `self-study-credits`, `lecture-hours`, `lab-hours`, `self-study-hours`, `is-active`, `display-source`, `display-lecture`, `display-lab`, and `display-self-study`; modal fields matching `[data-subject-detail-*]`.

- [ ] **Step 1: Write the failing rendering test**

Extend the table-renderer test in `tests/Feature/Subjects/SubjectHoursTest.php` with an active subject whose hours are `[0, 2, 0]`. Render both table partials and the new modal include, then assert:

```php
foreach ([$fullTableHtml, $rowHtml] as $html) {
    expect($html)
        ->toContain('data-role="subject-detail-trigger"')
        ->toContain('data-display-source="hours"')
        ->toContain('data-display-lecture="0"')
        ->toContain('data-display-lab="2"')
        ->toContain('data-display-self-study="0"')
        ->toContain('รายละเอียด')
        ->toMatch('/รายละเอียด.*แก้ไข.*ลบ/s');
}

expect($modalHtml)
    ->toContain('id="subjectDetailModal"')
    ->toContain('รายละเอียดรายวิชา')
    ->toContain('ข้อมูลหน่วยกิต')
    ->toContain('ข้อมูลชั่วโมง')
    ->toContain('data-subject-detail-source')
    ->not->toContain('<form')
    ->not->toContain('type="submit"');
```

- [ ] **Step 2: Run the rendering test and verify RED**

Run:

```powershell
$env:DB_DATABASE=':memory:'
php vendor/bin/pest tests/Feature/Subjects/SubjectHoursTest.php
```

Expected: FAIL because the detail trigger and `subjectDetailModal` do not exist.

- [ ] **Step 3: Add a reusable dataset to both row renderers**

In each row partial, derive the display source once:

```blade
@php
    $usesHours = collect([
        $subject->lecture_hours,
        $subject->lab_hours,
        $subject->self_study_hours,
    ])->contains(fn ($value) => (int) $value > 0);
@endphp
```

Insert this button before the edit button, using the existing `x-button` component:

```blade
<x-button
    type="info"
    text="รายละเอียด"
    class="text-sm"
    icon="fas fa-eye"
    data-role="subject-detail-trigger"
    data-code="{{ $subject->code }}"
    data-display-name="{{ $displayName }}"
    data-secondary-name="{{ $secondaryName ?? '' }}"
    data-credits="{{ $subject->credits }}"
    data-lecture-credits="{{ $subject->lecture_credits ?? 0 }}"
    data-lab-credits="{{ $subject->lab_credits ?? 0 }}"
    data-self-study-credits="{{ $subject->self_study_credits ?? 0 }}"
    data-lecture-hours="{{ $subject->lecture_hours ?? 0 }}"
    data-lab-hours="{{ $subject->lab_hours ?? 0 }}"
    data-self-study-hours="{{ $subject->self_study_hours ?? 0 }}"
    data-is-active="{{ $subject->is_active ? 1 : 0 }}"
    data-display-source="{{ $usesHours ? 'hours' : 'credits' }}"
    data-display-lecture="{{ $lectureDisplay }}"
    data-display-lab="{{ $labDisplay }}"
    data-display-self-study="{{ $selfStudyDisplay }}"
    aria-label="ดูรายละเอียด {{ $subject->code }} {{ $displayName }}" />
```

- [ ] **Step 4: Create and include the modal**

Create `resources/views/subjects/partials/detail-modal.blade.php` as a Bootstrap `modal-lg modal-dialog-centered modal-dialog-scrollable modal-fullscreen-sm-down`. Use plain text elements with these stable targets:

```blade
<span data-subject-detail-code-name></span>
<span data-subject-detail-secondary-name></span>
<span data-subject-detail-status></span>
<span data-subject-detail-credits></span>
<span data-subject-detail-lecture-credits></span>
<span data-subject-detail-lab-credits></span>
<span data-subject-detail-self-study-credits></span>
<span data-subject-detail-lecture-hours></span>
<span data-subject-detail-lab-hours></span>
<span data-subject-detail-self-study-hours></span>
<span data-subject-detail-source></span>
```

Use only `data-bs-dismiss="modal"` close controls. Include the partial once in `resources/views/subjects/index.blade.php` after the subject create/edit modal.

- [ ] **Step 5: Run the rendering test and verify GREEN**

Run:

```powershell
$env:DB_DATABASE=':memory:'
php vendor/bin/pest tests/Feature/Subjects/SubjectHoursTest.php tests/Feature/DeleteActionTest.php
```

Expected: PASS with the detail action present in both row renderers and existing delete behavior unchanged.

- [ ] **Step 6: Commit the rendered interface**

```powershell
git add -- resources/views/subjects/index.blade.php resources/views/subjects/partials/detail-modal.blade.php resources/views/subjects/partials/index-table-section.blade.php resources/views/subjects/partials/index-table-row.blade.php tests/Feature/Subjects/SubjectHoursTest.php
git commit -m "feat: render subject detail modal"
```

---

### Task 2: Populate and open the detail modal

**Files:**
- Modify: `resources/views/subjects/partials/index-script.blade.php`
- Create: `tests/js/subject-detail-modal.test.mjs`

**Interfaces:**
- Consumes: the trigger and modal dataset/targets from Task 1.
- Produces: `populateSubjectDetailModal(trigger: HTMLElement, modal: HTMLElement): void`; a delegated document click handler that opens `#subjectDetailModal`.

- [ ] **Step 1: Write the failing JavaScript behavior test**

Load the Blade script, extract `populateSubjectDetailModal`, and provide a trigger fixture with credits `[3, 0, 6]`, hours `[0, 2, 0]`, and `displaySource: 'hours'`. Assert the real DOM targets receive:

```javascript
assert.equal(targets.codeName.textContent, '1499202-3: Environmental Health');
assert.equal(targets.totalCredits.textContent, '3');
assert.equal(targets.labHours.textContent, '2');
assert.equal(targets.source.textContent, 'ค่าที่แสดงในตาราง: ชั่วโมง ( 0 / 2 / 0 )');
assert.equal(targets.status.textContent, 'เปิดใช้งาน');
```

Add a second fixture with all hours zero and assert:

```javascript
assert.equal(targets.source.textContent, 'ค่าที่แสดงในตาราง: หน่วยกิต ( 3 / 0 / 6 )');
```

- [ ] **Step 2: Run the JavaScript test and verify RED**

Run:

```powershell
node --test tests/js/subject-detail-modal.test.mjs
```

Expected: FAIL because `populateSubjectDetailModal` does not exist.

- [ ] **Step 3: Implement the population function**

Add a top-level function to `index-script.blade.php`:

```javascript
function populateSubjectDetailModal(trigger, modal) {
    const value = (name) => trigger.dataset[name] ?? '0';
    const write = (selector, text) => {
        const target = modal.querySelector(selector);
        if (target) target.textContent = text;
    };

    write('[data-subject-detail-code-name]', `${value('code')}: ${value('displayName')}`);
    write('[data-subject-detail-secondary-name]', trigger.dataset.secondaryName ?? '');
    write('[data-subject-detail-credits]', value('credits'));
    write('[data-subject-detail-lecture-credits]', value('lectureCredits'));
    write('[data-subject-detail-lab-credits]', value('labCredits'));
    write('[data-subject-detail-self-study-credits]', value('selfStudyCredits'));
    write('[data-subject-detail-lecture-hours]', value('lectureHours'));
    write('[data-subject-detail-lab-hours]', value('labHours'));
    write('[data-subject-detail-self-study-hours]', value('selfStudyHours'));

    const sourceLabel = value('displaySource') === 'hours' ? 'ชั่วโมง' : 'หน่วยกิต';
    write('[data-subject-detail-source]', `ค่าที่แสดงในตาราง: ${sourceLabel} ( ${value('displayLecture')} / ${value('displayLab')} / ${value('displaySelfStudy')} )`);
    write('[data-subject-detail-status]', value('isActive') === '1' ? 'เปิดใช้งาน' : 'ปิดใช้งาน');
}
```

- [ ] **Step 4: Add delegated modal opening**

In the existing document click listener, handle the detail trigger before the edit trigger:

```javascript
const detailTrigger = event.target.closest('[data-role="subject-detail-trigger"]');
if (detailTrigger) {
    const modal = document.getElementById('subjectDetailModal');
    if (!modal || !window.bootstrap?.Modal) return;
    populateSubjectDetailModal(detailTrigger, modal);
    window.bootstrap.Modal.getOrCreateInstance(modal).show();
    return;
}
```

Set the status badge classes to success for active and secondary for inactive while populating it.

- [ ] **Step 5: Run JavaScript verification**

Run:

```powershell
node --test tests/js/subject-detail-modal.test.mjs
npm run test:js
```

Expected: the new behavior test and the complete JavaScript suite pass.

- [ ] **Step 6: Run PHP regression verification**

Run:

```powershell
$env:DB_DATABASE=':memory:'
php vendor/bin/pest tests/Feature/Subjects/SubjectHoursTest.php tests/Feature/Subjects/SubjectPaginationTest.php tests/Feature/CreateModalContractTest.php tests/Feature/DeleteActionTest.php
```

Expected: PASS with no regression in subject rendering, pagination, create/edit modal, or delete controls.

- [ ] **Step 7: Build frontend assets**

Run:

```powershell
npm run build
```

Expected: Vite exits with code 0.

- [ ] **Step 8: Commit the modal behavior**

```powershell
git add -- resources/views/subjects/partials/index-script.blade.php tests/js/subject-detail-modal.test.mjs
git commit -m "feat: open subject detail modal"
```

---

### Task 3: Final contract verification

**Files:**
- No production files expected.

**Interfaces:**
- Consumes: all Task 1 and Task 2 outputs.
- Produces: fresh verification evidence for the completed feature.

- [ ] **Step 1: Verify formatting and whitespace**

```powershell
vendor/bin/pint tests/Feature/Subjects/SubjectHoursTest.php
git diff --check
```

Expected: both commands exit with code 0.

- [ ] **Step 2: Run the complete feature regression set**

```powershell
$env:DB_DATABASE=':memory:'
php -d memory_limit=512M vendor/bin/pest tests/Feature/Subjects tests/Feature/CreateModalContractTest.php tests/Feature/DeleteActionTest.php tests/Feature/Evaluation/WorkloadPageAsyncMutationTest.php
npm run test:js
npm run build
```

Expected: all commands exit with code 0. Continue using the in-memory database because `database/testing.sqlite` is intentionally excluded per the approved environment decision.

- [ ] **Step 3: Confirm only intended files are committed**

```powershell
git status --short
git log -4 --oneline
```

Expected: the new commits contain only the detail modal files and tests; unrelated pre-existing worktree changes remain unstaged.
