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
use App\Models\SupportActivityEntry;
use App\Models\SupportCriteria;
use App\Models\SupportScore;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('dashboard export includes support score summaries and role comments', function () {
    $criteriaVersion = CriteriaVersion::factory()->create();
    $reportData = ReportData::factory()->create([
        'assessment_type' => 'กลุ่มสนับสนุน',
        'criteria_version_id' => $criteriaVersion->id,
    ]);
    $report = Reports::factory()->create([
        'report_data_id' => $reportData->id,
        'status' => 'Completed',
        'support_score_total' => 112.50,
        'support_achievement_score' => 22.50,
        'comment' => 'ความเห็นรวม',
        'evaluator_comment' => 'ความเห็นผู้ประเมิน',
        'director_comment' => 'ความเห็นกรรมการ',
        'manager_comment' => 'ความเห็นผู้บริหาร',
    ]);
    $category = Category::factory()->create([
        'criteria_version_id' => $criteriaVersion->id,
        'sequence' => 1,
    ]);
    $evaluationList = EvaluationList::factory()->create([
        'criteria_version_id' => $criteriaVersion->id,
        'categorie_id' => $category->id,
        'sequence' => 1,
        'quantity_enabled' => false,
        'sum_score' => 75,
    ]);
    $firstCriterion = SupportCriteria::create([
        'evaluation_list_id' => $evaluationList->id,
        'sequence' => 1,
        'activity_name' => 'เรื่อง',
        'indicator' => 'จำนวนเรื่อง',
        'target_value' => 5,
        'weight' => 20,
        'require_evidence' => false,
        'allow_activity_entries' => true,
        'allow_evaluatee_weight' => true,
    ]);
    $secondCriterion = SupportCriteria::create([
        'evaluation_list_id' => $evaluationList->id,
        'sequence' => 2,
        'activity_name' => 'งานด้านประกันคุณภาพ',
        'indicator' => 'ร้อยละ',
        'target_value' => 5,
        'weight' => 20,
        'require_evidence' => false,
    ]);
    $thirdCriterion = SupportCriteria::create([
        'evaluation_list_id' => $evaluationList->id,
        'sequence' => 3,
        'activity_name' => 'งานอื่นที่ได้รับมอบหมาย',
        'indicator' => 'ร้อยละ',
        'target_value' => 5,
        'weight' => 5,
        'require_evidence' => false,
    ]);
    SupportActivityEntry::create([
        'report_id' => $report->id,
        'support_criteria_id' => $firstCriterion->id,
        'sequence' => 1,
        'content' => 'งานในหน้าที่รายการที่หนึ่ง',
        'weight' => 20,
        'achieved_score' => 4,
        'weighted_score' => 7.5,
    ]);
    SupportScore::create([
        'report_id' => $report->id,
        'support_criteria_id' => $secondCriterion->id,
        'achieved_score' => 5,
        'weighted_score' => 2,
    ]);
    SupportScore::create([
        'report_id' => $report->id,
        'support_criteria_id' => $thirdCriterion->id,
        'achieved_score' => 5,
        'weighted_score' => 0.5,
    ]);
    $evaluatee = User::factory()->create(['personnel_type' => 'สนับสนุน']);
    $assignment = Assignments::factory()->create([
        'report_id' => $report->id,
        'evaluatee_id' => $evaluatee->id,
    ]);

    $export = new ReportsExport(
        Assignments::query()->where('report_id', $report->id)
    );
    $sheets = $export->sheets();
    $sheet = $sheets[2];
    $headings = $sheet->headings();
    $row = $sheet->collection()->first();

    expect($sheets)
        ->toHaveCount(3)
        ->and($sheets[0]->title())->toBe('สายอาจารย์')
        ->and($sheets[1]->title())->toBe('สายผู้บริหาร')
        ->and($sheet->title())->toBe('สายสนับสนุน')
        ->and($headings)
        ->toHaveCount(22)
        ->and($headings[12])->toBe('')
        ->and($headings)
        ->toContain('ผลรวมคะแนนถ่วงน้ำหนักสายสนับสนุน')
        ->toContain('คะแนนผลสัมฤทธิ์ของงาน')
        ->toContain('ความเห็นผู้ประเมิน')
        ->toContain('ความเห็นกรรมการ')
        ->toContain('ความเห็นผู้บริหาร')
        ->and($row[5])->toBe(2.5)
        ->and($row[6])->toBe(1.0)
        ->and($row[7])->toBe(0.25)
        ->and($row[13])->toBe(3.75)
        ->and($row[14])->toBe(0.75)
        ->and($row)->toContain('ความเห็นผู้ประเมิน', 'ความเห็นกรรมการ', 'ความเห็นผู้บริหาร');
});

test('overview export separates all personnel groups using report criteria before personnel type', function () {
    $reportIds = [];
    foreach ([
        ['academic-person', 'กลุ่มวิชาการ', 'บริหาร'],
        ['management-person', 'กลุ่มบริหาร', 'วิชาการ'],
        ['support-person', 'กลุ่มสนับสนุน', 'วิชาการ'],
        ['fallback-management-person', '', 'บริหาร'],
    ] as [$name, $assessmentType, $personnelType]) {
        $reportData = ReportData::factory()->create(['assessment_type' => $assessmentType]);
        $report = Reports::factory()->create(['report_data_id' => $reportData->id]);
        $user = User::factory()->create(['name' => $name, 'personnel_type' => $personnelType]);
        Assignments::factory()->create(['report_id' => $report->id, 'evaluatee_id' => $user->id]);
        $reportIds[] = $report->id;
    }

    $sheets = (new ReportsExport(Assignments::whereIn('report_id', $reportIds)))->sheets();
    expect(array_map(fn ($sheet) => $sheet->title(), $sheets))
        ->toBe(['สายอาจารย์', 'สายผู้บริหาร', 'สายสนับสนุน'])
        ->and($sheets[0]->collection()->pluck(2)->all())->toBe(['academic-person'])
        ->and($sheets[1]->collection()->pluck(2)->all())->toEqualCanonicalizing(['management-person', 'fallback-management-person'])
        ->and($sheets[2]->collection()->pluck(2)->all())->toBe(['support-person']);
});

test('academic dashboard export follows the template and keeps quantity and quality totals', function () {
    $criteriaVersion = CriteriaVersion::factory()->create();
    $reportData = ReportData::factory()->create([
        'assessment_type' => 'กลุ่มวิชาการ',
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
        'score_a' => 1.5,
    ]);
    $qualitySubCriteria = QualitySubCriteria::factory()->create([
        'criteria_version_id' => $criteriaVersion->id,
        'evaluation_list_id' => $evaluationList->id,
        'num_score' => 2,
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
    $evaluatee = User::factory()->create(['personnel_type' => 'วิชาการ']);
    $assignment = Assignments::factory()->create([
        'report_id' => $report->id,
        'evaluatee_id' => $evaluatee->id,
    ]);

    $sheet = (new ReportsExport(
        Assignments::query()->where('report_id', $report->id)
    ))->sheets()[0];
    $row = $sheet->collection()->first();

    expect($sheet->headings())
        ->toHaveCount(30)
        ->and($sheet->headings()[6])->toBe('1.1 ภาระงานด้านการสอน')
        ->and($sheet->headings()[13])->toBe('2.1 ภาระงานด้านการสอน')
        ->and($row[6])->toBe(1.5)
        ->and($row[13])->toBe(2.0)
        ->and($row[20])->toBe(7.8)
        ->and($row[21])->toBe(1.5)
        ->and($row[22])->toBe(2.0);
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
