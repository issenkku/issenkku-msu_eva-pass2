<?php

use App\Support\AdminDashboardQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

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
