<?php

use App\Models\Subject;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;

uses(RefreshDatabase::class);

test('subjects default all hour fields to zero', function () {
    $subject = Subject::create([
        'code' => 'ZERO-HOURS',
        'name_th' => 'Zero Hours',
        'credits' => 3,
        'lecture_credits' => 2,
        'lab_credits' => 1,
        'self_study_credits' => 0,
    ]);

    expect($subject->fresh()->only(['lecture_hours', 'lab_hours', 'self_study_hours']))
        ->toBe([
            'lecture_hours' => 0,
            'lab_hours' => 0,
            'self_study_hours' => 0,
        ]);
});

test('component display values use the complete hour set when any hour is positive', function () {
    $subject = new Subject([
        'lecture_credits' => 1,
        'lab_credits' => 1,
        'self_study_credits' => 1,
        'lecture_hours' => 2,
        'lab_hours' => 0,
        'self_study_hours' => 0,
    ]);

    expect($subject->display_component_values)->toBe([2, 0, 0]);
});

test('component display values use the complete credit set when every hour is zero', function () {
    $subject = new Subject([
        'lecture_credits' => 3,
        'lab_credits' => 0,
        'self_study_credits' => 1,
        'lecture_hours' => 0,
        'lab_hours' => 0,
        'self_study_hours' => 0,
    ]);

    expect($subject->display_component_values)->toBe([3, 0, 1]);
});

test('subject table renderers use the shared component display values', function () {
    $subject = new Subject([
        'code' => 'DISPLAY101',
        'name_th' => 'Display Hours',
        'credits' => 3,
        'lecture_credits' => 1,
        'lab_credits' => 1,
        'self_study_credits' => 1,
        'lecture_hours' => 2,
        'lab_hours' => 0,
        'self_study_hours' => 0,
    ]);
    $subject->id = 101;

    $rowHtml = view('subjects.partials.index-table-row', [
        'subject' => $subject,
        'sequence' => 1,
    ])->render();
    $tableHtml = view('subjects.partials.index-table-section', [
        'subjects' => new LengthAwarePaginator([$subject], 1, 10, 1, ['path' => url('/subjects')]),
    ])->render();

    expect($rowHtml)->toContain('( 2 / 0 / 0)')
        ->not->toContain('( 1 / 1 / 1)')
        ->and($tableHtml)->toContain('( 2 / 0 / 0)')
        ->not->toContain('( 1 / 1 / 1)');
});

test('subject table renderers expose a read only detail modal action', function () {
    expect(view()->exists('subjects.partials.detail-modal'))->toBeTrue();

    $subject = new Subject([
        'code' => '1499202-3',
        'name_th' => 'อนามัยสิ่งแวดล้อม',
        'name_en' => 'Environmental Health',
        'credits' => 3,
        'lecture_credits' => 3,
        'lab_credits' => 0,
        'self_study_credits' => 6,
        'lecture_hours' => 0,
        'lab_hours' => 2,
        'self_study_hours' => 0,
        'is_active' => true,
    ]);
    $subject->id = 516;

    $rowHtml = view('subjects.partials.index-table-row', [
        'subject' => $subject,
        'sequence' => 1,
    ])->render();
    $tableHtml = view('subjects.partials.index-table-section', [
        'subjects' => new LengthAwarePaginator([$subject], 1, 10, 1, ['path' => url('/subjects')]),
    ])->render();
    $modalHtml = view('subjects.partials.detail-modal')->render();

    foreach ([$rowHtml, $tableHtml] as $html) {
        expect($html)
            ->toContain('data-role="subject-detail-trigger"')
            ->toContain('data-display-source="hours"')
            ->toContain('data-display-lecture="0"')
            ->toContain('data-display-lab="2"')
            ->toContain('data-display-self-study="0"')
            ->toMatch('/รายละเอียด.*แก้ไข.*ลบ/s');
    }

    expect($modalHtml)
        ->toContain('id="subjectDetailModal"')
        ->toContain('รายละเอียดรายวิชา')
        ->toContain('ข้อมูลหน่วยกิต')
        ->toContain('ข้อมูลชั่วโมง')
        ->toContain('data-subject-detail-source')
        ->not->toContain('<form')
        ->not->toContain('type="submit"');
});
