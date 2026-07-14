import assert from 'node:assert/strict';
import fs from 'node:fs';
import { createRequire } from 'node:module';
import path from 'node:path';
import test from 'node:test';

const root = process.cwd();
const read = (relativePath) => fs.readFileSync(path.join(root, relativePath), 'utf8');
const require = createRequire(import.meta.url);

test('TOR matrix has traceable statuses and excludes confidential contract data', () => {
    const matrix = read('docs/kickoff/2026-07-15-tor-system-matrix.md');

    for (const status of ['พร้อมสาธิต', 'ยืนยันด้วยหลักฐาน', 'อยู่ในแผน/รอข้อสรุป']) {
        assert.match(matrix, new RegExp(status));
    }

    assert.match(matrix, /routes\/(report|web)\.php/);
    assert.match(matrix, /tests\/(Feature|js)\//);
    assert.doesNotMatch(matrix, /เลขบัตรประชาชน|เลขที่บัญชี|188-8-99289-4|0405567005098/);
});

test('facilitator documents cover timing, recovery, and decision capture', () => {
    const runbook = read('docs/kickoff/2026-07-15-facilitator-runbook.md');
    const minutes = read('docs/kickoff/2026-07-15-meeting-minutes-template.md');
    const checklist = read('docs/kickoff/2026-07-15-demo-checklist.md');

    for (const minute of ['5 นาที', '10 นาที', '15 นาที', '60 นาที', '30 นาที']) {
        assert.match(runbook + checklist, new RegExp(minute));
    }

    assert.match(runbook, /ขอรับเป็น Action Item/);
    assert.match(runbook, /Parking Lot/);
    assert.match(runbook, /ฟีเจอร์หลักของ Phase 2/);
    assert.match(runbook, /ภายในระบบประเมินเดิม/);
    assert.doesNotMatch(runbook + checklist, /เชื่อมต่อ Phase 1|วิธีเชื่อม Phase 1|ระบบภายนอก/);
    assert.match(minutes, /มติ.*ผู้รับผิดชอบ.*กำหนด/s);
    assert.match(checklist, /08:15/);
    assert.match(checklist, /08:30/);
    assert.match(checklist, /08:45/);
});

test('kickoff deck has six Thai sections, correct timing, and safe content', () => {
    const { AGENDA_MINUTES, SLIDES } = require('../../scripts/generate-kickoff-pptx.cjs');
    const text = JSON.stringify(SLIDES);
    const featureSlide = SLIDES.find(({ id }) => id === 'features');

    assert.equal(SLIDES.length, 6);
    assert.equal(AGENDA_MINUTES.reduce((sum, value) => sum + value, 0), 60);
    assert.match(text, /เป้าหมายโครงการ/);
    assert.match(text, /แผนดำเนินงาน 90 วัน/);
    assert.equal(featureSlide.title, 'ฟีเจอร์หลักของ Phase 2');
    assert.equal(featureSlide.subtitle, 'โมดูลบริหารจัดการภาระงานภายในระบบประเมินเดิม');
    assert.equal(featureSlide.features.length, 6);

    for (const feature of [
        'กำหนดแบบฟอร์มภาระงาน',
        'กำหนดสูตรคำนวณคะแนน',
        'บันทึกและแก้ไขข้อมูลภาระงาน',
        'แนบลิงก์หลักฐาน',
        'คำนวณคะแนนและนำไปใช้ในแบบประเมิน',
        'รายงานและส่งออกข้อมูลภาระงาน',
    ]) {
        assert.match(JSON.stringify(featureSlide), new RegExp(feature));
    }

    for (const role of ['ผู้ดูแลระบบ', 'ผู้รับการประเมิน', 'ระบบ', 'ผู้บริหาร', 'กรรมการ', 'ผู้ประเมิน']) {
        assert.match(JSON.stringify(featureSlide), new RegExp(role));
    }

    assert.doesNotMatch(JSON.stringify(featureSlide), /พร้อมสาธิต|ยืนยันด้วยหลักฐาน|อยู่ในแผน|✓/);
    assert.doesNotMatch(text, /ระบบภายนอก|เชื่อมต่อ Phase 1|วิธีเชื่อม Phase 1/);
    assert.doesNotMatch(text, /เลขที่บัญชี|188-8-99289-4|200,000|ลายเซ็น/);
});

test('tailored kickoff PowerPoint keeps five slides and the Phase 2 feature page', async () => {
    const JSZip = require('jszip');
    const file = path.join(root, 'docs/kickoff/2026-07-15-kickoff-system-preview.pptx');
    const bytes = fs.readFileSync(file);

    assert.ok(bytes.length > 20000);
    assert.equal(bytes.subarray(0, 2).toString('ascii'), 'PK');

    const archive = await JSZip.loadAsync(bytes);
    const slidePaths = Object.keys(archive.files).filter((entry) => /^ppt\/slides\/slide\d+\.xml$/.test(entry));
    const slide3 = await archive.file('ppt/slides/slide3.xml').async('string');

    assert.equal(slidePaths.length, 5);
    assert.match(slide3, /ฟีเจอร์หลักของ Phase 2/);
    assert.match(slide3, /โมดูลบริหารจัดการภาระงานภายในระบบประเมินเดิม/);
    assert.match(slide3, /รายงานและส่งออกข้อมูลภาระงาน/);
    assert.doesNotMatch(slide3, /พร้อมสาธิต|ยืนยันด้วยหลักฐาน|อยู่ในแผน|✓/);
});
