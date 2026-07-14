# Evaluatee Navigation Label Design

**Date:** 2026-07-14

## Goal

Make the self-evaluation navigation visually distinct from the administrator home link when one user has both roles.

## Design

Keep the administrator `/dashboard` link unchanged with its home icon and `หน้าหลัก` label.

Present the existing `/evaluatee-dashboard` link as `หน้าประเมินตนเอง` with the `fas fa-user-check` icon in both navigation variants:

- Desktop header navigation
- Mobile menu navigation

Keep the existing route, visibility condition, and surrounding navigation styling unchanged. The link remains visible only when `$showEvaluateeNavigation` is true.

## Scope

Only the desktop and mobile evaluatee navigation presentation in `resources/views/layouts/app.blade.php` will change. Role names, role assignments, authorization, assignment checks, routes, and other role-based navigation remain unchanged.

## Verification

Add or update a focused view-level test that confirms:

- The desktop and mobile navigation each contain an `/evaluatee-dashboard` link labeled `หน้าประเมินตนเอง`.
- Both evaluatee links use the `fas fa-user-check` icon.
- The administrator `/dashboard` link retains its `fas fa-home` icon and `หน้าหลัก` label.
- The obsolete wording `หน้าประเมินตัวเอง` is absent from the layout.
