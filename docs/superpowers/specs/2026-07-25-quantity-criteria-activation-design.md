# Quantity Criteria Activation and Permanent Deletion Design

**Date:** 2026-07-25

**Status:** Awaiting user review

**Selected direction:** Explicit activation state with separate, guarded permanent deletion

## Goal

Make the “เกณฑ์ด้านปริมาณ” checkbox control whether quantity criteria are
active without using the presence or absence of criteria rows as the activation
signal.

The required behavior is:

- Clearing the checkbox preserves all saved quantity configuration and scoring
  data but hides the quantity criteria from evaluatees, evaluators, and
  directors.
- Selecting the checkbox again restores the same saved configuration and makes
  it available to those roles.
- Permanent deletion is a separate administrator action with confirmation and
  dependency checks.

## Current Problem

The editor currently sends an empty `quantity_main_criterias` array when the
checkbox is cleared. The update endpoint interprets omitted rows as removed
children and deletes any quantity criteria that are not protected by
`workload_forms`.

This produces two inconsistent outcomes:

- unused criteria are deleted and cannot be restored; and
- criteria referenced by workload forms survive and remain visible because the
  runtime screens infer activation from the surviving rows.

There is no persisted quantity-enabled state today.

## Data Model

Add a boolean `quantity_enabled` column to `evaluation_lists`.

- New evaluation lists default to `false`.
- Existing evaluation lists with at least one quantity sub-criterion are
  backfilled to `true`.
- Existing evaluation lists without quantity sub-criteria remain `false`.
- Add the field to `EvaluationList::$fillable` and cast it to boolean.

The activation state belongs to the evaluation list because the checkbox is
rendered and edited at that level. Quantity main and sub-criteria remain the
saved configuration; they are not soft-deleted when the state changes.

## Administrator Editor

### Loading

The report-structure response used by the administrator editor includes
`quantity_enabled` independently from `quantity_main_criterias`.

Disabled quantity criteria are still returned to the administrator editor so
the existing saved blocks can be restored when the checkbox is selected again.

### Toggling

Clearing the checkbox:

- hides the quantity editor section;
- sets `quantity_enabled` to `false`;
- does not remove quantity blocks from the DOM; and
- does not request deletion of saved quantity rows.

Selecting the checkbox:

- shows the existing blocks and values;
- sets `quantity_enabled` to `true`; and
- requires the normal quantity validation before saving.

The editor submits `quantity_enabled` explicitly. Saved quantity rows are
submitted independently of the enabled state. An untouched blank template for
an evaluation list that has never had quantity criteria is not submitted.

When `quantity_enabled` is false, omission of
`quantity_main_criterias` means “preserve existing quantity configuration,” not
“delete it.” Row removal remains available only while quantity criteria are
enabled.

### Validation

- `quantity_enabled` is required and boolean for every evaluation list.
- An enabled evaluation list must contain at least one complete quantity main
  criterion and at least one complete quantity sub-criterion per main
  criterion.
- A disabled evaluation list may retain saved quantity criteria without making
  those fields required in the active form.
- Clearing the checkbox must not trigger the existing “must have at least one”
  delete alert.

## Runtime Visibility and Scoring

`quantity_enabled` is the source of truth for active runtime quantity criteria.

When it is false, quantity criteria are excluded from:

- the evaluatee evaluation form and workload links;
- the evaluator form;
- the director/approval form;
- manager or administrative views that reuse the scoring components;
- quantity score summaries and overall score calculations;
- exports and other report projections intended to represent the active
  evaluation form.

Historical quantity scores, score histories, workload forms, and workload
entries remain stored. Re-enabling the quantity criteria makes the existing
configuration and applicable saved scores visible again.

Runtime code should use one named relationship/scope or one shared query rule
for active quantity criteria instead of duplicating ad hoc checks across
controllers. Administrator configuration endpoints may deliberately use the
unfiltered relationship.

Score-write endpoints must reject quantity sub-criteria whose parent evaluation
list has `quantity_enabled = false`, even if a client submits a valid existing
sub-criterion ID manually. Hiding the fields in HTML is not sufficient
authorization.

## Permanent Deletion

Add a separate administrator action labeled:

`ลบข้อมูลเกณฑ์ปริมาณทั้งหมด`

The action belongs to one evaluation list and is available only after quantity
criteria have been disabled. It is not the same action as clearing the
checkbox.

### Confirmation

Before sending the request, show a destructive confirmation naming the
evaluation list and explaining that the operation cannot be undone.

### Server-Side Dependency Check

The server performs the authoritative dependency check in the deletion
transaction. Deletion is blocked with HTTP 409 when any targeted quantity
sub-criterion is referenced by:

- `workload_forms` (including their fields, items, and entries);
- `quantity_scores`; or
- `quantity_score_histories`.

The response returns safe aggregate counts by dependency type so the UI can
explain why deletion was blocked without exposing unrelated report data.

There is no force-delete option in this scope. Administrators must resolve the
dependent records through their owning workflows before retrying.

### Successful Deletion

When no dependency exists:

- delete the quantity sub-criteria for that evaluation list;
- allow existing cascades to delete unreferenced subordinate configuration;
- delete a quantity main criterion and its formula only when it has no
  remaining sub-criteria in another evaluation list;
- leave `quantity_enabled` set to `false`;
- return success and remove the preserved quantity blocks from the editor; and
- record the actor, evaluation-list ID, and deleted row counts in the existing
  activity log.

The endpoint verifies that the evaluation list belongs to the criteria version
in the route and is restricted to the existing administrator middleware.

## API and Error Handling

- The normal report-structure update saves activation changes and configuration
  edits but does not perform permanent bulk deletion.
- The permanent-delete endpoint is an explicit `DELETE` route scoped to the
  criteria version and evaluation list.
- Return HTTP 422 for malformed activation/configuration payloads.
- Return HTTP 409 when permanent deletion is blocked by dependencies.
- Return HTTP 404 when the criteria version and evaluation list do not belong
  together.
- All activation and deletion changes run inside database transactions.

## Compatibility and Migration

The backfill preserves current visible behavior on deployment:

- evaluation lists currently showing quantity criteria remain enabled; and
- evaluation lists with no quantity criteria remain disabled.

Existing quantity data is not deleted by the migration. No quality or support
criteria behavior changes.

## Verification

Add focused feature and UI-contract coverage for:

1. Migration/backfill semantics for evaluation lists with and without quantity
   sub-criteria.
2. Clearing the checkbox persists `quantity_enabled = false` and preserves
   main criteria, sub-criteria, formulas, workload configuration, scores, and
   histories.
3. Disabled quantity criteria are absent from evaluatee, evaluator, director,
   summary, calculation, and export projections.
4. Score-write endpoints reject a disabled quantity sub-criterion.
5. Selecting the checkbox again restores the same criteria and saved values.
6. Permanent deletion returns 409 with dependency counts when workload or score
   data exists and deletes nothing.
7. Permanent deletion succeeds when there are no dependencies, cleans up
   unreferenced main criteria, and does not delete a main criterion still used
   by another evaluation list.
8. Quality and support criteria continue to behave unchanged.

Run the focused report-structure and role evaluation tests, followed by the
full test suite appropriate for the touched controllers.

## Out of Scope

- Applying the same activation model to quality or support criteria.
- Force-deleting historical scores or workload submissions.
- Changing quantity formulas or score calculations.
- Redesigning the role evaluation screens beyond hiding disabled quantity
  sections.

## Design Self-Review

The design separates three concepts that the current implementation conflates:
saved configuration, runtime activation, and permanent deletion. A single
evaluation-list flag provides a stable activation source, while the guarded
delete action prevents an ordinary checkbox interaction from destroying
configuration or historical data. Runtime write validation closes the gap where
a hidden but existing criterion could otherwise still accept submitted scores.
