# Empty Subject Credits Default to Zero

## Goal

When creating or editing a subject, treat an empty value in any of the four credit fields as the integer `0` instead of showing a required-field error.

Covered fields:

- `credits`
- `lecture_credits`
- `lab_credits`
- `self_study_credits`

## Behavior

- The subject modal permits all four credit inputs to be left empty.
- Client-side validation normalizes an empty credit input to `0` before submission.
- The Laravel create and update requests independently normalize missing, `null`, or empty-string credit values to `0` before validation.
- Explicit `0` remains valid.
- Non-integer and negative values remain invalid.
- The same behavior applies to subject management and subject creation from the evaluatee workload page.
- Native form submissions and JSON submissions use the same server-side rule.

## Implementation Boundaries

- Keep normalization in the existing subject form requests so every submission path has a reliable server-side default.
- Update the two existing subject-modal client validation paths so the UI does not reject empty credit inputs and submits normalized values.
- Remove the visual required indicators and HTML `required` attributes from the four credit inputs because the fields now have defaults.
- Do not change subject names, subject codes, credit calculation rules, or database schema.

## Testing

- Feature tests prove create and update persist all four empty credit values as integer zero.
- Feature tests prove negative values are still rejected.
- JavaScript tests prove the subject form converts empty credit inputs to `0` without native navigation.
- Existing subject async-mutation and workload-subject tests remain green.
