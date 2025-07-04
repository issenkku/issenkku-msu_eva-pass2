<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Clear existing data
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Truncate tables in correct order
        DB::table('evaluators')->truncate();
        DB::table('assignments')->truncate();
        DB::table('evidence_answers')->truncate();
        DB::table('quality_scores')->truncate();
        DB::table('quantity_scores')->truncate();
        DB::table('reports')->truncate();
        DB::table('quality_sub_criterias')->truncate();
        DB::table('quality_main_criterias')->truncate();
        DB::table('quantity_sub_criterias')->truncate();
        DB::table('quantity_main_criterias')->truncate();
        DB::table('evaluation_lists')->truncate();
        DB::table('categories')->truncate();
        DB::table('report_datas')->truncate();
        DB::table('criteria_versions')->truncate();
        DB::table('user_histories')->truncate();
        DB::table('password_reset_tokens')->truncate();
        DB::table('users')->truncate();
        DB::table('positions')->truncate();
        DB::table('departments')->truncate();
        DB::table('settings')->truncate();
        
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

<<<<<<< HEAD
        // Seed Settings
        DB::table('settings')->insert([
            'id' => 1,
            'faculty' => 'คณะวิทยาศาสตร์',
            'university' => 'มหาวิทยาลยเทคนิคขอนแก่น'
=======
        $this->call([
            PositionSeeder::class,
            DepartmentSeeder::class,
            UserSeeder::class,
            RoleSeeder::class
>>>>>>> dev
        ]);

        // Seed Departments
        $departments = [
            [
                'id' => 1,
                'department_name' => 'ภาควิชาวิทยาการคอมพิวเตอร์',
                'faculty' => 'คณะวิทยาศาสตร์',
                'description' => 'ภาควิชาที่เกี่ยวข้องกับการพัฒนาซอฟต์แวร์และเทคนิคการคำนวณ'
            ],
            [
                'id' => 2,
                'department_name' => 'ภาควิชาคณิตศาสตร์',
                'faculty' => 'คณะวิทยาศาสตร์',
                'description' => 'ภาควิชาที่เกี่ยวข้องกับการศึกษาคณิตศาสตร์และสถิติ'
            ]
        ];
        DB::table('departments')->insert($departments);

        // Seed Positions
        $positions = [
            [
                'id' => 1,
                'name' => 'อาจารย์',
                'description' => 'ตำแหน่งอาจารย์ประจำ',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'id' => 2,
                'name' => 'ผู้ช่วยศาสตราจารย์',
                'description' => 'ตำแหน่งผู้ช่วยศาสตราจารย์',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'id' => 3,
                'name' => 'รองศาสตราจารย์',
                'description' => 'ตำแหน่งรองศาสตราจารย์',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'id' => 4,
                'name' => 'ศาสตราจารย์',
                'description' => 'ตำแหน่งศาสตราจารย์',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]
        ];
        DB::table('positions')->insert($positions);

        // Seed Users (4 people)
        $users = [
            [
                'id' => 1,
                'prefix' => 'ดร.',
                'name' => 'สมชาย ใจดี',
                'employee_id' => 'EMP001',
                'password' => Hash::make('password123'),
                'email' => 'somchai@kku.ac.th',
                'phone' => '081-234-5678',
                'personnel_type' => 'อาจารย์ประจำ',
                'bio' => 'ผู้เชี่ยวชาญด้านวิทยาการคอมพิวเตอร์ มีประสบการณ์ 10 ปี',
                'status' => 'active',
                'position_id' => 2,
                'department_id' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'id' => 2,
                'prefix' => 'ผศ.ดร.',
                'name' => 'สมหญิง วิทยาศาสตร์',
                'employee_id' => 'EMP002',
                'password' => Hash::make('password123'),
                'email' => 'somying@kku.ac.th',
                'phone' => '082-345-6789',
                'personnel_type' => 'อาจารย์ประจำ',
                'bio' => 'ผู้เชี่ยวชาญด้านคณิตศาสตร์ประยุกต์ มีประสบการณ์ 15 ปี',
                'status' => 'active',
                'position_id' => 2,
                'department_id' => 2,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'id' => 3,
                'prefix' => 'รศ.ดร.',
                'name' => 'วิชาญ เทคนิค',
                'employee_id' => 'EMP003',
                'password' => Hash::make('password123'),
                'email' => 'wichan@kku.ac.th',
                'phone' => '083-456-7890',
                'personnel_type' => 'อาจารย์ประจำ',
                'bio' => 'ผู้เชี่ยวชาญด้านปัญญาประดิษฐ์ มีประสบการณ์ 20 ปี',
                'status' => 'active',
                'position_id' => 3,
                'department_id' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'id' => 4,
                'prefix' => 'อ.',
                'name' => 'จิราพร นวัตกรรม',
                'employee_id' => 'EMP004',
                'password' => Hash::make('password123'),
                'email' => 'jiraporn@kku.ac.th',
                'phone' => '084-567-8901',
                'personnel_type' => 'อาจารย์ประจำ',
                'bio' => 'ผู้เชี่ยวชาญด้านสถิติและการวิเคราะห์ข้อมูล มีประสบการณ์ 8 ปี',
                'status' => 'active',
                'position_id' => 1,
                'department_id' => 2,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]
        ];
        DB::table('users')->insert($users);

        // Seed Criteria Version
        DB::table('criteria_versions')->insert([
            'id' => 1,
            'version_name' => 'เกณฑ์การประเมิน 2568',
            'created_by' => 1,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);

        // Seed Categories
        $categories = [
            [
                'id' => 1,
                'main_categories' => 'การเรียนการสอน',
                'sub_categories' => 'ภาระงานสอน',
                'sequence' => 1,
                'criteria_version_id' => 1
            ],
            [
                'id' => 2,
                'main_categories' => 'การวิจัย',
                'sub_categories' => 'ผลงานวิจัย',
                'sequence' => 2,
                'criteria_version_id' => 1
            ],
            [
                'id' => 3,
                'main_categories' => 'การบริการวิชาการ',
                'sub_categories' => 'การให้บริการสังคม',
                'sequence' => 3,
                'criteria_version_id' => 1
            ]
        ];
        DB::table('categories')->insert($categories);

        // Seed Report Data
        DB::table('report_datas')->insert([
            'id' => 1,
            'report_title' => 'แบบประเมินผลการปฏิบัติงานอาจารย์ประจำปี 2568',
            'report_description' => 'การประเมินผลการปฏิบัติงานของอาจารย์ในด้านการเรียนการสอน การวิจัย และการบริการวิชาการ',
            'assessment_type' => 'ประเมินรายปี',
            'comment' => 'ใช้สำหรับการประเมินประจำปี 2568',
            'criteria_version_id' => 1
        ]);

        // Seed Evaluation Lists
        $evaluationLists = [
            [
                'id' => 1,
                'name' => 'ชั่วโมงการสอน',
                'sum_score' => 40.00,
                'sequence' => 1,
                'annotation' => 'คะแนนรวมจากการสอน',
                'categorie_id' => 1,
                'criteria_version_id' => 1
            ],
            [
                'id' => 2,
                'name' => 'บทความวิจัย',
                'sum_score' => 35.00,
                'sequence' => 2,
                'annotation' => 'คะแนนรวมจากผลงานวิจัย',
                'categorie_id' => 2,
                'criteria_version_id' => 1
            ],
            [
                'id' => 3,
                'name' => 'การบริการสังคม',
                'sum_score' => 25.00,
                'sequence' => 3,
                'annotation' => 'คะแนนรวมจากการบริการวิชาการ',
                'categorie_id' => 3,
                'criteria_version_id' => 1
            ]
        ];
        DB::table('evaluation_lists')->insert($evaluationLists);

        // Seed Quantity Main Criteria
        $quantityMainCriterias = [
            [
                'id' => 1,
                'name' => 'ภาระงานสอนต่อปี',
                'tooltips' => 'จำนวนชั่วโมงการสอนต่อปีการศึกษา',
                'criteria_version_id' => 1
            ],
            [
                'id' => 2,
                'name' => 'ผลงานวิจัยต่อปี',
                'tooltips' => 'จำนวนผลงานวิจัยที่ตีพิมพ์ต่อปี',
                'criteria_version_id' => 1
            ]
        ];
        DB::table('quantity_main_criterias')->insert($quantityMainCriterias);

        // Seed Quantity Sub Criteria
        $quantitySubCriterias = [
            [
                'id' => 1,
                'name' => 'ชั่วโมงสอนภาคเรียนที่ 1',
                'sequence' => 1,
                'score_a' => 20.00,
                'score_b' => 15.00,
                'quantity_main_criteria_id' => 1,
                'criteria_version_id' => 1,
                'evaluation_list_id' => 1
            ],
            [
                'id' => 2,
                'name' => 'ชั่วโมงสอนภาคเรียนที่ 2',
                'sequence' => 2,
                'score_a' => 20.00,
                'score_b' => 15.00,
                'quantity_main_criteria_id' => 1,
                'criteria_version_id' => 1,
                'evaluation_list_id' => 1
            ],
            [
                'id' => 3,
                'name' => 'บทความวารสารระดับชาติ',
                'sequence' => 1,
                'score_a' => 15.00,
                'score_b' => 10.00,
                'quantity_main_criteria_id' => 2,
                'criteria_version_id' => 1,
                'evaluation_list_id' => 2
            ],
            [
                'id' => 4,
                'name' => 'บทความวารสารระดับนานาชาติ',
                'sequence' => 2,
                'score_a' => 20.00,
                'score_b' => 15.00,
                'quantity_main_criteria_id' => 2,
                'criteria_version_id' => 1,
                'evaluation_list_id' => 2
            ]
        ];
        DB::table('quantity_sub_criterias')->insert($quantitySubCriterias);

        // Seed Quality Main Criteria
        $qualityMainCriterias = [
            [
                'id' => 1,
                'name' => 'คุณภาพการสอน',
                'ratio' => 70,
                'tooltips' => 'การประเมินคุณภาพการสอนจากนักศึกษา',
                'sequence' => 1,
                'criteria_version_id' => 1
            ],
            [
                'id' => 2,
                'name' => 'คุณภาพการบริการวิชาการ',
                'ratio' => 30,
                'tooltips' => 'การประเมินคุณภาพการให้บริการวิชาการ',
                'sequence' => 2,
                'criteria_version_id' => 1
            ]
        ];
        DB::table('quality_main_criterias')->insert($qualityMainCriterias);

        // Seed Quality Sub Criteria
        $qualitySubCriterias = [
            [
                'id' => 1,
                'name' => 'ความพึงพอใจของนักศึกษา',
                'sequence' => 1,
                'num_score' => 4.50,
                'quality_main_criteria_id' => 1,
                'criteria_version_id' => 1,
                'evaluation_list_id' => 1
            ],
            [
                'id' => 2,
                'name' => 'การใช้สื่อการเรียนการสอน',
                'sequence' => 2,
                'num_score' => 4.00,
                'quality_main_criteria_id' => 1,
                'criteria_version_id' => 1,
                'evaluation_list_id' => 1
            ],
            [
                'id' => 3,
                'name' => 'ความพึงพอใจของผู้รับบริการ',
                'sequence' => 1,
                'num_score' => 4.20,
                'quality_main_criteria_id' => 2,
                'criteria_version_id' => 1,
                'evaluation_list_id' => 3
            ]
        ];
        DB::table('quality_sub_criterias')->insert($qualitySubCriterias);

        // Seed Reports
        $reports = [
            [
                'id' => 1,
                'report_data_id' => 1,
                'report_code' => 'RPT2568001',
                'status' => 'draft',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'id' => 2,
                'report_data_id' => 1,
                'report_code' => 'RPT2568002',
                'status' => 'in_progress',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]
        ];
        DB::table('reports')->insert($reports);

        // Seed Assignments
        $assignments = [
            [
                'id' => 1,
                'period' => '2568',
                'start_time' => Carbon::now()->format('Y-m-d'),
                'end_time' => Carbon::now()->addMonths(3)->format('Y-m-d'),
                'report_id' => 1,
                'evaluatee' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'id' => 2,
                'period' => '2568',
                'start_time' => Carbon::now()->format('Y-m-d'),
                'end_time' => Carbon::now()->addMonths(3)->format('Y-m-d'),
                'report_id' => 2,
                'evaluatee' => 2,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]
        ];
        DB::table('assignments')->insert($assignments);

        // Seed Evaluators
        $evaluators = [
            [
                'assignment_id' => 1,
                'user_id' => 3
            ],
            [
                'assignment_id' => 1,
                'user_id' => 4
            ],
            [
                'assignment_id' => 2,
                'user_id' => 1
            ],
            [
                'assignment_id' => 2,
                'user_id' => 3
            ]
        ];
        DB::table('evaluators')->insert($evaluators);

        // Seed Sample Quantity Scores
        $quantityScores = [
            [
                'quantity_sub_criteria_id' => 1,
                'report_id' => 1,
                'score_C' => 18.00,
                'score_D' => 16.00,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'quantity_sub_criteria_id' => 2,
                'report_id' => 1,
                'score_C' => 19.00,
                'score_D' => 17.00,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'quantity_sub_criteria_id' => 3,
                'report_id' => 1,
                'score_C' => 12.00,
                'score_D' => 10.00,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]
        ];
        DB::table('quantity_scores')->insert($quantityScores);

        // Seed Sample Quality Scores
        $qualityScores = [
            [
                'quality_sub_criteria_id' => 1,
                'report_id' => 1,
                'score' => 4.20,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'quality_sub_criteria_id' => 2,
                'report_id' => 1,
                'score' => 3.80,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'quality_sub_criteria_id' => 3,
                'report_id' => 1,
                'score' => 4.00,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]
        ];
        DB::table('quality_scores')->insert($qualityScores);

        // Seed Sample Evidence Answers
        $evidenceAnswers = [
            [
                'evaluation_list_id' => 1,
                'report_id' => 1,
                'link' => 'https://drive.google.com/file/teaching-evidence',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'evaluation_list_id' => 2,
                'report_id' => 1,
                'link' => 'https://drive.google.com/file/research-evidence',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'evaluation_list_id' => 3,
                'report_id' => 1,
                'link' => 'https://drive.google.com/file/service-evidence',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]
        ];
        DB::table('evidence_answers')->insert($evidenceAnswers);

        // Seed User Histories
        $userHistories = [
            [
                'user_id' => 1,
                'action' => 'เข้าสู่ระบบ',
                'action_timestamp' => Carbon::now()
            ],
            [
                'user_id' => 1,
                'action' => 'สร้างรายงานการประเมิน',
                'action_timestamp' => Carbon::now()
            ],
            [
                'user_id' => 2,
                'action' => 'เข้าสู่ระบบ',
                'action_timestamp' => Carbon::now()
            ],
            [
                'user_id' => 3,
                'action' => 'ประเมินผลงาน',
                'action_timestamp' => Carbon::now()
            ]
        ];
        DB::table('user_histories')->insert($userHistories);

        echo "✅ Database seeded successfully!\n";
        echo "👥 Created 4 users:\n";
        echo "   - สมชาย ใจดี (EMP001) - ผู้ช่วยศาสตราจารย์\n";
        echo "   - สมหญิง วิทยาศาสตร์ (EMP002) - ผู้ช่วยศาสตราจารย์\n";
        echo "   - วิชาญ เทคนิค (EMP003) - รองศาสตราจารย์\n";
        echo "   - จิราพร นวัตกรรม (EMP004) - อาจารย์\n";
        echo "🔑 Default password for all users: password123\n";
        echo "📊 Created sample evaluation data and reports\n";
        // User::factory(10)->create();

        $this->call([
            PositionSeeder::class,
            DepartmentSeeder::class,
            UserSeeder::class,
            RoleSeeder::class
        ]);
    }
}