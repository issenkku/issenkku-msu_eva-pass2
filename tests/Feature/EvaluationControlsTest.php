<?php

use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;

function evaluationPaginator(array $items, string $path): LengthAwarePaginator
{
    return new LengthAwarePaginator($items, count($items), 10, 1, ['path' => url($path)]);
}

function evaluationAssignment(array $overrides = []): object
{
    $base = [
        'assignmentData' => (object) [
            'start_time' => Carbon::parse('2025-01-02 08:00:00'),
            'end_time' => Carbon::parse('2025-01-03 17:00:00'),
            'report' => (object) [
                'reportData' => (object) ['report_title' => 'รายงานตัวอย่าง'],
            ],
        ],
        'report' => (object) [
            'id' => 10,
            'status' => 'Completed',
            'reportData' => (object) ['report_title' => 'รายงานตัวอย่าง'],
            'updated_at' => Carbon::parse('2025-01-03 17:00:00'),
        ],
        'evaluateeUser' => (object) [
            'id' => 20,
            'name' => 'ผู้รับการประเมิน',
        ],
        'reviewerEntries' => collect([
            ['label' => 'กรรมการ', 'name' => 'Reviewer 1', 'position' => 'Director'],
            ['label' => 'กรรมการ', 'name' => 'Reviewer 2', 'position' => 'Manager'],
        ]),
        'created_at' => Carbon::parse('2025-01-01 09:00:00'),
        'evaluatee_id' => 20,
    ];

    return (object) array_replace_recursive($base, $overrides);
}

test('evaluation summary row renders reviewer data hooks without inline handlers', function () {
    $html = view('components.evaluation-summary-row', [
        'row' => [
            'index' => 1,
            'title' => 'รายงานตัวอย่าง',
            'is_recent' => false,
            'status_group' => 'completed',
            'start' => ['date' => '2 มกราคม 2568', 'time' => '08:00 น.'],
            'end' => ['date' => '3 มกราคม 2568', 'time' => '17:00 น.'],
            'primary_reviewer' => ['name' => 'Reviewer 1'],
            'additional_reviewer_count' => 1,
            'reviewers' => [
                ['label' => 'กรรมการ', 'name' => 'Reviewer 1', 'position' => 'Director'],
                ['label' => 'กรรมการ', 'name' => 'Reviewer 2', 'position' => 'Manager'],
            ],
            'status_classes' => 'bg-green-100 text-green-800',
            'status' => 'ประเมินเสร็จสิ้น',
            'action' => null,
            'url' => '#',
        ],
    ])->render();

    expect($html)
        ->toContain('data-evaluatee-reviewer-open')
        ->toContain('data-evaluatee-reviewers=')
        ->not->toContain('onclick=');
});

test('evaluation summary reviewer modal renders a data-hook close button', function () {
    $html = view('components.evaluation-summary-reviewer-modal')->render();

    expect($html)
        ->toContain('data-evaluatee-reviewer-close')
        ->not->toContain('onclick="window.closeEvaluateeReviewerModal()"');
});

test('director table renders reviewer data hooks without inline handlers', function () {
    $html = view('components.director-table', [
        'evaluations' => evaluationPaginator([evaluationAssignment()], '/director'),
        'statusCounts' => ['ประเมินเสร็จสิ้น' => 1],
        'years' => collect([2025]),
    ])->render();

    expect($html)
        ->toContain('data-director-reviewer-open')
        ->toContain('data-director-reviewer-close')
        ->not->toContain('onclick=\'window.openDirectorReviewerModal')
        ->not->toContain('onclick="window.closeDirectorReviewerModal()"');
});

test('manager table renders reviewer data hooks without inline handlers', function () {
    $html = view('components.manager-table', [
        'evaluations' => evaluationPaginator([evaluationAssignment()], '/manager'),
        'statusCounts' => ['ประเมินเสร็จสิ้น' => 1],
        'years' => collect([2025]),
    ])->render();

    expect($html)
        ->toContain('data-manager-reviewer-open')
        ->toContain('data-manager-reviewer-close')
        ->not->toContain('onclick=\'window.openManagerReviewerModal')
        ->not->toContain('onclick="window.closeManagerReviewerModal()"');
});

test('evaluation form action buttons render data-hook based draft submit trigger', function () {
    $html = view('partials.evaluation-form-action-buttons', [
        'readonly' => false,
        'backHref' => '/back',
        'draftStatus' => 'Draft',
        'submitText' => 'ส่งเพื่อยืนยัน',
    ])->render();

    expect($html)
        ->toContain('data-form-status-trigger')
        ->toContain('data-form-status="Draft"')
        ->not->toContain('onclick="setFormStatus(\'Draft\')"');
});

test('evaluatee evaluator comment partial renders all filled role comments', function () {
    $report = (object) [
        'comment' => 'legacy combined comment',
        'evaluator_comment' => 'evaluator says good',
        'director_comment' => null,
        'manager_comment' => 'manager says read books',
    ];

    $html = view('partials.evaluatee-evaluator-comment', [
        'report' => $report,
    ])->render();

    expect($html)
        ->toContain('evaluator says good')
        ->toContain('manager says read books')
        ->not->toContain('legacy combined comment');
});

test('evaluator dashboard form actions render data-hook based submit trigger', function () {
    $html = view('evaluator_dashboard.partials.form-actions')->render();

    expect($html)
        ->toContain('data-form-submit-confirm')
        ->not->toContain('onclick="confirmSubmit()"');
});

test('quality table renders data-hook based checkboxes', function () {
    $html = view('components.quality-table', [
        'qualityItems' => [
            [
                'evaluation_list_id' => 1,
                'is_evaluation_list' => true,
                'title' => 'รายการประเมิน',
            ],
            [
                'evaluation_list_id' => 1,
                'is_main' => true,
                'is_evaluation_list' => false,
                'main_criteria_id' => 10,
                'title' => 'หัวข้อหลัก',
            ],
            [
                'evaluation_list_id' => 1,
                'is_main' => false,
                'is_evaluation_list' => false,
                'main_criteria_id' => 10,
                'sub_criteria_id' => 100,
                'title' => 'หัวข้อย่อย',
                'num_score' => 5,
                'sequence' => 1,
            ],
        ],
        'readonly' => false,
        'evidenceMap' => [],
    ])->render();

    expect($html)
        ->toContain('data-quality-checkbox')
        ->not->toContain('onchange="handleQualityCheckboxChange(this)"');
});

test('workload unsaved back confirmation uses system modal instead of native confirm', function () {
    $actionsHtml = view('evaluatee.partials.workload-actions', [
        'readonly' => false,
        'reportId' => 10,
        'workloadTotalScore' => 3,
        'savedWorkloadScoreC' => null,
        'quantitySubCriteriaId' => 5,
        'importablePreviousReports' => collect(),
    ])->render();
    $scriptHtml = view('evaluatee.partials.workload-script-save-reminder')->render();

    expect($actionsHtml)
        ->toContain('data-workload-unsaved-modal')
        ->toContain('data-workload-unsaved-confirm')
        ->toContain('ยังไม่ได้บันทึกภาระงาน')
        ->toContain('อยู่หน้านี้ต่อ')
        ->toContain('ย้อนกลับ');

    expect($scriptHtml)
        ->toContain('data-workload-unsaved-modal')
        ->not->toContain('window.confirm')
        ->not->toContain('confirm(');
});

test('workload entry modal keeps its actions visible within the viewport', function () {
    $modalHtml = view('evaluatee.partials.workload-entry-modal', [
        'readonly' => false,
        'reportId' => 10,
        'workloadModal' => [
            'requires_evidence' => false,
            'requires_subject' => false,
            'workload_item_options' => [],
            'subjects' => [],
            'forms' => [],
        ],
    ])->render();
    $stylesHtml = view('evaluatee.partials.workload-styles')->render();

    expect($modalHtml)
        ->toContain('modal-dialog-scrollable')
        ->toContain('modal-fullscreen-sm-down')
        ->toContain('workload-modal-dialog')
        ->toContain('workload-modal-form');

    expect($stylesHtml)
        ->toContain('#workloadAddModal .workload-modal-dialog')
        ->toContain('height: calc(100vh - 2rem)')
        ->toContain('height: calc(100dvh - 2rem)')
        ->toContain('#workloadAddModal .workload-modal-form')
        ->toContain('flex-shrink: 0')
        ->toContain('overflow-y: auto')
        ->toContain('overscroll-behavior: contain')
        ->toContain('@media (max-width: 575.98px)');
});

test('reviewer list modals keep headers visible while their lists scroll', function () {
    $modalHtml = [
        view('dashboard.partials.index-reviewer-modal')->render(),
        view('components.evaluation-summary-reviewer-modal')->render(),
        view('components.director-table', [
            'evaluations' => evaluationPaginator([evaluationAssignment()], '/director'),
            'statusCounts' => [],
            'years' => collect([2025]),
        ])->render(),
        view('components.manager-table', [
            'evaluations' => evaluationPaginator([evaluationAssignment()], '/manager'),
            'statusCounts' => [],
            'years' => collect([2025]),
        ])->render(),
    ];

    foreach ($modalHtml as $html) {
        expect($html)
            ->toContain('data-reviewer-list-modal-panel')
            ->toContain('data-reviewer-list-modal-header')
            ->toContain('data-reviewer-list-modal-body')
            ->toContain('max-h-[calc(100dvh-2rem)]')
            ->toContain('flex-none')
            ->toContain('min-h-0 flex-1')
            ->toContain('overflow-y-auto overscroll-contain');
    }
});
