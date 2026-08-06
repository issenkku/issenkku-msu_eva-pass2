<?php

use App\Exports\Subjects\SubjectDataSheet;
use App\Models\Subject;
use App\Services\Subjects\SubjectWorkbookFactory;
use App\Support\Subjects\SubjectWorkbookSchema;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

test('blank template has ten data columns and no sample data', function () {
    $sheets = (new SubjectWorkbookFactory)->template()->sheets();

    expect($sheets)->toHaveCount(2)
        ->and($sheets[0]->title())->toBe(SubjectWorkbookSchema::DATA_SHEET)
        ->and($sheets[0]->headings())->toBe(SubjectWorkbookSchema::HEADERS)
        ->and($sheets[0]->headings())->toHaveCount(10)
        ->and($sheets[0]->array())->toBe([])
        ->and($sheets[1]->title())->toBe(SubjectWorkbookSchema::INSTRUCTIONS_SHEET);
});

test('current workbook exports all seven raw numeric values and excludes status and sort order', function () {
    $subject = new Subject([
        'code' => '=CS101',
        'name_th' => 'Thai Name',
        'name_en' => 'English',
        'credits' => 3,
        'lecture_credits' => 2,
        'lab_credits' => 1,
        'self_study_credits' => 0,
        'lecture_hours' => 3,
        'lab_hours' => 2,
        'self_study_hours' => 1,
        'is_active' => false,
        'sort_order' => 99,
    ]);
    $sheet = (new SubjectWorkbookFactory)->current(collect([$subject]))->sheets()[0];

    expect($sheet)->toBeInstanceOf(SubjectDataSheet::class)
        ->and($sheet->array())->toBe([[
            '=CS101', 'Thai Name', 'English',
            3, 2, 1, 0,
            3, 2, 1,
        ]]);

    $book = new Spreadsheet;
    $cell = $book->getActiveSheet()->getCell('A1');
    $sheet->bindValue($cell, '=CS101');
    expect($cell->getDataType())->toBe(DataType::TYPE_STRING);
    $book->disconnectWorksheets();
});

test('current workbook preserves an English-only long name in its original column', function () {
    $longEnglishName = trim(str_repeat('Environmental Health ', 20));
    $subject = new Subject([
        'code' => 'ENV101',
        'name_th' => null,
        'name_en' => $longEnglishName,
        'credits' => 3,
        'lecture_credits' => 2,
        'lab_credits' => 1,
        'self_study_credits' => 0,
        'lecture_hours' => 3,
        'lab_hours' => 2,
        'self_study_hours' => 1,
    ]);

    $sheet = (new SubjectWorkbookFactory)->current(collect([$subject]))->sheets()[0];

    expect($sheet->array())->toBe([[
        'ENV101', null, $longEnglishName,
        3, 2, 1, 0,
        3, 2, 1,
    ]]);
});
