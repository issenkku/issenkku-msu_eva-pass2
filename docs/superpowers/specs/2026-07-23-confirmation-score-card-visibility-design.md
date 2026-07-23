# Confirmation Score Card Visibility Design

**Date:** 2026-07-23

**Status:** Approved design

## Goal

In the evaluatee submission-confirmation modal, show only score categories
that have configured criteria. A category with criteria remains visible when
its current score is `0.00`.

## Root Cause

The main score summary already uses the shared
`EvaluationScoreSummary` visibility flags. The evaluatee confirmation modal
instead renders Quantity, Quality, and Support cards unconditionally, so a
missing category appears as a misleading `0.00` card.

## Selected Design

Use the existing `$scoreSummary` passed to the evaluatee evaluation view as
the sole visibility source:

- Render Quantity only when `has_quantity` is true.
- Render Quality only when `has_quality` is true.
- Render Support only when `has_support` is true.
- Render Total unconditionally.
- Do not use the numeric score to decide visibility.
- Keep every rendered value initialized to `0.00`; the existing submission
  summary script updates the value when the modal opens.

This is the same category-presence rule used by the shared score-summary
partial and therefore preserves zero scores correctly.

## Layout

The score-card grid selects a static responsive column class from the number
of visible cards, including Total:

- 2 visible cards: two columns at the small breakpoint.
- 3 visible cards: three columns at the small breakpoint.
- 4 visible cards: four columns at the small breakpoint.

The mobile layout remains one column. No card placeholder is rendered for a
missing category.

## Scope

- Modify only `partials.evaluatee-confirmation-modal`, the only confirmation
  modal that currently contains score cards.
- Do not add score cards to evaluator, manager, or director confirmation
  modals.
- Do not alter the category/list detail sections below the cards.
- Do not change score calculations, submission behavior, or validation.

## JavaScript Behavior

The current script obtains each modal score element with
`document.getElementById` and updates it only when the element exists. This
already supports conditionally absent cards, so no new client-side visibility
logic is required.

## Verification

- Render the modal with Support criteria only and assert that Support and
  Total cards exist while Quantity and Quality cards do not.
- Render the modal with Quantity criteria and a `0.00` score and assert that
  the Quantity card remains present.
- Assert the responsive grid class matches the number of rendered cards.
- Keep the existing support detail and JavaScript update contracts passing.
- Run the focused confirmation-modal tests and production build.

## Design Self-Review

The design removes duplicated category-detection logic rather than adding
another client-side rule. It is limited to the single modal in scope and does
not change behavior for roles whose confirmation modal has no score cards.
