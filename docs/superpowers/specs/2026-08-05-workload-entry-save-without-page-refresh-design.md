# Workload Entry Save Without Page Refresh

## Goal

When an evaluatee creates or edits a workload entry, save it without reloading the browser page. After a successful save, close the modal and immediately show the latest workload rows and totals.

## Current Behavior and Root Cause

`#workloadEntryForm` only prevents submission when client-side validation fails. A valid form falls through to the browser's native form submission. `EvaluateeWorkloadEntryController::store` and `update` then return redirects, so the browser loads the page again.

## Design

Intercept valid submissions in `workload-script-entry-submit.blade.php` and send the existing `FormData` with `fetch`. Keep the existing form action, CSRF token, HTTP method override, request classes, authorization, calculation, evidence persistence, and validation rules.

When the request expects JSON, the workload-entry controller will return:

- a success message;
- server-rendered HTML for the workload group panels;
- server-rendered HTML for the workload summary;
- the current total needed by the workload score form.

The response will be built from the same `EvaluateeWorkloadViewData` read model and existing Blade partials used by the full page. This avoids duplicating row formatting and score calculations in JavaScript. The page will expose stable live-region hooks around the panels and summary so JavaScript can replace only those regions.

Normal redirect responses remain unchanged for non-JavaScript submissions.

## Interaction Flow

1. Run the existing client-side required-field checks.
2. Prevent native submission when the form is valid.
3. Disable the save button and show a saving state.
4. Submit the form using `fetch`, requesting JSON.
5. On success, replace the workload panels and summary, update the total score input, close the Bootstrap modal, reset the form state, and show a success notification.
6. Restore the save button state after the request finishes.

Both create and edit modes use this flow. Event delegation and the modal's existing `show.bs.modal` handler allow newly rendered edit buttons to work without rebinding each row.

## Error Handling

- HTTP 422: keep the modal open, preserve entered values, mark matching fields invalid where possible, and show the server validation messages.
- Authorization, missing-record, or server errors: keep the modal open and show a general error message returned by the server when available.
- Network failure: keep the modal open, restore the save button, and tell the user to try again.
- Repeated clicks: ignore additional submissions while one request is in progress.

## Testing

- JavaScript test: a valid submit is prevented and sent through `fetch` instead of native navigation.
- JavaScript test: success replaces the live regions, updates the total, and closes the modal.
- JavaScript test: validation and network failures keep the modal open and restore the button.
- Feature tests: JSON create and update responses contain the success payload and refreshed fragments while existing redirect behavior remains intact.
- Run the focused JavaScript and PHP tests, the complete JavaScript suite, and the production asset build.

## Scope

This change covers the workload-entry modal shown in the request, for both create and edit. It does not change delete, subject creation, importing previous workload, or saving the aggregate workload score.
