# Subject Detail Modal Design

## Goal

Let administrators inspect every subject credit and hour value without entering the edit workflow. The table remains compact while the detail view explains whether its three-value tuple currently comes from hours or split credits.

## Approved approach

Use a read-only Bootstrap modal opened by a `รายละเอียด` button in each subject row. The button appears before `แก้ไข` and uses an eye icon with a restrained teal or neutral-indigo treatment.

This approach is preferred over expanding table rows because it does not change row height or interfere with reordering and bulk selection. It is preferred over a separate detail page because administrators can inspect data without leaving their current filters and pagination position.

## Table behavior

- Both the initial server-rendered rows and asynchronously inserted or replaced rows expose the same detail trigger and data contract.
- Action order is `รายละเอียด`, `แก้ไข`, `ลบ`.
- The existing total credit and three-value tuple remain unchanged.
- The detail action is keyboard accessible and has an explicit accessible label.

## Modal content

The modal title is `รายละเอียดรายวิชา` and it is read-only. It contains:

- Subject code, primary display name, and secondary name when available.
- An `เปิดใช้งาน` or `ปิดใช้งาน` status badge.
- A `ข้อมูลหน่วยกิต` group with total, lecture, lab, and self-study credits.
- A `ข้อมูลชั่วโมง` group with lecture, lab, and self-study hours.
- An informational line stating either `ค่าที่แสดงในตาราง: ชั่วโมง ( … )` when any hour is greater than zero, or `ค่าที่แสดงในตาราง: หน่วยกิต ( … )` when all three hours are zero.
- Close controls in the header and footer. There is no edit or save control inside the modal.

## Data flow

The row trigger carries the subject values already loaded for the table. A delegated click handler reads the trigger dataset, fills one shared modal, and opens it through `bootstrap.Modal.getOrCreateInstance`. No additional HTTP request is required. The display-source decision follows the existing model rule: use the complete hours set when any hour is positive; otherwise use the complete split-credit set.

## Responsive and visual behavior

The modal uses a centered, scrollable large dialog and becomes full-screen on small displays. Credit and hour groups use compact cards or bordered sections consistent with the existing Bootstrap UI. Values remain legible without editable-looking inputs.

## Error handling

Missing optional names render as empty rather than producing placeholders. Numeric dataset values are normalized to nonnegative integers with zero as the display fallback. If Bootstrap is unavailable, the click handler exits without changing page state.

## Testing

- Feature rendering test verifies the detail trigger, all raw values, modal shell, read-only labels, and absence of save inputs.
- Rendering coverage includes both the full table and the asynchronous row partial.
- JavaScript test verifies that clicking a detail trigger fills the modal, selects the correct tuple source, and opens the shared Bootstrap modal.
- Existing subject display, edit, delete, bulk-selection, and async-mutation tests remain green.
