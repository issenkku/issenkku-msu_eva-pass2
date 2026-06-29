# Workload Builder Audit Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Finish the remaining browser-level UX, accessibility, and performance audit for the workload builder surface so the page is understandable, keyboard-safe, and measurably bounded in query cost on real data.

**Architecture:** Keep the existing Blade + inline-script workload builder intact. Audit the current controls, then make only targeted fixes where the interaction is confusing, unlabelled, or query-heavy. Prefer semantic HTML and delegated listeners over new UI abstractions.

**Tech Stack:** Laravel Blade, vanilla JavaScript, Pest, SQLite testing DB, MySQL production DB read-only verification.

---

### Task 1: Browser smoke for workload builder

**Files:**
- Inspect: `resources/views/workload/app.blade.php`
- Inspect: `resources/views/workload/partials/app-script.blade.php`
- Inspect: `resources/views/workload/partials/app-styles.blade.php`
- Inspect: `routes/report.php`
- Test: `tests/Feature/WorkloadBuilderAccessibilityTest.php`

- [ ] **Step 1: Open the workload builder route in a browser with a real admin session**

Verify the page loads with an active `quant_sub_criteria_id` and the builder renders the nav, cards, toolbar, toast, and sticky save affordance.

- [ ] **Step 2: Exercise the primary controls**

Check collapse/expand, add main group, add sub group, add item, delete controls, reset, save, back navigation, and toast close. Confirm no action is hidden behind an unlabeled icon or dead control.

- [ ] **Step 3: Record concrete UX findings**

For each issue, note the control, exact failure mode, and whether the problem is duplicate control, unclear state, broken affordance, or keyboard gap.

### Task 2: Accessibility hardening for workload controls

**Files:**
- Modify: `resources/views/workload/app.blade.php`
- Modify: `resources/views/workload/partials/app-script.blade.php`
- Test: `tests/Feature/WorkloadBuilderAccessibilityTest.php`

- [x] **Step 1: Write or extend the accessibility regression test**

Lock the existing accessible names and ARIA state wiring for collapse/delete/toast controls, and add any missing contract for keyboard-facing controls you confirm in Task 1.

- [x] **Step 2: Apply the smallest possible markup fix**

Keep the current layout. Add or adjust `aria-label`, `aria-expanded`, `aria-controls`, and any missing native semantics only where the browser smoke showed a real gap.

- [x] **Step 3: Make the script keep the state in sync**

If a control is stateful, ensure the JS updates the relevant ARIA attributes on toggle without introducing a new component system.

### Task 3: Query and save-path profiling

**Files:**
- Modify: `app/Http/Controllers/Workload/WorkloadConfigController.php`
- Test: `tests/Feature/WorkloadConfigControllerTest.php`
- Optional new test if needed: `tests/Feature/WorkloadBuilderAccessibilityTest.php`

- [x] **Step 1: Measure current request/query counts on `subBlocks()` and `save()`**

Use a direct DB listener or feature test budget to confirm the actual query count on a realistic fixture. Separate controller work from middleware noise.

- [x] **Step 2: Decide whether the current ceiling is acceptable**

If the request is already bounded and the remaining queries are inherent to the flow, keep the budget and document it. If there is avoidable repetition, optimize only that repeat work.

- [x] **Step 3: Add a focused regression guard**

Keep the budget test in `tests/Feature/WorkloadConfigControllerTest.php` so the workload path cannot regress back into obvious N+1 or metadata thrash.

### Task 4: Final audit and cleanup

**Files:**
- Inspect: `resources/views/workload/app.blade.php`
- Inspect: `resources/views/workload/partials/app-script.blade.php`
- Inspect: `resources/views/workload/partials/app-styles.blade.php`
- Inspect: `tests/Feature/WorkloadBuilderAccessibilityTest.php`
- Inspect: `tests/Feature/WorkloadConfigControllerTest.php`

- [x] **Step 1: Re-scan the workload surface for remaining unlabeled controls**

Confirm the workload builder does not leave any icon-only or toggle controls without names after the fixes.

- [x] **Step 2: Re-run the focused regression suite**

Validate the workload builder tests plus the nearby query-heavy suite that was already green before this phase.

- [ ] **Step 3: Summarize remaining risks**

List any residual browser QA gaps, any intentionally accepted query budgets, and any controls that remain acceptable by design.

**Tests:**
- `php -l resources\views\workload\app.blade.php`
- `php -l resources\views\workload\partials\app-script.blade.php`
- `php -l tests\Feature\WorkloadBuilderAccessibilityTest.php`
- `php -l tests\Feature\WorkloadConfigControllerTest.php`
- `vendor\bin\pest tests\Feature\WorkloadBuilderAccessibilityTest.php tests\Feature\WorkloadConfigControllerTest.php tests\Feature\GraphDataServiceTest.php tests\Feature\ScoreServiceTest.php tests\Feature\AdminDashboardQueryTest.php`

**Definition of Done:**
- Browser smoke for the workload builder completed and findings are either fixed or logged as explicit risks.
- Accessible names and collapse state wiring exist for the interactive workload controls.
- `subBlocks()` and `save()` stay within the locked query budgets on the current test fixtures.
- Focused tests pass.
- No public API changes were introduced.
- No commit or push was made in this planning pass.
