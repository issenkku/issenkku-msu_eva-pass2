import assert from 'node:assert/strict';
import fs from 'node:fs';
import path from 'node:path';
import test from 'node:test';

const root = process.cwd();
const read = (relativePath) => fs.readFileSync(path.join(root, relativePath), 'utf8');

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
    assert.match(minutes, /มติ.*ผู้รับผิดชอบ.*กำหนด/s);
    assert.match(checklist, /08:15/);
    assert.match(checklist, /08:30/);
    assert.match(checklist, /08:45/);
});
