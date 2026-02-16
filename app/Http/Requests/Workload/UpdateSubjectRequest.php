<?php

namespace App\Http\Requests\Workload;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSubjectRequest extends FormRequest
{
    protected function getRedirectUrl()
    {
        $redirectTo = $this->input('redirect_to');
        if ($redirectTo) {
            return $redirectTo;
        }

        return parent::getRedirectUrl();
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => ['sometimes', 'required', 'string', 'max:255'],
            'name_th' => ['sometimes', 'required', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'credits' => ['sometimes', 'required', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
