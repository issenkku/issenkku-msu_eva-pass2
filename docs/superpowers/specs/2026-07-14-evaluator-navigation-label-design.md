# Evaluator Navigation Label Design

**Date:** 2026-07-14

## Goal

Distinguish the evaluator dashboard link from the administrator home link when one user has both roles.

## Design

Change the visible label for the existing `/evaluator-dashboard` navigation link from `หน้าหลัก` to `หน้าประเมินผู้อื่น` in both navigation variants:

- Desktop header navigation
- Mobile menu navigation

Keep the existing route, `ผู้ประเมิน` role condition, icon, styling, and navigation order unchanged.

## Scope

Only the two evaluator navigation labels in `resources/views/layouts/app.blade.php` will change. The administrator `/dashboard` link remains `หน้าหลัก`, and the evaluatee `/evaluatee-dashboard` link remains `หน้าประเมินตนเอง`. Role names, role assignments, authorization, assignment checks, and routes remain unchanged.

## Verification

Add or update a focused view-level test that confirms:

- The desktop and mobile `/evaluator-dashboard` links both contain `หน้าประเมินผู้อื่น`.
- The `/evaluator-dashboard` links no longer use `หน้าหลัก`.
- The administrator `/dashboard` link retains `หน้าหลัก`.
- The evaluatee `/evaluatee-dashboard` link retains `หน้าประเมินตนเอง`.
