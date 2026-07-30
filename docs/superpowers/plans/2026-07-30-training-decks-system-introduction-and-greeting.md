# MSU EVA System Introduction and Greeting Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Expand both facilitator decks from 16 to 18 slides by adding a system-functions slide, a five-role slide, and natural morning/afternoon greeting scripts.

**Architecture:** Continue using the verified JavaScript presentation builder under the conversation-specific temporary workspace. Add two shared slide helpers, insert two scripts into each role-specific notes sequence, renumber the existing deck calls, and overwrite only the two `ฉบับวิทยากร` outputs.

**Tech Stack:** JavaScript ES modules, `@oai/artifact-tool`, Microsoft PowerPoint COM rendering, PowerPoint Open XML package inspection

## Global Constraints

- Each deck contains exactly 18 slides and 18 Speaker Notes pages.
- Preserve the approved branding, colors, logo placement, typography, and 16:9 layout.
- Preserve `[Sources]` in every notes page.
- Slide 1 begins with the approved natural greeting for its session.
- Slide 3 explains five main system functions.
- Slide 4 explains five roles from `database/seeders/RoleSeeder.php`.
- Admin owns users, permissions, criteria, evaluation periods, assignments, and reviewer order.
- Participants do not create or select evaluation periods.
- Reviewer stages follow `app/Support/AssignmentFlow.php` and may omit some reviewer roles.
- Do not imply that the executive always closes the work.
- Preserve unrelated repository changes.

---

## File Structure

- Read: `docs/superpowers/specs/2026-07-30-training-decks-system-introduction-design.md`
- Modify: `C:\Users\pisut\AppData\Local\Temp\codex-presentations\training-facilitator-2026-07-30\tmp\build-facilitator-decks.mjs`
- Modify: `docs/MSU-EVA-อบรมสายวิชาการ-ฉบับวิทยากร.pptx`
- Modify: `docs/MSU-EVA-อบรมสายสนับสนุน-ฉบับวิทยากร.pptx`
- Validate: `database/seeders/RoleSeeder.php`
- Validate: `app/Support/AssignmentFlow.php`
- Validate: `app/Http/Controllers/AssignmentDataController.php`
- Validate: `docs/user-guide-th.md`

### Task 1: Update Greeting Scripts and Notes Indexes

**Files:**
- Modify: `C:\Users\pisut\AppData\Local\Temp\codex-presentations\training-facilitator-2026-07-30\tmp\build-facilitator-decks.mjs`

**Interfaces:**
- Consumes: existing `SCRIPTS.academic`, `SCRIPTS.support`, and `finish(slide, theme, page, noteSources)`
- Produces: two 18-entry script arrays whose indexes match the new slide order

- [ ] **Step 1: Replace the academic opening script**

Use:

```javascript
`[พูด] สวัสดีทุกท่าน ขอต้อนรับเข้าสู่การอบรมการใช้งานระบบ MSU EVA สำหรับบุคลากรสายวิชาการ วันนี้มีทั้งท่านที่เคยร่วมทดสอบระบบแล้ว ท่านที่เพิ่งเริ่มใช้งาน รวมถึงผู้ประเมิน กรรมการ และผู้บริหาร เราจะเริ่มจากทำความรู้จักระบบ บทบาท และฟังก์ชันที่เกี่ยวข้อง จากนั้นผมจะสาธิตการกรอกแบบประเมินหนึ่งรายการให้ดูก่อน แล้วทุกท่านจะได้ทดลองด้วยบัญชีที่เตรียมไว้ เป้าหมายคือให้ทุกคนเข้าใจทั้งงานของตนเองและขั้นตอนที่ส่งต่อไปยังบุคคลถัดไป`
```

- [ ] **Step 2: Replace the support opening script**

Use:

```javascript
`[พูด] สวัสดีทุกท่าน สำหรับช่วงบ่ายนี้เราจะอบรมการใช้งานระบบ MSU EVA สำหรับบุคลากรสายสนับสนุน มีทั้งท่านที่เคยทดลองระบบแล้วและท่านที่เพิ่งใช้งานครั้งแรก รวมถึงผู้ประเมิน กรรมการ และผู้บริหาร เราจะเริ่มจากภาพรวมของระบบและหน้าที่ของแต่ละบทบาท ก่อนดูวิธีบันทึกกิจกรรม ตรวจคะแนน แนบหลักฐาน และส่งแบบประเมิน หลังจากชมการสาธิต ทุกท่านจะได้ทดลองทำหนึ่งรายการด้วยตนเอง และหยุดที่การบันทึกร่างก่อนเข้าสู่ขั้นตอนผู้ประเมินร่วมกัน`
```

- [ ] **Step 3: Insert scripts for slides 3 and 4**

Add one complete `[พูด]` script for the five functions and one for the five roles to each array. State that Admin configures evaluation periods and reviewer order.

Expected: `SCRIPTS.academic.length === 18` and `SCRIPTS.support.length === 18`.

- [ ] **Step 4: Add a script count guard**

Before building the decks, add:

```javascript
for (const [key, scripts] of Object.entries(SCRIPTS)) {
  if (scripts.length !== 18) {
    throw new Error(`${key} must contain exactly 18 facilitator scripts`);
  }
}
```

- [ ] **Step 5: Run syntax and content checks**

Run:

```powershell
node --check 'C:\Users\pisut\AppData\Local\Temp\codex-presentations\training-facilitator-2026-07-30\tmp\build-facilitator-decks.mjs'
rg -n 'ครับ/ค่ะ|ผู้บริหาร.*ปิดงาน|เลือก.*รอบประเมิน' 'C:\Users\pisut\AppData\Local\Temp\codex-presentations\training-facilitator-2026-07-30\tmp\build-facilitator-decks.mjs'
```

Expected: syntax passes and no prohibited visible or spoken phrase remains.

### Task 2: Add System Functions and Five Roles Slides

**Files:**
- Modify: `C:\Users\pisut\AppData\Local\Temp\codex-presentations\training-facilitator-2026-07-30\tmp\build-facilitator-decks.mjs`

**Interfaces:**
- Consumes: `addHeader()`, `addText()`, `addShape()`, `finish()`, theme tokens, and source paths
- Produces: `addSystemFunctions(deck, theme, page)` and `addRoleOverview(deck, theme, page)`

- [ ] **Step 1: Add source references**

Add:

```javascript
roles: "database/seeders/RoleSeeder.php",
assignmentController: "app/Http/Controllers/AssignmentDataController.php",
```

- [ ] **Step 2: Implement `addSystemFunctions()`**

Use this signature:

```javascript
function addSystemFunctions(deck, theme, page) {}
```

Title:

```text
MSU EVA ช่วยจัดการงานประเมินตั้งแต่เตรียมรอบจนถึงรายงานผล
```

Five functions:

```text
จัดการผู้ใช้และสิทธิ์
จัดการเกณฑ์และแบบประเมิน
สร้างรอบและมอบหมายงาน
กรอกข้อมูลและประเมินผล
ติดตามสถานะและส่งออกรายงาน
```

Add the clarification:

```text
Admin เตรียมระบบและมอบหมายงาน ก่อนผู้ใช้งานเริ่มดำเนินการ
```

Finish with sources `roles`, `assignmentController`, `guide`, and `flow`.

- [ ] **Step 3: Implement `addRoleOverview()`**

Use this signature:

```javascript
function addRoleOverview(deck, theme, page) {}
```

Title:

```text
ระบบมี 5 บทบาท และแต่ละคนเห็นงานต่างกัน
```

Rows:

```text
Admin — ผู้ใช้ สิทธิ์ เกณฑ์ รอบประเมิน งาน และลำดับผู้ประเมิน
ผู้รับการประเมิน — กรอกข้อมูล แนบหลักฐาน บันทึกร่าง และส่งแบบประเมิน
ผู้ประเมิน — ตรวจ ให้คะแนนหรือความเห็น และส่งต่องาน
กรรมการ — ตรวจและรับรองผลตามขั้นที่ได้รับมอบหมาย
ผู้บริหาร — ตรวจหรือยืนยันตามลำดับ และดูรายงานตามสิทธิ์
```

Add the clarification:

```text
แต่ละรอบอาจใช้ผู้ประเมินไม่ครบทุกบทบาท ให้ยึดลำดับที่ Admin กำหนด
```

Finish with sources `roles`, `assignmentController`, and `flow`.

- [ ] **Step 4: Update page totals**

Change both footer locations from `/ 16` to `/ 18`.

- [ ] **Step 5: Inspect the presentation model**

Expected:

```text
Slide 3 contains all five function labels.
Slide 4 contains Admin, ผู้รับการประเมิน, ผู้ประเมิน, กรรมการ, ผู้บริหาร.
No title or role row overflows its text box.
```

### Task 3: Insert Slides and Renumber Both Decks

**Files:**
- Modify: `C:\Users\pisut\AppData\Local\Temp\codex-presentations\training-facilitator-2026-07-30\tmp\build-facilitator-decks.mjs`

**Interfaces:**
- Consumes: Task 1 scripts and Task 2 slide helpers
- Produces: 18-slide `buildAcademicDeck()` and `buildSupportDeck()`

- [ ] **Step 1: Insert the new slides after the outcome slide**

For each deck:

```javascript
addSystemFunctions(deck, t, 3);
addRoleOverview(deck, t, 4);
addWorkflow(deck, t, 5, ...);
```

- [ ] **Step 2: Renumber the academic deck**

Use:

```text
Workflow 5
Role 6
Dashboard 7
Relationship 8
Quantity 9
Quality 10
Evidence 11
Decision 12
Review 13
Reviewer checklist 14
Reviewer handoff 15
Completed 16
Practice 17
Troubleshooting 18
```

- [ ] **Step 3: Renumber the support deck**

Use:

```text
Workflow 5
Role 6
Dashboard 7
Relationship 8
Support entry 9
Formula 10
Score example 11
Evidence 12
Decision 13
Review 14
Reviewer checklist 15
Reviewer handoff 16
Practice 17
Troubleshooting 18
```

- [ ] **Step 4: Build both decks through the Node REPL**

Add the module directory:

```text
C:\Users\pisut\.cache\codex-runtimes\codex-primary-runtime\dependencies\node\node_modules
```

Import the builder with a cache-busting query.

Expected:

```text
Academic: 18 slides
Support: 18 slides
```

- [ ] **Step 5: Remove scratch inspection files outside the temporary workspace**

Delete only:

```text
docs/MSU-EVA-อบรมสายวิชาการ-ฉบับวิทยากร.pptx.inspect.ndjson
docs/MSU-EVA-อบรมสายสนับสนุน-ฉบับวิทยากร.pptx.inspect.ndjson
```

Expected: final deliverables remain and no scratch inspection file is present under `docs/`.

### Task 4: Render, Verify, and Commit the Revised Decks

**Files:**
- Modify: `docs/MSU-EVA-อบรมสายวิชาการ-ฉบับวิทยากร.pptx`
- Modify: `docs/MSU-EVA-อบรมสายสนับสนุน-ฉบับวิทยากร.pptx`

**Interfaces:**
- Consumes: Task 3 final PowerPoint files
- Produces: visually verified and committed 18-slide facilitator decks

- [ ] **Step 1: Inspect PowerPoint packages**

Expected for each file:

```text
ppt/slides/slide*.xml = 18
ppt/notesSlides/notesSlide*.xml = 18
Every notes page contains [พูด] and [Sources]
```

- [ ] **Step 2: Render every slide at 1600 × 900**

Export both decks through Microsoft PowerPoint.

Expected: 18 PNG files for each deck with correct Thai glyphs and brand marks.

- [ ] **Step 3: Inspect all 36 renders**

Check:

```text
Greeting and opening sequence are coherent.
Slide 3 shows five functions.
Slide 4 shows five roles.
No clipping, unintended overlap, or unexpected title wrap.
Page markers show / 18.
```

- [ ] **Step 4: Run PowerPoint bounds and overflow checks**

For every shape:

```text
Left >= 0
Top >= 0
Left + Width <= SlideWidth
Top + Height <= SlideHeight
TextFrame2.Overflowing is false
```

Expected: zero canvas violations and zero overflowing text.

- [ ] **Step 5: Verify role and greeting contracts**

Inspect visible text and notes.

Expected:

```text
Five roles appear in both decks.
Admin ownership of evaluation periods appears in both decks.
Academic slide 1 contains the morning greeting.
Support slide 1 contains the afternoon greeting.
No participant instruction says to select or create an evaluation period.
```

- [ ] **Step 6: Commit only the revised decks**

Run:

```powershell
git add -- 'docs/MSU-EVA-อบรมสายวิชาการ-ฉบับวิทยากร.pptx' 'docs/MSU-EVA-อบรมสายสนับสนุน-ฉบับวิทยากร.pptx'
git commit -m 'docs: introduce MSU EVA roles and functions'
```

- [ ] **Step 7: Inspect final repository state**

Run:

```powershell
git status --short
git diff --check
git show --stat --oneline HEAD
```

Expected: the task commit contains only the two facilitator decks and unrelated repository changes remain untouched.
