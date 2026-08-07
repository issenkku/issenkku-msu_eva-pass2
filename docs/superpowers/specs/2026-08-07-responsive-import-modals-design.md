# Responsive Import Modals Design

## Goal

Keep the user import and subject import modal titles and actions visible at every viewport size while only their content areas scroll.

## Scope

- Update `#importUserModal` and `#subjectImportModal` only.
- Apply the same viewport behavior as the existing subject and workload form modals.
- Preserve all fields, routes, validation messages, file handling, upload behavior, and import scripts.
- Do not introduce a shared global modal abstraction or alter unrelated modals.

## User Import Modal

The existing custom Tailwind modal remains in place. Its panel becomes a viewport-constrained vertical flex container. The title and close button form a non-shrinking header. The import form becomes a flex column containing a scrollable body for the upload controls and feedback, followed by a non-shrinking action footer.

Validation and row-level import errors remain inside the scrollable body. This prevents long error output from moving the import and cancel buttons beyond the viewport.

## Subject Import Modal

The existing Bootstrap modal gains centered, scrollable, and small-screen fullscreen dialog utilities. Scoped layout hooks constrain the dialog to the viewport and make its form a vertical flex container. The modal header and footer remain visible while the modal body owns vertical scrolling.

## Responsive Behavior

- Desktop and tablet: each modal retains a one-rem margin around the viewport.
- Screens up to `575.98px`: each modal occupies the full viewport with no outer margin.
- Header and footer never shrink.
- Only the content body scrolls and uses contained overscroll behavior.

## Compatibility

The change is limited to markup classes and scoped styling. Existing import endpoints, form payloads, disabled-button handling, drag-and-drop behavior, error rendering, and modal open/close scripts remain unchanged.

## Testing

Blade contract tests will render each modal and verify:

- viewport-constrained panel or dialog hooks;
- small-screen fullscreen behavior;
- vertical flex containment;
- non-shrinking header and footer;
- body-only vertical scrolling;
- preservation of existing form and JavaScript data hooks.

Existing subject import feature tests, user import modal tests, JavaScript tests, and the production asset build provide regression coverage.
