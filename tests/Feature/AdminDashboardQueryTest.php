<?php

use App\Models\AssignmentData;
use App\Models\Assignments;
use App\Models\ReportData;
use App\Models\Reports;
use App\Models\User;
use App\Support\AdminDashboardQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

function createDashboardAssignments(
    int $count,
    string $status,
    string $titlePrefix = 'Plan',
    ?string $startTime = null,
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
        $assignmentData = AssignmentData::factory()->create(
            $startTime ? ['start_time' => $startTime] : []
        );

        Assignments::factory()->create([
            'assignment_data_id' => $assignmentData->id,
            'report_id' => $report->id,
            'evaluatee_id' => $evaluatee->id,
        ]);
    }
}

test('admin dashboard query returns the existing dashboard view data contract', function () {
    $query = app(AdminDashboardQuery::class);
    $request = Request::create('/dashboard', 'GET');

    $viewData = $query->handle($request)->toViewData();

    expect($viewData)->toHaveKeys([
        'averageScore',
        'statusCounts',
        'evaluations',
        'scatterData',
        'chartData',
        'totalEvaluations',
        'totalEvaluatees',
        'totalUsers',
        'completedEvaluatees',
        'completedEvaluateesPercent',
        'notStartedEvaluatees',
        'startedEvaluatees',
        'startedEvaluateesPercent',
        'overviewEvaluateeStatusCounts',
        'overviewCompletedEvaluatees',
        'overviewCompletedEvaluateesPercent',
        'notStartedCount',
        'draftCount',
        'inReviewCount',
        'completedCount',
        'startedCount',
        'progressPercent',
        'startedPercent',
        'followUpEvaluations',
        'statusLabels',
        'statusColors',
        'departments',
        'positions',
        'reports',
        'evaluationPeriod',
        'years',
    ]);

    expect(array_keys($viewData['statusCounts']))->toBe([
        'ทั้งหมด',
        'มอบหมาย',
        'เริ่มกรอกข้อมูล',
        'กำลังดำเนินการ',
        'ประเมินเสร็จสิ้น',
    ]);

    expect(array_keys($viewData['overviewEvaluateeStatusCounts']))->toBe([
        'มอบหมาย',
        'เริ่มกรอกข้อมูล',
        'กำลังดำเนินการ',
        'ประเมินเสร็จสิ้น',
    ]);

    expect($viewData['evaluations'])->toBeInstanceOf(LengthAwarePaginator::class)
        ->and($viewData['evaluations']->perPage())->toBe(10)
        ->and($viewData['evaluations']->total())->toBe(0)
        ->and($viewData['averageScore'])->toBe(0)
        ->and($viewData['totalEvaluations'])->toBe(0)
        ->and($viewData['totalEvaluatees'])->toBe(0)
        ->and($viewData['totalUsers'])->toBe(0)
        ->and($viewData['completedEvaluatees'])->toBe(0)
        ->and($viewData['completedEvaluateesPercent'])->toBe(0)
        ->and($viewData['startedEvaluateesPercent'])->toBe(0)
        ->and($viewData['overviewCompletedEvaluatees'])->toBe(0)
        ->and($viewData['overviewCompletedEvaluateesPercent'])->toBe(0)
        ->and($viewData['notStartedCount'])->toBe(0)
        ->and($viewData['draftCount'])->toBe(0)
        ->and($viewData['inReviewCount'])->toBe(0)
        ->and($viewData['completedCount'])->toBe(0)
        ->and($viewData['startedCount'])->toBe(0)
        ->and($viewData['progressPercent'])->toBe(0)
        ->and($viewData['startedPercent'])->toBe(0)
        ->and($viewData['reports'])->toBe([]);

    expect($viewData['evaluationPeriod'])->toBe('All Periods');
});

test('admin dashboard query stays within the empty dashboard query budget', function () {
    DB::flushQueryLog();
    DB::enableQueryLog();
    $queryCount = 0;
    $thrownException = null;

    try {
        $query = app(AdminDashboardQuery::class);
        $request = Request::create('/dashboard', 'GET');

        $query->handle($request)->toViewData();

        $queryCount = count(DB::getQueryLog());
    } catch (Throwable $exception) {
        $thrownException = $exception;
    } finally {
        DB::disableQueryLog();
    }

    if ($thrownException) {
        throw $thrownException;
    }

    expect($queryCount)->toBeLessThanOrEqual(8);
});

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

test('admin dashboard keeps every available year after selecting one year', function () {
    createDashboardAssignments(1, 'Assigned', 'Previous year', '2026-01-01');
    createDashboardAssignments(1, 'Assigned', 'Next year', '2027-01-01');

    $data = app(AdminDashboardQuery::class)
        ->handle(Request::create('/dashboard', 'GET', ['year' => 2026]))
        ->toViewData();

    expect($data['evaluations']->total())->toBe(1)
        ->and($data['years']->values()->all())->toBe([2027, 2026]);
});
