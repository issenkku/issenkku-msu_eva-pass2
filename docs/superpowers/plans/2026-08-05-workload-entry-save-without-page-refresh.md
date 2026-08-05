# Workload Entry Save Without Page Refresh Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Save newly created and edited evaluatee workload entries without reloading the page, then refresh the visible rows and totals immediately.

**Architecture:** The form submits `FormData` with `fetch` and requests JSON. JSON saves return server-rendered workload panel and summary fragments built from the same read model as the full page; ordinary submissions retain redirects.

**Tech Stack:** Laravel 11, Blade, Fetch API, Bootstrap modal API, Pest 3, Node test runner, Vite 6

## Global Constraints

- Preserve CSRF, `_method`, authorization, request validation, scoring, evidence persistence, and non-JavaScript redirects.
- Support create and edit; keep the modal and its values on any failure.
- Block duplicate requests while saving.
- Do not alter delete, subject creation, previous-workload import, or aggregate score saving.
- Preserve unrelated working-tree changes.

## File Map

- Create `app/Support/EvaluateeWorkloadLiveData.php` for the shared workload read model.
- Modify both evaluatee workload controllers for shared initial data and JSON fragments.
- Add stable live-region wrappers in `evaluation-workload.blade.php`.
- Create `resources/js/workload-entry-submit.js` for testable Fetch/DOM helpers and import it from `app.ts`.
- Modify the inline submit coordinator.
- Add focused Pest and Node tests.

---

### Task 1: Shared Live Workload Read Model

**Files:**
- Create: `app/Support/EvaluateeWorkloadLiveData.php`
- Modify: `app/Http/Controllers/Evaluatee/EvaluationWorkloadController.php:75-129`
- Test: `tests/Feature/Evaluation/WorkloadEntryAsyncSaveTest.php`

**Interfaces:**
- Consumes: `EvaluateeWorkloadLiveData::build(QuantitySubCriteria $quantitySubCriteria, Collection $workloadForms, int $reportId): array`
- Produces: `workloadEntriesByFormId`, `evidenceLinksByEntryId`, `workloadTotalScore`, `workloadView`

- [ ] **Step 1: Write the failing read-model test**

Create an assigned evaluatee, draft report, quantity sub-criterion with one group/item, a form with `hours` and `rate`, and an entry scoring `6`. Assert:

```php
$liveData = EvaluateeWorkloadLiveData::build(
    $quantitySubCriteria->load('groups.items'),
    collect([$form->load(['fields', 'items', 'subCriteriaItem.group'])]),
    $report->id,
);

expect($liveData['workloadTotalScore'])->toBe(6.0)
    ->and($liveData['workloadView']['total_display'])->toBe('6.00')
    ->and($liveData['workloadView']['groups']->first()['items']->first()['rows'])->toHaveCount(1);
```

- [ ] **Step 2: Verify RED**

```powershell
php vendor/bin/pest tests/Feature/Evaluation/WorkloadEntryAsyncSaveTest.php --filter="builds live workload data" --compact
```

Expected: FAIL because `EvaluateeWorkloadLiveData` does not exist.

- [ ] **Step 3: Implement the builder**

```php
public static function build(QuantitySubCriteria $quantitySubCriteria, Collection $workloadForms, int $reportId): array
{
    $formIds = $workloadForms->pluck('id')->filter()->unique()->values();
    $entries = $formIds->isEmpty() ? collect() : WorkloadEntry::with('subject')
        ->where('report_id', $reportId)
        ->whereIn('workload_form_id', $formIds)
        ->orderByDesc('created_at')->get();
    $entriesByForm = $entries->groupBy('workload_form_id');
    $total = (float) $entries->sum(fn ($entry) => max(0, (float) ($entry->calculated_score ?? 0)));
    $evidence = $quantitySubCriteria->evaluation_list_id
        ? EvidenceAnswer::where('report_id', $reportId)
            ->where('evaluation_list_id', $quantitySubCriteria->evaluation_list_id)
            ->orderByDesc('created_at')->get()->groupBy('workload_entry_id')
            ->map(fn ($answers) => $answers->pluck('link')->filter()->values())
        : collect();

    return [
        'workloadEntriesByFormId' => $entriesByForm,
        'evidenceLinksByEntryId' => $evidence,
        'workloadTotalScore' => $total,
        'workloadView' => EvaluateeWorkloadViewData::build(
            $quantitySubCriteria, $workloadForms, $entriesByForm, $evidence
        ),
    ];
}
```

- [ ] **Step 4: Reuse the builder on the initial page**

Replace the duplicated entry/evidence/view block in `EvaluationWorkloadController::index` with a default empty array and:

```php
if ($reportId && $quantitySubCriteria) {
    $liveData = EvaluateeWorkloadLiveData::build(
        $quantitySubCriteria,
        $workloadForms,
        (int) $reportId,
    );
}
```

Map its four keys back to the existing view variable names. Retain saved-score and modal queries unchanged.

- [ ] **Step 5: Verify GREEN**

```powershell
php vendor/bin/pest tests/Feature/Evaluation/WorkloadEntryAsyncSaveTest.php --filter="builds live workload data" --compact
php vendor/bin/pest tests/Feature/Evaluation/PreviousWorkloadImportTest.php --compact
```

Expected: PASS.

- [ ] **Step 6: Commit**

```powershell
git add -- app/Support/EvaluateeWorkloadLiveData.php app/Http/Controllers/Evaluatee/EvaluationWorkloadController.php tests/Feature/Evaluation/WorkloadEntryAsyncSaveTest.php
git commit -m "refactor: share evaluatee workload live data"
```

---

### Task 2: JSON Save Response and Live Regions

**Files:**
- Modify: `app/Http/Controllers/Evaluatee/EvaluateeWorkloadEntryController.php:20-165`
- Modify: `resources/views/evaluatee/evaluation-workload.blade.php:20-34`
- Test: `tests/Feature/Evaluation/WorkloadEntryAsyncSaveTest.php`

**Interfaces:**
- Produces JSON `{ message, panels_html, summary_html, total_score }`
- Produces DOM hooks `#workloadPanelsLiveRegion` and `#workloadSummaryLiveRegion`

- [ ] **Step 1: Write failing create/fallback tests**

Use the Task 1 context. Assert `postJson` returns status 200, total `6`, string fragments, and saved row content:

```php
$response = $this->actingAs($evaluatee)
    ->postJson(route('evaluatee.workload-entries.store'), $payload)
    ->assertOk()
    ->assertJsonPath('total_score', 6)
    ->assertJsonStructure(['message', 'panels_html', 'summary_html', 'total_score']);

expect($response->json('panels_html'))->toContain('6.00')
    ->and($response->json('summary_html'))->toContain('6.00');
```

In another test, submit without JSON headers and retain `->assertRedirect()`.

- [ ] **Step 2: Write a failing update test**

Create one entry scoring `6`, `putJson` values scoring `12`, then assert `total_score === 12`, fragments include `12.00`, and the database still has one entry.

- [ ] **Step 3: Verify RED**

```powershell
php vendor/bin/pest tests/Feature/Evaluation/WorkloadEntryAsyncSaveTest.php --filter="JSON|redirect fallback" --compact
```

Expected: FAIL because saves redirect.

- [ ] **Step 4: Add stable wrappers**

```blade
<div id="workloadPanelsLiveRegion">
    @include('evaluatee.partials.workload-group-panels')
</div>
<div id="workloadSummaryLiveRegion">
    @include('evaluatee.partials.workload-summary-panel', [
        'totalDisplay' => $workloadView['total_display'] ?? '-',
    ])
</div>
```

- [ ] **Step 5: Return JSON fragments only when requested**

Add `successfulSaveResponse(Request $request, WorkloadEntry $entry, WorkloadForm $form, string $message): RedirectResponse|JsonResponse`. Its non-JSON branch returns the existing redirect. Its JSON branch reloads the quantity sub-criterion and forms, calls `EvaluateeWorkloadLiveData::build`, and returns:

```php
return response()->json([
    'message' => $message,
    'panels_html' => view('evaluatee.partials.workload-group-panels', [
        'workloadView' => $liveData['workloadView'],
        'readonly' => false,
    ])->render(),
    'summary_html' => view('evaluatee.partials.workload-summary-panel', [
        'totalDisplay' => $liveData['workloadView']['total_display'],
    ])->render(),
    'total_score' => $liveData['workloadTotalScore'],
]);
```

Call it from successful create and update paths. Do not alter failure, persistence, or audit behavior.

- [ ] **Step 6: Verify GREEN**

```powershell
php vendor/bin/pest tests/Feature/Evaluation/WorkloadEntryAsyncSaveTest.php tests/Feature/UatAuditLogTest.php --compact
```

Expected: PASS.

- [ ] **Step 7: Commit**

```powershell
git add -- app/Http/Controllers/Evaluatee/EvaluateeWorkloadEntryController.php resources/views/evaluatee/evaluation-workload.blade.php tests/Feature/Evaluation/WorkloadEntryAsyncSaveTest.php
git commit -m "feat: return live workload fragments after save"
```

---

### Task 3: Fetch Submission and DOM Refresh

**Files:**
- Create: `resources/js/workload-entry-submit.js`
- Modify: `resources/js/app.ts:1-8`
- Modify: `resources/views/evaluatee/partials/workload-script-entry-submit.blade.php`
- Modify: `resources/views/evaluatee/partials/workload-script-save-reminder.blade.php`
- Test: `tests/js/workload-entry-submit.test.mjs`

**Interfaces:**
- `requestWorkloadEntrySave(form, fetchImpl): Promise<object>`
- `applyWorkloadEntrySaveResponse(documentRef, payload): void`
- Browser global `window.WorkloadEntrySubmit`

- [ ] **Step 1: Write failing helper tests**

Assert the request uses `form.action`, POST, `FormData`, `Accept: application/json`, and `X-Requested-With: XMLHttpRequest`. Add a 422 test asserting rejected `error.status` and `error.errors`, plus a network rejection test.

```javascript
const payload = await requestWorkloadEntrySave(form, async (url, options) => {
    assert.equal(url, '/evaluatee/workload-entries/7');
    assert.equal(options.method, 'POST');
    assert.ok(options.body instanceof FormData);
    return jsonResponse(200, { total_score: 12 });
});
assert.equal(payload.total_score, 12);
```

- [ ] **Step 2: Write a failing DOM replacement test**

Call `applyWorkloadEntrySaveResponse` with fake live regions and a fake `#workloadSaveState`. Assert both `innerHTML` values, `workloadSaveState.dataset.currentTotal`, and the dispatched `workload:total-updated` event detail change.

- [ ] **Step 3: Verify RED**

```powershell
node --test tests/js/workload-entry-submit.test.mjs
```

Expected: FAIL because the module does not exist.

- [ ] **Step 4: Implement the helper and browser global**

```javascript
export async function requestWorkloadEntrySave(form, fetchImpl = window.fetch.bind(window)) {
    const response = await fetchImpl(form.action, {
        method: 'POST',
        body: new FormData(form),
        headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
    });
    const payload = await response.json().catch(() => ({}));
    if (!response.ok) {
        const error = new Error(payload.message || 'Unable to save workload entry');
        error.status = response.status;
        error.errors = payload.errors || {};
        throw error;
    }
    return payload;
}

export function applyWorkloadEntrySaveResponse(documentRef, payload) {
    const panels = documentRef.getElementById('workloadPanelsLiveRegion');
    const summary = documentRef.getElementById('workloadSummaryLiveRegion');
    const state = documentRef.getElementById('workloadSaveState');
    if (panels) panels.innerHTML = payload.panels_html || '';
    if (summary) summary.innerHTML = payload.summary_html || '';
    if (state) state.dataset.currentTotal = String(payload.total_score ?? 0);
    documentRef.dispatchEvent(new CustomEvent('workload:total-updated', {
        detail: { total: Number(payload.total_score ?? 0) },
    }));
}
```

Expose both functions as `window.WorkloadEntrySubmit` and import the module from `resources/js/app.ts`.

- [ ] **Step 5: Verify helper GREEN**

```powershell
node --test tests/js/workload-entry-submit.test.mjs
```

Expected: PASS.

- [ ] **Step 6: Implement the asynchronous coordinator**

Make the form handler `async`; always prevent native submission after the existing validation; add an `isSubmitting` guard; disable the button and show a saving label. On success:

```javascript
const payload = await window.WorkloadEntrySubmit.requestWorkloadEntrySave(workloadForm);
window.WorkloadEntrySubmit.applyWorkloadEntrySaveResponse(document, payload);
bootstrap.Modal.getOrCreateInstance(workloadModalEl).hide();
showWorkloadSaveMessage(payload.message, false);
```

On errors, flatten Laravel's `errors` object, add `is-invalid` to fields whose `name` matches a Laravel error key, keep the modal open, and display an alert. In `finally`, restore button text/state and clear the guard. If the browser helper is unavailable, call `workloadForm.submit()` as fallback.

Implement `showWorkloadSaveMessage(message, isError)` in the partial with `role="alert"`, text-only content, green/red status styling, and a five-second auto-hide.

In `workload-script-save-reminder.blade.php`, change `currentTotal` to `let` and listen for refreshed totals:

```javascript
document.addEventListener('workload:total-updated', function (event) {
    currentTotal = parseScore(event.detail?.total ?? 0);
    hasUnsavedChanges = hasSavedTotal
        ? Math.abs(currentTotal - savedTotal) > 0.0001
        : currentTotal > 0;
    updateReminder();
});
```

- [ ] **Step 7: Add an inline contract test**

Read the Blade partial and assert it contains `event.preventDefault()`, `requestWorkloadEntrySave(workloadForm)`, `applyWorkloadEntrySaveResponse`, the Bootstrap modal hide call, and the duplicate-submit guard.

- [ ] **Step 8: Run focused tests**

```powershell
node --test tests/js/workload-entry-submit.test.mjs
php vendor/bin/pest tests/Feature/Evaluation/WorkloadEntryAsyncSaveTest.php --compact
```

Expected: PASS.

- [ ] **Step 9: Commit**

```powershell
git add -- resources/js/workload-entry-submit.js resources/js/app.ts resources/views/evaluatee/partials/workload-script-entry-submit.blade.php resources/views/evaluatee/partials/workload-script-save-reminder.blade.php tests/js/workload-entry-submit.test.mjs
git commit -m "feat: save workload entries without page refresh"
```

---

### Task 4: Regression Verification

**Files:** Verify only.

**Interfaces:** Produces test/build evidence for the completed behavior.

- [ ] **Step 1: Run all JavaScript tests**

```powershell
npm run test:js
```

- [ ] **Step 2: Run workload-related PHP tests**

```powershell
php vendor/bin/pest tests/Feature/Evaluation/WorkloadEntryAsyncSaveTest.php tests/Feature/Evaluation/PreviousWorkloadImportTest.php tests/Feature/UatAuditLogTest.php --compact
```

- [ ] **Step 3: Build assets**

```powershell
npm run build
```

- [ ] **Step 4: Check PHP style and whitespace**

```powershell
php vendor/bin/pint --test app/Support/EvaluateeWorkloadLiveData.php app/Http/Controllers/Evaluatee/EvaluationWorkloadController.php app/Http/Controllers/Evaluatee/EvaluateeWorkloadEntryController.php tests/Feature/Evaluation/WorkloadEntryAsyncSaveTest.php
git diff --check
```

- [ ] **Step 5: Inspect scope**

```powershell
git status --short
git log -5 --oneline
```

Expected: all checks exit `0`; planned commits contain only planned files and unrelated pre-existing changes remain untouched.
