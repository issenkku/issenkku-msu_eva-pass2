<?php


namespace Tests\Feature\Report;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use App\Models\Category;
use App\Models\CriteriaVersion;
use App\Models\EvaluationList;
use App\Models\QualityMainCriteria;
use App\Models\QualitySubCriteria;
use App\Models\QuantityMainCriteria;
use App\Models\QuantitySubCriteria;
use App\Models\ReportData;
use App\Models\Setting\Departments;
use App\Models\Setting\Positions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ReportStructureTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Departments $department;
    protected Positions $position;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'admin']);
        Role::create(['name' => 'staff']);

        // Create departments and positions that will be globally available in the test DB
        $this->department = \Database\Factories\DepartmentFactory::new()->create();
        $this->position = \Database\Factories\PositionFactory::new()->create();

        // Create admin user using above department and position
        $this->admin = User::factory()->create([
            'employee_id'   => 'ADMIN001',
            'name' => 'Test User',
            'password'      => Hash::make('password'),
            'status'        => 'active',
            'department_id' => $this->department->id,
            'position_id'   => $this->position->id,
        ]);
        $this->admin->assignRole('admin');
        $this->actingAs($this->admin, 'web');
    }

    public function test_can_show_list_of_criteria_versions()
    {
        // Arrange: Create test data
        $criteriaVersion1 = CriteriaVersion::factory()->create([
            'version_name' => 'Test Version 1',
            'created_by' => $this->admin->id,
        ]);

        $criteriaVersion2 = CriteriaVersion::factory()->create([
            'version_name' => 'Test Version 2',
            'created_by' => $this->admin->id,
        ]);

        // Create a report data for version 1
        ReportData::factory()->create([
            'criteria_version_id' => $criteriaVersion1->id,
            'report_title' => 'Test Report Title',
        ]);

        // Act: Make GET request to index
        $response = $this->getJson(route('report-structure.index'));

        // Assert: Check response structure and data
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => [
                            'id',
                            'version_name',
                            'created_by',
                            'created_by_name',
                            'report_title',
                            'created_at',
                            'updated_at',
                        ]
                    ]
                ])
                ->assertJsonCount(2, 'data')
                ->assertJsonFragment([
                    'version_name' => 'Test Version 1',
                    'created_by_name' => 'Test User',
                    'report_title' => 'Test Report Title',
                ])
                ->assertJsonFragment([
                    'version_name' => 'Test Version 2',
                    'created_by_name' => 'Test User',
                ]);
    }

    public function test_can_show_single_criteria_version()
    {
        // Arrange: Create a complex criteria version structure
        $criteriaVersion = CriteriaVersion::factory()->create([
            'version_name' => 'Detailed Test Version',
            'created_by' => $this->admin->id,
        ]);

        $reportData = ReportData::factory()->create([
            'criteria_version_id' => $criteriaVersion->id,
            'report_title' => 'Test Report',
            'report_description' => 'Test Description',
            'assessment_type' => 'quality',
            'comment' => 'Test Comment',
        ]);

        $category = Category::factory()->create([
            'criteria_version_id' => $criteriaVersion->id,
            'main_categories' => 'Main Category',
            'sub_categories' => 'Sub Category',
            'sequence' => 1,
        ]);

        $evaluationList = EvaluationList::factory()->create([
            'categorie_id' => $category->id,
            'criteria_version_id' => $criteriaVersion->id,
            'name' => 'Test Evaluation',
            'sum_score' => 100.0,
            'sequence' => 1,
            'annotation' => 'Test Annotation',
        ]);

        // Act: Make GET request to show
        $response = $this->getJson(route('report-structure.show', $criteriaVersion->id));

        // Assert: Check response structure
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'version_name',
                        'created_by',
                        'report_datas' => [
                            '*' => [
                                'report_data_id',
                                'report_title',
                                'report_description',
                                'assessment_type',
                                'comment',
                            ]
                        ],
                        'categories' => [
                            '*' => [
                                'categorie_id',
                                'main_categories',
                                'sub_categories',
                                'sequence',
                                'evaluation_lists',
                            ]
                        ]
                    ]
                ])
                ->assertJsonFragment([
                    'version_name' => 'Detailed Test Version',
                    'report_title' => 'Test Report',
                    'main_categories' => 'Main Category',
                ]);
    }

    public function test_can_create_criteria_structure()
    {
        // Arrange: Prepare complex payload
        $complexPayload = [
            'version_name' => 'Complex Criteria',
            'created_by' => $this->admin->id,
            'report_datas' => [
                [
                    'report_title' => 'Complex Assessment',
                    'report_description' => 'Detailed assessment with both quantity and quality',
                    'assessment_type' => 'mixed',
                    'comment' => 'Complex evaluation criteria',
                ]
            ],
            'categories' => [
                [
                    'main_categories' => 'Performance Metrics',
                    'sub_categories' => 'Quantitative Analysis',
                    'sequence' => 1,
                    'evaluation_lists' => [
                        [
                            'name' => 'Quantity Evaluation',
                            'sum_score' => 200,
                            'sequence' => 1,
                            'annotation' => 'Quantitative measurements',
                            'quantity_enabled' => true,
                            'quantity_main_criterias' => [
                                [
                                    'name' => 'Performance Index',
                                    'tooltips' => 'Measures overall performance',
                                    'formula' => '(A + B) / 2',
                                    'quantity_sub_criterias' => [
                                        [
                                            'name' => 'Speed Factor',
                                            'sequence' => 1,
                                            'score_a' => 50.0,
                                            'score_b' => 30.0,
                                        ],
                                        [
                                            'name' => 'Efficiency Rate',
                                            'sequence' => 2,
                                            'score_a' => 40.0,
                                            'score_b' => 25.0,
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        [
                            'name' => 'Quality Evaluation',
                            'sum_score' => 300,
                            'sequence' => 2,
                            'annotation' => 'Qualitative assessments',
                            'quantity_enabled' => false,
                            'quality_main_criterias' => [
                                [
                                    'name' => 'Service Quality',
                                    'ratio' => 60,
                                    'tooltips' => 'Overall service quality rating',
                                    'sequence' => 1,
                                    'quality_sub_criterias' => [
                                        [
                                            'name' => 'Customer Satisfaction',
                                            'sequence' => 1,
                                            'num_score' => 80.0,
                                            'description' => 'Measure of customer satisfaction',
                                        ],
                                        [
                                            'name' => 'Service Reliability',
                                            'sequence' => 2,
                                            'num_score' => 75.0,
                                            'description' => 'Consistency of service delivery',
                                        ]
                                    ]
                                ],
                                [
                                    'name' => 'Innovation Score',
                                    'ratio' => 40,
                                    'tooltips' => 'Innovation and creativity assessment',
                                    'sequence' => 2,
                                    'quality_sub_criterias' => [
                                        [
                                            'name' => 'Creative Solutions',
                                            'sequence' => 1,
                                            'num_score' => 65.0,
                                            'description' => 'Innovative problem-solving approaches',
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        // Act: Create complex structure
        $response = $this->postJson(route('report-structure.store'), $complexPayload);

        // Assert: Verify creation
        $response->assertStatus(201)
                ->assertJson([
                    'success' => true,
                ]);

        // Verify database structure
        $createdVersion = CriteriaVersion::where('version_name', 'Complex Criteria')->first();
        $this->assertNotNull($createdVersion);

        // Check quantity main criteria and formula
        $this->assertDatabaseHas('quantity_main_criterias', [
            'criteria_version_id' => $createdVersion->id,
            'name' => 'Performance Index',
        ]);

        $this->assertDatabaseHas('formulas', [
            'condition' => '(A + B) / 2',
        ]);

        // Check quantity sub criteria
        $this->assertDatabaseHas('quantity_sub_criterias', [
            'criteria_version_id' => $createdVersion->id,
            'name' => 'Speed Factor',
            'score_a' => 50.0,
            'score_b' => 30.0,
        ]);

        // Check quality main criteria
        $this->assertDatabaseHas('quality_main_criterias', [
            'criteria_version_id' => $createdVersion->id,
            'name' => 'Service Quality',
            'ratio' => 60,
        ]);

        // Check quality sub criteria
        $this->assertDatabaseHas('quality_sub_criterias', [
            'criteria_version_id' => $createdVersion->id,
            'name' => 'Customer Satisfaction',
            'num_score' => 80.0,
        ]);
    }

    public function test_add_criteria_with_missing_info_returns_validation_error()
    {
        // Test case 1: Missing created_by
        $payload1 = [
            'report_datas' => [
                [
                    'report_title' => 'Test Report',
                    'assessment_type' => 'quality',
                ]
            ],
            'categories' => [
                [
                    'main_categories' => 'Test Main',
                    'sub_categories' => 'Test Sub',
                    'sequence' => 1,
                ]
            ]
        ];

        $response1 = $this->postJson(route('report-structure.store'), $payload1);
        $response1->assertStatus(422)
                 ->assertJsonValidationErrors(['created_by']);

        // Test case 2: Missing report_datas
        $payload2 = [
            'created_by' => $this->admin->id,
            'categories' => [
                [
                    'main_categories' => 'Test Main',
                    'sub_categories' => 'Test Sub',
                    'sequence' => 1,
                ]
            ]
        ];

        $response2 = $this->postJson(route('report-structure.store'), $payload2);
        $response2->assertStatus(422)
                 ->assertJsonValidationErrors(['report_datas']);

        // Test case 3: Missing categories
        $payload3 = [
            'created_by' => $this->admin->id,
            'report_datas' => [
                [
                    'report_title' => 'Test Report',
                    'assessment_type' => 'quality',
                ]
            ],
        ];

        $response3 = $this->postJson(route('report-structure.store'), $payload3);
        $response3->assertStatus(422)
                 ->assertJsonValidationErrors(['categories']);

        // Test case 4: Invalid data types
        $payload4 = [
            'created_by' => 'not_integer',
            'report_datas' => [
                [
                    'report_title' => 'Test Report',
                    'assessment_type' => 'quality',
                ]
            ],
            'categories' => [
                [
                    'main_categories' => 'Test Main',
                    'sub_categories' => 'Test Sub',
                    'sequence' => 'not_integer',
                ]
            ]
        ];

        $response4 = $this->postJson(route('report-structure.store'), $payload4);
        $response4->assertStatus(422)
                 ->assertJsonValidationErrors(['created_by', 'categories.0.sequence']);
    }

    public function test_can_edit_criteria_successfully()
    {
        // Arrange: Create existing criteria version
        $criteriaVersion = CriteriaVersion::factory()->create([
            'version_name' => 'Original Version',
            'created_by' => $this->admin->id,
        ]);

        ReportData::factory()->create([
            'criteria_version_id' => $criteriaVersion->id,
            'report_title' => 'Original Report',
        ]);

        $category = Category::factory()->create([
            'criteria_version_id' => $criteriaVersion->id,
            'main_categories' => 'Original Main',
        ]);

        // Prepare update payload
        $updatePayload = [
            'version_name' => 'Updated Version Name',
            'created_by' => $this->admin->id,
            'report_datas' => [
                [
                    'report_title' => 'Updated Report Title',
                    'report_description' => 'Updated Description',
                    'assessment_type' => 'quality',
                    'comment' => 'Updated Comment',
                ]
            ],
            'categories' => [
                [
                    'categorie_id' => $category->id,
                    'main_categories' => 'Updated Main Category',
                    'sub_categories' => 'Updated Sub Category',
                    'sequence' => 1,
                    'evaluation_lists' => [
                        [
                            'name' => 'Updated Evaluation',
                            'sum_score' => 150,
                            'sequence' => 1,
                            'annotation' => 'Updated Annotation',
                            'quantity_enabled' => false,
                        ]
                    ]
                ]
            ]
        ];

        // Act: Make PUT request
        $response = $this->putJson(route('report-structure.update', $criteriaVersion->id), $updatePayload);

        // Assert: Check successful update
        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Criteria version updated successfully',
                ]);

        // Check database updates
        $this->assertDatabaseHas('criteria_versions', [
            'id' => $criteriaVersion->id,
            'version_name' => 'Updated Version Name',
        ]);

        $this->assertDatabaseHas('report_datas', [
            'criteria_version_id' => $criteriaVersion->id,
            'report_title' => 'Updated Report Title',
        ]);

        $this->assertDatabaseHas('categories', [
            'criteria_version_id' => $criteriaVersion->id,
            'main_categories' => 'Updated Main Category',
        ]);

        // Verify old data is removed
        $this->assertDatabaseMissing('report_datas', [
            'criteria_version_id' => $criteriaVersion->id,
            'report_title' => 'Original Report',
        ]);
    }

    public function test_can_delete_criteria_successfully()
    {
        // Arrange: Create criteria version without related reports
        $criteriaVersion = CriteriaVersion::factory()->create([
            'version_name' => 'To Be Deleted',
            'created_by' => $this->admin->id,
        ]);

        $reportData = ReportData::factory()->create([
            'criteria_version_id' => $criteriaVersion->id,
        ]);

        $category = Category::factory()->create([
            'criteria_version_id' => $criteriaVersion->id,
        ]);

        // Act: Make DELETE request
        $response = $this->deleteJson(route('report-structure.destroy', $criteriaVersion->id));

        // Assert: Check successful deletion
        $response->assertStatus(204);

        // Verify data is deleted from database
        $this->assertDatabaseMissing('criteria_versions', [
            'id' => $criteriaVersion->id,
        ]);

        $this->assertDatabaseMissing('report_datas', [
            'criteria_version_id' => $criteriaVersion->id,
        ]);

        $this->assertDatabaseMissing('categories', [
            'criteria_version_id' => $criteriaVersion->id,
        ]);
    }

    public function test_cannot_delete_criteria_when_in_use_by_non_completed_reports()
    {
        // Arrange: Create criteria version with related non-completed reports
        $criteriaVersion = CriteriaVersion::factory()->create([
            'version_name' => 'In Use Version',
            'created_by' => $this->admin->id,
        ]);

        $reportData = ReportData::factory()->create([
            'criteria_version_id' => $criteriaVersion->id,
        ]);

        // Create reports using this criteria version (non-completed status)
        DB::table('reports')->insert([
            [
                'report_data_id' => $reportData->id,
                'status' => 'In Progress',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'report_data_id' => $reportData->id,
                'status' => 'Draft',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);

        // Act: Attempt to delete
        $response = $this->deleteJson(route('report-structure.destroy', $criteriaVersion->id));

        // Assert: Check deletion is prevented
        $response->assertStatus(409)
                ->assertJson([
                    'success' => false,
                    'message' => 'ไม่สามารถลบได้ เนื่องจากมีการประเมินที่ใช้โครงสร้างเกณฑ์นี้อยู่ ต้องให้การประเมินครบถ้วนก่อน',
                    'not_completed_count' => 2,
                ]);

        // Verify criteria version still exists
        $this->assertDatabaseHas('criteria_versions', [
            'id' => $criteriaVersion->id,
        ]);
    }
}
