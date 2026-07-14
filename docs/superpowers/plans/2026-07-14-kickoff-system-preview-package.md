# แผนจัดทำชุดประชุม Kickoff และสาธิตระบบ

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** จัดทำ PowerPoint ภาษาไทยและเอกสารประกอบที่ช่วยให้ Team Lead นำประชุม Kickoff พร้อมสาธิตระบบวันที่ 15 กรกฎาคม 2569 ได้อย่างถูกต้องตาม TOR และไม่กล่าวเกินสถานะจริงของระบบ

**Architecture:** เก็บเนื้อหาประชุมที่ตรวจสอบย้อนกลับได้ในไฟล์ Markdown แยกตามหน้าที่ แล้วใช้สคริปต์ PptxGenJS สร้าง PowerPoint จากข้อมูลสไลด์ที่ทดสอบได้ ตัวทดสอบ Node ตรวจโครงสร้าง เวลา ภาษาไทย สถานะฟีเจอร์ ข้อมูลลับ และไฟล์ PowerPoint ที่สร้างขึ้น

**Tech Stack:** Markdown, Node.js, PptxGenJS 4.0.1, Node built-in test runner, Laravel routes/views/tests เป็นหลักฐานฟีเจอร์

## Global Constraints

- เนื้อหาหลักและสคริปต์พูดต้องเป็นภาษาไทย
- ใช้คำบทบาทให้ตรงกับ TOR: Admin, Manager, Director, Evaluator และ Evaluatee
- วาระหลักรวม 60 นาที และวาระย่อรวม 30 นาที
- แยกสถานะฟีเจอร์เป็น `พร้อมสาธิต`, `ยืนยันด้วยหลักฐาน` และ `อยู่ในแผน/รอข้อสรุป`
- ห้ามแสดงเลขบัตรประชาชน ข้อมูลบัญชีธนาคาร ลายเซ็น ราคา หรือข้อมูลส่วนบุคคลจากสัญญา
- ใช้เฉพาะคำกล่าวที่ตรวจสอบได้จาก Route, View, Test หรือพฤติกรรมของระบบ
- ไม่สร้าง แก้ไข หรือลบข้อมูลจริงระหว่างการตรวจสอบชุดนำเสนอ

---

## โครงสร้างไฟล์

- Create: `docs/kickoff/2026-07-15-tor-system-matrix.md` — ตารางเทียบ TOR สถานะระบบ หลักฐาน และวิธีนำเสนอ
- Create: `docs/kickoff/2026-07-15-facilitator-runbook.md` — วาระ สคริปต์พูด ลำดับเดโม และประโยครับมือสถานการณ์
- Create: `docs/kickoff/2026-07-15-meeting-minutes-template.md` — แบบบันทึกมติ งาน ความเสี่ยง คำถาม และ Parking Lot
- Create: `docs/kickoff/2026-07-15-demo-checklist.md` — เช็กลิสต์ก่อนประชุมและแผนสำรอง 30 นาที
- Create: `scripts/generate-kickoff-pptx.cjs` — ข้อมูลสไลด์ รูปแบบภาพ และตัวสร้าง PowerPoint
- Create: `docs/kickoff/2026-07-15-kickoff-system-preview.pptx` — PowerPoint พร้อมใช้
- Create: `tests/js/kickoff-package.test.mjs` — ตรวจเนื้อหา ตัวสร้างสไลด์ เวลา ภาษาไทย และข้อมูลลับ

### Task 1: จัดทำตารางเทียบ TOR กับหลักฐานในระบบ

**Files:**
- Create: `docs/kickoff/2026-07-15-tor-system-matrix.md`
- Create: `tests/js/kickoff-package.test.mjs`

**Interfaces:**
- Consumes: `routes/report.php`, `routes/web.php`, `resources/views/workload/`, `resources/views/partials/evaluatee-evaluation-script.blade.php`, `tests/Feature/WorkloadConfigControllerTest.php`, `tests/Feature/UatAuditLogTest.php`, `tests/js/workload-formula-preview.test.mjs`
- Produces: ตาราง Markdown ที่เอกสารและสไลด์งานถัดไปใช้อ้างอิง โดยมีคอลัมน์ `หัวข้อ TOR`, `สถานะ`, `สิ่งที่แสดง`, `หลักฐานใน repo`, `คำพูดที่ปลอดภัย`

- [ ] **Step 1: เขียนการทดสอบโครงสร้างและความปลอดภัยของตาราง**

เพิ่ม `tests/js/kickoff-package.test.mjs` ให้ตรวจว่าไฟล์มีสถานะครบสามแบบ มีหลักฐานเป็น path ใน repo และไม่มีข้อมูลต้องห้าม:

```js
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
```

- [ ] **Step 2: รันทดสอบและยืนยันว่าไม่ผ่านเพราะยังไม่มีตาราง**

Run: `node --test tests/js/kickoff-package.test.mjs`

Expected: FAIL ด้วย `ENOENT` สำหรับ `docs/kickoff/2026-07-15-tor-system-matrix.md`

- [ ] **Step 3: เขียนตารางเทียบ TOR กับระบบ**

ใส่รายการอย่างน้อยต่อไปนี้พร้อม path และคำพูดที่ใช้ได้จริง:

```markdown
| หัวข้อ TOR | สถานะ | สิ่งที่แสดง | หลักฐานใน repo | คำพูดที่ปลอดภัย |
|---|---|---|---|---|
| Admin กำหนดแบบฟอร์มและ Field | พร้อมสาธิต | หน้า Workload Config และการเพิ่ม Field | `routes/report.php`; `tests/Feature/WorkloadConfigControllerTest.php` | ระบบปัจจุบันรองรับการกำหนดโครงสร้างภาระงานและ Field แล้ว |
| สูตรคำนวณและพรีวิวสูตร | พร้อมสาธิต | สูตร ตัวแปร และข้อความพรีวิวที่อ่านง่าย | `resources/js/workload-formula-preview.js`; `tests/js/workload-formula-preview.test.mjs` | ระบบรองรับสูตรพื้นฐานและ SUM, MAX, MIN พร้อมพรีวิวสูตร |
| Evaluatee เพิ่ม แก้ไข และลบภาระงาน | พร้อมสาธิต | หน้าประเมินตนเองและรายการภาระงาน | `routes/web.php`; `tests/Feature/UatAuditLogTest.php` | ผู้รับการประเมินบันทึกและปรับปรุงรายการภาระงานได้ |
| ลิงก์หลักฐาน | ยืนยันด้วยหลักฐาน | การตรวจ URL และข้อมูล evidence links | `resources/views/partials/evaluatee-evaluation-script.blade.php`; `app/Http/Requests/Workload/StoreWorkloadEntryRequest.php` | ระบบรองรับลิงก์หลักฐานและตรวจรูปแบบ URL |
| Audit Log | ยืนยันด้วยหลักฐาน | ผลทดสอบการบันทึกผู้ดำเนินการและเวลา | `tests/Feature/UatAuditLogTest.php` | ระบบบันทึกการเพิ่ม แก้ไข และลบภาระงานเพื่อการตรวจสอบย้อนหลัง |
| เชื่อมต่อ Phase 1 | อยู่ในแผน/รอข้อสรุป | ไม่สาธิตเป็นระบบเชื่อมต่อจริง | TOR ข้อ 4.4.2 และข้อ 5 | ต้องยืนยันวิธีเชื่อมต่อ รหัสจับคู่ และระบบทดสอบร่วมกับเจ้าของระบบเดิม |
```

เพิ่มรายการ Roles and Permissions, การคำนวณคะแนนใหม่, UAT, ประสิทธิภาพ, HTTPS, คู่มือ และการอบรม โดยไม่เปลี่ยนสถานะเป็น `พร้อมสาธิต` หากไม่มีเส้นทางเดโมที่ตรวจสอบแล้ว

- [ ] **Step 4: รันทดสอบตารางให้ผ่าน**

Run: `node --test tests/js/kickoff-package.test.mjs`

Expected: PASS 1 test

- [ ] **Step 5: Commit ตารางและการทดสอบ**

```bash
git add docs/kickoff/2026-07-15-tor-system-matrix.md tests/js/kickoff-package.test.mjs
git commit -m "docs: map kickoff demo to TOR evidence"
```

### Task 2: จัดทำคู่มือผู้นำประชุมและแบบบันทึก

**Files:**
- Create: `docs/kickoff/2026-07-15-facilitator-runbook.md`
- Create: `docs/kickoff/2026-07-15-meeting-minutes-template.md`
- Create: `docs/kickoff/2026-07-15-demo-checklist.md`
- Modify: `tests/js/kickoff-package.test.mjs`

**Interfaces:**
- Consumes: สถานะและคำพูดจาก `docs/kickoff/2026-07-15-tor-system-matrix.md`
- Produces: เอกสารภาษาไทยที่ Team Lead เปิดใช้ควบคู่กับ PowerPoint และแบบบันทึกที่กรอกได้ทันที

- [ ] **Step 1: เพิ่มการทดสอบเอกสารสามไฟล์**

```js
test('facilitator documents cover timing, recovery, and decision capture', () => {
    const runbook = read('docs/kickoff/2026-07-15-facilitator-runbook.md');
    const minutes = read('docs/kickoff/2026-07-15-meeting-minutes-template.md');
    const checklist = read('docs/kickoff/2026-07-15-demo-checklist.md');

    for (const minute of ['5 นาที', '10 นาที', '15 นาที', '60 นาที', '30 นาที']) {
        assert.match(runbook + checklist, new RegExp(minute));
    }

    assert.match(runbook, /ขอรับเป็น Action Item/);
    assert.match(runbook, /Parking Lot/);
    assert.match(minutes, /มติ.*ผู้รับผิดชอบ.*กำหนด/);
    assert.match(checklist, /08:15/);
    assert.match(checklist, /08:30/);
    assert.match(checklist, /08:45/);
});
```

- [ ] **Step 2: รันทดสอบและยืนยันว่าไม่ผ่านเพราะยังไม่มีเอกสาร**

Run: `node --test tests/js/kickoff-package.test.mjs`

Expected: FAIL ด้วย `ENOENT` สำหรับไฟล์ runbook ไฟล์แรก

- [ ] **Step 3: เขียนคู่มือผู้นำประชุม**

ให้ `2026-07-15-facilitator-runbook.md` มีส่วนต่อไปนี้ครบ:

- ตาราง 60 นาที: เปิด 5, เป้าหมาย 5, ฟีเจอร์เทียบ TOR 10, เดโม 15, แผน 10, มติและขั้นตอนถัดไป 15
- สคริปต์เปิดที่กล่าวถึงผลลัพธ์ 4 ข้อ
- คำเชื่อมก่อนและหลังทุกช่วง
- ลำดับเดโม Admin → สูตร → Evaluatee → หลักฐาน → คะแนน → Audit evidence
- ประโยคสำหรับสิ่งที่ยังอยู่ในแผน: `ส่วนนี้ยังไม่ขอระบุว่าเชื่อมต่อสมบูรณ์ จนกว่าจะยืนยันวิธีเชื่อมต่อและทดสอบร่วมกับระบบ Phase 1 ครับ`
- ประโยคเมื่อไม่ทราบคำตอบ: `ขอรับประเด็นนี้เป็น Action Item ระบุผู้รับผิดชอบและกำหนดวันที่ให้คำตอบครับ`
- ประโยคพักประเด็น: `ขอบันทึกเรื่องนี้ใน Parking Lot ก่อน แล้วจะกลับมาสรุปหลังปิดหัวข้อหลักครับ`
- สคริปต์ปิดที่อ่านทวนมติ ผู้รับผิดชอบ และกำหนดส่ง

- [ ] **Step 4: เขียนแบบบันทึกและเช็กลิสต์**

ให้ minutes template มีตาราง `มติ | เหตุผล | ผู้อนุมัติ`, `งาน | ผู้รับผิดชอบ | กำหนด`, `ความเสี่ยง | ผลกระทบ | ผู้ติดตาม`, `คำถาม | ผู้ตอบ | วันที่ตอบ`, และ `Parking Lot | เจ้าของเรื่อง | นัดหมาย` พร้อมหัวข้อยืนยันวันเริ่มนับ 90 วัน

ให้ checklist แบ่งเป็นก่อนนอน คืนก่อนประชุม เวลา 08:15, 08:30 และหลังประชุม พร้อมบัญชี Admin/Evaluatee, ข้อมูลตัวอย่าง, browser zoom, Teams share, แท็บที่ต้องเปิด และแผนย่อ 30 นาที

- [ ] **Step 5: รันทดสอบเอกสารให้ผ่าน**

Run: `node --test tests/js/kickoff-package.test.mjs`

Expected: PASS 2 tests

- [ ] **Step 6: Commit เอกสารผู้นำประชุม**

```bash
git add docs/kickoff/2026-07-15-facilitator-runbook.md docs/kickoff/2026-07-15-meeting-minutes-template.md docs/kickoff/2026-07-15-demo-checklist.md tests/js/kickoff-package.test.mjs
git commit -m "docs: add Thai kickoff facilitator package"
```

### Task 3: สร้าง PowerPoint ภาษาไทย

**Files:**
- Create: `scripts/generate-kickoff-pptx.cjs`
- Create: `docs/kickoff/2026-07-15-kickoff-system-preview.pptx`
- Modify: `tests/js/kickoff-package.test.mjs`

**Interfaces:**
- Consumes: `pptxgenjs`, `public/favicon-msu.png`, เนื้อหาและสถานะจาก matrix/runbook
- Produces: `SLIDES` จำนวน 6 รายการ, `AGENDA_MINUTES` เท่ากับ 60, ฟังก์ชัน `buildKickoffDeck()` และไฟล์ `.pptx`

- [ ] **Step 1: เพิ่มการทดสอบข้อมูลสไลด์และไฟล์ PowerPoint**

```js
import { createRequire } from 'node:module';

const require = createRequire(import.meta.url);

test('kickoff deck has six Thai sections, correct timing, and safe content', () => {
    const { AGENDA_MINUTES, SLIDES } = require('../../scripts/generate-kickoff-pptx.cjs');
    const text = JSON.stringify(SLIDES);

    assert.equal(SLIDES.length, 6);
    assert.equal(AGENDA_MINUTES.reduce((sum, value) => sum + value, 0), 60);
    assert.match(text, /เป้าหมายโครงการ/);
    assert.match(text, /พร้อมสาธิต/);
    assert.match(text, /แผนดำเนินงาน 90 วัน/);
    assert.doesNotMatch(text, /เลขที่บัญชี|188-8-99289-4|200,000|ลายเซ็น/);
});

test('generated kickoff PowerPoint is a non-empty OOXML package', () => {
    const file = path.join(root, 'docs/kickoff/2026-07-15-kickoff-system-preview.pptx');
    const bytes = fs.readFileSync(file);

    assert.ok(bytes.length > 20000);
    assert.equal(bytes.subarray(0, 2).toString('ascii'), 'PK');
});
```

- [ ] **Step 2: รันทดสอบและยืนยันว่าไม่ผ่านเพราะยังไม่มี generator**

Run: `node --test tests/js/kickoff-package.test.mjs`

Expected: FAIL ด้วย `MODULE_NOT_FOUND` สำหรับ `scripts/generate-kickoff-pptx.cjs`

- [ ] **Step 3: สร้าง generator พร้อมข้อมูลสไลด์หกส่วน**

กำหนด interface ใน `scripts/generate-kickoff-pptx.cjs` ดังนี้:

```js
const AGENDA_MINUTES = [5, 5, 10, 15, 10, 15];

const SLIDES = [
    { id: 'opening', title: 'Kickoff โครงการ Workload Management Module Phase 2' },
    { id: 'objective', title: 'ปัญหาและเป้าหมายโครงการ' },
    { id: 'features', title: 'ฟีเจอร์เทียบกับข้อกำหนด TOR' },
    { id: 'demo', title: 'สาธิต Workflow จาก Admin ถึง Evaluatee' },
    { id: 'timeline', title: 'แผนดำเนินงาน 90 วัน' },
    { id: 'next', title: 'เรื่องที่ต้องได้ข้อสรุปและขั้นตอนถัดไป' },
];

function buildKickoffDeck() {
    const pptx = new PptxGenJS();
    pptx.layout = 'LAYOUT_WIDE';
    pptx.author = 'MSU EVA Project Team';
    pptx.subject = 'Kickoff และสาธิต Workload Management Module Phase 2';
    pptx.title = 'Kickoff Workload Management Module Phase 2';
    pptx.lang = 'th-TH';

    SLIDES.forEach((definition, index) => addKickoffSlide(pptx, definition, index + 1));

    return pptx;
}

module.exports = { AGENDA_MINUTES, SLIDES, buildKickoffDeck };
```

การแสดงผลต้องใช้ฟอนต์ `Tahoma`, พื้นหลัง `F7FAFC`, หัวข้อ `17324D`, จุดเน้น `2A7F80`, สีรอข้อสรุป `D97706`, อัตราส่วน `LAYOUT_WIDE` และ footer แสดง `15 กรกฎาคม 2569` กับหมายเลขสไลด์

เนื้อหาในสไลด์ต้องสรุปจาก runbook ไม่คัดลอกข้อความยาว และใช้ workflow กับ timeline เป็น Shape แทน bullet จำนวนมาก

- [ ] **Step 4: สร้าง PowerPoint**

Run: `node scripts/generate-kickoff-pptx.cjs`

Expected: แสดง `Generated docs/kickoff/2026-07-15-kickoff-system-preview.pptx`

- [ ] **Step 5: รันทดสอบ PowerPoint ให้ผ่าน**

Run: `node --test tests/js/kickoff-package.test.mjs`

Expected: PASS 4 tests

- [ ] **Step 6: Commit generator และ PowerPoint**

```bash
git add scripts/generate-kickoff-pptx.cjs docs/kickoff/2026-07-15-kickoff-system-preview.pptx tests/js/kickoff-package.test.mjs
git commit -m "docs: generate Thai kickoff presentation"
```

### Task 4: ตรวจพร้อมใช้งานและส่งมอบ

**Files:**
- Verify: `docs/kickoff/2026-07-15-kickoff-system-preview.pptx`
- Verify: `docs/kickoff/2026-07-15-facilitator-runbook.md`
- Verify: `docs/kickoff/2026-07-15-tor-system-matrix.md`
- Verify: `docs/kickoff/2026-07-15-meeting-minutes-template.md`
- Verify: `docs/kickoff/2026-07-15-demo-checklist.md`

**Interfaces:**
- Consumes: ไฟล์ทั้งหมดจาก Task 1–3
- Produces: หลักฐานว่าชุดประชุมสร้างซ้ำได้ เนื้อหาครบ ไม่มีข้อมูลลับ และพร้อมเปิดใช้งาน

- [ ] **Step 1: รัน test suite ของชุดประชุม**

Run: `node --test tests/js/kickoff-package.test.mjs`

Expected: PASS 4 tests, FAIL 0

- [ ] **Step 2: รัน test suite JavaScript เดิมเพื่อป้องกัน regression**

Run: `npm run test:js`

Expected: PASS ทุก test, FAIL 0

- [ ] **Step 3: สร้าง PowerPoint ซ้ำและตรวจขนาดไฟล์**

Run: `node scripts/generate-kickoff-pptx.cjs`

Expected: generator จบด้วย exit code 0 และไฟล์ `.pptx` มีขนาดมากกว่า 20,000 bytes

- [ ] **Step 4: ตรวจข้อความค้างและข้อมูลลับ**

Run: `rg -n "TBD|TODO|PLACEHOLDER|188-8-99289-4|0405567005098|เลขที่บัญชี" docs/kickoff scripts/generate-kickoff-pptx.cjs`

Expected: ไม่มีผลลัพธ์และ `rg` คืน exit code 1

- [ ] **Step 5: ตรวจ whitespace และรายการไฟล์**

Run: `git diff --check`

Expected: ไม่มีผลลัพธ์

Run: `Get-ChildItem docs/kickoff | Select-Object Name,Length`

Expected: พบไฟล์ส่งมอบ 5 ไฟล์ และ PowerPoint มีขนาดมากกว่า 20,000 bytes

- [ ] **Step 6: Commit การปรับแก้จากการตรวจขั้นสุดท้าย หากมี**

```bash
git add docs/kickoff scripts/generate-kickoff-pptx.cjs tests/js/kickoff-package.test.mjs
git commit -m "docs: finalize kickoff meeting package"
```
