<?php

namespace App\Http\Requests\Workload;

use App\Support\Subjects\SubjectCode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSubjectRequest extends FormRequest
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
            'name_th' => ['required', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'credits' => ['required', 'integer', 'min:0'],
            'lecture_credits' => ['required', 'integer', 'min:0'],
            'lab_credits' => ['required', 'integer', 'min:0'],
            'self_study_credits' => ['required', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
