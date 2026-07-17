<?php

namespace App\Support\Subjects;

final class SubjectCode
{
    public static function normalize(mixed $value): string
    {
        return mb_strtoupper(trim((string) $value), 'UTF-8');
    }
}
