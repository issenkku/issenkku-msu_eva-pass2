# Login Viewport Responsive Design

## Goal

Make the existing login page fit within the visible browser viewport on common desktop and mobile screen sizes without requiring vertical scrolling. Preserve the current faculty identity, two-column composition, content, and login behavior.

## Problem

The desktop login page uses content-driven grid height together with `body { overflow: hidden; }`. When the combined logo, form, and spacing exceed the available viewport height, the body grows beyond the viewport while the overflow is clipped. The submit button and footer can therefore fall below the visible area.

## Scope

- Change only the login page presentation in `resources/views/user/management/loginForm.blade.php`.
- Keep all current text, fields, actions, colors, typography, and decorative elements.
- Keep the existing desktop two-column layout.
- Keep the existing mobile behavior where the information panel is hidden and the login panel occupies the screen.
- Do not change authentication, validation, password reset, branding settings, or application-wide layouts.

## Responsive Layout

### Viewport shell

- Size the page shell against the dynamic viewport using `100dvh`, with `100vh` as a compatibility fallback.
- Keep page overflow hidden for the supported viewport range after the content is made height-responsive.
- Ensure both desktop columns use the same viewport-height track and do not force the body beyond it.

### Fluid sizing

Use CSS custom properties and `clamp()` to reduce vertical space continuously as viewport height decreases. The responsive tokens will control:

- outer panel padding;
- logo diameter and surrounding space;
- login-card padding;
- heading, divider, field, password-reset, button, and footer spacing;
- left-panel header, feature-card, statistics, and footer spacing;
- selected typography sizes where spacing alone is insufficient.

The current desktop dimensions remain the maximum values. Compact values become active only when viewport height is limited.

### Short-height refinement

Add height-based media queries for short landscape and laptop viewports. These rules may tighten the fluid token minimums but must not:

- hide the submit button, footer note, fields, or left-panel content;
- use whole-page `transform: scale()` or browser-specific `zoom`;
- make interactive targets smaller than a practical usable size;
- introduce a vertical scrollbar at common supported sizes.

### Narrow screens

At the existing width breakpoint:

- retain a single login column;
- hide the left information panel;
- apply the same height-responsive tokens to the right panel;
- keep the form centered and fully visible.

## Visual Direction

The visual identity remains unchanged:

- purple and blue faculty palette;
- `Noto Serif Thai` for institutional display text;
- `Kanit` for interface and body text;
- split institutional story and authentication layout;
- circular faculty mark as the page signature.

The responsive change should feel like the same composition becoming denser, not a separate compact theme.

## Accessibility

- Preserve visible focus styles and existing accessible labels.
- Keep password-toggle and submit-button hit areas usable.
- Respect the existing reading order and semantic structure.
- Add reduced-motion handling for decorative and entrance animations if not already present.

## Testing

Add a focused regression test that verifies the login view contains:

- dynamic viewport sizing;
- height-responsive fluid tokens;
- a short-height media query;
- no whole-page scale or zoom workaround.

Run:

- the focused login view test;
- existing authentication and login-form tests;
- the complete JavaScript test suite;
- the production frontend build;
- formatting checks.

## Acceptance Criteria

1. On common desktop and laptop viewport heights, the logo, complete login card, submit button, and footer note are visible without scrolling.
2. Both desktop columns remain aligned to the viewport.
3. On narrow screens, the login panel remains centered and fully usable.
4. No login content or functionality is removed.
5. Existing branding configuration and authentication behavior remain unchanged.
