# Support Grouped Indicator Field Visibility Design

**Date:** 2026-07-25

**Status:** Approved design, awaiting written-spec review

## Goal

Correct the support-criteria editor so selecting
`แยกโครงการตามตัวชี้วัดย่อย` hides the legacy
`ตัวชี้วัด/เกณฑ์การประเมิน` Rich Text field and does not require an administrator
to fill it.

The `กิจกรรม/โครงการ/งาน` Rich Text field must remain visible and required in
both grouped and non-grouped modes.

## Current Problem

The shared support-criteria template places the
`data-support-legacy-indicator` marker on the `กิจกรรม/โครงการ/งาน` label.
Both create and edit scripts use that marker to choose which field to hide when
grouped mode is selected. As a result, the scripts hide the activity field
instead of the legacy indicator field.

Server-side validation and payload handling already distinguish the two modes:

- non-grouped criteria require a visible-text `indicator`; and
- grouped criteria accept a null `indicator` and require at least one unique
  sub-indicator code.

The defect is therefore in the shared template's field marker, not in the
domain model or persistence rules.

## Selected Approach

Move the `data-support-legacy-indicator` marker from the
`กิจกรรม/โครงการ/งาน` label to the `ตัวชี้วัด/เกณฑ์การประเมิน` label.

Keep the existing create and edit toggle functions unchanged. They already hide
the element carrying this marker whenever grouped mode is selected and reveal
it when grouped mode is cleared.

This approach corrects the source of the mismatch and keeps create and edit
behavior aligned through their shared template.

## Required Behavior

### Non-Grouped Mode

- Show `กิจกรรม/โครงการ/งาน`.
- Show `ตัวชี้วัด/เกณฑ์การประเมิน`.
- Require visible Rich Text content in both fields.
- Persist both values through the existing payload.

### Grouped Mode

- Show and require `กิจกรรม/โครงการ/งาน`.
- Hide `ตัวชี้วัด/เกณฑ์การประเมิน`, including its required marker and editor.
- Do not require the hidden legacy indicator.
- Submit and persist `indicator` as null.
- Show and validate the existing sub-indicator list instead.

### Toggling Back

- Clearing grouped mode reveals the legacy indicator field again.
- The existing non-grouped validation requires visible Rich Text content before
  the template can be saved.

## Scope

The implementation changes only the shared support-criteria template and adds
focused regression coverage.

There are no database, model, controller, API, scoring, or evaluation-flow
changes. Existing grouped sub-indicator validation remains authoritative.

## Testing

Add a focused view-contract regression test that verifies:

1. `data-support-legacy-indicator` is attached to the label containing
   `.support_indicator`.
2. The label containing `.support_activity_name` does not carry that marker.
3. Both create and edit flows continue to use the existing shared template and
   toggle behavior.

Run the focused support-criteria template tests and the existing grouped
support-indicator feature tests.

## Alternatives Considered

### Change the JavaScript Selector

The toggle functions could directly locate the `.support_indicator` editor and
its parent. This would duplicate structural knowledge across the create and
edit scripts and make the behavior more sensitive to future markup changes.

### Leave the Field Visible but Optional

Removing only the required marker and validation would permit empty content but
would leave an unused field visible. This conflicts with the grouped-mode
design, where the sub-indicator list replaces the legacy indicator field.

## Design Self-Review

The design has one localized production change and one observable regression
contract. It preserves the existing validation boundary: activity content is
always required, while legacy indicator content is required only outside
grouped mode. No placeholders, unresolved decisions, or unrelated changes
remain.
