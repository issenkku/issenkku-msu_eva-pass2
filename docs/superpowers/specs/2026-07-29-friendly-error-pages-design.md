# Friendly Error Pages Design

## Goal

Replace framework-style web error responses with clear Thai guidance for end users while preserving HTTP status codes, JSON behavior, technical logging, and existing authentication flows.

## Scope

The feature covers web responses for:

- `401` and `419`: unauthenticated or expired session
- `403`: authenticated user without permission
- `404`: route or resource not found
- `500`: unexpected server failure
- `503`: maintenance or temporary service unavailability

API, AJAX, and other requests that expect JSON remain JSON responses with their original HTTP status codes and machine-readable error messages.

## User Experience

### Session and authentication failures (`401` / `419`)

Web requests redirect immediately to `/login`. The Login page shows a visible Thai notification:

> เซสชันหมดอายุหรือออกจากระบบแล้ว กรุณาเข้าสู่ระบบอีกครั้ง

The notification remains visible until the user submits Login or navigates away. There is no intermediate error page and no timed redirect.

An intentional logout keeps its existing redirect to `/login`, but uses a distinct success notification:

> ออกจากระบบเรียบร้อยแล้ว

The Login page must render both the session-expired warning and the intentional-logout success message in an accessible notification region.

### Permission denied (`403`)

Show a friendly page with:

- Title: `ไม่สามารถเข้าถึงหน้านี้ได้`
- Explanation: the current account does not have permission to view the requested page
- Primary action: return to the appropriate dashboard when a dashboard route is available
- Secondary action: go back to the previous page

### Not found (`404`)

Show a friendly page with:

- Title: `ไม่พบหน้าที่ต้องการ`
- Explanation: the link may be incorrect, expired, moved, or the record may no longer exist
- Primary action: return to the appropriate dashboard or Login when unauthenticated
- Secondary action: go back to the previous page

### Server failure (`500`)

Show a friendly page with:

- Title: `ระบบขัดข้องชั่วคราว`
- Explanation: the request could not be completed and the user may try again
- Primary action: retry the current URL
- Secondary action: return to the appropriate dashboard or Login

Do not expose exception messages, stack traces, file paths, SQL, environment values, or internal identifiers.

### Service unavailable (`503`)

Show a friendly page with:

- Title: `ระบบยังไม่พร้อมใช้งาน`
- Explanation: the system may be under maintenance or temporarily unavailable
- Primary action: retry the current URL
- Secondary action: return to Login

## Visual Structure

Use one reusable Blade error shell so all error pages share:

- the application's visual identity and configured site name/logo when safely available
- a centered responsive card
- a status-specific icon and Thai copy
- one visually dominant primary action and at most one secondary action
- accessible headings, keyboard focus styles, and sufficient color contrast

Do not display `Error 404`, `Error 500`, raw exception names, or a technical error code as primary user-facing content. The response continues to carry the actual HTTP status for browsers, monitoring, and infrastructure.

## Architecture

### Error views

Create a shared Blade error component/layout and thin status views under `resources/views/errors/`:

- `403.blade.php`
- `404.blade.php`
- `500.blade.php`
- `503.blade.php`

Each status view supplies only its title, explanation, icon/tone, and allowed actions. Shared markup and styles live in one component or layout.

### Exception handling

Configure Laravel exception rendering in `bootstrap/app.php`.

- Requests expecting JSON are not redirected to HTML and retain Laravel's JSON status behavior.
- Web `401` and `419` responses redirect to the named Login route with a dedicated warning flash key.
- Web `403`, `404`, `500`, and `503` responses render the corresponding friendly Blade view with the original HTTP status.
- Unexpected exceptions continue through Laravel's reporting pipeline before the friendly `500` response is rendered.
- Debug detail remains governed by environment configuration and must never be included in the friendly production response.

Authentication middleware redirects that already lead an unauthenticated web user to Login should carry the same session-expired warning when the request represents a lost/invalid session. Visiting Login directly must not manufacture a warning.

### Login notifications

Update `resources/views/user/management/loginForm.blade.php` to render:

- the dedicated expired-session warning flash
- the existing intentional-logout success flash
- existing credential/AJAX validation errors without mixing them with session state

The server-rendered flash messages and the client-rendered Login error area remain separate so a failed Login attempt does not duplicate or overwrite the session notification.

### Dashboard destination

Friendly pages use a safe destination resolver:

- authenticated admin → admin dashboard
- authenticated manager/director/evaluator/evaluatee → that role's dashboard
- unauthenticated user or unknown role → Login

If a role-specific dashboard route cannot be safely resolved, fall back to Login rather than raising another exception from the error page.

## Logging and Security

- Preserve Laravel exception reporting for `500` and other unexpected exceptions.
- Do not log routine `404` responses as server failures.
- Do not include exception details in HTML, flash data, query parameters, or JavaScript.
- Do not redirect JSON/API requests to Login.
- Keep CSRF/session regeneration behavior unchanged.
- Avoid redirect loops: errors occurring while rendering Login or an error view must fall back to Laravel's minimal response behavior.

## Testing

Feature tests must verify:

- an unauthenticated protected web request reaches Login with the expired-session warning
- a `419` web response redirects to Login with the same warning
- intentional logout reaches Login with the distinct success notification
- `403`, `404`, `500`, and `503` web responses render the correct Thai page and retain their status
- friendly HTML does not contain exception messages, stack traces, or internal paths
- JSON requests for the same statuses remain JSON and are not redirected
- error-page dashboard actions choose a valid role destination and safely fall back to Login
- the Login page renders one notification for session expiration and one distinct notification for intentional logout

Regression verification includes authentication tests, authorization tests, route-not-found behavior, production asset build, and the existing application test suite.

## Acceptance Criteria

- End users do not see raw Laravel 404/500 pages or technical exception details.
- Session expiration takes web users directly to Login with a clear Thai notification.
- Intentional logout shows a different success notification.
- Permission, not-found, server-failure, and maintenance cases provide an actionable friendly page.
- API/AJAX clients retain JSON and the correct HTTP status.
- Monitoring and logs retain enough technical evidence for diagnosis.
