# System Workflow Documentation Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build a Thai developer reference that traces every user-visible MSU EVA workflow from UI action through route, backend mutation, status transition, and destination.

**Architecture:** Create a documentation hub under `docs/system-workflow/` with focused files for each workflow boundary. Derive behavior from the current UI and Laravel implementation, use tests as corroborating evidence, and centralize cross-cutting action lookup and known risks in dedicated references.

**Tech Stack:** Markdown, Mermaid, Laravel routes/controllers/services/models, Blade, Vue/Inertia, Pest/PHPUnit, Node.js tests

## Global Constraints

- Write explanatory content in Thai while preserving exact English route names, role names, status values, class names, method names, and field names.
- Treat current executable code as authoritative; use existing guides and audits only as supporting evidence.
- Mark claims with `ยืนยันจากโค้ด`, `ยืนยันจาก test`, `ข้อควรระวัง`, or `ไม่พบ UI trigger` where the distinction matters.
- Include normal, validation, authorization, empty-state, and known-risk paths.
- Do not modify application behavior or present recommendations as implemented behavior.
- Use Mermaid only for multi-role flows and state transitions; use prose or tables for simple actions.
- Link to repository files with relative Markdown links from each document.
- Commit only files created or modified by the current task; preserve unrelated worktree changes.

---

## File Map

- `docs/system-workflow/README.md`: audience, reading paths, role map, end-to-end flow, coverage index, and terminology
- `docs/system-workflow/01-auth-navigation.md`: authentication, password lifecycle, middleware redirects, role dashboards, and menu visibility
- `docs/system-workflow/02-admin-configuration.md`: users, roles, organizational settings, criteria, subjects, imports, workload builder, and system settings
- `docs/system-workflow/03-assignment.md`: assignment-data and criteria-assignment creation, participants, periods, report creation, edits, and deletion
- `docs/system-workflow/04-evaluation.md`: Evaluatee, Evaluator, Manager, and Director actions, score writes, evidence, comments, confirmation, and statuses
- `docs/system-workflow/05-report-dashboard-export.md`: dashboard query paths, report reads, summaries, filters, status groupings, and exports
- `docs/system-workflow/06-profile-settings.md`: profile editing, password, appearance, legacy profile pages, and public profile
- `docs/system-workflow/07-click-action-reference.md`: compact page/control/request/backend/data/status/destination lookup table
- `docs/system-workflow/08-known-risks.md`: confirmed mismatches, edge cases, audit findings, and impact boundaries

---

### Task 1: Establish the Workflow Index and Evidence Contract

**Files:**
- Create: `docs/system-workflow/README.md`
- Reference: `routes/web.php`
- Reference: `routes/report.php`
- Reference: `routes/auth.php`
- Reference: `routes/settings.php`
- Reference: `app/Support/AssignmentFlow.php`
- Reference: `docs/user-guide-th.md`
- Reference: `docs/workflow-edge-case-audit/README.md`
- Test: route inventory and Markdown structure checks

**Interfaces:**
- Consumes: approved design in `docs/superpowers/specs/2026-07-31-system-workflow-documentation-design.md`
- Produces: canonical document conventions, subsystem coverage matrix, role map, and links consumed by Tasks 2-8

- [ ] **Step 1: Inventory named routes and middleware groups**

Run:

```powershell
php artisan route:list --json | Set-Content -LiteralPath "$env:TEMP\msu-eva-routes.json"
Get-Content -Raw -LiteralPath "$env:TEMP\msu-eva-routes.json" | ConvertFrom-Json | Group-Object { ($_.middleware -join ',') } | Sort-Object Count -Descending | Select-Object Count, Name
```

Expected: route JSON is generated and the groups expose authenticated, role, and permission boundaries used by the index.

- [ ] **Step 2: Write the documentation contract and coverage matrix**

Create `README.md` with these sections in this order:

```markdown
# System Workflow — MSU EVA
## เอกสารนี้ตอบคำถามอะไร
## วิธีอ่านตามสถานการณ์
## บทบาทในระบบ
## ภาพรวม End-to-End
## สถานะหลักของงานประเมิน
## แผนที่เอกสาร
## Coverage Matrix
## หลักฐานและระดับความมั่นใจ
## คำศัพท์และชื่อในโค้ด
```

The coverage matrix must contain every subsystem from the approved design and link each row to exactly one primary detailed document.

- [ ] **Step 3: Add the cross-role Mermaid overview**

Trace and diagram this verified sequence without inventing status names:

```text
Admin configures master data and criteria
→ Admin creates an evaluation cycle/assignment
→ Evaluatee completes workload/self-evaluation
→ Evaluator reviews and scores
→ Manager reviews and forwards
→ Director certifies/completes
→ Dashboards and exports read the resulting report
```

Include branches for academic and support criteria only where the code actually diverges.

- [ ] **Step 4: Verify structure and links**

Run:

```powershell
rg -n '^## ' docs/system-workflow/README.md
rg -n '01-auth-navigation|02-admin-configuration|03-assignment|04-evaluation|05-report-dashboard-export|06-profile-settings|07-click-action-reference|08-known-risks' docs/system-workflow/README.md
git diff --check -- docs/system-workflow/README.md
```

Expected: all nine required sections and all eight child-document links are present; `git diff --check` prints nothing.

- [ ] **Step 5: Commit the index**

```powershell
git add -- docs/system-workflow/README.md
git commit -m "docs: add system workflow index"
```

---

### Task 2: Document Authentication and Role Navigation

**Files:**
- Create: `docs/system-workflow/01-auth-navigation.md`
- Modify: `docs/system-workflow/README.md`
- Reference: `routes/auth.php`
- Reference: `routes/web.php`
- Reference: `app/Http/Controllers/Auth/AuthenticatedSessionController.php`
- Reference: `app/Http/Controllers/Auth/PasswordResetLinkController.php`
- Reference: `app/Http/Controllers/Auth/NewPasswordController.php`
- Reference: `app/Providers/RouteServiceProvider.php`
- Reference: `resources/js/pages/auth/Login.vue`
- Reference: `resources/js/pages/auth/ForgotPassword.vue`
- Reference: `resources/js/pages/auth/ResetPassword.vue`
- Reference: `resources/views/layouts/app.blade.php`
- Reference: `resources/views/layouts/dashboard.blade.php`
- Reference: `resources/views/dashboard/index.blade.php`
- Reference: `resources/views/evaluatee/dashboard.blade.php`
- Reference: `resources/views/evaluator_dashboard/index.blade.php`
- Reference: `resources/views/manager_dashboard/index.blade.php`
- Reference: `resources/views/director_dashboard/index.blade.php`
- Test: `tests/Feature/Auth/AuthenticationTest.php`
- Test: `tests/Feature/Auth/PasswordResetTest.php`
- Test: `tests/Feature/DashboardTest.php`
- Test: `tests/Feature/LayoutAppShellTest.php`

**Interfaces:**
- Consumes: evidence labels and role names from `README.md`
- Produces: canonical entry points and role-to-dashboard/menu mappings used by the click-action reference

- [ ] **Step 1: Trace authentication requests and redirects**

Record login, logout, forgot-password, reset-password, confirm-password, and verification routes with HTTP methods, controller methods, middleware, success redirects, and failure feedback.

- [ ] **Step 2: Trace dashboard selection by role**

For Admin, Manager, Director, Evaluator, and Evaluatee, identify the first reachable dashboard, route name, controller, view, and behavior when the user has multiple roles.

- [ ] **Step 3: Trace visible navigation controls**

Inspect the shared layouts and role views. Record each menu label, visibility condition, route target, and active-state behavior. Mark backend routes with no discovered menu control as `ไม่พบ UI trigger`.

- [ ] **Step 4: Write failure and access paths**

Include guest redirects, inactive or unauthorized account behavior found in code/tests, 403 behavior, invalid credentials, expired reset tokens, and post-logout destination.

- [ ] **Step 5: Verify against focused tests**

Run:

```powershell
php artisan test tests/Feature/Auth/AuthenticationTest.php tests/Feature/Auth/PasswordResetTest.php tests/Feature/DashboardTest.php tests/Feature/LayoutAppShellTest.php
rg -n 'GET|POST|PUT|PATCH|DELETE|route\(|redirect|middleware' docs/system-workflow/01-auth-navigation.md
git diff --check -- docs/system-workflow/01-auth-navigation.md docs/system-workflow/README.md
```

Expected: focused tests pass and every documented action exposes a request or explicitly states that it is client-only.

- [ ] **Step 6: Commit authentication documentation**

```powershell
git add -- docs/system-workflow/01-auth-navigation.md docs/system-workflow/README.md
git commit -m "docs: map authentication and role navigation"
```

---

### Task 3: Document Admin Configuration and Import Workflows

**Files:**
- Create: `docs/system-workflow/02-admin-configuration.md`
- Modify: `docs/system-workflow/README.md`
- Reference: `app/Http/Controllers/User/UserController.php`
- Reference: `app/Http/Controllers/User/UserTemplateController.php`
- Reference: `app/Http/Controllers/User/ActivityLogController.php`
- Reference: `app/Http/Controllers/Settings/RoleAndPermissionController.php`
- Reference: `app/Http/Controllers/Setting/DepartmentsController.php`
- Reference: `app/Http/Controllers/Setting/PositionsController.php`
- Reference: `app/Http/Controllers/Setting/JobLevelsController.php`
- Reference: `app/Http/Controllers/Setting/SettingsController.php`
- Reference: `app/Http/Controllers/ReportStructureController.php`
- Reference: `app/Http/Controllers/QualityScoresController.php`
- Reference: `app/Http/Controllers/Workload/SubjectController.php`
- Reference: `app/Http/Controllers/Workload/SubjectImportController.php`
- Reference: `app/Http/Controllers/Workload/WorkloadConfigController.php`
- Reference: `app/Http/Controllers/Workload/WorkloadFormController.php`
- Reference: `app/Http/Controllers/Workload/WorkloadFormFieldController.php`
- Reference: `resources/views/user/management/`
- Reference: `resources/views/user/role-management/`
- Reference: `resources/views/departments/`
- Reference: `resources/views/positions/`
- Reference: `resources/views/Job Level/`
- Reference: `resources/views/criteria_config/`
- Reference: `resources/views/subjects/`
- Reference: `resources/views/workload/`
- Test: `tests/Feature/User/UserControllerTest.php`
- Test: `tests/Feature/Settings/BulkSettingDeleteTest.php`
- Test: `tests/Feature/RoleManagementControlsTest.php`
- Test: `tests/Feature/WorkloadConfigControllerTest.php`
- Test: `tests/Feature/Subjects/SubjectImportEndToEndTest.php`
- Test: `tests/js/workload-formula-preview.test.mjs`

**Interfaces:**
- Consumes: route/evidence conventions from `README.md`
- Produces: admin action map and configuration prerequisites consumed by assignment and evaluation documentation

- [ ] **Step 1: Map user, role, and activity-log actions**

Document search/filter/pagination, create/edit, activation changes, password handling, Excel template/import, bulk status, bulk delete, role assignment, permission editing, and activity-log inspection. Include last-active-admin and self-protection guards where implemented.

- [ ] **Step 2: Map organization and global settings actions**

Document departments, positions, job levels, university/system settings, single and bulk mutations, protected-record behavior, partial success messages, and access control.

- [ ] **Step 3: Map criteria and version configuration**

Document report versions, report structure, quantity/quality/support criteria, category ordering, activation/deletion constraints, rich-text fields, participant assignment entry points, and save/confirmation modals.

- [ ] **Step 4: Map subjects and Excel import**

Trace template download → workbook upload → preview snapshot → row selection → confirmation → stale fingerprint check → transactional create/update → result feedback. Include new/changed/unchanged/error group behavior and session ownership.

- [ ] **Step 5: Map workload builder and formulas**

Document workload forms, fields, subjects, entry configuration, ordering, formula preview, validation, and dependencies later consumed by Evaluatee workload entry.

- [ ] **Step 6: Verify focused behavior**

Run:

```powershell
php artisan test tests/Feature/User/UserControllerTest.php tests/Feature/Settings/BulkSettingDeleteTest.php tests/Feature/RoleManagementControlsTest.php tests/Feature/WorkloadConfigControllerTest.php tests/Feature/Subjects/SubjectImportEndToEndTest.php
node --test tests/js/workload-formula-preview.test.mjs
rg -n '^## |^### ' docs/system-workflow/02-admin-configuration.md
git diff --check -- docs/system-workflow/02-admin-configuration.md docs/system-workflow/README.md
```

Expected: focused tests pass; headings expose each admin subsystem; diff check is clean.

- [ ] **Step 7: Commit admin documentation**

```powershell
git add -- docs/system-workflow/02-admin-configuration.md docs/system-workflow/README.md
git commit -m "docs: map admin configuration workflows"
```

---

### Task 4: Document Assignment Creation and Lifecycle

**Files:**
- Create: `docs/system-workflow/03-assignment.md`
- Modify: `docs/system-workflow/README.md`
- Reference: `app/Http/Controllers/AssignmentDataController.php`
- Reference: `app/Http/Controllers/ReportStructureController.php`
- Reference: `app/Models/AssignmentData.php`
- Reference: `app/Models/Assignments.php`
- Reference: `app/Models/Reports.php`
- Reference: `app/Support/AssignmentFlow.php`
- Reference: `resources/views/assignment-data/`
- Reference: `resources/views/criteria_config/assign.blade.php`
- Reference: `resources/views/criteria_config/evaluators.blade.php`
- Test: `tests/Feature/Report/AssignmentDataTest.php`
- Test: `tests/Feature/DeleteActionTest.php`
- Test: `tests/Feature/CreateModalContractTest.php`
- Test: `tests/js/assignment-participant-selection.test.mjs`
- Test: `tests/js/assignment-dropdown-stacking.test.mjs`

**Interfaces:**
- Consumes: configured criteria, users, and role terminology from Tasks 1-3
- Produces: assignment/report creation contract and initial statuses consumed by evaluation and reporting documents

- [ ] **Step 1: Trace both assignment entry points**

Identify whether `assignment-data` and criteria configuration assignment screens create the same domain records or follow distinct paths. Document the UI labels, routes, controllers, and reconciliation rules separately when they differ.

- [ ] **Step 2: Trace participant and period selection**

Document Evaluatee, Evaluator, Manager, and Director selection; duplicate or missing participant validation; department/position filters; start/end dates; and criteria-version attachment.

- [ ] **Step 3: Trace persistence and generated records**

Record the transaction boundary and the exact creation/update relationship among `AssignmentData`, `Assignments`, and `Reports`, including initial status and notifications.

- [ ] **Step 4: Trace edit, delete, and inaccessible states**

Document edits after reports exist, delete guards/cascades, expired or not-yet-open assignments, missing graph relations, and navigation after successful or rejected operations.

- [ ] **Step 5: Verify focused behavior**

Run:

```powershell
php artisan test tests/Feature/Report/AssignmentDataTest.php tests/Feature/DeleteActionTest.php tests/Feature/CreateModalContractTest.php
node --test tests/js/assignment-participant-selection.test.mjs tests/js/assignment-dropdown-stacking.test.mjs
rg -n 'AssignmentData|Assignments|Reports|status|redirect|route' docs/system-workflow/03-assignment.md
git diff --check -- docs/system-workflow/03-assignment.md docs/system-workflow/README.md
```

Expected: focused tests pass and the document names all three persisted domain records and every verified initial state.

- [ ] **Step 6: Commit assignment documentation**

```powershell
git add -- docs/system-workflow/03-assignment.md docs/system-workflow/README.md
git commit -m "docs: map assignment lifecycle"
```

---

### Task 5: Document the Multi-Role Evaluation Workflow

**Files:**
- Create: `docs/system-workflow/04-evaluation.md`
- Modify: `docs/system-workflow/README.md`
- Reference: `app/Http/Controllers/Evaluatee/EvaluationScoreController.php`
- Reference: `app/Http/Controllers/Evaluatee/EvaluationWorkloadController.php`
- Reference: `app/Http/Controllers/Evaluatee/EvaluateeWorkloadEntryController.php`
- Reference: `app/Http/Controllers/EvaluatorScoreController.php`
- Reference: `app/Http/Controllers/Manager/ManagerScoreController.php`
- Reference: `app/Http/Controllers/Director/DirectorScoreController.php`
- Reference: `app/Services/ScoreService.php`
- Reference: `app/Services/SupportScoreService.php`
- Reference: `app/Services/SupportActivityEntryService.php`
- Reference: `app/Services/PreviousWorkloadImportService.php`
- Reference: `app/Support/ScoreChangePolicy.php`
- Reference: `app/Support/ScoreHistoryVisibility.php`
- Reference: `resources/views/evaluatee/`
- Reference: `resources/views/evaluator_dashboard/`
- Reference: `resources/views/manager_dashboard/`
- Reference: `resources/views/director_dashboard/`
- Reference: `resources/views/partials/evaluation-form-action-buttons.blade.php`
- Reference: `resources/views/partials/evaluation-form-confirmation-modal.blade.php`
- Test: `tests/Feature/Evaluation/EvaluateeTest.php`
- Test: `tests/Feature/Evaluation/EvaluatorTest.php`
- Test: `tests/Feature/Evaluation/ManagerTest.php`
- Test: `tests/Feature/Evaluation/DirectorTest.php`
- Test: `tests/Feature/Evaluation/SupportCriteriaReviewerFlowTest.php`
- Test: `tests/Feature/Evaluation/ScoreChangeTransactionTest.php`
- Test: `tests/js/evaluation-completeness-validation.test.mjs`
- Test: `tests/js/support-activity-entries.test.mjs`

**Interfaces:**
- Consumes: assignment graph and initial states from Task 4
- Produces: canonical state-transition table and role action map consumed by dashboards, reports, click reference, and risks

- [ ] **Step 1: Derive the status state machine from code**

List every status literal and transition found in controllers, models, support classes, dashboard metadata, and tests. For each transition record source role, prerequisite, write action, comment field, next role, and whether the transition is reversible.

- [ ] **Step 2: Document Evaluatee workload and self-evaluation**

Trace assignment opening, workload entry create/edit/delete, subject selection, evidence links, previous-workload import, quantity/quality/support input modes, autosave or explicit save, completeness confirmation, and submit behavior.

- [ ] **Step 3: Document Evaluator scoring**

Trace score input, evidence review, comments, score history/reason requirements, completeness checks, confirmation modal, persistence transaction, and handoff status.

- [ ] **Step 4: Document Manager and Director review**

Trace each reviewer dashboard entry, allowed score changes, role-specific comments, history visibility, confirmation, handoff/completion, and authorization boundaries.

- [ ] **Step 5: Document academic and support branches**

Show where quantity/quality scoring differs from support criteria, including weighted scores, indicator-mode exclusivity, user-defined activity entries, evidence, grouped projects, and achievement summaries.

- [ ] **Step 6: Add Mermaid state diagram and transition matrix**

The diagram and matrix must use exact status literals from the implementation. If modules group a status differently, choose no canonical synonym; show the discrepancy and link it to `08-known-risks.md`.

- [ ] **Step 7: Verify focused behavior**

Run:

```powershell
php artisan test tests/Feature/Evaluation/EvaluateeTest.php tests/Feature/Evaluation/EvaluatorTest.php tests/Feature/Evaluation/ManagerTest.php tests/Feature/Evaluation/DirectorTest.php tests/Feature/Evaluation/SupportCriteriaReviewerFlowTest.php tests/Feature/Evaluation/ScoreChangeTransactionTest.php
node --test tests/js/evaluation-completeness-validation.test.mjs tests/js/support-activity-entries.test.mjs
rg -n 'stateDiagram|status|Evaluatee|Evaluator|Manager|Director|ข้อควรระวัง' docs/system-workflow/04-evaluation.md
git diff --check -- docs/system-workflow/04-evaluation.md docs/system-workflow/README.md
```

Expected: focused tests pass; all roles and both criteria families appear; diff check is clean.

- [ ] **Step 8: Commit evaluation documentation**

```powershell
git add -- docs/system-workflow/04-evaluation.md docs/system-workflow/README.md
git commit -m "docs: map multi-role evaluation workflow"
```

---

### Task 6: Document Dashboards, Reports, and Exports

**Files:**
- Create: `docs/system-workflow/05-report-dashboard-export.md`
- Modify: `docs/system-workflow/README.md`
- Reference: `app/Http/Controllers/DashboardController.php`
- Reference: `app/Http/Controllers/ReportController.php`
- Reference: `app/Http/Controllers/ReportsController.php`
- Reference: `app/Http/Controllers/FileExportController.php`
- Reference: `app/Services/ReportDataService.php`
- Reference: `app/Services/GraphDataService.php`
- Reference: `app/Services/EvaluationService.php`
- Reference: `app/Support/AdminDashboardQuery.php`
- Reference: `app/Support/AdminDashboardStatusSummary.php`
- Reference: `app/Support/ReportScoreSummary.php`
- Reference: `app/Exports/ReportsExport.php`
- Reference: `app/Exports/SingleReportExport.php`
- Reference: `resources/views/dashboard/`
- Reference: `resources/views/components/export-button.blade.php`
- Test: `tests/Feature/AdminDashboardQueryTest.php`
- Test: `tests/Feature/DashboardTest.php`
- Test: `tests/Feature/GraphDataServiceTest.php`
- Test: `tests/Feature/ReportsExportTest.php`
- Test: `tests/Feature/ReportExportAuthorizationTest.php`
- Test: `tests/js/dashboard-evaluation-list.test.mjs`

**Interfaces:**
- Consumes: role transitions and status literals from Task 5
- Produces: read-model, filter, detail, and export behavior consumed by click reference and risks

- [ ] **Step 1: Trace each dashboard read path**

Document Admin, Evaluatee, Evaluator, Manager, and Director dashboard controllers/read models, query scope, metrics, status grouping, follow-up lists, pagination, filters, and AJAX fragment refresh behavior.

- [ ] **Step 2: Trace report detail pages**

Document authorization, report graph loading, quantity/quality/support summaries, evidence, comments, score-history visibility, back-button destinations, and missing/orphan report behavior.

- [ ] **Step 3: Trace export actions**

Document bulk and single-report export buttons, routes, authorization, applied filters, workbook contents, filename/response behavior, and differences between Admin and non-Admin exports.

- [ ] **Step 4: Cross-check status aggregation**

Compare `AdminDashboardStatusSummary`, dashboard metadata, `GraphDataService`, and `EvaluationService` against the Task 5 transition matrix. Record every mismatch as `ข้อควรระวัง` and link to `08-known-risks.md`.

- [ ] **Step 5: Verify focused behavior**

Run:

```powershell
php artisan test tests/Feature/AdminDashboardQueryTest.php tests/Feature/DashboardTest.php tests/Feature/GraphDataServiceTest.php tests/Feature/ReportsExportTest.php tests/Feature/ReportExportAuthorizationTest.php
node --test tests/js/dashboard-evaluation-list.test.mjs
rg -n 'dashboard|filter|status|report|export|authorization' docs/system-workflow/05-report-dashboard-export.md
git diff --check -- docs/system-workflow/05-report-dashboard-export.md docs/system-workflow/README.md
```

Expected: focused tests pass and all five role dashboards plus report/export paths are documented.

- [ ] **Step 6: Commit reporting documentation**

```powershell
git add -- docs/system-workflow/05-report-dashboard-export.md docs/system-workflow/README.md
git commit -m "docs: map dashboards reports and exports"
```

---

### Task 7: Document Profile and Personal Settings

**Files:**
- Create: `docs/system-workflow/06-profile-settings.md`
- Modify: `docs/system-workflow/README.md`
- Reference: `routes/settings.php`
- Reference: `app/Http/Controllers/Settings/ProfileController.php`
- Reference: `app/Http/Controllers/Settings/PasswordController.php`
- Reference: `app/Http/Controllers/Auth/AuthController.php`
- Reference: `resources/js/pages/settings/Profile.vue`
- Reference: `resources/js/pages/settings/Password.vue`
- Reference: `resources/js/pages/settings/Appearance.vue`
- Reference: `resources/views/user/profile/edit-profile.blade.php`
- Reference: `resources/views/user/profile/show-profile.blade.php`
- Reference: `resources/views/user/profile/show-profile-public.blade.php`
- Test: `tests/Feature/Settings/ProfileUpdateTest.php`
- Test: `tests/Feature/Settings/PasswordUpdateTest.php`
- Test: `tests/Feature/ProfilePublicViewTest.php`

**Interfaces:**
- Consumes: authentication/session conventions from Task 2
- Produces: profile and settings actions consumed by click reference

- [ ] **Step 1: Resolve modern and legacy profile paths**

Identify which profile screens are currently linked, which controllers/routes serve them, how current-user and public-profile access differ, and whether legacy pages still have UI triggers.

- [ ] **Step 2: Document profile, password, and appearance actions**

Record field validation, email-verification side effects, password confirmation/current-password errors, success behavior, account deletion if reachable, and client-only appearance persistence.

- [ ] **Step 3: Document public-profile behavior**

Record UUID generation/use, visible fields, missing UUID/user behavior, authorization boundary, and link entry points.

- [ ] **Step 4: Verify focused behavior**

Run:

```powershell
php artisan test tests/Feature/Settings/ProfileUpdateTest.php tests/Feature/Settings/PasswordUpdateTest.php tests/Feature/ProfilePublicViewTest.php
rg -n 'profile|password|appearance|public|uuid|ไม่พบ UI trigger' docs/system-workflow/06-profile-settings.md
git diff --check -- docs/system-workflow/06-profile-settings.md docs/system-workflow/README.md
```

Expected: focused tests pass and both current and legacy paths have an explicit status.

- [ ] **Step 5: Commit profile documentation**

```powershell
git add -- docs/system-workflow/06-profile-settings.md docs/system-workflow/README.md
git commit -m "docs: map profile and personal settings"
```

---

### Task 8: Build the Click Reference and Known-Risk Register

**Files:**
- Create: `docs/system-workflow/07-click-action-reference.md`
- Create: `docs/system-workflow/08-known-risks.md`
- Modify: `docs/system-workflow/README.md`
- Reference: `docs/system-workflow/01-auth-navigation.md`
- Reference: `docs/system-workflow/02-admin-configuration.md`
- Reference: `docs/system-workflow/03-assignment.md`
- Reference: `docs/system-workflow/04-evaluation.md`
- Reference: `docs/system-workflow/05-report-dashboard-export.md`
- Reference: `docs/system-workflow/06-profile-settings.md`
- Reference: `docs/workflow-edge-case-audit/01-assignment-evaluation-flow.md`
- Reference: `docs/workflow-edge-case-audit/02-score-persistence-evidence.md`
- Reference: `docs/workflow-edge-case-audit/03-report-read-dashboard-export.md`
- Reference: `docs/workflow-edge-case-audit/04-criteria-config-support-criteria.md`
- Reference: `docs/workflow-edge-case-audit/05-subject-import-settings-user-management.md`
- Reference: `docs/workflow-edge-case-audit/06-ui-accessibility-state.md`
- Test: cross-document action and risk consistency checks

**Interfaces:**
- Consumes: verified actions, transitions, and discrepancies from Tasks 2-7
- Produces: the primary quick-answer lookup and consolidated risk register

- [ ] **Step 1: Build the action-reference schema**

Use one row per distinct user action with these columns:

```markdown
| บทบาท | หน้า/เมนู | ปุ่มหรือการกระทำ | Method + Route | Backend | ผลต่อข้อมูล/สถานะ | ไปไหนต่อ | กรณีไม่สำเร็จ | หลักฐาน |
```

Group rows by subsystem, use exact UI labels, and link detailed explanations instead of duplicating long prose.

- [ ] **Step 2: Populate every documented UI action**

Extract all explicit buttons, links, form submissions, bulk actions, confirmation controls, exports, and client-only controls from Tasks 2-7. Add backend-only routes in a separate `ไม่พบ UI trigger` section.

- [ ] **Step 3: Build the known-risk schema and entries**

For every confirmed discrepancy record:

```markdown
## Risk identifier and short name
- สถานะ: ยืนยันจากโค้ด | ยืนยันจาก test | ข้อสังเกตจาก audit
- กระทบ workflow:
- พฤติกรรมปัจจุบัน:
- ผลที่ dev หรือผู้ใช้จะเห็น:
- หลักฐาน:
- แนวทางที่ audit เสนอ:
```

At minimum assess status grouping, mutable combined comments, orphan report graphs, optional-date parsing, score/evidence transactions, criteria limits, subject-import staleness, bulk mutation guards, and UI state/accessibility findings.

- [ ] **Step 4: Cross-link risks to operational workflows**

Each risk must link back to at least one detailed workflow section. Each workflow warning must link forward to the matching risk identifier.

- [ ] **Step 5: Verify action and risk coverage**

Run:

```powershell
rg -n '^\| .*\| .*\| .*\| (GET|POST|PUT|PATCH|DELETE)' docs/system-workflow/07-click-action-reference.md
rg -n '^## Risk|สถานะ:|กระทบ workflow:|พฤติกรรมปัจจุบัน:|หลักฐาน:' docs/system-workflow/08-known-risks.md
rg -n 'ไม่พบ UI trigger|ข้อควรระวัง' docs/system-workflow/*.md
git diff --check -- docs/system-workflow/README.md docs/system-workflow/07-click-action-reference.md docs/system-workflow/08-known-risks.md
```

Expected: the action table contains rows from every subsystem; every risk has the required fields; diff check is clean.

- [ ] **Step 6: Commit reference documentation**

```powershell
git add -- docs/system-workflow/README.md docs/system-workflow/07-click-action-reference.md docs/system-workflow/08-known-risks.md
git commit -m "docs: add workflow action and risk references"
```

---

### Task 9: Perform Cross-System Verification and Finalize the Documentation

**Files:**
- Modify: `docs/system-workflow/README.md`
- Modify: `docs/system-workflow/01-auth-navigation.md`
- Modify: `docs/system-workflow/02-admin-configuration.md`
- Modify: `docs/system-workflow/03-assignment.md`
- Modify: `docs/system-workflow/04-evaluation.md`
- Modify: `docs/system-workflow/05-report-dashboard-export.md`
- Modify: `docs/system-workflow/06-profile-settings.md`
- Modify: `docs/system-workflow/07-click-action-reference.md`
- Modify: `docs/system-workflow/08-known-risks.md`
- Test: complete repository route, reference, Markdown, and regression checks

**Interfaces:**
- Consumes: all documents from Tasks 1-8
- Produces: one internally consistent and navigable system workflow reference

- [ ] **Step 1: Compare documented routes with Laravel route inventory**

Run `php artisan route:list --json`, extract every named route mentioned in the documentation, and verify method/name/URI/middleware. Correct documentation mismatches; do not change application code.

- [ ] **Step 2: Verify repository links**

Run this PowerShell check from the repository root:

```powershell
$docs = Get-ChildItem -LiteralPath 'docs/system-workflow' -Filter '*.md'
$missing = foreach ($doc in $docs) {
    $content = Get-Content -Raw -LiteralPath $doc.FullName
    foreach ($match in [regex]::Matches($content, '\[[^\]]+\]\((?!https?://|#)([^)#]+)(?:#[^)]+)?\)')) {
        $target = Join-Path $doc.DirectoryName $match.Groups[1].Value
        if (-not (Test-Path -LiteralPath $target)) { "$($doc.Name): $($match.Groups[1].Value)" }
    }
}
if ($missing) { $missing; throw 'Broken documentation links found' }
```

Expected: no missing links and exit code 0.

- [ ] **Step 3: Check coverage and unresolved markers**

Run:

```powershell
rg -n 'Login|Dashboard|User|Role|Department|Position|Criteria|Subject|Workload|Assignment|Evaluatee|Evaluator|Manager|Director|Report|Export|Profile' docs/system-workflow
rg -n 'TODO|TBD|FIXME|\?\?\?' docs/system-workflow
```

Expected: every named subsystem appears; the unresolved-marker search returns no matches.

- [ ] **Step 4: Validate Mermaid fences and Markdown whitespace**

Run:

```powershell
$files = Get-ChildItem -LiteralPath 'docs/system-workflow' -Filter '*.md'
foreach ($file in $files) {
    $count = (Select-String -LiteralPath $file.FullName -Pattern '^```').Count
    if ($count % 2 -ne 0) { throw "Unclosed code fence: $($file.FullName)" }
}
git diff --check -- docs/system-workflow
```

Expected: code-fence counts are even and diff check prints nothing.

- [ ] **Step 5: Run relevant regression suites**

Run:

```powershell
php artisan test
npm run test:js
```

Expected: both existing suites pass. If an unrelated pre-existing failure occurs, record the exact command, test, and failure without changing application code.

- [ ] **Step 6: Review the quick-answer use case**

Use `07-click-action-reference.md` to answer at least one question from each subsystem in under two lookups:

```text
กดแล้วส่ง request อะไร?
controller/service ไหนทำงาน?
ข้อมูลหรือ status อะไรเปลี่ยน?
สำเร็จแล้วไปไหน?
ไม่สำเร็จเกิดอะไรขึ้น?
```

Correct missing links or duplicated/conflicting answers found during the review.

- [ ] **Step 7: Commit final verification fixes**

```powershell
git add -- docs/system-workflow
git commit -m "docs: verify complete system workflow reference"
```

---

## Completion Criteria

- All nine files in the file map exist and are linked from `README.md`.
- Every subsystem in the approved scope has one detailed workflow and corresponding quick-reference actions.
- Multi-role status transitions use exact implementation literals and expose cross-module inconsistencies.
- Important actions identify role, UI label, request, backend flow, mutation/status effect, destination, failure path, and evidence.
- Known risks clearly separate current behavior, test evidence, audit observations, and proposed remediation.
- All repository links resolve, Markdown checks pass, and relevant test results are recorded.
