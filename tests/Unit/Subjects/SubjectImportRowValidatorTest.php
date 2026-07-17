<?php

use App\Services\Subjects\SubjectImportRowValidator;

function validSubjectImportValues(array $overrides = []): array
{
    return array_replace([' cs101 ', ' วิทยาการคอมพิวเตอร์ ', '', 3, 2, 1, 0], $overrides);
}

test('validator returns a normalized row', function () {
    $result = (new SubjectImportRowValidator)->validate(2, validSubjectImportValues());

    expect($result['errors'])->toBe([])
        ->and($result['row']->excelRow)->toBe(2)
        ->and($result['row']->code)->toBe('CS101')
        ->and($result['row']->nameTh)->toBe('วิทยาการคอมพิวเตอร์')
        ->and($result['row']->nameEn)->toBeNull()
        ->and($result['row']->attributes()['credits'])->toBe(3);
});

test('validator reports every invalid field in one pass', function () {
    $result = (new SubjectImportRowValidator)->validate(7, ['', '', null, 4, 2, 1, 0.5]);

    expect($result['row'])->toBeNull()
        ->and(collect($result['errors'])->pluck('column')->all())
        ->toContain('รหัสรายวิชา', 'ชื่อรายวิชา (ไทย)', 'หน่วยกิตศึกษาด้วยตนเอง');
});

test('validator rejects a total that does not equal the three component credits', function () {
    $result = (new SubjectImportRowValidator)->validate(8, validSubjectImportValues([3 => 4]));

    expect($result['row'])->toBeNull()
        ->and(collect($result['errors'])->pluck('column')->all())->toContain('หน่วยกิตรวม');
});

test('validator accepts unicode names without language restrictions', function () {
    $result = (new SubjectImportRowValidator)->validate(3, validSubjectImportValues([
        1 => '情報科学',
        2 => 'علوم الحاسوب',
    ]));

    expect($result['errors'])->toBe([])
        ->and($result['row']->nameTh)->toBe('情報科学')
        ->and($result['row']->nameEn)->toBe('علوم الحاسوب');
});
