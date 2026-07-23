# Dashboard Status Filter and Pagination Design

**Date:** 2026-07-23  
**Scope:** Admin dashboard, section “ผลการประเมินรายบุคคล”

## Problem

The quick-status badges are calculated from the complete filtered evaluation
collection, but the dashboard paginates that collection before the browser
applies the selected status and text search to the ten rendered rows.

This produces inconsistent UI:

- a badge can show matching records while the current page displays an empty
  state;
- the paginator and “Showing … results” text continue to describe the
  unfiltered collection;
- matching records on another page are inaccessible from the selected status;
- status changes only affect the current page;
- the status `Manager_assign` is assigned inconsistently between the summary
  counter and the table-row status group.

## Desired Behaviour

1. Status and search filters operate on the complete evaluation collection
   before pagination.
2. The rows, result summary, and paginator always describe the same filtered
   collection.
3. Status badge totals describe the collection after the existing dashboard
   filters (year, date, department, position, and search where applicable) but
   before applying the selected status. This lets users see how many records
   are available in each status.
4. Selecting a status, searching, or changing page updates only the evaluation
   list region. The whole dashboard must not refresh.
5. The URL stores the active `status`, `search`, and `page` values so the view
   can be bookmarked, shared, reloaded, and restored with browser Back/Forward.
6. Normal link/form navigation remains a fallback when JavaScript is
   unavailable or an asynchronous request cannot be started.

## Status Classification

There must be one canonical mapping used by both counting and filtering:

| Group | Raw report statuses |
| --- | --- |
| มอบหมาย | `Assigned` |
| เริ่มกรอกข้อมูล | `Draft` |
| กำลังดำเนินการ | `Pending`, `Evaluator_draft`, `Director_assigned`, `Director_draft`, `Manager_assign`, `Manager_draft` |
| ประเมินเสร็จสิ้น | `Completed` |

An absent report status continues to behave as `Assigned`.

`Manager_assign` belongs only to “กำลังดำเนินการ”. This resolves the current
overlap where the same raw status can be counted as both “มอบหมาย” and
“กำลังดำเนินการ”.

The status summary service will own the mapping and expose classification and
collection-filtering behaviour. Blade templates must not maintain a separate
group mapping.

## Server-Side Data Flow

The dashboard query performs these operations in order:

1. load and map evaluation assignments;
2. apply the existing dashboard filters;
3. apply the evaluation-list text search;
4. calculate status badge totals from this base collection;
5. apply the selected canonical status group;
6. sort the filtered collection;
7. paginate the filtered collection.

An unknown or missing status value is treated as `all`. A valid status change
resets the page to `1`.

The main dashboard request continues to render the complete page. An
asynchronous request returns a dedicated evaluation-list fragment containing:

- status badges and active state;
- table body or empty state;
- filtered result summary;
- pagination controls.

This keeps the server-rendered HTML as the single source of truth and avoids
duplicating row markup in JavaScript.

## Browser Interaction

The evaluation-list controller intercepts:

- status badge clicks;
- pagination link clicks;
- submission of the evaluation-list search.

It builds a URL from the current filters, requests the fragment, and replaces
only the evaluation-list region. While loading, controls are disabled and the
existing loading state is shown. On success it updates browser history with
`pushState`. On `popstate` it reloads the fragment for the restored URL without
adding another history entry.

Requests use a monotonically increasing request identifier or an abort
controller so a slower old response cannot overwrite a newer selection.

If a request fails, the current table remains visible and an inline retryable
error is shown. The user may retry or follow the generated URL using normal
navigation.

Text search is submitted explicitly through the existing search action rather
than filtering only the currently rendered rows. This avoids firing a request
for every keystroke and keeps keyboard and non-JavaScript behaviour predictable.

## Pagination and Numbering

Pagination links preserve all active query parameters. Row numbering is
continuous across pages:

`firstItem + loop index`

The paginator’s total, last page, links, and “Showing … results” text are all
derived from the status-filtered collection.

## Accessibility

- The selected status uses `aria-pressed="true"`.
- Loading is announced through an `aria-live` region.
- Focus remains on the activated status button after a successful status
  change.
- After pagination, focus moves to the evaluation-list heading.
- The loading state must not permanently remove keyboard access if a request
  fails.

## Testing

Automated coverage will verify:

1. every raw status maps to exactly one canonical group;
2. `Manager_assign` is counted and filtered only as “กำลังดำเนินการ”;
3. filtering occurs before pagination and paginator totals match badge totals;
4. a page containing no records for the selected status does not produce a
   false empty result when matching records exist elsewhere;
5. query parameters are preserved by pagination links;
6. unknown statuses fall back to `all`;
7. full-page and fragment responses render equivalent list content;
8. the fragment includes correct active states, rows, empty state, and result
   summary.

## Out of Scope

- Changing the dashboard overview charts or their status semantics.
- Changing the export workbook contents.
- Changing workflow state transitions.
- Replacing the existing Blade/Tailwind implementation with a frontend
  framework.
