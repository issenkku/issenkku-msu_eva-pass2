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
        'achieved_score' => '125.50',
        'weighted_score' => '25.10',
        'modification_reason' => null,
        'evidence_links' => ['https://example.com/evidence'],
        'histories' => [],
    ];
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
        ->toContain('hidden md:table')
        ->toContain('md:hidden')
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
        ->not->toContain('border-t border-amber-200 bg-white p-4 sm:p-5');

    expect(substr_count($html, 'data-support-modal role="dialog"'))->toBe(1);
    expect(substr_count($html, 'name="support_list[7][achieved_score]"'))->toBe(1);
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
        ->toContain('https://example.com/evidence');
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
        ->toContain('previouslyFocusedElement.focus()');

    foreach (['unified-evaluation', 'unified-evaluator', 'unified-director'] as $component) {
        $source = file_get_contents(resource_path("views/components/{$component}.blade.php"));
        expect($source)
            ->toContain('support_items')
            ->toContain('support-criteria-table')
            ->toContain('support-criteria-table-script');
    }
});
