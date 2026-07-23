# Confirmation Support Achievement Card Design

**Date:** 2026-07-23

**Status:** Approved design

## Goal

Add the missing `คะแนนผลสัมฤทธิ์ของงาน` card to the evaluatee
submission-confirmation modal whenever Support criteria exist.

## Root Cause

The confirmation modal contains cards for the Support weighted total and the
overall total, but it has no achievement card or modal element for the
achievement value. The modal update script therefore cannot display the
already-calculated Support achievement score.

## Selected Design

When `$scoreSummary['has_support']` is true, render two adjacent Support
cards:

1. `คะแนนสายสนับสนุน`
2. `คะแนนผลสัมฤทธิ์ของงาน`

The achievement card displays:

- Element ID: `modal-support-achievement-summary`
- Initial value: `0.00`
- Formula helper:
  `ผลรวมคะแนนถ่วงน้ำหนัก ÷ จำนวนระดับค่าเป้าหมาย: 5`

When Support criteria do not exist, neither Support card is rendered.

## Data Flow

The existing support score script calculates the current achievement value
and writes it to `support-achievement-summary`. When the confirmation summary
is refreshed, the modal script reads that displayed value and writes it to
`modal-support-achievement-summary`.

This avoids a second implementation of the divide-by-five rule and keeps the
main summary and confirmation modal synchronized.

## Layout

The achievement card counts as an additional visible score card:

- Support-only: Support, achievement, and Total use three columns at the small
  breakpoint.
- All categories: Quantity, Quality, Support, achievement, and Total use two
  columns at the small breakpoint and five columns at the large breakpoint.
- Mobile remains one column.

The existing card colors remain unchanged. The achievement card uses an
amber-compatible treatment to communicate that it is derived from Support.

## Scope

- Modify only the evaluatee confirmation modal and its existing update script.
- Do not change the main score-summary calculation.
- Do not change persistence, submission, validation, or other role modals.
- Preserve the previously approved category-presence visibility rules.

## Verification

- Render the modal with Support criteria and assert that both Support card IDs
  and the fixed formula helper are present.
- Render without Support criteria and assert that both Support card IDs are
  absent.
- Assert Support-only uses the three-card grid class.
- Assert the modal script copies the value from
  `support-achievement-summary` to
  `modal-support-achievement-summary`.
- Run focused confirmation-modal tests and the production build.

## Design Self-Review

The modal consumes the existing live achievement value instead of duplicating
the formula. The change remains limited to the confirmation surface that
already displays score cards and does not broaden behavior for other roles.
