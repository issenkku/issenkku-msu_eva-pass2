<?php

namespace App\Exports\Subjects;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

final readonly class SubjectWorkbookExport implements WithMultipleSheets
{
    public function __construct(private array $rows) {}

    public function sheets(): array
    {
        return [new SubjectDataSheet($this->rows), new SubjectInstructionsSheet];
    }
}
