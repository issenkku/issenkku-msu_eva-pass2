# Settings Page Width Alignment Design

## Goal

Make the main card on the website settings page use the same desktop content width as the department management page. This change is visual only and must not alter form fields, submission behavior, page copy, colors, background behavior, or data handling.

## Current State

The department management page uses a centered container with a maximum width of 1,200 pixels and 24 pixels of inner padding. Its page header fills the available content width.

The website settings page also has a 1,200-pixel container, but its card is nested in Bootstrap's `col-lg-8 col-md-10`. On large screens this limits the card to eight of twelve grid columns, so the card is substantially narrower than the department page.

## Chosen Design

Replace the settings page's `container > row > col-lg-8 col-md-10` width wrappers with a page-specific `settings-content-shell` wrapper.

The wrapper will:

- have a maximum width of 1,200 pixels;
- be horizontally centered;
- use 24 pixels of inner padding, matching the department page;
- let `card-custom` fill the available width;
- remain fluid below 1,200 pixels.

The resulting structure is:

```text
form-container
└── settings-content-shell
    └── card-custom
```

A page-specific class is preferred over overriding Bootstrap's global `.container-fluid` class. This keeps the change isolated and prevents width rules from leaking into other screens.

## Responsive Behavior

On desktop, the settings card will align with the department page's main header width. On smaller screens, the shell will shrink to the viewport while preserving 24 pixels of inner spacing. Existing settings-page mobile rules for the card, header, and upload fields remain unchanged.

## Unchanged Behavior

- Settings form action, method, CSRF protection, and multipart upload behavior
- All form fields and validation messages
- Site background selection and preview JavaScript
- Header copy, colors, card styling, and information panels
- Department management page

## Verification

Before changing production markup, add or identify the narrowest automated view-level check that proves the settings page uses the dedicated full-width shell and no longer uses the limiting Bootstrap column classes. Confirm that check fails before the markup change and passes afterward.

Then run the relevant Laravel test suite and inspect the settings page at desktop and mobile widths. At desktop width, the card's usable width should match the department page's header width under the same viewport and browser zoom. At mobile width, the page must not introduce horizontal scrolling.
