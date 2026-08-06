<?php

use App\Data\Subjects\SubjectImportError;
use App\Data\Subjects\SubjectImportRow;
use App\Data\Subjects\SubjectWorkbookReadResult;
use App\Models\Subject;
use App\Services\Subjects\SubjectImportPreviewService;
use App\Services\Subjects\SubjectImportResultStore;
use App\Services\Subjects\SubjectImportSnapshotStore;
use Carbon\Carbon;

function previewSubjectPayload(array $overrides = []): array
{
    return array_replace([
        'code' => 'CS101', 'name_th' => 'เดิม', 'name_en' => null,
        'credits' => 3, 'lecture_credits' => 2, 'lab_credits' => 1,
        'self_study_credits' => 0, 'is_active' => true, 'sort_order' => 1,
    ], $overrides);
}

function importRow(string $code, string $name, int $row): SubjectImportRow
{
    return new SubjectImportRow($row, $code, $name, null, 3, 2, 1, 0, 0, 0, 0);
}

test('preview classifies new changed unchanged and errors in one batch', function () {
    Subject::create(previewSubjectPayload(['code' => 'CS101', 'name_th' => 'เดิม']));
    Subject::create(previewSubjectPayload(['code' => 'CS102', 'name_th' => 'เหมือนเดิม']));
    $read = new SubjectWorkbookReadResult([
        importRow('CS100', 'ใหม่', 2),
        importRow('CS101', 'เปลี่ยนแล้ว', 3),
        importRow('CS102', 'เหมือนเดิม', 4),
    ], [new SubjectImportError(5, 'BAD', 'หน่วยกิตรวม', 'x', 'ต้องเป็นจำนวนเต็ม')]);

    $preview = (new SubjectImportPreviewService)->build($read);

    expect($preview['new'])->toHaveCount(1)
        ->and($preview['changed'])->toHaveCount(1)
        ->and($preview['changed'][0]['diff']['name_th'])->toBe(['old' => 'เดิม', 'new' => 'เปลี่ยนแล้ว'])
        ->and($preview['unchanged'])->toHaveCount(1)
        ->and($preview['errors'])->toHaveCount(1);
});

test('snapshot tokens are owner scoped expiring and single use', function () {
    $store = app(SubjectImportSnapshotStore::class);
    $token = $store->put(10, 'subjects.xlsx', ['new' => [], 'changed' => [], 'unchanged' => [], 'errors' => []]);

    expect($store->getForUser($token, 11))->toBeNull()
        ->and($store->getForUser($token, 10)['filename'])->toBe('subjects.xlsx')
        ->and($store->claimForUser($token, 10))->not->toBeNull()
        ->and($store->claimForUser($token, 10))->toBeNull();
});

test('result tokens are owner scoped and pulled once', function () {
    $store = app(SubjectImportResultStore::class);
    $token = $store->put(10, ['created' => ['CS100'], 'updated' => [], 'skipped' => [], 'unchanged' => []]);

    expect($store->pullForUser($token, 11))->toBeNull()
        ->and($store->pullForUser($token, 10)['created'])->toBe(['CS100'])
        ->and($store->pullForUser($token, 10))->toBeNull();

    Carbon::setTestNow('2026-07-17 10:00:00');
    $expired = $store->put(10, ['created' => [], 'updated' => [], 'skipped' => [], 'unchanged' => []]);
    Carbon::setTestNow('2026-07-17 10:11:00');
    expect($store->pullForUser($expired, 10))->toBeNull();
    Carbon::setTestNow();
});

test('snapshot expires after thirty minutes', function () {
    Carbon::setTestNow('2026-07-17 10:00:00');
    $store = app(SubjectImportSnapshotStore::class);
    $token = $store->put(10, 'subjects.xlsx', ['new' => [], 'changed' => [], 'unchanged' => [], 'errors' => []]);
    Carbon::setTestNow('2026-07-17 10:31:00');

    expect($store->getForUser($token, 10))->toBeNull();
    Carbon::setTestNow();
});
