<?php

namespace App\Data\Subjects;

final readonly class SubjectWorkbookReadResult
{
    /** @param list<SubjectImportRow> $rows @param list<SubjectImportError> $errors */
    public function __construct(public array $rows, public array $errors) {}
}
