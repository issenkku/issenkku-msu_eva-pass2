# Support Score Summary Layout Design

**Date:** 2026-07-23

**Status:** Approved visual direction

**Selected direction:** B — Compact two-row card

## Goal

Improve the score-summary hierarchy for support personnel by presenting the
weighted score total and the derived achievement score as one related group.
The calculation and all non-support criteria remain unchanged.

## Scope

- Apply only when `has_support` is true.
- Keep the existing summary heading and the separate overall-total card.
- Keep both existing dynamic values and DOM IDs:
  - `support-summary`
  - `support-achievement-summary`
- Keep the target-level count fixed at 5 and retain the current formula copy:
  `ผลรวมคะแนนถ่วงน้ำหนัก ÷ จำนวนระดับค่าเป้าหมาย: 5`
- Do not alter quantity, quality, director, or other scoring behavior.

## Layout

The two support metrics share one white card with a subtle blue border.

```text
┌─────────────────────────────────────────────────────┐
│ ผลรวมคะแนนถ่วงน้ำหนัก                         1.00 │
├─────────────────────────────────────────────────────┤
│ คะแนนผลสัมฤทธิ์ของงาน                        0.20 │
│ ผลรวมคะแนนถ่วงน้ำหนัก ÷ จำนวนระดับค่าเป้าหมาย: 5 │
└─────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────┐
│ คะแนนรวมทั้งหมด                               1.00 │
└─────────────────────────────────────────────────────┘
```

On narrow screens, each row may stack its label block above the value. The
order remains weighted total first and achievement score second.

## Visual System

- **Surface:** white support card inside the existing pale-blue summary panel.
- **Boundary:** one blue-gray border and a divider between the two rows.
- **Primary text:** existing dark blue for labels and score values.
- **Secondary text:** existing medium blue for the formula explanation.
- **Typography roles:**
  - Metric labels use the existing semibold body style.
  - Numeric values use the existing bold score style with tabular alignment.
  - Formula copy uses the existing smaller supporting-text style.
- **Spacing:** compact, consistent row padding; no nested card or heavy shadow.

The distinctive element is the shared card and divider: it makes the two
numbers read as a calculation pair without introducing a new visual language.

## Behavior and Accessibility

- Live JavaScript updates continue targeting the same element IDs.
- Values remain readable at mobile widths without horizontal scrolling.
- The design adds no animation or interactive control.
- Text contrast and semantic reading order follow the existing summary.

## Verification

- Extend the Blade view test to verify that both support metrics are inside the
  same grouped container.
- Confirm the support formula copy and target value remain unchanged.
- Confirm summaries without support criteria do not render the grouped card.
- Run the focused feature test and the relevant frontend test/build checks.

## Design Self-Review

The design deliberately avoids a generic dashboard tile grid: equal standalone
tiles would imply unrelated metrics. A single divided card communicates that
the achievement score is derived from the weighted total, while preserving the
visual rhythm of the existing page.
