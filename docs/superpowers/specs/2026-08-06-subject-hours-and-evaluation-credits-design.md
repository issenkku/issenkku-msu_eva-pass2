# Subject Hours and Evaluation Credits Design

**Date:** 2026-08-06

**Status:** Approved in conversation; pending written-spec review

## Goal

Represent subject contact hours separately from the independent credit values used by workload formulas, while preserving the existing three-value subject display and allowing an administrator to select and delete more than ten visible subjects at once.

## Problem

The subject model currently stores these four independent integers:

- `credits`
- `lecture_credits`
- `lab_credits`
- `self_study_credits`

The three split-credit fields are also used to render curriculum-style tuples such as `(3 / 0 / 0)`. In source curriculum data, tuple values can mean weekly hours rather than evaluation credits. A practical component of two or three hours may both represent one evaluation credit, so the system cannot derive evaluation credits from hours with one fixed conversion rule.

The subject index is paginated at ten rows. Its bulk-selection control selects only checkboxes rendered on the current page, so an administrator cannot select more than ten subjects in one deletion unless the page size increases.

## Considered Approaches

### 1. Convert hours to credits automatically

Rejected. The conversion is not uniform: both two and three hours can represent one credit. A hard-coded divisor would silently produce incorrect workload scores.

### 2. Reinterpret the existing split-credit fields as hours

Rejected. Workload formulas already consume `lecture_credits`, `lab_credits`, and `self_study_credits`. Reinterpreting those fields would preserve the ambiguity and make formula behavior depend on curriculum notation.

### 3. Store independent hour and credit fields

Selected. Add three hour fields and retain the existing credit fields for evaluation. Administrators enter authoritative values from the curriculum source; the application does not infer a relationship or enforce a total across them.

## Data Model

Each subject has seven independent non-negative integer values:

| Field | Meaning | Default |
|---|---|---:|
| `credits` | Total official subject credits | `0` |
| `lecture_credits` | Lecture credits used by workload formulas | `0` |
| `lab_credits` | Practical credits used by workload formulas | `0` |
| `self_study_credits` | Self-study credits used by workload formulas | `0` |
| `lecture_hours` | Weekly lecture hours | `0` |
| `lab_hours` | Weekly practical hours | `0` |
| `self_study_hours` | Weekly self-study hours | `0` |

The database migration adds the three hour columns as non-null integers with a default of zero. Existing rows receive zero hours. No existing credit value is copied or converted.

All seven values remain independent:

- Do not calculate credits from hours.
- Do not calculate total credits from split credits.
- Do not require any fields to add up.
- Accept integers greater than or equal to zero only.
- Normalize missing, blank, or `null` form and workbook cells to integer zero.

## Workload Calculation

Workload formulas continue to consume only the existing variables:

- `credits`
- `lecture_credits`
- `lab_credits`
- `self_study_credits`

The new hour fields do not replace, alter, or fall back into formula variables. They are stored curriculum data and may be exposed as formula variables only in a separately designed future change.

The existing preferred-credit selection behavior remains unchanged: lecture contexts use lecture credits, practical contexts use practical credits, and general contexts use total credits.

## Subject Tuple Display

The large value in the subject credit column always displays `credits`.

The three values in parentheses are chosen as one complete set, never field by field:

1. If at least one of `lecture_hours`, `lab_hours`, or `self_study_hours` is greater than zero, render all three hour values.
2. If all three hour values equal zero, render `lecture_credits`, `lab_credits`, and `self_study_credits`.

Examples:

```text
credits = 3
hours = (2 / 0 / 0)
split credits = (1 / 1 / 0)
display = 3, then (2 / 0 / 0)
```

```text
credits = 3
hours = (0 / 0 / 0)
split credits = (3 / 0 / 0)
display = 3, then (3 / 0 / 0)
```

Apply this rule at every application location that renders the three-value subject tuple. Preserve the current visual format; do not add a `ชั่วโมง` or `หน่วยกิต` label beside the tuple.

The display rule must live behind one shared subject interface or helper so all renderers use identical behavior.

## Manual Subject Form

Group numeric inputs into two visually distinct sections:

1. Credit information
   - Total credits
   - Lecture credits
   - Practical credits
   - Self-study credits
2. Hour information
   - Lecture hours
   - Practical hours
   - Self-study hours

Every input defaults to zero. Client-side and server-side validation normalize blank values to zero and reject negative or non-integer values. Create and update operations, including asynchronous modal submissions, preserve all seven values exactly.

## Excel Import and Export

The new workbook schema contains all seven numeric columns and uses the same credit/hour grouping as the manual form.

- New templates and exports include the three hour columns.
- Export writes all seven stored values directly; it never exports tuple fallback values in place of raw data.
- Blank numeric cells normalize to zero.
- Preview, comparison, snapshot, and confirmation flows carry the hour fields without loss.
- Import validation rejects negative and non-integer hour values.

Legacy workbooks without all three hour headers are rejected rather than inferred. The error identifies the workbook as an old format and directs the administrator to download the new template. The importer does not copy legacy split-credit values into hour fields or calculation-credit fields.

## Subject Pagination and Bulk Delete

Add a per-page selector with exactly these values:

```text
10, 25, 50, 100
```

The default remains 10. Reject or replace unsupported `per_page` query values with 10. Preserve `per_page` alongside search, status, and sort query parameters.

Bulk selection remains scoped to the current page:

- “Select all” selects every visible subject on the current page only.
- An administrator may select any subset of visible subjects and delete them in one request.
- The confirmation modal displays the exact selected count.
- There is no “all subjects in the system” selection or deletion control.

The existing bulk endpoint continues to delete only the submitted, validated subject IDs.

## Existing Data and Rollout

The migration is backward-compatible for existing rows because their hour fields default to zero. Until a row is replaced or updated with hour data, tuple rendering falls back to its existing split-credit values.

Administrators may use the page-size selector to select and delete up to 100 visible legacy subjects at a time, then import authoritative data using the new workbook template. The migration itself does not delete subject records.

## Error Handling

- Manual entry returns field-specific validation errors for negative or non-integer values.
- Import preview returns header-specific guidance for a legacy workbook.
- Import rows report field-specific errors for invalid hour values.
- Unsupported page sizes do not affect query scope and resolve to the default page size of 10.
- Bulk deletion keeps the existing validation that every submitted ID is distinct and exists.

## Testing

Automated coverage must prove:

1. The migration supplies zero hour values to existing subjects.
2. Create and update paths normalize blank hour and credit inputs to zero and persist all seven independent values.
3. Negative and decimal hour values fail validation.
4. Tuple rendering chooses the complete hour set when any hour is positive.
5. Tuple rendering chooses the complete split-credit set when all hours are zero.
6. Every tuple-rendering location uses the same display contract.
7. Formula evaluation continues to receive credit values even when hour values differ.
8. Workbook export writes all seven raw values.
9. Workbook preview and confirmation preserve all seven values.
10. Legacy headers are rejected with new-template guidance.
11. Blank workbook numeric cells become zero.
12. Subject pagination accepts 10, 25, 50, and 100, defaults to 10, rejects unsupported values, and preserves filters.
13. Bulk deletion accepts and deletes more than ten submitted IDs while deleting no unselected subjects.

## Out of Scope

- Automatic conversion between hours and credits
- Cross-field total validation
- Decimal hour or credit values
- Using hour fields in workload formulas
- Selecting subjects across multiple pages
- A delete-all-subjects operation
- Automatic conversion of legacy Excel workbooks
