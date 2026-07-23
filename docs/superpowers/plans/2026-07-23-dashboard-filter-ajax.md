# Dashboard Filter AJAX Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Update every filter-dependent admin dashboard region without a full-page refresh and render the actions in the confirmed “กรองข้อมูล → ล้างค่า” order.

**Architecture:** Keep `AdminDashboardQuery` and Blade rendering as the source of truth. Add a `dashboard-results` server-rendered fragment around overview, follow-up, and evaluation-list content, while retaining the narrower `evaluation-list` fragment for status/search/year/pagination interactions. Extend the existing dashboard script with independent cancellable loaders, URL/history synchronization, filter-indicator synchronization, and an idempotent Chart.js initializer.

**Tech Stack:** Laravel, Blade, Pest/PHPUnit, Tailwind CSS, browser Fetch API, Chart.js

## Global Constraints

- Work only on the admin dashboard at `/dashboard`; evaluator, manager, and director dashboards are out of scope.
- Do not change filtering criteria, status grouping, score calculations, or dashboard query rules.
- Preserve the existing `evaluation-list` fragment contract.
- Main filter and reset actions must not reload the whole page when JavaScript is available.
- Main filter actions update overview cards, overview chart, follow-up content, status totals, and evaluation list together.
- Render **กรองข้อมูล** before **ล้างค่า** on desktop and mobile.
- Reset removes all dashboard query parameters, matching the current reset result.
- Normal GET rendering remains the non-JavaScript fallback.
- Preserve unrelated working-tree changes and untracked files.

---

## File Structure

- Create `resources/views/dashboard/partials/index-results.blade.php`
  - Owns presentation-only dashboard maps/configuration and the complete replaceable results region.
- Modify `resources/views/dashboard/index.blade.php`
  - Keeps the page shell and includes the new results partial.
- Modify `app/Http/Controllers/DashboardController.php`
  - Returns either the complete page, `dashboard-results`, or `evaluation-list`.
- Modify `resources/views/dashboard/partials/index-filter-panel.blade.php`
  - Standardizes action order and exposes stable hooks for state/busy synchronization.
- Modify `resources/views/dashboard/partials/index-page-header.blade.php`
  - Always renders a hideable active-filter indicator.
- Modify `resources/views/dashboard/partials/index-script.blade.php`
  - Owns fragment loading, form/reset handling, history, state synchronization, errors, and chart lifecycle.
- Modify `tests/Feature/DashboardTest.php`
  - Tests server fragment contracts and client interaction markup/script contracts.

---

### Task 1: Add the complete dashboard results fragment

**Files:**
- Create: `resources/views/dashboard/partials/index-results.blade.php`
- Modify: `resources/views/dashboard/index.blade.php`
- Modify: `app/Http/Controllers/DashboardController.php`
- Test: `tests/Feature/DashboardTest.php`

**Interfaces:**
- Consumes: `AdminDashboardQuery::handle(Request)->toViewData()`
- Produces: request header `X-Dashboard-Fragment: dashboard-results`, response header with the same value, and root selector `[data-dashboard-results]`
- Preserves: request/response header `X-Dashboard-Fragment: evaluation-list` and root selector `[data-evaluation-list]`

- [ ] **Step 1: Write failing fragment tests**

Add these tests after the existing evaluation-list fragment test:

```php
test('dashboard can return all filter dependent results as a fragment', function () {
    [$admin] = createDashboardScenario(['Assigned', 'Draft']);

    $response = $this->actingAs($admin)
        ->withHeader('X-Dashboard-Fragment', 'dashboard-results')
        ->get('/dashboard');

    $response->assertOk()
        ->assertHeader('X-Dashboard-Fragment', 'dashboard-results')
        ->assertSee('data-dashboard-results', false)
        ->assertSee('id="overallCompletionChart"', false)
        ->assertSee('id="evaluation-list"', false)
        ->assertDontSee('id="filterForm"', false)
        ->assertDontSee('<html', false);
});

test('dashboard full page includes one replaceable results region', function () {
    [$admin] = createDashboardScenario(['Assigned']);

    $response = $this->actingAs($admin)->get('/dashboard');

    $response->assertOk()
        ->assertSee('data-dashboard-results', false)
        ->assertSee('data-overview-chart-config', false)
        ->assertSee('data-dashboard-results-error', false);
});
```

- [ ] **Step 2: Run the new tests and verify red**

Run:

```powershell
php artisan test tests/Feature/DashboardTest.php --filter="dashboard can return all filter dependent results|dashboard full page includes one replaceable results region"
```

Expected: FAIL because the `dashboard-results` response contract and root do not exist.

- [ ] **Step 3: Extract the result region**

Create `resources/views/dashboard/partials/index-results.blade.php`. Cut the complete opening `@php ... @endphp` block from `resources/views/dashboard/index.blade.php`—starting at `$progressMap = [` and ending after the `$overviewStatusCards` assignment—and paste that block unchanged at the top of the new partial. This preserves every current status, label, color, percentage, and chart value exactly once. Then append the following markup:

```blade
@php
    $progressMap = [
        'Assigned' => 0,
        'Manager_assign' => 0,
        'Draft' => 25,
        'Pending' => 50,
        'Evaluator_draft' => 50,
        'Director_assigned' => 75,
        'Manager_draft' => 75,
        'Director_draft' => 90,
        'Completed' => 100,
    ];

    $statusLabelMap = [
        'Assigned' => 'ยังไม่ประเมิน',
        'Draft' => 'เริ่มกรอกข้อมูล',
        'Pending' => 'รอผู้ประเมินประเมิน',
        'Evaluator_draft' => 'ผู้ประเมินเริ่มประเมิน',
        'Director_assigned' => 'รอกรรมการรับรองผล',
        'Director_draft' => 'กรรมการเริ่มรับรองผล',
        'Manager_assign' => 'ยังไม่ประเมิน',
        'Manager_draft' => 'กำลังดำเนินการ',
        'Completed' => 'ประเมินเสร็จสิ้น',
    ];

    $overviewChart = [
        'id' => 'overallCompletionChart',
        'labels' => ['มอบหมาย', 'เริ่มกรอกข้อมูล', 'กำลังดำเนินการ', 'ประเมินเสร็จสิ้น'],
        'data' => [
            $overviewEvaluateeStatusCounts['มอบหมาย'] ?? 0,
            $overviewEvaluateeStatusCounts['เริ่มกรอกข้อมูล'] ?? 0,
            $overviewEvaluateeStatusCounts['กำลังดำเนินการ'] ?? 0,
            $overviewEvaluateeStatusCounts['ประเมินเสร็จสิ้น'] ?? 0,
        ],
        'colors' => ['#ef4444', '#f97316', '#3b82f6', '#22c55e'],
        'filters' => ['มอบหมาย', 'เริ่มกรอกข้อมูล', 'กำลังดำเนินการ', 'ประเมินเสร็จสิ้น'],
        'centerValue' => $overviewCompletedEvaluateesPercent . '%',
        'centerLabel' => 'ความคืบหน้ารวม',
        'centerSubLabel' => 'เสร็จสิ้นแล้ว ' . $overviewCompletedEvaluatees . ' จาก ' . $totalEvaluatees . ' คน',
    ];

    $overviewMiniCards = [
        [
            'title' => 'บุคลากรทั้งหมด',
            'value' => $totalUsers,
            'unit' => 'คน',
            'accent' => 'text-blue-600',
            'badge' => 'bg-blue-100 text-blue-700',
        ],
        [
            'title' => 'ผู้เข้ารับการประเมิน',
            'value' => $totalEvaluatees,
            'unit' => 'คน',
            'accent' => 'text-emerald-600',
            'badge' => 'bg-emerald-100 text-emerald-700',
        ],
    ];

    $overviewStatusCards = [
        [
            'label' => 'มอบหมาย',
            'count' => $overviewEvaluateeStatusCounts['มอบหมาย'] ?? 0,
            'percent' => $totalEvaluatees > 0 ? round((($overviewEvaluateeStatusCounts['มอบหมาย'] ?? 0) / $totalEvaluatees) * 100, 1) : 0,
            'color' => '#ef4444',
            'filter' => 'มอบหมาย',
            'text' => 'ยังไม่ประเมิน',
        ],
        [
            'label' => 'เริ่มกรอกข้อมูล',
            'count' => $overviewEvaluateeStatusCounts['เริ่มกรอกข้อมูล'] ?? 0,
            'percent' => $totalEvaluatees > 0 ? round((($overviewEvaluateeStatusCounts['เริ่มกรอกข้อมูล'] ?? 0) / $totalEvaluatees) * 100, 1) : 0,
            'color' => '#f97316',
            'filter' => 'เริ่มกรอกข้อมูล',
            'text' => 'เริ่มกรอกข้อมูล',
        ],
        [
            'label' => 'กำลังดำเนินการ',
            'count' => $overviewEvaluateeStatusCounts['กำลังดำเนินการ'] ?? 0,
            'percent' => $totalEvaluatees > 0 ? round((($overviewEvaluateeStatusCounts['กำลังดำเนินการ'] ?? 0) / $totalEvaluatees) * 100, 1) : 0,
            'color' => '#3b82f6',
            'filter' => 'กำลังดำเนินการ',
            'text' => 'กำลังดำเนินการ',
        ],
        [
            'label' => 'ประเมินเสร็จสิ้น',
            'count' => $overviewEvaluateeStatusCounts['ประเมินเสร็จสิ้น'] ?? 0,
            'percent' => $totalEvaluatees > 0 ? round((($overviewEvaluateeStatusCounts['ประเมินเสร็จสิ้น'] ?? 0) / $totalEvaluatees) * 100, 1) : 0,
            'color' => '#22c55e',
            'filter' => 'ประเมินเสร็จสิ้น',
            'text' => 'ประเมินเสร็จสิ้น',
        ],
    ];
@endphp

<div data-dashboard-results aria-busy="false" class="relative">
    <script type="application/json" data-overview-chart-config>@json($overviewChart)</script>

    <div
        data-dashboard-results-error
        role="alert"
        class="mb-4 hidden rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
        โหลดข้อมูลไม่สำเร็จ กรุณาลองอีกครั้ง
    </div>

    <div
        data-dashboard-results-loading
        aria-hidden="true"
        class="pointer-events-none absolute inset-0 z-20 hidden items-start justify-center rounded-xl bg-white/70 pt-12 backdrop-blur-[1px]">
        <span class="rounded-full bg-blue-50 px-4 py-2 text-sm font-semibold text-blue-700">
            กำลังโหลดข้อมูล...
        </span>
    </div>

    <div class="mb-8 gap-6">
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            @include('dashboard.partials.index-overview-section')
            @include('dashboard.partials.index-follow-up-section')
        </div>
    </div>

    @include('dashboard.partials.index-evaluation-list')
</div>
```

Reduce `resources/views/dashboard/index.blade.php` to the page-level shell:

```blade
@extends('layouts.app')
@section('content')
    @include('dashboard.partials.index-styles')

    <div class="min-h-screen bg-gray-50 py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            @php
                $hasDashboardFilters = request('start_time')
                    || request('end_time')
                    || request('department_name')
                    || request('position_name');
            @endphp

            @include('dashboard.partials.index-page-header')
            @include('dashboard.partials.index-filter-panel')
            @include('dashboard.partials.index-results')
        </div>
    </div>

    @include('dashboard.partials.index-reviewer-modal')
@endsection

@push('scripts')
    @include('dashboard.partials.index-script')
@endpush
```

- [ ] **Step 4: Add the controller fragment branch**

In `DashboardController::index()`, add the broader fragment before the existing list fragment:

```php
$fragment = $request->header('X-Dashboard-Fragment');

if ($fragment === 'dashboard-results') {
    return response()
        ->view('dashboard.partials.index-results', $viewData)
        ->header('X-Dashboard-Fragment', 'dashboard-results');
}

if ($fragment === 'evaluation-list') {
    return response()
        ->view('dashboard.partials.index-evaluation-list', $viewData)
        ->header('X-Dashboard-Fragment', 'evaluation-list');
}
```

- [ ] **Step 5: Run dashboard tests**

Run:

```powershell
php artisan test tests/Feature/DashboardTest.php
```

Expected: all tests in `DashboardTest.php` PASS, including both fragment contracts.

- [ ] **Step 6: Commit the server fragment**

```powershell
git add -- app/Http/Controllers/DashboardController.php resources/views/dashboard/index.blade.php resources/views/dashboard/partials/index-results.blade.php tests/Feature/DashboardTest.php
git commit -m "feat: add dashboard results fragment"
```

---

### Task 2: Standardize filter actions and active-state hooks

**Files:**
- Modify: `resources/views/dashboard/partials/index-filter-panel.blade.php`
- Modify: `resources/views/dashboard/partials/index-page-header.blade.php`
- Test: `tests/Feature/DashboardTest.php`

**Interfaces:**
- Produces: `[data-dashboard-filter-submit]`, `[data-reset-filters]`, `[data-dashboard-filter-summary]`, `[data-dashboard-filter-badge]`, and `[data-dashboard-filter-indicator]`
- Consumes: `$hasDashboardFilters`

- [ ] **Step 1: Write the failing view-contract test**

Add:

```php
test('dashboard filter renders primary action before reset and stable state hooks', function () {
    [$admin] = createDashboardScenario(['Assigned']);

    $html = $this->actingAs($admin)->get('/dashboard')->getContent();

    expect($html)
        ->toContain('data-dashboard-filter-submit')
        ->toContain('data-reset-filters')
        ->toContain('data-dashboard-filter-summary')
        ->toContain('data-dashboard-filter-badge')
        ->toContain('data-dashboard-filter-indicator');

    expect(strpos($html, 'data-dashboard-filter-submit'))
        ->toBeLessThan(strpos($html, 'data-reset-filters'));
});
```

- [ ] **Step 2: Run the test and verify red**

Run:

```powershell
php artisan test tests/Feature/DashboardTest.php --filter="dashboard filter renders primary action"
```

Expected: FAIL because the stable hooks do not exist and reset currently precedes submit.

- [ ] **Step 3: Make the filter state markup persistent**

In `index-filter-panel.blade.php`, always render the summary and badge:

```blade
<p
    data-dashboard-filter-summary
    class="mt-0.5 text-xs text-gray-500 sm:text-sm">
    {{ $hasDashboardFilters ? 'มีตัวกรองที่กำลังใช้งานอยู่ กดเพื่อแก้ไขหรือล้างค่า' : 'กดเพื่อแสดงตัวเลือกการกรองเพิ่มเติม' }}
</p>
```

```blade
<span
    data-dashboard-filter-badge
    class="{{ $hasDashboardFilters ? 'inline-flex' : 'hidden' }} rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700 ring-1 ring-blue-100 sm:inline-flex">
    กำลังกรอง
</span>
```

Use a visibility class that remains hidden on all breakpoints when inactive; do not leave an unconditional `sm:inline-flex` that overrides `hidden`. A valid implementation is to compute the complete class:

```blade
class="{{ $hasDashboardFilters ? 'hidden sm:inline-flex' : 'hidden' }} rounded-full ..."
```

- [ ] **Step 4: Put the primary action first**

Replace the action group with:

```blade
<div class="flex flex-col gap-2 pt-2 md:flex-row md:justify-end">
    <button
        type="submit"
        data-dashboard-filter-submit
        class="rounded-lg bg-blue-600 px-5 py-2 text-white transition hover:bg-blue-700 disabled:cursor-wait disabled:opacity-60">
        กรองข้อมูล
    </button>
    <button
        type="button"
        data-reset-filters
        class="rounded-lg bg-gray-200 px-5 py-2 text-gray-800 transition hover:bg-gray-300 disabled:cursor-wait disabled:opacity-60">
        ล้างค่า
    </button>
</div>
```

- [ ] **Step 5: Always render the page-header indicator**

Replace the conditional block in `index-page-header.blade.php` with:

```blade
<span
    data-dashboard-filter-indicator
    class="{{ $hasDashboardFilters ? 'inline-flex' : 'hidden' }} ml-4 rounded border bg-gray-100 px-2 py-1 text-sm text-black">
    มีการกรองข้อมูล
</span>
```

- [ ] **Step 6: Run the dashboard tests**

Run:

```powershell
php artisan test tests/Feature/DashboardTest.php
```

Expected: PASS, and the action-order assertion confirms submit precedes reset.

- [ ] **Step 7: Commit the interaction markup**

```powershell
git add -- resources/views/dashboard/partials/index-filter-panel.blade.php resources/views/dashboard/partials/index-page-header.blade.php tests/Feature/DashboardTest.php
git commit -m "fix: standardize dashboard filter actions"
```

---

### Task 3: Load all dashboard results without refreshing

**Files:**
- Modify: `resources/views/dashboard/partials/index-script.blade.php`
- Test: `tests/Feature/DashboardTest.php`

**Interfaces:**
- Consumes: form `#filterForm`, result root `[data-dashboard-results]`, fragment header `dashboard-results`, chart config `[data-overview-chart-config]`
- Produces: `loadDashboardResults(url, options)`, `initializeOverviewChart()`, `syncDashboardFilterState(url)`, and `buildDashboardFilterUrl(form)`
- Preserves: `loadEvaluationList()` behavior for status/search/year/pagination

- [ ] **Step 1: Write failing script-contract assertions**

Extend `dashboard table rows expose searchable report metadata and filter hooks` with:

```php
->assertSee("'X-Dashboard-Fragment': 'dashboard-results'", false)
->assertSee('const loadDashboardResults = async', false)
->assertSee('const initializeOverviewChart =', false)
->assertSee('overviewChartInstance?.destroy()', false)
->assertSee("event.target.closest('#filterForm')", false)
->assertSee("loadDashboardResults(window.location.href, { push: false", false)
->assertDontSee("document.getElementById('filterForm').submit()", false)
```

Add a dedicated reset/URL contract test:

```php
test('dashboard asynchronous filter controller clears page and synchronizes history', function () {
    [$admin] = createDashboardScenario(['Assigned']);

    $response = $this->actingAs($admin)->get('/dashboard');

    $response->assertOk()
        ->assertSee("url.searchParams.delete('page')", false)
        ->assertSee("window.history.pushState({ dashboardFragment: 'dashboard-results' }, '', url)", false)
        ->assertSee('syncDashboardFilterState(url)', false)
        ->assertSee('syncDashboardFilterForm(url)', false)
        ->assertSee("field.value = ''", false)
        ->assertSee("data-dashboard-results-error", false);
});
```

- [ ] **Step 2: Run the contract tests and verify red**

Run:

```powershell
php artisan test tests/Feature/DashboardTest.php --filter="dashboard table rows|dashboard asynchronous filter controller"
```

Expected: FAIL on the missing dashboard-results loader and chart lifecycle strings.

- [ ] **Step 3: Replace the global reset function with URL and state helpers**

Inside `DOMContentLoaded`, define:

```js
const filterForm = document.getElementById('filterForm');
const mainFilterNames = ['start_time', 'end_time', 'department_name', 'position_name'];
let dashboardResultsRequest = null;
let evaluationListRequest = null;
let overviewChartInstance = null;

const hasMainFilters = (url) =>
    mainFilterNames.some((name) => (url.searchParams.get(name) || '').trim() !== '');

const syncDashboardFilterForm = (url) => {
    if (!filterForm) {
        return;
    }

    mainFilterNames.forEach((name) => {
        const field = filterForm.elements.namedItem(name);
        if (field) {
            field.value = url.searchParams.get(name) || '';
        }
    });
};

const syncDashboardFilterState = (url) => {
    const active = hasMainFilters(url);
    const summary = document.querySelector('[data-dashboard-filter-summary]');
    const badge = document.querySelector('[data-dashboard-filter-badge]');
    const indicator = document.querySelector('[data-dashboard-filter-indicator]');

    if (summary) {
        summary.textContent = active
            ? 'มีตัวกรองที่กำลังใช้งานอยู่ กดเพื่อแก้ไขหรือล้างค่า'
            : 'กดเพื่อแสดงตัวเลือกการกรองเพิ่มเติม';
    }

    badge?.classList.toggle('hidden', !active);
    indicator?.classList.toggle('hidden', !active);

    if (panel && chevron && toggle) {
        panel.classList.toggle('hidden', !active);
        chevron.classList.toggle('rotate-180', active);
        toggle.setAttribute('aria-expanded', active ? 'true' : 'false');
        panel.setAttribute('aria-hidden', active ? 'false' : 'true');
    }
};

const buildDashboardFilterUrl = (form) => {
    const url = new URL(form.action || window.location.pathname, window.location.origin);
    const values = new FormData(form);

    mainFilterNames.forEach((name) => {
        const value = (values.get(name) || '').toString().trim();
        if (value) {
            url.searchParams.set(name, value);
        }
    });

    url.searchParams.delete('page');
    return url;
};
```

Remove the page-level `resetFilters()` function and its call to native `form.submit()`.

- [ ] **Step 4: Add reusable busy and chart lifecycle functions**

Define:

```js
const setDashboardResultsBusy = (results, busy) => {
    results.setAttribute('aria-busy', busy ? 'true' : 'false');
    results.querySelector('[data-dashboard-results-loading]')?.classList.toggle('hidden', !busy);
    results.querySelector('[data-dashboard-results-loading]')?.classList.toggle('flex', busy);
    filterForm?.querySelectorAll('button, input, select').forEach((control) => {
        control.disabled = busy;
    });
};

const initializeOverviewChart = () => {
    overviewChartInstance?.destroy();
    overviewChartInstance = null;

    const configNode = document.querySelector('[data-overview-chart-config]');
    if (!configNode) {
        return;
    }

    const overviewChart = JSON.parse(configNode.textContent);
    const canvas = document.getElementById(overviewChart.id);
    const centerValueEl = document.getElementById('overviewChartCenterValue');
    const centerLabelEl = document.getElementById('overviewChartCenterLabel');
    const centerSubLabelEl = document.getElementById('overviewChartCenterSubLabel');

    if (!canvas) {
        return;
    }

    if (!centerValueEl || !centerLabelEl || !centerSubLabelEl) {
        return;
    }

    const resetCenter = () => {
        centerValueEl.textContent = overviewChart.centerValue;
        centerLabelEl.textContent = overviewChart.centerLabel;
        centerSubLabelEl.textContent = overviewChart.centerSubLabel || '';
    };

    const updateCenter = (chart, element) => {
        const index = element.index;
        const value = chart.data.datasets[0].data[index];
        const total = chart.data.datasets[0].data.reduce((sum, item) => sum + item, 0);
        const percent = total > 0 ? ((value / total) * 100).toFixed(1) : '0.0';
        centerValueEl.textContent = `${percent}%`;
        centerLabelEl.textContent = chart.data.labels[index];
        centerSubLabelEl.textContent = `${value} คน`;
    };

    overviewChartInstance = new Chart(canvas, {
        type: 'doughnut',
        data: {
            labels: overviewChart.labels,
            datasets: [{
                data: overviewChart.data,
                backgroundColor: overviewChart.colors,
                hoverBackgroundColor: overviewChart.colors,
                hoverOffset: 8,
                borderColor: '#ffffff',
                borderWidth: 3,
                spacing: 1,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '74%',
            plugins: { legend: { display: false }, tooltip: { enabled: false } },
            animation: window.matchMedia('(prefers-reduced-motion: reduce)').matches
                ? false
                : { animateRotate: true, duration: 900 },
            onHover(event, elements, chart) {
                chart.canvas.style.cursor = elements.length ? 'pointer' : 'default';
                elements.length ? updateCenter(chart, elements[0]) : resetCenter();
            },
            onClick(event, elements, chart) {
                elements.length ? updateCenter(chart, elements[0]) : resetCenter();
            },
        },
    });

    resetCenter();
};
```

The explicit center-node guard above prevents assignments to missing elements. Keep the remaining chart behavior identical to the current implementation.

- [ ] **Step 5: Add the dashboard-results loader**

Define:

```js
const loadDashboardResults = async (url, { push = true, focus = null } = {}) => {
    const currentResults = document.querySelector('[data-dashboard-results]');
    if (!currentResults) {
        return;
    }

    dashboardResultsRequest?.abort();
    evaluationListRequest?.abort();
    dashboardResultsRequest = new AbortController();
    currentResults.querySelector('[data-dashboard-results-error]')?.classList.add('hidden');
    setDashboardResultsBusy(currentResults, true);

    try {
        const response = await fetch(url, {
            headers: {
                Accept: 'text/html',
                'X-Dashboard-Fragment': 'dashboard-results',
            },
            signal: dashboardResultsRequest.signal,
        });

        if (!response.ok || response.headers.get('X-Dashboard-Fragment') !== 'dashboard-results') {
            throw new Error(`Unexpected dashboard response: ${response.status}`);
        }

        const container = document.createElement('div');
        container.innerHTML = await response.text();
        const nextResults = container.querySelector('[data-dashboard-results]');
        if (!nextResults) {
            throw new Error('Dashboard results fragment is missing');
        }

        overviewChartInstance?.destroy();
        overviewChartInstance = null;
        currentResults.replaceWith(nextResults);

        const nextUrl = new URL(url, window.location.href);
        if (push) {
            window.history.pushState({ dashboardFragment: 'dashboard-results' }, '', nextUrl);
        }

        syncDashboardFilterForm(nextUrl);
        syncDashboardFilterState(nextUrl);
        initializeOverviewChart();

        if (focus) {
            document.querySelector(focus)?.focus();
        }
    } catch (error) {
        if (error.name === 'AbortError') {
            return;
        }

        setDashboardResultsBusy(currentResults, false);
        currentResults.querySelector('[data-dashboard-results-error]')?.classList.remove('hidden');
    } finally {
        const activeResults = document.querySelector('[data-dashboard-results]');
        if (activeResults) {
            setDashboardResultsBusy(activeResults, false);
        }
    }
};
```

Only update history after a valid fragment has been parsed. On failure, keep the previous result root mounted.

- [ ] **Step 6: Intercept main submit and reset**

Add submit handling before the list-search submit handler:

```js
document.addEventListener('submit', (event) => {
    const form = event.target.closest('#filterForm');
    if (!form) {
        return;
    }

    event.preventDefault();
    loadDashboardResults(buildDashboardFilterUrl(form).toString(), {
        focus: '[data-dashboard-filter-submit]',
    });
});
```

Replace the reset delegation body with:

```js
document.addEventListener('click', (event) => {
    const resetButton = event.target.closest('[data-reset-filters]');
    if (!resetButton || !filterForm) {
        return;
    }

    event.preventDefault();
    mainFilterNames.forEach((name) => {
        const field = filterForm.elements.namedItem(name);
        if (field) {
            field.value = '';
        }
    });

    loadDashboardResults(new URL(window.location.pathname, window.location.origin).toString(), {
        focus: '[data-reset-filters]',
    });
});
```

Remove the old global binding guard because this page script is mounted once and the filter shell is not replaced.

- [ ] **Step 7: Make history reload the broad fragment**

Replace the current `popstate` listener:

```js
window.addEventListener('popstate', () => {
    loadDashboardResults(window.location.href, {
        push: false,
        focus: '#evaluation-list-heading',
    });
});
```

Keep list actions on `loadEvaluationList()`. Change their successful `pushState` call to include a marker:

```js
window.history.pushState({ dashboardFragment: 'evaluation-list' }, '', url);
```

The broad `popstate` reload is deliberate because historical URLs can change main filters and therefore all result regions.

- [ ] **Step 8: Initialize the first chart and remove the one-shot chart block**

Delete the old one-shot `const overviewChart = @json($overviewChart)` block through `resetOverviewCenter();`. Call this once near the end of `DOMContentLoaded`:

```js
initializeOverviewChart();
```

The script must no longer depend on `$overviewChart`, allowing the same page script to initialize every newly received fragment.

- [ ] **Step 9: Run dashboard tests**

Run:

```powershell
php artisan test tests/Feature/DashboardTest.php
```

Expected: PASS with the full-page and both fragment contracts intact.

- [ ] **Step 10: Run formatting checks**

Run:

```powershell
npx prettier --check resources/views/dashboard/index.blade.php resources/views/dashboard/partials/index-results.blade.php resources/views/dashboard/partials/index-filter-panel.blade.php resources/views/dashboard/partials/index-page-header.blade.php resources/views/dashboard/partials/index-script.blade.php
```

Expected: `All matched files use Prettier code style!`

If the check fails, run Prettier only on those five files and repeat the check:

```powershell
npx prettier --write resources/views/dashboard/index.blade.php resources/views/dashboard/partials/index-results.blade.php resources/views/dashboard/partials/index-filter-panel.blade.php resources/views/dashboard/partials/index-page-header.blade.php resources/views/dashboard/partials/index-script.blade.php
```

- [ ] **Step 11: Commit asynchronous filtering**

```powershell
git add -- resources/views/dashboard/partials/index-script.blade.php tests/Feature/DashboardTest.php
git commit -m "feat: filter dashboard without page refresh"
```

---

### Task 4: Regression verification

**Files:**
- Verify only; do not modify unrelated files.

**Interfaces:**
- Verifies: full page, both fragment contracts, dashboard feature suite, complete application suite, and production asset build

- [ ] **Step 1: Run dashboard feature tests**

```powershell
php artisan test tests/Feature/DashboardTest.php
```

Expected: all dashboard feature tests PASS.

- [ ] **Step 2: Run the complete PHP test suite**

```powershell
php artisan test
```

Expected: all application tests PASS.

- [ ] **Step 3: Run JavaScript tests**

```powershell
npm run test:js
```

Expected: all Node test files PASS.

- [ ] **Step 4: Build production assets**

```powershell
npm run build
```

Expected: Vite exits successfully and writes the production manifest/assets.

- [ ] **Step 5: Check whitespace and intended scope**

```powershell
git diff --check
git status --short
git log -4 --oneline
```

Expected:

- `git diff --check` prints no errors.
- Existing unrelated modified/untracked files remain untouched.
- The recent commits show the server fragment, standardized actions, and AJAX filtering changes.

- [ ] **Step 6: Perform focused browser smoke verification**

At `/dashboard` while signed in as an admin:

1. Open the filter panel.
2. Confirm **กรองข้อมูล** appears before **ล้างค่า** at desktop and narrow widths.
3. Apply a date, department, or position filter.
4. Confirm the browser does not perform a document navigation.
5. Confirm overview cards, doughnut chart, follow-up items, status totals, and table update together.
6. Confirm the filter panel stays open and active indicators appear.
7. Click **ล้างค่า**.
8. Confirm all query parameters disappear, default results return, indicators disappear, and no full-page refresh occurs.
9. Use a status filter, search, year selection, and pagination; confirm only the evaluation list updates.
10. Use browser Back and Forward; confirm form values, indicators, chart, follow-up content, and table match the URL.
11. Simulate an offline request in browser developer tools; confirm old results remain and the Thai retry message appears.

Expected: all eleven checks match the approved design.
