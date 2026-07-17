<?php

namespace App\Http\Requests\Workload;

use Illuminate\Foundation\Http\FormRequest;

final class ConfirmSubjectImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'selected_codes' => ['nullable', 'array', 'max:5000'],
            'selected_codes.*' => ['string', 'max:255', 'distinct'],
        ];
    }
}
