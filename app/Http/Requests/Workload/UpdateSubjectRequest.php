<?php

namespace App\Http\Requests\Workload;

use App\Models\Subject;
use App\Support\Subjects\SubjectCode;
use App\Support\Subjects\SubjectName;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateSubjectRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $credits = (int) ($this->input('credits') ?: 0);
        $lectureCredits = (int) ($this->input('lecture_credits') ?: 0);
        $labCredits = (int) ($this->input('lab_credits') ?: 0);
        $selfStudyCredits = (int) ($this->input('self_study_credits') ?: 0);

        $normalized = [
            'credits' => $credits,
            'lecture_credits' => $lectureCredits,
            'lab_credits' => $labCredits,
            'self_study_credits' => $selfStudyCredits,
        ];

        foreach (['name_th', 'name_en'] as $field) {
            if ($this->exists($field)) {
                $value = $this->input($field);
                $normalized[$field] = is_scalar($value) || $value === null
                    ? SubjectName::normalize($value)
                    : $value;
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
            'code' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('subjects', 'code')->ignore($this->route('id'))],
            'name_th' => ['sometimes', 'nullable', 'string'],
            'name_en' => ['sometimes', 'nullable', 'string'],
            'credits' => ['sometimes', 'required', 'integer', 'min:0'],
            'lecture_credits' => ['sometimes', 'required', 'integer', 'min:0'],
            'lab_credits' => ['sometimes', 'required', 'integer', 'min:0'],
            'self_study_credits' => ['sometimes', 'required', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            $subject = Subject::find($this->route('id'));
            $nameTh = $this->exists('name_th') ? $this->input('name_th') : $subject?->name_th;
            $nameEn = $this->exists('name_en') ? $this->input('name_en') : $subject?->name_en;

            if (! SubjectName::hasAtLeastOne($nameTh, $nameEn)) {
                $validator->errors()->add('name_th', SubjectName::REQUIRED_MESSAGE);
            }
        }];
    }
}
