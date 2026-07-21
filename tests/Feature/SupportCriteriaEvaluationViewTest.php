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
        'target_value' => '12.00',
        'weight' => '20.00',
        'require_evidence' => true,
        'allow_activity_entries' => false,
        'activity_entries' => [],
        'achieved_score' => '125.50',
        'weighted_score' => '25.10',
        'modification_reason' => null,
        'evidence_links' => ['https://example.com/evidence'],
        'histories' => [],
    ];
}

function supportActivityViewItem(): array
{
    return array_replace(supportViewItem(), [
        'allow_activity_entries' => true,
        'activity_entries' => [[
            'id' => 41,
            'sequence' => 1,
            'content' => '<p><strong>โครงการประจำเดือน</strong></p><script>alert(1)</script>',
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
        ->toContain('data-support-evidence-count="7"')
        ->toContain('data-support-evidence-open="7"')
        ->toContain('data-support-manage-open="7"')
        ->toContain('data-support-editor-store')
        ->toContain('data-support-modal')
        ->toContain('data-support-modal-body')
        ->toContain('data-support-modal-save')
        ->toContain('aria-label="แก้ไขข้อมูลสำหรับ จัดทำรายงาน"')
        ->toContain('aria-label="ดูหลักฐานของ จัดทำรายงาน 1 ลิงก์"')
        ->toContain('aria-label="ลิงก์หลักฐานสำหรับ จัดทำรายงาน"')
        ->toContain('id="support-modal-errors"')
        ->toContain('aria-live="assertive"')
        ->not->toContain('border-t border-amber-200 bg-white p-4 sm:p-5');

    expect(substr_count($html, 'data-support-modal role="dialog"'))->toBe(1);
    expect(substr_count($html, 'name="support_list[7][achieved_score]"'))->toBe(1);
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
        ->toContain('data-support-evidence-open="7"')
        ->toContain('target="_blank"')
        ->toContain('rel="noopener noreferrer"');
});

test('evaluatee can add edit and delete optional support activity entries', function () {
    $html = view('components.support-criteria-table', [
        'items' => [supportActivityViewItem()],
        'readonly' => false,
        'evidenceEditable' => true,
        'requireReason' => false,
        'activityEntryRole' => 'evaluatee',
    ])->render();

    expect($html)
        ->toContain('<strong>โครงการประจำเดือน</strong>')
        ->not->toContain('alert(1)')
        ->toContain('data-add-support-activity="7"')
        ->toContain('data-remove-support-activity')
        ->toContain('support_list[7][activity_entries][0][id]')
        ->toContain('support_list[7][activity_entries][0][content]')
        ->toContain('data-support-activity-content')
        ->toContain('support-activity-richtext');
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
        ->toContain('เหตุผลที่แก้ไขกิจกรรม/โครงการ')
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
        ->not->toContain('alert(1)')
        ->not->toContain('support_list[7][activity_entries]')
        ->not->toContain('data-support-activity-content')
        ->not->toContain('data-add-support-activity')
        ->not->toContain('data-remove-support-activity');
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
        ->toContain('data-support-evidence-open="7"')
        ->toContain('https://example.com/evidence');
});

test('read only support table shows evidence count without management controls', function () {
    $html = view('components.support-criteria-table', [
        'items' => [supportViewItem()],
        'readonly' => true,
        'evidenceEditable' => false,
        'requireReason' => false,
    ])->render();

    expect($html)
        ->toContain('data-support-evidence-count="7"')
        ->toContain('data-support-evidence-open="7"')
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
        ->toContain('__supportCriteriaBound')
        ->toContain('const openSupportModal =')
        ->toContain('const closeSupportModal =')
        ->toContain('const snapshotSupportItem =')
        ->toContain('const restoreSupportItem =')
        ->toContain('const updateSupportRow =')
        ->toContain("'[data-support-manage-open]'")
        ->toContain("'[data-support-evidence-open]'")
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
        ->toContain("'support-modal-errors'");

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
        ->toContain('w-[19%]')
        ->toContain('w-[28%]')
        ->toContain('break-words')
        ->not->toContain('overflow-x-auto')
        ->not->toContain('min-w-[1180px]')
        ->not->toContain('min-w-[240px]')
        ->not->toContain('min-w-[360px]')
        ->not->toContain('md:table')
        ->not->toContain('md:hidden');
});
