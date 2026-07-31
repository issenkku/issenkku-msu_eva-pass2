# System Workflow Documentation Design

**Date:** 2026-07-31  
**Audience:** Developers maintaining and supporting MSU EVA  
**Language:** Thai, with code identifiers preserved in English

## Goal

Create a developer-oriented workflow reference that answers questions such as:

- ผู้ใช้บทบาทนี้เห็นเมนูหรือปุ่มใด
- เมื่อกดแล้ว frontend ส่ง request ใด
- request ผ่าน controller, service, support class และ model ใด
- ข้อมูลหรือสถานะใดเปลี่ยน
- ระบบ redirect หรือแสดงหน้าใดต่อ
- กรณี validation, authorization หรือข้อมูลไม่ครบเกิดอะไรขึ้น

The documentation covers the entire current system, not only the evaluation
happy path.

## Source of Truth

The current implementation is authoritative. Each workflow is traced through:

1. React or Blade UI elements and navigation
2. Laravel routes and middleware
3. Controllers, services, support classes, and models
4. Feature, unit, and JavaScript tests

Existing user guides and workflow audit documents provide supporting context.
When they disagree with executable code, the documentation records the current
code behavior and flags the discrepancy.

## Deliverable Structure

Create the following files under `docs/system-workflow/`:

- `README.md`: system overview, roles, and end-to-end workflow
- `01-auth-navigation.md`: authentication, password reset, dashboards, and role navigation
- `02-admin-configuration.md`: users, departments, positions, criteria, versions, subjects, workload, and imports
- `03-assignment.md`: evaluation cycles, assignment creation, editing, deletion, and report creation effects
- `04-evaluation.md`: Evaluatee, Evaluator, Manager, and Director workflows and status transitions
- `05-report-dashboard-export.md`: dashboard reads, reports, score summaries, and exports
- `06-profile-settings.md`: profile, password, appearance, and public profile
- `07-click-action-reference.md`: searchable UI action-to-backend reference
- `08-known-risks.md`: confirmed defects, edge cases, and implementation inconsistencies

This hybrid structure provides an end-to-end overview, role-oriented guidance,
and a fast lookup reference without producing one unmaintainably large file.

## Action Entry Format

Important actions use a consistent template:

| Field | Content |
|---|---|
| Starting point | Role, page, and visibility conditions |
| User action | Actual UI label or control |
| Request | HTTP method, route name, and URL |
| Backend flow | Controller to service/support class to model |
| Data effect | Records and fields created, updated, or deleted |
| Status effect | State before and after the action |
| Navigation | Redirect, reload, modal state, or destination page |
| Failure path | Validation, authorization, missing data, and user-facing feedback |
| Code reference | Repository file references |

The click-action reference presents these fields in a compact table optimized
for repository search.

## Workflow and Status Presentation

- Use Mermaid flowcharts for multi-role end-to-end flows.
- Use Mermaid state diagrams plus transition tables for report and assignment statuses.
- Use prose only for simple, linear actions.
- Mark destructive or difficult-to-reverse actions explicitly.
- Preserve exact route names, status values, role names, and code identifiers.

## Scope

The documentation includes:

- login, logout, reset password, and authorization behavior
- role-specific dashboards and navigation
- users, roles, departments, positions, and related settings
- criteria versions and academic/support criteria configuration
- subjects, workload forms, workload entries, formula preview, and Excel import/export
- assignment data, participant selection, evaluation cycles, and generated reports
- quantity, quality, support, evidence, comments, and score-history behavior
- Evaluatee, Evaluator, Manager, and Director submission flows
- dashboards, filtering, status summaries, reports, and exports
- profile, password, appearance, and public profiles
- normal, validation, authorization, empty-state, and known-risk paths

## Evidence Labels

Each workflow or notable assertion may use one or more labels:

- `ยืนยันจากโค้ด`: traced from UI through backend implementation
- `ยืนยันจาก test`: behavior is asserted by an automated test
- `ข้อควรระวัง`: implementation inconsistency, confirmed defect, or important edge case
- `ไม่พบ UI trigger`: backend capability exists, but no current UI control was found

Known-risk entries distinguish confirmed current behavior from audit
recommendations. Recommendations are never presented as implemented behavior.

## Verification

Before completion:

1. Verify route names and methods against the route files.
2. Verify UI labels and destination behavior against React and Blade views.
3. Verify mutations and status transitions against controllers, services, and models.
4. Cross-check important failure paths with automated tests.
5. Confirm every linked repository file exists.
6. Search the documentation for unresolved `TODO`, `TBD`, and placeholder text.
7. Check Mermaid blocks for syntactic consistency.
8. Ensure every subsystem listed in scope is reachable from the documentation index.

## Non-Goals

- Changing application behavior or fixing defects
- Replacing end-user training material
- Describing a future architecture as if it already exists
- Documenting database columns that do not affect an observable workflow

