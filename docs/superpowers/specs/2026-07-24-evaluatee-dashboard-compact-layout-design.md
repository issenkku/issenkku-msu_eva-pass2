# Evaluatee Dashboard Compact Layout Design

## Goal

Make the evaluatee dashboard easier to scan on wide screens by limiting the content width to approximately 1280 pixels and removing unnecessary vertical stretching, while preserving the existing information, visual styling, and responsive behavior.

## Scope

The change applies only to the evaluatee dashboard at `/evaluatee-dashboard`.

Included:

- Limit the dashboard content wrapper to Tailwind's standard `max-w-7xl` width.
- Keep the wrapper centered within the page.
- Align the unfinished-assignment panel with the other top-level dashboard cards.
- Let empty due-soon and overdue cards size naturally instead of stretching to match the overview chart.
- Preserve the existing three-column desktop overview and the current responsive column breakpoints.

Excluded:

- Changes to dashboard data, counts, filters, charts, or actions.
- Changes to manager, director, or evaluator dashboards.
- New colors, typography, copy, animation, or components.
- Refactoring the global Tailwind, Bootstrap, or CDN setup.

## Layout

On desktop, all top-level evaluatee dashboard sections share a centered 1280-pixel maximum width. The overview continues to use one flexible status column followed by two 340-pixel deadline columns at the existing `2xl` breakpoint.

The due-soon and overdue cards align at the top and use content-driven height. When either list is empty, its card no longer contains a large unused vertical area. On smaller screens, the existing single-column stacking remains unchanged.

## Implementation Direction

- Replace the unsupported `max-w-8xl` wrapper utility with the standard `max-w-7xl`.
- Remove the extra horizontal margin from the unfinished-assignment panel so all top-level cards share the same edges.
- Replace grid stretching with start alignment in the overview section.
- Remove full-height utilities from the three overview columns where they force equal heights.

## Verification

Add a focused view-structure regression test that checks:

- The evaluatee dashboard uses `max-w-7xl` and no longer uses `max-w-8xl`.
- The unfinished-assignment panel has no independent `mx-5` offset.
- The overview grid aligns items at the start.
- The due-soon and overdue cards do not use `h-full`.

Run the focused test, relevant evaluatee feature tests, and the frontend production build.
