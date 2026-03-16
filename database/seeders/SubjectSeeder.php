<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class SubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $timestamp = now();
        $source = database_path('seeders/data/subject-seed-source.xlsx');

        if (! is_file($source)) {
            throw new \RuntimeException("Subject seed source not found: {$source}");
        }

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

            if ($code === '' || $nameTh === '' || isset($subjects[$code])) {
                continue;
            }

            if ($credits === '' && preg_match('/^(\d+)/', $creditText, $matches)) {
                $credits = $matches[1];
            }

            $subjects[$code] = [
                'code' => $code,
                'name_th' => $nameTh,
                'name_en' => $nameEn !== '' ? $nameEn : null,
                'credits' => (int) $credits,
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
}
