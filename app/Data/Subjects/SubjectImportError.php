<?php

namespace App\Data\Subjects;

final readonly class SubjectImportError
{
    public function __construct(
        public int $excelRow,
        public ?string $code,
        public string $column,
        public mixed $value,
        public string $message,
    ) {}

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
