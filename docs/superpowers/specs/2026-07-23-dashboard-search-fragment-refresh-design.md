# Dashboard Search Fragment Refresh Design

**Date:** 2026-07-23
**Scope:** Admin dashboard, section “ผลการประเมินรายบุคคล”

## Problem

Submitting or clearing the evaluation-list search must not navigate or refresh
the complete dashboard page. A full-page refresh disrupts the user's reading
position and unnecessarily reloads overview content that the search does not
change.

The dashboard already supports a server-rendered evaluation-list fragment for
status filtering and pagination. Search and clear must use the same fragment
request path and must behave correctly after the list element has been replaced.

## Desired Behaviour

1. Submitting the search updates only the complete evaluation-list card:
   its search toolbar, status badges, table, result summary, and pagination.
2. Clearing the search performs the same fragment-only update.
3. Search and clear do not reload the document and do not move the page's
   current scroll position.
4. A search or clear removes `page` so results start from the first page.
5. All other active dashboard query parameters remain intact.
6. The browser URL stores the resulting query without reloading the document.
7. Browser Back and Forward restore the corresponding evaluation-list fragment
   without creating an additional history entry.
8. The server-rendered form remains a normal GET form so searching still works
   through full navigation when JavaScript is unavailable.

## Architecture

The dashboard script owns evaluation-list navigation. It uses delegated event
handlers rooted at `document` because every successful request replaces the
evaluation-list element and its search controls.

One fragment loader remains the single asynchronous path for:

- status-filter links;
- pagination links;
- search form submission;
- clear-search actions;
- browser history restoration.

The shared search-bar component continues to provide accessible form markup and
non-JavaScript GET behaviour. Dashboard-specific fragment behaviour stays in
the dashboard script instead of introducing AJAX semantics on every page that
uses the shared component.

## Interaction and Data Flow

### Search

1. The delegated dashboard submit handler recognizes a search form inside
   `[data-evaluation-list]`.
2. It prevents the form's document navigation.
3. It creates a URL from the form action and `FormData`.
4. It removes `page`.
5. It requests the evaluation-list fragment.
6. On success it replaces only `[data-evaluation-list]` and calls
   `history.pushState`.

### Clear

1. The delegated dashboard click handler recognizes the clear-search button
   inside `[data-evaluation-list]`.
2. It prevents any competing default or shared clear behaviour.
3. It clears the search field and follows the same submission path as Search.
4. The resulting URL omits the empty `search` parameter and removes `page`.
5. On success it replaces only `[data-evaluation-list]` and calls
   `history.pushState`.

Handling Clear explicitly in the dashboard controller avoids depending on the
registration order of the shared search-bar listener and the dashboard submit
listener.

### Scroll and Focus

Search and clear do not call `scrollTo`, `scrollIntoView`, or focus the list
heading after replacement. The browser therefore keeps the current viewport.
The replacement search input may lose focus as a natural consequence of
replacing the fragment; no focus change may cause the document to scroll.

Status and pagination focus behaviour is unchanged by this work.

## Loading and Failure Behaviour

While a fragment request is active, controls within the current evaluation list
are temporarily disabled and the list's loading indicator is shown. A newer
request aborts the previous request so an older response cannot overwrite newer
results.

On a non-abort failure:

- retain the current evaluation list and its data;
- restore access to its controls;
- reveal the existing inline request error;
- do not update browser history.

The normal form action remains available as the no-JavaScript fallback.

## Testing

Add a browser-level regression test that exercises the rendered dashboard:

1. submit a search and verify that no document navigation occurs;
2. verify the requested URL preserves active filters and resets `page`;
3. verify only the evaluation-list region is replaced;
4. verify the document scroll position is unchanged;
5. clear the search and verify the same fragment-only behaviour;
6. verify the resulting URL no longer contains `search` or `page`;
7. verify Back and Forward restore list state without document navigation.

Retain server-side feature coverage for:

- complete-page rendering;
- fragment response rendering and response header;
- search filtering before pagination;
- query preservation in generated links.

If the repository's existing browser-test tooling cannot exercise the
interaction, add the smallest JavaScript DOM test supported by the current
toolchain. It must execute the event handlers and assert navigation and DOM
replacement behaviour rather than only searching the rendered source for
handler names.

## Out of Scope

- Refreshing the dashboard overview, charts, or top-level filter panel after
  an evaluation-list search.
- Changing search matching rules or searchable fields.
- Changing status grouping or pagination semantics.
- Adding AJAX behaviour to the shared search-bar component on other pages.
- Introducing a frontend framework or new runtime dependency.
