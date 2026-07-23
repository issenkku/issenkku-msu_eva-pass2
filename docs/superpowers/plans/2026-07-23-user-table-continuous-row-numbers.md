# User Table Continuous Row Numbers Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Make staff-management row numbers continue across Laravel pagination pages and filtered result sets.

**Architecture:** Use the existing `LengthAwarePaginator::firstItem()` value as the absolute offset and add the local collection index. Keep the row component and controller untouched. Align existing view-contract fixtures with the production paginator contract and add a page-two regression test.

**Tech Stack:** Laravel Blade, Laravel `LengthAwarePaginator`, Pest

## Global Constraints

- Change only the staff-management table index expression and its tests.
- Do not change the user row component, controller query, sorting, filters, pagination size, footer summary, actions, or bulk selection.
- Page 1 must start at 1; page 2 at 11; page 3 at 21 for the current ten-row page size.
- Filtered and searched results use their own paginator offset.
- Empty results continue to render the existing empty state.

---

### Task 1: Add a page-two row-number regression test

**Files:**
- Create: `tests/Feature/UserManagementPaginationTest.php`

**Interfaces:**
- Consumes: `user.management.partials.index-table` and an `Illuminate\Pagination\LengthAwarePaginator`
- Produces: a rendered-table contract requiring page-two rows to display 11 and 12

- [ ] **Step 1: Create the failing test**

Create the file with this content:

```php
<?php

use Illuminate\Pagination\LengthAwarePaginator;

function paginationTestUser(int $id, string $name): array
{
    return [
        'id' => $id,
        'prefix' => '',
        'name' => $name,
        'employee_id' => "EMP{$id}",
        'position' => ['name' => 'Officer'],
        'job_level' => null,
        'personnel_type' => 'staff',
        'phone' => '0812345678',
        'email' => "user{$id}@example.com",
        'bio' => null,
        'education_history_entries' => [],
        'status' => 'active',
        'position_id' => 1,
        'job_level_id' => null,
        'department_id' => 1,
        'role_names' => [],
    ];
}

test('staff row numbers continue on the second pagination page', function () {
    $users = new LengthAwarePaginator(
        collect([
            paginationTestUser(11, 'Eleventh User'),
            paginationTestUser(12, 'Twelfth User'),
        ]),
        12,
        10,
        2,
        ['path' => '/users'],
    );

    $html = view('user.management.partials.index-table', compact('users'))->render();

    expect($html)
        ->toContain('<td class="p-4 text-center">11</td>')
        ->toContain('<td class="p-4 text-center">12</td>')
        ->not->toContain('<td class="p-4 text-center">1</td>')
        ->not->toContain('<td class="p-4 text-center">2</td>');
});
```

- [ ] **Step 2: Run the test and verify RED**

Run:

```powershell
vendor\bin\pest tests\Feature\UserManagementPaginationTest.php
```

Expected: FAIL because the table currently renders 1 and 2.

- [ ] **Step 3: Commit the failing test**

```powershell
git add -- tests/Feature/UserManagementPaginationTest.php
git commit -m "test: cover user row pagination numbers"
```

---

### Task 2: Use the paginator offset in the staff table

**Files:**
- Modify: `resources/views/user/management/partials/index-table.blade.php:6-8`
- Modify: `tests/Feature/UserManagementModalTest.php:45-101`
- Test: `tests/Feature/UserManagementPaginationTest.php`

**Interfaces:**
- Consumes: `LengthAwarePaginator::firstItem(): ?int`
- Produces: absolute row number passed as the existing `index` prop to `x-user-table`

- [ ] **Step 1: Change the row index expression**

Replace:

```blade
<x-user-table :index="$index + 1" :employee="[
```

with:

```blade
<x-user-table :index="$users->firstItem() + $index" :employee="[
```

- [ ] **Step 2: Make existing table-view fixtures match the production paginator contract**

Add this import to `tests/Feature/UserManagementModalTest.php`:

```php
use Illuminate\Pagination\LengthAwarePaginator;
```

Add this helper below the import:

```php
function userManagementTestPaginator(array $users): LengthAwarePaginator
{
    return new LengthAwarePaginator(
        collect($users),
        count($users),
        10,
        1,
        ['path' => '/users'],
    );
}
```

In both tests that render `user.management.partials.index-table`, replace:

```php
'users' => collect([
```

with:

```php
'users' => userManagementTestPaginator([
```

- [ ] **Step 3: Run focused tests and verify GREEN**

Run:

```powershell
vendor\bin\pest tests\Feature\UserManagementPaginationTest.php tests\Feature\UserManagementModalTest.php
```

Expected: all tests PASS; the page-two contract finds 11 and 12.

- [ ] **Step 4: Run controller regression tests**

Run:

```powershell
vendor\bin\pest tests\Feature\User\UserControllerTest.php
```

Expected: all tests PASS.

- [ ] **Step 5: Commit the implementation**

```powershell
git add -- resources/views/user/management/partials/index-table.blade.php tests/Feature/UserManagementModalTest.php
git commit -m "fix: continue user row numbers across pages"
```

---

### Task 3: Verify formatting and the full PHP suite

**Files:**
- Verify: `resources/views/user/management/partials/index-table.blade.php`
- Verify: `tests/Feature/UserManagementPaginationTest.php`
- Verify: `tests/Feature/UserManagementModalTest.php`

**Interfaces:**
- Consumes: completed pagination-number implementation
- Produces: repository-wide verification evidence

- [ ] **Step 1: Run PHP formatting**

```powershell
vendor\bin\pint --test tests\Feature\UserManagementPaginationTest.php tests\Feature\UserManagementModalTest.php
```

Expected: PASS with no formatting changes required.

- [ ] **Step 2: Run the full PHP suite**

```powershell
composer test
```

Expected: all tests PASS.

- [ ] **Step 3: Check repository integrity**

```powershell
git diff --check
git status --short
git log --oneline -5
```

Expected: no whitespace errors; only pre-existing unrelated workspace changes remain uncommitted; both row-number commits appear in history.
