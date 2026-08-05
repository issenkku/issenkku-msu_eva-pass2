# Async Workload Delete Design

## Goal

Delete a workload entry without navigating or refreshing the page. Keep the existing confirmation modal and, after a successful deletion, leave only the dropdown containing the deleted entry open.

## Scope

- Applies only to workload-entry deletion on the evaluatee workload page.
- Keeps the shared delete confirmation modal's existing visual design and confirmation step.
- Does not change delete behavior on user, department, subject, position, or job-level pages.
- Does not change create or edit behavior.

## User Flow

1. The user clicks a workload row's delete button.
2. The existing delete confirmation modal opens.
3. Cancelling closes the modal and changes nothing.
4. Confirming sends the modal form as an asynchronous `DELETE` request.
5. On success, the page replaces the workload panels and summary with server-rendered fragments, updates the current total, closes the confirmation modal, shows a success message, and opens only the dropdown that contained the deleted row.
6. On failure, the page stays unchanged, the modal remains available, the confirm button is restored, and an error message is shown.

## Architecture and Data Flow

### Trigger context

The workload delete button includes the quantity subcriteria item ID in addition to the workload entry ID. The workload page records this item ID on the shared delete form when opening the confirmation modal. This scopes the extra behavior to workload deletion without changing the shared modal API for other pages.

### Server response

`EvaluateeWorkloadEntryController::destroy` keeps its redirect response for ordinary form submissions. For requests expecting JSON, it deletes the entry and its evidence, rebuilds live workload data, and returns the same live-update contract used by create and edit:

- `message`
- `panels_html`
- `summary_html`
- `total_score`
- `active_item_id`

The controller captures the entry's form and quantity subcriteria item before deletion so it can rebuild the correct page section and identify the dropdown after the row no longer exists.

### Client behavior

A workload-specific delete coordinator intercepts only `#deleteForm` submissions whose trigger supplied workload context. It sends the form with `Accept: application/json`, blocks duplicate submissions, validates the response, and applies it through the existing workload live-response renderer. The renderer uses `active_item_id` to open exactly one dropdown.

## Error Handling

- HTTP and network failures must not replace the current panels.
- A malformed successful response is treated as a failure.
- The delete confirmation button is disabled only while the request is pending and always restored.
- A missing or already deleted entry returns JSON `404` for an asynchronous request rather than following a redirect.
- The existing redirect fallback and flash messages remain available when JavaScript is unavailable.

## Testing

- Feature tests cover successful JSON deletion, deletion of evidence, refreshed fragments and total, `active_item_id`, redirect fallback, and JSON `404`.
- JavaScript tests cover request method and headers, duplicate-submit protection, successful live update and modal close, originating dropdown preservation, and failure behavior.
- A view contract test confirms workload delete triggers carry the stable item ID and the workload-specific async handler is present.

## Success Criteria

- Confirming workload deletion does not reload or navigate the page.
- The deleted row disappears and totals update from server-rendered data.
- Only the dropdown containing the deleted row remains open.
- Cancelling and failure paths preserve current data.
- Shared deletion behavior outside the workload page is unchanged.
