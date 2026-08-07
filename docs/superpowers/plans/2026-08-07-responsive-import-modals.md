# Responsive Import Modals Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Keep the user and subject import modal titles and action buttons visible while only their content bodies scroll.

**Architecture:** Preserve each modal's current framework and behavior. Restructure the custom Tailwind user import modal into a viewport-constrained flex panel, and extend the Bootstrap subject import modal with scrollable/fullscreen utilities plus scoped flex styles.

**Tech Stack:** Laravel Blade, Tailwind CSS utilities, Bootstrap 5 modal utilities, Pest, Node test runner, Vite.

## Global Constraints

- Update `#importUserModal` and `#subjectImportModal` only.
- Desktop and tablet retain a one-rem viewport margin.
- Screens up to `575.98px` occupy the full viewport with no outer margin.
- Header and footer never shrink; only the content body scrolls.
- Preserve all fields, routes, validation messages, file handling, upload behavior, and import scripts.
- Do not create a global modal abstraction or alter unrelated modals.

---

### Task 1: Make the user import modal viewport-safe

**Files:**
- Modify: `tests/Feature/ImportModalTest.php`
- Modify: `resources/views/user/management/import-user-modal.blade.php`

**Interfaces:**
- Consumes: existing `#importUserModal`, `#importForm`, and `data-import-*` JavaScript hooks.
- Produces: `data-import-modal-panel`, `data-import-modal-header`, `data-import-modal-body`, and `data-import-modal-footer` layout hooks without changing form behavior.

- [ ] **Step 1: Write the failing Blade contract test**

Extend `import user modal renders import hooks without inline handlers` in `tests/Feature/ImportModalTest.php`:

```php
    expect($html)
        ->toContain('data-import-modal-panel')
        ->toContain('data-import-modal-header')
        ->toContain('data-import-modal-body')
        ->toContain('data-import-modal-footer')
        ->toContain('h-[100dvh]')
        ->toContain('sm:max-h-[calc(100dvh-2rem)]')
        ->toContain('flex-1 overflow-y-auto overscroll-contain')
        ->toContain('flex-none');
```

This test catches removal of viewport containment, body scroll ownership, or fixed header/footer regions while continuing to render the real Blade view.

- [ ] **Step 2: Run the test to verify RED**

```powershell
$env:DB_DATABASE=':memory:'
php vendor/bin/pest tests/Feature/ImportModalTest.php --filter="import user modal renders"
```

Expected: FAIL because the four layout hooks and viewport classes are absent.

- [ ] **Step 3: Restructure the user import modal**

In `resources/views/user/management/import-user-modal.blade.php`:

- Change the backdrop padding to `p-0 sm:p-4` and remove backdrop scrolling.
- Give the panel `data-import-modal-panel` and these layout classes:

```html
flex h-[100dvh] max-h-[100dvh] w-full max-w-4xl flex-col overflow-hidden rounded-none bg-white shadow-2xl sm:h-auto sm:max-h-[calc(100dvh-2rem)] sm:rounded-xl
```

- Give the existing header `data-import-modal-header` and `flex-none px-4 py-4 sm:px-6`.
- Give `#importForm` `class="flex min-h-0 flex-1 flex-col"`.
- Wrap the CSRF field, method field, upload section, and feedback include in:

```html
<div data-import-modal-body class="min-h-0 flex-1 overflow-y-auto overscroll-contain px-4 py-4 sm:px-6">
    ...existing form content...
</div>
```

- Move the existing action block after the body and give it:

```html
<div data-import-modal-footer class="flex flex-none justify-end gap-4 border-t px-4 py-4 sm:px-6">
```

- [ ] **Step 4: Run the focused test to verify GREEN**

```powershell
$env:DB_DATABASE=':memory:'
php vendor/bin/pest tests/Feature/ImportModalTest.php --filter="import user modal renders"
```

Expected: PASS.

- [ ] **Step 5: Commit Task 1**

```powershell
git add -- tests/Feature/ImportModalTest.php resources/views/user/management/import-user-modal.blade.php
git commit -m "fix: keep user import actions visible"
```

---

### Task 2: Make the subject import modal viewport-safe

**Files:**
- Modify: `tests/Feature/Subjects/SubjectImportUiTest.php`
- Modify: `resources/views/subjects/partials/import-modal.blade.php`
- Modify: `resources/views/subjects/partials/index-styles.blade.php`

**Interfaces:**
- Consumes: Bootstrap modal behavior and existing `data-subject-import-*` hooks.
- Produces: `.subject-import-dialog`, `.subject-import-content`, and `.subject-import-form` layout hooks without changing import behavior.

- [ ] **Step 1: Write the failing Blade contract test**

Extend `subject index renders import actions and accessible modal hooks` in `tests/Feature/Subjects/SubjectImportUiTest.php`:

```php
    expect($html)
        ->toContain('modal-dialog-centered')
        ->toContain('modal-dialog-scrollable')
        ->toContain('modal-fullscreen-sm-down')
        ->toContain('subject-import-dialog')
        ->toContain('subject-import-content')
        ->toContain('subject-import-form')
        ->toContain('#subjectImportModal .subject-import-dialog')
        ->toContain('height: calc(100dvh - 2rem)')
        ->toContain('overflow-y: auto')
        ->toContain('overscroll-behavior: contain');
```

This test catches removal of the responsive Bootstrap utilities, viewport constraint, or body-only scrolling.

- [ ] **Step 2: Run the test to verify RED**

```powershell
$env:DB_DATABASE=':memory:'
php vendor/bin/pest tests/Feature/Subjects/SubjectImportUiTest.php --filter="subject index renders import actions"
```

Expected: FAIL because the responsive classes and scoped layout rules are absent.

- [ ] **Step 3: Add subject import layout hooks**

Update the openings in `resources/views/subjects/partials/import-modal.blade.php`:

```blade
<div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable modal-fullscreen-sm-down subject-import-dialog">
    <div class="modal-content subject-import-content">
```

Add `class="subject-import-form"` to the existing form. Do not change its method, action, enctype, or data hook.

- [ ] **Step 4: Add scoped subject import styles**

Before the closing `</style>` in `resources/views/subjects/partials/index-styles.blade.php`, add:

```css
    #subjectImportModal .subject-import-dialog {
        height: calc(100vh - 2rem);
        height: calc(100dvh - 2rem);
        margin: 1rem auto !important;
    }

    #subjectImportModal .subject-import-content {
        height: 100%;
        max-height: 100%;
        overflow: hidden;
    }

    #subjectImportModal .subject-import-form {
        display: flex;
        flex-direction: column;
        min-height: 0;
        height: 100%;
    }

    #subjectImportModal .modal-header,
    #subjectImportModal .modal-footer {
        flex-shrink: 0;
    }

    #subjectImportModal .modal-body {
        flex: 1 1 auto;
        min-height: 0;
        overflow-y: auto;
        overscroll-behavior: contain;
    }

    @media (max-width: 575.98px) {
        #subjectImportModal .subject-import-dialog {
            height: 100vh;
            height: 100dvh;
            margin: 0 !important;
        }
    }
```

- [ ] **Step 5: Run the focused test to verify GREEN**

```powershell
$env:DB_DATABASE=':memory:'
php vendor/bin/pest tests/Feature/Subjects/SubjectImportUiTest.php --filter="subject index renders import actions"
```

Expected: PASS.

- [ ] **Step 6: Run regression tests**

```powershell
$env:DB_DATABASE=':memory:'
php vendor/bin/pest tests/Feature/ImportModalTest.php tests/Feature/Subjects/SubjectImportUiTest.php tests/Feature/Subjects/SubjectImportHttpTest.php
npm run test:js
```

Expected: all selected PHP tests and all JavaScript tests PASS.

- [ ] **Step 7: Build assets and validate the diff**

```powershell
npm run build
git diff --check -- resources/views/user/management/import-user-modal.blade.php resources/views/subjects/partials/import-modal.blade.php resources/views/subjects/partials/index-styles.blade.php tests/Feature/ImportModalTest.php tests/Feature/Subjects/SubjectImportUiTest.php
```

Expected: build exits successfully and `git diff --check` prints no errors.

- [ ] **Step 8: Commit Task 2**

```powershell
git add -- tests/Feature/Subjects/SubjectImportUiTest.php resources/views/subjects/partials/import-modal.blade.php resources/views/subjects/partials/index-styles.blade.php
git commit -m "fix: keep subject import actions visible"
```
