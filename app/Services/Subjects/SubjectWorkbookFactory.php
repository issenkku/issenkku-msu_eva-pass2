<?php

namespace App\Services\Subjects;

use App\Exports\Subjects\SubjectWorkbookExport;
use Illuminate\Support\Collection;

final class SubjectWorkbookFactory
{
    public function template(): SubjectWorkbookExport
    {
        return new SubjectWorkbookExport([]);
    }

    public function current(Collection $subjects): SubjectWorkbookExport
    {
        return new SubjectWorkbookExport($subjects->map(fn ($subject) => [
            $subject->code, $subject->name_th, $subject->name_en,
            $subject->credits, $subject->lecture_credits,
            $subject->lab_credits, $subject->self_study_credits,
        ])->all());
    }
}
