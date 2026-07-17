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

test('reader returns normalized rows and ignores empty trailing rows', function () {
    $path = subjectWorkbook([[' cs101 ', 'วิทยาการคอมพิวเตอร์', '', 3, 2, 1, 0], ['', '', '', '', '', '', '']]);
    $result = (new SubjectWorkbookReader(new SubjectImportRowValidator))->read($path);
    @unlink($path);

    expect($result->errors)->toBe([])
        ->and($result->rows)->toHaveCount(1)
        ->and($result->rows[0]->code)->toBe('CS101');
});

test('reader rejects an incorrect header contract', function () {
    $path = subjectWorkbook([], ['wrong']);

    expect(fn () => (new SubjectWorkbookReader(new SubjectImportRowValidator))->read($path))
        ->toThrow(SubjectWorkbookException::class, 'หัวคอลัมน์');
    @unlink($path);
});

test('reader reports formulas and duplicate normalized codes with every row number', function () {
    $path = subjectWorkbook([
        ['FORM', 'สูตร', '', '=1+2', 2, 1, 0],
        ['cs101', 'ชื่อหนึ่ง', '', 3, 2, 1, 0],
        [' CS101 ', 'ชื่อสอง', '', 3, 2, 1, 0],
    ]);
    $result = (new SubjectWorkbookReader(new SubjectImportRowValidator))->read($path);
    @unlink($path);

    expect(collect($result->errors)->pluck('message')->implode(' '))
        ->toContain('Formula')
        ->toContain('แถว 3, 4');
});

test('reader enforces a configured row limit and production remains limited to five thousand', function () {
    expect(SubjectWorkbookSchema::MAX_ROWS)->toBe(5000);
    $row = ['CS101', 'ชื่อ', '', 3, 2, 1, 0];
    $path = subjectWorkbook([$row, $row, $row]);

    expect(fn () => (new SubjectWorkbookReader(new SubjectImportRowValidator, 2))->read($path))
        ->toThrow(SubjectWorkbookException::class, '2');
    @unlink($path);
});
