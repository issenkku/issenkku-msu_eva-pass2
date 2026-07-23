<?php

use App\Models\AssignmentData;
use App\Models\Assignments;
use App\Models\ReportData;
use App\Models\Reports;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

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

test('guests are redirected to the login page', function () {
    $response = $this->get('/dashboard');
    $response->assertRedirect('/login');
});

test('authenticated users can visit the dashboard', function () {
    Role::create(['name' => 'admin']);
    $user = User::factory()->create();
    $user->assignRole('admin');
    $this->actingAs($user);

    $response = $this->get('/dashboard');
    $response->assertStatus(200);
});

test('dashboard table rows expose searchable report metadata and filter hooks', function () {
    Role::create(['name' => 'admin']);
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $evaluatee = User::factory()->create();
    $reportData = ReportData::factory()->create([
        'report_title' => 'Annual Performance Plan',
    ]);
    $report = Reports::factory()->create([
        'report_data_id' => $reportData->id,
        'status' => 'Completed',
    ]);
    $assignmentData = AssignmentData::factory()->create();
    Assignments::factory()->create([
        'assignment_data_id' => $assignmentData->id,
        'report_id' => $report->id,
        'evaluatee_id' => $evaluatee->id,
    ]);

    $response = $this->actingAs($admin)->get('/dashboard');

    $response->assertStatus(200)
        ->assertSee('role="search"', false)
        ->assertSee('data-auto-search-form', false)
        ->assertSee('data-auto-search-input', false)
        ->assertSee('aria-label=', false)
        ->assertSee('aria-controls="dashboardFilterPanel"', false)
        ->assertSee('aria-expanded="false"', false)
        ->assertSee('aria-hidden="true"', false)
        ->assertSee('data-reset-filters', false)
        ->assertDontSee('onclick="resetFilters()"', false)
        ->assertDontSee('data-overview-filter=', false)
        ->assertSee('role="radiogroup"', false)
        ->assertSee('aria-controls="year-filter-badge-panel"', false)
        ->assertSee('data-search-text=', false)
        ->assertSee('aria-pressed="true"', false)
        ->assertSee('data-evaluation-list', false)
        ->assertSee('X-Dashboard-Fragment', false)
        ->assertSee('window.history.pushState', false)
        ->assertSee("window.addEventListener('popstate'", false)
        ->assertSee('buildEvaluationSearchUrl', false)
        ->assertSee('event.stopImmediatePropagation()', false)
        ->assertSee('clearSearch: true', false)
        ->assertDontSee("loadEvaluationList(url.toString(), { focus: 'heading' });", false)
        ->assertDontSee('row.style.display', false)
        ->assertDontSee('clearStatusQuery', false)
        ->assertSee("'X-Dashboard-Fragment': 'dashboard-results'", false)
        ->assertSee('const loadDashboardResults = async', false)
        ->assertSee('const initializeOverviewChart =', false)
        ->assertSee('overviewChartInstance?.destroy()', false)
        ->assertSee("event.target.closest('#filterForm')", false)
        ->assertSee("loadDashboardResults(window.location.href, { push: false", false)
        ->assertSee("form.querySelector('[data-auto-submit-select]')", false)
        ->assertDontSee("document.getElementById('filterForm').submit()", false)
        ->assertSee('ค้นหารายการทั้งหมด: ชื่อ, ชื่องาน, ผู้ประเมิน...', false)
        ->assertSee('Annual Performance Plan', false);
});

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

test('dashboard asynchronous filter controller clears page and synchronizes history', function () {
    [$admin] = createDashboardScenario(['Assigned']);

    $response = $this->actingAs($admin)->get('/dashboard');

    $response->assertOk()
        ->assertSee("url.searchParams.delete('page')", false)
        ->assertSee("window.history.pushState({ dashboardFragment: 'dashboard-results' }, '', nextUrl)", false)
        ->assertSee('syncDashboardFilterState(nextUrl)', false)
        ->assertSee('syncDashboardFilterForm(nextUrl)', false)
        ->assertSee("field.value = ''", false)
        ->assertSee('data-dashboard-results-error', false)
        ->assertSee('data-dashboard-results-retry', false)
        ->assertSee("loading?.setAttribute('aria-hidden'", false)
        ->assertSee('lastDashboardResultsRequest', false);
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
