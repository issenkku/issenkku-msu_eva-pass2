# User Table Continuous Row Numbers Design

## Goal

Display continuous row numbers across pagination on the staff management page.

## Problem

`resources/views/user/management/partials/index-table.blade.php` currently passes `$index + 1` to each user row. The collection index restarts at zero on every page, so every pagination page displays 1–10.

The Laravel paginator already exposes the absolute position of the first record through `firstItem()`. Other paginated management tables in this repository use that value successfully.

## Behavior

- Page 1 displays 1–10.
- Page 2 displays 11–20.
- Page 3 displays 21–30.
- The final page continues from its absolute position even when it contains fewer than ten records.
- Search and filter results form their own paginated result set:
  - the first result page starts at 1;
  - later result pages continue from 11, 21, and so on.
- Empty results render the existing empty state and do not calculate a row number.

## Implementation

Change only the index passed from the staff table partial to the existing `x-user-table` component:

```blade
:index="$users->firstItem() + $index"
```

Do not change the row component, controller query, sorting, filters, pagination size, or footer summary.

## Testing

Add a feature regression test at `tests/Feature/UserManagementPaginationTest.php`. Build a paginator positioned on page 2, render the staff table partial, and assert that the rendered row numbers are 11 and 12 rather than 1 and 2.

Run:

- the focused staff-management view test;
- `tests/Feature/User/UserControllerTest.php`;
- PHP formatting check;
- the full PHP test suite.

## Acceptance Criteria

1. Staff row numbers continue across pagination.
2. Search and filter pagination use the same continuous numbering rule.
3. Existing user data, actions, bulk selection, pagination links, and empty state remain unchanged.
