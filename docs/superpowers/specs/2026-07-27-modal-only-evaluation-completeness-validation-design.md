# Modal-Only Evaluation Completeness Validation Design

**Date:** 2026-07-27

## Goal

Allow users to save an evaluation draft or submit an evaluation even when some
evaluation data is incomplete or evidence required by an administrator has not
been attached. Completeness and required-evidence validation must block only the
Save action inside the data-entry modal.

## Current Behavior

The support-criteria validator is reused in two different contexts:

- the Save button inside the support data-entry modal; and
- the outer evaluation form's draft and final-submit flows.

The outer form therefore opens a support modal and blocks the request whenever
any support criterion is incomplete or lacks required evidence. Server-side
support and quality evidence checks can also reject the request after the
client-side checks are bypassed.

## Approved Behavior

### Outer evaluation form

- Save Draft must submit regardless of missing scores, descriptions, activity
  details, or administrator-required evidence.
- Submit Evaluation must open its existing confirmation dialog and submit
  regardless of those completeness conditions.
- Draft and final submission must not invoke a validator that opens a
  data-entry modal.
- Data that is present must still satisfy structural and safety validation,
  including allowed report status, authorization, criterion ownership, numeric
  shape, URL shape, and valid identifiers.

### Data-entry modal

- Save must continue to validate the active item only.
- Save must remain blocked when a field required by that modal is incomplete.
- Save must remain blocked when administrator-required evidence is absent.
- Save must remain blocked for invalid scores, URLs, or required change
  reasons.
- Validation errors stay inside the modal and focus the first invalid control.
- Cancel, backdrop close, and Escape continue to restore the snapshot taken
  when the modal opened.

## Design

### Client-side validation boundary

The shared support table script retains its item-level validator as the sole
validator used by the modal Save button. The outer evaluation form scripts stop
calling the whole-form support completeness validator.

The evaluatee form's final-submit validation also stops enforcing
administrator-required evidence as a whole-form completeness condition. Format
validation for values that users actually supplied remains in place.

No draft or final-submit action will automatically open the support data-entry
modal.

### Server-side validation boundary

Server-side request rules continue validating submitted value types, URL
formats, report workflow state, authorization, and criterion membership.

Service and controller checks that require every configured criterion to have
evidence are removed from the outer save/submit request path. Missing optional
evaluation values and missing administrator-required evidence are accepted for
both draft and final statuses.

The server does not attempt to reproduce modal completeness validation because
the approved workflow intentionally permits the outer form to persist or
submit an incomplete evaluation.

### Existing data behavior

Only values present in the submitted form are persisted under the existing
save semantics. This change does not synthesize placeholder scores, activity
entries, or evidence records. Existing modal cancellation and snapshot restore
behavior remains unchanged.

## Error Handling

- Modal completeness errors render in the modal error region.
- Invalid supplied data rejected by the server follows the existing validation
  response behavior.
- Authorization and invalid workflow transitions remain hard failures.
- Missing evaluation data or required evidence alone is not an outer-form
  validation error.

## Testing

Regression coverage will prove:

1. Save Draft does not invoke support completeness validation.
2. Submit Evaluation does not invoke support completeness validation and can
   reach its confirmation flow.
3. Evaluatee draft persistence succeeds without administrator-required
   evidence.
4. Evaluatee final submission succeeds without administrator-required
   evidence and advances the report status.
5. The modal Save button still calls item-level validation and remains blocked
   for incomplete fields or missing required evidence.
6. Invalid supplied URLs, unauthorized criteria, and disallowed report states
   remain rejected.

## Out of Scope

- Removing confirmation dialogs from final submission.
- Changing modal layout, styling, or field definitions.
- Changing score calculations.
- Changing role permissions or workflow status transitions.
- Automatically completing or defaulting missing evaluation data.
