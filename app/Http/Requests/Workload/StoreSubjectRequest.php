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
        $credits = (int) ($this->input('credits') ?: 0);
        $lectureCredits = (int) ($this->input('lecture_credits') ?: 0);
        $labCredits = (int) ($this->input('lab_credits') ?: 0);
        $selfStudyCredits = (int) ($this->input('self_study_credits') ?: 0);

        $normalized = [
            'name_th' => is_scalar($this->input('name_th')) || $this->input('name_th') === null
                ? SubjectName::normalize($this->input('name_th'))
                : $this->input('name_th'),
            'name_en' => is_scalar($this->input('name_en')) || $this->input('name_en') === null
                ? SubjectName::normalize($this->input('name_en'))
                : $this->input('name_en'),
            'credits' => $credits,
            'lecture_credits' => $lectureCredits,
            'lab_credits' => $labCredits,
            'self_study_credits' => $selfStudyCredits,
        ];

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
