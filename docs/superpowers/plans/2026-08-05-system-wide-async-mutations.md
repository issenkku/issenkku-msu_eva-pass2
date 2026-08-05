# System-wide Async Mutations Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Make the ten approved in-page mutation areas save, update, copy, import, and delete without a full document refresh while retaining native redirect fallbacks.

**Architecture:** A framework-independent JavaScript transport module owns request/error/duplicate-submit behavior, while focused page coordinators own DOM fragments and UI state. Laravel controllers keep their redirect branches and add JSON branches that return server-rendered Blade fragments plus minimal state, preventing presentation logic from being duplicated in JavaScript.

**Tech Stack:** Laravel/PHP, Blade, vanilla JavaScript ES modules, Node's built-in test runner, Pest/PHPUnit, Vite.

## Global Constraints

- Work only on branch `Jui` and preserve unrelated dirty-worktree changes.
- Do not migrate the covered Blade pages to Vue or Inertia.
- Do not change navigation behavior for authentication, evaluation workflow transitions, exports, file-import preview/confirmation, or dedicated create/edit pages.
- Every covered controller must retain its existing redirect response when the request does not expect JSON.
- A successful in-page mutation must not call `location.reload()`, assign `window.location`, or submit the native form.
- Validation failures keep the active modal/form open and retain entered values.
- The evaluatee workload keeps only the action's dropdown expanded.
- Production changes follow a red-green-refactor cycle; each test must be observed failing for the intended missing behavior before implementation.

---

## File Structure

- `resources/js/async-form.js`: request parsing, typed mutation errors, duplicate-submit coordinator, button restoration.
- `resources/js/async-resource-table.js`: insert/replace/remove server-rendered rows and refresh only a table region when pagination becomes empty.
- `resources/js/app.ts`: imports the two shared modules so Blade coordinators can access their exported browser globals.
- `tests/js/async-form.test.mjs`: transport, validation, malformed response, network error, and duplicate-submit tests.
- `tests/js/async-resource-table.test.mjs`: fragment application, state retention, empty-page fragment refresh, and no-navigation tests.
- `tests/Feature/AsyncMasterDataMutationTest.php`: departments, positions, job levels, and subjects JSON/redirect contracts.
- `tests/Feature/AsyncUserRoleMutationTest.php`: users and roles JSON/redirect contracts.
- `tests/Feature/AsyncIndexMutationTest.php`: assignment deletion and criteria copy/delete contracts.
- `tests/Feature/Evaluation/WorkloadPageAsyncMutationTest.php`: subject creation, previous-cycle import, and aggregate-score JSON contracts.
- `tests/Feature/Settings/WebsiteSettingsAsyncTest.php`: multipart settings JSON response and redirect fallback.
- Existing page scripts/controllers/Blade partials listed in each task: thin coordinators and renderable row/region fragments.

---

### Task 1: Shared async form and resource-table primitives

**Files:**
- Create: `resources/js/async-form.js`
- Create: `resources/js/async-resource-table.js`
- Modify: `resources/js/app.ts`
- Create: `tests/js/async-form.test.mjs`
- Create: `tests/js/async-resource-table.test.mjs`

**Interfaces:**
- Produces: `requestFormMutation(form, fetchImpl = fetch): Promise<object>`.
- Produces: `createAsyncFormCoordinator(options): (SubmitEvent) => Promise<void>` where `options` supplies `form`, `getSubmitButton`, `applySuccess`, `applyValidationErrors`, and `showMessage`.
- Produces: `applyResourceMutation(documentRef, payload, options): void` for `html.row`, `state.id`, and `state.deleted_ids`.
- Produces: `refreshTableRegion(url, selector, fetchImpl = fetch, documentRef = document): Promise<void>`.
- Browser globals: `window.AsyncForm` and `window.AsyncResourceTable` expose the same functions for Blade scripts.

- [ ] **Step 1: Write failing transport and coordinator tests**

```js
test('requestFormMutation sends FormData and requests JSON', async () => {
    const payload = await requestFormMutation(form, async (url, options) => {
        assert.equal(url, form.action);
        assert.equal(options.method, 'POST');
        assert.equal(options.headers.Accept, 'application/json');
        assert.equal(options.headers['X-Requested-With'], 'XMLHttpRequest');
        return jsonResponse(200, { success: true, state: { id: 7 } });
    });
    assert.equal(payload.state.id, 7);
});

test('coordinator blocks duplicate submits and leaves values intact after 422', async () => {
    const pending = deferred();
    const harness = coordinatorHarness(() => pending.promise);
    const first = harness.coordinator(submitEvent());
    const duplicate = harness.coordinator(submitEvent());
    assert.equal(harness.requests, 1);
    pending.reject(Object.assign(new Error('Invalid'), { status: 422, errors: { name: ['Required'] } }));
    await Promise.all([first, duplicate]);
    assert.equal(harness.form.value, 'unchanged');
    assert.equal(harness.button.disabled, false);
    assert.deepEqual(harness.validationErrors, { name: ['Required'] });
});
```

- [ ] **Step 2: Run the new JavaScript tests and verify RED**

Run: `node --test tests/js/async-form.test.mjs tests/js/async-resource-table.test.mjs`

Expected: FAIL because `resources/js/async-form.js` and `resources/js/async-resource-table.js` do not exist.

- [ ] **Step 3: Implement the minimal shared modules**

Implement `AsyncMutationError` with `status`, `errors`, and `payload`; reject redirected/HTML/malformed 2xx responses; always restore the active button in `finally`; and apply DOM mutations only after a valid success payload. `refreshTableRegion` must parse returned HTML with `DOMParser`, replace only the requested selector, and never assign a location.

- [ ] **Step 4: Import the modules from the application entry point**

```ts
import './async-form';
import './async-resource-table';
```

- [ ] **Step 5: Run focused and complete JavaScript tests**

Run: `node --test tests/js/async-form.test.mjs tests/js/async-resource-table.test.mjs`

Expected: PASS.

Run: `npm run test:js`

Expected: PASS with no existing JavaScript regression.

- [ ] **Step 6: Commit the shared primitives**

```bash
git add resources/js/async-form.js resources/js/async-resource-table.js resources/js/app.ts tests/js/async-form.test.mjs tests/js/async-resource-table.test.mjs
git commit -m "feat: add shared async mutation utilities"
```

---

### Task 2: Master-data modal CRUD without refresh

**Files:**
- Create: `tests/Feature/AsyncMasterDataMutationTest.php`
- Modify: `app/Http/Controllers/Setting/DepartmentsController.php`
- Modify: `app/Http/Controllers/Setting/PositionsController.php`
- Modify: `app/Http/Controllers/Setting/JobLevelsController.php`
- Modify: `app/Http/Controllers/Workload/SubjectController.php`
- Create: `resources/views/departments/partials/index-table-row.blade.php`
- Create: `resources/views/positions/partials/index-table-row.blade.php`
- Create: `resources/views/Job Level/partials/index-table-row.blade.php`
- Create: `resources/views/subjects/partials/index-table-row.blade.php`
- Modify: `resources/views/departments/partials/index-table-section.blade.php`
- Modify: `resources/views/positions/partials/index-table-section.blade.php`
- Modify: `resources/views/Job Level/partials/index-table-section.blade.php`
- Modify: `resources/views/subjects/partials/index-table-section.blade.php`
- Modify: `resources/views/departments/partials/index-modal.blade.php`
- Modify: `resources/views/positions/partials/index-modal.blade.php`
- Modify: `resources/views/Job Level/partials/index-modal.blade.php`
- Modify: `resources/views/components/subject-modal.blade.php`
- Modify: `resources/views/departments/partials/index-script.blade.php`
- Modify: `resources/views/positions/partials/index-script.blade.php`
- Modify: `resources/views/Job Level/partials/index-script.blade.php`
- Modify: `resources/views/subjects/partials/index-script.blade.php`
- Modify: `resources/views/components/delete-warning-modal-script.blade.php`
- Modify: `resources/views/components/bulk-delete-script.blade.php`

**Interfaces:**
- Consumes: `window.AsyncForm.createAsyncFormCoordinator` and `window.AsyncResourceTable.applyResourceMutation`.
- Produces JSON create/update payload: `success`, `message`, `html.row`, `state.id`.
- Produces JSON delete/bulk payload: `success`, `message`, `state.deleted_ids`, `state.total`.
- Every table row exposes `data-resource-row` and `data-resource-id`; every replaceable table wrapper exposes `data-async-table-region`.

- [ ] **Step 1: Write parameterized failing feature tests for all four resources**

Use a Pest dataset with explicit route/model/payload entries for `departments`, `positions`, `job-level`, and `subjects`. Assert that JSON create and update responses contain a rendered row and persisted ID, JSON single and bulk deletion return exact deleted IDs, `422` returns an errors map, and the existing non-JSON tests continue to assert redirects.

```php
dataset('async master resources', [
    'departments' => ['departments.store', ['department_name' => 'Async Department']],
    'positions' => ['positions.store', ['name' => 'Async Position']],
    'job levels' => ['job-level.store', ['name' => 'Async Job Level']],
    'subjects' => ['subjects.store', [
        'code' => 'ASYNC101',
        'name_th' => 'Async Subject',
        'credits' => 3,
        'lecture_credits' => 3,
        'lab_credits' => 0,
        'self_study_credits' => 6,
    ]],
]);

$response = $this->actingAs($admin)->postJson(route($routeName), $validPayload);
$response->assertSuccessful()
    ->assertJsonPath('success', true)
    ->assertJsonStructure(['message', 'html' => ['row'], 'state' => ['id']]);
expect($response->json('html.row'))->toContain('data-resource-row');
```

- [ ] **Step 2: Run the feature test and verify RED**

Run: `php artisan test tests/Feature/AsyncMasterDataMutationTest.php`

Expected: FAIL because three controllers redirect for JSON and the table rows do not provide the required fragments/attributes.

- [ ] **Step 3: Extract the four row partials and add stable DOM hooks**

Move each existing loop body into its named row partial without changing visible markup. Pass the model and row index explicitly. Wrap table plus pagination in `data-async-table-region`, and give each row `data-resource-row data-resource-id="{{ $model->id }}"`.

- [ ] **Step 4: Add JSON branches to the four controllers**

After successful persistence, render the correct row partial and return the response contract. After deletion return the deleted IDs and remaining count. Keep every existing redirect/flash branch for normal requests. For subjects, extend the existing JSON branches to use the common envelope and add JSON support to bulk deletion.

- [ ] **Step 5: Replace native modal/delete/bulk submission with page coordinators**

The create/update coordinator must preserve form data on errors, map Laravel field errors, close the modal only on success, and insert/replace the returned row. Shared delete scripts intercept only forms marked `data-async-delete-form` or `data-async-bulk-delete-form`, allowing unrelated workflow forms to retain native behavior.

- [ ] **Step 6: Run RED-to-GREEN verification**

Run: `php artisan test tests/Feature/AsyncMasterDataMutationTest.php tests/Feature/Settings/DepartmentSettingTest.php tests/Feature/Settings/PositionSettingTest.php tests/Feature/Settings/BulkSettingDeleteTest.php`

Expected: PASS.

Run: `npm run test:js`

Expected: PASS.

- [ ] **Step 7: Commit master-data async CRUD**

```bash
git add app/Http/Controllers/Setting app/Http/Controllers/Workload/SubjectController.php resources/views/departments resources/views/positions "resources/views/Job Level" resources/views/subjects resources/views/components/delete-warning-modal-script.blade.php resources/views/components/bulk-delete-script.blade.php tests/Feature/AsyncMasterDataMutationTest.php
git commit -m "feat: update master data without refresh"
```

---

### Task 3: User and role management without refresh

**Files:**
- Create: `tests/Feature/AsyncUserRoleMutationTest.php`
- Modify: `app/Http/Controllers/User/UserController.php`
- Modify: `app/Http/Controllers/Settings/RoleAndPermissionController.php`
- Create: `resources/views/user/management/partials/index-table-row.blade.php`
- Modify: `resources/views/user/management/partials/index-table.blade.php`
- Modify: `resources/views/user/management/partials/user-modal-script.blade.php`
- Modify: `resources/views/user/management/partials/bulk-delete-script.blade.php`
- Modify: `resources/views/user/management/partials/index-toolbar.blade.php`
- Modify: `resources/views/user/role-management/partials/index-table.blade.php`
- Modify: `resources/views/user/role-management/partials/index-table-row.blade.php`
- Modify: `resources/views/user/role-management/partials/index-table-actions.blade.php`
- Modify: `resources/views/user/role-management/partials/index-script.blade.php`

**Interfaces:**
- Consumes the shared async modules and the master-table DOM hooks.
- User create/update returns `html.row`, `state.id`, and normalized role names.
- User bulk status returns `state.updated_ids` and `state.status`; bulk/single delete returns `state.deleted_ids`.
- Role create returns `html.row` and `state.id`; role delete returns `state.deleted_ids`.

- [ ] **Step 1: Write failing feature tests**

Cover JSON user create/update/delete, bulk status, bulk delete, role create/delete, last-admin/dependency error responses, and redirect fallbacks. Assert that import routes are not changed.

- [ ] **Step 2: Run the focused feature test and verify RED**

Run: `php artisan test tests/Feature/AsyncUserRoleMutationTest.php`

Expected: FAIL because the controllers currently return `RedirectResponse` for these actions.

- [ ] **Step 3: Extract the user row and add JSON controller responses**

Keep role synchronization and audit logging inside the existing transactions. Render the user/role rows after relationships are refreshed. Convert validation and protected-user failures into status `422` or `409` JSON for JSON requests while preserving redirect errors otherwise.

- [ ] **Step 4: Connect modal, single-delete, bulk-status, and bulk-delete coordinators**

Remove the second-pass `requestSubmit()` path from the user modal. Keep asynchronous uniqueness validation, then call the shared coordinator. After bulk status, update visible status badges for returned IDs and clear selection. After deletion, remove returned rows and clear selection without changing filters or scroll.

- [ ] **Step 5: Verify focused PHP and JavaScript suites**

Run: `php artisan test tests/Feature/AsyncUserRoleMutationTest.php tests/Feature/User/UserControllerTest.php tests/Feature/UserManagementModalTest.php tests/Feature/RoleManagementControlsTest.php tests/Feature/RoleTableRenderTest.php`

Expected: PASS.

Run: `npm run test:js`

Expected: PASS.

- [ ] **Step 6: Commit user and role mutations**

```bash
git add app/Http/Controllers/User/UserController.php app/Http/Controllers/Settings/RoleAndPermissionController.php resources/views/user/management resources/views/user/role-management tests/Feature/AsyncUserRoleMutationTest.php
git commit -m "feat: manage users and roles without refresh"
```

---

### Task 4: Assignment and criteria index actions without delayed reloads

**Files:**
- Create: `tests/Feature/AsyncIndexMutationTest.php`
- Modify: `app/Http/Controllers/AssignmentDataController.php`
- Modify: `app/Http/Controllers/ReportStructureController.php`
- Modify: `resources/views/assignment-data/partials/index-table-row.blade.php`
- Modify: `resources/views/assignment-data/partials/index-table-section.blade.php`
- Modify: `resources/views/assignment-data/partials/index-script.blade.php`
- Create: `resources/views/criteria_config/partials/index-list-item.blade.php`
- Modify: `resources/views/criteria_config/partials/index-script.blade.php`

**Interfaces:**
- Assignment delete returns `success`, `message`, `state.deleted_ids`, and `state.total`.
- Criteria copy returns `success`, `message`, `html.row`, and `state.id`.
- Criteria delete returns `success`, `message`, and `state.deleted_ids`.

- [ ] **Step 1: Write failing tests for the index response contracts**

Assert assignment JSON deletion has no redirect, criteria copy returns a rendered list item, criteria deletion returns the deleted ID, and non-JSON assignment deletion retains its redirect.

- [ ] **Step 2: Run the test and verify RED**

Run: `php artisan test tests/Feature/AsyncIndexMutationTest.php`

Expected: FAIL on missing/common response fields and missing criteria row fragment.

- [ ] **Step 3: Implement the JSON contracts and stable row hooks**

Reuse the existing assignment row partial. Create `resources/views/criteria_config/partials/index-list-item.blade.php`, use it for the initial/list JSON representation, and return that rendered fragment from copy so there is one server-owned criteria item rendering path.

- [ ] **Step 4: Replace both delayed reload paths**

In assignment deletion remove the row after the toast instead of invoking `location.reload()`. In criteria copy insert the returned item and in criteria delete remove the item; call `fetchCriteriaVersions()` only when the list needs complete reconciliation. Do not call `window.location.reload()`.

- [ ] **Step 5: Verify tests and scan the two scripts**

Run: `php artisan test tests/Feature/AsyncIndexMutationTest.php tests/Feature/Report/AssignmentDataTest.php tests/Feature/CriteriaConfigControlsTest.php`

Expected: PASS.

Run: `rg -n "location\.reload|window\.location\.reload" resources/views/assignment-data/partials/index-script.blade.php resources/views/criteria_config/partials/index-script.blade.php`

Expected: no matches.

- [ ] **Step 6: Commit index mutation changes**

```bash
git add app/Http/Controllers/AssignmentDataController.php app/Http/Controllers/ReportStructureController.php resources/views/assignment-data resources/views/criteria_config/partials/index-script.blade.php tests/Feature/AsyncIndexMutationTest.php
git commit -m "feat: reconcile admin index mutations in place"
```

---

### Task 5: Complete evaluatee workload actions without navigation

**Files:**
- Create: `tests/Feature/Evaluation/WorkloadPageAsyncMutationTest.php`
- Modify: `tests/js/workload-subject-form.test.mjs`
- Modify: `tests/js/workload-entry-submit.test.mjs`
- Modify: `app/Http/Controllers/Workload/SubjectController.php`
- Modify: `app/Http/Controllers/Evaluatee/EvaluationWorkloadController.php`
- Modify: `app/Support/EvaluateeWorkloadLiveData.php`
- Modify: `resources/js/workload-entry-submit.js`
- Modify: `resources/views/evaluatee/partials/workload-actions.blade.php`
- Modify: `resources/views/evaluatee/partials/workload-script-subject-form.blade.php`
- Modify: `resources/views/evaluatee/partials/workload-script-import-previous.blade.php`
- Modify: `resources/views/evaluatee/partials/workload-script-save-reminder.blade.php`

**Interfaces:**
- Subject creation returns `data.subject` with `id`, display label, and selected option HTML.
- Previous-cycle import returns the existing workload live fragments, `total_score`, `copied_entries`, and `active_item_id` when one group is determinable.
- Aggregate-score save returns `saved_total`, `score_c`, `score_d`, and the success message.
- Consumes existing `applyWorkloadEntrySaveResponse(document, payload, activeItemId)` so all workload fragment replacements use the same dropdown restoration logic.

- [ ] **Step 1: Add failing PHP tests for all three remaining workload actions**

Assert JSON subject creation does not redirect and returns the subject option; JSON import returns copied count plus live fragments; JSON aggregate save persists `QuantityScore` and returns saved totals; normal form requests still redirect.

- [ ] **Step 2: Add failing JavaScript tests for modal/dropdown behavior**

```js
test('subject creation keeps the workload modal open and selects the returned subject', async () => {
    await coordinator(submitEvent());
    assert.equal(calls.hideModal, 0);
    assert.equal(subjectSelect.value, '44');
});

test('aggregate save clears the reminder without navigating', async () => {
    await coordinator(submitEvent());
    assert.equal(reminder.hidden, true);
    assert.equal(state.dataset.savedTotal, '18');
    assert.equal(calls.navigate, 0);
});
```

- [ ] **Step 3: Run both suites and verify RED**

Run: `php artisan test tests/Feature/Evaluation/WorkloadPageAsyncMutationTest.php`

Run: `node --test tests/js/workload-subject-form.test.mjs tests/js/workload-entry-submit.test.mjs`

Expected: both fail because subject/import/aggregate forms still use native submission.

- [ ] **Step 4: Add controller JSON branches and live-data payloads**

Use `EvaluateeWorkloadLiveData` after import so panels, summary, total, and active item share the same representation used by entry CRUD. Return aggregate-score state directly after the existing update-or-create operation. Preserve readonly checks and redirect branches.

- [ ] **Step 5: Connect the three forms to async coordinators**

Subject success appends/selects the server option and leaves the entry modal session untouched. Import success applies workload fragments after closing only the confirmation dialog. Aggregate-score success updates `data-saved-total`, clears dirty/reminder state, and keeps the active dropdown expanded.

- [ ] **Step 6: Run workload verification**

Run: `php artisan test tests/Feature/Evaluation/WorkloadPageAsyncMutationTest.php tests/Feature/Evaluation/WorkloadEntryAsyncSaveTest.php tests/Feature/Evaluation/PreviousWorkloadImportTest.php`

Expected: PASS.

Run: `node --test tests/js/workload-subject-form.test.mjs tests/js/workload-entry-submit.test.mjs`

Expected: PASS.

- [ ] **Step 7: Commit workload page completion**

```bash
git add app/Http/Controllers/Workload/SubjectController.php app/Http/Controllers/Evaluatee/EvaluationWorkloadController.php app/Support/EvaluateeWorkloadLiveData.php resources/js/workload-entry-submit.js resources/views/evaluatee/partials tests/Feature/Evaluation/WorkloadPageAsyncMutationTest.php tests/js/workload-subject-form.test.mjs tests/js/workload-entry-submit.test.mjs
git commit -m "feat: complete workload actions without refresh"
```

---

### Task 6: Website settings multipart save without refresh

**Files:**
- Create: `tests/Feature/Settings/WebsiteSettingsAsyncTest.php`
- Create: `tests/js/website-settings.test.mjs`
- Modify: `app/Http/Controllers/Setting/SettingsController.php`
- Create: `resources/js/website-settings.js`
- Modify: `resources/js/app.ts`
- Create: `resources/views/settings/partials/background-library.blade.php`
- Modify: `resources/views/settings/partials/index-form-fields.blade.php`
- Modify: `resources/views/settings/partials/index-info-box.blade.php`
- Modify: `resources/views/settings/index.blade.php`

**Interfaces:**
- JSON response contains `success`, `message`, `data.settings`, `data.logo_url`, `data.background_url`, `html.background_library`, and `html.info`.
- `initializeWebsiteSettings(documentRef, fetchImpl)` binds only forms marked `data-async-settings-form`.

- [ ] **Step 1: Write the failing multipart feature tests**

Use `UploadedFile::fake()->image()` to assert JSON upload/save, selected-library background, pending background deletion, response fragments/URLs, and unchanged redirect fallback.

- [ ] **Step 2: Write failing JavaScript state-reconciliation tests**

Assert the coordinator submits `FormData`, replaces server URLs after local previews, replaces the library/info fragments, clears pending deletion inputs, preserves the page location, restores the button after failure, and calls `URL.revokeObjectURL` for replaced previews.

- [ ] **Step 3: Run both tests and verify RED**

Run: `php artisan test tests/Feature/Settings/WebsiteSettingsAsyncTest.php`

Run: `node --test tests/js/website-settings.test.mjs`

Expected: FAIL because settings currently redirect and the JavaScript module does not exist.

- [ ] **Step 4: Extract settings fragments and return the JSON representation**

After the existing filesystem/database transaction succeeds, reload the persisted setting and background assets, render both fragments, and return the response contract for JSON. Keep file cleanup and redirect/flash behavior unchanged for normal requests.

- [ ] **Step 5: Implement and initialize the settings coordinator**

Move the inline preview/library binding into `website-settings.js`. Rebind library handlers after fragment replacement, update the page background and previews from persisted URLs, revoke local object URLs, clear file and pending-delete fields, and show the shared success/error message.

- [ ] **Step 6: Verify settings behavior**

Run: `php artisan test tests/Feature/Settings/WebsiteSettingsAsyncTest.php tests/Feature/Settings/UniversitySettingTest.php tests/Feature/Settings/SettingAccessTest.php`

Expected: PASS.

Run: `node --test tests/js/website-settings.test.mjs`

Expected: PASS.

Run: `npm run build`

Expected: exit code 0.

- [ ] **Step 7: Commit async website settings**

```bash
git add app/Http/Controllers/Setting/SettingsController.php resources/js/app.ts resources/js/website-settings.js resources/views/settings tests/Feature/Settings/WebsiteSettingsAsyncTest.php tests/js/website-settings.test.mjs
git commit -m "feat: save website settings without refresh"
```

---

### Task 7: System-wide regression and requirement verification

**Files:**
- Modify only files required to resolve regressions introduced by Tasks 1-6.

**Interfaces:**
- Verifies all response contracts and no-refresh UI guarantees from the approved design.

- [ ] **Step 1: Scan covered scripts for forbidden full-page refreshes**

Run:

```bash
rg -n "location\.reload|window\.location\.reload" resources/views/departments resources/views/positions "resources/views/Job Level" resources/views/subjects resources/views/user/management resources/views/user/role-management resources/views/assignment-data resources/views/criteria_config resources/views/evaluatee resources/views/settings resources/js
```

Expected: no match in covered mutation success paths. Intentional navigation outside the covered actions must be documented rather than removed.

- [ ] **Step 2: Run the complete JavaScript suite**

Run: `npm run test:js`

Expected: all tests pass.

- [ ] **Step 3: Run the focused PHP regression set**

Run:

```bash
php artisan test tests/Feature/AsyncMasterDataMutationTest.php tests/Feature/AsyncUserRoleMutationTest.php tests/Feature/AsyncIndexMutationTest.php tests/Feature/Evaluation/WorkloadPageAsyncMutationTest.php tests/Feature/Evaluation/WorkloadEntryAsyncSaveTest.php tests/Feature/Settings/WebsiteSettingsAsyncTest.php
```

Expected: all tests pass.

- [ ] **Step 4: Run the full PHP suite and distinguish unrelated pre-existing failures**

Run: `php artisan test`

Expected: exit code 0. If failures occur only in files already dirty before this work, rerun the focused suite, record exact failing test names and output, and do not modify unrelated user work without authorization.

- [ ] **Step 5: Run formatting checks and production build**

Run: `npm run format:check`

Run: `npm run build`

Run: `git diff --check`

Expected: all commands exit 0.

- [ ] **Step 6: Review the diff against all ten scope items**

Confirm departments, positions, job levels, subjects, users, roles, assignment deletion, criteria copy/delete, evaluatee workload actions, and website settings each have: JSON response, redirect fallback, coordinator, success DOM reconciliation, error preservation, and regression coverage.

- [ ] **Step 7: Commit only any final in-scope regression fixes**

Inspect `git status --short`, stage each in-scope regression file by its literal path, verify the staged diff with `git diff --cached --check`, then run `git commit -m "test: verify async mutation workflows"`. Skip this commit when verification required no additional file changes.
