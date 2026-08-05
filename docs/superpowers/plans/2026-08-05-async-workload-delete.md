# Async Workload Delete Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Delete workload entries through the existing confirmation modal without page navigation, refresh the server-rendered workload fragments and total, and keep only the deleted row's dropdown open.

**Architecture:** Extend the workload destroy endpoint with the same JSON live-update contract used by create and edit while preserving its redirect fallback. Add a workload-specific delete coordinator to the existing workload JavaScript module; the Blade initializer captures the stable item ID, intercepts only the workload delete form, and delegates panel restoration to `applyWorkloadEntrySaveResponse`.

**Tech Stack:** Laravel 11 controllers and Blade, Fetch API, Bootstrap modal, Node test runner, Pest, Vite.

## Global Constraints

- Preserve the existing shared delete confirmation modal and confirmation step.
- Apply async deletion only to workload entries on the evaluatee workload page.
- On success, open only the dropdown containing the deleted row.
- On failure, preserve the current panels and restore the delete button.
- Keep ordinary non-JSON deletion as a redirect with flash messaging.
- Do not change create, edit, or deletion behavior on other pages.

---

### Task 1: Return Live Workload Data from JSON Delete

**Files:**
- Modify: `app/Http/Controllers/Evaluatee/EvaluateeWorkloadEntryController.php`
- Test: `tests/Feature/Evaluation/WorkloadEntryAsyncSaveTest.php`

**Interfaces:**
- Consumes: `EvaluateeWorkloadLiveData::build(QuantitySubCriteria $criteria, Collection $forms, int $reportId): array` and the existing workload panel/summary partials.
- Produces: JSON `{ message: string, panels_html: string, summary_html: string, total_score: number, active_item_id: int|null }` for successful delete; JSON `{ message: string }` with HTTP 404 for a missing entry; unchanged redirect fallback for ordinary requests.

- [ ] **Step 1: Write failing feature tests for successful JSON deletion**

Import `App\Models\EvidenceAnswer`, return `evaluationList` from `workloadAsyncSaveContext()`, then add a test that creates two entries under the context form, attaches evidence to the deleted entry, calls `deleteJson`, and asserts the response and database state:

```php
test('JSON delete removes the entry and returns refreshed workload fragments', function () {
    ['evaluatee' => $evaluatee, 'evaluationList' => $evaluationList, 'report' => $report, 'form' => $form, 'item' => $item] = workloadAsyncSaveContext();
    $deletedEntry = WorkloadEntry::create([
        ...workloadAsyncSavePayload($report, $form),
        'calculated_score' => 6,
    ]);
    $remainingEntry = WorkloadEntry::create([
        ...workloadAsyncSavePayload($report, $form, 4, 3),
        'calculated_score' => 12,
    ]);
    EvidenceAnswer::create([
        'evaluation_list_id' => $evaluationList->id,
        'report_id' => $report->id,
        'workload_entry_id' => $deletedEntry->id,
        'link' => 'https://example.test/deleted-evidence',
    ]);
    Sanctum::actingAs($evaluatee);

    $response = $this->deleteJson(route('evaluatee.workload-entries.destroy', $deletedEntry->id));

    $response
        ->assertOk()
        ->assertJsonStructure(['message', 'panels_html', 'summary_html', 'total_score', 'active_item_id'])
        ->assertJsonPath('total_score', 12)
        ->assertJsonPath('active_item_id', $item->id);
    expect($response->json('panels_html'))->toContain('12.00')
        ->and(WorkloadEntry::find($deletedEntry->id))->toBeNull()
        ->and(WorkloadEntry::find($remainingEntry->id))->not->toBeNull()
        ->and(EvidenceAnswer::where('workload_entry_id', $deletedEntry->id)->exists())->toBeFalse();
});
```

- [ ] **Step 2: Write failing feature tests for fallback and JSON not-found behavior**

```php
test('redirect fallback keeps the existing delete response', function () {
    ['evaluatee' => $evaluatee, 'report' => $report, 'form' => $form] = workloadAsyncSaveContext();
    $entry = WorkloadEntry::create([
        ...workloadAsyncSavePayload($report, $form),
        'calculated_score' => 6,
    ]);
    Sanctum::actingAs($evaluatee);

    $this->delete(route('evaluatee.workload-entries.destroy', $entry->id))
        ->assertRedirect()
        ->assertSessionHas('success');
});

test('JSON delete returns not found without following a redirect', function () {
    ['evaluatee' => $evaluatee] = workloadAsyncSaveContext();
    Sanctum::actingAs($evaluatee);

    $this->deleteJson(route('evaluatee.workload-entries.destroy', 999999999))
        ->assertNotFound()
        ->assertJsonStructure(['message']);
});
```

- [ ] **Step 3: Run the feature tests to verify RED**

Run:

```powershell
php vendor/bin/pest tests/Feature/Evaluation/WorkloadEntryAsyncSaveTest.php --filter="JSON delete|redirect fallback keeps the existing delete" --compact
```

Expected: JSON success test receives a redirect or non-JSON body, and the JSON not-found test receives a redirect instead of HTTP 404.

- [ ] **Step 4: Generalize the live mutation response and implement JSON delete**

Replace the entry-dependent response helper with a report-ID-based helper:

```php
private function successfulMutationResponse(
    Request $request,
    int $reportId,
    WorkloadForm $form,
    string $message,
    ?int $activeItemId,
): RedirectResponse|JsonResponse
```

Update create and edit to pass `(int) $entry->report_id`. In `destroy`, load the form before deleting, capture its item ID, delete evidence and the entry, and return:

```php
return $this->successfulMutationResponse(
    request(),
    (int) $entry->report_id,
    $form,
    'ลบข้อมูลภาระงานเรียบร้อยแล้ว',
    $form->quantity_sub_criteria_item_id ? (int) $form->quantity_sub_criteria_item_id : null,
);
```

Change `destroy` to return `RedirectResponse|JsonResponse`. In the `ModelNotFoundException` handler, return `response()->json(['message' => $message], 404)` when `request()->expectsJson()`; otherwise keep the redirect fallback.

- [ ] **Step 5: Run the feature tests to verify GREEN**

Run:

```powershell
php vendor/bin/pest tests/Feature/Evaluation/WorkloadEntryAsyncSaveTest.php --compact
```

Expected: all tests in the file pass, including create, edit, delete, redirect fallback, and not-found cases.

- [ ] **Step 6: Commit the server contract**

```powershell
git add app/Http/Controllers/Evaluatee/EvaluateeWorkloadEntryController.php tests/Feature/Evaluation/WorkloadEntryAsyncSaveTest.php
git commit -m "feat: return live workload data after deletion"
```

---

### Task 2: Submit Workload Delete Without Navigation

**Files:**
- Modify: `resources/js/workload-entry-submit.js`
- Modify: `resources/views/evaluatee/partials/workload-row-actions.blade.php`
- Create: `resources/views/evaluatee/partials/workload-script-entry-delete.blade.php`
- Modify: `resources/views/evaluatee/partials/workload-scripts.blade.php`
- Modify: `resources/views/evaluatee/partials/workload-script-save-reminder.blade.php`
- Test: `tests/js/workload-entry-submit.test.mjs`

**Interfaces:**
- Consumes: `requestWorkloadEntrySave(form, fetchImpl)`, `applyWorkloadEntrySaveResponse(documentRef, payload, dropdownItemId)`, Bootstrap `Modal.getOrCreateInstance`, `#deleteForm`, and `data-workload-item-id`.
- Produces: `createWorkloadEntryDeleteCoordinator(options): (event: SubmitEvent) => Promise<void>`, exposed as `window.WorkloadEntrySubmit.createWorkloadEntryDeleteCoordinator`.

- [ ] **Step 1: Write failing JavaScript tests for async delete success and duplicate protection**

Import `createWorkloadEntryDeleteCoordinator`. Build a harness with a pending request and assert:

```js
test('delete coordinator prevents navigation, blocks duplicates, and applies success', async () => {
    const pending = deferred();
    const calls = { apply: [], hide: 0, requests: 0, messages: [] };
    const button = { disabled: false, textContent: 'Delete' };
    const coordinator = createWorkloadEntryDeleteCoordinator({
        applyResponse(payload) { calls.apply.push(payload); },
        form: { dataset: { workloadItemId: '12' } },
        getSubmitButton: () => button,
        hideModal() { calls.hide += 1; },
        requestDelete: async () => { calls.requests += 1; return pending.promise; },
        showMessage(message, isError) { calls.messages.push({ message, isError }); },
    });
    const first = submitEvent();
    const duplicate = submitEvent();

    const submission = coordinator(first);
    const duplicateSubmission = coordinator(duplicate);
    pending.resolve({ message: 'Deleted', panels_html: '', summary_html: '', total_score: 0, active_item_id: 12 });
    await Promise.all([submission, duplicateSubmission]);

    assert.equal(first.prevented, 1);
    assert.equal(duplicate.prevented, 1);
    assert.equal(calls.requests, 1);
    assert.equal(calls.hide, 1);
    assert.equal(button.disabled, false);
    assert.deepEqual(calls.apply[0].active_item_id, 12);
});
```

- [ ] **Step 2: Write a failing JavaScript test for delete failure**

```js
test('delete coordinator preserves the page and restores controls after failure', async () => {
    const error = Object.assign(new Error('Delete failed'), { status: 500 });
    const calls = { apply: 0, hide: 0, messages: [] };
    const button = { disabled: false, textContent: 'Delete' };
    const coordinator = createWorkloadEntryDeleteCoordinator({
        applyResponse() { calls.apply += 1; },
        form: { dataset: { workloadItemId: '12' } },
        getSubmitButton: () => button,
        hideModal() { calls.hide += 1; },
        requestDelete: async () => { throw error; },
        showMessage(message, isError) { calls.messages.push({ message, isError }); },
    });

    await coordinator(submitEvent());

    assert.equal(calls.apply, 0);
    assert.equal(calls.hide, 0);
    assert.equal(button.disabled, false);
    assert.equal(calls.messages.at(-1).isError, true);
});
```

- [ ] **Step 3: Extend the view contract test**

Read the row actions and new delete initializer partial, then assert the stable ID and coordinator wiring:

```js
assert.match(rowActions, /data-workload-item-id/);
assert.match(entryDelete, /createWorkloadEntryDeleteCoordinator/);
assert.match(entryDelete, /event\.target\.closest\('\[data-delete-trigger\]'\)/);
assert.match(entryDelete, /applyWorkloadEntrySaveResponse/);
```

- [ ] **Step 4: Run the JavaScript tests to verify RED**

Run:

```powershell
node --test tests/js/workload-entry-submit.test.mjs
```

Expected: import failure because `createWorkloadEntryDeleteCoordinator` is not exported, or assertions fail because the coordinator and view wiring do not exist.

- [ ] **Step 5: Implement the delete coordinator**

Add `createWorkloadEntryDeleteCoordinator(options)` to `resources/js/workload-entry-submit.js`. It must always call `event.preventDefault()`, reject duplicate submissions while one is pending, snapshot the current submit button label, disable the button, await `options.requestDelete(options.form)`, call `options.applyResponse(payload)`, hide the modal, and show success. On error it must skip apply/hide and show an error; in `finally` it restores the button and clears the pending flag. Export it on `window.WorkloadEntrySubmit`.

Use the existing `requestWorkloadEntrySave` as `requestDelete`; the shared delete form already contains `_method=DELETE`, so the request remains a POST transport with the complete `FormData`, JSON headers, and the existing strict response validation. Map request failures to the delete-specific fallback text `ไม่สามารถลบข้อมูลได้ กรุณาลองใหม่อีกครั้ง` before calling `showMessage`.

- [ ] **Step 6: Add stable context to delete buttons**

In `workload-row-actions.blade.php`, add:

```blade
data-workload-item-id="{{ $itemView['id'] ?? '' }}"
```

The partial is included inside the `$itemView` loop, so this ID matches `data-workload-item-id` on its containing `<details>`.

- [ ] **Step 7: Create the workload-specific delete initializer**

Create `workload-script-entry-delete.blade.php` with one `DOMContentLoaded` handler. Register a capture-phase click listener that copies the clicked workload trigger's `data-workload-item-id` onto `#deleteForm.dataset.workloadItemId` before the shared delete listener opens the modal. Attach the coordinator to `#deleteForm` and wire:

```js
requestDelete: window.WorkloadEntrySubmit.requestWorkloadEntrySave,
applyResponse: function (payload) {
    window.WorkloadEntrySubmit.applyWorkloadEntrySaveResponse(
        document,
        payload,
        deleteForm.dataset.workloadItemId || ''
    );
},
hideModal: function () {
    bootstrap.Modal.getOrCreateInstance(deleteModalEl).hide();
},
showMessage: function (message, isError) {
    window.showWorkloadSaveMessage(message, isError);
},
```

Expose the existing `showWorkloadSaveMessage` as `window.showWorkloadSaveMessage` in the entry submit partial, and include the new delete initializer after `workload-script-entry-modal` in `workload-scripts.blade.php`. In `workload-script-save-reminder.blade.php`, treat `#deleteForm[data-workload-item-id]` like `#workloadEntryForm` so its intercepted async submit does not set `allowPageExit`.

- [ ] **Step 8: Run focused tests to verify GREEN**

Run:

```powershell
node --test tests/js/workload-entry-submit.test.mjs
php vendor/bin/pest tests/Feature/Evaluation/WorkloadEntryAsyncSaveTest.php --compact
```

Expected: all focused JavaScript and Laravel tests pass.

- [ ] **Step 9: Run full scoped verification**

Run:

```powershell
npm run test:js
php vendor/bin/pest tests/Feature/Evaluation/WorkloadEntryAsyncSaveTest.php tests/Feature/Evaluation/PreviousWorkloadImportTest.php tests/Feature/UatAuditLogTest.php --compact
php vendor/bin/pint --test app/Http/Controllers/Evaluatee/EvaluateeWorkloadEntryController.php tests/Feature/Evaluation/WorkloadEntryAsyncSaveTest.php
npx prettier --check resources/js/workload-entry-submit.js tests/js/workload-entry-submit.test.mjs
npm run build
git diff --check -- app/Http/Controllers/Evaluatee/EvaluateeWorkloadEntryController.php resources/js/workload-entry-submit.js resources/views/evaluatee/partials/workload-row-actions.blade.php resources/views/evaluatee/partials/workload-script-entry-delete.blade.php resources/views/evaluatee/partials/workload-script-entry-submit.blade.php resources/views/evaluatee/partials/workload-script-save-reminder.blade.php resources/views/evaluatee/partials/workload-scripts.blade.php tests/Feature/Evaluation/WorkloadEntryAsyncSaveTest.php tests/js/workload-entry-submit.test.mjs
```

Expected: all commands exit `0`; JavaScript and PHP suites report zero failures; Vite produces a production bundle.

- [ ] **Step 10: Commit the client behavior**

```powershell
git add resources/js/workload-entry-submit.js resources/views/evaluatee/partials/workload-row-actions.blade.php resources/views/evaluatee/partials/workload-script-entry-delete.blade.php resources/views/evaluatee/partials/workload-script-entry-submit.blade.php resources/views/evaluatee/partials/workload-script-save-reminder.blade.php resources/views/evaluatee/partials/workload-scripts.blade.php tests/js/workload-entry-submit.test.mjs
git commit -m "feat: delete workload entries without refresh"
```
