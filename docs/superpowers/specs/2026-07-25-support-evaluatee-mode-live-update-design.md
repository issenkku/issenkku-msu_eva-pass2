# Live Updates for Evaluatee-Owned Support Fields

## Goal

Allow an Admin to enable or disable the evaluatee-owned indicator and weight
fields on an existing support criterion even after reports contain scores or
activity entries.

## Behavior

- Updating `allow_evaluatee_indicator` or `allow_evaluatee_weight` replaces the
  criterion's previous configuration immediately.
- Existing support scores, activity entries, evidence, and history remain
  unchanged.
- When a field is enabled, existing activity entries expose that field the next
  time the evaluatee edits the report. Values submitted by the evaluatee update
  the existing activity entry.
- When a field is disabled, previously stored values remain in the database but
  are hidden and excluded from the active input/calculation flow.
- Enabling either field still requires `allow_activity_entries`.
- Existing protections for removing grouped indicator items referenced by
  projects remain unchanged.

## Implementation Boundary

Remove only the update-time validation that rejects changes to
`allow_evaluatee_indicator` and `allow_evaluatee_weight` when report data
exists. Keep the normal configuration validation and persistence paths.

## Verification

- A feature test changes both flags after an activity entry exists and asserts
  that the update succeeds.
- The test confirms the existing activity entry is preserved.
- Existing grouped-indicator protection tests continue to pass.
