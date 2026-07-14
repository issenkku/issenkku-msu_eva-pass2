# Evaluatee Navigation Label Design

**Date:** 2026-07-14

## Goal

Make the self-evaluation navigation label use formal, consistent Thai wording so users can distinguish it from the other role-based home links.

## Design

Change the visible label for the `/evaluatee-dashboard` navigation link from `หน้าประเมินตัวเอง` to `หน้าประเมินตนเอง` in both navigation variants:

- Desktop header navigation
- Mobile menu navigation

Keep the existing route, visibility condition, styling, and icons unchanged. The link remains visible only when `$showEvaluateeNavigation` is true.

## Scope

Only the two evaluatee navigation labels in `resources/views/layouts/app.blade.php` will change. Other role-based labels such as manager, evaluator, director, and administrator navigation remain unchanged.

## Verification

Add or update a focused view-level test that confirms:

- The desktop and mobile navigation both contain `หน้าประเมินตนเอง`.
- The obsolete wording `หน้าประเมินตัวเอง` is absent from the layout.
- Both labels continue to link to `/evaluatee-dashboard`.
