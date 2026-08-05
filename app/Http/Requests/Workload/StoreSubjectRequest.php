<?php

namespace App\Http\Requests\Workload;

use App\Support\Subjects\SubjectCode;
use App\Support\Subjects\SubjectName;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreSubjectRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $normalized = [
            'name_th' => is_scalar($this->input('name_th')) || $this->input('name_th') === null
                ? SubjectName::normalize($this->input('name_th'))
                : $this->input('name_th'),
            'name_en' => is_scalar($this->input('name_en')) || $this->input('name_en') === null
                ? SubjectName::normalize($this->input('name_en'))
                : $this->input('name_en'),
        ];

        foreach (['credits', 'lecture_credits', 'lab_credits', 'self_study_credits'] as $field) {
            if ($this->input($field) === null || $this->input($field) === '') {
                $normalized[$field] = 0;
            }
        }

        if ($this->exists('code')) {
            $normalized['code'] = SubjectCode::normalize($this->input('code'));
        }

        $this->merge($normalized);
    }

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
            'code' => ['required', 'string', 'max:255', Rule::unique('subjects', 'code')],
            'name_th' => ['nullable', 'string'],
            'name_en' => ['nullable', 'string'],
            'credits' => ['required', 'integer', 'min:0'],
            'lecture_credits' => ['required', 'integer', 'min:0'],
            'lab_credits' => ['required', 'integer', 'min:0'],
            'self_study_credits' => ['required', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            if (! SubjectName::hasAtLeastOne($this->input('name_th'), $this->input('name_en'))) {
                $validator->errors()->add('name_th', SubjectName::REQUIRED_MESSAGE);
            }
        }];
    }
}
