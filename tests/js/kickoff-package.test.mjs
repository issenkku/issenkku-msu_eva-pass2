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
