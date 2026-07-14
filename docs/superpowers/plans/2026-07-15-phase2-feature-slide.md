# Phase 2 Feature Slide Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan.

**Goal:** Replace the status-based TOR slide with a clear six-feature Phase 2 slide, update the Thai speaking script, and remove the incorrect implication that Phase 2 must integrate with a separate Phase 1 system.

**Architecture:** Keep `scripts/generate-kickoff-pptx.cjs` as the source of truth for the six-slide deck. Model slide 3 as six feature objects with Thai role labels, render them as a two-by-three card grid, and keep the runbook, TOR matrix, and demo checklist aligned with the same-system workflow confirmed in the approved design. Preserve the user's existing PowerPoint and runbook edits before regenerating binary output.

**Tech Stack:** Node.js, PptxGenJS, Node test runner, Markdown, PowerPoint COM export for visual verification.

## Global Constraints

- Use the approved wording in `docs/superpowers/specs/2026-07-14-kickoff-system-preview-design.md` as the content authority.
- Treat Phase 2 as a workload module inside the existing evaluation application, not as an external system connected to Phase 1.
- Do not show development-status columns, checkmarks, UAT status, manuals, training, installation, or Hypercare on slide 3.
- Preserve the user's unstaged runbook edit at the opening script unless the user explicitly asks to change it.
- Do not modify the untracked UAT DOCX or `scripts/generate-uat-summary-docx.py`.
- Do not overwrite the currently modified PPTX until its slide text has been compared with the generated/source version and any unique user edits have been preserved.
- Use Thai role names on slide 3: `ผู้ดูแลระบบ`, `ผู้รับการประเมิน`, `ระบบ`, `ผู้บริหาร`, `กรรมการ`, and `ผู้ประเมิน`.

---

### Task 1: Lock the approved slide contract in tests

**Files:**
- Modify: `tests/js/kickoff-package.test.mjs:40-50`
- Test: `tests/js/kickoff-package.test.mjs`

**Step 1: Add the failing feature-slide assertions**

Replace the current generic `พร้อมสาธิต` deck assertion with assertions that describe the approved content:

```js
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
```

**Step 2: Run the test to verify it fails**

Run: `npm run test:js`

Expected: FAIL because slide 3 still has `title: 'ฟีเจอร์เทียบกับข้อกำหนด TOR'`, a `statuses` array, and separate-Phase-1 wording.

**Step 3: Commit the red test**

```powershell
git add -- tests/js/kickoff-package.test.mjs
git commit -m "test: define Phase 2 feature slide contract"
```

---

### Task 2: Replace the status slide data with six Phase 2 features

**Files:**
- Modify: `scripts/generate-kickoff-pptx.cjs:41-119`
- Test: `tests/js/kickoff-package.test.mjs`

**Step 1: Update the problem statement and same-system wording**

Change slide 2 so its content matches the approved wording:

```js
problems: [
    { number: '01', title: 'คำนวณนอกโมดูล', text: 'คำนวณคะแนนภาระงานแยกจากแบบประเมิน' },
    { number: '02', title: 'กรอกคะแนนซ้ำ', text: 'นำคะแนนกลับมากรอกซ้ำในแบบประเมิน' },
    { number: '03', title: 'ตรวจสอบยาก', text: 'ใช้เวลาและเสี่ยงต่อความคลาดเคลื่อนของข้อมูล' },
],
goal: 'Phase 2 เพิ่มการบันทึกภาระงาน การแนบหลักฐาน และการคำนวณคะแนนอัตโนมัติภายในระบบประเมินเดิม',
notes:
    'อธิบายปัญหาการคำนวณคะแนนภาระงานนอกโมดูลและการนำคะแนนกลับมากรอกซ้ำ แล้วเน้นว่า Phase 2 ทำงานภายในระบบประเมินเดิม',
```

**Step 2: Replace slide 3's `statuses` data with `features`**

Use this exact slide definition:

```js
{
    id: 'features',
    title: 'ฟีเจอร์หลักของ Phase 2',
    subtitle: 'โมดูลบริหารจัดการภาระงานภายในระบบประเมินเดิม',
    features: [
        { roles: ['ผู้ดูแลระบบ'], title: 'กำหนดแบบฟอร์มภาระงาน', color: 'green' },
        { roles: ['ผู้ดูแลระบบ'], title: 'กำหนดสูตรคำนวณคะแนน', color: 'blue' },
        { roles: ['ผู้รับการประเมิน'], title: 'บันทึกและแก้ไขข้อมูลภาระงาน', color: 'teal' },
        { roles: ['ผู้รับการประเมิน'], title: 'แนบลิงก์หลักฐาน', color: 'green' },
        { roles: ['ระบบ'], title: 'คำนวณคะแนนและนำไปใช้ในแบบประเมิน', color: 'blue' },
        {
            roles: ['ผู้ดูแลระบบ', 'ผู้บริหาร', 'กรรมการ', 'ผู้ประเมิน'],
            title: 'รายงานและส่งออกข้อมูลภาระงาน',
            color: 'teal',
        },
    ],
    notes:
        'Phase 2 มีฟีเจอร์หลักหกส่วนที่ทำงานต่อเนื่องกันครับ เริ่มจากผู้ดูแลระบบกำหนดแบบฟอร์มภาระงานและสูตรคำนวณคะแนน จากนั้นผู้รับการประเมินบันทึกหรือแก้ไขข้อมูลภาระงานพร้อมแนบลิงก์หลักฐาน เมื่อบันทึกแล้วระบบจะคำนวณคะแนนอัตโนมัติและนำคะแนนไปใช้ในแบบประเมิน สุดท้ายผู้ที่มีสิทธิ์สามารถดูรายงานและส่งออกข้อมูลภาระงานได้ ทั้งหมดนี้ทำงานอยู่ภายในระบบประเมินเดิมครับ',
},
```

**Step 3: Correct the remaining deck wording**

- Slide 4 guardrail: replace the warning about incomplete Phase 1 integration with `เดโมด้วยข้อมูลตัวอย่าง • ไม่แก้ข้อมูลจริง • แสดงคะแนนในรายงานและแบบประเมินเดียวกัน`.
- Slide 4 notes: end with showing that the calculated workload score is stored and used by the evaluation report.
- Slide 5 phase 3: use title `ทดสอบ Workflow & UAT` and detail `ทดสอบกับกระบวนการประเมินและรวบรวมข้อแก้ไข`.
- Slide 6 decision 3: use `การจับคู่คะแนนกับรายงาน เกณฑ์ย่อย และผู้ตรวจสอบความถูกต้อง`.

**Step 4: Run the tests to confirm the data contract is green**

Run: `npm run test:js`

Expected: the feature-slide content assertions pass; the generated-PPTX package assertion remains valid because the existing binary is still present.

**Step 5: Commit the slide data change**

```powershell
git add -- scripts/generate-kickoff-pptx.cjs
git commit -m "feat: redefine Phase 2 feature slide content"
```

---

### Task 3: Render slide 3 as a two-by-three feature grid

**Files:**
- Modify: `scripts/generate-kickoff-pptx.cjs:385-451`
- Test: `tests/js/kickoff-package.test.mjs`

**Step 1: Extend the palette helper for teal cards**

Change the palette helper so `green`, `blue`, and `teal` return explicit strong/light pairs. Do not fall back to amber for feature cards.

```js
function featurePalette(name) {
    if (name === 'green') return { strong: COLORS.green, light: COLORS.greenLight };
    if (name === 'blue') return { strong: COLORS.blue, light: COLORS.blueLight };
    return { strong: COLORS.teal, light: COLORS.tealLight };
}
```

**Step 2: Replace `renderFeatures`**

Implement a two-row, three-column layout:

- Add `definition.subtitle` at `x: 0.7, y: 1.16, w: 11.9, h: 0.35`, 14 pt, slate.
- Card width `3.78`, height `2.08`.
- Columns at `x = 0.62 + column * 4.13`.
- Rows at `y = 1.72 + row * 2.36`.
- Give each card a 0.12-inch colored top bar.
- Render role labels as small rounded pills above the feature title.
- For cards with one role, use one pill; for the report card, lay out four compact pills on two lines so every Thai role is readable.
- Render the feature title at 18 pt, bold, navy, centered vertically in the remaining card area.
- Do not render `✓`, status labels, or a TOR/UAT disclaimer.

Use `feature.roles.forEach(...)` to render the pills rather than joining the roles into an unreadably long line. Keep each pill at least 0.66 inches wide and reduce the report-card pill font to 9 pt only if required to avoid clipping.

**Step 3: Run the Node tests**

Run: `npm run test:js`

Expected: PASS.

**Step 4: Commit the renderer**

```powershell
git add -- scripts/generate-kickoff-pptx.cjs
git commit -m "feat: render Phase 2 features as role cards"
```

---

### Task 4: Align the facilitator script and supporting meeting documents

**Files:**
- Modify: `docs/kickoff/2026-07-15-facilitator-runbook.md:18-103`
- Modify: `docs/kickoff/2026-07-15-facilitator-runbook.md:159-200`
- Modify: `docs/kickoff/2026-07-15-tor-system-matrix.md:27-47`
- Modify: `docs/kickoff/2026-07-15-demo-checklist.md:53-78`
- Test: `tests/js/kickoff-package.test.mjs`

**Step 1: Update runbook sections 2 and 3**

- Rename agenda/section 3 to `ฟีเจอร์หลักของ Phase 2`.
- In section 2, use the two approved explanations exactly:
  - `อธิบายปัญหาการคำนวณคะแนนภาระงานนอกโมดูล และการนำคะแนนกลับมากรอกซ้ำในแบบประเมิน`
  - `อธิบายว่า Phase 2 เพิ่มการบันทึกภาระงาน การแนบหลักฐาน และการคำนวณคะแนนอัตโนมัติภายในระบบประเมินเดิม`
- Replace section 3's three status groups with the six feature/role pairs from the spec.
- Replace the 1–2 minute status script with the same approximately one-minute workflow script used in the slide speaker notes.
- Remove the paragraph explaining checkmarks and the `ประโยคป้องกันการรับปากเกินจริง` subsection from slide 3.
- Transition to the demo with: `ต่อไปผมจะสาธิตลำดับการทำงานจริง ตั้งแต่ผู้ดูแลระบบกำหนดแบบฟอร์ม ไปจนถึงการนำคะแนนไปใช้ในแบบประเมินครับ`.

**Step 2: Correct the demo and decision wording**

- Rename demo step 5 to `คะแนนในแบบประเมินและประวัติการแก้ไข`.
- Say that the calculated workload score is stored against the same report/sub-criterion and is used in the evaluation form; then mention Audit Log.
- Close the demo by saying the workflow is inside the same evaluation system.
- In the 90-day script, replace `Integration และ UAT` with `ทดสอบการทำงานร่วมกับกระบวนการประเมินและทำ UAT`.
- Replace decision item 3 with confirmation of score-to-report/sub-criterion mapping and the person responsible for checking correctness.

**Step 3: Correct the matrix and checklist without hiding delivery work**

- Matrix row `ส่งคะแนนเข้าสู่ระบบประเมินหลัก Phase 1`: rename it to `นำคะแนนภาระงานไปใช้ในแบบประเมินเดิม`, set it to `ยืนยันด้วยหลักฐาน`, and cite `app/Http/Controllers/Evaluatee/EvaluationWorkloadController.php`, `app/Models/QuantityScore.php`, and the relevant evaluation/workload tests.
- Matrix closing guidance: replace separate-system integration cautions with verification of report/sub-criterion mapping and displayed score.
- Keep report export, formal UAT, manuals, training, installation, and Hypercare as delivery/acceptance items; do not move those claims onto slide 3.
- Checklist: replace `ไม่กล่าวว่า Phase 1 เชื่อมต่อสมบูรณ์...` with a check that the score is shown in the same report/evaluation flow.
- Checklist follow-up: replace `Integration` ownership with ownership for score mapping, sample data, and UAT.

**Step 4: Add a consistency assertion**

In the facilitator-documents test, add:

```js
assert.match(runbook, /ฟีเจอร์หลักของ Phase 2/);
assert.match(runbook, /ภายในระบบประเมินเดิม/);
assert.doesNotMatch(runbook + checklist, /เชื่อมต่อ Phase 1|วิธีเชื่อม Phase 1|ระบบภายนอก/);
```

**Step 5: Run tests**

Run: `npm run test:js`

Expected: PASS.

**Step 6: Stage only intended runbook hunks and commit**

Because the runbook already has a user edit near line 30, inspect `git diff` and use interactive/patch staging so that line is not accidentally included:

```powershell
git diff -- docs/kickoff/2026-07-15-facilitator-runbook.md
git add -p -- docs/kickoff/2026-07-15-facilitator-runbook.md
git add -- docs/kickoff/2026-07-15-tor-system-matrix.md docs/kickoff/2026-07-15-demo-checklist.md tests/js/kickoff-package.test.mjs
git diff --cached --check
git commit -m "docs: align kickoff script with same-system workflow"
```

---

### Task 5: Preserve user PowerPoint edits and regenerate the deck

**Files:**
- Inspect: `docs/kickoff/2026-07-15-kickoff-system-preview.pptx`
- Modify: `docs/kickoff/2026-07-15-kickoff-system-preview.pptx`
- Source: `scripts/generate-kickoff-pptx.cjs`

**Step 1: Confirm PowerPoint is not holding a lock**

Run:

```powershell
Get-ChildItem -LiteralPath docs/kickoff -Force | Where-Object Name -Like '~$*kickoff*.pptx'
```

Expected: no lock file. If a lock exists, stop and ask the user to close the presentation.

**Step 2: Compare the modified PPTX with the source-controlled deck**

- Extract slide text from the current PPTX with PowerPoint COM or OOXML.
- Extract the `HEAD` version to a temporary directory and extract its slide text the same way.
- Compare slide-by-slide text and speaker notes.
- If the current PPTX contains unique user changes not represented in `scripts/generate-kickoff-pptx.cjs` or the approved spec, stop before overwriting and ask which changes to retain.
- If the difference is only generated content/metadata or changes already captured in the source, continue.

**Step 3: Save a temporary safety copy outside the repository**

Copy the current PPTX to a timestamped file under `$env:TEMP` before regeneration. Do not add the safety copy to Git.

**Step 4: Regenerate the deck**

Run: `node scripts/generate-kickoff-pptx.cjs`

Expected: `Generated docs\kickoff\2026-07-15-kickoff-system-preview.pptx`.

**Step 5: Verify OOXML content**

Run: `npm run test:js`

Expected: all tests pass and the binary is a non-empty OOXML package.

---

### Task 6: Visually verify slide 3 and complete the package

**Files:**
- Verify: `docs/kickoff/2026-07-15-kickoff-system-preview.pptx`
- Verify: `docs/kickoff/2026-07-15-facilitator-runbook.md`

**Step 1: Export the regenerated slides to temporary PNG files**

Use PowerPoint COM to open the PPTX read-only and export all six slides to a temporary directory. Close and quit PowerPoint in a `finally` block so the presentation is not left locked.

**Step 2: Inspect slide 3 at original detail**

Confirm visually:

- title is `ฟีเจอร์หลักของ Phase 2`;
- subtitle says the module is inside the existing evaluation system;
- six cards appear in two rows of three;
- every feature and Thai role label is readable;
- the report card's four role pills do not overlap or clip;
- no checkmark or status column remains;
- no red spell-check underline or off-slide text is visible.

If anything clips, adjust only the renderer dimensions/font sizes, regenerate, and export again.

**Step 3: Run final verification**

```powershell
npm run test:js
git diff --check
git status --short
```

Expected:

- all Node tests pass;
- `git diff --check` prints no errors;
- only the intended PPTX/source/docs changes plus the user's pre-existing runbook edit and untracked UAT files remain.

**Step 4: Commit the regenerated binary**

```powershell
git add -- docs/kickoff/2026-07-15-kickoff-system-preview.pptx
git commit -m "docs: regenerate Phase 2 kickoff slides"
```

**Step 5: Report the result**

Provide links to the PowerPoint and facilitator runbook, state the exact test result, and explicitly mention that the user's unrelated UAT files were left untouched.
