# Nullable Admin Weight for Evaluatee-Weighted Support Criteria

## Goal

Do not require an Admin-defined support criterion weight when the Admin enables
the evaluatee-owned weight option.

## Data Model

- Change `support_criterias.weight` to nullable.
- A criterion with `allow_evaluatee_weight = true` stores its Admin weight as
  `NULL`.
- A criterion with `allow_evaluatee_weight = false` must have an Admin weight
  greater than `0` and no greater than `100`.
- Existing support activity entries, scores, evidence, and histories remain
  unchanged when the mode changes.

## Admin Interface

- On both create and edit forms, selecting “allow evaluatee to enter weight”
  clears and disables the Admin weight input.
- Deselecting the option enables the Admin weight input and requires the Admin
  to enter a valid value before saving.
- Client-side validation applies the same conditional rule as the server.
- The payload sends `weight: null` when evaluatee-owned weight is enabled.

## Server Validation and Persistence

- Store and update requests accept a nullable weight only when
  `allow_evaluatee_weight` is true.
- Store and update requests require a numeric weight in the range `(0, 100]`
  when the option is false.
- Persistence writes `NULL` when evaluatee-owned weight is enabled, replacing
  an existing Admin weight if the mode is changed later.
- Enabling evaluatee-owned weight continues to require
  `allow_activity_entries = true`.

## Scoring Boundary

When evaluatee-owned weight is enabled, the criterion-level Admin weight is not
used for active score calculation. Evaluation-list score capping remains based
on the list's configured total, such as `75`.

## Verification

- Migration tests prove the column accepts `NULL` and rolls back safely.
- Feature tests cover create and update with an empty Admin weight when the
  option is enabled.
- Feature tests keep rejecting an empty, zero, negative, or greater-than-100
  Admin weight when the option is disabled.
- View contract tests cover the create/edit toggle state, conditional
  validation, and `null` payload.
- Existing report activity data remains present after enabling the option.
