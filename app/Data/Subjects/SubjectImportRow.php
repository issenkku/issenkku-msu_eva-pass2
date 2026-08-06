<?php

namespace App\Data\Subjects;

final readonly class SubjectImportRow
{
    public function __construct(
        public int $excelRow,
        public string $code,
        public ?string $nameTh,
        public ?string $nameEn,
        public int $credits,
        public int $lectureCredits,
        public int $labCredits,
        public int $selfStudyCredits,
        public int $lectureHours,
        public int $labHours,
        public int $selfStudyHours,
    ) {}

    public function attributes(): array
    {
        return [
            'code' => $this->code,
            'name_th' => $this->nameTh,
            'name_en' => $this->nameEn,
            'credits' => $this->credits,
            'lecture_credits' => $this->lectureCredits,
            'lab_credits' => $this->labCredits,
            'self_study_credits' => $this->selfStudyCredits,
            'lecture_hours' => $this->lectureHours,
            'lab_hours' => $this->labHours,
            'self_study_hours' => $this->selfStudyHours,
        ];
    }

    public function toArray(): array
    {
        return ['excel_row' => $this->excelRow, ...$this->attributes()];
    }
}
