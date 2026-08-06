# Responsive Subject Form Modal Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Keep the subject form title and actions visible at every viewport height while scrolling only the form fields.

**Architecture:** The shared subject modal component owns a viewport-constrained flex-column shell consumed by both administrator and evaluatee pages. Bootstrap supplies centering, scrollable-dialog, and mobile-fullscreen behavior; scoped CSS fixes the header/footer and gives overflow ownership to the body. The evaluatee-only vertical offset is removed.

**Tech Stack:** Laravel 11, Blade, Bootstrap 5, scoped CSS, Pest/PHPUnit, Vite.

## Global Constraints

- Header and footer remain visible while only the modal body scrolls.
- Desktop/tablet dialogs use a small viewport margin and `100dvh`-derived height.
- Small-screen dialogs use Bootstrap fullscreen behavior.
- Include a `100vh` fallback before each `100dvh` declaration.
- Remove the evaluatee workload `margin-top: 450px` override.
- Preserve form fields, validation, create/edit behavior, and submit handlers.

---

### Task 1: Constrain the shared subject form modal to the viewport

**Files:**
- Modify: `resources/views/components/subject-modal.blade.php`
- Modify: `resources/views/evaluatee/partials/workload-styles.blade.php`
- Modify: `tests/Feature/CreateModalContractTest.php`

**Interfaces:**
- Consumes: Bootstrap modal classes and the existing `#subjectModal`, `.subject-modal-dialog`, `.modal-header`, `.modal-body`, and `.modal-footer` elements.
- Produces: a shared responsive dialog with fixed header/footer and a vertically scrollable body on both consuming pages.

- [ ] **Step 1: Write the failing modal layout contract test**

Extend `subjects index and subject modal render create hooks without inline handlers` in `tests/Feature/CreateModalContractTest.php`:

```php
expect($modalHtml)
    ->toContain('modal-dialog-centered')
    ->toContain('modal-dialog-scrollable')
    ->toContain('modal-fullscreen-sm-down')
    ->toContain('height: calc(100vh - 2rem)')
    ->toContain('height: calc(100dvh - 2rem)')
    ->toContain('overflow-y: auto')
    ->toContain('flex-shrink: 0');

$evaluateeStyles = file_get_contents(resource_path('views/evaluatee/partials/workload-styles.blade.php'));
expect($evaluateeStyles)
    ->not->toMatch('/#subjectModal\s+\.subject-modal-dialog\s*\{[^}]*margin-top:\s*450px/s');
```

This test catches removal of the viewport constraint, scroll ownership, fixed action regions, mobile fullscreen class, or reintroduction of the evaluatee offset.

- [ ] **Step 2: Run the contract test and verify RED**

Run:

```powershell
$env:DB_DATABASE=':memory:'
php vendor/bin/pest tests/Feature/CreateModalContractTest.php
```

Expected: FAIL because the dialog lacks the responsive classes and still has `margin-top: 450px`.

- [ ] **Step 3: Add the responsive Bootstrap classes**

Change the shared dialog opening tag to:

```blade
<div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable modal-fullscreen-sm-down subject-modal-dialog">
```

- [ ] **Step 4: Add scoped viewport and scroll rules to the component**

Add this scoped style block at the top of `subject-modal.blade.php`:

```blade
<style>
    #subjectModal .subject-modal-dialog {
        height: calc(100vh - 2rem);
        height: calc(100dvh - 2rem);
        margin: 1rem auto !important;
    }

    #subjectModal .modal-content {
        display: flex;
        flex-direction: column;
        width: 100%;
        height: 100%;
        max-height: 100%;
        overflow: hidden;
    }

    #subjectModal .modal-header,
    #subjectModal .modal-footer {
        flex-shrink: 0;
    }

    #subjectModal .modal-body {
        flex: 1 1 auto;
        min-height: 0;
        overflow-y: auto;
        overscroll-behavior: contain;
    }

    @media (max-width: 575.98px) {
        #subjectModal .subject-modal-dialog {
            height: 100vh;
            height: 100dvh;
            margin: 0 !important;
        }
    }
</style>
```

- [ ] **Step 5: Remove the conflicting evaluatee offset**

Delete exactly this rule from `resources/views/evaluatee/partials/workload-styles.blade.php`:

```css
#subjectModal .subject-modal-dialog {
    margin-top: 450px;
}
```

- [ ] **Step 6: Run targeted verification**

Run:

```powershell
$env:DB_DATABASE=':memory:'
php vendor/bin/pest tests/Feature/CreateModalContractTest.php tests/Feature/Evaluation/WorkloadPageAsyncMutationTest.php tests/Feature/Subjects/SubjectHoursTest.php
npm run test:js
```

Expected: all PHP and JavaScript tests pass.

- [ ] **Step 7: Build and inspect whitespace**

Run:

```powershell
npm run build
git diff --check
```

Expected: Vite and diff check exit with code 0.

- [ ] **Step 8: Commit the responsive modal**

```powershell
git add -- resources/views/components/subject-modal.blade.php resources/views/evaluatee/partials/workload-styles.blade.php tests/Feature/CreateModalContractTest.php
git commit -m "fix: keep subject modal actions visible"
```
