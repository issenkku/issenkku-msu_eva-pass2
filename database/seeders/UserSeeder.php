<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // สร้างข้อมูล departments ก่อน
        $departments = [
            ['id' => 1, 'department_name' => 'คณะวิศวกรรมศาสตร์', 'faculty' => 'วิศวกรรมศาสตร์'],
            ['id' => 2, 'department_name' => 'คณะครุศาสตร์', 'faculty' => 'ครุศาสตร์'],
            ['id' => 3, 'department_name' => 'คณะเทคโนโลยี', 'faculty' => 'เทคโนโลยี'],
        ];

        foreach ($departments as $department) {
            DB::table('departments')->insertOrIgnore($department);
        }

        // สร้างข้อมูล positions ก่อน
        $positions = [
            ['id' => 1, 'name' => 'อาจารย์', 'description' => 'อาจารย์ประจำ', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'name' => 'ผู้ช่วยศาสตราจารย์', 'description' => 'ผู้ช่วยศาสตราจารย์', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'name' => 'รองศาสตราจารย์', 'description' => 'รองศาสตราจารย์', 'created_at' => now(), 'updated_at' => now()],
        ];

        foreach ($positions as $position) {
            DB::table('positions')->insertOrIgnore($position);
        }

        // สร้างข้อมูล settings
        DB::table('settings')->insertOrIgnore([
            'id' => 1,
            'faculty' => 'มหาวิทยาลัยเทคโนโลยีราชมงคล',
            'university' => 'มหาวิทยาลัยเทคโนโลยีราชมงคลอีสาน'
        ]);
    }

    private function seedCriteriaVersions()
    {
        $criteriaVersions = [
            [
                'id' => 1,
                'version_name' => 'เกณฑ์การประเมิน ปีการศึกษา 2568',
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        foreach ($criteriaVersions as $version) {
            DB::table('criteria_versions')->insertOrIgnore($version);
        }
    }

    private function seedQuantityCriteria()
    {
        // Quantity Main Criteria
        $quantityMainCriterias = [
            ['id' => 1, 'name' => 'ด้านการเรียนการสอน', 'criteria_version_id' => 1],
            ['id' => 2, 'name' => 'ด้านการวิจัยและงานสร้างสรรค์', 'criteria_version_id' => 1],
            ['id' => 3, 'name' => 'ด้านการบริการวิชาการ', 'criteria_version_id' => 1],
        ];

        foreach ($quantityMainCriterias as $criteria) {
            DB::table('quantity_main_criterias')->insertOrIgnore($criteria);
        }

        // Quantity Sub Criteria
        $quantitySubCriterias = [
            // การเรียนการสอน
            ['id' => 1, 'name' => 'ภาระงานสอนต่อปี', 'sequence' => 1, 'score_A' => 100.00, 'score_B' => 80.00, 'quantity_main_criteria_id' => 1, 'criteria_version_id' => 1],
            ['id' => 2, 'name' => 'การพัฒนาหลักสูตร', 'sequence' => 2, 'score_A' => 90.00, 'score_B' => 70.00, 'quantity_main_criteria_id' => 1, 'criteria_version_id' => 1],
            
            // การวิจัย
            ['id' => 3, 'name' => 'บทความวิจัยที่ตีพิมพ์', 'sequence' => 1, 'score_A' => 100.00, 'score_B' => 85.00, 'quantity_main_criteria_id' => 2, 'criteria_version_id' => 1],
            ['id' => 4, 'name' => 'โครงการวิจัยที่ได้รับทุน', 'sequence' => 2, 'score_A' => 95.00, 'score_B' => 75.00, 'quantity_main_criteria_id' => 2, 'criteria_version_id' => 1],
            
            // การบริการวิชาการ
            ['id' => 5, 'name' => 'โครงการบริการวิชาการแก่สังคม', 'sequence' => 1, 'score_A' => 80.00, 'score_B' => 60.00, 'quantity_main_criteria_id' => 3, 'criteria_version_id' => 1],
        ];

        foreach ($quantitySubCriterias as $criteria) {
            DB::table('quantity_sub_criterias')->insertOrIgnore($criteria);
        }
    }

    private function seedQualityCriteria()
    {
        // Quality Main Criteria
        $qualityMainCriterias = [
            ['id' => 1, 'name' => 'คุณภาพการสอน', 'ratio' => 40, 'sequence' => 1, 'criteria_version_id' => 1],
            ['id' => 2, 'name' => 'คุณภาพงานวิจัย', 'ratio' => 35, 'sequence' => 2, 'criteria_version_id' => 1],
            ['id' => 3, 'name' => 'คุณภาพการบริการ', 'ratio' => 25, 'sequence' => 3, 'criteria_version_id' => 1],
        ];

        foreach ($qualityMainCriterias as $criteria) {
            DB::table('quality_main_criterias')->insertOrIgnore($criteria);
        }

        // Quality Sub Criteria
        $qualitySubCriterias = [
            // คุณภาพการสอน
            ['id' => 1, 'name' => 'ความพึงพอใจของนักศึกษา', 'sequence' => 1, 'num_score' => 4.50, 'quality_main_criteria_id' => 1, 'criteria_version_id' => 1],
            ['id' => 2, 'name' => 'การใช้เทคโนโลยีในการสอน', 'sequence' => 2, 'num_score' => 4.00, 'quality_main_criteria_id' => 1, 'criteria_version_id' => 1],
            
            // คุณภาพงานวิจัย
            ['id' => 3, 'name' => 'ผลกระทบของงานวิจัย (Impact Factor)', 'sequence' => 1, 'num_score' => 3.80, 'quality_main_criteria_id' => 2, 'criteria_version_id' => 1],
            ['id' => 4, 'name' => 'การประยุกต์ใช้งานวิจัย', 'sequence' => 2, 'num_score' => 4.20, 'quality_main_criteria_id' => 2, 'criteria_version_id' => 1],
            
            // คุณภาพการบริการ
            ['id' => 5, 'name' => 'ความต่อเนื่องของโครงการบริการ', 'sequence' => 1, 'num_score' => 3.90, 'quality_main_criteria_id' => 3, 'criteria_version_id' => 1],
        ];

        foreach ($qualitySubCriterias as $criteria) {
            DB::table('quality_sub_criterias')->insertOrIgnore($criteria);
        }
    }

    private function seedCategoriesAndEvaluationLists()
    {
        // Categories
        $categories = [
            ['id' => 1, 'main_categories' => 'การเรียนการสอน', 'sub_categories' => 'การวางแผนการสอน', 'sequence' => 1, 'criteria_version_id' => 1],
            ['id' => 2, 'main_categories' => 'การเรียนการสอน', 'sub_categories' => 'การดำเนินการสอน', 'sequence' => 2, 'criteria_version_id' => 1],
            ['id' => 3, 'main_categories' => 'การวิจัย', 'sub_categories' => 'การตีพิมพ์ผลงาน', 'sequence' => 3, 'criteria_version_id' => 1],
            ['id' => 4, 'main_categories' => 'การบริการ', 'sub_categories' => 'การบริการสังคม', 'sequence' => 4, 'criteria_version_id' => 1],
        ];

        foreach ($categories as $category) {
            DB::table('categories')->insertOrIgnore($category);
        }

        // Evaluation Lists
        $evaluationLists = [
            // การเรียนการสอน - การวางแผน
            ['id' => 1, 'name' => 'มีแผนการสอนรายวิชาที่ชัดเจน', 'sum_score' => 20.00, 'sequence' => 1, 'annotation' => 'ประเมินจากความชัดเจนของแผนการสอน', 'categorie_id' => 1, 'criteria_version_id' => 1],
            ['id' => 2, 'name' => 'กำหนดวัตถุประสงค์การเรียนรู้ที่วัดได้', 'sum_score' => 15.00, 'sequence' => 2, 'annotation' => 'ประเมินจากความเฉพาะเจาะจงของวัตถุประสงค์', 'categorie_id' => 1, 'criteria_version_id' => 1],
            
            // การเรียนการสอน - การดำเนินการ
            ['id' => 3, 'name' => 'ใช้วิธีการสอนที่หลากหลาย', 'sum_score' => 18.00, 'sequence' => 1, 'annotation' => 'ประเมินจากความหลากหลายของวิธีการสอน', 'categorie_id' => 2, 'criteria_version_id' => 1],
            ['id' => 4, 'name' => 'ให้ข้อมูลป้อนกลับแก่นักศึกษา', 'sum_score' => 17.00, 'sequence' => 2, 'annotation' => 'ประเมินจากคุณภาพของ feedback', 'categorie_id' => 2, 'criteria_version_id' => 1],
            
            // การวิจัย
            ['id' => 5, 'name' => 'ตีพิมพ์บทความในวารสารระดับนานาชาติ', 'sum_score' => 25.00, 'sequence' => 1, 'annotation' => 'ประเมินจากจำนวนและคุณภาพของบทความ', 'categorie_id' => 3, 'criteria_version_id' => 1],
            ['id' => 6, 'name' => 'นำเสนอผลงานในที่ประชุมวิชาการ', 'sum_score' => 15.00, 'sequence' => 2, 'annotation' => 'ประเมินจากการเข้าร่วมประชุมวิชาการ', 'categorie_id' => 3, 'criteria_version_id' => 1],
            
            // การบริการ
            ['id' => 7, 'name' => 'เป็นวิทยากรให้ความรู้แก่ชุมชน', 'sum_score' => 12.00, 'sequence' => 1, 'annotation' => 'ประเมินจากจำนวนครั้งและผลกระทบ', 'categorie_id' => 4, 'criteria_version_id' => 1],
            ['id' => 8, 'name' => 'เป็นที่ปรึกษาโครงการชุมชน', 'sum_score' => 13.00, 'sequence' => 2, 'annotation' => 'ประเมินจากความต่อเนื่องของการให้คำปรึกษา', 'categorie_id' => 4, 'criteria_version_id' => 1],
        ];

        foreach ($evaluationLists as $evaluation) {
            DB::table('evaluation_lists')->insertOrIgnore($evaluation);
        }
    }

    private function seedReports()
    {
        // Report Data
        $reportDatas = [
            [
                'id' => 1,
                'report_title' => 'รายงานการประเมินผลการปฏิบัติงาน ประจำปีการศึกษา 2568',
                'report_description' => 'การประเมินผลการปฏิบัติงานของอาจารย์ในด้านการเรียนการสอน การวิจัย และการบริการวิชาการ',
                'assessment_type' => 'ประเมินรายปี',
                'comment' => 'การประเมินครั้งนี้เน้นการพัฒนาคุณภาพการศึกษาและการวิจัย',
                'criteria_version_id' => 1
            ]
        ];

        foreach ($reportDatas as $data) {
            DB::table('report_datas')->insertOrIgnore($data);
        }

        // Reports
        $reports = [
            [
                'id' => 1,
                'report_code' => 'RPT2568001',
                'status' => 'pending',
                'report_data_id' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 2,
                'report_code' => 'RPT2568002',
                'status' => 'in_progress',
                'report_data_id' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 3,
                'report_code' => 'RPT2568003',
                'status' => 'completed',
                'report_data_id' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        foreach ($reports as $report) {
            DB::table('reports')->insertOrIgnore($report);
        }
    }

    private function seedAssignments()
    {
        // Assignments
        $assignments = [
            [
                'id' => 1,
                'period' => 'ปีการศึกษา 2568/1',
                'start_time' => '2025-06-01',
                'end_time' => '2025-08-31',
                'report_id' => 1,
                'evaluatee' => 1, // ดร.สมชาย
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 2,
                'period' => 'ปีการศึกษา 2568/1',
                'start_time' => '2025-06-01',
                'end_time' => '2025-08-31',
                'report_id' => 2,
                'evaluatee' => 2, // ผศ.ดร.สุวิทย์
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 3,
                'period' => 'ปีการศึกษา 2568/1',
                'start_time' => '2025-06-01',
                'end_time' => '2025-08-31',
                'report_id' => 3,
                'evaluatee' => 3, // รศ.ดร.มาลี
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        foreach ($assignments as $assignment) {
            DB::table('assignments')->insertOrIgnore($assignment);
        }

        // Evaluators (ผู้ประเมิน)
        $evaluators = [
            // สำหรับ assignment 1 (ดร.สมชาย ถูกประเมินโดย ผศ.ดร.สุวิทย์ และ รศ.ดร.มาลี)
            ['assignment_id' => 1, 'user_id' => 2],
            ['assignment_id' => 1, 'user_id' => 3],
            
            // สำหรับ assignment 2 (ผศ.ดร.สุวิทย์ ถูกประเมินโดย ดร.สมชาย และ รศ.ดร.มาลี)
            ['assignment_id' => 2, 'user_id' => 1],
            ['assignment_id' => 2, 'user_id' => 3],
            
            // สำหรับ assignment 3 (รศ.ดร.มาลี ถูกประเมินโดย ดร.สมชาย และ ผศ.ดร.สุวิทย์)
            ['assignment_id' => 3, 'user_id' => 1],
            ['assignment_id' => 3, 'user_id' => 2],
        ];

        foreach ($evaluators as $evaluator) {
            DB::table('evaluators')->insertOrIgnore($evaluator);
        }

        // เพิ่มคะแนนตัวอย่าง (Sample Scores)
        $this->seedSampleScores();
    }

    private function seedSampleScores()
    {
        // Quantity Scores ตัวอย่าง
        $quantityScores = [
            // Report 1 - ดร.สมชาย
            ['score_C' => 85.00, 'score_D' => 90.00, 'quantity_sub_criteria_id' => 1, 'evaluation_list_id' => 1, 'report_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['score_C' => 80.00, 'score_D' => 85.00, 'quantity_sub_criteria_id' => 2, 'evaluation_list_id' => 2, 'report_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            
            // Report 2 - ผศ.ดร.สุวิทย์
            ['score_C' => 90.00, 'score_D' => 95.00, 'quantity_sub_criteria_id' => 1, 'evaluation_list_id' => 3, 'report_id' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['score_C' => 88.00, 'score_D' => 92.00, 'quantity_sub_criteria_id' => 3, 'evaluation_list_id' => 5, 'report_id' => 2, 'created_at' => now(), 'updated_at' => now()],
        ];

        foreach ($quantityScores as $score) {
            DB::table('quantity_scores')->insertOrIgnore($score);
        }

        // Quality Scores ตัวอย่าง
        $qualityScores = [
            // Report 1
            ['score' => 4.20, 'quality_sub_criteria_id' => 1, 'evaluation_list_id' => 1, 'report_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['score' => 3.80, 'quality_sub_criteria_id' => 2, 'evaluation_list_id' => 2, 'report_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            
            // Report 2
            ['score' => 4.50, 'quality_sub_criteria_id' => 1, 'evaluation_list_id' => 3, 'report_id' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['score' => 4.10, 'quality_sub_criteria_id' => 3, 'evaluation_list_id' => 5, 'report_id' => 2, 'created_at' => now(), 'updated_at' => now()],
        ];

        foreach ($qualityScores as $score) {
            DB::table('quality_scores')->insertOrIgnore($score);
        }

        // Evidence Answers ตัวอย่าง
        $evidenceAnswers = [
            ['link' => 'https://drive.google.com/file/d/teaching-plan-2568', 'evaluation_list_id' => 1, 'report_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['link' => 'https://drive.google.com/file/d/learning-objectives-2568', 'evaluation_list_id' => 2, 'report_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['link' => 'https://drive.google.com/file/d/teaching-methods-2568', 'evaluation_list_id' => 3, 'report_id' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['link' => 'https://drive.google.com/file/d/research-publication-2568', 'evaluation_list_id' => 5, 'report_id' => 2, 'created_at' => now(), 'updated_at' => now()],
        ];

        foreach ($evidenceAnswers as $evidence) {
            DB::table('evidence_answers')->insertOrIgnore($evidence);
        }
    }
}