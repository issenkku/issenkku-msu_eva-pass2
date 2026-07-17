<?php

namespace App\Http\Requests\Workload;

use Illuminate\Foundation\Http\FormRequest;

final class StoreSubjectImportPreviewRequest extends FormRequest
{
    protected $errorBag = 'subjectImport';

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['import_file' => ['required', 'file', 'mimes:xlsx', 'max:10240']];
    }
}
