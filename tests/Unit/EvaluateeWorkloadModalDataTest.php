<?php

use App\Models\Subject;
use App\Support\EvaluateeWorkloadModalData;
use Tests\TestCase;

uses(TestCase::class);

test('workload modal data exposes raw credits and the shared component display values', function () {
    $subject = new Subject([
        'code' => 'MODAL101',
        'name_th' => 'Modal Hours',
        'credits' => 3,
        'lecture_credits' => 1,
        'lab_credits' => 1,
        'self_study_credits' => 1,
        'lecture_hours' => 2,
        'lab_hours' => 0,
        'self_study_hours' => 0,
    ]);
    $subject->id = 101;

    $data = EvaluateeWorkloadModalData::build(null, collect(), collect([$subject]));
    $subjectView = $data['subjects']->first();

    expect($subjectView)->toMatchArray([
        'lecture_credits' => 1,
        'lab_credits' => 1,
        'self_study_credits' => 1,
        'lecture_hours' => 2,
        'lab_hours' => 0,
        'self_study_hours' => 0,
        'display_component_values' => [2, 0, 0],
    ]);
});

test('workload subject picker renders the complete display tuple without changing credit data attributes', function () {
    $subjectView = [
        'id' => 101,
        'code' => 'MODAL101',
        'display_name' => 'Modal Hours',
        'secondary_name' => null,
        'credits' => 3,
        'lecture_credits' => 1,
        'lab_credits' => 1,
        'self_study_credits' => 1,
        'display_component_values' => [2, 0, 0],
        'search' => 'modal101 modal hours',
    ];

    $html = view('evaluatee.partials.workload-entry-modal-subject-section', [
        'workloadModal' => ['subjects' => [$subjectView]],
    ])->render();
    $decodedHtml = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');

    expect($html)->toContain('data-lab-credits="1"')
        ->and($decodedHtml)->toContain('บ 2 / ป 0 / ศ 0')
        ->not->toContain('บ 1 / ป 1 / ศ 1');
});
