# Dashboard Filter AJAX Design

**Date:** 2026-07-23  
**Scope:** Admin dashboard main filter panel

## Goal

Make the dashboard's main filter actions update all filter-dependent content without a full-page refresh. Standardize the action order so the primary action, **กรองข้อมูล**, appears before the secondary action, **ล้างค่า**, on every viewport.

## Current Behavior

- The main filter form performs a regular GET submission and refreshes the whole page.
- Reset clears the four main filter fields and submits the form normally.
- Status filters, search, and pagination already request an `evaluation-list` HTML fragment and replace only the evaluation list.
- The main date, department, and position filters affect the overview cards, chart, follow-up data, status counts, and evaluation list. Updating only the list would make the dashboard inconsistent.

## Confirmed User Experience

### Main filter actions

- Render the actions in this order:
  1. **กรองข้อมูล** — primary blue button
  2. **ล้างค่า** — secondary gray button
- Preserve the same semantic and visual order on desktop and mobile.
- Submitting the form updates all filter-dependent dashboard results without reloading the page.
- Resetting clears all active dashboard query filters and immediately loads the unfiltered results without reloading the page.
- The filter panel remains expanded after applying filters. It may return to its default collapsed state after clearing all filters.

### Updated result regions

The main filter request updates one server-rendered `dashboard-results` fragment containing:

- overview summary cards;
- overview doughnut chart and its center labels;
- follow-up section;
- evaluation status totals and evaluation list.

The page header, navigation, filter shell, and modal shell remain mounted.
The client synchronizes the filter shell's active text/badge and the page-header filter indicator from the successful URL so these controls never display stale filter state.

### Existing list interactions

- Status selection, list search, year selection, and pagination continue to request only the existing `evaluation-list` fragment.
- A main filter submission resets pagination to page 1.
- Reset removes the dashboard query string, including main filters and list-level filters, matching the existing reset behavior.

## Architecture

### Server-rendered fragments

Extract the filter-dependent result markup into a dashboard results partial with a stable root such as:

```html
<div data-dashboard-results>...</div>
```

`DashboardController` will support two explicit fragment request headers:

- `X-Dashboard-Fragment: dashboard-results`
- `X-Dashboard-Fragment: evaluation-list`

Each fragment response returns only its matching partial and repeats the fragment name in the response header. A normal request continues to render the complete dashboard.

The existing dashboard query service remains the single source of truth. Fragment and full-page responses receive the same computed view data.

### Client-side controller

Extend the existing dashboard script with a request path for `dashboard-results`:

1. Prevent the main filter form's normal submit.
2. Build a URL from the form values and remove `page`.
3. Mark the result region and action controls busy.
4. Fetch the `dashboard-results` fragment.
5. Validate both the HTTP status and fragment response header.
6. Parse and replace only `[data-dashboard-results]`.
7. recreate the overview chart from configuration embedded safely in the new fragment;
8. update browser history, synchronize the active-filter indicators, and restore the filter panel state.

Use an `AbortController` so a newer action cancels an older request. The existing list-only loader keeps its own request lifecycle.

### Browser history

- Successful filter, reset, status, search, year, and pagination actions update the URL.
- `popstate` reloads the complete `dashboard-results` fragment. This is intentionally broader than list-only replacement because a historical URL may contain main filters that affect every dashboard result.
- Failed requests do not change the URL.

### Chart lifecycle

Move chart creation into an idempotent initializer that reads the current fragment's chart configuration.

Before replacing or reinitializing the chart:

- destroy the previous Chart.js instance when one exists;
- locate the new canvas and center-label elements;
- create exactly one chart instance;
- restore hover, click, and center-label behavior.

## Visual Direction

This is a focused interaction correction, not a dashboard restyle. Preserve the faculty system's current blue, slate, and white visual language.

- **Primary blue:** existing `blue-600` / `blue-700`
- **Secondary surface:** existing `gray-200` / `gray-300`
- **Busy treatment:** restrained opacity and an inline loading indicator within the results region
- **Typography:** preserve the project's current Thai interface type stack
- **Layout signature:** a stable filter panel above a smoothly replaced results block, so the user's location never jumps during filtering

The deliberate design choice is continuity: controls remain fixed while every dependent result changes as one coherent unit.

## Accessibility and Feedback

- Set `aria-busy="true"` on the results region during loading.
- Temporarily disable filter action controls while a main result request is active.
- Keep visible keyboard focus on the initiating action where possible.
- Do not animate when the user prefers reduced motion.
- Include a non-destructive inline error message with a retry path.
- Keep the current rendered results visible if loading fails.
- The regular GET form remains a progressive-enhancement fallback when JavaScript is unavailable.

## Error Handling

- Abort errors caused by a newer request are ignored.
- Network failures, non-success responses, incorrect fragment headers, or missing fragment roots leave the old results in place.
- The action controls are always re-enabled in a `finally` path.
- The user receives a concise Thai error message that asks them to retry.

## Testing

### Feature tests

- A normal dashboard request still returns the complete page.
- A `dashboard-results` fragment request returns the result root and response header, without the full page shell.
- The existing `evaluation-list` fragment contract remains unchanged.
- Main filter values affect overview, follow-up, status totals, and list data consistently.
- Reset/default requests return unfiltered result data.

### View and interaction contract tests

- The submit button appears before the reset button in markup.
- The main form submit is intercepted and requests `dashboard-results`.
- Reset clears fields and uses the same asynchronous result loader.
- The filter shell and page-header indicators reflect the successful main-filter state.
- Main filtering removes `page`, updates history only after success, and replaces the result region.
- `popstate` reloads `dashboard-results`.
- The chart initializer destroys an old instance before creating a new one.
- Loading and error states restore controls and preserve previous results on failure.

## Out of Scope

- Redesigning dashboard cards, charts, tables, or navigation.
- Changing filter criteria or dashboard calculation rules.
- Converting non-admin evaluator, manager, or director dashboards to the new fragment contract.
- Introducing a JSON API or client-side templating framework.
