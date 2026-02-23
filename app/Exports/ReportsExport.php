<?php

namespace App\Exports;

use Carbon\Carbon;
use App\Services\ScoreService;
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

            // 3️⃣ Quality score (raw sum with caps)
            $qualityScore = $report ? ScoreService::calculateQualityScoreRaw($report->id) : 0;

            // 4️⃣ Total score
            $totalScore = $quantityScore + $qualityScore;

            $start = optional($assignment->assignmentData)->start_time;
            $end = optional($assignment->assignmentData)->end_time;

            $startDate = $start ? Carbon::parse($start)->locale('th')->translatedFormat('d M Y H:i') : '-';
            $endDate = $end ? Carbon::parse($end)->locale('th')->translatedFormat('d M Y H:i') : '-';

            // Convert to Buddhist year (+543)
            if ($start) {
                $startDate = Carbon::parse($start)
                    ->locale('th')
                    ->translatedFormat('d M ').(Carbon::parse($start)->year + 543).Carbon::parse($start)->format(' H:i');
            }
            if ($end) {
                $endDate = Carbon::parse($end)
                    ->locale('th')
                    ->translatedFormat('d M ').(Carbon::parse($end)->year + 543).Carbon::parse($end)->format(' H:i');
            }

            $evaluationRound = $startDate.' ถึง '.$endDate;

            return [
                $index + 1,
                $evaluationRound,
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


