# Preserve Active Workload Dropdown Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** After a successful workload-entry creation, reopen only the workload dropdown whose **เพิ่มข้อมูล** button launched the modal.

**Architecture:** Blade exposes the quantity-sub-criteria item ID on each `<details>` and the existing add-button click handler records the matching origin ID. The async submit coordinator snapshots that ID at submission time and passes it to the response helper, which restores exactly one matching dropdown after replacing the panel HTML.

**Tech Stack:** Laravel 11, Blade, JavaScript ES modules, Node test runner, Vite 6

## Global Constraints

- Use the quantity-sub-criteria item ID, not DOM position or workload-form-item ID.
- Reopen only the originating create dropdown; edit behavior remains unchanged.
- Unknown or absent origin IDs must not open an arbitrary dropdown.
- Failure paths must not replace panel HTML or change dropdown state.
- Preserve existing async save, validation, modal-session, total-update, and non-JavaScript fallback behavior.
- Preserve unrelated working-tree changes.

## File Map

- Modify `resources/views/evaluatee/partials/workload-group-panels.blade.php`: expose stable item ID on `<details>`.
- Modify `resources/views/evaluatee/partials/workload-script-entry-modal.blade.php`: record the last add-button item ID.
- Modify `resources/views/evaluatee/partials/workload-script-entry-submit.blade.php`: provide the create-only origin ID to the coordinator and response helper.
- Modify `resources/js/workload-entry-submit.js`: snapshot submission context and restore the matching dropdown after HTML replacement.
- Modify `tests/js/workload-entry-submit.test.mjs`: behaviorally verify context capture and dropdown restoration.

---

### Task 1: Preserve the Originating Dropdown

**Files:**
- Modify: `resources/views/evaluatee/partials/workload-group-panels.blade.php`
- Modify: `resources/views/evaluatee/partials/workload-script-entry-modal.blade.php`
- Modify: `resources/views/evaluatee/partials/workload-script-entry-submit.blade.php`
- Modify: `resources/js/workload-entry-submit.js`
- Test: `tests/js/workload-entry-submit.test.mjs`

**Interfaces:**
- Consumes: `data-item-id` from `.workload-add-btn`
- Produces: `data-workload-item-id` on `.workload-item-dropdown`
- Extends: `createWorkloadEntrySubmitCoordinator(options)` with optional `getDropdownItemId(): string`
- Extends: `options.applyResponse(payload, { dropdownItemId: string })`
- Extends: `applyWorkloadEntrySaveResponse(documentRef, payload, dropdownItemId = '')`

- [ ] **Step 1: Write failing response-helper tests**

Add a fake panels region containing dropdown objects with IDs `11`, `12`, and `13`. Call:

```javascript
applyWorkloadEntrySaveResponse(documentRef, payload, '12');
```

Assert dropdown `12` has `open === true` and `11`/`13` have `open === false`. Add another case with `''` and `999`; assert all dropdowns remain closed.

- [ ] **Step 2: Write a failing coordinator-context test**

Extend the existing behavioral harness with `getDropdownItemId` and make `applyResponse` capture its second argument. Start a submission with ID `12`, change the harness ID to `13` before resolving the request, and assert the applied context remains `{ dropdownItemId: '12' }`. This proves the context is snapshotted at submission time.

- [ ] **Step 3: Write a failing Blade contract test**

Read `workload-group-panels.blade.php`, `workload-script-entry-modal.blade.php`, and `workload-script-entry-submit.blade.php`. Assert they contain:

```text
data-workload-item-id="{{ $itemView['id'] }}"
lastDefaultItemId = btn.dataset.itemId || ''
getDropdownItemId
methodField.value === 'PUT'
```

The edit-mode expression must return an empty string; create mode returns the recorded add-button ID.

- [ ] **Step 4: Run tests to verify RED**

```powershell
node --test tests/js/workload-entry-submit.test.mjs
```

Expected: FAIL because dropdown restoration and submission context do not exist.

- [ ] **Step 5: Add the stable Blade hook and origin state**

Render:

```blade
<details
    class="workload-item-dropdown"
    data-workload-item-id="{{ $itemView['id'] }}"
>
```

Declare `let lastDefaultItemId = '';` beside the existing default modal state. In the delegated `.workload-add-btn` click handler assign:

```javascript
lastDefaultItemId = btn.dataset.itemId || '';
```

- [ ] **Step 6: Snapshot and forward the context**

In `createWorkloadEntrySubmitCoordinator`, immediately after capturing the modal session, capture:

```javascript
const submissionDropdownItemId = options.getDropdownItemId?.() ?? '';
```

Call:

```javascript
options.applyResponse(payload, { dropdownItemId: submissionDropdownItemId });
```

In the Blade coordinator options, return `''` for edit mode and `lastDefaultItemId` for create mode:

```javascript
getDropdownItemId: function () {
    return methodField && methodField.value === 'PUT' ? '' : lastDefaultItemId;
},
applyResponse: function (payload, context) {
    window.WorkloadEntrySubmit.applyWorkloadEntrySaveResponse(
        document,
        payload,
        context.dropdownItemId
    );
},
```

- [ ] **Step 7: Restore exactly one dropdown after panel replacement**

Extend the helper signature and, after assigning `panelsRegion.innerHTML`, run:

```javascript
const normalizedItemId = String(dropdownItemId || '');
panelsRegion.querySelectorAll('[data-workload-item-id]').forEach((dropdown) => {
    dropdown.open = normalizedItemId !== ''
        && String(dropdown.dataset.workloadItemId || '') === normalizedItemId;
});
```

This explicitly closes non-matching dropdowns and opens none for an absent/unknown ID.

- [ ] **Step 8: Run focused tests to verify GREEN**

```powershell
node --test tests/js/workload-entry-submit.test.mjs
php vendor/bin/pest tests/Feature/Evaluation/WorkloadEntryAsyncSaveTest.php --compact
```

Expected: all tests PASS.

- [ ] **Step 9: Commit Task 1**

```powershell
git add -- resources/views/evaluatee/partials/workload-group-panels.blade.php resources/views/evaluatee/partials/workload-script-entry-modal.blade.php resources/views/evaluatee/partials/workload-script-entry-submit.blade.php resources/js/workload-entry-submit.js tests/js/workload-entry-submit.test.mjs
git commit -m "fix: preserve active workload dropdown"
```

---

### Task 2: Regression Verification

**Files:** Verify only.

**Interfaces:** Produces final test/build evidence without modifying files.

- [ ] **Step 1: Run the complete JavaScript suite**

```powershell
npm run test:js
```

- [ ] **Step 2: Run focused workload PHP tests**

```powershell
php vendor/bin/pest tests/Feature/Evaluation/WorkloadEntryAsyncSaveTest.php tests/Feature/Evaluation/PreviousWorkloadImportTest.php tests/Feature/UatAuditLogTest.php --compact
```

- [ ] **Step 3: Build production assets**

```powershell
npm run build
```

- [ ] **Step 4: Check formatting and scope**

```powershell
npx prettier --check resources/js/workload-entry-submit.js tests/js/workload-entry-submit.test.mjs
git diff --check
git status --short -- resources/views/evaluatee/partials/workload-group-panels.blade.php resources/views/evaluatee/partials/workload-script-entry-modal.blade.php resources/views/evaluatee/partials/workload-script-entry-submit.blade.php resources/js/workload-entry-submit.js tests/js/workload-entry-submit.test.mjs
```

Expected: all commands exit `0`; feature files have no uncommitted changes.
