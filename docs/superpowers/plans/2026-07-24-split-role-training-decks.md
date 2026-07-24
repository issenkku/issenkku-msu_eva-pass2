# Split Role-Based MSU EVA Training Decks Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build and verify two polished Thai PowerPoint decks for MSU EVA training: one for academic personnel in the morning and one for support personnel in the afternoon.

**Architecture:** Use one shared visual system and two role-specific slide builders in a plain JavaScript ES module backed by `@oai/artifact-tool`. Source system behavior from repository documentation and code, use the faculty seal and supplied IntelliGen logo as branded assets, and export two independent 16:9 PowerPoint files. Render and inspect every slide before delivery.

**Tech Stack:** JavaScript ES modules, `@oai/artifact-tool`, PowerPoint (`.pptx`), PowerPoint COM for SVG rasterization if required, bundled presentation rendering and validation tools

## Global Constraints

- Create two independent Thai-language decks, not a combined deck.
- Each deck contains exactly 14 audience-facing slides.
- Academic deck output: `docs/MSU-EVA-อบรมสายวิชาการ.pptx`.
- Support deck output: `docs/MSU-EVA-อบรมสายสนับสนุน.pptx`.
- Use a “Professional Training” visual direction built from scratch; do not use Codex Grid.
- Use Mahasarakham purple, faculty gold, deep navy, and warm white as the shared palette.
- Use indigo/blue as the academic accent and teal with restrained orange as the support accent.
- Use `public/favicon-msu.png` as the Faculty of Public Health seal.
- Use `C:\Users\pisut\Downloads\logoบริษัท.svg` as the IntelliGen Software Solutions logo.
- The faculty seal is visually primary; the company logo is secondary.
- Use at least 50 pt for deck titles, 35 pt for slide titles, 24 pt for subheadings, and 16 pt for body text.
- The last configured reviewer closes the work; do not imply that an executive always closes it.
- Show the support formula exactly: `คะแนนถ่วงน้ำหนัก = น้ำหนัก × คะแนนที่ทำได้ ÷ 100`.
- Do not expose credentials, personal data, production data, unsupported policy claims, or internal production notes.
- Add `[Sources]` blocks to speaker notes for externally sourced assets and non-trivial claims.
- Preserve all unrelated existing repository changes.

---

## File Structure

- Create: `C:\Users\pisut\AppData\Local\Temp\codex-presentations\split-role-training-2026-07-24\tmp\build-role-decks.mjs` — shared theme, helpers, slide builders, speaker notes, and export logic.
- Create: `C:\Users\pisut\AppData\Local\Temp\codex-presentations\split-role-training-2026-07-24\tmp\source-notes.txt` — verified system claims and asset provenance.
- Create: `C:\Users\pisut\AppData\Local\Temp\codex-presentations\split-role-training-2026-07-24\tmp\assets\company-logo.png` — rasterized copy of the supplied SVG for reliable rendering.
- Create: `C:\Users\pisut\AppData\Local\Temp\codex-presentations\split-role-training-2026-07-24\tmp\qa\academic-ledger.txt` — full-size visual review results for 14 academic slides.
- Create: `C:\Users\pisut\AppData\Local\Temp\codex-presentations\split-role-training-2026-07-24\tmp\qa\support-ledger.txt` — full-size visual review results for 14 support slides.
- Create: `docs/MSU-EVA-อบรมสายวิชาการ.pptx` — final morning deck.
- Create: `docs/MSU-EVA-อบรมสายสนับสนุน.pptx` — final afternoon deck.
- Read without modification: `docs/user-guide-th.md`, `app/Support/AssignmentFlow.php`, support scoring views and tests, and existing training decks.

### Task 1: Verify Content and Prepare Brand Assets

**Files:**
- Create: `C:\Users\pisut\AppData\Local\Temp\codex-presentations\split-role-training-2026-07-24\tmp\source-notes.txt`
- Create: `C:\Users\pisut\AppData\Local\Temp\codex-presentations\split-role-training-2026-07-24\tmp\assets\company-logo.png`
- Read: `docs/user-guide-th.md`
- Read: `app/Support/AssignmentFlow.php`
- Read: `public/favicon-msu.png`
- Read: `C:\Users\pisut\Downloads\logoบริษัท.svg`

**Interfaces:**
- Consumes: approved design specification at `docs/superpowers/specs/2026-07-24-split-role-training-decks-design.md`
- Produces: verified slide claims, asset provenance, faculty seal path, and rasterized company logo path

- [ ] **Step 1: Initialize the artifact workspace**

Run:

```powershell
$env:SKILL_DIR='C:\Users\pisut\.codex\plugins\cache\openai-primary-runtime\presentations\26.723.12215\skills\presentations'
$env:TMP_DIR='C:\Users\pisut\AppData\Local\Temp\codex-presentations\split-role-training-2026-07-24\tmp'
New-Item -ItemType Directory -Force -Path $env:TMP_DIR,(Join-Path $env:TMP_DIR 'assets'),(Join-Path $env:TMP_DIR 'qa') | Out-Null
node "$env:SKILL_DIR\container_tools\setup_artifact_tool_workspace.mjs" --workspace "$env:TMP_DIR"
```

Expected: the workspace resolves `@oai/artifact-tool` and contains `assets` and `qa` directories.

- [ ] **Step 2: Verify system behavior**

Search the source material for:

```text
Draft and submission behavior
Academic quantity and quality data
Support project, activity, indicator, target, weight, achieved score, and evidence behavior
Dynamic evaluator sequence
Final Completed state
Report/export availability
```

Record verified statements and exact repository paths in `source-notes.txt`.

Expected: every instructional claim in the approved slide outlines is backed by a repository source, while unverified policy language is excluded.

- [ ] **Step 3: Prepare the supplied company logo**

Use PowerPoint COM to place `logoบริษัท.svg` on a blank 1600 × 900 slide and export it as `assets\company-logo.png`.

Expected: the PNG has a white background, preserves the red-brown head mark and navy `INTELLIGEN SOFTWARE SOLUTIONS` wordmark, and is readable at 25% scale.

- [ ] **Step 4: Inspect both brand marks**

Open `public/favicon-msu.png` and `assets\company-logo.png` at original size.

Expected:

```text
Faculty seal is not clipped or distorted.
Company logo is not clipped or distorted.
Both images remain legible on warm white.
No recoloring is required.
```

- [ ] **Step 5: Review Task 1**

Confirm that `source-notes.txt` includes the exact support formula, dynamic final-reviewer rule, all asset paths, and no sensitive information.

### Task 2: Build the Academic Training Deck

**Files:**
- Create: `C:\Users\pisut\AppData\Local\Temp\codex-presentations\split-role-training-2026-07-24\tmp\build-role-decks.mjs`
- Create: `docs/MSU-EVA-อบรมสายวิชาการ.pptx`

**Interfaces:**
- Consumes: Task 1 sources and brand assets
- Produces: `buildAcademicDeck()` returning a 14-slide presentation and the exported academic PPTX

- [ ] **Step 1: Create shared presentation helpers**

Implement these plain-JavaScript functions in `build-role-decks.mjs`:

```javascript
function createDeck({ accent, sectionLabel }) {}
function addTitle(slide, title, subtitle) {}
function addBranding(slide, { showSeal, showCompany, page }) {}
function addWorkflowRibbon(slide, { activeStep, accent }) {}
function addSectionDivider(deck, { eyebrow, title, statement, accent }) {}
function addSourceNotes(slide, sourceLines) {}
function buildAcademicDeck() {}
function buildSupportDeck() {}
```

The helpers must use one flat editorial composition, large typography, restrained rounded shapes, consistent margins, and native PowerPoint shapes only for simple workflow relationships.

Expected: a one-slide smoke-test deck exports successfully with Thai text and both brand assets.

- [ ] **Step 2: Implement academic slides 1–5**

Create:

```text
1 การใช้งานระบบ MSU EVA สำหรับสายวิชาการ
2 เช้านี้คุณจะทำงานหนึ่งรายการให้พร้อมส่งประเมิน
3 เห็นเส้นทางทั้งงานก่อนเริ่มกรอก
4 ผู้รับการประเมินรับผิดชอบข้อมูล ก่อนส่งต่อผู้ตรวจตามลำดับ
5 เริ่มจากรอบประเมินและรายการที่ได้รับมอบหมาย
```

Use a minimal branded cover, one outcome composition, one workflow diagram, one role handoff composition, and one annotated dashboard-oriented composition.

Expected: the opening establishes the participant goal, their responsibility, and the complete academic workflow without dense text.

- [ ] **Step 3: Implement academic slides 6–10**

Create:

```text
6 รายวิชา ภาระงาน และผลงานต้องสอดคล้องกัน
7 กรอกข้อมูลเชิงปริมาณ แล้วตรวจค่าที่ระบบคำนวณ
8 ข้อมูลเชิงคุณภาพต้องทำให้ผู้ประเมินเข้าใจผลงาน
9 หลักฐานที่ดีต้องเปิดได้และตรวจสอบย้อนกลับได้
10 บันทึกร่างยังแก้ได้ แต่ส่งแล้วจะเข้าสู่กระบวนการประเมิน
```

Use one relationship diagram, one numeric worked example, one quality/evidence comparison, one evidence checklist, and one high-contrast Draft-versus-Submit decision slide.

Expected: participants can distinguish quantity, quality, evidence, draft, and submission behavior.

- [ ] **Step 4: Implement academic slides 11–14**

Create:

```text
11 ผู้ประเมินแต่ละลำดับตรวจ ให้คะแนน และส่งต่องาน
12 ผู้ตรวจลำดับสุดท้ายรับรองจนสถานะเป็น Completed
13 ภารกิจทดลอง: ทำหนึ่งรายการให้ครบและตรวจความพร้อมก่อนส่ง
14 หากติดขัด ให้ตรวจสถานะ สิทธิ์ และข้อมูลบังคับก่อน
```

End with a practice checklist and actionable recovery guidance, not a generic thank-you slide.

Expected: the close resolves the opening promise and gives participants a usable practice path.

- [ ] **Step 5: Export and inspect academic deck structure**

Run `buildAcademicDeck()` and export to `docs/MSU-EVA-อบรมสายวิชาการ.pptx`.

Expected:

```text
Slides: 14
Aspect ratio: 16:9
Primary accent: indigo/blue
No support-only scoring content
No unresolved placeholder text
```

- [ ] **Step 6: Commit the academic deliverable**

Run:

```powershell
git add -- 'docs/MSU-EVA-อบรมสายวิชาการ.pptx'
git commit -m 'docs: add academic MSU EVA training deck'
```

Expected: one commit containing only the academic PPTX.

### Task 3: Build the Support Training Deck

**Files:**
- Modify: `C:\Users\pisut\AppData\Local\Temp\codex-presentations\split-role-training-2026-07-24\tmp\build-role-decks.mjs`
- Create: `docs/MSU-EVA-อบรมสายสนับสนุน.pptx`

**Interfaces:**
- Consumes: Task 1 sources and assets plus Task 2 shared helpers
- Produces: `buildSupportDeck()` returning a 14-slide presentation and the exported support PPTX

- [ ] **Step 1: Implement support slides 1–5**

Create:

```text
1 การใช้งานระบบ MSU EVA สำหรับสายสนับสนุน
2 บ่ายนี้คุณจะทำกิจกรรมหนึ่งรายการให้ครบและเห็นที่มาของคะแนน
3 เห็นเส้นทางทั้งงานก่อนเริ่มกรอก
4 ผู้รับการประเมินรับผิดชอบผลกิจกรรม ก่อนส่งต่อผู้ตรวจตามลำดับ
5 เริ่มจากรอบประเมินและรายการที่ได้รับมอบหมาย
```

Use the shared system with teal accents while retaining the same family resemblance as the academic deck.

Expected: the support opening is visibly distinct yet structurally familiar.

- [ ] **Step 2: Implement support slides 6–10**

Create:

```text
6 โครงการ กิจกรรม ตัวชี้วัด และหลักฐานต้องเชื่อมกัน
7 กรอกเป้าหมาย ผลที่ทำได้ และรายละเอียดกิจกรรมให้ครบ
8 คะแนนถ่วงน้ำหนัก = น้ำหนัก × คะแนนที่ทำได้ ÷ 100
9 ตัวอย่าง: น้ำหนัก 20 × คะแนนที่ทำได้ 80 ÷ 100 = 16 คะแนน
10 หลักฐานต้องเปิดได้และชี้กลับมาที่กิจกรรม
```

Use one relationship diagram, one activity-entry composition, one large formula slide, one numeric worked example, and one evidence-gate composition.

Expected: formula, example, and evidence relationship are immediately understandable from the projected slide.

- [ ] **Step 3: Implement support slides 11–14**

Create:

```text
11 บันทึกร่างยังแก้ได้ แต่การส่งต้องผ่านข้อมูลบังคับและหลักฐาน
12 ผู้ประเมินตรวจตามลำดับ และผู้ตรวจลำดับสุดท้ายปิดงาน
13 ภารกิจทดลอง: กรอกกิจกรรม คำนวณคะแนน และแนบหลักฐาน
14 หากติดขัด ให้ตรวจสถานะ สิทธิ์ ข้อมูลบังคับ และลิงก์หลักฐาน
```

Expected: the ending gives support personnel a complete practice and recovery path.

- [ ] **Step 4: Export and inspect support deck structure**

Run `buildSupportDeck()` and export to `docs/MSU-EVA-อบรมสายสนับสนุน.pptx`.

Expected:

```text
Slides: 14
Aspect ratio: 16:9
Primary accent: teal
Formula text matches the approved specification exactly
No unresolved placeholder text
```

- [ ] **Step 5: Commit the support deliverable**

Run:

```powershell
git add -- 'docs/MSU-EVA-อบรมสายสนับสนุน.pptx'
git commit -m 'docs: add support MSU EVA training deck'
```

Expected: one commit containing only the support PPTX.

### Task 4: Render, Inspect, Correct, and Deliver

**Files:**
- Create: `C:\Users\pisut\AppData\Local\Temp\codex-presentations\split-role-training-2026-07-24\tmp\qa\academic-ledger.txt`
- Create: `C:\Users\pisut\AppData\Local\Temp\codex-presentations\split-role-training-2026-07-24\tmp\qa\support-ledger.txt`
- Validate: `docs/MSU-EVA-อบรมสายวิชาการ.pptx`
- Validate: `docs/MSU-EVA-อบรมสายสนับสนุน.pptx`

**Interfaces:**
- Consumes: Task 2 and Task 3 final PPTX files
- Produces: two fully rendered, visually reviewed, mechanically validated deliverables

- [ ] **Step 1: Render every slide in both decks**

Run:

```powershell
python "$env:SKILL_DIR\container_tools\render_slides.py" 'docs/MSU-EVA-อบรมสายวิชาการ.pptx'
python "$env:SKILL_DIR\container_tools\render_slides.py" 'docs/MSU-EVA-อบรมสายสนับสนุน.pptx'
```

Expected: each render directory contains exactly 14 PNG files.

- [ ] **Step 2: Create one montage for each deck**

Run:

```powershell
python "$env:SKILL_DIR\container_tools\create_montage.py" --input_dir 'docs/MSU-EVA-อบรมสายวิชาการ' --output_file "$env:TMP_DIR\qa\academic-montage.png"
python "$env:SKILL_DIR\container_tools\create_montage.py" --input_dir 'docs/MSU-EVA-อบรมสายสนับสนุน' --output_file "$env:TMP_DIR\qa\support-montage.png"
```

Expected: both montages show a shared visual family and clearly different role accents.

- [ ] **Step 3: Inspect all 28 slides at full size**

For each slide, record pass or fail for:

```text
Title fits without unintended wrapping.
Thai glyphs render correctly.
No text or image is clipped.
No unintended overlap is visible.
Logos are clear and subordinate to the slide message.
Color contrast is sufficient.
Visual density is balanced.
The slide advances one teaching point.
```

Expected: both ledger files contain 14 completed slide records.

- [ ] **Step 4: Run mechanical validation**

Run:

```powershell
python "$env:SKILL_DIR\container_tools\slides_test.py" 'docs/MSU-EVA-อบรมสายวิชาการ.pptx'
python "$env:SKILL_DIR\container_tools\slides_test.py" 'docs/MSU-EVA-อบรมสายสนับสนุน.pptx'
```

Expected: both commands exit successfully with no slide content outside the canvas.

- [ ] **Step 5: Correct every failed check**

Edit `build-role-decks.mjs`, regenerate both decks, rerender affected slides, and rerun mechanical validation.

Expected: all 28 visual records pass and both validators exit successfully.

- [ ] **Step 6: Verify final repository state**

Run:

```powershell
git status --short
git diff --check
git log --oneline -5
```

Expected: the two final PPTX files and approved planning documents are task-owned; unrelated pre-existing changes remain untouched.

- [ ] **Step 7: Commit any final QA corrections**

If regeneration changed either PPTX after its task commit, run:

```powershell
git add -- 'docs/MSU-EVA-อบรมสายวิชาการ.pptx' 'docs/MSU-EVA-อบรมสายสนับสนุน.pptx'
git commit -m 'docs: polish role-based MSU EVA training decks'
```

Expected: the final commit contains only presentation corrections.
