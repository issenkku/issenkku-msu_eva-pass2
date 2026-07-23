# Navigation Active State Design

**Date:** 2026-07-23  
**Scope:** Shared authenticated navigation in `resources/views/layouts/app.blade.php`

## Goal

Show users which navigation section contains the page they are currently
viewing. The state must remain correct on list, detail, create, and edit pages,
and it must be conveyed consistently in desktop and mobile navigation.

## Visual Direction

The navigation remains a compact institutional control bar. The active state
uses the system's existing purple accent as a location marker rather than
introducing a new navigation style.

### Tokens

| Token | Value | Use |
| --- | --- | --- |
| Navigation navy | `#0f172a` | Existing desktop navigation background |
| Navigation slate | `#334155` | Existing border and inactive hover |
| Active purple | `#8b5cf6` | Active marker and icon accent |
| Active lavender | `#c4b5fd` | Active text/icon highlight on navy |
| Active wash | `rgba(139, 92, 246, 0.18)` | Desktop active background |
| Mobile wash | `#f3e8ff` | Mobile and dropdown active background |
| Mobile active text | `#6d28d9` | Mobile and dropdown active label |

Typography continues to use the application's current Kanit typography. No
font or dependency is added.

### Desktop State

The active top-level item has:

- a subtle purple wash;
- lavender text and icon;
- a 3px purple inset marker along its bottom edge;
- `aria-current="page"` for a direct page, or `aria-current="true"` for an
  active parent dropdown.

The treatment uses no continuous animation. Existing hover transitions remain
short and must respect the current navbar behaviour.

### Dropdown State

When a page belongs to a dropdown:

- the dropdown trigger receives the desktop active treatment;
- the exact child item receives a light-purple background and dark-purple
  text;
- the child receives `aria-current="page"`.

### Mobile State

A direct active item or exact active dropdown child receives:

- the light-purple background;
- a 4px purple left marker;
- dark-purple text and icon.

The Settings mobile dropdown trigger is active and expanded when one of its
children owns the current route. The user can still collapse it manually.

## Route Ownership

Route names are the source of truth. URL-string comparisons and client-side
path detection are not used.

### Primary Navigation

| Navigation item | Owned route names |
| --- | --- |
| Admin “หน้าหลัก” | `dashboard`, `dashboard.*`, `admin.show` |
| Manager “หน้าหลัก” | `manager.dashboard`, `manager.show` |
| Director “หน้าหลัก” | `director.dashboard`, `director.show` |
| “หน้าประเมินผู้อื่น” | `evaluator.*` |
| “หน้าประเมินตนเอง” | `evaluatee.dashboard`, `evaluation.show`, `evaluatee.workload` |
| User profile dropdown | `profile.show`, `profile.edit` |

Only navigation items visible for the authenticated user's roles participate
in active-state rendering.

### Admin Navigation Groups

| Parent item | Owned route names | Child ownership |
| --- | --- | --- |
| ข้อมูลผู้ใช้ | `users.*`, `user.management.log*` | Users and activity-log children are selected independently |
| จัดการเกณฑ์ | `criteria_config.*`, `assignment-data.*` | Criteria structure and evaluation rounds are selected independently |
| ตั้งค่า | `settings.*`, `departments.*`, `positions.*`, `job-level.*`, `subjects.*` | Each settings child is selected independently |

Routes that perform POST, PUT, PATCH, or DELETE actions do not render pages but
remain correctly classified if validation redirects back to their owning
screen.

## Server-Side State Model

The layout computes a small set of named booleans once, before rendering either
navigation:

- `$isAdminHomeActive`
- `$isManagerHomeActive`
- `$isDirectorHomeActive`
- `$isEvaluatorActive`
- `$isEvaluateeActive`
- `$isUserDataActive`
- `$isCriteriaManagementActive`
- `$isSettingsActive`
- `$isProfileActive`

Child booleans are computed for each dropdown link. Both desktop and mobile
markup consume these shared values so the two navigation variants cannot
drift.

A small Blade expression applies:

- `is-active` when the boolean is true;
- `aria-current` only when the item owns the current route.

No JavaScript is needed to determine the current item.

## Markup Corrections

The three current admin dropdown triggers reuse the same
`id="settingDropdown"`. While adding active states, replace these duplicated
IDs with unique values:

- `userDataDropdown`
- `criteriaManagementDropdown`
- `settingsDropdownDesktop`

Each dropdown menu's `aria-labelledby` must point to its matching trigger.

## Accessibility

- Active state is communicated by colour, background, and a structural edge
  marker, not colour alone.
- Direct active links use `aria-current="page"`.
- Active parent dropdown triggers use `aria-current="true"`.
- Keyboard focus remains visibly distinct from active state.
- Active dropdown child contrast must meet WCAG AA for normal text.
- Mobile Settings is expanded on initial render when a child is active and its
  button exposes `aria-expanded="true"`.

## Responsive Behaviour

Desktop behaviour applies at the layout's existing `xl` breakpoint. Mobile
active state applies to the existing off-canvas menu without changing its
width, ordering, or role visibility rules.

## Testing

Feature tests will render representative routes for an authenticated admin and
verify:

1. the dashboard marks only “หน้าหลัก” active;
2. `users.index` marks the user-data parent and users child active;
3. `user.management.log` selects the activity-log child;
4. `criteria_config.edit` marks the criteria parent and criteria child active;
5. `assignment-data.edit` selects the evaluation-round child;
6. each Settings route activates the Settings parent and matching child;
7. profile routes activate the profile dropdown;
8. desktop and mobile variants expose matching active-state hooks;
9. inactive navigation items do not receive `aria-current`;
10. all desktop dropdown trigger IDs are unique.

Existing role-visibility tests must continue to pass.

## Out of Scope

- Reordering or renaming navigation items.
- Changing role permissions or route middleware.
- Rebuilding the mobile menu.
- Making the external handbook link active.
- Adding breadcrumb navigation.
