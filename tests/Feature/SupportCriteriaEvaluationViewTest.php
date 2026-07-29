<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function supportViewItem(): array
{
    return [
        'id' => 7,
        'sequence' => 1,
        'activity_name' => 'จัดทำรายงาน',
        'indicator' => 'ส่งตรงเวลา',
        'target_value' => '5.00',
        'weight' => '20.00',
        'require_evidence' => true,
        'allow_activity_entries' => false,
        'activity_entries' => [],
        'achieved_score' => '5.00',
        'weighted_score' => '1.00',
        'modification_reason' => null,
        'evidence_links' => ['https://example.com/evidence'],
        'histories' => [],
    ];
}

function supportActivityViewItem(): array
{
    return array_replace(supportViewItem(), [
        'allow_activity_entries' => true,
        'evidence_links' => [],
        'activity_entries' => [[
            'id' => 41,
            'sequence' => 1,
            'content' => '<p><strong>โครงการประจำเดือน</strong></p><script>alert(1)</script>',
            'evidence_links' => ['https://example.com/activity-proof'],
            'histories' => [[
                'previous_content' => '<p>ข้อความเดิม</p><script>alert(2)</script>',
                'new_content' => '<p>โครงการประจำเดือน</p>',
                'reason' => 'ปรับตามผลงานจริง',
                'modified_by_name' => 'นาย ผู้ประเมิน',
                'modified_by_role' => 'ผู้ประเมิน',
                'created_at' => '21/07/2026 10:00',
            ]],
        ]],
    ]);
}

function supportGroupedActivityViewItem(): array
{
    return array_replace(supportActivityViewItem(), [
        'indicator' => null,
        'group_activity_entries_by_indicator' => true,
        'indicator_items' => [
            [
                'id' => 11,
                'sequence' => 1,
                'code' => '2.1 ดำเนินการวิจัยเพื่อพัฒนางาน',
            ],
            [
                'id' => 12,
                'sequence' => 2,
                'code' => '2.2 การเผยแพร่งานวิจัย',
            ],
        ],
        'activity_entries' => [
            [
                'id' => 41,
                'sequence' => 1,
                'support_indicator_item_id' => 11,
                'content' => '<p>โครงการ A</p>',
                'evidence_links' => ['https://example.com/project-a'],
                'histories' => [],
            ],
            [
                'id' => 42,
                'sequence' => 2,
                'support_indicator_item_id' => 11,
                'content' => '<p>โครงการ B</p>',
                'evidence_links' => [],
                'histories' => [],
            ],
            [
                'id' => 43,
                'sequence' => 3,
                'support_indicator_item_id' => 12,
                'content' => '<p>โครงการ C</p>',
                'evidence_links' => [],
                'histories' => [],
            ],
        ],
    ]);
}

function supportEvaluateeWeightedViewItem(): array
{
    return array_replace(supportActivityViewItem(), [
        'weight' => null,
        'allow_evaluatee_indicator' => true,
        'allow_evaluatee_weight' => true,
        'achieved_score' => null,
        'weighted_score' => '32.00',
        'activity_entries' => [[
            'id' => 41,
            'sequence' => 1,
            'support_indicator_item_id' => null,
            'content' => '<p>โครงการประจำเดือน</p>',
            'indicator' => '<p>ผ่านความเห็นชอบ</p>',
            'weight' => '40.00',
            'achieved_score' => '80.00',
            'weighted_score' => '32.00',
            'evidence_links' => [],
            'histories' => [],
        ]],
    ]);
}

function supportEvaluateeIndicatorOnlyViewItem(): array
{
    return array_replace(supportActivityViewItem(), [
        'activity_name' => '<p>หัวข้อจากแอดมิน</p>',
        'indicator' => null,
        'allow_evaluatee_indicator' => true,
        'allow_evaluatee_weight' => false,
        'weight' => '20.00',
        'achieved_score' => '80.00',
        'weighted_score' => '16.00',
        'activity_entries' => [
            [
                'id' => 41,
                'sequence' => 1,
                'content' => '<p>กิจกรรมหนึ่ง</p>',
                'indicator' => '<p>ตัวชี้วัดหนึ่ง</p>',
                'weight' => null,
                'achieved_score' => null,
                'weighted_score' => null,
                'evidence_links' => ['https://example.com/one'],
                'histories' => [],
            ],
            [
                'id' => 42,
                'sequence' => 2,
                'content' => '<p>กิจกรรมสอง</p>',
                'indicator' => '<p>ตัวชี้วัดสอง</p>',
                'weight' => null,
                'achieved_score' => null,
                'weighted_score' => null,
                'evidence_links' => ['https://example.com/two'],
                'histories' => [],
            ],
        ],
    ]);
}

test('support criteria component renders one responsive editable form control set', function () {
    $html = view('components.support-criteria-table', [
        'items' => [supportViewItem()],
        'readonly' => false,
        'evidenceEditable' => true,
        'requireReason' => false,
    ])->render();

    expect($html)
        ->toContain('กิจกรรม/โครงการ/งาน')
        ->toContain('ตัวชี้วัด/เกณฑ์การประเมิน')
        ->toContain('ระดับค่าเป้าหมาย')
        ->toContain('ค่าคะแนนที่ได้')
        ->toContain('คะแนนถ่วงน้ำหนัก')
        ->toContain('>หลักฐาน<')
        ->toContain('>จัดการ<')
        ->toContain('hidden w-full table-fixed')
        ->toContain('lg:hidden')
        ->toContain('support_list[7][support_criteria_id]')
        ->toContain('support_list[7][achieved_score]')
        ->toContain('support_list[7][evidence_links][]')
        ->toContain('บังคับแนบหลักฐาน')
        ->toContain('data-support-evidence-list="7"')
        ->toContain('href="https://example.com/evidence"')
        ->toContain('target="_blank"')
        ->toContain('rel="noopener noreferrer"')
        ->not->toContain('data-support-evidence-open')
        ->not->toContain('1 ลิงก์')
        ->toContain('data-support-manage-open="7"')
        ->toContain('data-support-editor-store')
        ->toContain('data-support-modal')
        ->toContain('data-support-modal-body')
        ->toContain('data-support-modal-save')
        ->toContain('aria-label="แก้ไขข้อมูลสำหรับ จัดทำรายงาน"')
        ->toContain('aria-label="ลิงก์หลักฐานสำหรับ จัดทำรายงาน"')
        ->toContain('id="support-modal-errors"')
        ->toContain('aria-live="assertive"')
        ->not->toContain('border-t border-amber-200 bg-white p-4 sm:p-5');

    expect(substr_count($html, 'data-support-modal role="dialog"'))->toBe(1);
    expect(substr_count($html, 'name="support_list[7][achieved_score]"'))->toBe(1);
    expect(substr_count($html, 'data-support-evidence-list="7"'))->toBe(2);
    expect(substr_count($html, 'href="https://example.com/evidence"'))->toBe(2);
});

test('criterion score input accepts whole values from one through its target', function () {
    $html = view('components.support-criteria-table', [
        'items' => [array_replace(supportViewItem(), [
            'target_value' => '4.00',
            'achieved_score' => '4.00',
        ])],
        'readonly' => false,
        'evidenceEditable' => true,
        'requireReason' => false,
    ])->render();

    expect($html)
        ->toContain('type="number" min="1" max="4" step="1"')
        ->toContain('data-support-target="4.00"')
        ->toContain('data-support-score-help')
        ->toContain('data-support-score-error')
        ->toContain('aria-describedby="support-score-help-7 support-score-error-7"')
        ->toContain('กรอกเฉพาะจำนวนเต็มตั้งแต่ 1–5 และต้องไม่เกินระดับค่าเป้าหมาย 4.00');
});

test('criterion score input caps its browser maximum at five', function () {
    $html = view('components.support-criteria-table', [
        'items' => [array_replace(supportViewItem(), ['target_value' => '12.00'])],
        'readonly' => false,
        'evidenceEditable' => true,
        'requireReason' => false,
    ])->render();

    expect($html)
        ->toContain('type="number" min="1" max="5" step="1"')
        ->toContain('data-support-target="12.00"');
});

test('activity score input uses the one-to-five target-limited contract', function () {
    $item = array_replace(supportEvaluateeWeightedViewItem(), [
        'target_value' => '4.00',
    ]);
    $item['activity_entries'][0]['achieved_score'] = '4.00';
    $item['activity_entries'][0]['weighted_score'] = '1.60';

    $html = view('components.support-criteria-table', [
        'items' => [$item],
        'readonly' => false,
        'evidenceEditable' => true,
        'requireReason' => false,
        'activityEntryRole' => 'evaluatee',
    ])->render();

    expect($html)
        ->toContain('type="number" min="1" max="4" step="1"')
        ->toContain('data-support-entry-target="4.00"')
        ->toContain('data-support-score-help')
        ->toContain('data-support-score-error')
        ->toContain('กรอกเฉพาะจำนวนเต็มตั้งแต่ 1–5 และต้องไม่เกินระดับค่าเป้าหมาย 4.00')
        ->toContain('aria-describedby="support-entry-score-help-7-0 support-entry-score-error-7-0"');
});

test('support criteria activity and indicator render as sanitized rich text', function () {
    $item = array_replace(supportViewItem(), [
        'activity_name' => '<p><strong>กิจกรรม</strong></p><script>alert(1)</script>',
        'indicator' => '<ul><li>ครบตามแผน</li></ul>',
    ]);

    $html = view('components.support-criteria-table', [
        'items' => [$item],
        'readonly' => false,
        'evidenceEditable' => true,
        'requireReason' => false,
    ])->render();

    expect($html)
        ->toContain('<strong>กิจกรรม</strong>')
        ->toContain('<ul>')
        ->not->toContain('&lt;script&gt;')
        ->not->toContain('alert(1)')
        ->not->toContain('aria-label="ดูหลักฐานของ &lt;p&gt;');
});

test('reviewer can edit score with a reason while evidence is preserved read only', function () {
    $html = view('components.support-criteria-table', [
        'items' => [supportViewItem()],
        'readonly' => false,
        'evidenceEditable' => false,
        'requireReason' => true,
    ])->render();

    expect($html)
        ->toContain('support_list[7][modification_reason]')
        ->toContain('support_list[7][evidence_links][]')
        ->toContain('type="hidden"')
        ->toContain('https://example.com/evidence')
        ->toContain('data-support-manage-open="7"')
        ->not->toContain('data-support-evidence-open="7"')
        ->toContain('href="https://example.com/evidence"')
        ->toContain('target="_blank"')
        ->toContain('rel="noopener noreferrer"');
});

test('support score history is available from its own desktop and mobile column in every mode', function () {
    $history = [
        'previous_achieved_score' => '80.00',
        'new_achieved_score' => '90.00',
        'previous_weighted_score' => '16.00',
        'new_weighted_score' => '18.00',
        'reason' => 'ปรับตามหลักฐาน',
        'modified_by_name' => 'ผู้ประเมิน',
        'modified_by_role' => 'Evaluator',
        'created_at' => '24/07/2026 10:00',
    ];
    $item = array_replace(supportViewItem(), ['histories' => [$history, $history, $history]]);

    foreach ([false, true] as $readonly) {
        $html = view('components.support-criteria-table', [
            'items' => [$item],
            'readonly' => $readonly,
            'evidenceEditable' => ! $readonly,
            'requireReason' => ! $readonly,
        ])->render();

        expect($html)
            ->toContain('ประวัติการแก้ไข')
            ->toContain('data-support-history-open="7"')
            ->toContain('3 ครั้ง')
            ->toContain('id="support-history-modal"')
            ->toContain('data-support-history-payload="7"');

        expect(substr_count($html, 'data-support-history-open="7"'))->toBe(2);

        if ($readonly) {
            expect($html)->not->toContain('data-support-manage-open="7"');

            $document = new DOMDocument;
            @$document->loadHTML("<fieldset disabled>{$html}</fieldset>");
            $xpath = new DOMXPath($document);

            expect($xpath->query('//fieldset[@disabled]//button[@data-support-history-open]')->length)
                ->toBe(0);
            expect($xpath->query('//fieldset[@disabled]//*[@data-support-history-open and @href]')->length)
                ->toBe(2);
            expect($xpath->query('//fieldset[@disabled]//button[@data-support-history-close]')->length)
                ->toBe(0);
            expect($xpath->query('//fieldset[@disabled]//*[@data-support-history-close and @href]')->length)
                ->toBe(2);
        }
    }

    $emptyHtml = view('components.support-criteria-table', [
        'items' => [supportViewItem()],
        'readonly' => true,
        'evidenceEditable' => false,
        'requireReason' => false,
    ])->render();

    expect($emptyHtml)
        ->toContain('aria-label="ไม่มีประวัติการแก้ไข"')
        ->not->toContain('data-support-history-open="7"');

    $script = file_get_contents(resource_path('views/components/support-criteria-table-script.blade.php'));
    expect($script)
        ->toContain('openSupportHistoryModal')
        ->toContain('closeSupportHistoryModal')
        ->toContain('trapSupportHistoryModalFocus')
        ->toContain('line.textContent = value')
        ->toContain("event.key === 'Escape'");
});

test('evaluatee can add edit and delete optional support activity entries', function () {
    $html = view('components.support-criteria-table', [
        'items' => [supportActivityViewItem()],
        'readonly' => false,
        'evidenceEditable' => true,
        'requireReason' => false,
        'activityEntryRole' => 'evaluatee',
    ])->render();

    $activityContainerPosition = strpos($html, 'data-support-activity-container');
    $activityEvidencePosition = strpos($html, 'value="https://example.com/activity-proof"');
    $addActivityButtonPosition = strpos($html, 'data-add-support-activity="7"');

    expect($activityContainerPosition)->not->toBeFalse()
        ->and($activityEvidencePosition)->not->toBeFalse()
        ->and($addActivityButtonPosition)->not->toBeFalse()
        ->and($addActivityButtonPosition)->toBeGreaterThan($activityContainerPosition)
        ->and($addActivityButtonPosition)->toBeGreaterThan($activityEvidencePosition);

    expect($html)
        ->toContain('<strong>โครงการประจำเดือน</strong>')
        ->not->toContain('alert(1)')
        ->toContain('data-support-manage-open="7"')
        ->not->toContain('data-support-activity-open')
        ->toContain('data-add-support-activity="7"')
        ->toContain('data-remove-support-activity')
        ->toContain('support_list[7][activity_entries][0][id]')
        ->toContain('support_list[7][activity_entries][0][content]')
        ->toContain('support_list[7][activity_entries][0][evidence_links][]')
        ->toContain('value="https://example.com/activity-proof"')
        ->not->toContain('name="support_list[7][evidence_links][]"')
        ->toContain('data-support-activity-content')
        ->toContain('support-activity-richtext');
});

test('evaluatee owned support fields render per project without criterion score input', function () {
    $html = view('components.support-criteria-table', [
        'items' => [supportEvaluateeWeightedViewItem()],
        'readonly' => false,
        'evidenceEditable' => true,
        'requireReason' => false,
        'activityEntryRole' => 'evaluatee',
    ])->render();
    preg_match('/<table class="hidden.*?<\/table>/su', $html, $desktopTable);

    expect($html)
        ->toContain('support_list[7][activity_entries][0][indicator]')
        ->toContain('support_list[7][activity_entries][0][weight]')
        ->toContain('support_list[7][activity_entries][0][achieved_score]')
        ->toContain('data-support-entry-indicator-list="7"')
        ->toContain('data-support-entry-weight-list="7"')
        ->toContain('data-support-entry-score-list="7"')
        ->toContain('data-support-entry-weighted')
        ->toContain('32.00')
        ->not->toContain('ส่งตรงเวลา')
        ->not->toContain('name="support_list[7][achieved_score]"');

    expect(substr_count($desktopTable[0], 'data-support-entry-indicator-list="7"'))->toBe(1)
        ->and(substr_count($desktopTable[0], 'data-support-entry-weight-list="7"'))->toBe(1)
        ->and(substr_count($desktopTable[0], 'data-support-entry-score-list="7"'))->toBe(1);
});

test('desktop support table renders evaluatee owned values as aligned entry rows', function () {
    $item = supportEvaluateeWeightedViewItem();
    $item['activity_entries'][] = array_replace($item['activity_entries'][0], [
        'id' => 42,
        'sequence' => 2,
        'content' => '<p>โครงการรายการที่สอง</p>',
        'indicator' => '<p>ตัวชี้วัดรายการที่สอง</p>',
        'weight' => '50.00',
        'achieved_score' => '20.00',
        'weighted_score' => '10.00',
        'evidence_links' => ['https://example.com/second-proof'],
    ]);

    $html = view('components.support-criteria-table', [
        'items' => [$item],
        'readonly' => false,
        'evidenceEditable' => true,
        'requireReason' => false,
    ])->render();
    preg_match('/<table class="hidden.*?<\/table>/su', $html, $desktopTable);

    expect(substr_count($html, 'data-support-entry-row="7"'))->toBe(2)
        ->and($html)->toContain('data-support-entry-index="0"')
        ->and($html)->toContain('data-support-entry-index="1"')
        ->and(substr_count($html, 'data-support-criterion-heading="7"'))->toBe(1)
        ->and($html)->toContain('colspan="9"')
        ->and(substr_count($desktopTable[0], 'data-support-entry-activity-cell'))->toBe(2)
        ->and(substr_count($desktopTable[0], 'data-support-entry-indicator-cell'))->toBe(2)
        ->and(substr_count($desktopTable[0], 'data-support-shared-evidence="7"'))->toBe(1)
        ->and($html)->toContain('rowspan="2"')
        ->and($html)->toContain('aria-label="เปิดหลักฐาน 1 สำหรับรายการ 2"')
        ->and($html)->toContain('whitespace-nowrap')
        ->and($html)->toContain('>เปิดดู</span>')
        ->and($desktopTable[0])->not->toMatch('/>\s*https:\/\/example\.com\/second-proof\s*</u');

    expect(substr_count($desktopTable[0], 'data-support-entry-weight-list="7"'))->toBe(2)
        ->and(substr_count($desktopTable[0], 'data-support-entry-score-list="7"'))->toBe(2)
        ->and(substr_count($desktopTable[0], 'data-support-shared-weight="7"'))->toBe(0)
        ->and(substr_count($desktopTable[0], 'data-support-shared-score="7"'))->toBe(0)
        ->and($desktopTable[0])->toMatch('/>\s*40\.00\s*</')
        ->and($desktopTable[0])->toMatch('/>\s*50\.00\s*</')
        ->and($desktopTable[0])->toMatch('/>\s*80\.00\s*</')
        ->and($desktopTable[0])->toMatch('/>\s*20\.00\s*</');

    $script = file_get_contents(resource_path('views/components/support-criteria-table-script.blade.php'));
    expect($script)->toContain('syncDesktopEntryRows');
});

test('empty non grouped support mode renders a live desktop row shell', function () {
    $item = supportEvaluateeWeightedViewItem();
    $item['activity_entries'] = [];

    $html = view('components.support-criteria-table', [
        'items' => [$item],
        'readonly' => false,
        'evidenceEditable' => true,
        'requireReason' => false,
        'activityEntryRole' => 'evaluatee',
    ])->render();

    preg_match('/<table class="hidden.*?<\/table>/su', $html, $desktopTable);
    $desktop = $desktopTable[0];

    expect(substr_count($desktop, 'data-support-entry-row="7"'))->toBe(1)
        ->and($desktop)->toContain('data-support-entry-empty')
        ->and($desktop)->toContain('data-support-entry-cell')
        ->and($desktop)->toContain('data-support-entry-number')
        ->and($html)->toContain('data-support-entry-row-template="7"')
        ->and($desktop)->toContain('rowspan="1"')
        ->and($desktop)->toContain('ยังไม่มีกิจกรรม/โครงการเพิ่มเติม');
});

test('indicator-only entry rows keep criterion scores in shared cells', function () {
    $html = view('components.support-criteria-table', [
        'items' => [supportEvaluateeIndicatorOnlyViewItem()],
        'readonly' => false,
        'evidenceEditable' => true,
        'requireReason' => false,
    ])->render();

    preg_match('/<table class="hidden.*?<\/table>/su', $html, $desktopTable);
    $desktop = $desktopTable[0];

    expect(substr_count($desktop, 'data-support-entry-row="7"'))->toBe(2)
        ->and(substr_count($desktop, 'data-support-entry-activity-cell'))->toBe(2)
        ->and(substr_count($desktop, 'data-support-entry-indicator-cell'))->toBe(2)
        ->and(substr_count($desktop, 'data-support-entry-weight-list="7"'))->toBe(0)
        ->and(substr_count($desktop, 'data-support-entry-score-list="7"'))->toBe(0)
        ->and(substr_count($desktop, 'data-support-shared-weight="7"'))->toBe(1)
        ->and(substr_count($desktop, 'data-support-shared-score="7"'))->toBe(1)
        ->and($desktop)->toContain('rowspan="2"')
        ->and($desktop)->toMatch('/>\s*20\.00\s*</')
        ->and($desktop)->toMatch('/>\s*80\.00\s*</');
});

test('activity-only entries still render under a full-width admin heading', function () {
    $item = supportActivityViewItem();
    $item['activity_name'] = '<p>หัวข้อค่าร่วมจากแอดมิน</p>';
    $item['activity_entries'][] = [
        'id' => 42,
        'sequence' => 2,
        'content' => '<p>กิจกรรมสอง</p>',
        'evidence_links' => [],
        'histories' => [],
    ];

    $html = view('components.support-criteria-table', [
        'items' => [$item],
        'readonly' => false,
        'evidenceEditable' => true,
        'requireReason' => false,
    ])->render();

    preg_match('/<table class="hidden.*?<\/table>/su', $html, $desktopTable);
    $desktop = $desktopTable[0];

    expect(substr_count($desktop, 'data-support-criterion-heading="7"'))->toBe(1)
        ->and(substr_count($desktop, 'data-support-entry-row="7"'))->toBe(2)
        ->and(substr_count($desktop, 'data-support-entry-activity-cell'))->toBe(2)
        ->and(substr_count($desktop, 'data-support-shared-indicator="7"'))->toBe(1)
        ->and(substr_count($desktop, 'data-support-shared-weight="7"'))->toBe(1)
        ->and(substr_count($desktop, 'data-support-shared-score="7"'))->toBe(1)
        ->and($desktop)->toContain('data-support-admin-heading')
        ->and($desktop)->toContain('rowspan="2"');
});

test('aligned desktop rows use one grouped evidence cell without losing activity links', function () {
    $html = view('components.support-criteria-table', [
        'items' => [supportEvaluateeIndicatorOnlyViewItem()],
        'readonly' => false,
        'evidenceEditable' => true,
        'requireReason' => false,
    ])->render();

    preg_match('/<table class="hidden.*?<\/table>/su', $html, $desktopTable);
    $desktop = $desktopTable[0];

    expect(substr_count($desktop, 'data-support-shared-evidence="7"'))->toBe(1)
        ->and(substr_count($desktop, 'data-support-evidence-list="7"'))->toBe(1)
        ->and($desktop)->toContain('rowspan="2"')
        ->and($desktop)->toContain('href="https://example.com/one"')
        ->and($desktop)->toContain('href="https://example.com/two"')
        ->and($desktop)->toContain('รายการ 1')
        ->and($desktop)->toContain('รายการ 2');
});

test('admin heading is black and has no explanatory badge', function () {
    $html = view('components.support-criteria-table', [
        'items' => [supportEvaluateeIndicatorOnlyViewItem()],
        'readonly' => false,
        'evidenceEditable' => true,
        'requireReason' => false,
    ])->render();

    preg_match('/<table class="hidden.*?<\/table>/su', $html, $desktopTable);

    expect(substr_count($desktopTable[0], 'data-support-admin-heading'))->toBe(1)
        ->and($desktopTable[0])->toContain('font-bold')
        ->and($desktopTable[0])->toContain('text-slate-950')
        ->and($html)->not->toContain('หัวข้อที่แอดมินกำหนด');
});

test('mobile criterion card shows shared score once unless evaluatee weight is enabled', function () {
    $indicatorOnly = view('components.support-criteria-table', [
        'items' => [supportEvaluateeIndicatorOnlyViewItem()],
        'readonly' => false,
        'evidenceEditable' => true,
        'requireReason' => false,
    ])->render();
    preg_match('/<div class="space-y-3 p-4 lg:hidden">.*?<div class="hidden" data-support-editor-store/su', $indicatorOnly, $mobile);

    expect(substr_count($mobile[0], 'data-support-entry-weight-list="7"'))->toBe(0)
        ->and(substr_count($mobile[0], 'data-support-entry-score-list="7"'))->toBe(0)
        ->and($mobile[0])->toMatch('/>\s*20\.00\s*</')
        ->and($mobile[0])->toMatch('/>\s*80\.00\s*</');

    $weighted = view('components.support-criteria-table', [
        'items' => [supportEvaluateeWeightedViewItem()],
        'readonly' => false,
        'evidenceEditable' => true,
        'requireReason' => false,
    ])->render();
    preg_match('/<div class="space-y-3 p-4 lg:hidden">.*?<div class="hidden" data-support-editor-store/su', $weighted, $weightedMobile);

    expect(substr_count($weightedMobile[0], 'data-support-entry-weight-list="7"'))->toBe(1)
        ->and(substr_count($weightedMobile[0], 'data-support-entry-score-list="7"'))->toBe(1);
});

test('grouped indicator mode renders under a full-width admin heading', function () {
    $html = view('components.support-criteria-table', [
        'items' => [supportGroupedActivityViewItem()],
        'readonly' => false,
        'evidenceEditable' => true,
        'requireReason' => false,
        'activityEntryRole' => 'evaluatee',
    ])->render();

    preg_match('/<table class="hidden.*?<\/table>/su', $html, $desktopTable);
    $desktop = $desktopTable[0];

    expect(substr_count($desktop, 'data-support-criterion-heading="7"'))->toBe(1)
        ->and(substr_count($desktop, 'data-support-admin-heading'))->toBe(1)
        ->and(substr_count($desktop, 'data-support-activity-grouped="1"'))->toBe(1)
        ->and($desktop)->toContain('2.1 ดำเนินการวิจัยเพื่อพัฒนางาน')
        ->and($desktop)->toContain('2.2 การเผยแพร่งานวิจัย')
        ->and($desktop)->toContain('โครงการ A')
        ->and($desktop)->toContain('โครงการ B')
        ->and($desktop)->toContain('โครงการ C');
});

test('grouped indicator mode renders aligned table rows with circular project numbers', function () {
    $html = view('components.support-criteria-table', [
        'items' => [supportGroupedActivityViewItem()],
        'readonly' => false,
        'evidenceEditable' => true,
        'requireReason' => false,
        'activityEntryRole' => 'evaluatee',
    ])->render();

    preg_match('/<table class="hidden.*?<\/table>/su', $html, $desktopTable);
    $desktop = $desktopTable[0];

    expect(substr_count($desktop, 'data-support-grouped-indicator-row="7"'))->toBe(2)
        ->and(substr_count($desktop, 'data-support-group-divider'))->toBe(2)
        ->and(substr_count($desktop, 'data-support-grouped-indicator-id="11"'))->toBe(1)
        ->and(substr_count($desktop, 'data-support-grouped-indicator-id="12"'))->toBe(1)
        ->and(substr_count($desktop, 'data-support-grouped-project-number'))->toBe(3)
        ->and($desktop)->toContain('rowspan="2"')
        ->and($desktop)->toContain('rounded-full bg-amber-100');
});

test('grouped indicator mode keeps an aligned row when an indicator has no project', function () {
    $item = supportGroupedActivityViewItem();
    $item['activity_entries'] = [];

    $html = view('components.support-criteria-table', [
        'items' => [$item],
        'readonly' => false,
        'evidenceEditable' => true,
        'requireReason' => false,
        'activityEntryRole' => 'evaluatee',
    ])->render();

    preg_match('/<table class="hidden.*?<\/table>/su', $html, $desktopTable);
    $desktop = $desktopTable[0];

    expect(substr_count($desktop, 'data-support-grouped-indicator-row="7"'))->toBe(2)
        ->and(substr_count($desktop, 'data-support-display-group-entries'))->toBe(2)
        ->and(substr_count($desktop, 'data-support-grouped-project-empty'))->toBe(2);
});

test('empty activity mode keeps its admin heading separate from the placeholder row', function () {
    $item = supportActivityViewItem();
    $item['activity_name'] = '<p>หัวข้อที่ยังไม่มีรายการย่อย</p>';
    $item['activity_entries'] = [];

    $html = view('components.support-criteria-table', [
        'items' => [$item],
        'readonly' => false,
        'evidenceEditable' => true,
        'requireReason' => false,
    ])->render();

    preg_match('/<table class="hidden.*?<\/table>/su', $html, $desktopTable);
    $desktop = $desktopTable[0];

    expect(substr_count($desktop, 'data-support-criterion-heading="7"'))->toBe(1)
        ->and(substr_count($desktop, 'data-support-admin-heading'))->toBe(1)
        ->and($desktop)->toContain('ยังไม่มีกิจกรรม/โครงการเพิ่มเติม');
});

test('grouped support projects render and edit under their assigned indicator item', function () {
    $html = view('components.support-criteria-table', [
        'items' => [supportGroupedActivityViewItem()],
        'readonly' => false,
        'evidenceEditable' => true,
        'requireReason' => false,
        'activityEntryRole' => 'evaluatee',
    ])->render();

    expect($html)
        ->toContain('data-support-activity-group="11"')
        ->toContain('data-support-activity-group="12"')
        ->toContain('+ เพิ่มโครงการในข้อ 2.1')
        ->toContain('+ เพิ่มโครงการในข้อ 2.2')
        ->toContain('2.1 ดำเนินการวิจัยเพื่อพัฒนางาน')
        ->toContain('ข้อ 2.1')
        ->not->toContain('ข้อ 2.1 ดำเนินการวิจัยเพื่อพัฒนางาน')
        ->not->toContain('ข้อ 2.2 การเผยแพร่งานวิจัย')
        ->toContain('support_list[7][activity_entries][0][support_indicator_item_id]')
        ->toContain('support_list[7][activity_entries][0][evidence_links][]')
        ->toContain('value="https://example.com/project-a"')
        ->not->toContain('name="support_list[7][evidence_links][]"')
        ->toContain('value="11"')
        ->not->toContain('ส่งตรงเวลา');
});

test('reviewer can edit existing support activities with a reason but cannot add or delete', function () {
    $html = view('components.support-criteria-table', [
        'items' => [supportActivityViewItem()],
        'readonly' => false,
        'evidenceEditable' => false,
        'requireReason' => true,
        'activityEntryRole' => 'reviewer',
    ])->render();

    expect($html)
        ->toContain('support_list[7][activity_entries][0][content]')
        ->toContain('support_list[7][activity_entries][0][modification_reason]')
        ->toContain('support_list[7][activity_entries][0][evidence_links][]')
        ->toContain('href="https://example.com/activity-proof"')
        ->toContain('target="_blank"')
        ->toContain('rel="noopener noreferrer"')
        ->toContain('เหตุผลที่แก้ไขข้อมูลกิจกรรม/โครงการ')
        ->toContain('ประวัติการแก้ไขกิจกรรม/โครงการ')
        ->toContain('<p>ข้อความเดิม</p>')
        ->not->toContain('alert(2)')
        ->not->toContain('data-add-support-activity')
        ->not->toContain('data-remove-support-activity');
});

test('read only support activities show sanitized content without preservation fields', function () {
    $html = view('components.support-criteria-table', [
        'items' => [supportActivityViewItem()],
        'readonly' => true,
        'evidenceEditable' => false,
        'requireReason' => false,
        'activityEntryRole' => 'readonly',
    ])->render();

    expect($html)
        ->toContain('<strong>โครงการประจำเดือน</strong>')
        ->toContain('data-support-activity-evidence-list="41"')
        ->toContain('รายการ 1')
        ->toContain('href="https://example.com/activity-proof"')
        ->toContain('target="_blank"')
        ->toContain('rel="noopener noreferrer"')
        ->not->toContain('alert(1)')
        ->not->toContain('support_list[7][activity_entries]')
        ->not->toContain('data-support-activity-content')
        ->not->toContain('data-add-support-activity')
        ->not->toContain('data-remove-support-activity');

    expect(substr_count($html, 'data-support-activity-evidence-list="41"'))->toBe(2);
});

test('single activity evidence group omits its redundant visible item label', function () {
    $html = view('components.support-evidence-summary', [
        'item' => supportActivityViewItem(),
    ])->render();

    expect(strip_tags($html))
        ->toContain('เปิดดู')
        ->not->toContain('รายการ 1');
});

test('read only support criteria has no editable score or preservation fields', function () {
    $html = view('components.support-criteria-table', [
        'items' => [supportViewItem()],
        'readonly' => true,
        'evidenceEditable' => false,
        'requireReason' => false,
    ])->render();

    expect($html)
        ->not->toContain('data-support-score')
        ->not->toContain('name="support_list[7][achieved_score]"')
        ->not->toContain('name="support_list[7][evidence_links][]"')
        ->not->toContain('data-support-modal-save')
        ->not->toContain('data-support-evidence-open="7"')
        ->toContain('href="https://example.com/evidence"')
        ->toContain('target="_blank"')
        ->toContain('rel="noopener noreferrer"');
});

test('read only support table shows evidence links without management controls', function () {
    $html = view('components.support-criteria-table', [
        'items' => [supportViewItem()],
        'readonly' => true,
        'evidenceEditable' => false,
        'requireReason' => false,
    ])->render();

    expect($html)
        ->toContain('data-support-evidence-list="7"')
        ->not->toContain('data-support-evidence-open="7"')
        ->toContain('href="https://example.com/evidence"')
        ->not->toContain('data-support-manage-open="7"')
        ->not->toContain('>จัดการ<')
        ->not->toContain('data-support-modal-save');
});

test('shared support script and all role components expose the same contracts', function () {
    $script = file_get_contents(resource_path('views/components/support-criteria-table-script.blade.php'));

    expect($script)
        ->toContain('window.recalculateSupportScores')
        ->toContain('window.validateSupportCriteria')
        ->toContain('Math.min(rawTotal, 100)')
        ->toContain('support-achievement-summary')
        ->toContain('supportTargetLevelCount')
        ->toContain('calculateSupportAchievement')
        ->toContain('__supportCriteriaBound')
        ->toContain('const openSupportModal =')
        ->toContain('const closeSupportModal =')
        ->toContain('const snapshotSupportItem =')
        ->toContain('const restoreSupportItem =')
        ->toContain('const updateSupportRow =')
        ->toContain("'[data-support-manage-open]'")
        ->toContain("document.createElement('a')")
        ->toContain("anchor.target = '_blank'")
        ->toContain("anchor.rel = 'noopener noreferrer'")
        ->toContain('anchor.textContent = `🔗 ${label} ↗`')
        ->toContain('data-support-evidence-list')
        ->not->toContain("'[data-support-evidence-open]'")
        ->toContain("'[data-support-modal-save]'")
        ->toContain("event.key === 'Escape'")
        ->toContain("document.body.style.overflow = 'hidden'")
        ->toContain("'[data-support-evidence-section] a[href]'")
        ->toContain('snapshotActivityEntries')
        ->toContain('restoreActivityEntries')
        ->toContain('reindexActivityEntries')
        ->toContain('initializeActivityEditors')
        ->toContain('destroyActivityEditors')
        ->toContain('updateActivityDisplays')
        ->toContain('updateEntryValueDisplays')
        ->toContain('if (contentFields.length === 0) return;')
        ->toContain("'[data-add-support-activity]'")
        ->toContain("'[data-remove-support-activity]'")
        ->toContain('previouslyFocusedElement.focus()')
        ->toContain('const validateSupportItem =')
        ->toContain('firstInvalid')
        ->toContain('activeItem?.dataset.supportId === String(criterionId)')
        ->toContain("openSupportModal(firstInvalidItem.dataset.supportId, 'error')")
        ->toContain('data-support-modal-errors')
        ->toContain('const trapSupportModalFocus =')
        ->toContain("event.key === 'Tab'")
        ->toContain("setAttribute('aria-invalid', 'true')")
        ->toContain("'support-modal-errors'")
        ->toContain('isCriterionScoreValid')
        ->toContain('input.dataset.supportTarget')
        ->toContain('ต้องเป็นจำนวนเต็ม 1–5 และไม่เกินระดับค่าเป้าหมาย');

    foreach (['unified-evaluation', 'unified-evaluator', 'unified-director'] as $component) {
        $source = file_get_contents(resource_path("views/components/{$component}.blade.php"));
        expect($source)
            ->toContain('support_items')
            ->toContain('support-criteria-table')
            ->toContain('support-criteria-table-script');
    }

    expect(file_get_contents(resource_path('views/components/unified-evaluation.blade.php')))
        ->toContain('activity-entry-role="evaluatee"');
    foreach (['unified-evaluator', 'unified-director'] as $component) {
        expect(file_get_contents(resource_path("views/components/{$component}.blade.php")))
            ->toContain('activity-entry-role="reviewer"');
    }
});

test('all evaluation form shells use the wider shared layout', function () {
    foreach ([
        'evaluatee/evaluation.blade.php',
        'partials/evaluation-evaluator-form.blade.php',
        'partials/evaluation-approval-form.blade.php',
        'dashboard/admin.blade.php',
    ] as $viewPath) {
        $source = file_get_contents(resource_path("views/{$viewPath}"));

        expect($source)
            ->toContain('evaluation-form-shell')
            ->toContain('w-full max-w-7xl')
            ->not->toContain('max-w-4xl');
    }

    $styles = file_get_contents(resource_path('views/partials/evaluation-form-styles.blade.php'));
    expect($styles)
        ->toContain('.evaluation-form-shell')
        ->not->toContain('.max-w-4xl');
});

test('support criteria uses a fixed desktop table and cards without horizontal scrolling', function () {
    $source = file_get_contents(resource_path('views/components/support-criteria-table.blade.php'));

    expect($source)
        ->toContain('hidden w-full table-fixed')
        ->toContain('lg:table')
        ->toContain('lg:hidden')
        ->toContain('w-[17%]')
        ->toContain('w-[23%]')
        ->toContain('w-[8%]')
        ->toContain('break-words')
        ->not->toContain('overflow-x-auto')
        ->not->toContain('min-w-[1180px]')
        ->not->toContain('min-w-[240px]')
        ->not->toContain('min-w-[360px]')
        ->not->toContain('md:table')
        ->not->toContain('md:hidden');
});

test('reviewer score components expose per item reasons and shared histories', function () {
    foreach (['unified-evaluator', 'unified-director'] as $component) {
        $source = file_get_contents(resource_path("views/components/{$component}.blade.php"));

        expect($source)
            ->toContain('quantity_list[')
            ->toContain('quality_list[')
            ->toContain('[modification_reason]')
            ->toContain('data-score-change-reason')
            ->toContain('score-change-history-list');
    }
});

test('evaluatee score component shows shared histories without reviewer reason fields', function () {
    $source = file_get_contents(resource_path('views/components/unified-evaluation.blade.php'));

    expect($source)
        ->toContain('score-change-history-list')
        ->not->toContain('data-score-change-reason');
});

test('evaluation form scripts validate changed score reasons', function () {
    foreach ([
        'partials/evaluatee-evaluation-script.blade.php',
        'partials/evaluation-form-script.blade.php',
    ] as $viewPath) {
        expect(file_get_contents(resource_path("views/{$viewPath}")))
            ->toContain('window.validateScoreChangeReasons');
    }
});
