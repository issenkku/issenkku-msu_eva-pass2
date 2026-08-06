# Responsive Subject Form Modal Design

## Goal

Keep the subject create/edit modal usable at every viewport height. The title and form actions must remain visible while only the form body scrolls.

## Root cause

The shared subject modal does not constrain its height to the viewport, so its long form pushes the footer below the visible screen. The evaluatee workload page also applies `margin-top: 450px` to the dialog, which moves it farther outside the viewport.

## Approved layout

Use one shared responsive layout in `components.subject-modal` for both the administrator subject page and the evaluatee workload page.

- On desktop and tablet, the dialog is centered with a small viewport margin and a maximum height derived from `100dvh`.
- On small screens, the existing Bootstrap fullscreen behavior is used.
- The modal content is a vertical flex container.
- The header and footer never scroll or shrink.
- The modal body owns vertical scrolling with `overflow-y: auto` and `min-height: 0`.
- The footer always exposes both `ยกเลิก` and `บันทึก`.
- The form fields, validation, create/edit behavior, and submit handlers remain unchanged.

## CSS ownership

The responsive rules are scoped to `#subjectModal` and live with the shared subject modal component so both consuming pages receive identical behavior. Page-specific styling may adjust colors and spacing but must not reposition `.subject-modal-dialog` vertically.

The evaluatee workload rule `margin-top: 450px` is removed because it contradicts viewport centering and causes the reported overflow.

## Accessibility and viewport behavior

- Keyboard focus remains inside Bootstrap's modal focus trap.
- Tab navigation can reach every field and both footer actions.
- Scrolling occurs inside the body, leaving the dialog title and actions visible.
- Dynamic viewport units (`dvh`) account for mobile browser chrome; a `vh` fallback is provided before the `dvh` declaration.
- The modal remains usable at common desktop heights and narrow mobile widths without covering inaccessible content.

## Testing

- A component rendering test verifies the dialog has centered, scrollable, and small-screen fullscreen Bootstrap classes.
- The test verifies the scoped layout contract contains viewport height, flex, nonshrinking header/footer, and scrolling body rules.
- A regression contract verifies the evaluatee workload stylesheet no longer contains the `margin-top: 450px` override.
- Existing subject modal, workload subject form, JavaScript, and production build tests remain green.
