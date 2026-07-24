# User Modal Fixed Header and Footer Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Keep the user modal title, close control, and action buttons visible while only the form body scrolls on desktop and mobile.

**Architecture:** Convert the modal panel into a viewport-bounded vertical Flexbox shell. Keep the header outside the form, make the form consume the remaining height, and split the form into one scrollable body plus one fixed action footer without changing existing IDs, data hooks, submission, validation, or education-row behavior.

**Tech Stack:** Laravel Blade, Tailwind CSS utility classes, Pest feature tests

## Global Constraints

- Preserve all existing form fields, request payloads, routes, validation, education-row behavior, and modal close behavior.
- Use existing Tailwind utilities only; add no dependency and no JavaScript height calculation.
- Preserve all existing element IDs and data hooks.
- Use `dvh` to bound the modal against mobile browser chrome.
- Only the form body may use vertical scrolling; the overlay and panel must not be vertical scroll containers.
- Keep the existing purple visual language, button components, and form-control styling.
- Add an accessible Thai label to the symbol-only close button.
- Do not refactor unrelated Blade partials or JavaScript.

## File Structure

- Modify `tests/Feature/UserManagementModalTest.php` to specify the modal shell, scroll body, action footer, dialog semantics, and accessible close control.
- Modify `resources/views/user/management/user-form-modal.blade.php` to provide the viewport-bounded Flexbox shell and the single scroll container.
- Modify `resources/views/user/management/partials/user-modal-header.blade.php` to provide fixed header spacing, dialog title identity, and the accessible close button.
- Modify `resources/views/user/management/partials/user-modal-actions.blade.php` to provide a fixed, opaque footer separated from the scrolling body.

---

### Task 1: Viewport-Bounded Modal Shell

**Files:**

- Modify: `tests/Feature/UserManagementModalTest.php:17-37`
- Modify: `resources/views/user/management/user-form-modal.blade.php:1-23`
- Modify: `resources/views/user/management/partials/user-modal-header.blade.php:2-5`
- Modify: `resources/views/user/management/partials/user-modal-actions.blade.php:2-5`

**Interfaces:**

- Consumes: Existing `#userModal`, `#userForm`, `#formMethod`, `data-user-modal-close`, `data-user-education-add`, and `data-user-education-remove` hooks used by `user-modal-script.blade.php`.
- Produces: `data-user-modal-panel` for the bounded panel, `data-user-modal-header` for the fixed header, `data-user-modal-scroll` for the only vertical scroll container, and `data-user-modal-actions` for the fixed footer.

- [ ] **Step 1: Extend the view test with the required shell contract**

Replace the expectation chain in `user management modal renders data-hook based shell controls` with:

```php
    expect($html)
        ->toContain('id="userModal"')
        ->toContain('role="dialog"')
        ->toContain('aria-modal="true"')
        ->toContain('aria-labelledby="userModalTitle"')
        ->toContain('data-user-modal-panel')
        ->toContain('min-w-0 max-w-full')
        ->toContain('max-h-[calc(100dvh-2rem)]')
        ->toContain('data-user-modal-header')
        ->toContain('data-user-modal-scroll')
        ->toContain('min-h-0 flex-1 overflow-y-auto overscroll-contain')
        ->toContain('data-user-modal-actions')
        ->toContain('aria-label="ปิดหน้าต่าง"')
        ->toContain('shrink-0')
        ->toContain('data-user-modal-close')
        ->toContain('data-user-education-add')
        ->toContain('data-user-education-remove')
        ->not->toContain('onclick="closeModal()"')
        ->not->toContain('onclick="addEducationHistoryRow()"');
```

- [ ] **Step 2: Run the focused test to verify it fails**

Run:

```powershell
php .\vendor\bin\pest tests\Feature\UserManagementModalTest.php --filter="user management modal renders data-hook based shell controls" --compact
```

Expected: FAIL because the rendered view does not contain `role="dialog"` and the new modal layout hooks.

- [ ] **Step 3: Convert the modal view into a bounded Flexbox shell**

Replace `resources/views/user/management/user-form-modal.blade.php` with:

```blade
<div
    id="userModal"
    class="fixed inset-0 z-[9999] hidden items-center justify-center bg-black bg-opacity-50 p-4 sm:p-6"
    role="dialog"
    aria-modal="true"
    aria-labelledby="userModalTitle">
    <div
        data-user-modal-panel
        class="flex min-w-0 max-w-full max-h-[calc(100dvh-2rem)] w-full max-w-3xl flex-col overflow-hidden rounded-xl bg-white shadow-xl sm:max-h-[calc(100dvh-3rem)]">
        @include('user.management.partials.user-modal-header')

        <form
            id="userForm"
            class="flex min-h-0 min-w-0 flex-1 flex-col"
            action="{{ route('users.store') }}"
            method="POST">
            <div data-user-modal-scroll class="min-w-0 min-h-0 flex-1 overflow-y-auto overscroll-contain px-4 py-4 sm:px-6">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">

                @include('user.management.partials.user-modal-error-list')

                <div class="mt-4 grid grid-cols-1 gap-6">
                    @include('user.management.partials.user-modal-personal-section')
                    @include('user.management.partials.user-modal-work-section')
                    @include('user.management.partials.user-modal-contact-section')
                    @include('user.management.partials.user-modal-education-section')
                    @include('user.management.partials.user-modal-password-section')
                    @include('user.management.partials.user-modal-settings-section')
                </div>
            </div>

            @include('user.management.partials.user-modal-actions')
        </form>
    </div>
</div>

@include('user.management.partials.user-modal-script')
```

The overlay remains the backdrop click target expected by `user-modal-script.blade.php`. `overflow-hidden` clips the panel to its rounded corners, while `min-h-0` on the form and scroll body allows the body to shrink and become the only scroll container.

- [ ] **Step 4: Make the header fixed, opaque, and keyboard-visible**

Replace the rendered markup in `resources/views/user/management/partials/user-modal-header.blade.php` with:

```blade
<div data-user-modal-header class="flex flex-none items-center justify-between border-b bg-white px-4 py-4 sm:px-6">
    <h2 id="userModalTitle" class="min-w-0 pr-4 text-lg font-semibold text-purple-700">เพิ่มผู้ใช้งานใหม่</h2>
    <button
        type="button"
        data-user-modal-close
        aria-label="ปิดหน้าต่าง"
        class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-md text-xl leading-none text-gray-500 transition-colors hover:bg-red-50 hover:text-red-500 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2">
        &times;
    </button>
</div>
```

Keep the existing Blade comment above the markup. The `id="userModalTitle"` value matches the panel's `aria-labelledby`, and the focus ring makes keyboard focus visible.

- [ ] **Step 5: Make the action footer fixed and visually separated**

Replace the rendered markup in `resources/views/user/management/partials/user-modal-actions.blade.php` with:

```blade
<div data-user-modal-actions class="flex flex-none justify-center gap-4 border-t bg-white px-4 py-4 sm:px-6">
    <x-button type="secondary" text="ย้อนกลับ" data-user-modal-close icon="fas fa-arrow-left" />
    <x-button type="primary" text="บันทึก" icon="fas fa-save" buttonType="submit" />
</div>
```

Keep the existing Blade comment above the markup. Removing `mt-8` prevents a transparent scrolling gap above the footer; the scroll body owns the spacing around the final form section.

- [ ] **Step 6: Run the focused test to verify it passes**

Run:

```powershell
php .\vendor\bin\pest tests\Feature\UserManagementModalTest.php --filter="user management modal renders data-hook based shell controls" --compact
```

Expected: PASS, 1 test successful.

- [ ] **Step 7: Run the complete user modal feature test**

Run:

```powershell
php .\vendor\bin\pest tests\Feature\UserManagementModalTest.php --compact
```

Expected: PASS, all tests in `UserManagementModalTest.php` successful.

- [ ] **Step 8: Build frontend assets to validate Tailwind classes**

Run:

```powershell
npm run build
```

Expected: exit code 0 and a successful Vite production build with no invalid utility or template parsing error.

- [ ] **Step 9: Commit the tested modal shell**

```powershell
git add -- tests/Feature/UserManagementModalTest.php resources/views/user/management/user-form-modal.blade.php resources/views/user/management/partials/user-modal-header.blade.php resources/views/user/management/partials/user-modal-actions.blade.php
git commit -m "fix: keep user modal actions visible"
```

Expected: one commit containing only the test and the three modal view changes.

---

### Task 2: Responsive Interaction Verification

**Files:**

- Verify: `resources/views/user/management/user-form-modal.blade.php`
- Verify: `resources/views/user/management/partials/user-modal-header.blade.php`
- Verify: `resources/views/user/management/partials/user-modal-actions.blade.php`

**Interfaces:**

- Consumes: The `data-user-modal-panel`, `data-user-modal-scroll`, `data-user-modal-header`, and `data-user-modal-actions` regions produced by Task 1.
- Produces: Verified desktop and mobile behavior for create, edit, scroll, validation, education rows, close, and submit interactions.

- [ ] **Step 1: Start the local application using the repository's configured development environment**

Use the repository's normal local URL. If no server is running, run:

```powershell
php artisan serve
```

Expected: Laravel reports a local application URL and remains running while verification is performed.

- [ ] **Step 2: Verify the desktop create modal**

At a viewport of approximately `1440 × 900`:

1. Open the users page.
2. Select the create-user action.
3. Confirm the panel is vertically centered and remains inside the viewport.
4. Scroll the form body from the personal section to the role section.
5. Confirm the title, close button, back button, and save button remain visible.
6. Confirm only the middle form body scrolls and no second scrollbar appears on the overlay.

Expected: all six checks pass.

- [ ] **Step 3: Verify the mobile create modal**

At a viewport of approximately `390 × 844`:

1. Open the create-user modal.
2. Confirm the panel uses nearly all available height while retaining outer spacing and rounded corners.
3. Scroll to the last role option.
4. Confirm the header and footer remain visible and do not cover the first or last form controls.
5. Add and remove an education-history row.

Expected: all five checks pass, with no horizontal page scroll.

- [ ] **Step 4: Verify edit, validation, and close behavior**

1. Close the modal using the close button and reopen an existing user in edit mode.
2. Confirm the title changes to the existing edit title.
3. Clear a required field and activate the save action.
4. Confirm the validation message appears in the scroll body and the invalid field can receive focus.
5. Close using the back button.
6. Reopen the modal and close it by selecting the backdrop.

Expected: edit title, validation focus, all three close paths, and the existing submission flow behave as before.

- [ ] **Step 5: Re-run automated verification after the interaction check**

Run:

```powershell
php .\vendor\bin\pest tests\Feature\UserManagementModalTest.php --compact
npm run build
git status --short
```

Expected: all modal feature tests pass, the Vite build succeeds, and `git status --short` shows no unexpected generated or modified files from verification.
