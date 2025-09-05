<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SingleReportExport implements WithMultipleSheets
{
    protected $assignment;

    protected $categoryItems;

    public function __construct($assignment, $categoryItems = null)
    {
        $this->assignment = $assignment;
        $this->categoryItems = $categoryItems ?? $this->getCategoryItems();
    }

    public function sheets(): array
    {
        $sheets = [];

        // First sheet - Summary
        $sheets[] = new SummarySheet($this->assignment);

        // Category sheets
        foreach ($this->categoryItems as $category) {
            $sheets[] = new CategorySheet($this->assignment, $category);
        }

        return $sheets;
    }

    private function getCategoryItems()
    {
        $report = $this->assignment->report;

        if (! $report || ! $report->reportData || ! $report->reportData->criteriaVersion) {
            return [];
        }

        // Get quality data for calculations
        $qualityData = DB::table('quality_scores')
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
            ->get();

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

        $categories = $report->reportData->criteriaVersion->categories()
            ->with(['evaluationLists' => function ($query) {
                $query->with([
                    'quantitySubCriterias.mainCriteria',
                    'qualitySubCriterias.mainCriteria',
                ])->orderBy('sequence');
            }])
            ->orderBy('sequence')
            ->get();

        $categoryItems = [];

        foreach ($categories as $category) {
            $categoryData = [
                'id' => $category->id,
                'main_categories' => $category->main_categories,
                'sub_categories' => $category->sub_categories,
                'sequence' => $category->sequence,
                'evaluation_lists' => [],
            ];

            foreach ($category->evaluationLists as $list) {
                $evaluationListData = [
                    'id' => $list->id,
                    'name' => $list->name,
                    'annotation' => $list->annotation,
                    'sum_score' => $list->sum_score,
                    'sequence' => $list->sequence,
                    'quantity_items' => [],
                    'quality_items' => [],
                ];

                // Process quantity items
                if ($list->quantitySubCriterias && $list->quantitySubCriterias->count() > 0) {
                    $quantityMainGroups = $list->quantitySubCriterias->groupBy('quantity_main_criteria_id');

                    foreach ($quantityMainGroups as $mainCriteriaId => $subCriterias) {
                        $mainCriteria = $subCriterias->first()->mainCriteria;

                        if ($mainCriteria) {
                            $mainCriteriaData = [
                                'id' => $mainCriteria->id,
                                'name' => $mainCriteria->name,
                                'sub_criterias' => [],
                            ];

                            foreach ($subCriterias->sortBy('sequence') as $subCriteria) {
                                $quantityScore = \App\Models\QuantityScore::where('report_id', $report->id)
                                    ->where('quantity_sub_criteria_id', $subCriteria->id)
                                    ->first();

                                $mainCriteriaData['sub_criterias'][] = [
                                    'id' => $subCriteria->id,
                                    'name' => $subCriteria->name,
                                    'sequence' => $subCriteria->sequence,
                                    'score_a' => $subCriteria->score_a,
                                    'score_b' => $subCriteria->score_b,
                                    'score_d' => $quantityScore?->score_D ?? 0,
                                ];
                            }

                            $evaluationListData['quantity_items'][] = $mainCriteriaData;
                        }
                    }
                }

                // Process quality items (only main criteria)
                if ($list->qualitySubCriterias && $list->qualitySubCriterias->count() > 0) {
                    $qualityMainGroups = $list->qualitySubCriterias->groupBy('quality_main_criteria_id');

                    foreach ($qualityMainGroups as $mainCriteriaId => $subCriterias) {
                        $mainCriteria = $subCriterias->first()->mainCriteria;

                        if ($mainCriteria) {
                            $arrScoreEvaKey = $list->id.'_'.$mainCriteriaId;
                            $mainCalculatedScore = $arrScoreEva[$arrScoreEvaKey] ?? 0;

                            $evaluationListData['quality_items'][] = [
                                'id' => $mainCriteria->id,
                                'name' => $mainCriteria->name,
                                'ratio' => $mainCriteria->ratio,
                                'main_calculated_score' => round($mainCalculatedScore, 2),
                            ];
                        }
                    }
                }

                $categoryData['evaluation_lists'][] = $evaluationListData;
            }

            $categoryItems[] = $categoryData;
        }

        return $categoryItems;
    }
}

class SummarySheet implements FromArray, WithColumnWidths, WithEvents, WithStyles, WithTitle
{
    protected $assignment;

    public function __construct($assignment)
    {
        $this->assignment = $assignment;
    }

    public function title(): string
    {
        return 'สรุปผล';
    }

    public function array(): array
    {
        $report = $this->assignment->report;

        // Calculate scores (same as your original logic)
        $quantityScore = $report?->quantityScores?->sum('score_D') ?? 0;

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
                $arrScoreEva[$row->evaluation_list_id.'_'.$row->main_id] = $calculatedScore;
            }
        }
        $qualityScore = round(array_sum($arrScoreEva), 2);
        $totalScore = $quantityScore + $qualityScore;

        // Format dates
        $start = optional($this->assignment->assignmentData)->start_time;
        $end = optional($this->assignment->assignmentData)->end_time;

        $startDate = $start ? Carbon::parse($start)->locale('th')->translatedFormat('d M Y H:i') : '-';
        $endDate = $end ? Carbon::parse($end)->locale('th')->translatedFormat('d M Y H:i') : '-';

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
        $evaluators = $this->assignment->getEvaluatorUsers();
        $evaluatorNames = $evaluators->pluck('name')->implode(', ');

        return [
            ['รอบประเมิน', $evaluationRound],
            ['ชื่อ-สกุล', $this->assignment->evaluateeUser?->name],
            ['แผนก', $this->assignment->evaluateeUser?->department?->department_name],
            ['กลุ่มงาน', $this->assignment->evaluateeUser?->personnel_type],
            ['ตำแหน่ง', $this->assignment->evaluateeUser?->position?->name],
            ['คะแนนรวม', $totalScore],
            ['คะแนนด้านปริมาณ', $quantityScore],
            ['คะแนนด้านคุณภาพ', $qualityScore],
            ['ข้อเสนอแนะ', $report?->comment],
            ['ชื่อผู้ประเมิน', $evaluatorNames],
            ['ตำแหน่งผู้ประเมิน', $this->assignment->assignmentData?->evaluatorPosition?->name ?? '-'],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:A11')->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'D3D3D3'],
            ],
        ]);

        $sheet->getStyle('B1:B11')->applyFromArray([
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
        ]);

        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 17,
            'B' => 40,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->getStyle('A1:B100')->getFont()->setName('TH Sarabun New')->setSize(14);
            },
        ];
    }
}

class CategorySheet implements FromArray, WithColumnWidths, WithEvents, WithStyles, WithTitle
{
    protected $assignment;

    protected $category;

    public function __construct($assignment, $category)
    {
        $this->assignment = $assignment;
        $this->category = $category;
    }

    public function title(): string
    {
        return 'หมวดหมู่ที่'.$this->category['sequence'];
    }

    public function array(): array
    {
        $data = [];
        $data[] = ['หัวข้อเกณฑ์', 'คะแนน'];
        $data[] = ['หัวข้อหลัก: '.$this->category['main_categories'].' ('.$this->category['sub_categories'].')', ''];

        foreach ($this->category['evaluation_lists'] as $evaluationList) {
            // Add evaluation list header
            $totalListScore = 0;

            // Calculate total score for this evaluation list
            foreach ($evaluationList['quantity_items'] as $quantityMain) {
                foreach ($quantityMain['sub_criterias'] as $sub) {
                    $totalListScore += (float) $sub['score_d'];
                }
            }

            foreach ($evaluationList['quality_items'] as $qualityMain) {
                $totalListScore += (float) $qualityMain['main_calculated_score'];
            }

            $data[] = ['หัวข้อ: '.$evaluationList['name'], $totalListScore];

            $mainCounter = 1; // Counter for main criteria numbering

            // Add quantity main criteria with sub-criteria
            foreach ($evaluationList['quantity_items'] as $quantityMain) {
                $mainTotalScore = 0;
                foreach ($quantityMain['sub_criterias'] as $sub) {
                    $mainTotalScore += (float) $sub['score_d'];
                }
                $data[] = [$quantityMain['name'], $mainTotalScore];

                $subCounter = 1; // Counter for sub-criteria numbering
                foreach ($quantityMain['sub_criterias'] as $sub) {
                    $data[] = ['  '.$sub['name'], $sub['score_d']];
                    $subCounter++;
                }
                $mainCounter++;
            }

            // Add quality main criteria (only main, no sub)
            foreach ($evaluationList['quality_items'] as $qualityMain) {
                $data[] = [$qualityMain['name'], $qualityMain['main_calculated_score']];
                $mainCounter++;
            }

            $data[]=[' ', ' '];
        }

        return $data;
    }

    public function styles(Worksheet $sheet)
    {
        // Style the header row
        $sheet->getStyle('A1:B1')->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'D3D3D3'],
            ],
        ]);

        $sheet->getStyle('A2:B2')->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'E6E6E6'],
            ],
        ]);

        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 55,
            'B' => 10,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->getStyle('A1:D100')->getFont()->setName('TH Sarabun New')->setSize(14);
                $sheet->getStyle('A1:B1')->getFont()->setSize(16);
                $sheet->getStyle('A2:B2')->getFont()->setSize(16);
            },
        ];
    }
}
