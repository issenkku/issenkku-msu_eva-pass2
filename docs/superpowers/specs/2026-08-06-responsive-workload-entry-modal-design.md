# Responsive Workload Entry Modal Design

## Goal

Keep the workload entry modal usable at every viewport height. The title and action buttons must remain visible while only the form content scrolls. This applies to both create and edit modes because they share the same modal.

## Scope

- Update `#workloadAddModal` only.
- Preserve the existing fields, validation, asynchronous submission, and modal lifecycle.
- Use the same responsive behavior as the subject form modal.
- Do not introduce a global modal abstraction or change unrelated modals.

## Layout

The existing Bootstrap modal remains the foundation. The dialog gains Bootstrap's scrollable and small-screen fullscreen utilities. Scoped CSS constrains the desktop dialog to the viewport with a one-rem margin and switches it to full viewport height on screens below Bootstrap's small breakpoint.

The modal content and its form become vertical flex containers. The header and footer do not shrink. The body consumes the remaining height, has a zero minimum height, and owns vertical scrolling. This keeps the title, close button, cancel button, and save button visible while the user moves through long or dynamically expanded form content.

## Responsive Behavior

- Desktop and tablet: center the dialog, retain rounded styling, and keep a one-rem viewport margin.
- Small screens up to `575.98px`: use a fullscreen dialog with no outer margin.
- All sizes: scroll only `.workload-modal-body`; prevent scroll chaining beyond the modal body.

## Compatibility

The change is CSS and markup-class only. Field names, request payloads, create/edit title switching, evidence rows, subject selection, validation feedback, and save behavior remain unchanged.

## Testing

A Blade contract test will render the workload modal and verify:

- the scrollable and small-screen fullscreen dialog classes;
- viewport-relative height rules for desktop and mobile;
- flex containment for the modal form;
- non-shrinking header and footer;
- body-only vertical scrolling.

Existing workload feature tests, JavaScript tests, and the production asset build will provide regression coverage.
