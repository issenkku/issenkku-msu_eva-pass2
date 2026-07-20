<?php

namespace App\Rules;

use App\Support\SafeHtml;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

final class HasRichText implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || SafeHtml::plainText($value) === '') {
            $fail('กรุณากรอกข้อความที่มีเนื้อหา');
        }
    }
}
