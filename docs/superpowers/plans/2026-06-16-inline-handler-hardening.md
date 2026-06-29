# Remaining Inline Handler Hardening Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Remove the remaining non-API inline event handlers from the UI layer by replacing them with data hooks and delegated listeners, while keeping behavior unchanged and locking each contract with regression tests.

**Architecture:** Keep the current Blade component and partial boundaries. Add `data-*` hooks to existing markup and bind behavior in the existing page/component scripts instead of adding a new frontend framework or build step. Use component render tests for reusable UI pieces and page render tests for route-level controls.

**Tech Stack:** Laravel Blade, plain browser JavaScript, Pest, SQLite test database.

**Non-goals:**
- Do not change public APIs such as the `onclick` prop support in `resources/views/components/button.blade.php`
- Do not redesign layouts or introduce a new frontend framework
- Do not change backend routes, controllers, or scoring logic
- Do not commit or push during this phase

---

### Task 1: Dashboard shell controls

**Files:**
- Modify: `resources/views/components/search-bar.blade.php`
- Modify: `resources/views/dashboard/partials/index-filter-panel.blade.php`
- Modify: `resources/views/components/scatter-chart-component.blade.php`
- Modify: `resources/views/dashboard/partials/index-script.blade.php`
- Test: `tests/Feature/SearchBarTest.php`
- Test: `tests/Feature/DashboardTest.php`
- Create: `tests/Feature/ScatterChartComponentTest.php`

- [ ] **Step 1: Write the failing tests**

Add coverage for:
- the search bar clear button rendering as a data-hook trigger instead of inline `onclick`
- the dashboard filter-panel reset action rendering with a data hook
- the scatter chart download action rendering with a data hook

Example assertions:
```php
expect($html)
    ->toContain('data-auto-search-clear')
    ->not->toContain('onclick="this.form.search.value=\'\'; this.form.submit();"');
```

- [ ] **Step 2: Run the tests to confirm they fail**

Run:
```bash
vendor\bin\pest tests\Feature\SearchBarTest.php tests\Feature\DashboardTest.php tests\Feature\ScatterChartComponentTest.php
```

Expected:
- The new assertions fail on the current inline-handler markup

- [ ] **Step 3: Replace inline handlers with data hooks**

Use existing scripts and delegated listeners:
- replace `onclick="this.form.search.value=''; this.form.submit();"` with a `data-auto-search-clear` hook
- replace `onclick="resetFilters()"` with a `data-reset-filters` hook
- replace the scatter download `onclick` with a `data-scatter-download` hook
- keep the existing behavior and query-string handling unchanged

- [ ] **Step 4: Run the tests again**

Run:
```bash
vendor\bin\pest tests\Feature\SearchBarTest.php tests\Feature\DashboardTest.php tests\Feature\ScatterChartComponentTest.php
```

Expected:
- PASS

### Task 2: Evaluation and account-action controls

**Files:**
- Modify: `resources/views/evaluator_dashboard/partials/form-actions.blade.php`
- Modify: `resources/views/user/profile/show-profile-public.blade.php`
- Modify: `resources/views/user/profile/partials/edit-profile-script.blade.php`
- Modify: `resources/views/user/role-management/partials/index-header.blade.php`
- Modify: `resources/views/user/role-management/partials/index-modal-actions.blade.php`
- Test: `tests/Feature/EvaluationControlsTest.php`
- Test: `tests/Feature/ProfilePublicViewTest.php`
- Test: `tests/Feature/RoleManagementControlsTest.php`

- [ ] **Step 1: Write the failing tests**

Add coverage for:
- evaluator dashboard submit confirmation using a data hook instead of inline `confirmSubmit()`
- public profile print control using a non-inline trigger
- profile education-row removal using a data hook
- role-management create/cancel modal buttons using data hooks

Example assertions:
```php
expect($html)
    ->toContain('data-form-submit-confirm')
    ->not->toContain('onclick="confirmSubmit()"');
```

- [ ] **Step 2: Run the tests to confirm they fail**

Run:
```bash
vendor\bin\pest tests\Feature\EvaluationControlsTest.php tests\Feature\ProfilePublicViewTest.php tests\Feature\RoleManagementControlsTest.php
```

Expected:
- The new assertions fail on the current inline-handler markup

- [ ] **Step 3: Replace inline handlers with data hooks**

Use the existing page scripts and DOM event delegation:
- move evaluator submit confirmation off inline `onclick`
- move public profile print action off inline `window.print()`
- move education-row removal off inline `removeEducationHistoryRow(this)`
- move role-management modal open/cancel actions off inline handlers

Keep the current modal and print behavior unchanged.

- [ ] **Step 4: Run the tests again**

Run:
```bash
vendor\bin\pest tests\Feature\EvaluationControlsTest.php tests\Feature\ProfilePublicViewTest.php tests\Feature\RoleManagementControlsTest.php
```

Expected:
- PASS

### Task 3: Dynamic builder scripts

**Files:**
- Modify: `resources/views/criteria_config/partials/index-script.blade.php`
- Modify: `resources/views/quality-scores/partials/create-script.blade.php`
- Test: `tests/Feature/CriteriaConfigControlsTest.php`
- Test: `tests/Feature/QualityScoresCreateScriptTest.php`

- [ ] **Step 1: Write the failing tests**

Add coverage for generated markup in the JS builders:
- criteria version copy/delete buttons should render data hooks instead of inline `onclick`
- quality-score row remove buttons should render data hooks instead of inline `onclick`
- quality-score inputs should not rely on inline `onchange`

Example assertions:
```php
expect($html)
    ->toContain('data-criteria-copy')
    ->not->toContain('onclick="copyCriteriaVersion(');
```

- [ ] **Step 2: Run the tests to confirm they fail**

Run:
```bash
vendor\bin\pest tests\Feature\CriteriaConfigControlsTest.php tests\Feature\QualityScoresCreateScriptTest.php
```

Expected:
- The assertions fail on the current generated markup

- [ ] **Step 3: Replace inline handlers with data hooks**

Update the generated button/input markup to use `data-*` attributes and bind the existing script behavior with delegated listeners.

Keep the current add/remove row workflow and submit-state behavior unchanged.

- [ ] **Step 4: Run the tests again**

Run:
```bash
vendor\bin\pest tests\Feature\CriteriaConfigControlsTest.php tests\Feature\QualityScoresCreateScriptTest.php
```

Expected:
- PASS

### Task 4: Sequential verification and regression sweep

**Files:**
- No new code expected unless a regression is found

- [ ] **Step 1: Run syntax checks on every file touched in Tasks 1-3**

Run:
```bash
php -l resources\views\components\search-bar.blade.php
php -l resources\views\dashboard\partials\index-filter-panel.blade.php
php -l resources\views\components\scatter-chart-component.blade.php
php -l resources\views\dashboard\partials\index-script.blade.php
php -l resources\views\evaluator_dashboard\partials\form-actions.blade.php
php -l resources\views\user\profile\show-profile-public.blade.php
php -l resources\views\user\profile\partials\edit-profile-script.blade.php
php -l resources\views\user\role-management\partials\index-header.blade.php
php -l resources\views\user\role-management\partials\index-modal-actions.blade.php
php -l resources\views\criteria_config\partials\index-script.blade.php
php -l resources\views\quality-scores\partials\create-script.blade.php
```

- [ ] **Step 2: Run the focused test set sequentially**

Run:
```bash
vendor\bin\pest tests\Feature\SearchBarTest.php tests\Feature\DashboardTest.php tests\Feature\ScatterChartComponentTest.php tests\Feature\EvaluationControlsTest.php tests\Feature\ProfilePublicViewTest.php tests\Feature\RoleManagementControlsTest.php tests\Feature\CriteriaConfigControlsTest.php tests\Feature\QualityScoresCreateScriptTest.php
```

Expected:
- all targeted tests pass in a single sequential run

- [ ] **Step 3: Run the broader UI regression suite**

Run:
```bash
vendor\bin\pest tests\Feature\SearchBarTest.php tests\Feature\DashboardTest.php tests\Feature\ScatterChartComponentTest.php tests\Feature\EvaluationControlsTest.php tests\Feature\ProfilePublicViewTest.php tests\Feature\RoleManagementControlsTest.php tests\Feature\CriteriaConfigControlsTest.php tests\Feature\QualityScoresCreateScriptTest.php tests\Feature\RoleTableRenderTest.php tests\Feature\FilterComponentTest.php tests\Feature\FilterBadgeSingleTest.php tests\Feature\ButtonComponentTest.php tests\Feature\ListToolbarTest.php tests\Feature\ShowBackButtonTest.php tests\Feature\FlashMessageAccessibilityTest.php tests\Feature\ImportModalTest.php tests\Feature\LoginFormTest.php tests\Feature\DeleteActionTest.php
```

Expected:
- PASS

Definition of Done:
- The remaining non-API inline handlers in the listed UI subsystems are replaced with data hooks and delegated listeners
- Existing behavior is unchanged
- New or updated render tests lock the new contract
- Existing dashboard/evaluation regression tests still pass
- No public API is changed
- No commit or push is performed
