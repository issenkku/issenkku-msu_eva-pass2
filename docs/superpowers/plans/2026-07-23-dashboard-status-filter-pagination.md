# Dashboard Status Filter and Pagination Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Make the admin dashboard status badges filter the complete evaluation collection before pagination and update the evaluation list without a full-page refresh.

**Architecture:** `AdminDashboardStatusSummary` becomes the single source of truth for mapping raw report statuses to the four Thai status groups. `AdminDashboardQuery` calculates badge totals from the base filtered collection, applies the selected status only to the list collection, and then paginates it. The existing `/dashboard` endpoint returns either the full page or a server-rendered evaluation-list fragment, while delegated JavaScript navigation replaces that fragment and synchronizes browser history.

**Tech Stack:** Laravel, Blade, Pest/PHPUnit, Tailwind CSS, browser Fetch API

## Global Constraints

- Status and search filters must operate on the complete collection before pagination.
- Badge counts, rows, result summary, and paginator must describe compatible collections.
- `Manager_assign` belongs only to “กำลังดำเนินการ”.
- Status, search, and page values must remain in the URL.
- Status changes, search submissions, and pagination must not refresh the whole dashboard when JavaScript is available.
- Full-page GET navigation remains the non-JavaScript fallback.
- Do not change overview-chart semantics, export workbook contents, or workflow transitions.
- Do not add a frontend framework or a new runtime dependency.

---

## File Structure

- Modify `app/Support/AdminDashboardStatusSummary.php`: own canonical group constants, raw-status classification, group validation, counting, and collection filtering.
- Modify `app/Support/AdminDashboardQuery.php`: preserve the base dashboard collection, filter the list by canonical status before pagination, and expose `activeStatus`.
- Modify `app/Http/Controllers/DashboardController.php`: return the evaluation-list fragment when requested by the dashboard AJAX controller.
- Create `resources/views/dashboard/partials/index-evaluation-list.blade.php`: render the replaceable list region from the same server data used by the full page.
- Modify `resources/views/dashboard/index.blade.php`: include the new list partial and remove duplicated list markup.
- Modify `resources/views/dashboard/partials/index-list-header.blade.php`: expose a focusable heading and keep the search form inside the replaceable region.
- Modify `resources/views/dashboard/partials/index-status-filters.blade.php`: render the server-selected active state and navigable fallback URLs.
- Modify `resources/views/dashboard/partials/index-table-row.blade.php`: consume canonical status-group data and use paginator-aware numbering.
- Modify `resources/views/dashboard/partials/index-pagination.blade.php`: add stable hooks and an accessible live result summary.
- Modify `resources/views/dashboard/partials/index-loading-state.blade.php`: expose a loading hook and live announcement.
- Modify `resources/views/dashboard/partials/index-empty-state.blade.php`: make the server-rendered empty state authoritative.
- Modify `resources/views/dashboard/partials/index-script.blade.php`: replace current-page row hiding with delegated fragment navigation.
- Create `tests/Unit/AdminDashboardStatusSummaryTest.php`: verify mutually exclusive canonical classification.
- Modify `tests/Feature/AdminDashboardQueryTest.php`: verify status filtering occurs before pagination.
- Modify `tests/Feature/DashboardTest.php`: verify fragment/full-page contracts, active state, query-preserving links, and fallback behaviour.

### Task 1: Canonical Status Classification

**Files:**
- Create: `tests/Unit/AdminDashboardStatusSummaryTest.php`
- Modify: `app/Support/AdminDashboardStatusSummary.php`

**Interfaces:**
- Produces: `AdminDashboardStatusSummary::groupForStatus(?string $status): string`
- Produces: `AdminDashboardStatusSummary::normalizeGroup(?string $group): string`
- Produces: `AdminDashboardStatusSummary::filterByGroup(Collection $evaluations, ?string $group): Collection`
- Preserves: `statusCounts()`, `overviewStatusCounts()`, and the existing count methods

- [ ] **Step 1: Write failing classification tests**

```php
<?php

use App\Support\AdminDashboardStatusSummary;

function dashboardAssignmentWithStatus(?string $status): object
{
    return (object) [
        'report' => $status === null ? null : (object) ['status' => $status],
    ];
}

test('each report status belongs to one canonical dashboard group', function (
    ?string $status,
    string $expectedGroup,
) {
    $summary = app(AdminDashboardStatusSummary::class);

    expect($summary->groupForStatus($status))->toBe($expectedGroup);
})->with([
    [null, 'มอบหมาย'],
    ['Assigned', 'มอบหมาย'],
    ['Draft', 'เริ่มกรอกข้อมูล'],
    ['Pending', 'กำลังดำเนินการ'],
    ['Evaluator_draft', 'กำลังดำเนินการ'],
    ['Director_assigned', 'กำลังดำเนินการ'],
    ['Director_draft', 'กำลังดำเนินการ'],
    ['Manager_assign', 'กำลังดำเนินการ'],
    ['Manager_draft', 'กำลังดำเนินการ'],
    ['Completed', 'ประเมินเสร็จสิ้น'],
]);

test('manager assign is counted and filtered only as in progress', function () {
    $summary = app(AdminDashboardStatusSummary::class);
    $evaluations = collect([
        dashboardAssignmentWithStatus('Assigned'),
        dashboardAssignmentWithStatus('Manager_assign'),
    ]);

    expect($summary->statusCounts($evaluations))->toBe([
        'ทั้งหมด' => 2,
        'มอบหมาย' => 1,
        'เริ่มกรอกข้อมูล' => 0,
        'กำลังดำเนินการ' => 1,
        'ประเมินเสร็จสิ้น' => 0,
    ])->and($summary->filterByGroup($evaluations, 'มอบหมาย'))->toHaveCount(1)
        ->and($summary->filterByGroup($evaluations, 'กำลังดำเนินการ'))->toHaveCount(1);
});

test('unknown dashboard groups normalize to all', function () {
    $summary = app(AdminDashboardStatusSummary::class);

    expect($summary->normalizeGroup('not-a-group'))->toBe('all')
        ->and($summary->normalizeGroup(null))->toBe('all');
});
```

- [ ] **Step 2: Run the unit test and verify it fails**

Run:

```powershell
php artisan test tests/Unit/AdminDashboardStatusSummaryTest.php
```

Expected: FAIL because `groupForStatus`, `normalizeGroup`, and `filterByGroup` do not exist, and `Manager_assign` is counted in overlapping groups.

- [ ] **Step 3: Implement the canonical mapping and derive all list counts from it**

Add constants and public methods to `AdminDashboardStatusSummary`:

```php
public const ALL = 'all';
public const ASSIGNED = 'มอบหมาย';
public const STARTED = 'เริ่มกรอกข้อมูล';
public const IN_PROGRESS = 'กำลังดำเนินการ';
public const COMPLETED = 'ประเมินเสร็จสิ้น';

private const STATUS_GROUPS = [
    'Assigned' => self::ASSIGNED,
    'Draft' => self::STARTED,
    'Pending' => self::IN_PROGRESS,
    'Evaluator_draft' => self::IN_PROGRESS,
    'Director_assigned' => self::IN_PROGRESS,
    'Director_draft' => self::IN_PROGRESS,
    'Manager_assign' => self::IN_PROGRESS,
    'Manager_draft' => self::IN_PROGRESS,
    'Completed' => self::COMPLETED,
];

public function groupForStatus(?string $status): string
{
    return self::STATUS_GROUPS[$status ?? 'Assigned'] ?? self::ASSIGNED;
}

public function normalizeGroup(?string $group): string
{
    return in_array($group, [
        self::ASSIGNED,
        self::STARTED,
        self::IN_PROGRESS,
        self::COMPLETED,
    ], true) ? $group : self::ALL;
}

public function filterByGroup($evaluations, ?string $group)
{
    $normalizedGroup = $this->normalizeGroup($group);

    if ($normalizedGroup === self::ALL) {
        return $evaluations->values();
    }

    return $evaluations
        ->filter(fn ($assignment) => $this->groupForStatus(
            optional($assignment->report)->status
        ) === $normalizedGroup)
        ->values();
}
```

Rewrite `statusCounts()` so every record is classified once:

```php
public function statusCounts($evaluations): array
{
    $groupedCounts = $evaluations
        ->countBy(fn ($assignment) => $this->groupForStatus(
            optional($assignment->report)->status
        ));

    return [
        'ทั้งหมด' => $evaluations->count(),
        self::ASSIGNED => $groupedCounts->get(self::ASSIGNED, 0),
        self::STARTED => $groupedCounts->get(self::STARTED, 0),
        self::IN_PROGRESS => $groupedCounts->get(self::IN_PROGRESS, 0),
        self::COMPLETED => $groupedCounts->get(self::COMPLETED, 0),
    ];
}
```

Make the four existing count methods count through `groupForStatus()` instead
of overlapping raw-status arrays:

```php
public function notStartedStatusesCount($evaluations): int
{
    return $this->countByGroup($evaluations, self::ASSIGNED);
}

public function draftStatusesCount($evaluations): int
{
    return $this->countByGroup($evaluations, self::STARTED);
}

public function inReviewStatusesCount($evaluations): int
{
    return $this->countByGroup($evaluations, self::IN_PROGRESS);
}

public function completedStatusesCount($evaluations): int
{
    return $this->countByGroup($evaluations, self::COMPLETED);
}

private function countByGroup($evaluations, string $group): int
{
    return $evaluations
        ->filter(fn ($assignment) => $this->groupForStatus(
            optional($assignment->report)->status
        ) === $group)
        ->count();
}
```

Keep `overviewStatusCounts()` unchanged because overview semantics are out of
scope.

- [ ] **Step 4: Run the focused tests**

Run:

```powershell
php artisan test tests/Unit/AdminDashboardStatusSummaryTest.php tests/Feature/AdminDashboardQueryTest.php
```

Expected: PASS.

- [ ] **Step 5: Commit the canonical status work**

```powershell
git add app/Support/AdminDashboardStatusSummary.php tests/Unit/AdminDashboardStatusSummaryTest.php
git commit -m "fix: centralize dashboard status groups"
```

### Task 2: Filter Before Pagination

**Files:**
- Modify: `tests/Feature/AdminDashboardQueryTest.php`
- Modify: `app/Support/AdminDashboardQuery.php`
- Modify: `app/Support/AdminDashboardData.php` only if the current value-object contract requires an accessor; otherwise leave unchanged

**Interfaces:**
- Consumes: `AdminDashboardStatusSummary::normalizeGroup()` and `filterByGroup()`
- Produces view data: `activeStatus: string`
- Preserves view data: all current dashboard overview and export-related keys

- [ ] **Step 1: Add failing query tests for filtering before pagination**

Add the required model imports and this factory helper in
`AdminDashboardQueryTest.php`, then add:

```php
use App\Models\Assignments;
use App\Models\AssignmentData;
use App\Models\ReportData;
use App\Models\Reports;
use App\Models\User;

function createDashboardAssignments(
    int $count,
    string $status,
    string $titlePrefix = 'Plan',
): void {
    foreach (range(1, $count) as $index) {
        $evaluatee = User::factory()->create();
        $reportData = ReportData::factory()->create([
            'report_title' => "{$titlePrefix} {$index}",
        ]);
        $report = Reports::factory()->create([
            'report_data_id' => $reportData->id,
            'status' => $status,
        ]);
        $assignmentData = AssignmentData::factory()->create();

        Assignments::factory()->create([
            'assignment_data_id' => $assignmentData->id,
            'report_id' => $report->id,
            'evaluatee_id' => $evaluatee->id,
        ]);
    }
}
```

```php
test('admin dashboard applies status before pagination', function () {
    createDashboardAssignments(11, 'Assigned');
    createDashboardAssignments(2, 'Draft');

    $data = app(AdminDashboardQuery::class)
        ->handle(Request::create('/dashboard', 'GET', [
            'status' => 'เริ่มกรอกข้อมูล',
            'page' => 1,
        ]))
        ->toViewData();

    expect($data['statusCounts']['ทั้งหมด'])->toBe(13)
        ->and($data['statusCounts']['มอบหมาย'])->toBe(11)
        ->and($data['statusCounts']['เริ่มกรอกข้อมูล'])->toBe(2)
        ->and($data['activeStatus'])->toBe('เริ่มกรอกข้อมูล')
        ->and($data['evaluations']->total())->toBe(2)
        ->and($data['evaluations'])->toHaveCount(2)
        ->and($data['evaluations']->lastPage())->toBe(1);
});

test('admin dashboard falls back to all for an unknown status', function () {
    createDashboardAssignments(2, 'Assigned');

    $data = app(AdminDashboardQuery::class)
        ->handle(Request::create('/dashboard', 'GET', ['status' => 'invalid']))
        ->toViewData();

    expect($data['activeStatus'])->toBe('all')
        ->and($data['evaluations']->total())->toBe(2);
});
```

- [ ] **Step 2: Run the focused query tests and verify they fail**

Run:

```powershell
php artisan test tests/Feature/AdminDashboardQueryTest.php
```

Expected: FAIL because the paginator still contains all 13 assignments and
`activeStatus` is missing.

- [ ] **Step 3: Preserve the base collection and paginate only the selected group**

In `AdminDashboardQuery::handle()`, immediately after the existing
`filterEvaluations()` and global sort:

```php
$activeStatus = $this->statusSummary->normalizeGroup($request->input('status'));
$statusCounts = $this->statusSummary->statusCounts($evaluations);
$listEvaluations = $this->statusSummary->filterByGroup($evaluations, $activeStatus);
```

Keep overview metrics, charts, follow-up data, and score calculations based on
`$evaluations`. Change only the paginator:

```php
$page = max((int) $request->input('page', 1), 1);
$perPage = 10;
$paginatedEvaluations = new LengthAwarePaginator(
    $listEvaluations->forPage($page, $perPage)->values(),
    $listEvaluations->count(),
    $perPage,
    $page,
    ['path' => $request->url(), 'query' => $request->query()]
);
```

Add the normalized group to returned view data:

```php
'activeStatus' => $activeStatus,
```

- [ ] **Step 4: Run query and dashboard regression tests**

Run:

```powershell
php artisan test tests/Feature/AdminDashboardQueryTest.php tests/Feature/DashboardTest.php
```

Expected: PASS.

- [ ] **Step 5: Commit server-side filtering**

```powershell
git add app/Support/AdminDashboardQuery.php tests/Feature/AdminDashboardQueryTest.php
git commit -m "fix: filter dashboard status before pagination"
```

### Task 3: One Server-Rendered Evaluation-List Fragment

**Files:**
- Create: `resources/views/dashboard/partials/index-evaluation-list.blade.php`
- Modify: `resources/views/dashboard/index.blade.php`
- Modify: `resources/views/dashboard/partials/index-list-header.blade.php`
- Modify: `resources/views/dashboard/partials/index-status-filters.blade.php`
- Modify: `resources/views/dashboard/partials/index-table-row.blade.php`
- Modify: `resources/views/dashboard/partials/index-pagination.blade.php`
- Modify: `resources/views/dashboard/partials/index-empty-state.blade.php`
- Modify: `resources/views/dashboard/partials/index-loading-state.blade.php`
- Modify: `app/Http/Controllers/DashboardController.php`
- Modify: `tests/Feature/DashboardTest.php`

**Interfaces:**
- Consumes view data: `evaluations`, `statusCounts`, and `activeStatus`
- Consumes request header: `X-Dashboard-Fragment: evaluation-list`
- Produces response header: `X-Dashboard-Fragment: evaluation-list`
- Produces DOM root: `#evaluation-list[data-evaluation-list]`

- [ ] **Step 1: Add failing feature tests for full and fragment responses**

Add this helper and the fragment tests to `DashboardTest.php`:

```php
function createDashboardScenario(array $statuses, string $titlePrefix = 'Plan'): array
{
    $role = Role::firstOrCreate(['name' => 'admin']);
    $admin = User::factory()->create();
    $admin->assignRole($role);
    $assignments = collect();

    foreach ($statuses as $index => $status) {
        $evaluatee = User::factory()->create();
        $reportData = ReportData::factory()->create([
            'report_title' => "{$titlePrefix} ".($index + 1),
        ]);
        $report = Reports::factory()->create([
            'report_data_id' => $reportData->id,
            'status' => $status,
        ]);
        $assignmentData = AssignmentData::factory()->create();
        $assignments->push(Assignments::factory()->create([
            'assignment_data_id' => $assignmentData->id,
            'report_id' => $report->id,
            'evaluatee_id' => $evaluatee->id,
        ]));
    }

    return [$admin, $assignments];
}
```

```php
test('dashboard status links preserve filters and select the server filtered list', function () {
    [$admin] = createDashboardScenario(['Assigned', 'Draft']);

    $response = $this->actingAs($admin)->get('/dashboard?year=2026&status='.urlencode('เริ่มกรอกข้อมูล'));

    $response->assertOk()
        ->assertSee('data-evaluation-list', false)
        ->assertSee('data-status-filter="เริ่มกรอกข้อมูล"', false)
        ->assertSee('aria-pressed="true"', false)
        ->assertSee('status='.urlencode('มอบหมาย'), false);
});

test('dashboard can return only the evaluation list fragment', function () {
    [$admin] = createDashboardScenario(['Assigned', 'Draft']);

    $response = $this->actingAs($admin)
        ->withHeader('X-Dashboard-Fragment', 'evaluation-list')
        ->get('/dashboard?status='.urlencode('เริ่มกรอกข้อมูล'));

    $response->assertOk()
        ->assertHeader('X-Dashboard-Fragment', 'evaluation-list')
        ->assertSee('id="evaluation-list"', false)
        ->assertDontSee('<html', false);
});

test('dashboard filtered pagination preserves status and search parameters', function () {
    [$admin] = createDashboardScenario(array_fill(0, 12, 'Draft'));

    $response = $this->actingAs($admin)->get('/dashboard?status='
        .urlencode('เริ่มกรอกข้อมูล').'&search=Plan');

    $response->assertOk()
        ->assertSee('status='.urlencode('เริ่มกรอกข้อมูล'), false)
        ->assertSee('search=Plan', false)
        ->assertSee('page=2', false);
});
```

- [ ] **Step 2: Run the feature tests and verify they fail**

Run:

```powershell
php artisan test tests/Feature/DashboardTest.php
```

Expected: FAIL because status badges are buttons without fallback URLs, no
fragment response exists, and the list is not a reusable partial.

- [ ] **Step 3: Extract the complete replaceable list partial**

Create `index-evaluation-list.blade.php` with the existing list markup. The
root and accessibility hooks are:

```blade
<section
    id="evaluation-list"
    data-evaluation-list
    aria-labelledby="evaluation-list-heading"
    class="overflow-hidden rounded-xl bg-white shadow-lg">
    @include('dashboard.partials.index-list-header')
    @include('dashboard.partials.index-status-filters')

    <div class="overflow-x-auto">
        <table id="userParticipant" class="min-w-full divide-y divide-gray-200">
            @include('dashboard.partials.index-table-head')
            <tbody id="userTableBody" class="divide-y divide-gray-200 bg-white">
                @forelse($evaluations as $evaluation)
                    @include('dashboard.partials.index-table-row', [
                        'evaluation' => $evaluation,
                        'rowNumber' => ($evaluations->firstItem() ?? 1) + $loop->index,
                    ])
                @empty
                    @include('dashboard.partials.index-table-empty-row')
                @endforelse
            </tbody>
        </table>
    </div>

    @include('dashboard.partials.index-loading-state')
    @include('dashboard.partials.index-empty-state')
    @include('dashboard.partials.index-pagination')
</section>
```

Move the existing `$progressMap` into the row partial and replace
`$loop->iteration` with `{{ $rowNumber }}`. At the top of the row partial,
inject the canonical service and delete the duplicate Blade
`$statusGroupMapping`:

```blade
@inject('dashboardStatusSummary', 'App\Support\AdminDashboardStatusSummary')
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
    $statusGroup = $dashboardStatusSummary->groupForStatus($status);
@endphp
```

In `index.blade.php`, replace the old list block with:

```blade
@include('dashboard.partials.index-evaluation-list')
```

Give the existing list heading a stable focus target in
`index-list-header.blade.php`:

```blade
<h3 id="evaluation-list-heading" tabindex="-1"
    class="mb-2 text-lg font-semibold text-gray-900 focus:outline-none sm:mb-0">
    ผลการประเมินรายบุคคล
</h3>
```

- [ ] **Step 4: Make badges and pagination server-navigable and authoritative**

Render each status as an anchor. Build its query by preserving current
parameters, replacing `status`, and removing `page`:

```blade
@php
    $isShowAll = $status === array_key_first($statusCounts);
    $filterValue = $isShowAll ? 'all' : $status;
    $query = request()->except('page', 'status');
    if (! $isShowAll) {
        $query['status'] = $status;
    }
    $href = request()->url().($query ? '?'.http_build_query($query) : '');
    $isActive = $activeStatus === $filterValue;
@endphp

<a href="{{ $href }}"
   data-status-filter="{{ $filterValue }}"
   aria-pressed="{{ $isActive ? 'true' : 'false' }}"
   class="dashboard-status-filter ...">
    {{ $status }} ({{ $count }})
</a>
```

Make the pagination wrapper expose `data-evaluation-pagination` and add an
`aria-live="polite"` result summary:

```blade
<div data-evaluation-pagination class="border-t border-gray-200 px-6 py-3">
    <p data-evaluation-result-summary aria-live="polite" class="sr-only">
        แสดง {{ $evaluations->firstItem() ?? 0 }} ถึง
        {{ $evaluations->lastItem() ?? 0 }} จาก {{ $evaluations->total() }} รายการ
    </p>
    {{ $evaluations->onEachSide(1)->links() }}
</div>
```

Keep the server-rendered empty row for a genuinely empty paginator. Remove the
old client-only empty-state visibility logic. Change the extra empty-state
container into a hidden request-error region with a normal-navigation recovery
link:

```blade
<div data-evaluation-request-error role="alert" class="hidden p-6 text-center">
    <p class="text-sm text-red-700">ไม่สามารถโหลดรายการได้ กรุณาลองอีกครั้ง</p>
    <a href="{{ request()->fullUrl() }}"
       class="mt-3 inline-flex rounded-lg border border-red-200 px-4 py-2 text-sm text-red-700">
        โหลดหน้าใหม่
    </a>
</div>
```

Add `data-evaluation-loading` and `aria-live="polite"` to the existing loading
state.

- [ ] **Step 5: Return the same partial from the controller**

Change `DashboardController::index()`:

```php
public function index(Request $request, AdminDashboardQuery $dashboardQuery)
{
    $viewData = $dashboardQuery->handle($request)->toViewData();

    if ($request->header('X-Dashboard-Fragment') === 'evaluation-list') {
        return response()
            ->view('dashboard.partials.index-evaluation-list', $viewData)
            ->header('X-Dashboard-Fragment', 'evaluation-list');
    }

    return view('dashboard.index', $viewData);
}
```

- [ ] **Step 6: Run the view and query tests**

Run:

```powershell
php artisan test tests/Feature/DashboardTest.php tests/Feature/AdminDashboardQueryTest.php tests/Unit/AdminDashboardStatusSummaryTest.php
```

Expected: PASS.

- [ ] **Step 7: Commit the server-rendered fragment**

```powershell
git add app/Http/Controllers/DashboardController.php app/Support/AdminDashboardStatusSummary.php resources/views/dashboard/index.blade.php resources/views/dashboard/partials/index-evaluation-list.blade.php resources/views/dashboard/partials/index-list-header.blade.php resources/views/dashboard/partials/index-status-filters.blade.php resources/views/dashboard/partials/index-table-row.blade.php resources/views/dashboard/partials/index-pagination.blade.php resources/views/dashboard/partials/index-empty-state.blade.php resources/views/dashboard/partials/index-loading-state.blade.php tests/Feature/DashboardTest.php
git commit -m "feat: render filterable dashboard list fragment"
```

### Task 4: Fragment Navigation Without Full-Page Refresh

**Files:**
- Modify: `resources/views/dashboard/partials/index-script.blade.php`
- Modify: `tests/Feature/DashboardTest.php`

**Interfaces:**
- Consumes DOM hooks: `[data-evaluation-list]`, `[data-status-filter]`, `[data-auto-search-form]`, `[data-evaluation-pagination]`
- Consumes server fragment response header: `X-Dashboard-Fragment: evaluation-list`
- Produces browser history entries containing `status`, `search`, and `page`

- [ ] **Step 1: Add failing markup-contract assertions**

Extend the dashboard hook test:

```php
$response->assertSee('data-evaluation-list', false)
    ->assertSee('X-Dashboard-Fragment', false)
    ->assertSee('window.history.pushState', false)
    ->assertSee("window.addEventListener('popstate'", false)
    ->assertDontSee('row.style.display', false)
    ->assertDontSee('clearStatusQuery', false);
```

- [ ] **Step 2: Run the contract test and verify it fails**

Run:

```powershell
php artisan test tests/Feature/DashboardTest.php --filter="dashboard table rows expose"
```

Expected: FAIL because the script still hides only current-page rows and does
not fetch fragments.

- [ ] **Step 3: Replace current-page filtering with delegated navigation**

Remove `tableRows`, `activeStatusFilter`, `clearStatusQuery()`,
`setActiveStatusButton()`, `applyDashboardFilters()`, and
`applyStatusFilter()` from `index-script.blade.php`. Add a small controller:

```js
let evaluationListRequest = null;

const setEvaluationListBusy = (list, busy) => {
    list.setAttribute('aria-busy', busy ? 'true' : 'false');
    list.querySelectorAll('button, input, select').forEach((control) => {
        control.disabled = busy;
    });
    list.querySelectorAll('a').forEach((link) => {
        link.classList.toggle('pointer-events-none', busy);
        link.setAttribute('aria-disabled', busy ? 'true' : 'false');
    });
    list.querySelector('[data-evaluation-loading]')?.classList.toggle('hidden', !busy);
};

const loadEvaluationList = async (url, { push = true, focus = null } = {}) => {
    const currentList = document.querySelector('[data-evaluation-list]');
    if (!currentList) return;

    evaluationListRequest?.abort();
    evaluationListRequest = new AbortController();
    setEvaluationListBusy(currentList, true);

    try {
        const response = await fetch(url, {
            headers: {
                'Accept': 'text/html',
                'X-Dashboard-Fragment': 'evaluation-list',
            },
            signal: evaluationListRequest.signal,
        });

        if (!response.ok || response.headers.get('X-Dashboard-Fragment') !== 'evaluation-list') {
            throw new Error(`Unexpected dashboard response: ${response.status}`);
        }

        const container = document.createElement('div');
        container.innerHTML = await response.text();
        const nextList = container.querySelector('[data-evaluation-list]');
        if (!nextList) throw new Error('Evaluation list fragment is missing');

        currentList.replaceWith(nextList);
        if (push) window.history.pushState({}, '', url);

        if (focus === 'heading') {
            document.getElementById('evaluation-list-heading')?.focus();
        } else if (focus) {
            document.querySelector(`[data-status-filter="${CSS.escape(focus)}"]`)?.focus();
        }
    } catch (error) {
        if (error.name === 'AbortError') return;
        setEvaluationListBusy(currentList, false);
        currentList.querySelector('[data-evaluation-request-error]')?.classList.remove('hidden');
    }
};
```

Use delegated handlers so replacement markup does not need rebinding:

```js
document.addEventListener('click', (event) => {
    const statusLink = event.target.closest('[data-status-filter]');
    const paginationLink = event.target.closest('[data-evaluation-pagination] a');
    const link = statusLink || paginationLink;
    if (!link || event.defaultPrevented || event.button !== 0
        || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;

    event.preventDefault();
    loadEvaluationList(link.href, {
        focus: statusLink ? statusLink.dataset.statusFilter : 'heading',
    });
});

document.addEventListener('submit', (event) => {
    const form = event.target.closest('[data-evaluation-list] [data-auto-search-form]');
    if (!form) return;

    event.preventDefault();
    const url = new URL(form.action, window.location.href);
    url.search = new URLSearchParams(new FormData(form)).toString();
    url.searchParams.delete('page');
    loadEvaluationList(url.toString(), { focus: 'heading' });
});

window.addEventListener('popstate', () => {
    loadEvaluationList(window.location.href, { push: false, focus: 'heading' });
});
```

Keep reviewer-modal behaviour functional after fragment replacement by
replacing the static `reviewerButtons.forEach(...)` binding with delegated
handling:

```js
document.addEventListener('click', (event) => {
    const button = event.target.closest('[data-reviewer-modal-button]');
    if (!button) return;

    try {
        openReviewerDialog(JSON.parse(button.dataset.reviewers || '[]'));
    } catch (error) {
        console.error('Failed to parse reviewer list', error);
    }
});
```

Preserve Ctrl/Cmd-click and other modified-click normal browser behaviour for
status and pagination links.

- [ ] **Step 4: Run focused tests**

Run:

```powershell
php artisan test tests/Feature/DashboardTest.php tests/Feature/AdminDashboardQueryTest.php tests/Unit/AdminDashboardStatusSummaryTest.php
```

Expected: PASS.

- [ ] **Step 5: Format changed PHP files and run the full suite**

Run:

```powershell
vendor/bin/pint app/Http/Controllers/DashboardController.php app/Support/AdminDashboardQuery.php app/Support/AdminDashboardStatusSummary.php tests/Feature/AdminDashboardQueryTest.php tests/Feature/DashboardTest.php tests/Unit/AdminDashboardStatusSummaryTest.php
php artisan test
```

Expected: Pint completes successfully and the complete test suite passes.

- [ ] **Step 6: Manually verify browser behaviour**

Run:

```powershell
php artisan serve
```

Verify on `/dashboard`:

1. selecting every status changes rows and paginator without a document reload;
2. the selected badge count equals the paginator total;
3. a status whose records were previously on another page now appears on page 1;
4. search and pagination preserve the selected status;
5. Back and Forward restore the list;
6. opening a pagination/status link in a new tab renders the full dashboard;
7. reviewer-detail links still open correctly after one or more fragment replacements;
8. forced request failure leaves the existing rows visible and shows the inline error.

- [ ] **Step 7: Commit the AJAX navigation**

```powershell
git add resources/views/dashboard/partials/index-script.blade.php tests/Feature/DashboardTest.php
git commit -m "feat: update dashboard filters without page refresh"
```

### Task 5: Final Regression Check

**Files:**
- Verify only; modify a test or implementation file only if a regression is found

**Interfaces:**
- Verifies all interfaces produced by Tasks 1–4

- [ ] **Step 1: Check the working tree and scoped diff**

Run:

```powershell
git status --short
git diff --check
git diff HEAD~4 -- app/Support/AdminDashboardStatusSummary.php app/Support/AdminDashboardQuery.php app/Http/Controllers/DashboardController.php resources/views/dashboard tests/Unit/AdminDashboardStatusSummaryTest.php tests/Feature/AdminDashboardQueryTest.php tests/Feature/DashboardTest.php
```

Expected: no whitespace errors; unrelated pre-existing changes remain
untouched.

- [ ] **Step 2: Run the complete automated suite once more**

Run:

```powershell
php artisan test
```

Expected: all tests pass.

- [ ] **Step 3: Confirm no verification-only commit is needed**

Run:

```powershell
git log -4 --oneline
```

Expected: the focused implementation commits from Tasks 1–4 are present. Do
not create an empty verification commit.
