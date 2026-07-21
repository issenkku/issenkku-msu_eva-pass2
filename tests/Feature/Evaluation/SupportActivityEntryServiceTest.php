<?php

namespace Tests\Feature\Evaluation;

use App\Models\Category;
use App\Models\CriteriaVersion;
use App\Models\EvaluationList;
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

    /** @param array<int, array<string, mixed>> $activityEntries */
    private function persist(array $activityEntries): void
    {
        app(SupportScoreService::class)->persist($this->report, [[
            'support_criteria_id' => $this->criterion->id,
            'achieved_score' => 50,
            'evidence_links' => [],
            'activity_entries' => $activityEntries,
        ]], $this->evaluatee, null, false);
    }

    /** @param array<int, array<string, mixed>> $activityEntries */
    private function persistAsReviewer(array $activityEntries): void
    {
        app(SupportScoreService::class)->persist($this->report, [[
            'support_criteria_id' => $this->criterion->id,
            'achieved_score' => 50,
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
