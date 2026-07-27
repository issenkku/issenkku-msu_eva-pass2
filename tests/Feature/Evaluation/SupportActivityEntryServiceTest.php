<?php

namespace Tests\Feature\Evaluation;

use App\Models\Category;
use App\Models\CriteriaVersion;
use App\Models\EvaluationList;
use App\Models\EvidenceAnswer;
use App\Models\ReportData;
use App\Models\Reports;
use App\Models\SupportActivityEntry;
use App\Models\SupportCriteria;
use App\Models\SupportScore;
use App\Models\User;
use App\Services\SupportScoreService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class SupportActivityEntryServiceTest extends TestCase
{
    use RefreshDatabase;

    private Reports $report;

    private SupportCriteria $criterion;

    private User $evaluatee;

    private User $reviewer;

    protected function setUp(): void
    {
        parent::setUp();

        $version = CriteriaVersion::factory()->create();
        $reportData = ReportData::factory()->create(['criteria_version_id' => $version->id]);
        $this->report = Reports::factory()->create(['report_data_id' => $reportData->id]);
        $category = Category::factory()->create(['criteria_version_id' => $version->id]);
        $evaluationList = EvaluationList::factory()->create([
            'criteria_version_id' => $version->id,
            'categorie_id' => $category->id,
        ]);
        $this->criterion = SupportCriteria::create([
            'evaluation_list_id' => $evaluationList->id,
            'sequence' => 1,
            'activity_name' => 'งานตามหน้าที่',
            'indicator' => 'ส่งงานตรงเวลา',
            'target_value' => 100,
            'weight' => 20,
            'allow_activity_entries' => true,
        ]);
        $this->evaluatee = User::factory()->create();
        $this->reviewer = User::factory()->create();
    }

    public function test_evaluatee_can_create_multiple_optional_activity_entries(): void
    {
        $this->persist([
            ['content' => '<p>โครงการที่หนึ่ง</p>'],
            ['content' => '<p>โครงการที่สอง</p>'],
        ]);

        $this->assertSame(
            [
                [1, '<p>โครงการที่หนึ่ง</p>', $this->evaluatee->id, $this->evaluatee->id],
                [2, '<p>โครงการที่สอง</p>', $this->evaluatee->id, $this->evaluatee->id],
            ],
            SupportActivityEntry::query()
                ->orderBy('sequence')
                ->get()
                ->map(fn (SupportActivityEntry $entry) => [
                    $entry->sequence,
                    $entry->content,
                    $entry->created_by,
                    $entry->updated_by,
                ])
                ->all()
        );

        $this->persist([]);
        $this->assertDatabaseCount('support_activity_entries', 0);
    }

    public function test_each_activity_entry_keeps_its_own_normalized_evidence_links(): void
    {
        $this->persist([
            [
                'content' => '<p>โครงการหนึ่ง</p>',
                'evidence_links' => [
                    ' https://example.com/one ',
                    'https://example.com/one',
                ],
            ],
            [
                'content' => '<p>โครงการสอง</p>',
                'evidence_links' => ['https://example.com/two'],
            ],
        ]);

        $entries = SupportActivityEntry::with('evidenceAnswers')
            ->orderBy('sequence')
            ->get();

        $this->assertSame(
            ['https://example.com/one'],
            $entries[0]->evidenceAnswers->pluck('link')->all()
        );
        $this->assertSame(
            ['https://example.com/two'],
            $entries[1]->evidenceAnswers->pluck('link')->all()
        );
    }

    public function test_updating_one_activity_evidence_does_not_replace_another_activity_evidence(): void
    {
        $this->persist([
            [
                'content' => '<p>โครงการหนึ่ง</p>',
                'evidence_links' => ['https://example.com/one'],
            ],
            [
                'content' => '<p>โครงการสอง</p>',
                'evidence_links' => ['https://example.com/two'],
            ],
        ]);
        [$first, $second] = SupportActivityEntry::query()->orderBy('sequence')->get();

        $this->persist([
            [
                'id' => $first->id,
                'content' => $first->content,
                'evidence_links' => ['https://example.com/updated'],
            ],
            [
                'id' => $second->id,
                'content' => $second->content,
                'evidence_links' => ['https://example.com/two'],
            ],
        ]);

        $this->assertDatabaseHas('evidence_answers', [
            'support_activity_entry_id' => $first->id,
            'link' => 'https://example.com/updated',
        ]);
        $this->assertDatabaseHas('evidence_answers', [
            'support_activity_entry_id' => $second->id,
            'link' => 'https://example.com/two',
        ]);
        $this->assertDatabaseMissing('evidence_answers', [
            'support_activity_entry_id' => $first->id,
            'link' => 'https://example.com/one',
        ]);
    }

    public function test_deleting_an_activity_removes_only_that_activity_evidence(): void
    {
        $this->persist([
            [
                'content' => '<p>โครงการหนึ่ง</p>',
                'evidence_links' => ['https://example.com/one'],
            ],
            [
                'content' => '<p>โครงการสอง</p>',
                'evidence_links' => ['https://example.com/two'],
            ],
        ]);
        [$first, $second] = SupportActivityEntry::query()->orderBy('sequence')->get();

        $this->persist([[
            'id' => $second->id,
            'content' => $second->content,
            'evidence_links' => ['https://example.com/two'],
        ]]);

        $this->assertDatabaseMissing('evidence_answers', [
            'support_activity_entry_id' => $first->id,
        ]);
        $this->assertDatabaseHas('evidence_answers', [
            'support_activity_entry_id' => $second->id,
            'link' => 'https://example.com/two',
        ]);
        $this->assertSame(1, EvidenceAnswer::query()->count());
    }

    public function test_evaluatee_can_create_many_projects_under_one_indicator_and_another_group(): void
    {
        $this->criterion->update([
            'indicator' => null,
            'group_activity_entries_by_indicator' => true,
        ]);
        $first = $this->criterion->indicatorItems()->create([
            'sequence' => 1, 'code' => '2.1', 'description' => '<p>วิจัย</p>',
        ]);
        $second = $this->criterion->indicatorItems()->create([
            'sequence' => 2, 'code' => '2.2', 'description' => '<p>เผยแพร่</p>',
        ]);
        $this->criterion->indicatorItems()->create([
            'sequence' => 3, 'code' => '2.3', 'description' => '<p>กลุ่มที่ปล่อยว่างได้</p>',
        ]);

        $this->persist([
            ['support_indicator_item_id' => $first->id, 'content' => '<p>โครงการ A</p>'],
            ['support_indicator_item_id' => $first->id, 'content' => '<p>โครงการ B</p>'],
            ['support_indicator_item_id' => $second->id, 'content' => '<p>โครงการ C</p>'],
        ]);

        $this->assertSame(
            [$first->id, $first->id, $second->id],
            SupportActivityEntry::query()->orderBy('sequence')
                ->pluck('support_indicator_item_id')->all()
        );
    }

    public function test_grouped_project_requires_an_indicator_from_the_same_criterion(): void
    {
        $this->criterion->update([
            'indicator' => null,
            'group_activity_entries_by_indicator' => true,
        ]);
        $otherCriterion = SupportCriteria::create([
            'evaluation_list_id' => $this->criterion->evaluation_list_id,
            'sequence' => 2,
            'activity_name' => 'เกณฑ์อื่น',
            'indicator' => null,
            'target_value' => 100,
            'weight' => 20,
            'allow_activity_entries' => true,
            'group_activity_entries_by_indicator' => true,
        ]);
        $foreignItem = $otherCriterion->indicatorItems()->create([
            'sequence' => 1,
            'code' => '9.1',
            'description' => '<p>ข้ออื่น</p>',
        ]);

        foreach ([null, $foreignItem->id] as $indicatorItemId) {
            try {
                $this->persist([[
                    'support_indicator_item_id' => $indicatorItemId,
                    'content' => '<p>โครงการ</p>',
                ]]);
                $this->fail('Expected validation failure');
            } catch (ValidationException $exception) {
                $this->assertArrayHasKey(
                    'support_list.0.activity_entries.0.support_indicator_item_id',
                    $exception->errors()
                );
            }
        }
    }

    public function test_existing_project_cannot_move_to_another_indicator_item(): void
    {
        $this->criterion->update([
            'indicator' => null,
            'group_activity_entries_by_indicator' => true,
        ]);
        $first = $this->criterion->indicatorItems()->create([
            'sequence' => 1, 'code' => '2.1', 'description' => '<p>หนึ่ง</p>',
        ]);
        $second = $this->criterion->indicatorItems()->create([
            'sequence' => 2, 'code' => '2.2', 'description' => '<p>สอง</p>',
        ]);
        $entry = SupportActivityEntry::create([
            'report_id' => $this->report->id,
            'support_criteria_id' => $this->criterion->id,
            'support_indicator_item_id' => $first->id,
            'sequence' => 1,
            'content' => '<p>โครงการเดิม</p>',
        ]);

        foreach (['persist', 'persistAsReviewer'] as $method) {
            try {
                $this->{$method}([[
                    'id' => $entry->id,
                    'support_indicator_item_id' => $second->id,
                    'content' => '<p>โครงการเดิม</p>',
                ]]);
                $this->fail('Expected validation failure');
            } catch (ValidationException $exception) {
                $this->assertArrayHasKey(
                    'support_list.0.activity_entries.0.support_indicator_item_id',
                    $exception->errors()
                );
            }
        }
    }

    public function test_ungrouped_project_can_keep_its_existing_hidden_indicator_link(): void
    {
        $item = $this->criterion->indicatorItems()->create([
            'sequence' => 1,
            'code' => '2.1',
        ]);
        $entry = SupportActivityEntry::create([
            'report_id' => $this->report->id,
            'support_criteria_id' => $this->criterion->id,
            'support_indicator_item_id' => $item->id,
            'sequence' => 1,
            'content' => '<p>โครงการเดิม</p>',
        ]);
        $this->criterion->update([
            'indicator' => null,
            'allow_evaluatee_indicator' => true,
            'group_activity_entries_by_indicator' => false,
        ]);

        $this->persist([[
            'id' => $entry->id,
            'support_indicator_item_id' => $item->id,
            'content' => '<p>โครงการเดิมที่แก้ไขแล้ว</p>',
            'indicator' => '<p>ตัวชี้วัดที่ผู้ถูกประเมินกรอก</p>',
        ]]);

        $this->assertDatabaseHas('support_activity_entries', [
            'id' => $entry->id,
            'support_indicator_item_id' => $item->id,
            'content' => '<p>โครงการเดิมที่แก้ไขแล้ว</p>',
            'indicator' => '<p>ตัวชี้วัดที่ผู้ถูกประเมินกรอก</p>',
        ]);
    }

    public function test_ungrouped_mode_rejects_changed_or_new_indicator_links(): void
    {
        $first = $this->criterion->indicatorItems()->create([
            'sequence' => 1,
            'code' => '2.1',
        ]);
        $second = $this->criterion->indicatorItems()->create([
            'sequence' => 2,
            'code' => '2.2',
        ]);
        $entry = SupportActivityEntry::create([
            'report_id' => $this->report->id,
            'support_criteria_id' => $this->criterion->id,
            'support_indicator_item_id' => $first->id,
            'sequence' => 1,
            'content' => '<p>โครงการเดิม</p>',
        ]);
        $this->criterion->update([
            'indicator' => null,
            'allow_evaluatee_indicator' => true,
            'group_activity_entries_by_indicator' => false,
        ]);

        foreach ([
            [
                'id' => $entry->id,
                'support_indicator_item_id' => $second->id,
                'content' => '<p>เปลี่ยนข้อย่อย</p>',
                'indicator' => '<p>ตัวชี้วัด</p>',
            ],
            [
                'support_indicator_item_id' => $first->id,
                'content' => '<p>โครงการใหม่</p>',
                'indicator' => '<p>ตัวชี้วัด</p>',
            ],
        ] as $payload) {
            try {
                $this->persist([$payload]);
                $this->fail('Expected hidden indicator validation failure');
            } catch (ValidationException $exception) {
                $this->assertArrayHasKey(
                    'support_list.0.activity_entries.0.support_indicator_item_id',
                    $exception->errors()
                );
            }
        }
    }

    public function test_evaluatee_can_update_delete_and_append_without_creating_history(): void
    {
        $this->persist([
            ['content' => '<p>รายการแรก</p>'],
            ['content' => '<p>รายการที่สอง</p>'],
        ]);
        $first = SupportActivityEntry::query()->orderBy('sequence')->firstOrFail();

        $this->persist([
            ['id' => $first->id, 'content' => '<p>แก้ไขรายการแรก</p>'],
            ['content' => '<p>รายการใหม่</p>'],
        ]);

        $this->assertSame(
            ['<p>แก้ไขรายการแรก</p>', '<p>รายการใหม่</p>'],
            SupportActivityEntry::query()->orderBy('sequence')->pluck('content')->all()
        );
        $this->assertDatabaseCount('support_activity_entry_histories', 0);
    }

    public function test_activity_content_must_have_visible_text(): void
    {
        try {
            $this->persist([['content' => '<p><br></p>']]);
            $this->fail('Expected validation failure');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('support_list.0.activity_entries.0.content', $exception->errors());
        }
    }

    public function test_evaluatee_owned_fields_are_validated_calculated_and_persisted(): void
    {
        $this->criterion->update([
            'weight' => null,
            'allow_evaluatee_indicator' => true,
            'allow_evaluatee_weight' => true,
        ]);

        $this->persist([[
            'content' => '<p>โครงการหนึ่ง</p>',
            'indicator' => '<p>ผ่านความเห็นชอบ</p>',
            'weight' => 40,
            'achieved_score' => 80,
        ]]);

        $this->assertDatabaseHas('support_activity_entries', [
            'support_criteria_id' => $this->criterion->id,
            'indicator' => '<p>ผ่านความเห็นชอบ</p>',
            'weight' => '40.00',
            'achieved_score' => '80.00',
            'weighted_score' => '32.00',
        ]);
    }

    public function test_evaluatee_owned_fields_reject_invalid_or_unauthorized_values(): void
    {
        $this->criterion->update([
            'weight' => null,
            'allow_evaluatee_indicator' => true,
            'allow_evaluatee_weight' => true,
        ]);

        foreach ([
            ['field' => 'indicator', 'entry' => ['indicator' => '<p><br></p>', 'weight' => 40, 'achieved_score' => 80]],
            ['field' => 'weight', 'entry' => ['indicator' => '<p>ตัวชี้วัด</p>', 'weight' => 0, 'achieved_score' => 80]],
            ['field' => 'weight', 'entry' => ['indicator' => '<p>ตัวชี้วัด</p>', 'weight' => -1, 'achieved_score' => 80]],
            ['field' => 'weight', 'entry' => ['indicator' => '<p>ตัวชี้วัด</p>', 'weight' => 100.01, 'achieved_score' => 80]],
            ['field' => 'achieved_score', 'entry' => ['indicator' => '<p>ตัวชี้วัด</p>', 'weight' => 40, 'achieved_score' => -0.01]],
            ['field' => 'achieved_score', 'entry' => ['indicator' => '<p>ตัวชี้วัด</p>', 'weight' => 40, 'achieved_score' => 100.01]],
        ] as $case) {
            try {
                $this->persist([[
                    'content' => '<p>โครงการ</p>',
                    ...$case['entry'],
                ]]);
                $this->fail("Expected {$case['field']} validation failure");
            } catch (ValidationException $exception) {
                $this->assertArrayHasKey(
                    "support_list.0.activity_entries.0.{$case['field']}",
                    $exception->errors()
                );
            }
        }

        $this->criterion->update([
            'weight' => 20,
            'allow_evaluatee_indicator' => false,
            'allow_evaluatee_weight' => false,
        ]);

        foreach (['indicator', 'weight', 'achieved_score'] as $field) {
            try {
                $this->persist([[
                    'content' => '<p>โครงการ</p>',
                    $field => $field === 'indicator' ? '<p>ไม่ได้รับอนุญาต</p>' : 10,
                ]]);
                $this->fail("Expected unauthorized {$field} validation failure");
            } catch (ValidationException $exception) {
                $this->assertArrayHasKey(
                    "support_list.0.activity_entries.0.{$field}",
                    $exception->errors()
                );
            }
        }
    }

    public function test_disabled_criterion_rejects_new_entries_without_deleting_hidden_entries(): void
    {
        $entry = SupportActivityEntry::create([
            'report_id' => $this->report->id,
            'support_criteria_id' => $this->criterion->id,
            'sequence' => 1,
            'content' => '<p>ข้อมูลเดิมที่ซ่อนอยู่</p>',
            'created_by' => $this->evaluatee->id,
            'updated_by' => $this->evaluatee->id,
        ]);
        $this->criterion->update(['allow_activity_entries' => false]);

        try {
            $this->persist([['content' => '<p>รายการใหม่</p>']]);
            $this->fail('Expected validation failure');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('support_list.0.activity_entries', $exception->errors());
        }

        $this->persist([]);
        $this->assertDatabaseHas('support_activity_entries', ['id' => $entry->id]);
    }

    public function test_entry_id_must_belong_to_the_same_report_and_criterion(): void
    {
        $otherReport = Reports::factory()->create(['report_data_id' => $this->report->report_data_id]);
        $entry = SupportActivityEntry::create([
            'report_id' => $otherReport->id,
            'support_criteria_id' => $this->criterion->id,
            'sequence' => 1,
            'content' => '<p>ของรายงานอื่น</p>',
        ]);

        $this->expectException(ValidationException::class);
        $this->persist([['id' => $entry->id, 'content' => '<p>พยายามแก้ไข</p>']]);
    }

    public function test_reviewer_change_requires_reason_and_records_activity_history(): void
    {
        $entry = $this->existingEntry('<p>ข้อความเดิม</p>');
        SupportScore::create([
            'report_id' => $this->report->id,
            'support_criteria_id' => $this->criterion->id,
            'achieved_score' => 50,
            'weighted_score' => 10,
        ]);

        try {
            $this->persistAsReviewer([[
                'id' => $entry->id,
                'content' => '<p>ข้อความใหม่</p>',
                'modification_reason' => ' ',
            ]]);
            $this->fail('Expected validation failure');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('support_list.0.activity_entries.0.modification_reason', $exception->errors());
        }

        $this->persistAsReviewer([[
            'id' => $entry->id,
            'content' => '<p>ข้อความใหม่</p>',
            'modification_reason' => 'ปรับตามผลงานจริง',
        ]]);

        $this->assertDatabaseHas('support_activity_entries', [
            'id' => $entry->id,
            'content' => '<p>ข้อความใหม่</p>',
            'updated_by' => $this->reviewer->id,
        ]);
        $this->assertDatabaseHas('support_activity_entry_histories', [
            'support_activity_entry_id' => $entry->id,
            'previous_content' => '<p>ข้อความเดิม</p>',
            'new_content' => '<p>ข้อความใหม่</p>',
            'reason' => 'ปรับตามผลงานจริง',
            'modified_by' => $this->reviewer->id,
            'modified_by_role' => 'ผู้ประเมิน',
        ]);
    }

    public function test_reviewer_cannot_create_delete_or_reorder_entries(): void
    {
        $first = $this->existingEntry('<p>หนึ่ง</p>', 1);
        $second = $this->existingEntry('<p>สอง</p>', 2);

        foreach ([
            [['id' => $first->id, 'content' => $first->content]],
            [
                ['id' => $second->id, 'content' => $second->content],
                ['id' => $first->id, 'content' => $first->content],
            ],
            [
                ['id' => $first->id, 'content' => $first->content],
                ['id' => $second->id, 'content' => $second->content],
                ['content' => '<p>เพิ่มใหม่</p>'],
            ],
        ] as $activityEntries) {
            try {
                $this->persistAsReviewer($activityEntries);
                $this->fail('Expected validation failure');
            } catch (ValidationException $exception) {
                $this->assertArrayHasKey('support_list.0.activity_entries', $exception->errors());
            }
        }

        $this->assertSame([$first->id, $second->id], SupportActivityEntry::query()->orderBy('sequence')->pluck('id')->all());
        $this->assertDatabaseCount('support_activity_entry_histories', 0);
    }

    public function test_reviewer_saving_unchanged_content_does_not_create_history(): void
    {
        $entry = $this->existingEntry('<p>ข้อความเดิม</p>');

        $this->persistAsReviewer([[
            'id' => $entry->id,
            'content' => '<p>ข้อความเดิม</p>',
        ]]);

        $this->assertDatabaseCount('support_activity_entry_histories', 0);
        $this->assertDatabaseHas('support_activity_entries', [
            'id' => $entry->id,
            'content' => '<p>ข้อความเดิม</p>',
        ]);
    }

    public function test_reviewer_changes_to_evaluatee_owned_fields_require_reason_and_record_full_history(): void
    {
        $this->criterion->update([
            'weight' => null,
            'allow_evaluatee_indicator' => true,
            'allow_evaluatee_weight' => true,
        ]);
        $entry = SupportActivityEntry::create([
            'report_id' => $this->report->id,
            'support_criteria_id' => $this->criterion->id,
            'sequence' => 1,
            'content' => '<p>โครงการเดิม</p>',
            'indicator' => '<p>ตัวชี้วัดเดิม</p>',
            'weight' => 40,
            'achieved_score' => 80,
            'weighted_score' => 32,
            'created_by' => $this->evaluatee->id,
            'updated_by' => $this->evaluatee->id,
        ]);

        foreach ([
            ['indicator' => '<p>ตัวชี้วัดใหม่</p>', 'weight' => 40, 'achieved_score' => 80],
            ['indicator' => '<p>ตัวชี้วัดเดิม</p>', 'weight' => 50, 'achieved_score' => 80],
            ['indicator' => '<p>ตัวชี้วัดเดิม</p>', 'weight' => 40, 'achieved_score' => 90],
        ] as $next) {
            SupportActivityEntry::query()->whereKey($entry->id)->update([
                'indicator' => '<p>ตัวชี้วัดเดิม</p>',
                'weight' => 40,
                'achieved_score' => 80,
                'weighted_score' => 32,
            ]);
            $entry->histories()->delete();

            $payload = [
                'id' => $entry->id,
                'content' => '<p>โครงการเดิม</p>',
                ...$next,
            ];
            try {
                $this->persistAsReviewer([$payload]);
                $this->fail('Expected modification reason validation failure');
            } catch (ValidationException $exception) {
                $this->assertArrayHasKey(
                    'support_list.0.activity_entries.0.modification_reason',
                    $exception->errors()
                );
            }

            $payload['modification_reason'] = 'ปรับตามหลักฐาน';
            $this->persistAsReviewer([$payload]);

            $this->assertDatabaseCount('support_activity_entry_histories', 1);
            $this->assertDatabaseHas('support_activity_entry_histories', [
                'support_activity_entry_id' => $entry->id,
                'previous_content' => '<p>โครงการเดิม</p>',
                'new_content' => '<p>โครงการเดิม</p>',
                'previous_indicator' => '<p>ตัวชี้วัดเดิม</p>',
                'new_indicator' => $next['indicator'],
                'previous_weight' => '40.00',
                'new_weight' => number_format($next['weight'], 2, '.', ''),
                'previous_achieved_score' => '80.00',
                'new_achieved_score' => number_format($next['achieved_score'], 2, '.', ''),
                'reason' => 'ปรับตามหลักฐาน',
            ]);
        }
    }

    /** @param array<int, array<string, mixed>> $activityEntries */
    private function persist(array $activityEntries): void
    {
        app(SupportScoreService::class)->persist($this->report, [[
            'support_criteria_id' => $this->criterion->id,
            'achieved_score' => $this->criterion->allow_evaluatee_weight ? null : 50,
            'evidence_links' => [],
            'activity_entries' => $activityEntries,
        ]], $this->evaluatee, null, false);
    }

    /** @param array<int, array<string, mixed>> $activityEntries */
    private function persistAsReviewer(array $activityEntries): void
    {
        SupportScore::firstOrCreate(
            [
                'report_id' => $this->report->id,
                'support_criteria_id' => $this->criterion->id,
            ],
            [
                'achieved_score' => 50,
                'weighted_score' => 10,
            ]
        );

        app(SupportScoreService::class)->persist($this->report, [[
            'support_criteria_id' => $this->criterion->id,
            'achieved_score' => $this->criterion->allow_evaluatee_weight ? null : 50,
            'evidence_links' => [],
            'activity_entries' => $activityEntries,
        ]], $this->reviewer, 'ผู้ประเมิน', true);
    }

    private function existingEntry(string $content, int $sequence = 1): SupportActivityEntry
    {
        return SupportActivityEntry::create([
            'report_id' => $this->report->id,
            'support_criteria_id' => $this->criterion->id,
            'sequence' => $sequence,
            'content' => $content,
            'created_by' => $this->evaluatee->id,
            'updated_by' => $this->evaluatee->id,
        ]);
    }
}
