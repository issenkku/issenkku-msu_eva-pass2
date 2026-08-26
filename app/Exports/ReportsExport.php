<?php

namespace App\Exports;

use App\Models\WorkloadEntry;
use App\Models\WorkloadForm;
use App\Services\ScoreService;
use App\Support\ReportScoreSummary;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReportsExport implements FromCollection, WithColumnWidths, WithEvents, WithHeadings, WithStyles
{
    /**
     * @return Collection
     */
    protected $query;

    protected ?Collection $assignments = null;

    protected Collection $workloadColumns;

    protected Collection $workloadTotalsByReportAndForm;

    public function __construct($query = null)
    {
        $this->query = $query;
        $this->workloadColumns = collect();
        $this->workloadTotalsByReportAndForm = collect();
    }

    public function collection()
    {
        $this->prepareExportData();

        $assignments = $this->assignments;
        $reportIds = $assignments->pluck('report_id')->filter()->unique()->values();
        $quantityScores = ScoreService::calculateQuantityScoresRawByReportIds($reportIds);
        $qualityScores = ScoreService::calculateQualityScoresRawByReportIds($reportIds);

        return $assignments->map(function ($assignment, $index) use ($quantityScores, $qualityScores) {
            $report = $assignment->report;

            $evaluatorNames = $assignment->assignmentData?->evaluatorUser?->name ?? '';
            $scores = ReportScoreSummary::fromTotals(
                (float) ($quantityScores[$report?->id] ?? 0),
                (float) ($qualityScores[$report?->id] ?? 0),
                (float) ($report?->support_score_total ?? 0),
            );
            $workloadScores = $this->workloadColumns
                ->map(function ($column) use ($report) {
                    $formTotals = collect($this->workloadTotalsByReportAndForm->get($report?->id, collect()));

                    return round(collect($column['form_ids'])->sum(
                        fn ($formId) => (float) ($formTotals[$formId] ?? 0)
                    ), 4);
                })
                ->all();

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
                $scores['total'],
                $scores['quantity'],
                $scores['quality'],
                $scores['support_raw'],
                $scores['support_achievement'],
                ...$workloadScores,
                $report?->comment,
                $report?->evaluator_comment,
                $report?->director_comment,
                $report?->manager_comment,
                $evaluatorNames,
                $report?->created_at,
                $report?->updated_at,
            ];
        });
    }

    public function headings(): array
    {
        $this->prepareExportData();

        $headings = [
            'ลำดับ',
            'รอบประเมิน',
            'ชื่อ-สกุล',
            'แผนก',
            'กลุ่มงาน',
            'ตำแหน่ง',
            'คะแนนรวม',
            'คะแนนด้านปริมาณ',
            'คะแนนด้านคุณภาพ',
            'ผลรวมคะแนนถ่วงน้ำหนักสายสนับสนุน',
            'คะแนนผลสัมฤทธิ์ของงาน',
            'ข้อเสนอแนะ',
            'ความเห็นผู้ประเมิน',
            'ความเห็นกรรมการ',
            'ความเห็นผู้บริหาร',
            'ชื่อผู้ประเมิน',
            'สร้างเมื่อ',
            'แก้ไขเมื่อ',
        ];

        array_splice($headings, 11, 0, $this->workloadColumns->pluck('heading')->all());

        return $headings;
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
        $this->prepareExportData();

        $widths = [
            5, 25, 20, 20, 8, 20, 10, 16, 16, 30, 20,
            ...array_fill(0, $this->workloadColumns->count(), 35),
            30, 30, 30, 30, 25, 20, 20,
        ];

        return collect($widths)
            ->mapWithKeys(fn ($width, $index) => [Coordinate::stringFromColumnIndex($index + 1) => $width])
            ->all();
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $this->prepareExportData();
                $lastColumn = Coordinate::stringFromColumnIndex(count($this->headings()));
                $lastRow = max(100, $this->assignments->count() + 1);

                $sheet->getStyle("A1:{$lastColumn}{$lastRow}")->getFont()->setName('TH Sarabun New')->setSize(14);
            },
        ];
    }

    private function prepareExportData(): void
    {
        if ($this->assignments !== null) {
            return;
        }

        $this->assignments = $this->query
            ->with([
                'assignmentData.evaluatorUser',
                'evaluateeUser.department',
                'evaluateeUser.position',
                'report.reportData',
            ])
            ->get();

        $reportIds = $this->assignments->pluck('report_id')->filter()->unique()->values();
        $criteriaVersionIds = $this->assignments
            ->pluck('report.reportData.criteria_version_id')
            ->filter()
            ->unique()
            ->values();

        $forms = WorkloadForm::with([
            'quantitySubCriteria.evaluationList.category',
            'subCriteriaItem.group',
        ])
            ->whereHas('quantitySubCriteria', fn ($query) => $query
                ->active()
                ->whereIn('criteria_version_id', $criteriaVersionIds))
            ->get()
            ->sortBy(fn ($form) => sprintf(
                '%010d-%010d-%010d-%010d-%010d-%010d',
                (int) ($form->quantitySubCriteria?->criteria_version_id ?? PHP_INT_MAX),
                (int) ($form->quantitySubCriteria?->evaluationList?->category?->sequence ?? PHP_INT_MAX),
                (int) ($form->quantitySubCriteria?->evaluationList?->sequence ?? PHP_INT_MAX),
                (int) ($form->quantitySubCriteria?->sequence ?? PHP_INT_MAX),
                (int) ($form->subCriteriaItem?->group?->sequence ?? PHP_INT_MAX),
                (int) ($form->subCriteriaItem?->sequence ?? PHP_INT_MAX),
            ));

        $this->workloadColumns = $forms
            ->map(function ($form) {
                $parts = collect([
                    $form->quantitySubCriteria?->name,
                    $form->subCriteriaItem?->group?->name,
                    $form->subCriteriaItem?->name,
                ])->filter(fn ($value) => trim((string) $value) !== '')
                    ->unique()
                    ->values();

                return [
                    'heading' => 'คะแนนภาระงาน: '.$parts->implode(' / '),
                    'form_id' => (int) $form->id,
                ];
            })
            ->map(fn ($formColumn) => [
                'heading' => $formColumn['heading'],
                'form_ids' => [$formColumn['form_id']],
            ])
            ->values();

        $this->workloadTotalsByReportAndForm = WorkloadEntry::query()
            ->whereIn('report_id', $reportIds)
            ->whereIn('workload_form_id', $forms->pluck('id'))
            ->get(['report_id', 'workload_form_id', 'calculated_score'])
            ->groupBy('report_id')
            ->map(fn ($reportEntries) => $reportEntries
                ->groupBy('workload_form_id')
                ->map(fn ($formEntries) => round($formEntries->sum(
                    fn ($entry) => max(0, (float) ($entry->calculated_score ?? 0))
                ), 4)));
    }
}
