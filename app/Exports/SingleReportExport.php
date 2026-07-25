<?php

namespace App\Exports;

use App\Models\QualityScore;
use App\Models\QuantityScore;
use App\Services\ScoreService;
use App\Support\ReportScoreSummary;
use App\Support\SafeHtml;
use App\Support\SupportCriteriaReadModel;
use Carbon\Carbon;
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
        $hasSupport = collect($this->categoryItems)
            ->flatMap(fn ($category) => $category['evaluation_lists'] ?? [])
            ->contains(fn ($list) => ! empty($list['support_items']));

        // First sheet - Summary
        $sheets = [new SummarySheet($this->assignment, $hasSupport)];

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

        $qualityScores = QualityScore::where('report_id', $report->id)
            ->get()
            ->keyBy('quality_sub_criteria_id');
        $supportItemsByList = app(SupportCriteriaReadModel::class)->forReport($report);

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
                    'support_items' => $supportItemsByList[$list->id] ?? [],
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
                                $quantityScore = QuantityScore::where('report_id', $report->id)
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
                            $mainCalculatedScore = 0;
                            $subItems = [];
                            foreach ($subCriterias->sortBy('sequence') as $subCriteria) {
                                $score = $qualityScores[$subCriteria->id]?->score ?? 0;
                                $mainCalculatedScore += (float) $score;
                                $subItems[] = [
                                    'id' => $subCriteria->id,
                                    'name' => $subCriteria->name,
                                    'sequence' => $subCriteria->sequence,
                                    'num_score' => $subCriteria->num_score,
                                    'score' => $score,
                                ];
                            }

                            $evaluationListData['quality_items'][] = [
                                'id' => $mainCriteria->id,
                                'name' => $mainCriteria->name,
                                'main_calculated_score' => round($mainCalculatedScore, 2),
                                'sub_criterias' => $subItems,
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

    protected bool $hasSupport;

    public function __construct($assignment, bool $hasSupport = false)
    {
        $this->assignment = $assignment;
        $this->hasSupport = $hasSupport;
    }

    public function title(): string
    {
        return 'สรุปผล';
    }

    public function array(): array
    {
        $report = $this->assignment->report;

        $quantityScore = $report?->quantityScores?->sum('score_D') ?? 0;
        $qualityScore = $report ? ScoreService::calculateQualityScoreRaw($report->id) : 0;
        $scores = ReportScoreSummary::fromTotals(
            (float) $quantityScore,
            (float) $qualityScore,
            (float) ($report?->support_score_total ?? 0),
        );

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

        $rows = [
            ['รอบประเมิน', $evaluationRound],
            ['ชื่อ-สกุล', $this->assignment->evaluateeUser?->name],
            ['แผนก', $this->assignment->evaluateeUser?->department?->department_name],
            ['กลุ่มงาน', $this->assignment->evaluateeUser?->personnel_type],
            ['ตำแหน่ง', $this->assignment->evaluateeUser?->position?->name],
            ['คะแนนรวม', $scores['total']],
            ['คะแนนด้านปริมาณ', $scores['quantity']],
            ['คะแนนด้านคุณภาพ', $scores['quality']],
        ];

        if ($this->hasSupport) {
            $rows[] = ['ผลรวมคะแนนถ่วงน้ำหนักสายสนับสนุน', $scores['support_raw']];
            $rows[] = ['คะแนนผลสัมฤทธิ์ของงาน', $scores['support_achievement']];
        }

        return [
            ...$rows,
            ['ข้อเสนอแนะ', $report?->comment],
            ['ความเห็นผู้ประเมิน', $report?->evaluator_comment],
            ['ความเห็นกรรมการ', $report?->director_comment],
            ['ความเห็นผู้บริหาร', $report?->manager_comment],
            ['ชื่อผู้ประเมิน', $evaluatorNames],
            ['ตำแหน่งผู้ประเมิน', $this->assignment->assignmentData?->evaluatorPosition?->name ?? '-'],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A:A')->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'D3D3D3'],
            ],
        ]);

        $sheet->getStyle('B:B')->applyFromArray([
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

            $qualityListScore = 0;
            foreach ($evaluationList['quality_items'] as $qualityMain) {
                $subTotal = 0;
                foreach ($qualityMain['sub_criterias'] ?? [] as $sub) {
                    $subTotal += (float) ($sub['score'] ?? 0);
                }
                $qualityListScore += $subTotal;
            }
            $listMax = (float) ($evaluationList['sum_score'] ?? 0);
            if ($listMax > 0 && $qualityListScore > $listMax) {
                $qualityListScore = $listMax;
            }
            $totalListScore += $qualityListScore;
            $supportListScore = collect($evaluationList['support_items'] ?? [])
                ->sum(fn ($item) => (float) ($item['weighted_score'] ?? 0));
            $totalListScore += $supportListScore;

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
                $mainScore = 0;
                foreach ($qualityMain['sub_criterias'] ?? [] as $sub) {
                    $mainScore += (float) ($sub['score'] ?? 0);
                }
                $data[] = [$qualityMain['name'], $mainScore];
                $mainCounter++;
            }

            foreach ($evaluationList['support_items'] ?? [] as $supportItem) {
                $data[] = [
                    'สายสนับสนุน: '.SafeHtml::plainText($supportItem['activity_name'] ?? ''),
                    (float) ($supportItem['weighted_score'] ?? 0),
                ];
                $data[] = ['  ตัวชี้วัด', SafeHtml::plainText($supportItem['indicator'] ?? '')];
                $data[] = ['  ค่าเป้าหมาย', (float) ($supportItem['target_value'] ?? 0)];
                $data[] = ['  น้ำหนัก', (float) ($supportItem['weight'] ?? 0)];
                $data[] = ['  คะแนนที่ทำได้', (float) ($supportItem['achieved_score'] ?? 0)];
                $data[] = ['  คะแนนถ่วงน้ำหนัก', (float) ($supportItem['weighted_score'] ?? 0)];

                foreach ($supportItem['activity_entries'] ?? [] as $activityEntry) {
                    $data[] = [
                        '  กิจกรรม/โครงการเพิ่มเติม',
                        SafeHtml::plainText($activityEntry['content'] ?? ''),
                    ];

                    foreach ($activityEntry['evidence_links'] ?? [] as $evidenceLink) {
                        $data[] = ['    หลักฐาน', $evidenceLink];
                    }
                }

                foreach ($supportItem['evidence_links'] ?? [] as $evidenceLink) {
                    $data[] = ['  หลักฐาน', $evidenceLink];
                }
            }

            $data[] = [' ', ' '];
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
