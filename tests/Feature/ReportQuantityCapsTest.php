<?php

use App\Exports\ReportsExport;
use App\Exports\SingleReportExport;
use App\Models\Assignments;
use App\Models\Category;
use App\Models\CriteriaVersion;
use App\Models\EvaluationList;
use App\Models\QualityScore;
use App\Models\QualitySubCriteria;
use App\Models\QuantityScore;
use App\Models\QuantitySubCriteria;
use App\Models\ReportData;
use App\Models\Reports;
use App\Services\ScoreService;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;

test('report exports cap each quantity criterion without changing dashboard or stored scores', function (array $caps, array $expected, float $total, array $raw) {
    $version = CriteriaVersion::factory()->create();
    $sheetIndex = $caps[6] === 20 ? 1 : 0;
    $reportData = ReportData::factory()->create([
        'criteria_version_id' => $version->id,
        'assessment_type' => $sheetIndex === 1 ? 'กลุ่มบริหาร' : 'กลุ่มวิชาการ',
    ]);
    $report = Reports::factory()->create(['report_data_id' => $reportData->id, 'support_score_total' => 0]);
    $category = Category::factory()->create(['criteria_version_id' => $version->id, 'sequence' => 1]);
    $list = EvaluationList::factory()->create([
        'criteria_version_id' => $version->id, 'categorie_id' => $category->id,
        'quantity_enabled' => true, 'sum_score' => 40, 'name' => 'ปริมาณ', 'sequence' => 1,
    ]);
    foreach ($caps as $index => $cap) {
        $criterion = QuantitySubCriteria::factory()->create([
            'criteria_version_id' => $version->id, 'evaluation_list_id' => $list->id,
            'sequence' => $index + 1, 'name' => 'ข้อ '.($index + 1), 'score_a' => $cap,
        ]);
        QuantityScore::factory()->create([
            'report_id' => $report->id, 'quantity_sub_criteria_id' => $criterion->id, 'score_D' => $raw[$index],
        ]);
    }
    $qualityList = EvaluationList::factory()->create([
        'criteria_version_id' => $version->id, 'categorie_id' => $category->id,
        'quantity_enabled' => false, 'sum_score' => 30, 'sequence' => 2,
    ]);
    $quality = QualitySubCriteria::factory()->create([
        'criteria_version_id' => $version->id, 'evaluation_list_id' => $qualityList->id, 'num_score' => 30,
    ]);
    QualityScore::factory()->create(['report_id' => $report->id, 'quality_sub_criteria_id' => $quality->id, 'score' => 29.79]);
    $assignment = Assignments::factory()->create(['report_id' => $report->id]);

    $row = (new ReportsExport(Assignments::where('report_id', $report->id)))->sheets()[$sheetIndex]->collection()->first();
    expect(array_slice($row, 6, 7))->toBe($expected)
        ->and($row[21])->toBe($total)
        ->and($row[20])->toBe(round($total + 29.79, 2));

    $sheets = (new SingleReportExport($assignment->load('report', 'assignmentData', 'evaluateeUser')))->sheets();
    expect($sheets[0]->array())->toContain(['คะแนนด้านปริมาณ', $total])
        ->toContain(['คะแนนรวม', round($total + 29.79, 2)]);
    $details = $sheets[1]->array();
    expect($details)->toContain(['หัวข้อ: ปริมาณ', $total]);
    foreach ($expected as $index => $value) {
        expect($details)->toContain(['  ข้อ '.($index + 1), $value]);
    }
    $path = tempnam(sys_get_temp_dir(), 'report-caps-');
    try {
        file_put_contents($path, Excel::raw(new ReportsExport(Assignments::where('report_id', $report->id)), ExcelFormat::XLSX));
        $workbook = IOFactory::load($path);
        expect($workbook->getSheetNames())->toBe(['สายอาจารย์', 'สายผู้บริหาร', 'สายสนับสนุน']);
        $sheet = $workbook->getSheet($sheetIndex);
        expect((float) $sheet->getCell('G2')->getValue())->toBe($expected[0])
            ->and((float) $sheet->getCell('V2')->getValue())->toBe($total)
            ->and((float) $sheet->getCell('U2')->getValue())->toBe(round($total + 29.79, 2));
        $workbook->disconnectWorksheets();
    } finally {
        unlink($path);
    }
    expect((float) ScoreService::calculateQuantityScoresRawByReportIds([$report->id])[$report->id])
        ->toBe(min(40.0, array_sum(array_map(fn ($value) => max(0.0, (float) $value), $raw))))
        ->and(QuantityScore::where('report_id', $report->id)->orderBy('id')->pluck('score_D')->map(fn ($value) => $value === null ? null : (float) $value)->all())
        ->toBe(array_map(fn ($value) => $value === null ? null : (float) $value, $raw));
})->with([
    'academic' => [[15, 8, 4, 2, 3, 4, 4], [15.0, 8.0, 4.0, 2.0, 2.4, 4.0, 4.0], 39.4, [49.75, 11, 6, 3.35, 2.4, 5, 10]],
    'management' => [[10, 3, 1.5, 1.5, 2, 2, 20], [10.0, 3.0, 1.5, 1.5, 2.0, 2.0, 10.0], 30.0, [49.75, 11, 6, 3.35, 2.4, 5, 10]],
    'boundaries' => [[0, 8, 4, 2, 3, 4, 4], [0.0, 8.0, 1.25, 0.0, 0.0, 0.0, 4.0], 13.25, [9, 8, 1.25, 0, null, -2, 10]],
]);
