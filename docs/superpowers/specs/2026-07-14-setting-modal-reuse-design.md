# Setting Modal Reuse Design

## Problem

The department, position, job-level, and subject management pages create a new Bootstrap modal instance every time a create or edit action is opened. Each opener also calls a cleanup routine that can start an asynchronous hide transition immediately before the modal is shown again. This lifecycle is fragile when users reopen a modal quickly or return to the page after a save.

## Scope

Apply the same correction to these four setting pages:

- departments
- positions
- job levels
- subjects

Delete, bulk-delete, user-management, workload, and evaluation modals are outside this change because they already use a different lifecycle or `getOrCreateInstance()`.

## Design

Each create and edit opener will obtain the page modal through `bootstrap.Modal.getOrCreateInstance(modalEl)` and call `show()` on that stable instance. Openers will no longer call `clearModalBackdrop()` before showing the modal.

The existing cleanup routine remains available only for initial page restoration through `DOMContentLoaded`, `pageshow`, and `load`. It protects navigation and browser-cache restoration without racing normal user-triggered modal opens.

No controller, route, validation, form action, or submitted payload changes are required.

## Testing

Add a render-level regression contract covering all four script partials. The contract will require both create and edit paths to use `getOrCreateInstance()` and will reject cleanup calls inside either opener. Existing feature tests will continue to verify the management page markup and backend CRUD behavior.

Run the focused modal contract tests first, then the related setting feature tests and formatting checks. Perform a browser smoke test that opens, closes, and reopens the department edit modal and repeats the flow after saving in an isolated test database.

## Success Criteria

- Create and edit actions reuse one Bootstrap modal instance on all four pages.
- Opening a modal does not trigger global modal cleanup.
- Repeated edit opens retain the correct record data and exactly one backdrop.
- Existing CRUD, bulk-delete, and list behavior remains unchanged.
