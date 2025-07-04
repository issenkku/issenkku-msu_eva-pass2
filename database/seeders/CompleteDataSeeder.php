<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CompleteDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Settings
        DB::table('settings')->insert([
            [
                'faculty' => 'คณะสาธารณสุข',
                'university' => 'มหาวิทยาลัยขอนแก่น',
            ]
        ]);

        // 2. Criteria Versions
        DB::table('criteria_versions')->insert([
            [
                'id' => 1,
                'version_name' => 'เกณฑ์การประเมิน ปี 2568',
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'version_name' => 'เกณฑ์การประเมิน ปี 2569',
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);

        // 3. Report Datas
        DB::table('report_datas')->insert([
            [
                'id' => 1,
                'report_title' => 'รายงานการประเมินคุณภาพการปฏิบัติงานบุคลากรสายวิชาการ',
                'report_description' => 'การประเมินคุณภาพการปฏิบัติงานของบุคลากรสายวิชาการ ประจำปีงบประมาณ 2568',
                'assessment_type' => 'วิชาการ',
                'comment' => 'ประเมินตามเกณฑ์มาตรฐานของมหาวิทยาลัย',
                'criteria_version_id' => 1,
            ],
            [
                'id' => 2,
                'report_title' => 'รายงานการประเมินคุณภาพการปฏิบัติงานบุคลากรสายสนับสนุน',
                'report_description' => 'การประเมินคุณภาพการปฏิบัติงานของบุคลากรสายสนับสนุน ประจำปีงบประมาณ 2568',
                'assessment_type' => 'สนับสนุน',
                'comment' => 'ประเมินตามเกณฑ์มาตรฐานของมหาวิทยาลัย',
                'criteria_version_id' => 1,
            ]
        ]);

        // 4. Categories
        DB::table('categories')->insert([
            [
                'id' => 1,
                'main_categories' => 'ด้านการจัดการเรียนการสอน',
                'sub_categories' => 'การวางแผนการเรียนการสอน',
                'sequence' => 1,
                'criteria_version_id' => 1,
            ],
            [
                'id' => 2,
                'main_categories' => 'ด้านการจัดการเรียนการสอน',
                'sub_categories' => 'การดำเนินการเรียนการสอน',
                'sequence' => 2,
                'criteria_version_id' => 1,
            ],
            [
                'id' => 3,
                'main_categories' => 'ด้านการวิจัย',
                'sub_categories' => 'การทำวิจัย',
                'sequence' => 3,
                'criteria_version_id' => 1,
            ],
            [
                'id' => 4,
                'main_categories' => 'ด้านการบริการวิชาการ',
                'sub_categories' => 'การบริการวิชาการแก่สังคม',
                'sequence' => 4,
                'criteria_version_id' => 1,
            ],
            [
                'id' => 5,
                'main_categories' => 'ด้านการบริหารจัดการ',
                'sub_categories' => 'การบริหารจัดการงาน',
                'sequence' => 1,
                'criteria_version_id' => 1,
            ],
            [
                'id' => 6,
                'main_categories' => 'ด้านการพัฒนาตนเอง',
                'sub_categories' => 'การพัฒนาความรู้และทักษะ',
                'sequence' => 2,
                'criteria_version_id' => 1,
            ]
        ]);

        // 5. Evaluation Lists
        DB::table('evaluation_lists')->insert([
            [
                'id' => 1,
                'name' => 'การจัดทำแผนการเรียนรู้',
                'sum_score' => 20.00,
                'sequence' => 1,
                'annotation' => 'ประเมินการจัดทำแผนการเรียนรู้ที่สอดคล้องกับหลักสูตร',
                'categorie_id' => 1,
                'criteria_version_id' => 1,
            ],
            [
                'id' => 2,
                'name' => 'การใช้เทคโนโลยีในการเรียนการสอน',
                'sum_score' => 15.00,
                'sequence' => 2,
                'annotation' => 'ประเมินการใช้เทคโนโลยีเพื่อเสริมการเรียนรู้',
                'categorie_id' => 2,
                'criteria_version_id' => 1,
            ],
            [
                'id' => 3,
                'name' => 'การทำวิจัยเชิงสร้างสรรค์',
                'sum_score' => 25.00,
                'sequence' => 3,
                'annotation' => 'ประเมินการทำวิจัยที่สร้างประโยชน์ต่อสังคม',
                'categorie_id' => 3,
                'criteria_version_id' => 1,
            ],
            [
                'id' => 4,
                'name' => 'การให้บริการวิชาการแก่ชุมชน',
                'sum_score' => 20.00,
                'sequence' => 4,
                'annotation' => 'ประเมินการให้บริการวิชาการแก่ชุมชนและสังคม',
                'categorie_id' => 4,
                'criteria_version_id' => 1,
            ],
            [
                'id' => 5,
                'name' => 'การบริหารจัดการงานประจำ',
                'sum_score' => 30.00,
                'sequence' => 1,
                'annotation' => 'ประเมินการบริหารจัดการงานประจำอย่างมีประสิทธิภาพ',
                'categorie_id' => 5,
                'criteria_version_id' => 1,
            ],
            [
                'id' => 6,
                'name' => 'การพัฒนาความรู้และทักษะใหม่',
                'sum_score' => 15.00,
                'sequence' => 2,
                'annotation' => 'ประเมินการพัฒนาตนเองในด้านความรู้และทักษะ',
                'categorie_id' => 6,
                'criteria_version_id' => 1,
            ]
        ]);

        // 6. Quantity Main Criterias
        DB::table('quantity_main_criterias')->insert([
            [
                'id' => 1,
                'name' => 'จำนวนงานวิจัยที่ตีพิมพ์',
                'tooltips' => 'นับจากผลงานวิจัยที่ได้รับการตีพิมพ์ในวารสารระดับชาติและนานาชาติ',
                'criteria_version_id' => 1,
            ],
            [
                'id' => 2,
                'name' => 'จำนวนชั่วโมงการสอน',
                'tooltips' => 'นับจากจำนวนชั่วโมงการสอนจริงในแต่ละภาคการศึกษา',
                'criteria_version_id' => 1,
            ],
            [
                'id' => 3,
                'name' => 'จำนวนโครงการบริการวิชาการ',
                'tooltips' => 'นับจากโครงการบริการวิชาการที่ได้รับการอนุมัติและดำเนินการแล้วเสร็จ',
                'criteria_version_id' => 1,
            ]
        ]);

        // 7. Quantity Sub Criterias
        DB::table('quantity_sub_criterias')->insert([
            [
                'id' => 1,
                'name' => 'วารสารระดับชาติ',
                'sequence' => 1,
                'score_a' => 5.00,
                'score_b' => 3.00,
                'quantity_main_criteria_id' => 1,
                'criteria_version_id' => 1,
                'evaluation_list_id' => 3,
            ],
            [
                'id' => 2,
                'name' => 'วารสารระดับนานาชาติ',
                'sequence' => 2,
                'score_a' => 10.00,
                'score_b' => 7.00,
                'quantity_main_criteria_id' => 1,
                'criteria_version_id' => 1,
                'evaluation_list_id' => 3,
            ],
            [
                'id' => 3,
                'name' => 'การสอนภาคปกติ',
                'sequence' => 1,
                'score_a' => 2.00,
                'score_b' => 1.50,
                'quantity_main_criteria_id' => 2,
                'criteria_version_id' => 1,
                'evaluation_list_id' => 1,
            ],
            [
                'id' => 4,
                'name' => 'การสอนภาคพิเศษ',
                'sequence' => 2,
                'score_a' => 1.50,
                'score_b' => 1.00,
                'quantity_main_criteria_id' => 2,
                'criteria_version_id' => 1,
                'evaluation_list_id' => 1,
            ],
            [
                'id' => 5,
                'name' => 'โครงการระดับชุมชน',
                'sequence' => 1,
                'score_a' => 3.00,
                'score_b' => 2.00,
                'quantity_main_criteria_id' => 3,
                'criteria_version_id' => 1,
                'evaluation_list_id' => 4,
            ],
            [
                'id' => 6,
                'name' => 'โครงการระดับประเทศ',
                'sequence' => 2,
                'score_a' => 5.00,
                'score_b' => 3.50,
                'quantity_main_criteria_id' => 3,
                'criteria_version_id' => 1,
                'evaluation_list_id' => 4,
            ]
        ]);

        // 8. Quality Main Criterias
        DB::table('quality_main_criterias')->insert([
            [
                'id' => 1,
                'name' => 'คุณภาพการสอน',
                'ratio' => 40,
                'tooltips' => 'ประเมินจากแบบประเมินของนักศึกษาและการสังเกตการสอน',
                'sequence' => 1,
                'criteria_version_id' => 1,
            ],
            [
                'id' => 2,
                'name' => 'คุณภาพงานวิจัย',
                'ratio' => 30,
                'tooltips' => 'ประเมินจากคุณภาพของงานวิจัยและผลกระทบต่อสังคม',
                'sequence' => 2,
                'criteria_version_id' => 1,
            ],
            [
                'id' => 3,
                'name' => 'คุณภาพการบริการวิชาการ',
                'ratio' => 20,
                'tooltips' => 'ประเมินจากความพึงพอใจของผู้รับบริการและผลลัพธ์ของโครงการ',
                'sequence' => 3,
                'criteria_version_id' => 1,
            ],
            [
                'id' => 4,
                'name' => 'คุณภาพการบริหารจัดการ',
                'ratio' => 10,
                'tooltips' => 'ประเมินจากประสิทธิภาพในการบริหารจัดการงานที่ได้รับมอบหมาย',
                'sequence' => 4,
                'criteria_version_id' => 1,
            ]
        ]);

        // 9. Quality Sub Criterias
        DB::table('quality_sub_criterias')->insert([
            [
                'id' => 1,
                'name' => 'ความพึงพอใจของนักศึกษา',
                'sequence' => 1,
                'num_score' => 4.00,
                'quality_main_criteria_id' => 1,
                'criteria_version_id' => 1,
                'evaluation_list_id' => 1,
            ],
            [
                'id' => 2,
                'name' => 'การใช้นวัตกรรมการสอน',
                'sequence' => 2,
                'num_score' => 3.50,
                'quality_main_criteria_id' => 1,
                'criteria_version_id' => 1,
                'evaluation_list_id' => 2,
            ],
            [
                'id' => 3,
                'name' => 'ความเป็นต้นฉบับของงานวิจัย',
                'sequence' => 1,
                'num_score' => 4.50,
                'quality_main_criteria_id' => 2,
                'criteria_version_id' => 1,
                'evaluation_list_id' => 3,
            ],
            [
                'id' => 4,
                'name' => 'ผลกระทบต่อสังคม',
                'sequence' => 2,
                'num_score' => 4.00,
                'quality_main_criteria_id' => 2,
                'criteria_version_id' => 1,
                'evaluation_list_id' => 3,
            ],
            [
                'id' => 5,
                'name' => 'ความพึงพอใจของผู้รับบริการ',
                'sequence' => 1,
                'num_score' => 4.20,
                'quality_main_criteria_id' => 3,
                'criteria_version_id' => 1,
                'evaluation_list_id' => 4,
            ],
            [
                'id' => 6,
                'name' => 'ประสิทธิภาพการบริหารจัดการ',
                'sequence' => 1,
                'num_score' => 3.80,
                'quality_main_criteria_id' => 4,
                'criteria_version_id' => 1,
                'evaluation_list_id' => 5,
            ]
        ]);

        // 10. Reports
        DB::table('reports')->insert([
            [
                'id' => 1,
                'report_data_id' => 1,
                'status' => 'draft',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'report_data_id' => 2,
                'status' => 'submitted',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'report_data_id' => 1,
                'status' => 'approved',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);

        // 11. Assignment Datas
        DB::table('assignment_datas')->insert([
            [
                'id' => 1,
                'start_time' => '2024-01-01',
                'end_time' => '2024-12-31',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'start_time' => '2024-06-01',
                'end_time' => '2024-11-30',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);

        // 12. Assignments
        DB::table('assignments')->insert([
            [
                'assignment_data_id' => 1,
                'report_id' => 1,
                'evaluatee' => 1,
                'evaluator' => 2,
            ],
            [
                'assignment_data_id' => 1,
                'report_id' => 2,
                'evaluatee' => 2,
                'evaluator' => 1,
            ],
            [
                'assignment_data_id' => 2,
                'report_id' => 3,
                'evaluatee' => 1,
                'evaluator' => 2,
            ]
        ]);

        // 13. Quantity Scores
        DB::table('quantity_scores')->insert([
            [
                'quantity_sub_criteria_id' => 1,
                'report_id' => 1,
                'score_C' => 2.00,
                'score_D' => 10.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'quantity_sub_criteria_id' => 2,
                'report_id' => 1,
                'score_C' => 1.00,
                'score_D' => 10.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'quantity_sub_criteria_id' => 3,
                'report_id' => 1,
                'score_C' => 120.00,
                'score_D' => 240.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'quantity_sub_criteria_id' => 4,
                'report_id' => 1,
                'score_C' => 60.00,
                'score_D' => 90.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'quantity_sub_criteria_id' => 5,
                'report_id' => 1,
                'score_C' => 3.00,
                'score_D' => 6.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'quantity_sub_criteria_id' => 6,
                'report_id' => 1,
                'score_C' => 1.00,
                'score_D' => 5.00,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);

        // 14. Quality Scores
        DB::table('quality_scores')->insert([
            [
                'quality_sub_criteria_id' => 1,
                'report_id' => 1,
                'score' => 4.20,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'quality_sub_criteria_id' => 2,
                'report_id' => 1,
                'score' => 3.80,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'quality_sub_criteria_id' => 3,
                'report_id' => 1,
                'score' => 4.50,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'quality_sub_criteria_id' => 4,
                'report_id' => 1,
                'score' => 4.10,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'quality_sub_criteria_id' => 5,
                'report_id' => 1,
                'score' => 4.30,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'quality_sub_criteria_id' => 6,
                'report_id' => 1,
                'score' => 3.90,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);

        // 15. Evidence Answers
        DB::table('evidence_answers')->insert([
            [
                'evaluation_list_id' => 1,
                'report_id' => 1,
                'link' => 'https://drive.google.com/file/d/1ABC123/view',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'evaluation_list_id' => 2,
                'report_id' => 1,
                'link' => 'https://drive.google.com/file/d/1DEF456/view',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'evaluation_list_id' => 3,
                'report_id' => 1,
                'link' => 'https://drive.google.com/file/d/1GHI789/view',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'evaluation_list_id' => 4,
                'report_id' => 1,
                'link' => 'https://drive.google.com/file/d/1JKL012/view',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'evaluation_list_id' => 5,
                'report_id' => 2,
                'link' => 'https://drive.google.com/file/d/1MNO345/view',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'evaluation_list_id' => 6,
                'report_id' => 2,
                'link' => 'https://drive.google.com/file/d/1PQR678/view',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);

        // 16. User Histories
        DB::table('user_histories')->insert([
            [
                'user_id' => 1,
                'action' => 'เข้าสู่ระบบ',
                'action_timestamp' => now(),
            ],
            [
                'user_id' => 1,
                'action' => 'สร้างรายงานการประเมิน',
                'action_timestamp' => now()->subHours(2),
            ],
            [
                'user_id' => 2,
                'action' => 'เข้าสู่ระบบ',
                'action_timestamp' => now()->subHours(1),
            ],
            [
                'user_id' => 2,
                'action' => 'ส่งรายงานการประเมิน',
                'action_timestamp' => now()->subMinutes(30),
            ],
            [
                'user_id' => 1,
                'action' => 'อนุมัติรายงานการประเมิน',
                'action_timestamp' => now()->subMinutes(15),
            ]
        ]);
    }
}