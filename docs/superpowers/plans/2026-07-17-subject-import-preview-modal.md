# Subject Import Preview Modal Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Render the tokenized subject-import Preview as an accessible modal over the subjects index with a compact status-count strip instead of a full page and large summary cards.

**Architecture:** Redirect successful uploads to `subjects.index?import_preview=<token>`, resolve the user-owned snapshot inside `SubjectController::index()`, and render a server-side Bootstrap modal. Reuse the existing Preview tables and Confirm/Cancel routes; add minimal JavaScript only for automatic opening, selection controls, focus, and Escape-to-Cancel.

**Tech Stack:** Laravel 11, PHP 8.3, Pest, Blade, Bootstrap Modal, vanilla JavaScript

## Global Constraints

- Keep the upload modal, snapshot storage, 30-minute token lifetime, duplicate comparison, Confirm transaction, and result messages unchanged.
- Replace the full-page Preview presentation with a `modal-xl`, scrollable, mobile-fullscreen dialog.
- Show four compact text statuses with counts; do not render the former four summary cards.
- Use native buttons/forms, accessible names, semantic tables, Bootstrap focus trapping, and visible text that does not rely on color.
- Escape must submit the Cancel form and delete the Preview token; clicking the backdrop must not hide the workflow.
- Invalid, expired, or foreign tokens return 404.
- Do not add AJAX, JSON endpoints, schema changes, or unrelated refactors.
- Preserve unrelated working-tree changes.

---

## File Map

- `SubjectImportController.php`: redirect uploads and the legacy Preview route to the subjects index query.
- `SubjectController.php`: resolve the query token for the authenticated user and provide Preview data to the view.
- `subjects/index.blade.php`: include the modal when a valid Preview is present.
- `subjects/imports/preview-modal.blade.php`: own dialog markup and Confirm/Cancel forms.
- `subjects/imports/partials/summary.blade.php`: render compact status text/counts.
- `subjects/imports/partials/tables.blade.php`: reuse existing semantic Preview tables unchanged.
- `subjects/imports/partials/script.blade.php`: auto-open, focus, select rows, and implement Escape-to-Cancel.
- `subjects/imports/show.blade.php`: remove the now-unused full-page presentation.
- Subject import HTTP, UI, and end-to-end tests: lock routing, ownership, modal markup, accessibility, and workflow behavior.

### Task 1: Route Preview tokens through the subjects index

**Files:**
- Modify: `tests/Feature/Subjects/SubjectImportHttpTest.php`
- Modify: `tests/Feature/Subjects/SubjectImportEndToEndTest.php:34-37`
- Modify: `app/Http/Controllers/Workload/SubjectImportController.php:48-69`
- Modify: `app/Http/Controllers/Workload/SubjectController.php:25-61`

**Interfaces:**
- Consumes: query parameter `import_preview` containing a 64-character snapshot token.
- Produces: nullable view data `importPreview: ?array` and `importPreviewToken: ?string`; 404 for missing ownership; legacy Preview route redirect.

- [ ] **Step 1: Change the HTTP expectations before production code**

Replace `admin uploads a valid workbook and receives a Preview redirect` in `SubjectImportHttpTest.php` with:

```php
test('admin upload redirects to the subjects index with a Preview token', function () {
    $path = httpWorkbook([['CS100', 'ใหม่', '', 3, 2, 1, 0]]);
    $file = new UploadedFile($path, 'subjects.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

    $response = $this->actingAs(importAdmin(), 'web')
        ->post(route('subjects.import.preview.store'), ['import_file' => $file])
        ->assertRedirect();

    $location = $response->headers->get('Location');
    parse_str((string) parse_url($location, PHP_URL_QUERY), $query);

    expect(parse_url($location, PHP_URL_PATH))->toBe('/subjects')
        ->and($query['import_preview'] ?? null)->toMatch('/^[A-Za-z0-9]{64}$/');
    @unlink($path);
});
```

Append these tests:

```php
test('legacy Preview route redirects to the subjects index query', function () {
    $admin = importAdmin();
    $token = app(SubjectImportSnapshotStore::class)->put($admin->id, 'subjects.xlsx', [
        'new' => [], 'changed' => [], 'unchanged' => [], 'errors' => [],
    ]);

    $this->actingAs($admin, 'web')
        ->get(route('subjects.import.preview.show', $token))
        ->assertRedirect(route('subjects.index', ['import_preview' => $token]));
});

test('subjects index rejects a Preview token owned by another user', function () {
    $owner = importAdmin();
    $other = importAdmin();
    $token = app(SubjectImportSnapshotStore::class)->put($owner->id, 'subjects.xlsx', [
        'new' => [], 'changed' => [], 'unchanged' => [], 'errors' => [],
    ]);

    $this->actingAs($other, 'web')
        ->get(route('subjects.index', ['import_preview' => $token]))
        ->assertNotFound();
});
```

Replace `tokenFromRedirect()` in `SubjectImportEndToEndTest.php` with:

```php
function tokenFromRedirect($response): string
{
    parse_str((string) parse_url($response->headers->get('Location'), PHP_URL_QUERY), $query);

    return $query['import_preview'];
}
```

- [ ] **Step 2: Verify RED**

```powershell
vendor\bin\pest --compact tests/Feature/Subjects/SubjectImportHttpTest.php tests/Feature/Subjects/SubjectImportEndToEndTest.php
```

Expected: FAIL because uploads still redirect to `/subject-imports/{token}`, the legacy route returns a page, and the index ignores foreign tokens.

- [ ] **Step 3: Redirect upload and legacy Preview routes**

In `SubjectImportController`, remove `use Illuminate\View\View;`. Change the successful return in `storePreview()` to:

```php
            return redirect()->route('subjects.index', ['import_preview' => $token]);
```

Replace `showPreview()` with:

```php
    public function showPreview(Request $request, string $token): RedirectResponse
    {
        $snapshot = $this->snapshots->getForUser($token, $request->user()->id);
        abort_if($snapshot === null, 404);

        return redirect()->route('subjects.index', ['import_preview' => $token]);
    }
```

- [ ] **Step 4: Resolve the snapshot in SubjectController**

Add the import:

```php
use App\Services\Subjects\SubjectImportSnapshotStore;
```

Change the `index()` signature to:

```php
    public function index(
        Request $request,
        SubjectImportResultStore $results,
        SubjectImportSnapshotStore $snapshots,
    ) {
```

Immediately before the final `return view(...)`, add:

```php
        $importPreviewToken = $request->query('import_preview');
        $importPreview = null;

        if (is_string($importPreviewToken) && $importPreviewToken !== '') {
            $snapshot = $snapshots->getForUser($importPreviewToken, $request->user()->id);
            abort_if($snapshot === null, 404);
            $importPreview = $snapshot['preview'];
        } else {
            $importPreviewToken = null;
        }
```

Replace the view return with:

```php
        return view('subjects.index', compact(
            'subjects',
            'importResult',
            'importPreview',
            'importPreviewToken',
        ));
```

- [ ] **Step 5: Verify GREEN and commit**

Run Step 2 again. Expected: PASS.

```powershell
git add app/Http/Controllers/Workload/SubjectImportController.php app/Http/Controllers/Workload/SubjectController.php tests/Feature/Subjects/SubjectImportHttpTest.php tests/Feature/Subjects/SubjectImportEndToEndTest.php
git commit -m "feat: route subject import previews through index"
```

### Task 2: Render the Preview dialog and compact status strip

**Files:**
- Modify: `tests/Feature/Subjects/SubjectImportUiTest.php`
- Create: `resources/views/subjects/imports/preview-modal.blade.php`
- Modify: `resources/views/subjects/imports/partials/summary.blade.php`
- Modify: `resources/views/subjects/index.blade.php`
- Delete: `resources/views/subjects/imports/show.blade.php`

**Interfaces:**
- Consumes: `importPreview` and `importPreviewToken` from Task 1.
- Produces: `#subjectImportPreviewModal`, `#subjectImportConfirmForm`, `#subjectImportCancelForm`, and `[data-subject-import-status-summary]`.

- [ ] **Step 1: Add failing view contracts**

In `SubjectImportUiTest.php`, extract the existing Preview array into this helper above the Preview tests:

```php
function subjectImportPreviewFixture(): array
{
    return [
        'new' => [
            ['row' => ['excel_row' => 2, 'code' => 'CS100', 'name_th' => 'ใหม่']],
            ['row' => ['excel_row' => 6, 'code' => 'EN100', 'name_th' => null, 'name_en' => 'English Only']],
        ],
        'changed' => [[
            'row' => ['excel_row' => 3, 'code' => 'CS101', 'name_th' => 'ใหม่'],
            'current' => ['id' => 1, 'name_th' => 'เดิม'],
            'fingerprint' => 'hash',
            'diff' => ['name_th' => ['old' => 'เดิม', 'new' => 'ใหม่']],
        ]],
        'unchanged' => [['row' => ['excel_row' => 4, 'code' => 'CS102', 'name_th' => 'เหมือนเดิม']]],
        'errors' => [['excelRow' => 5, 'code' => 'BAD', 'column' => 'หน่วยกิตรวม', 'value' => 'x', 'message' => 'ต้องเป็นจำนวนเต็ม']],
    ];
}
```

Update the existing `Preview renders summaries...` test to assign:

```php
    $preview = subjectImportPreviewFixture();
```

and render the new modal through the index:

```php
    $html = view('subjects.index', [
        'subjects' => emptySubjectPaginator(),
        'importResult' => null,
        'importPreview' => $preview,
        'importPreviewToken' => str_repeat('A', 64),
    ])->render();
```

Add these expectations to that test:

```php
        ->toContain('id="subjectImportPreviewModal"')
        ->toContain('modal-xl modal-dialog-scrollable modal-fullscreen-sm-down')
        ->toContain('data-bs-backdrop="static"')
        ->toContain('data-bs-keyboard="false"')
        ->toContain('id="subjectImportConfirmForm"')
        ->toContain('id="subjectImportCancelForm"')
        ->toContain(route('subjects.import.confirm', str_repeat('A', 64)))
        ->toContain(route('subjects.import.cancel', str_repeat('A', 64)));
```

Append a separate compact-summary contract:

```php
test('Preview uses compact text statuses instead of summary cards', function () {
    $html = view('subjects.imports.partials.summary', [
        'preview' => subjectImportPreviewFixture(),
    ])->render();

    expect($html)
        ->toContain('data-subject-import-status-summary')
        ->toContain('เพิ่มใหม่', 'ข้อมูลซ้ำที่เปลี่ยน', 'ไม่เปลี่ยนแปลง', 'ข้อผิดพลาด')
        ->toContain('2', '1')
        ->not->toContain('class="card')
        ->not->toContain('row g-3');
});
```

- [ ] **Step 2: Verify RED**

```powershell
vendor\bin\pest --compact tests/Feature/Subjects/SubjectImportUiTest.php
```

Expected: FAIL because the index has no Preview modal and the summary still uses cards.

- [ ] **Step 3: Replace the summary cards with compact statuses**

Replace `summary.blade.php` with:

```blade
<div class="d-flex flex-wrap align-items-center gap-2 mb-3"
     aria-label="สรุปสถานะการตรวจสอบข้อมูล"
     data-subject-import-status-summary>
    @foreach([
        ['เพิ่มใหม่', count($preview['new']), 'success'],
        ['ข้อมูลซ้ำที่เปลี่ยน', count($preview['changed']), 'warning'],
        ['ไม่เปลี่ยนแปลง', count($preview['unchanged']), 'secondary'],
        ['ข้อผิดพลาด', count($preview['errors']), 'danger'],
    ] as [$label, $count, $type])
        <span class="badge rounded-pill text-bg-{{ $type }}">
            {{ $label }} <span class="ms-1">{{ $count }}</span>
        </span>
    @endforeach
</div>
```

The label and count remain visible text; color is supplemental only. If Bootstrap's warning badge contrast is insufficient in the rendered application, add `text-dark` to the warning item during visual verification without changing the status API.

- [ ] **Step 4: Create the server-rendered Preview modal**

Create `resources/views/subjects/imports/preview-modal.blade.php`:

```blade
@if($importPreview !== null && $importPreviewToken !== null)
    <div class="modal fade" id="subjectImportPreviewModal" tabindex="-1"
         aria-labelledby="subjectImportPreviewModalLabel" aria-hidden="true"
         data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-xl modal-dialog-scrollable modal-fullscreen-sm-down">
            <form id="subjectImportConfirmForm" class="modal-content" method="POST"
                  action="{{ route('subjects.import.confirm', $importPreviewToken) }}"
                  data-subject-import-confirm-form>
                @csrf
                <div class="modal-header">
                    <div>
                        <h2 class="modal-title fs-5" id="subjectImportPreviewModalLabel" tabindex="-1">
                            <i class="fas fa-file-import me-2" aria-hidden="true"></i>ตรวจสอบข้อมูลก่อนนำเข้า
                        </h2>
                        <p class="mb-0 text-muted small">ตรวจรายการใหม่และเลือกรายการซ้ำที่ต้องการอัปเดต</p>
                    </div>
                    <button type="submit" form="subjectImportCancelForm" class="btn-close"
                            aria-label="ยกเลิก Preview และปิด"></button>
                </div>
                <div class="modal-body">
                    @include('subjects.imports.partials.summary', ['preview' => $importPreview])
                    @include('subjects.imports.partials.tables', ['preview' => $importPreview])
                    @if(count($importPreview['errors']) > 0)
                        <p id="subjectImportConfirmDisabledReason" class="text-danger mb-0" role="alert">
                            กรุณาแก้ไขข้อผิดพลาดในไฟล์แล้วสร้าง Preview ใหม่ก่อนยืนยันการนำเข้า
                        </p>
                    @endif
                </div>
                <div class="modal-footer">
                    <x-button type="secondary" buttonType="submit" text="ยกเลิก"
                        form="subjectImportCancelForm" icon="fas fa-times" />
                    <x-button type="primary" buttonType="submit" text="ยืนยันการนำเข้า"
                        icon="fas fa-check" :disabled="count($importPreview['errors']) > 0"
                        aria-describedby="{{ count($importPreview['errors']) > 0 ? 'subjectImportConfirmDisabledReason' : '' }}" />
                </div>
            </form>
        </div>
    </div>

    <form id="subjectImportCancelForm" method="POST"
          action="{{ route('subjects.import.cancel', $importPreviewToken) }}" class="d-none">
        @csrf
        @method('DELETE')
    </form>

    @include('subjects.imports.partials.script')
@endif
```

- [ ] **Step 5: Include the modal and remove the full-page view**

In `subjects/index.blade.php`, immediately after the existing upload-modal include, add:

```blade
    @include('subjects.imports.preview-modal', [
        'importPreview' => $importPreview ?? null,
        'importPreviewToken' => $importPreviewToken ?? null,
    ])
```

Delete `resources/views/subjects/imports/show.blade.php`; no controller renders it after Task 1.

- [ ] **Step 6: Verify GREEN and commit**

Run Step 2 again. Expected: PASS.

```powershell
git add resources/views/subjects/index.blade.php resources/views/subjects/imports/preview-modal.blade.php resources/views/subjects/imports/partials/summary.blade.php resources/views/subjects/imports/show.blade.php tests/Feature/Subjects/SubjectImportUiTest.php
git commit -m "feat: render subject import Preview modal"
```

### Task 3: Auto-open the dialog and implement accessible keyboard behavior

**Files:**
- Modify: `tests/Feature/Subjects/SubjectImportUiTest.php`
- Modify: `resources/views/subjects/imports/partials/script.blade.php`

**Interfaces:**
- Consumes: modal id `subjectImportPreviewModal`, heading id `subjectImportPreviewModalLabel`, cancel form id `subjectImportCancelForm`, and existing selection data attributes.
- Produces: automatic modal opening, initial focus, Bootstrap focus trapping, select-all/none behavior, and Escape-triggered Cancel submission.

- [ ] **Step 1: Add a failing JavaScript contract**

Append to `SubjectImportUiTest.php`:

```php
test('Preview modal script opens focuses and cancels with Escape', function () {
    $script = file_get_contents(resource_path('views/subjects/imports/partials/script.blade.php'));

    expect($script)
        ->toContain("document.getElementById('subjectImportPreviewModal')")
        ->toContain("document.getElementById('subjectImportCancelForm')")
        ->toContain('bootstrap.Modal.getOrCreateInstance(modalElement)')
        ->toContain("modalElement.addEventListener('shown.bs.modal'")
        ->toContain("event.key === 'Escape'")
        ->toContain('cancelForm.requestSubmit()')
        ->toContain('modal.show()')
        ->toContain('data-subject-import-select-all')
        ->toContain('data-subject-import-select-none');
});
```

- [ ] **Step 2: Verify RED**

```powershell
vendor\bin\pest --compact tests/Feature/Subjects/SubjectImportUiTest.php
```

Expected: FAIL because the current script only handles selection checkboxes.

- [ ] **Step 3: Implement modal lifecycle, focus, and Escape**

Replace `resources/views/subjects/imports/partials/script.blade.php` with:

```blade
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalElement = document.getElementById('subjectImportPreviewModal');
    const cancelForm = document.getElementById('subjectImportCancelForm');
    if (!modalElement || !cancelForm) return;

    const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
    const conflicts = Array.from(modalElement.querySelectorAll('[data-subject-import-conflict]'));

    modalElement.querySelector('[data-subject-import-select-all]')?.addEventListener('click', function () {
        conflicts.forEach(function (checkbox) { checkbox.checked = true; });
    });
    modalElement.querySelector('[data-subject-import-select-none]')?.addEventListener('click', function () {
        conflicts.forEach(function (checkbox) { checkbox.checked = false; });
    });

    modalElement.addEventListener('shown.bs.modal', function () {
        document.getElementById('subjectImportPreviewModalLabel')?.focus();
    });

    modalElement.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            event.preventDefault();
            event.stopImmediatePropagation();
            cancelForm.requestSubmit();
        }
    }, true);

    modal.show();
});
</script>
```

The capture-phase listener runs before Bootstrap's disabled-keyboard handler, so Escape performs the explicit server-side Cancel instead of merely hiding the dialog. The static backdrop remains visible and does not dismiss the modal.

- [ ] **Step 4: Verify GREEN and commit**

Run Step 2 again. Expected: PASS.

```powershell
git add resources/views/subjects/imports/partials/script.blade.php tests/Feature/Subjects/SubjectImportUiTest.php
git commit -m "fix: make subject Preview modal keyboard accessible"
```

### Task 4: Verify the modal workflow end to end

**Files:**
- Verify the files changed in Tasks 1–3.

**Interfaces:**
- Consumes: upload redirect, index snapshot resolution, dialog markup, Confirm/Cancel forms, and modal JavaScript.
- Produces: fresh evidence for routing, ownership, Preview rendering, confirmation, cancellation, accessibility contracts, and application regressions.

- [ ] **Step 1: Run all focused subject-import tests**

```powershell
vendor\bin\pest --compact tests/Feature/Subjects/SubjectImportHttpTest.php tests/Feature/Subjects/SubjectImportUiTest.php tests/Feature/Subjects/SubjectImportEndToEndTest.php tests/Feature/Subjects/SubjectImportPreviewTest.php tests/Feature/Subjects/SubjectImportCommitterTest.php tests/Unit/Subjects/SubjectImportRowValidatorTest.php tests/Unit/Subjects/SubjectWorkbookReaderTest.php tests/Unit/Subjects/SubjectWorkbookExportTest.php
```

Expected: PASS with no failures or errors.

- [ ] **Step 2: Confirm the full-page Preview is gone and modal hooks exist**

```powershell
if (Test-Path 'resources/views/subjects/imports/show.blade.php') { throw 'Full-page Preview view still exists' }
rg -n "subjectImportPreviewModal|data-subject-import-status-summary|subjectImportCancelForm|requestSubmit" resources/views/subjects
```

Expected: the old view is absent and all four modal hooks have matches.

- [ ] **Step 3: Run the complete PHP suite**

```powershell
vendor\bin\pest --compact
```

Expected: PASS with no failures or errors.

- [ ] **Step 4: Run JavaScript, scoped formatting, and build gates**

```powershell
npm run test:js
vendor\bin\pint --test app/Http/Controllers/Workload/SubjectImportController.php app/Http/Controllers/Workload/SubjectController.php tests/Feature/Subjects/SubjectImportHttpTest.php tests/Feature/Subjects/SubjectImportUiTest.php tests/Feature/Subjects/SubjectImportEndToEndTest.php
npm run build
```

Expected: JavaScript tests pass, changed PHP files pass Pint, and Vite builds successfully. Full-repository Pint is a known pre-existing baseline failure and must not trigger unrelated formatting edits.

- [ ] **Step 5: Review workspace and commits**

```powershell
git status --short
git diff --check
git log -6 --oneline
```

Expected: no whitespace errors; three implementation commits are present; unrelated pre-existing files remain unchanged and uncommitted.
