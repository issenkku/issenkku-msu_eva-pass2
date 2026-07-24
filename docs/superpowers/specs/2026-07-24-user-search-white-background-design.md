# User Search White Background Design

## Goal

Make the user-management search input use an opaque white background so the page background image does not reduce input readability.

## Scope

- Update the shared `search-bar` Blade component to accept an optional input class.
- Pass `bg-white` from the user-management search partial only.
- Preserve the current input dimensions, border, icon, placeholder, focus state, search button, query parameters, and automatic-search behavior.
- Do not change search bars on other pages.

## Implementation

Add an optional `inputClass` prop to `resources/views/components/search-bar.blade.php` and append it to the input class list. In `resources/views/user/management/partials/index-search-section.blade.php`, pass `input-class="bg-white"`.

## Verification

Add a view-contract test confirming that the user-management search passes `bg-white` while the shared component remains opt-in. Render or compile the relevant Blade view to catch syntax errors.
