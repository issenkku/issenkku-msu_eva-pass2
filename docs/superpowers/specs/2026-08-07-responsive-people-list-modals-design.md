# Responsive People List Modals Design

## Goal

Keep modal controls visible while long, dynamically generated reviewer and evaluatee lists scroll within the available viewport.

## Scope

- Update the four reviewer-list modals used by the admin dashboard, evaluatee summary, director dashboard, and manager dashboard.
- Update the evaluatee-list modal on the assignment data page.
- Preserve all existing element IDs, data hooks, list rendering, searching, loading states, and open/close scripts.
- Do not consolidate the modals into a shared component in this change.

## Reviewer List Modals

Each existing custom Tailwind modal keeps its current markup identity and JavaScript contract. The outer overlay retains a small viewport inset. The panel becomes a viewport-constrained vertical flex container with hidden overflow. Its header is non-shrinking, and its dynamic list body consumes the remaining space and owns vertical scrolling.

All four modals use the same layout contract even though their IDs and scripts remain separate.

## Evaluatee List Modal

The fixed `top-20` panel offset is removed. The overlay centers the panel within the available viewport and supplies the outer spacing. The panel becomes a viewport-constrained vertical flex container.

The title row and search field stay in a non-shrinking top region. Only `#evaluateesContent` scrolls. Loading, empty, error, filtered, and populated list states continue to render through the existing script without changes.

## Responsive Behavior

- Desktop and tablet: panels remain centered with a one-rem viewport inset.
- Small screens: panels may use nearly the full viewport while preserving the one-rem inset for an accessible close target and visible backdrop boundary.
- Headers and search controls never shrink or scroll away.
- Dynamic list bodies use contained vertical overscroll.

## Compatibility

The change is limited to Blade layout classes and the existing assignment modal styles where needed. No controller, endpoint, payload, search algorithm, or JavaScript selector changes are permitted.

## Testing

Blade contract tests will render each reviewer modal and the assignment evaluatee modal and verify:

- viewport-relative maximum height;
- flex-column panel containment;
- non-shrinking top controls;
- list-only vertical scrolling and contained overscroll;
- removal of the fixed `top-20` offset;
- preservation of existing IDs and close/open hooks.

Existing evaluation control tests, assignment page tests, JavaScript tests, and the production asset build provide regression coverage.
