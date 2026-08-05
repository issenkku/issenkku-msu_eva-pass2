# Login Form Fallback Design

## Problem

The Login page currently intercepts form submission only when Axios has loaded
from an external CDN. If Axios is unavailable, the browser performs a normal
form POST. Invalid credentials then receive an unconditional JSON response,
which the browser displays as a raw document instead of returning to Login with
an error message.

## Desired behavior

- Login remains usable when JavaScript or an external CDN is unavailable.
- A normal form POST with invalid credentials redirects back to Login, preserves
  the entered employee ID, and displays the existing generic credential error.
- An AJAX request with invalid credentials continues to receive a JSON error and
  the appropriate HTTP status.
- Successful normal and AJAX login behavior remains unchanged.
- Rate-limited and inactive-account responses follow the same content-negotiation
  rule as invalid credentials.

## Design

### Server response negotiation

`AuthController::login` will choose the response format from the request:

- For requests that expect JSON or are AJAX requests, return the existing JSON
  payload and status code.
- For normal form requests, redirect back to the Login page with the message in
  the validation error bag and retain only `employee_id` as old input.

Password values must never be flashed to the session. Authentication failure
messages remain generic so the response does not reveal whether an employee ID
exists.

### Login page

The Blade view will render server-side validation and authentication errors in
the existing accessible error area. This is the baseline behavior and does not
depend on JavaScript.

The form enhancement will use the browser's built-in `fetch` API instead of the
Axios CDN. If `fetch` is unavailable, no submit handler is installed and the
normal form fallback remains functional. The request will continue to send the
CSRF token and request a JSON response.

### Error handling

- Invalid credentials: HTTP 401 for AJAX; redirect with a generic error for a
  normal form submission.
- Rate limit: HTTP 429 for AJAX; redirect with the existing throttling message
  for a normal form submission.
- Inactive account: preserve the current AJAX status and message; redirect with
  the same message for a normal form submission.
- Network or malformed-response failures in the enhanced form display a generic
  Login failure message without exposing implementation details.

## Testing

Regression coverage will verify:

1. A plain form POST with an invalid password redirects to Login, flashes the
   credential error, preserves `employee_id`, and does not preserve `password`.
2. An AJAX invalid-password request still returns the expected JSON and status.
3. The Login view renders the server-side error in an accessible region.
4. The Login page no longer depends on the external Axios CDN and its enhancement
   uses the native request path.
5. Existing successful login, inactive-account, and throttling tests continue to
   pass.

## Scope

This change is limited to Login request/response handling and its tests. It does
not change authentication rules, role routing, account status policy, or rate
limits.
