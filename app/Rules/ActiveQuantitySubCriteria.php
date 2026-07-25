<?php

namespace App\Rules;

use App\Models\QuantitySubCriteria;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

final class ActiveQuantitySubCriteria implements ValidationRule
{
    public function __construct(
        private readonly int $criteriaVersionId,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $exists = QuantitySubCriteria::query()
            ->active()
            ->where('criteria_version_id', $this->criteriaVersionId)
            ->whereKey($value)
            ->exists();

        if (! $exists) {
            $fail('เกณฑ์ด้านปริมาณนี้ไม่ได้เปิดใช้งาน');
        }
    }
}
