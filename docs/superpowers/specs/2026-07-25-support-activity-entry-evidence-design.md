# Support Activity Entry Evidence Design

**Date:** 2026-07-25

**Status:** Approved design

## Goal

Move support evidence inputs from the end of a support criterion to the end of
each activity/project/work entry. Each entry owns and manages its own evidence
links instead of sharing one criterion-level list.

This change applies to support criteria where
`allow_activity_entries = true`. Criteria that do not allow activity entries
retain the existing criterion-level evidence behavior.

## Data Model

Reuse `evidence_answers` as the single store for support evidence and add a
nullable `support_activity_entry_id` foreign key:

- The foreign key references `support_activity_entries.id`.
- Deleting an activity entry cascades to its evidence.
- Evidence for criteria without activity entries keeps
  `support_activity_entry_id = null`.
- New evidence for an activity-enabled criterion must reference an activity
  entry that belongs to the same report and support criterion.

No second evidence table and no JSON evidence column will be introduced.

## Development-Stage Data Reset

During the migration, delete existing support evidence rows belonging to
criteria where `allow_activity_entries = true`. These links will not be
migrated to an activity entry because the system is still under development
and the user explicitly approved discarding them.

Existing evidence for criteria where `allow_activity_entries = false` remains
unchanged.

## Evaluatee Interface

For an activity-enabled criterion:

- Remove the criterion-level evidence section from the bottom of the editor.
- Render an evidence section at the end of every activity entry card.
- The section contains the label `หลักฐาน`, a
  `+ เพิ่มลิงก์หลักฐาน` button, and URL inputs belonging only to that entry.
- A newly added activity entry starts with one empty evidence URL input.
- Users may add and remove multiple evidence URL inputs independently within
  each activity entry.
- Input names include the activity entry index so submitted evidence stays
  associated with the correct activity.
- Removing an unsaved activity also removes its unsaved evidence inputs from
  the form.

For a criterion that does not allow activity entries, retain the current
criterion-level evidence editor.

## Persistence Flow

The support evaluation save operation processes each activity entry and its
evidence together in a database transaction:

1. Validate the submitted activity entry and its evidence links.
2. Create or update the activity entry.
3. Replace that entry's evidence rows with the submitted non-empty links.
4. Delete activity entries removed by the evaluatee; their evidence is
   deleted through the foreign key.
5. Continue saving criterion-level score data as before.

Reviewers may continue editing activity content according to the existing
role and modification-reason rules. Evidence edit permissions remain aligned
with the existing support evidence permissions.

The server must reject an activity entry ID that does not belong to the
submitted report and support criterion.

## Required-Evidence Rule

For an activity-enabled criterion with `require_evidence = true`, saving or
submitting the evaluation requires at least one non-empty, valid evidence URL
across all of that criterion's activity entries.

The rule does not require every activity entry to contain a link.

For criteria without activity entries, the existing criterion-level required
evidence rule remains unchanged.

## Read Models and Read-Only Views

Load support evidence grouped by `support_activity_entry_id` and expose the
links on each activity entry in saved order.

Evaluatee, reviewer, confirmation, summary, and report/export views must
display each link with its owning activity entry. Links open safely in a new
tab using `target="_blank"` and `rel="noopener noreferrer"`.

Criterion-level evidence remains visible only for criteria where activity
entries are disabled.

## Client-Side Behavior

The activity-entry template used when adding a new entry must include its own
evidence container and initial empty URL input. Reindexing activity entries
must reindex their nested evidence input names as well.

Evidence controls must not move links between activity entries. Existing
rich-text editor setup, grouped-indicator placement, score calculation, and
weighted score behavior remain unchanged.

## Validation and Error Handling

- Evidence values are optional unless the criterion requires evidence.
- Every non-empty evidence value must be a valid URL under the existing
  support evidence validation policy.
- Validation errors identify the activity entry and evidence input that
  failed.
- A failed validation or persistence operation leaves activity entries,
  evidence, and scores unchanged through transaction rollback.

## Testing

Add regression coverage for:

- The migration adds the activity-entry foreign key.
- The migration deletes existing evidence only for activity-enabled support
  criteria.
- Creating and updating an activity entry persists its own evidence links.
- Two activity entries under one criterion keep independent evidence lists.
- Deleting an activity entry deletes its evidence.
- Foreign activity entry IDs are rejected.
- Required evidence accepts a link on any activity entry and rejects a
  criterion with no links.
- The evaluatee editor renders evidence controls at the end of every activity
  entry and omits the criterion-level controls for activity-enabled criteria.
- Criteria without activity entries retain criterion-level evidence.
- Newly added client-side activity entries contain correctly indexed evidence
  inputs.
- Read-only views and exports associate links with the correct activity.

Run the focused PHP and JavaScript tests, the broader support evaluation test
suite, and the production asset build before completion.

## Out of Scope

- Migrating existing activity-enabled criterion evidence to an activity
  entry.
- Requiring evidence on every individual activity entry.
- Changing support score, weight, or achievement score calculations.
- Changing quantity or quality evidence behavior.
- Adding file uploads; evidence remains URL-based.
