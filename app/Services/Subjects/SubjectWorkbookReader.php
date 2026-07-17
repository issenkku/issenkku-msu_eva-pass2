<?php

namespace App\Services\Subjects;

use App\Data\Subjects\SubjectImportError;
use App\Data\Subjects\SubjectWorkbookReadResult;
use App\Exceptions\Subjects\SubjectWorkbookException;
use App\Support\Subjects\SubjectCode;
use App\Support\Subjects\SubjectWorkbookSchema;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Throwable;

final class SubjectWorkbookReader
{
    public function __construct(private SubjectImportRowValidator $validator) {}

    public function read(string $path): SubjectWorkbookReadResult
    {
        try {
            $reader = IOFactory::createReader('Xlsx');
            $reader->setReadDataOnly(false);
            $book = $reader->load($path);
        } catch (Throwable $exception) {
            throw new SubjectWorkbookException('ไม่สามารถเปิดไฟล์ Excel ได้', previous: $exception);
        }

        try {
            $sheet = $book->getSheetByName(SubjectWorkbookSchema::DATA_SHEET);
            if ($sheet === null) {
                throw new SubjectWorkbookException('ไม่พบชีต ข้อมูลรายวิชา');
            }

            $headers = array_map(
                fn (mixed $value): string => trim((string) $value),
                $sheet->rangeToArray('A1:G1', null, true, true, false)[0],
            );
            if ($headers !== SubjectWorkbookSchema::HEADERS || $sheet->getHighestDataColumn() !== 'G') {
                throw new SubjectWorkbookException('หัวคอลัมน์ต้องตรงกับ Template ทั้ง 7 คอลัมน์');
            }

            $rows = [];
            $errors = [];
            $codesByRow = [];
            $nonEmptyRows = 0;
            for ($excelRow = 2; $excelRow <= $sheet->getHighestDataRow(); $excelRow++) {
                $values = $sheet->rangeToArray("A{$excelRow}:G{$excelRow}", null, true, true, false)[0];
                if (collect($values)->every(fn ($value) => trim((string) $value) === '')) {
                    continue;
                }
                $nonEmptyRows++;
                if ($nonEmptyRows > SubjectWorkbookSchema::MAX_ROWS) {
                    throw new SubjectWorkbookException('ไฟล์ต้องมีข้อมูลไม่เกิน 5,000 แถว');
                }
                $codesByRow[$excelRow] = SubjectCode::normalize($values[0] ?? null);

                $formulaFound = false;
                foreach (range('A', 'G') as $offset => $column) {
                    $cell = $sheet->getCell("{$column}{$excelRow}");
                    if ($cell->getDataType() === DataType::TYPE_FORMULA) {
                        $formulaFound = true;
                        $errors[] = new SubjectImportError(
                            $excelRow,
                            trim((string) ($values[0] ?? '')) ?: null,
                            SubjectWorkbookSchema::HEADERS[$offset],
                            $cell->getValue(),
                            'ไม่อนุญาตให้ใช้ Excel Formula',
                        );
                    }
                }
                if ($formulaFound) {
                    continue;
                }

                $validated = $this->validator->validate($excelRow, $values);
                array_push($errors, ...$validated['errors']);
                if ($validated['row'] !== null) {
                    $rows[] = $validated['row'];
                }
            }

            foreach (collect($codesByRow)
                ->filter()
                ->groupBy(fn ($code) => $code, preserveKeys: true)
                ->filter(fn ($group) => $group->count() > 1) as $code => $duplicates) {
                $numbers = $duplicates->keys()->sort()->values()->all();
                foreach ($numbers as $duplicateRow) {
                    $errors[] = new SubjectImportError(
                        $duplicateRow,
                        $code,
                        'รหัสรายวิชา',
                        $code,
                        'รหัสซ้ำภายในไฟล์ที่แถว '.implode(', ', $numbers),
                    );
                }
            }

            return new SubjectWorkbookReadResult($rows, $errors);
        } finally {
            $book->disconnectWorksheets();
        }
    }
}
