<?php

namespace App\Support;

use Illuminate\Validation\ValidationException;

final class ScoreChangePolicy
{
    public static function numbersDiffer(mixed $before, mixed $after): bool
    {
        return self::normalizeNumber($before) !== self::normalizeNumber($after);
    }

    public static function textsDiffer(mixed $before, mixed $after): bool
    {
        return self::normalizeText($before) !== self::normalizeText($after);
    }

    public static function validatedReason(
        bool $changed,
        mixed $reason,
        bool $required,
        string $errorKey
    ): ?string {
        if (! $changed) {
            return null;
        }

        $normalized = self::normalizeText($reason);

        if ($required && $normalized === null) {
            throw ValidationException::withMessages([
                $errorKey => ['กรุณาระบุเหตุผลที่แก้ไขคะแนน'],
            ]);
        }

        if ($normalized !== null && mb_strlen($normalized) > 2000) {
            throw ValidationException::withMessages([
                $errorKey => ['เหตุผลที่แก้ไขคะแนนต้องมีความยาวไม่เกิน 2,000 ตัวอักษร'],
            ]);
        }

        return $normalized;
    }

    private static function normalizeNumber(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return number_format((float) $value, 2, '.', '');
    }

    private static function normalizeText(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value === '' ? null : $value;
    }
}
