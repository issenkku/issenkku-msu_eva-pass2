<?php

namespace App\Support\Subjects;

final class SubjectName
{
    public const REQUIRED_MESSAGE = 'กรุณากรอกชื่อรายวิชาภาษาไทยหรือภาษาอังกฤษอย่างน้อยหนึ่งช่อง';

    public static function normalize(mixed $value): ?string
    {
        if ($value !== null && ! is_string($value) && ! is_numeric($value)) {
            return null;
        }

        $normalized = trim((string) ($value ?? ''));

        return $normalized === '' ? null : $normalized;
    }

    public static function hasAtLeastOne(mixed $nameTh, mixed $nameEn): bool
    {
        return self::normalize($nameTh) !== null || self::normalize($nameEn) !== null;
    }

    public static function display(mixed $nameTh, mixed $nameEn): string
    {
        return self::normalize($nameTh) ?? self::normalize($nameEn) ?? '';
    }
}
