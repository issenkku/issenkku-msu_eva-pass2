<?php

// ไฟล์คลาสของระบบ: app/Http/Requests/Workload/StoreWorkloadFormRequest.php

namespace App\Http\Requests\Workload;

use Illuminate\Foundation\Http\FormRequest;

class StoreWorkloadFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'formula_logic' => ['nullable', 'string'],
            'quantity_sub_criteria_id' => ['required', 'integer', 'exists:quantity_sub_criterias,id'],
            'quantity_sub_criteria_item_id' => ['nullable', 'integer', 'exists:quantity_sub_criteria_items,id'],
        ];
    }
}
