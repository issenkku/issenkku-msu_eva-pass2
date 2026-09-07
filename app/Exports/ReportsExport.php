<?php

namespace App\Exports;

use App\Models\EvaluationList;
use App\Models\QualityScore;
use App\Models\QuantityScore;
use App\Models\QuantitySubCriteria;
use App\Models\SupportActivityEntry;
use App\Models\SupportCriteria;
use App\Models\SupportScore;
use App\Models\WorkloadEntry;
use App\Services\ScoreService;
use App\Support\ReportScoreSummary;
use App\Support\SupportAchievementScore;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReportsExport implements WithMultipleSheets
{
    public function __construct(protected $query = null) {}

    public function sheets(): array
    {
        $assignments = $this->query
            ->with([
                'assignmentData.evaluatorUser',
                'evaluateeUser.department',
                'evaluateeUser.position',
                'report.reportData',
            ])
            ->get();

        [$supportAssignments, $academicAssignments] = $assignments
            ->partition(fn ($assignment) => $this->isSupportAssignment($assignment));

        return [
            new AcademicReportsSheet($academicAssignments->values()),
            new SupportReportsSheet($supportAssignments->values()),
        ];
    }

    private function isSupportAssignment($assignment): bool
    {
        $assessmentType = (string) ($assignment->report?->reportData?->assessment_type ?? '');
        $personnelType = (string) ($assignment->evaluateeUser?->personnel_type ?? '');

        if ($assessmentType !== '') {
            return str_contains($assessmentType, 'สนับสนุน');
        }

        return str_contains($personnelType, 'สนับสนุน');
    }
}

abstract class ReportsOverviewSheet implements FromCollection, WithColumnWidths, WithEvents, WithHeadings, WithStrictNullComparison, WithStyles, WithTitle
{
    protected const DETAIL_COLUMN_COUNT = 7;

    protected Collection $quantityPositionsByVersion;

    protected Collection $workloadTotalsByReportAndSubCriteria;

    public function __construct(protected Collection $assignments)
    {
        $criteriaVersionIds = $assignments
            ->pluck('report.reportData.criteria_version_id')
            ->filter()
            ->unique()
            ->values();
        $reportIds = $assignments->pluck('report_id')->filter()->unique()->values();

        $quantitySubCriteria = QuantitySubCriteria::query()
            ->active()
            ->whereIn('criteria_version_id', $criteriaVersionIds)
            ->get(['id', 'criteria_version_id', 'sequence', 'name'])
            ->groupBy('criteria_version_id')
            ->map(fn (Collection $items) => $items
                ->sortBy(fn ($item) => sprintf('%010d-%010d', (int) $item->sequence, (int) $item->id))
                ->take(self::DETAIL_COLUMN_COUNT)
                ->values());

        $this->quantityPositionsByVersion = $quantitySubCriteria
            ->map(fn (Collection $items) => $items
                ->mapWithKeys(fn ($item, $index) => [(int) $item->id => $index]));

        $this->workloadTotalsByReportAndSubCriteria = WorkloadEntry::query()
            ->with('form:id,quantity_sub_criteria_id')
            ->whereIn('report_id', $reportIds)
            ->get(['report_id', 'workload_form_id', 'calculated_score'])
            ->groupBy('report_id')
            ->map(fn (Collection $entries) => $entries
                ->filter(fn ($entry) => $entry->form?->quantity_sub_criteria_id)
                ->groupBy(fn ($entry) => (int) $entry->form->quantity_sub_criteria_id)
                ->map(fn (Collection $subCriteriaEntries) => round($subCriteriaEntries->sum(
                    fn ($entry) => max(0, (float) ($entry->calculated_score ?? 0))
                ), 4)));
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'D3D3D3'],
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastColumn = Coordinate::stringFromColumnIndex(count($this->headings()));
                $lastRow = max(1, $this->assignments->count() + 1);

                $sheet->getStyle("A1:{$lastColumn}{$lastRow}")
                    ->getFont()
                    ->setName('TH Sarabun New')
                    ->setSize(14);
                $sheet->getRowDimension(1)->setRowHeight(18);

                for ($row = 2; $row <= $lastRow; $row++) {
                    $sheet->getRowDimension($row)->setRowHeight(17.5);
                }
            },
        ];
    }

    protected function workloadScores($assignment): array
    {
        $values = array_fill(0, self::DETAIL_COLUMN_COUNT, 0.0);
        $versionId = (int) ($assignment->report?->reportData?->criteria_version_id ?? 0);
        $reportId = (int) ($assignment->report_id ?? 0);
        $positions = collect($this->quantityPositionsByVersion->get($versionId, collect()));
        $scores = collect($this->workloadTotalsByReportAndSubCriteria->get($reportId, collect()));

        foreach ($positions as $subCriteriaId => $position) {
            $values[$position] = (float) ($scores[(int) $subCriteriaId] ?? 0);
        }

        return $values;
    }

    protected function commonTrailingValues($assignment): array
    {
        $report = $assignment->report;

        return [
            $report?->comment,
            $report?->evaluator_comment,
            $report?->director_comment,
            $report?->manager_comment,
            $assignment->assignmentData?->evaluatorUser?->name ?? '',
            $report?->created_at?->format('Y-m-d H:i:s'),
            $report?->updated_at?->format('Y-m-d H:i:s'),
        ];
    }

    protected function evaluationRound($assignment): string
    {
        $start = $assignment->assignmentData?->start_time;
        $end = $assignment->assignmentData?->end_time;

        return $this->thaiBuddhistDateTime($start).' ถึง '.$this->thaiBuddhistDateTime($end);
    }

    protected function widthsToColumns(array $widths): array
    {
        return collect($widths)
            ->mapWithKeys(fn ($width, $index) => [Coordinate::stringFromColumnIndex($index + 1) => $width])
            ->all();
    }

    private function thaiBuddhistDateTime($value): string
    {
        if (! $value) {
            return '-';
        }

        $date = Carbon::parse($value);

        return $date->locale('th')->translatedFormat('d M ').($date->year + 543).$date->format(' H:i');
    }
}

class AcademicReportsSheet extends ReportsOverviewSheet
{
    private const HEADINGS = [
        'ลำดับ',
        'รอบประเมิน',
        'ชื่อ-สกุล',
        'แผนก',
        'กลุ่มงาน',
        'ตำแหน่ง',
        '1.1 ภาระงานด้านการสอน',
        '1.2 ภาระด้านการวิจัย',
        '1.3 ภาระงานบริการวิชาการ',
        '1.4 ภาระทำนุบำรุงศิลปวัฒนธรรม',
        '1.5 ภาระงานพัฒนาตนเอง',
        '1.6 ภาระผลงานทางวิชาการ',
        '1.7 ภาระงานเกี่ยวกับการบริหาร',
        '2.1 ภาระงานด้านการสอน',
        '2.2 ภาระด้านการวิจัย',
        '2.3 ภาระงานบริการวิชาการ',
        '2.4 ภาระทำนุบำรุงศิลปวัฒนธรรม',
        '2.5 ภาระงานพัฒนาตนเอง',
        '2.6 ภาระผลงานทางวิชาการ',
        '2.7 ภาระงานด้านการบริหารองค์กรสู่ความเป็นเลิศ',
        'คะแนนรวม',
        'คะแนนด้านปริมาณ',
        'คะแนนด้านคุณภาพ',
        'ข้อเสนอแนะ',
        'ความเห็นผู้ประเมิน',
        'ความเห็นกรรมการ',
        'ความเห็นผู้บริหาร',
        'ชื่อผู้ประเมิน',
        'สร้างเมื่อ',
        'แก้ไขเมื่อ',
    ];

    private Collection $qualityListPositionsByVersion;

    private Collection $qualityScoresByReportAndList;

    private Collection $quantityScoresByReportAndSubCriteria;

    private Collection $quantityScores;

    private Collection $qualityScores;

    public function __construct(Collection $assignments)
    {
        parent::__construct($assignments);

        $criteriaVersionIds = $assignments
            ->pluck('report.reportData.criteria_version_id')
            ->filter()
            ->unique()
            ->values();
        $reportIds = $assignments->pluck('report_id')->filter()->unique()->values();

        $qualityLists = EvaluationList::query()
            ->with('category:id,sequence')
            ->whereIn('criteria_version_id', $criteriaVersionIds)
            ->whereHas('qualitySubCriterias')
            ->get(['id', 'criteria_version_id', 'categorie_id', 'sequence', 'sum_score'])
            ->groupBy('criteria_version_id')
            ->map(fn (Collection $lists) => $lists
                ->sortBy(fn ($list) => sprintf(
                    '%010d-%010d-%010d',
                    (int) ($list->category?->sequence ?? PHP_INT_MAX),
                    (int) $list->sequence,
                    (int) $list->id,
                ))
                ->take(self::DETAIL_COLUMN_COUNT)
                ->values());

        $this->qualityListPositionsByVersion = $qualityLists
            ->map(fn (Collection $lists) => $lists
                ->mapWithKeys(fn ($list, $index) => [(int) $list->id => [
                    'position' => $index,
                    'maximum' => (float) $list->sum_score,
                ]]));

        $this->qualityScoresByReportAndList = QualityScore::query()
            ->with('qualitySubCriteria:id,evaluation_list_id,num_score')
            ->whereIn('report_id', $reportIds)
            ->get(['report_id', 'quality_sub_criteria_id', 'score'])
            ->groupBy('report_id')
            ->map(fn (Collection $scores) => $scores
                ->filter(fn ($score) => $score->qualitySubCriteria?->evaluation_list_id)
                ->groupBy(fn ($score) => (int) $score->qualitySubCriteria->evaluation_list_id)
                ->map(fn (Collection $listScores) => (float) $listScores->sum(function ($score) {
                    $value = max(0.0, (float) $score->score);
                    $maximum = $score->qualitySubCriteria?->num_score;

                    return $maximum === null
                        ? $value
                        : min($value, max(0.0, (float) $maximum));
                })));

        $this->quantityScoresByReportAndSubCriteria = QuantityScore::query()
            ->whereIn('report_id', $reportIds)
            ->get(['report_id', 'quantity_sub_criteria_id', 'score_D'])
            ->groupBy('report_id')
            ->map(fn (Collection $scores) => $scores
                ->groupBy('quantity_sub_criteria_id')
                ->map(fn (Collection $subCriteriaScores) => round(
                    (float) $subCriteriaScores->sum('score_D'),
                    2,
                )));

        $this->quantityScores = ScoreService::calculateQuantityScoresRawByReportIds($reportIds);
        $this->qualityScores = ScoreService::calculateQualityScoresRawByReportIds($reportIds);
    }

    public function title(): string
    {
        return 'สายอาจารย์';
    }

    public function headings(): array
    {
        return self::HEADINGS;
    }

    public function collection(): Collection
    {
        return $this->assignments->map(function ($assignment, $index) {
            $report = $assignment->report;
            $reportId = (int) ($report?->id ?? 0);
            $scores = ReportScoreSummary::fromTotals(
                (float) ($this->quantityScores[$reportId] ?? 0),
                (float) ($this->qualityScores[$reportId] ?? 0),
                (float) ($report?->support_score_total ?? 0),
            );

            return [
                $index + 1,
                $this->evaluationRound($assignment),
                $assignment->evaluateeUser?->name,
                $assignment->evaluateeUser?->department?->department_name,
                $assignment->evaluateeUser?->personnel_type,
                $assignment->evaluateeUser?->position?->name,
                ...$this->quantityBreakdown($assignment),
                ...$this->qualityBreakdown($assignment),
                $scores['total'],
                $scores['quantity'],
                $scores['quality'],
                ...$this->commonTrailingValues($assignment),
            ];
        });
    }

    public function columnWidths(): array
    {
        return $this->widthsToColumns([
            4.33, 28.92, 11.75, 30.58, 6, 6.25,
            ...array_fill(0, 7, 6.58),
            ...array_fill(0, 7, 6.33),
            8.33, 13.92, 14.25, 8.92, 13.75, 13.42, 13, 12.33, 14.25, 14.25,
        ]);
    }

    private function qualityBreakdown($assignment): array
    {
        $values = array_fill(0, self::DETAIL_COLUMN_COUNT, 0.0);
        $versionId = (int) ($assignment->report?->reportData?->criteria_version_id ?? 0);
        $reportId = (int) ($assignment->report_id ?? 0);
        $definitions = collect($this->qualityListPositionsByVersion->get($versionId, collect()));
        $scores = collect($this->qualityScoresByReportAndList->get($reportId, collect()));

        foreach ($definitions as $listId => $definition) {
            $score = (float) ($scores[(int) $listId] ?? 0);
            $maximum = (float) $definition['maximum'];

            if ($maximum > 0) {
                $score = min($score, $maximum);
            }

            $values[$definition['position']] = round($score, 2);
        }

        return $values;
    }

    private function quantityBreakdown($assignment): array
    {
        $values = array_fill(0, self::DETAIL_COLUMN_COUNT, 0.0);
        $versionId = (int) ($assignment->report?->reportData?->criteria_version_id ?? 0);
        $reportId = (int) ($assignment->report_id ?? 0);
        $positions = collect($this->quantityPositionsByVersion->get($versionId, collect()));
        $scores = collect($this->quantityScoresByReportAndSubCriteria->get($reportId, collect()));

        foreach ($positions as $subCriteriaId => $position) {
            $score = max(0.0, (float) ($scores[(int) $subCriteriaId] ?? 0));
            $values[$position] = round($score, 2);
        }

        return $values;
    }
}

class SupportReportsSheet extends ReportsOverviewSheet
{
    private const HEADINGS = [
        'ลำดับ',
        'รอบประเมิน',
        'ชื่อ-สกุล',
        'กลุ่มงาน',
        'ตำแหน่ง',
        '1. ภาระงานในหน้าที่',
        '1.2 งานด้านประกันคุณภาพ EdPEx คำรับรองปฏิบัติราชการ ความเสี่ยงและควบคุมภายใน',
        '1.3 งานอื่น ๆ ที่ได้รับมอบหมาย',
        '2. ภาระด้านการพัฒนาระบบงานสู่องค์กรแห่งความเป็นเลิศ',
        '3. ภาระงานบริการวิชาการ',
        '4. ภาระทำนุบำรุงศิลปวัฒนธรรม',
        '5. ภาระงานพัฒนาตนเอง',
        '',
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

    private Collection $supportCriteriaPositionsByVersion;

    private Collection $supportCriteriaMaximumsByVersion;

    private Collection $supportScoresByReportAndCriteria;

    public function __construct(Collection $assignments)
    {
        parent::__construct($assignments);

        $criteriaVersionIds = $assignments
            ->pluck('report.reportData.criteria_version_id')
            ->filter()
            ->unique()
            ->values();
        $reportIds = $assignments->pluck('report_id')->filter()->unique()->values();

        $supportCriteria = SupportCriteria::query()
            ->with('evaluationList:id,criteria_version_id,sequence,sum_score')
            ->whereHas('evaluationList', fn ($query) => $query->whereIn('criteria_version_id', $criteriaVersionIds))
            ->get(['id', 'evaluation_list_id', 'sequence', 'weight', 'allow_evaluatee_weight'])
            ->groupBy(fn ($criterion) => (int) ($criterion->evaluationList?->criteria_version_id ?? 0))
            ->map(fn (Collection $criteria) => $criteria
                ->sortBy(fn ($criterion) => sprintf(
                    '%010d-%010d-%010d',
                    (int) ($criterion->evaluationList?->sequence ?? PHP_INT_MAX),
                    (int) $criterion->sequence,
                    (int) $criterion->id,
                ))
                ->take(self::DETAIL_COLUMN_COUNT)
                ->values());

        $this->supportCriteriaPositionsByVersion = $supportCriteria
            ->map(fn (Collection $criteria) => $criteria
                ->mapWithKeys(fn ($criterion, $index) => [(int) $criterion->id => $index]));

        $this->supportCriteriaMaximumsByVersion = $supportCriteria
            ->map(function (Collection $criteria) {
                $fixedWeightsByList = $criteria
                    ->groupBy('evaluation_list_id')
                    ->map(fn (Collection $listCriteria) => $listCriteria
                        ->reject(fn ($criterion) => $criterion->allow_evaluatee_weight)
                        ->sum(fn ($criterion) => max(0.0, (float) ($criterion->weight ?? 0))));

                return $criteria->mapWithKeys(function ($criterion) use ($fixedWeightsByList) {
                    $weight = $criterion->weight;

                    if ($criterion->allow_evaluatee_weight) {
                        $listWeight = (float) ($criterion->evaluationList?->sum_score ?? 0);
                        $fixedWeight = (float) $fixedWeightsByList->get($criterion->evaluation_list_id, 0);
                        $weight = $listWeight > 0
                            ? max(0.0, $listWeight - $fixedWeight)
                            : null;
                    }

                    $maximum = $weight === null
                        ? null
                        : max(0.0, (float) $weight) * SupportAchievementScore::TARGET_LEVEL_COUNT / 100;

                    return [(int) $criterion->id => $maximum];
                });
            });

        $legacyScores = SupportScore::query()
            ->whereIn('report_id', $reportIds)
            ->whereHas('supportCriteria', fn ($query) => $query->where('allow_evaluatee_weight', false))
            ->get(['report_id', 'support_criteria_id', 'weighted_score']);

        $activityScores = SupportActivityEntry::query()
            ->whereIn('report_id', $reportIds)
            ->whereHas('supportCriteria', fn ($query) => $query->where('allow_evaluatee_weight', true))
            ->get(['report_id', 'support_criteria_id', 'weighted_score']);

        $this->supportScoresByReportAndCriteria = $legacyScores
            ->concat($activityScores)
            ->groupBy('report_id')
            ->map(fn (Collection $scores) => $scores
                ->groupBy('support_criteria_id')
                ->map(fn (Collection $criterionScores) => round($criterionScores->sum(
                    fn ($score) => max(0, (float) ($score->weighted_score ?? 0))
                ), 4)));
    }

    public function title(): string
    {
        return 'สายสนับสนุน';
    }

    public function headings(): array
    {
        return self::HEADINGS;
    }

    public function collection(): Collection
    {
        return $this->assignments->map(function ($assignment, $index) {
            $supportBreakdown = $this->supportBreakdown($assignment);
            $scores = ReportScoreSummary::fromTotals(
                0,
                0,
                array_sum($supportBreakdown),
            );

            return [
                $index + 1,
                $this->evaluationRound($assignment),
                $assignment->evaluateeUser?->name,
                $assignment->evaluateeUser?->personnel_type,
                $assignment->evaluateeUser?->position?->name,
                ...$supportBreakdown,
                null,
                $scores['support_raw'],
                $scores['support_achievement'],
                ...$this->commonTrailingValues($assignment),
            ];
        });
    }

    public function columnWidths(): array
    {
        return $this->widthsToColumns([
            4.33, 28.92, 11.75, 6, 6.25, 6.08, 6.08,
            ...array_fill(0, 5, 6.58),
            6.33, 28.08, 18.5, 8.92, 13.75, 13.42, 13, 12.33, 14.25, 14.25,
        ]);
    }

    private function supportBreakdown($assignment): array
    {
        $versionId = (int) ($assignment->report?->reportData?->criteria_version_id ?? 0);
        $positions = collect($this->supportCriteriaPositionsByVersion->get($versionId, collect()));
        $maximums = collect($this->supportCriteriaMaximumsByVersion->get($versionId, collect()));

        if ($positions->isEmpty()) {
            return $this->workloadScores($assignment);
        }

        $values = array_fill(0, self::DETAIL_COLUMN_COUNT, 0.0);
        $reportId = (int) ($assignment->report_id ?? 0);
        $scores = collect($this->supportScoresByReportAndCriteria->get($reportId, collect()));

        foreach ($positions as $criterionId => $position) {
            $score = max(0.0, (float) ($scores[(int) $criterionId] ?? 0));
            $maximum = $maximums->get((int) $criterionId);

            $values[$position] = round(
                $maximum === null ? $score : min($score, (float) $maximum),
                2,
            );
        }

        return $values;
    }

}
