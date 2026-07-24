# User Search White Background Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Give only the user-management search input an opaque white background.

**Architecture:** Extend the shared Blade search component with one optional CSS-class prop. The user-management partial opts into `bg-white`; all other callers retain their current rendering.

**Tech Stack:** Laravel Blade, Tailwind CSS, Pest

## Global Constraints

- Preserve the existing dimensions, border, icon, placeholder, focus state, search button, query parameters, and automatic-search behavior.
- Do not change search bars on other pages.
- Do not alter user-search behavior or controller queries.

---

### Task 1: Opt-in White Search Background

**Files:**
- Modify: `tests/Feature/SearchBarTest.php`
- Modify: `resources/views/components/search-bar.blade.php`
- Modify: `resources/views/user/management/partials/index-search-section.blade.php`

**Interfaces:**
- Consumes: Blade attribute `input-class`.
- Produces: optional component prop `$inputClass` appended to the search input class list.

- [ ] **Step 1: Write the failing tests**

Add these tests to `tests/Feature/SearchBarTest.php`:

```php
test('shared search bar accepts an optional input class', function () {
    $html = view('components.search-bar', [
        'placeholder' => 'ค้นหา',
        'inputClass' => 'bg-white',
    ])->render();

    expect($html)->toContain('search-bar-input')
        ->toContain('bg-white');
});

test('user management search opts into a white input background', function () {
    $source = file_get_contents(
        resource_path('views/user/management/partials/index-search-section.blade.php')
    );

    expect($source)->toContain('input-class="bg-white"');
});
```

- [ ] **Step 2: Run the tests and verify RED**

Run:

```powershell
php vendor/bin/pest tests/Feature/SearchBarTest.php
```

Expected: both new assertions fail because the component has no `inputClass` prop and the user-management caller does not pass it.

- [ ] **Step 3: Add the optional component prop**

Change the prop declaration in `resources/views/components/search-bar.blade.php` to:

```blade
@props([
    'placeholder',
    'inputClass' => '',
])
```

Append the prop to the input classes:

```blade
class="search-bar-input w-full sm:w-64 pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-gray-500 {{ $inputClass }}"
```

- [ ] **Step 4: Opt in only on the user-management page**

Change `resources/views/user/management/partials/index-search-section.blade.php` to:

```blade
{{-- ช่องค้นหาผู้ใช้งานแบบอิสระ --}}
<x-search-bar
    placeholder="ค้นหาชื่อ, รหัสพนักงาน..."
    input-class="bg-white"
/>
```

- [ ] **Step 5: Run verification**

Run:

```powershell
php vendor/bin/pest tests/Feature/SearchBarTest.php
php artisan view:cache
git diff --check
```

Expected: all SearchBar tests pass, Blade templates compile, and the diff check exits 0.

- [ ] **Step 6: Commit**

```powershell
git add tests/Feature/SearchBarTest.php resources/views/components/search-bar.blade.php resources/views/user/management/partials/index-search-section.blade.php
git commit -m "style: give user search a white background"
```
