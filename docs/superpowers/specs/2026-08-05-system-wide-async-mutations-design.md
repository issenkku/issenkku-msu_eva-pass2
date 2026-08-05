# System-wide Async Mutations Design

## Goal

Make in-page create, update, delete, copy, import-from-previous, and settings-save actions complete without a full document refresh while preserving the user's current UI context. Workflow transitions and file-import flows continue to navigate normally.

## Scope

The change covers these ten user-facing areas:

1. Evaluatee workload: add a subject, import workload from a previous cycle, save the aggregate workload score, and preserve the already-async workload-entry behavior.
2. Departments: create, update, delete, and bulk delete.
3. Positions: create, update, delete, and bulk delete.
4. Job levels: create, update, delete, and bulk delete.
5. Subjects: create, update, delete, and bulk delete. Spreadsheet import remains a navigation flow.
6. Users: create, update, delete, bulk status update, and bulk delete. Spreadsheet import remains a navigation flow.
7. Roles: create from the index modal and delete from the index table. The dedicated edit page continues to redirect to the index after save.
8. Assignment data: delete from the index without the existing delayed reload. Dedicated create and edit pages continue to redirect.
9. Criteria versions: copy and delete from the index without the existing delayed reload. Dedicated creation may navigate to the index; editing remains async.
10. Website settings: save text settings, logo, background, background selection, and pending background deletions without refreshing the document.

Out of scope are authentication, account deletion, evaluation submission and approval, exports, user/subject file-import preview and confirmation, dedicated-page create/edit workflows, and a migration to Inertia/Vue.

## Architecture

Add one small framework-independent async form utility for shared transport behavior. It will serialize either `FormData` or JSON, attach the CSRF and `Accept: application/json` headers, prevent duplicate submissions, parse success and validation responses, and restore the submit control after completion. It will not know about tables, modals, dropdowns, or domain-specific totals.

Each page will have a thin coordinator that calls the shared utility and applies the returned representation to its own DOM. Page coordinators own modal state, row insertion or replacement, row removal, pagination fallback, totals, filters, and expanded dropdown state. Existing markup remains server-rendered Blade; JSON responses carry render-ready HTML fragments and small state fields rather than duplicating presentation templates in JavaScript.

Controllers keep their existing redirect responses as the non-JavaScript fallback. When `expectsJson()` is true, they return a stable JSON envelope with the mutation result and the fragments/state required by the page.

## Response Contract

Successful in-page mutations return HTTP `200`, `201`, or `204` with this shape where applicable:

```json
{
  "success": true,
  "message": "บันทึกข้อมูลเรียบร้อยแล้ว",
  "data": {},
  "html": {
    "row": "<tr>...</tr>",
    "list": "<div>...</div>",
    "summary": "<div>...</div>"
  },
  "state": {
    "id": 123,
    "page": 1,
    "total": 10
  }
}
```

Fields not needed by an action are omitted. Validation failures use HTTP `422` and Laravel's `message` plus `errors` map. Authorization, conflict, not-found, and server failures retain their appropriate HTTP status and include a user-safe `message`.

## User Interface Behavior

### Create and update

On success, close only the active modal, insert or replace the affected row, update counters, clear the form, and show a success toast. Search terms, filters, pagination, selected tab, scroll position, and unrelated expanded sections remain unchanged. On `422`, leave the modal open, map messages to fields, and focus the first invalid field.

### Delete and bulk actions

After confirmation and a successful response, remove affected rows and update counters without reloading the document. If deletion empties the current paginated page, request the previous page's table fragment and update only the table region and pagination controls. A failed deletion leaves the DOM unchanged.

### Evaluatee workload

Workload-entry create, update, and delete continue using their current async response application. Adding a subject refreshes the subject options and selects the new subject without closing the workload-entry modal. Importing a previous cycle replaces the affected workload groups and recalculates totals. Saving the aggregate score updates the saved-score state and clears the unsaved reminder without navigation.

Only the dropdown associated with the action remains expanded after a workload mutation. Other dropdowns remain closed. The identifier of that dropdown is sent with or retained across the request and reapplied after fragment replacement.

### Website settings

The settings coordinator submits `FormData` so file uploads continue to work. The response returns persisted settings, current logo/background URLs, and a rendered background-library fragment. The page updates previews, background classes, selected-library state, pending-deletion inputs, and the information box. Object URLs created for local previews are revoked after the server URL replaces them.

## Progressive Enhancement

Native form actions and controller redirects remain valid. Coordinators intercept submission only when `window.fetch` and the shared async utility are available. A missing or failed JavaScript bundle therefore falls back to the current full-page behavior.

## Error Handling and Concurrency

- Disable only the active submit or confirmation control while its request is in flight.
- Ignore subsequent submissions from the same form until the active request settles.
- Restore the control and preserve entered data after validation, authorization, conflict, network, or server errors.
- Apply no DOM mutation until the server response has succeeded and parsed correctly.
- Show inline field errors for `422`; use a page toast or alert region for other failures.
- Treat a response that lacks its required fragment/state as an error and leave the current UI intact.
- Preserve existing server-side authorization, dependency checks, transactions, and uniqueness rules.

## Testing Strategy

Implementation follows red-green-refactor cycles.

PHP feature tests will verify that each covered controller:

- returns JSON for requests that accept JSON;
- retains redirect behavior for normal form submissions;
- returns the expected row/list/summary fragments and state;
- returns `422` errors without changing persisted data;
- enforces delete constraints and authorization;
- reports correct totals after single and bulk mutations.

JavaScript tests will verify that the shared utility and page coordinators:

- never call `location.reload()` or perform document navigation after a successful in-page mutation;
- insert, replace, and remove the correct DOM fragment;
- retain filters, pagination, scroll state, and the active workload dropdown;
- leave modal contents and DOM rows unchanged after errors;
- prevent duplicate submission and restore controls afterward;
- apply the settings response and revoke superseded preview object URLs.

Focused tests run after every task, followed by the complete JavaScript suite, relevant PHP feature suites, formatting/static checks configured by the repository, and the production asset build.

## Delivery Order

1. Shared async request and response contract.
2. Master-data tables: departments, positions, job levels, and subjects.
3. User and role management.
4. Assignment-data and criteria-version index actions that currently reload after AJAX.
5. Remaining evaluatee workload actions and dropdown preservation.
6. Website settings with file uploads and background-library reconciliation.
7. Full regression verification and removal of covered `location.reload()` calls.

Each delivery unit remains independently testable and keeps native redirect fallback behavior.
