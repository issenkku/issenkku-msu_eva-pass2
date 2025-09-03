<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReportsExport implements FromCollection, WithColumnWidths, WithEvents, WithHeadings, WithStyles
{
    /**
     * @return \Illuminate\Support\Collection
     */
    protected $query;

    public function __construct($query = null)
    {
        $this->query = $query;
    }

    public function collection()
    {
        return $this->query->get()->map(function ($assignment, $index) {
            $report = $assignment->report;

            // 1️⃣ Evaluator names
            $evaluators = $assignment->getEvaluatorUsers();
            $evaluatorNames = $evaluators->pluck('name')->implode(', ');

            // 2️⃣ Quantity score
            $quantityScore = $report?->quantityScores?->sum('score_D') ?? 0;

            // 3️⃣ Quality score calculation using arrScoreEva logic
            $qualityData = $report
                ? DB::table('quality_scores')
                    ->join('quality_sub_criterias', 'quality_scores.quality_sub_criteria_id', '=', 'quality_sub_criterias.id')
                    ->join('quality_main_criterias', 'quality_sub_criterias.quality_main_criteria_id', '=', 'quality_main_criterias.id')
                    ->join('evaluation_lists', 'quality_sub_criterias.evaluation_list_id', '=', 'evaluation_lists.id')
                    ->where('quality_scores.report_id', $report->id)
                    ->selectRaw('
                        quality_sub_criterias.evaluation_list_id,
                        quality_main_criterias.id as main_id,
                        quality_main_criterias.ratio,
                        evaluation_lists.sum_score,
                        SUM(quality_scores.score) as total_score,
                        SUM(quality_sub_criterias.num_score) as total_max_score
                    ')
                    ->groupBy(
                        'quality_sub_criterias.evaluation_list_id',
                        'quality_main_criterias.id',
                        'quality_main_criterias.ratio',
                        'evaluation_lists.sum_score'
                    )
                    ->get()
                : collect();

            $arrScoreEva = [];
            foreach ($qualityData as $row) {
                $maxSum = (float) $row->total_max_score;
                $accSum = (float) $row->total_score;
                $ratio = (float) $row->ratio;
                $sumScoreEva = (float) $row->sum_score;

                if ($maxSum > 0) {
                    $scoreRatioMain = $ratio * ($accSum / $maxSum);
                    $calculatedScore = ($scoreRatioMain / 100) * $sumScoreEva;
                    $key = $row->evaluation_list_id.'_'.$row->main_id;
                    $arrScoreEva[$key] = $calculatedScore;
                }
            }

            $qualityScore = round(array_sum($arrScoreEva), 2);

            // 4️⃣ Total score
            $totalScore = $quantityScore + $qualityScore;

            return [
                $index + 1,
                optional($assignment->assignmentData)->start_time.' ถึง '.optional($assignment->assignmentData)->end_time,
                $assignment->evaluateeUser?->name,
                $assignment->evaluateeUser?->department?->department_name,
                $assignment->evaluateeUser?->personnel_type,
                $assignment->evaluateeUser?->position?->name,
                $totalScore,
                $quantityScore,
                $qualityScore,
                $report?->comment,
                $evaluatorNames,
                $report?->created_at,
                $report?->updated_at,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'ลำดับ',
            'รอบประเมิน',
            'ชื่อ-สกุล',
            'แผนก',
            'กลุ่มงาน',
            'ตำแหน่ง',
            'คะแนนรวม',
            'คะแนนด้านปริมาณ',
            'คะแนนด้านคุณภาพ',
            'ข้อเสนอแนะ',
            'ชื่อผู้ประเมิน',
            'สร้างเมื่อ',
            'แก้ไขเมื่อ',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Heading row styles (row 1)
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'D3D3D3'],
                ],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,
            'B' => 25,
            'C' => 20,
            'D' => 20,
            'E' => 8,
            'F' => 20,
            'G' => 10,
            'H' => 16,
            'I' => 16,
            'J' => 30,
            'K' => 25,
            'L' => 20,
            'M' => 20,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Apply font to A1:M100 range
                $sheet->getStyle('A1:M100')->getFont()->setName('TH Sarabun New')->setSize(14);
            },
        ];
    }
}
