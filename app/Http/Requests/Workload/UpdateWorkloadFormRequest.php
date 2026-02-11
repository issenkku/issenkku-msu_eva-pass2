<?php

namespace App\Http\Requests\Workload;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWorkloadFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'formula_logic' => ['sometimes', 'required', 'string'],
            'quantity_sub_criteria_id' => ['sometimes', 'required', 'integer', 'exists:quantity_sub_criterias,id'],
            'quantity_sub_criteria_item_id' => ['sometimes', 'required', 'integer', 'exists:quantity_sub_criteria_items,id'],
        ];
    }
}
