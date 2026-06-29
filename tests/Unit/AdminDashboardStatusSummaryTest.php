<?php

use App\Support\AdminDashboardStatusSummary;

function admin_dashboard_assignment(?string $status = null, ?int $evaluateeId = null): object
{
    return (object) [
        'report' => (object) [
            'status' => $status,
        ],
        'evaluateeUser' => $evaluateeId === null ? null : (object) [
            'id' => $evaluateeId,
        ],
    ];
}

test('admin dashboard status summary counts the dashboard status groups', function () {
    $summary = new AdminDashboardStatusSummary();
    $evaluations = collect([
        admin_dashboard_assignment('Assigned'),
        admin_dashboard_assignment('Draft'),
        admin_dashboard_assignment('Pending'),
        admin_dashboard_assignment('Evaluator_draft'),
        admin_dashboard_assignment('Director_assigned'),
        admin_dashboard_assignment('Completed'),
    ]);

    expect($summary->statusCounts($evaluations))->toBe([
        'ทั้งหมด' => 6,
        'มอบหมาย' => 1,
        'เริ่มกรอกข้อมูล' => 1,
        'กำลังดำเนินการ' => 3,
        'ประเมินเสร็จสิ้น' => 1,
    ]);
});

test('admin dashboard status summary counts overview evaluatee groups', function () {
    $summary = new AdminDashboardStatusSummary();
    $evaluations = collect([
        admin_dashboard_assignment('Assigned', 1),
        admin_dashboard_assignment('Manager_assign', 1),
        admin_dashboard_assignment('Draft', 2),
        admin_dashboard_assignment('Assigned', 2),
        admin_dashboard_assignment('Completed', 3),
        admin_dashboard_assignment('Completed', 3),
        admin_dashboard_assignment('Pending', 4),
        admin_dashboard_assignment(null, 5),
    ]);

    expect($summary->overviewStatusCounts($evaluations))->toBe([
        'มอบหมาย' => 2,
        'เริ่มกรอกข้อมูล' => 1,
        'กำลังดำเนินการ' => 1,
        'ประเมินเสร็จสิ้น' => 1,
    ]);
});

test('admin dashboard status summary exposes named taxonomy counts', function () {
    $summary = new AdminDashboardStatusSummary();
    $evaluations = collect([
        admin_dashboard_assignment('Assigned'),
        admin_dashboard_assignment('Manager_assign'),
        admin_dashboard_assignment('Draft'),
        admin_dashboard_assignment('Pending'),
        admin_dashboard_assignment('Completed'),
    ]);

    expect($summary->notStartedStatusesCount($evaluations))->toBe(2);
    expect($summary->draftStatusesCount($evaluations))->toBe(1);
    expect($summary->inReviewStatusesCount($evaluations))->toBe(2);
    expect($summary->completedStatusesCount($evaluations))->toBe(1);
});
