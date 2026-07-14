# Setting Modal Reuse Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Make create and edit modals reopen reliably across all four setting management pages.

**Architecture:** Keep each page's existing form and validation logic, but reuse its Bootstrap modal instance through `getOrCreateInstance()`. Preserve navigation cleanup while removing cleanup from user-triggered open paths, and lock the lifecycle contract with a focused Pest test.

**Tech Stack:** Laravel 11, Blade, Bootstrap 5.3, Pest 3

## Global Constraints

- Change only department, position, job-level, and subject modal lifecycle code.
- Do not change controllers, routes, form fields, validation, or payloads.
- Preserve existing cleanup on `DOMContentLoaded`, `pageshow`, and `load`.
- Do not modify unrelated kickoff or UAT files in the working tree.

---

### Task 1: Reuse setting modal instances

**Files:**
- Modify: `tests/Feature/CreateModalContractTest.php`
- Modify: `resources/views/departments/partials/index-script.blade.php`
- Modify: `resources/views/positions/partials/index-script.blade.php`
- Modify: `resources/views/Job Level/partials/index-script.blade.php`
- Modify: `resources/views/subjects/partials/index-script.blade.php`

**Interfaces:**
- Consumes: Bootstrap 5.3 `bootstrap.Modal.getOrCreateInstance(element)`
- Produces: Stable create/edit modal lifecycle on all four setting pages

- [ ] **Step 1: Write the failing lifecycle contract**

Append this test to `tests/Feature/CreateModalContractTest.php`:

```php
test('setting modal openers reuse bootstrap instances without cleanup races', function () {
    $scripts = [
        resource_path('views/departments/partials/index-script.blade.php'),
        resource_path('views/positions/partials/index-script.blade.php'),
        resource_path('views/Job Level/partials/index-script.blade.php'),
        resource_path('views/subjects/partials/index-script.blade.php'),
    ];

    foreach ($scripts as $scriptPath) {
        $script = file_get_contents($scriptPath);

        expect(substr_count($script, 'bootstrap.Modal.getOrCreateInstance(modalEl)'))
            ->toBe(2)
            ->and($script)
            ->not->toContain('new bootstrap.Modal(modalEl)')
            ->not->toMatch('/function openCreateModal\(\)\s*\{\s*clearModalBackdrop\(\);/')
            ->not->toMatch('/function handleEdit\([^)]*\)\s*\{\s*clearModalBackdrop\(\);/');
    }
});
```

- [ ] **Step 2: Run the focused test to verify RED**

Run: `php artisan test tests/Feature/CreateModalContractTest.php --filter="setting modal openers reuse"`

Expected: FAIL because every script currently has zero `getOrCreateInstance(modalEl)` calls and still creates new modal instances.

- [ ] **Step 3: Apply the minimal lifecycle correction**

In both `openCreateModal()` and `handleEdit()` in each of the four scripts:

```javascript
// Remove this line from the start of each opener.
clearModalBackdrop();

// Replace the constructor with stable instance reuse.
const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
modal.show();
```

Leave `clearModalBackdrop()` and its `DOMContentLoaded`, `pageshow`, and `load` registrations unchanged.

- [ ] **Step 4: Run focused tests to verify GREEN**

Run: `php artisan test tests/Feature/CreateModalContractTest.php`

Expected: all modal contract tests PASS.

- [ ] **Step 5: Run related regression and formatting checks**

Run:

```powershell
php artisan test tests/Feature/CreateModalContractTest.php tests/Feature/DeleteActionTest.php tests/Feature/Settings/DepartmentSettingTest.php tests/Feature/Settings/PositionSettingTest.php tests/Feature/Settings/BulkSettingDeleteTest.php
vendor/bin/pint --test tests/Feature/CreateModalContractTest.php
git diff --check
```

Expected: all tests PASS, Pint reports no style issues, and `git diff --check` exits 0.

- [ ] **Step 6: Perform browser smoke verification**

Using an isolated database and headless Chrome, verify department edit modal state after open, close, and reopen:

```text
show=true
display=block
backdrops=1
instance=true
```

Repeat after submitting an edit and returning to the departments index. Confirm the second edit modal contains the saved department name.

- [ ] **Step 7: Commit the implementation**

```powershell
git add -- tests/Feature/CreateModalContractTest.php resources/views/departments/partials/index-script.blade.php resources/views/positions/partials/index-script.blade.php 'resources/views/Job Level/partials/index-script.blade.php' resources/views/subjects/partials/index-script.blade.php
git commit -m "fix: reuse setting modal instances"
```
