import test from 'node:test';
import assert from 'node:assert/strict';

import { formatWorkloadFormulaPreview } from '../../resources/js/workload-formula-preview.js';

test('formats built-in and custom variables with readable operators', () => {
    const preview = formatWorkloadFormulaPreview('(item_star*num_1*num_3)/num_2', {
        num_1: 'หน่วยกิต',
        num_2: 'จำนวนนักศึกษา',
        num_3: 'จำนวนผู้สอน',
    });

    assert.equal(preview, '(ค่าภารงาน×หน่วยกิต×จำนวนผู้สอน)÷จำนวนนักศึกษา');
});

test('matches complete identifiers case-insensitively and preserves unknown identifiers', () => {
    const preview = formatWorkloadFormulaPreview('SUM(NUM_1,num_10,unknown)', {
        num_1: 'หน่วยกิต',
        num_10: 'จำนวนกลุ่ม',
    });

    assert.equal(preview, 'SUM(หน่วยกิต,จำนวนกลุ่ม,unknown)');
});

test('formats subject credit variables and returns guidance for an empty formula', () => {
    assert.equal(
        formatWorkloadFormulaPreview('credits+lecture_credits+lab_credits+self_study_credits'),
        'หน่วยกิตรวม+หน่วยกิตบรรยาย+หน่วยกิตปฏิบัติ+หน่วยกิตศึกษาด้วยตนเอง',
    );
    assert.equal(formatWorkloadFormulaPreview('  '), 'พรีวิวจะแสดงเมื่อระบุสูตรการคำนวณ');
});
