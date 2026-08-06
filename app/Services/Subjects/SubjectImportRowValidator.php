<?php

namespace App\Services\Subjects;

use App\Data\Subjects\SubjectImportError;
use App\Data\Subjects\SubjectImportRow;
use App\Support\Subjects\SubjectCode;
use App\Support\Subjects\SubjectName;
use App\Support\Subjects\SubjectWorkbookSchema;

final class SubjectImportRowValidator
{
    public function validate(int $excelRow, array $values): array
    {
        $code = SubjectCode::normalize($values[0] ?? null);
        $nameTh = SubjectName::normalize($values[1] ?? null);
        $nameEn = SubjectName::normalize($values[2] ?? null);
        $errors = [];

        if ($code === '' || mb_strlen($code) > 255) {
            $errors[] = $this->error($excelRow, $code, 0, $values[0] ?? null, 'ต้องกรอกรหัสไม่เกิน 255 ตัวอักษร');
        }
        if (! SubjectName::hasAtLeastOne($nameTh, $nameEn)) {
            $errors[] = new SubjectImportError(
                $excelRow,
                $code === '' ? null : $code,
                'ชื่อรายวิชา (ไทย/อังกฤษ)',
                null,
                SubjectName::REQUIRED_MESSAGE,
            );
        }

        $numbers = [];
        foreach (range(3, 9) as $column) {
            $numbers[$column] = $this->integer($values[$column] ?? null);
            if ($numbers[$column] === null) {
                $errors[] = $this->error($excelRow, $code, $column, $values[$column] ?? null, 'ต้องเป็นจำนวนเต็มตั้งแต่ 0 ขึ้นไป');
            }
        }

        if ($errors !== []) {
            return ['row' => null, 'errors' => $errors];
        }

        return ['row' => new SubjectImportRow(
            $excelRow, $code, $nameTh, $nameEn,
            $numbers[3], $numbers[4], $numbers[5], $numbers[6],
            $numbers[7], $numbers[8], $numbers[9],
        ), 'errors' => []];
    }

    private function integer(mixed $value): ?int
    {
        if ($value === null || (is_string($value) && trim($value) === '')) {
            return 0;
        }
        if (is_int($value) && $value >= 0) {
            return $value;
        }
        if (is_float($value) && $value >= 0 && floor($value) === $value) {
            return (int) $value;
        }
        if (is_string($value) && preg_match('/^\d+$/', trim($value)) === 1) {
            return (int) trim($value);
        }

        return null;
    }

    private function error(int $row, ?string $code, int $column, mixed $value, string $message): SubjectImportError
    {
        return new SubjectImportError($row, $code ?: null, SubjectWorkbookSchema::HEADERS[$column], $value, $message);
    }
}
