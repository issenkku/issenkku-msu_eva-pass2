<?php

use App\Data\Subjects\SubjectImportRow;
use App\Data\Subjects\SubjectWorkbookReadResult;
use App\Exceptions\Subjects\StaleSubjectImportException;
use App\Models\Subject;
use App\Models\User;
use App\Services\Subjects\SubjectImportCommitter;
use App\Services\Subjects\SubjectImportPreviewService;
use Spatie\Activitylog\Models\Activity;

function committerSubject(array $overrides = []): Subject
{
    return Subject::create(array_replace([
        'code' => 'CS101', 'name_th' => 'เดิม', 'name_en' => 'Old',
        'credits' => 3, 'lecture_credits' => 2, 'lab_credits' => 1,
        'self_study_credits' => 0, 'is_active' => false, 'sort_order' => 5,
    ], $overrides));
}

test('committer creates new rows updates selected rows and preserves status and order', function () {
    $existing = committerSubject();
    $preview = (new SubjectImportPreviewService)->build(new SubjectWorkbookReadResult([
        new SubjectImportRow(2, 'CS100', 'ใหม่', null, 3, 2, 1, 0),
        new SubjectImportRow(3, 'CS101', 'เปลี่ยน', null, 3, 2, 1, 0),
    ], []));
    $result = (new SubjectImportCommitter)->commit(
        ['filename' => 'subjects.xlsx', 'token_hash' => 'hashed-token', 'preview' => $preview],
        ['CS101'],
        User::factory()->create(),
    );

    expect($result)->toMatchArray([
        'created' => ['CS100'], 'updated' => ['CS101'], 'skipped' => [], 'unchanged' => [],
    ])
        ->and($existing->fresh()->name_th)->toBe('เปลี่ยน')
        ->and($existing->fresh()->name_en)->toBeNull()
        ->and($existing->fresh()->is_active)->toBeFalse()
        ->and($existing->fresh()->sort_order)->toBe(5)
        ->and(Subject::where('code', 'CS100')->first()->is_active)->toBeTrue()
        ->and(Subject::where('code', 'CS100')->first()->sort_order)->toBe(6)
        ->and(Activity::where('description', 'นำเข้าข้อมูลรายวิชา')->count())->toBe(1)
        ->and(Activity::where('description', 'นำเข้าข้อมูลรายวิชา')->first()->properties->get('preview_token_hash'))->toBe('hashed-token');
});

test('committer leaves unselected changed rows untouched', function () {
    $existing = committerSubject();
    $preview = (new SubjectImportPreviewService)->build(new SubjectWorkbookReadResult([
        new SubjectImportRow(2, 'CS101', 'ไม่เลือก', null, 3, 2, 1, 0),
    ], []));
    $result = (new SubjectImportCommitter)->commit(
        ['filename' => 'subjects.xlsx', 'token_hash' => 'hashed-token', 'preview' => $preview], [], User::factory()->create(),
    );

    expect($existing->fresh()->name_th)->toBe('เดิม')
        ->and($result['skipped'])->toBe(['CS101']);
});

test('stale data rolls back the entire import before creating new rows', function () {
    $existing = committerSubject();
    $preview = (new SubjectImportPreviewService)->build(new SubjectWorkbookReadResult([
        new SubjectImportRow(2, 'CS100', 'ใหม่', null, 3, 2, 1, 0),
        new SubjectImportRow(3, 'CS101', 'จากไฟล์', null, 3, 2, 1, 0),
    ], []));
    $existing->update(['name_th' => 'แก้โดยผู้ใช้อื่น']);

    expect(fn () => (new SubjectImportCommitter)->commit(
        ['filename' => 'subjects.xlsx', 'token_hash' => 'hashed-token', 'preview' => $preview], ['CS101'], User::factory()->create(),
    ))->toThrow(StaleSubjectImportException::class);

    expect(Subject::where('code', 'CS100')->exists())->toBeFalse();
});
