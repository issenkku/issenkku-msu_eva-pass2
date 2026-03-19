<?php

namespace App\Http\Requests\Workload;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSubjectRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $lectureCredits = (int) ($this->input('lecture_credits') ?: 0);
        $labCredits = (int) ($this->input('lab_credits') ?: 0);
        $selfStudyCredits = (int) ($this->input('self_study_credits') ?: 0);

        $this->merge([
            'lecture_credits' => $lectureCredits,
            'lab_credits' => $labCredits,
            'self_study_credits' => $selfStudyCredits,
            'credits' => $lectureCredits + $labCredits + $selfStudyCredits,
        ]);
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
            'code' => ['sometimes', 'required', 'string', 'max:255'],
            'name_th' => ['sometimes', 'required', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'credits' => ['sometimes', 'required', 'integer', 'min:0'],
            'lecture_credits' => ['sometimes', 'required', 'integer', 'min:0'],
            'lab_credits' => ['sometimes', 'required', 'integer', 'min:0'],
            'self_study_credits' => ['sometimes', 'required', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
