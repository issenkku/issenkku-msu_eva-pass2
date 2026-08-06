<?php

use App\Exceptions\Subjects\SubjectWorkbookException;
use App\Services\Subjects\SubjectImportRowValidator;
use App\Services\Subjects\SubjectWorkbookReader;
use App\Support\Subjects\SubjectWorkbookSchema;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

function subjectWorkbook(array $rows, ?array $headers = null): string
{
    $path = tempnam(sys_get_temp_dir(), 'subjects-').'.xlsx';
    $book = new Spreadsheet;
    $sheet = $book->getActiveSheet();
    $sheet->setTitle(SubjectWorkbookSchema::DATA_SHEET);
    $sheet->fromArray($headers ?? SubjectWorkbookSchema::HEADERS, null, 'A1');
    foreach ($rows as $offset => $row) {
        $sheet->fromArray($row, null, 'A'.($offset + 2), true);
    }
    (new Xlsx($book))->save($path);
    $book->disconnectWorksheets();

    return $path;
}

test('reader returns ten-column normalized rows and ignores empty trailing rows', function () {
    $path = subjectWorkbook([
        [' cs101 ', 'Computer Science', '', 3, 2, 1, 0, 3, 2, 1],
        ['', '', '', '', '', '', '', '', '', ''],
    ]);
    $result = (new SubjectWorkbookReader(new SubjectImportRowValidator))->read($path);
    @unlink($path);

    expect($result->errors)->toBe([])
        ->and($result->rows)->toHaveCount(1)
        ->and($result->rows[0]->code)->toBe('CS101')
        ->and($result->rows[0]->attributes())->toMatchArray([
            'lecture_hours' => 3,
            'lab_hours' => 2,
            'self_study_hours' => 1,
        ]);
});

test('reader rejects an incorrect header contract', function () {
    $path = subjectWorkbook([], ['wrong']);

    expect(fn () => (new SubjectWorkbookReader(new SubjectImportRowValidator))->read($path))
        ->toThrow(SubjectWorkbookException::class);
    @unlink($path);
});

test('reader rejects the legacy seven column template with new template guidance', function () {
    $legacyHeaders = array_slice(SubjectWorkbookSchema::HEADERS, 0, 7);
    $path = subjectWorkbook([], $legacyHeaders);

    expect(fn () => (new SubjectWorkbookReader(new SubjectImportRowValidator))->read($path))
        ->toThrow(SubjectWorkbookException::class, 'Template รูปแบบเก่า');
    @unlink($path);
});

test('reader reports formulas and duplicate normalized codes with every row number', function () {
    $path = subjectWorkbook([
        ['FORM', 'Formula', '', '=1+2', 2, 1, 0, 3, 2, 1],
        ['cs101', 'First Name', '', 3, 2, 1, 0, 3, 2, 1],
        [' CS101 ', 'Second Name', '', 3, 2, 1, 0, 3, 2, 1],
    ]);
    $result = (new SubjectWorkbookReader(new SubjectImportRowValidator))->read($path);
    @unlink($path);

    expect(collect($result->errors)->pluck('message')->implode(' '))
        ->toContain('Formula')
        ->toContain('3, 4');
});

test('reader enforces a configured row limit and production remains limited to five thousand', function () {
    expect(SubjectWorkbookSchema::MAX_ROWS)->toBe(5000);
    $row = ['CS101', 'Name', '', 3, 2, 1, 0, 3, 2, 1];
    $path = subjectWorkbook([$row, $row, $row]);

    expect(fn () => (new SubjectWorkbookReader(new SubjectImportRowValidator, 2))->read($path))
        ->toThrow(SubjectWorkbookException::class, '2');
    @unlink($path);
});
