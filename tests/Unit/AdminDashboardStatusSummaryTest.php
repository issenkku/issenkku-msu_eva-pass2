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
