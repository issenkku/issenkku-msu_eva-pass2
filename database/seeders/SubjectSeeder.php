<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        $timestamp = now();
        $source = $this->resolveSourcePath();

        $sheet = IOFactory::load($source)->getSheet(0);
        $rows = $sheet->toArray(null, true, true, false);

        array_shift($rows);

        $subjects = [];

        foreach ($rows as $row) {
            $code = preg_replace('/\s+/u', ' ', trim((string) ($row[0] ?? '')));
            $nameTh = preg_replace('/\s+/u', ' ', trim((string) ($row[1] ?? '')));
            $nameEn = preg_replace('/\s+/u', ' ', trim((string) ($row[2] ?? '')));
            $creditText = trim((string) ($row[3] ?? ''));
            $credits = trim((string) ($row[4] ?? ''));
            $lectureCredits = trim((string) ($row[5] ?? ''));
            $labCredits = trim((string) ($row[6] ?? ''));
            $selfStudyCredits = trim((string) ($row[7] ?? ''));

            if ($code === '' || $nameTh === '' || isset($subjects[$code])) {
                continue;
            }

            if ($credits === '' && preg_match('/^(\d+)/', $creditText, $matches)) {
                $credits = $matches[1];
            }

            $lectureCredits = $lectureCredits !== '' ? (int) $lectureCredits : 0;
            $labCredits = $labCredits !== '' ? (int) $labCredits : 0;
            $selfStudyCredits = $selfStudyCredits !== '' ? (int) $selfStudyCredits : 0;
            $credits = $credits !== '' ? (int) $credits : ($lectureCredits + $labCredits + $selfStudyCredits);

            $subjects[$code] = [
                'code' => $code,
                'name_th' => $nameTh,
                'name_en' => $nameEn !== '' ? $nameEn : null,
                'credits' => $credits,
                'lecture_credits' => $lectureCredits,
                'lab_credits' => $labCredits,
                'self_study_credits' => $selfStudyCredits,
                'is_active' => true,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];
        }

        foreach ($subjects as $subject) {
            DB::table('subjects')->updateOrInsert(
                ['code' => $subject['code']],
                $subject
            );
        }
    }

    private function resolveSourcePath(): string
    {
        $candidates = [
            'C:\\Users\\pisut\\Downloads\\ข้อมูลรายวิชา(1).xlsx',
            database_path('seeders/data/subject-seed-source.xlsx'),
        ];

        foreach ($candidates as $candidate) {
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        throw new \RuntimeException('Subject seed source not found in expected locations.');
    }
}
