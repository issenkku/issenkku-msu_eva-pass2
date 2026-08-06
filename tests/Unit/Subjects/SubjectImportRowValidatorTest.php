<?php

use App\Services\Subjects\SubjectImportRowValidator;
use App\Support\Subjects\SubjectWorkbookSchema;

function validSubjectImportValues(array $overrides = []): array
{
    return array_replace([
        ' cs101 ', 'Computer Science', '',
        3, 2, 1, 0,
        3, 2, 1,
    ], $overrides);
}

test('validator returns a normalized row with independent credits and hours', function () {
    $result = (new SubjectImportRowValidator)->validate(2, validSubjectImportValues());

    expect($result['errors'])->toBe([])
        ->and($result['row']->excelRow)->toBe(2)
        ->and($result['row']->code)->toBe('CS101')
        ->and($result['row']->nameTh)->toBe('Computer Science')
        ->and($result['row']->nameEn)->toBeNull()
        ->and($result['row']->attributes())->toMatchArray([
            'credits' => 3,
            'lecture_credits' => 2,
            'lab_credits' => 1,
            'self_study_credits' => 0,
            'lecture_hours' => 3,
            'lab_hours' => 2,
            'self_study_hours' => 1,
        ]);
});

test('validator reports every invalid numeric field in one pass', function () {
    $result = (new SubjectImportRowValidator)->validate(
        7,
        ['', '', null, 4, 2, 1, 0.5, -1, 1.5, 'many'],
    );

    expect($result['row'])->toBeNull()
        ->and(collect($result['errors'])->pluck('column')->all())
        ->toContain(
            SubjectWorkbookSchema::HEADERS[6],
            SubjectWorkbookSchema::HEADERS[7],
            SubjectWorkbookSchema::HEADERS[8],
            SubjectWorkbookSchema::HEADERS[9],
        );
});

test('validator defaults blank numeric cells to zero', function () {
    $result = (new SubjectImportRowValidator)->validate(9, validSubjectImportValues([
        3 => '', 4 => null, 5 => ' ', 6 => '', 7 => null, 8 => ' ', 9 => '',
    ]));

    expect($result['errors'])->toBe([])
        ->and($result['row']->attributes())->toMatchArray([
            'credits' => 0,
            'lecture_credits' => 0,
            'lab_credits' => 0,
            'self_study_credits' => 0,
            'lecture_hours' => 0,
            'lab_hours' => 0,
            'self_study_hours' => 0,
        ]);
});

test('validator accepts unicode names without language restrictions', function () {
    $result = (new SubjectImportRowValidator)->validate(3, validSubjectImportValues([
        1 => '情境教学',
        2 => 'ชื่อ วิชา',
    ]));

    expect($result['errors'])->toBe([])
        ->and($result['row']->nameTh)->toBe('情境教学')
        ->and($result['row']->nameEn)->toBe('ชื่อ วิชา');
});

test('validator accepts an English-only name longer than 255 characters', function () {
    $longEnglishName = trim(str_repeat('Environmental Health ', 20));
    $result = (new SubjectImportRowValidator)->validate(2, validSubjectImportValues([
        1 => ' ',
        2 => " {$longEnglishName} ",
    ]));

    expect($result['errors'])->toBe([])
        ->and($result['row']->nameTh)->toBeNull()
        ->and($result['row']->nameEn)->toBe($longEnglishName);
});

test('validator reports one combined error when both names are blank', function () {
    $result = (new SubjectImportRowValidator)->validate(7, validSubjectImportValues([
        1 => ' ',
        2 => null,
    ]));

    expect($result['row'])->toBeNull()
        ->and($result['errors'])->toHaveCount(1);
});
