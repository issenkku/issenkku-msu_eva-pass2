<?php

use App\Exports\Subjects\SubjectDataSheet;
use App\Models\Subject;
use App\Services\Subjects\SubjectWorkbookFactory;
use App\Support\Subjects\SubjectWorkbookSchema;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

test('blank template has data and instructions sheets without sample data', function () {
    $sheets = (new SubjectWorkbookFactory)->template()->sheets();
    $instructions = collect($sheets[1]->array())->flatten()->implode(' ');

    expect($sheets)->toHaveCount(2)
        ->and($sheets[0]->title())->toBe(SubjectWorkbookSchema::DATA_SHEET)
        ->and($sheets[0]->headings())->toBe(SubjectWorkbookSchema::HEADERS)
        ->and($sheets[0]->array())->toBe([])
        ->and($sheets[1]->title())->toBe(SubjectWorkbookSchema::INSTRUCTIONS_SHEET)
        ->and($instructions)->toContain('อย่างน้อยหนึ่งช่อง')
        ->and($instructions)->toContain('ทั้งสี่ค่าเป็นอิสระต่อกัน')
        ->and($instructions)->not->toContain('รวมต้องเท่ากับผลรวมสามช่องย่อย')
        ->and($instructions)->not->toContain('255 ตัวอักษร');
});

test('current workbook exports every editable field and excludes status and sort order', function () {
    $subject = new Subject([
        'code' => '=CS101', 'name_th' => 'ชื่อไทย', 'name_en' => 'English',
        'credits' => 3, 'lecture_credits' => 2, 'lab_credits' => 1,
        'self_study_credits' => 0, 'is_active' => false, 'sort_order' => 99,
    ]);
    $sheet = (new SubjectWorkbookFactory)->current(collect([$subject]))->sheets()[0];

    expect($sheet)->toBeInstanceOf(SubjectDataSheet::class)
        ->and($sheet->array())->toBe([['=CS101', 'ชื่อไทย', 'English', 3, 2, 1, 0]]);

    $book = new Spreadsheet;
    $cell = $book->getActiveSheet()->getCell('A1');
    $sheet->bindValue($cell, '=CS101');
    expect($cell->getDataType())->toBe(DataType::TYPE_STRING);
    $book->disconnectWorksheets();
});

test('current workbook preserves an English-only long name in its original column', function () {
    $longEnglishName = trim(str_repeat('Environmental Health ', 20));
    $subject = new Subject([
        'code' => 'ENV101', 'name_th' => null, 'name_en' => $longEnglishName,
        'credits' => 3, 'lecture_credits' => 2, 'lab_credits' => 1,
        'self_study_credits' => 0,
    ]);

    $sheet = (new SubjectWorkbookFactory)->current(collect([$subject]))->sheets()[0];

    expect($sheet->array())->toBe([
        ['ENV101', null, $longEnglishName, 3, 2, 1, 0],
    ]);
});
