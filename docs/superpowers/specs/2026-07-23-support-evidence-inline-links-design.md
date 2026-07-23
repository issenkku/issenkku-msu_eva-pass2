# Support Evidence Inline Links Design

**Date:** 2026-07-23

**Status:** Approved visual direction

## Goal

Show the actual evidence URLs in each support-criteria row instead of a
count label such as `1 ลิงก์`. Selecting a URL opens that evidence directly
and must not open the support score/activity editor.

## Root Cause

The evidence count is currently rendered as a button with
`data-support-evidence-open`. The shared click handler responds by moving the
entire support item editor into the management modal. Therefore a read action
for evidence incorrectly exposes the score and activity editing interface.

## Selected Design

Render every non-empty evidence URL as an anchor inside the existing
`หลักฐาน` column or mobile evidence area.

- The visible anchor text is the complete URL saved by the user.
- Each URL opens in a new tab with `target="_blank"` and
  `rel="noopener noreferrer"`.
- Multiple URLs appear as a vertical list in their saved order.
- Long URLs wrap within the available cell using word-breaking styles.
- The empty state remains `ไม่มีหลักฐาน`.
- No count label, evidence popup, or support management modal is used when an
  evidence URL is selected.

## Scope

- Apply to the shared support-criteria table for all roles and both desktop
  and mobile layouts.
- Preserve the existing management button and management modal for score,
  activity, and evidence editing.
- Preserve evidence validation, storage, and score calculations.
- Do not change quantity or quality evidence displays.

## Live Update Behavior

After an editable support item is saved in the management modal, the evidence
area in both desktop and mobile views is rebuilt from the current non-empty
evidence values:

- Create one safe external anchor per URL.
- Keep the same URL text, ordering, and wrapping behavior as the initial
  server-rendered view.
- Render the unchanged empty state when no evidence remains.

The obsolete evidence-count button creation and evidence-button click path are
removed so the initial render and live update share the same user behavior.

## Accessibility and Security

- The anchor's accessible name is its visible URL.
- Keyboard users can focus and activate each URL normally.
- Focus rings remain visible.
- New tabs are isolated with `noopener noreferrer`.
- URLs continue to be escaped by Blade or assigned through DOM text/content
  and attribute APIs; no URL is inserted as raw HTML.

## Verification

- Add a Blade view regression test proving the full evidence URL is visible
  in desktop and mobile output.
- Assert the URL uses safe new-tab attributes.
- Assert `data-support-evidence-open` and the count text are absent.
- Assert the client-side refresh code creates anchors instead of modal-opening
  buttons.
- Run the focused support criteria view tests, JavaScript tests, and production
  build.

## Design Self-Review

This design removes the misleading interaction at its source rather than
hiding sections of the editor modal. Showing the URL directly matches the
user's stated need, works for one or many links, and introduces no new
component or state.
