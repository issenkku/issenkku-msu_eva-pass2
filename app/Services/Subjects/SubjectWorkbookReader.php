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
    public function __construct(
        private SubjectImportRowValidator $validator,
        private int $maxRows = SubjectWorkbookSchema::MAX_ROWS,
    ) {}

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
                $sheet->rangeToArray('A1:J1', null, true, true, false)[0],
            );
            if ($sheet->getHighestDataColumn() === 'G' && array_slice($headers, 0, 7) === SubjectWorkbookSchema::LEGACY_HEADERS) {
                throw new SubjectWorkbookException('ไฟล์นี้เป็น Template รูปแบบเก่า กรุณาดาวน์โหลด Template ใหม่ที่มีคอลัมน์ชั่วโมงครบ 3 ช่อง');
            }
            if ($headers !== SubjectWorkbookSchema::HEADERS || $sheet->getHighestDataColumn() !== 'J') {
                throw new SubjectWorkbookException('หัวคอลัมน์ต้องตรงกับ Template ใหม่ทั้ง 10 คอลัมน์');
            }

            $rows = [];
            $errors = [];
            $codesByRow = [];
            $nonEmptyRows = 0;
            for ($excelRow = 2; $excelRow <= $sheet->getHighestDataRow(); $excelRow++) {
                $values = $sheet->rangeToArray("A{$excelRow}:J{$excelRow}", null, true, true, false)[0];
                if (collect($values)->every(fn ($value) => trim((string) $value) === '')) {
                    continue;
                }
                $nonEmptyRows++;
                if ($nonEmptyRows > $this->maxRows) {
                    throw new SubjectWorkbookException('ไฟล์ต้องมีข้อมูลไม่เกิน '.number_format($this->maxRows).' แถว');
                }
                $codesByRow[$excelRow] = SubjectCode::normalize($values[0] ?? null);

                $formulaFound = false;
                foreach (range('A', 'J') as $offset => $column) {
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
