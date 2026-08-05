# Preserve Active Workload Dropdown

## Goal

After a workload entry is created without a page refresh, keep open only the workload dropdown from which the user clicked **เพิ่มข้อมูล**.

## Root Cause

The asynchronous save response replaces `#workloadPanelsLiveRegion.innerHTML`. Browser state on the old `<details open>` element is not part of the returned markup, so every newly inserted dropdown uses its default closed state.

## Design

Each workload `<details>` element will expose its quantity-sub-criteria item ID through a stable `data-workload-item-id` attribute. The existing add button already exposes the same item ID.

When the add modal opens, the client records the originating item ID as the active dropdown context. The successful save coordinator passes that ID to `applyWorkloadEntrySaveResponse`. After replacing the panel HTML, the response helper closes every workload dropdown and opens only the dropdown whose stable item ID matches the recorded context.

The implementation must not use the dropdown's DOM index because item ordering may change. It must not infer the dropdown from the selected workload-form item because that is a different identifier.

## Behavior

- Create success: refresh rows/totals, close the modal, and reopen only the originating dropdown.
- Create validation, server, network, or malformed-response failure: do not replace the panels; the existing page and modal state remain unchanged.
- Other dropdowns remain closed after a successful create.
- If no valid originating item ID exists, retain the current safe behavior and do not open an arbitrary dropdown.
- Edit behavior is unchanged by this request.

## Testing

- Blade test/contract verifies each workload dropdown exposes the stable item ID used by its add button.
- JavaScript test replaces panel HTML containing multiple dropdowns and verifies only the requested item ID receives `open`.
- JavaScript test verifies an unknown or absent ID does not open any dropdown.
- Existing asynchronous save and workload feature tests continue to pass.

## Scope

This change only preserves dropdown state after successful creation through the workload-entry modal. It does not change modal validation, persistence, row rendering, delete behavior, or the open/closed behavior of unrelated dropdowns.
