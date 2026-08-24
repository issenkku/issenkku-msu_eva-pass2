<?php

use App\Exports\ReportsExport;
use App\Exports\SingleReportExport;
use App\Models\Assignments;
use App\Models\Category;
use App\Models\CriteriaVersion;
use App\Models\EvaluationList;
use App\Models\EvidenceAnswer;
use App\Models\QualityScore;
use App\Models\QualitySubCriteria;
use App\Models\QuantityScore;
use App\Models\QuantitySubCriteria;
use App\Models\ReportData;
use App\Models\Reports;
use App\Models\SupportCriteria;
use App\Models\SupportScore;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('dashboard export includes support score summaries and role comments', function () {
    $report = Reports::factory()->create([
        'status' => 'Completed',
        'support_score_total' => 112.50,
        'support_achievement_score' => 22.50,
        'comment' => 'ความเห็นรวม',
        'evaluator_comment' => 'ความเห็นผู้ประเมิน',
        'director_comment' => 'ความเห็นกรรมการ',
        'manager_comment' => 'ความเห็นผู้บริหาร',
    ]);
    $assignment = Assignments::factory()->create(['report_id' => $report->id]);

    $export = new ReportsExport(
        Assignments::query()->where('report_id', $report->id)
    );
    $headings = $export->headings();
    $row = $export->collection()->first();

    expect($headings)
        ->toHaveCount(18)
        ->toContain('ผลรวมคะแนนถ่วงน้ำหนักสายสนับสนุน')
        ->toContain('คะแนนผลสัมฤทธิ์ของงาน')
        ->toContain('ความเห็นผู้ประเมิน')
        ->toContain('ความเห็นกรรมการ')
        ->toContain('ความเห็นผู้บริหาร')
        ->and($row[6])->toBe(100.0)
        ->and($row[9])->toBe(112.5)
        ->and($row[10])->toBe(22.5)
        ->and($row)->toContain('ความเห็นผู้ประเมิน', 'ความเห็นกรรมการ', 'ความเห็นผู้บริหาร');
});

test('dashboard export grand total includes quantity quality and capped support', function () {
    $criteriaVersion = CriteriaVersion::factory()->create();
    $reportData = ReportData::factory()->create([
        'criteria_version_id' => $criteriaVersion->id,
    ]);
    $report = Reports::factory()->create([
        'report_data_id' => $reportData->id,
        'status' => 'Completed',
        'support_score_total' => 4.30,
        'support_achievement_score' => 0.86,
    ]);
    $evaluationList = EvaluationList::factory()->create([
        'criteria_version_id' => $criteriaVersion->id,
        'quantity_enabled' => true,
        'sum_score' => 10,
    ]);
    $quantitySubCriteria = QuantitySubCriteria::factory()->create([
        'criteria_version_id' => $criteriaVersion->id,
        'evaluation_list_id' => $evaluationList->id,
    ]);
    $qualitySubCriteria = QualitySubCriteria::factory()->create([
        'criteria_version_id' => $criteriaVersion->id,
        'evaluation_list_id' => $evaluationList->id,
    ]);
    QuantityScore::factory()->create([
        'report_id' => $report->id,
        'quantity_sub_criteria_id' => $quantitySubCriteria->id,
        'score_D' => 2,
    ]);
    QualityScore::factory()->create([
        'report_id' => $report->id,
        'quality_sub_criteria_id' => $qualitySubCriteria->id,
        'score' => 3,
    ]);
    $assignment = Assignments::factory()->create(['report_id' => $report->id]);

    $row = (new ReportsExport(
        Assignments::query()->where('report_id', $report->id)
    ))->collection()->first();

    expect($row[6])->toBe(9.3)
        ->and($row[7])->toBe(2.0)
        ->and($row[8])->toBe(3.0)
        ->and($row[9])->toBe(4.3)
        ->and($row[10])->toBe(0.86);
});

test('single report summary conditionally includes support scores and role comments', function () {
    $report = Reports::factory()->create([
        'status' => 'Completed',
        'support_score_total' => 4.30,
        'support_achievement_score' => 0.86,
        'comment' => 'ความเห็นรวม',
        'evaluator_comment' => 'ความเห็นผู้ประเมิน',
        'director_comment' => 'ความเห็นกรรมการ',
        'manager_comment' => 'ความเห็นผู้บริหาร',
    ]);
    $assignment = Assignments::factory()->create(['report_id' => $report->id]);
    $assignment->load('report', 'assignmentData', 'evaluateeUser');
    $categoryItems = [[
        'sequence' => 1,
        'main_categories' => 'หมวด',
        'sub_categories' => 'ย่อย',
        'evaluation_lists' => [[
            'name' => 'รายการ',
            'sum_score' => 0,
            'quantity_items' => [],
            'quality_items' => [],
            'support_items' => [['weighted_score' => 4.30]],
        ]],
    ]];

    $rows = (new SingleReportExport($assignment, $categoryItems))
        ->sheets()[0]
        ->array();

    expect($rows)
        ->toContain(['ผลรวมคะแนนถ่วงน้ำหนักสายสนับสนุน', 4.3])
        ->toContain(['คะแนนผลสัมฤทธิ์ของงาน', 0.86])
        ->toContain(['ความเห็นผู้ประเมิน', 'ความเห็นผู้ประเมิน'])
        ->toContain(['ความเห็นกรรมการ', 'ความเห็นกรรมการ'])
        ->toContain(['ความเห็นผู้บริหาร', 'ความเห็นผู้บริหาร']);
});

test('single report summary omits support rows when support criteria do not exist', function () {
    $report = Reports::factory()->create([
        'status' => 'Completed',
        'support_score_total' => 0,
        'support_achievement_score' => 0,
    ]);
    $assignment = Assignments::factory()->create(['report_id' => $report->id]);
    $assignment->load('report', 'assignmentData', 'evaluateeUser');
    $categoryItems = [[
        'sequence' => 1,
        'main_categories' => 'หมวด',
        'sub_categories' => 'ย่อย',
        'evaluation_lists' => [[
            'name' => 'รายการ',
            'sum_score' => 0,
            'quantity_items' => [],
            'quality_items' => [],
            'support_items' => [],
        ]],
    ]];

    $rows = (new SingleReportExport($assignment, $categoryItems))
        ->sheets()[0]
        ->array();
    $labels = collect($rows)->pluck(0);

    expect($labels)
        ->not->toContain('ผลรวมคะแนนถ่วงน้ำหนักสายสนับสนุน')
        ->not->toContain('คะแนนผลสัมฤทธิ์ของงาน');
});

test('single report category exports support details activities evidence and list total', function () {
    $report = Reports::factory()->create([
        'status' => 'Completed',
        'support_score_total' => 1,
        'support_achievement_score' => 0.2,
    ]);
    $assignment = Assignments::factory()->create(['report_id' => $report->id]);
    $assignment->load('report', 'assignmentData', 'evaluateeUser');
    $categoryItems = [[
        'sequence' => 1,
        'main_categories' => 'หมวด',
        'sub_categories' => 'ย่อย',
        'evaluation_lists' => [[
            'name' => 'รายการ',
            'sum_score' => 0,
            'quantity_items' => [],
            'quality_items' => [],
            'support_items' => [[
                'activity_name' => '<p>โครงการหนึ่ง</p>',
                'indicator' => '<p>ตัวชี้วัดหนึ่ง</p>',
                'target_value' => '5.00',
                'weight' => '20.00',
                'achieved_score' => '5.00',
                'weighted_score' => '1.00',
                'activity_entries' => [[
                    'content' => '<p>โครงการเพิ่มเติม</p>',
                    'evidence_links' => ['https://example.com/activity-evidence'],
                ]],
                'evidence_links' => [],
            ]],
        ]],
    ]];

    $rows = (new SingleReportExport($assignment, $categoryItems))
        ->sheets()[1]
        ->array();

    expect($rows)
        ->toContain(['หัวข้อ: รายการ', 1.0])
        ->toContain(['สายสนับสนุน: โครงการหนึ่ง', 1.0])
        ->toContain(['  ตัวชี้วัด', 'ตัวชี้วัดหนึ่ง'])
        ->toContain(['  ค่าเป้าหมาย', 5.0])
        ->toContain(['  น้ำหนัก', 20.0])
        ->toContain(['  คะแนนที่ทำได้', 5.0])
        ->toContain(['  คะแนนถ่วงน้ำหนัก', 1.0])
        ->toContain(['  กิจกรรม/โครงการเพิ่มเติม', 'โครงการเพิ่มเติม'])
        ->toContain(['    หลักฐาน', 'https://example.com/activity-evidence']);

    $activityRow = array_search(
        ['  กิจกรรม/โครงการเพิ่มเติม', 'โครงการเพิ่มเติม'],
        $rows,
        true
    );
    $evidenceRow = array_search(
        ['    หลักฐาน', 'https://example.com/activity-evidence'],
        $rows,
        true
    );

    expect($evidenceRow)->toBe($activityRow + 1);
});

test('single report export loads support criteria through the support read model', function () {
    $criteriaVersion = CriteriaVersion::factory()->create();
    $reportData = ReportData::factory()->create([
        'criteria_version_id' => $criteriaVersion->id,
    ]);
    $report = Reports::factory()->create([
        'report_data_id' => $reportData->id,
        'status' => 'Completed',
        'support_score_total' => 1,
        'support_achievement_score' => 0.2,
    ]);
    $category = Category::factory()->create([
        'criteria_version_id' => $criteriaVersion->id,
        'sequence' => 1,
    ]);
    $evaluationList = EvaluationList::factory()->create([
        'criteria_version_id' => $criteriaVersion->id,
        'categorie_id' => $category->id,
        'sequence' => 1,
    ]);
    $criterion = SupportCriteria::create([
        'evaluation_list_id' => $evaluationList->id,
        'sequence' => 1,
        'activity_name' => '<p>โครงการจากฐานข้อมูล</p>',
        'indicator' => '<p>ตัวชี้วัดจากฐานข้อมูล</p>',
        'target_value' => 5,
        'weight' => 20,
        'require_evidence' => true,
    ]);
    SupportScore::create([
        'report_id' => $report->id,
        'support_criteria_id' => $criterion->id,
        'achieved_score' => 5,
        'weighted_score' => 1,
    ]);
    EvidenceAnswer::create([
        'evaluation_list_id' => $evaluationList->id,
        'support_criteria_id' => $criterion->id,
        'report_id' => $report->id,
        'link' => 'https://example.com/database-evidence',
    ]);
    $assignment = Assignments::factory()->create(['report_id' => $report->id]);
    $assignment->load('report', 'assignmentData', 'evaluateeUser');

    $rows = (new SingleReportExport($assignment))
        ->sheets()[1]
        ->array();

    expect($rows)
        ->toContain(['สายสนับสนุน: โครงการจากฐานข้อมูล', 1.0])
        ->toContain(['  หลักฐาน', 'https://example.com/database-evidence']);
});
