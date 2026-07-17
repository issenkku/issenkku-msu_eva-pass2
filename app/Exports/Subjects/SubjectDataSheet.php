<?php

namespace App\Exports\Subjects;

use App\Support\Subjects\SubjectWorkbookSchema;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

final class SubjectDataSheet extends DefaultValueBinder implements FromArray, WithColumnWidths, WithCustomValueBinder, WithHeadings, WithStyles, WithTitle
{
    public function __construct(private array $rows) {}

    public function array(): array
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return SubjectWorkbookSchema::HEADERS;
    }

    public function title(): string
    {
        return SubjectWorkbookSchema::DATA_SHEET;
    }

    public function columnWidths(): array
    {
        return ['A' => 18, 'B' => 34, 'C' => 34, 'D' => 14, 'E' => 18, 'F' => 18, 'G' => 26];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->freezePane('A2');
        $sheet->setAutoFilter('A1:G1');

        return [1 => ['font' => ['bold' => true]]];
    }

    public function bindValue(Cell $cell, $value): bool
    {
        if (is_string($value)) {
            $cell->setValueExplicit($value, DataType::TYPE_STRING);

            return true;
        }

        return parent::bindValue($cell, $value);
    }
}
