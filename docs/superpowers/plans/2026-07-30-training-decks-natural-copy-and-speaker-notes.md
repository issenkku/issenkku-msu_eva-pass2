# Natural Thai Training Decks and Speaker Notes Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Produce two revised 16-slide MSU EVA training decks with natural Thai copy, reviewer-role instruction, and approximately one minute of facilitator script per slide.

**Architecture:** Revise the existing `@oai/artifact-tool` JavaScript builder rather than editing the open PowerPoint files in place. Preserve the approved visual system and assets, add two reviewer-role slides to each deck, attach structured facilitator scripts before each existing `[Sources]` block, and export new “ฉบับวิทยากร” files. Render all 32 slides in Microsoft PowerPoint and run text/canvas validation before delivery.

**Tech Stack:** JavaScript ES modules, `@oai/artifact-tool`, PowerPoint (`.pptx`), Microsoft PowerPoint COM rendering and validation

## Global Constraints

- Use polite, natural Thai that a facilitator can comfortably say aloud.
- Use these terms consistently: ผู้รับการประเมิน, ผู้ประเมิน, ผู้ประเมินคนสุดท้าย, บันทึกร่าง, ส่งแบบประเมิน, ประเมินเสร็จสิ้น (`Completed`).
- Each deck contains exactly 16 slides.
- Add approximately one minute of facilitator script to the Speaker Notes of every slide.
- Notes may use `[พูด]`, `[สาธิต]`, `[ถามผู้เข้าอบรม]`, `[เริ่มทดลองใช้งาน — 30 นาที]`, `[ให้ผู้เข้าอบรมหยุดที่บันทึกร่าง]`, and `[กลับมาที่สไลด์]`.
- Preserve every `[Sources]` block after the facilitator script.
- Preserve the approved branding, colors, logo placement, and 16:9 layout.
- Do not reduce text below the approved minimum sizes.
- Do not expose credentials, real evaluation records, or production data.
- Preserve the currently open source decks and export revised copies.
- Academic output: `docs/MSU-EVA-อบรมสายวิชาการ-ฉบับวิทยากร.pptx`.
- Support output: `docs/MSU-EVA-อบรมสายสนับสนุน-ฉบับวิทยากร.pptx`.
- Preserve unrelated repository changes.

---

## File Structure

- Read: `docs/superpowers/specs/2026-07-30-training-decks-natural-thai-copy-design.md`
- Read: `C:\Users\pisut\AppData\Local\Temp\codex-presentations\split-role-training-2026-07-24\tmp\build-role-decks.mjs`
- Create: `C:\Users\pisut\AppData\Local\Temp\codex-presentations\training-facilitator-2026-07-30\tmp\build-facilitator-decks.mjs`
- Create: `C:\Users\pisut\AppData\Local\Temp\codex-presentations\training-facilitator-2026-07-30\tmp\qa\academic-ledger.txt`
- Create: `C:\Users\pisut\AppData\Local\Temp\codex-presentations\training-facilitator-2026-07-30\tmp\qa\support-ledger.txt`
- Create: `docs/MSU-EVA-อบรมสายวิชาการ-ฉบับวิทยากร.pptx`
- Create: `docs/MSU-EVA-อบรมสายสนับสนุน-ฉบับวิทยากร.pptx`

### Task 1: Revise Audience-Facing Thai Copy

**Files:**
- Create: `C:\Users\pisut\AppData\Local\Temp\codex-presentations\training-facilitator-2026-07-30\tmp\build-facilitator-decks.mjs`
- Read: `docs/superpowers/specs/2026-07-30-training-decks-natural-thai-copy-design.md`

**Interfaces:**
- Consumes: existing slide helpers, theme tokens, brand assets, and approved natural-copy specification
- Produces: revised `buildAcademicDeck()` and `buildSupportDeck()` functions with natural Thai titles and body copy

- [ ] **Step 1: Copy the verified builder into the new scratch workspace**

Run:

```powershell
$src='C:\Users\pisut\AppData\Local\Temp\codex-presentations\split-role-training-2026-07-24\tmp\build-role-decks.mjs'
$dst='C:\Users\pisut\AppData\Local\Temp\codex-presentations\training-facilitator-2026-07-30\tmp\build-facilitator-decks.mjs'
New-Item -ItemType Directory -Force -Path (Split-Path $dst),(Join-Path (Split-Path $dst) 'qa') | Out-Null
Copy-Item -LiteralPath $src -Destination $dst -Force
```

Expected: the new builder is byte-identical to the previously verified builder before copy edits.

- [ ] **Step 2: Update output paths**

Set:

```javascript
const OUT_ACADEMIC = path.join(ROOT, "docs", "MSU-EVA-อบรมสายวิชาการ-ฉบับวิทยากร.pptx");
const OUT_SUPPORT = path.join(ROOT, "docs", "MSU-EVA-อบรมสายสนับสนุน-ฉบับวิทยากร.pptx");
```

Expected: generation never overwrites either currently open source deck.

- [ ] **Step 3: Replace all academic titles and key body copy**

Use the exact 16-title academic sequence and body-copy rules from the approved specification. Replace system-like phrases with participant-facing instructions, including:

```text
มาดูขั้นตอนทั้งหมดก่อนเริ่มกรอกข้อมูล
กรอกข้อมูลของคุณให้ครบ แล้วส่งให้ผู้ประเมิน
หากข้อมูลยังไม่ครบ ให้บันทึกร่างไว้ก่อน
หลังส่งแบบประเมิน ระบบจะส่งงานตามลำดับผู้ประเมิน
```

Expected: visible academic copy consistently uses the approved terms and contains none of the deprecated phrases listed in the specification.

- [ ] **Step 4: Replace all support titles and key body copy**

Use the exact 16-title support sequence and body-copy rules from the approved specification. Preserve this exact formula:

```text
คะแนนถ่วงน้ำหนัก = น้ำหนัก × คะแนนที่ทำได้ ÷ 100
```

Expected: support copy is natural Thai while the formula, evidence requirement, and workflow remain technically unchanged.

- [ ] **Step 5: Run a text contract check**

Inspect the generated presentation model and search visible text for:

```text
ผู้ตรวจ
เห็นเส้นทางทั้งงาน
ข้อมูลแต่ละส่วนต้องเล่าเรื่องเดียวกัน
หลักฐานต้องชี้กลับมาที่กิจกรรม
```

Expected: no deprecated phrase remains except inside source notes where a repository filename contains that text.

### Task 2: Add Reviewer Slides and Facilitator Scripts

**Files:**
- Modify: `C:\Users\pisut\AppData\Local\Temp\codex-presentations\training-facilitator-2026-07-30\tmp\build-facilitator-decks.mjs`

**Interfaces:**
- Consumes: Task 1 revised slide builders
- Produces: `addReviewerChecklist()`, `addReviewerHandoff()`, and facilitator notes for all 32 slides

- [ ] **Step 1: Extend the notes helper**

Implement:

```javascript
function addNotes(slide, script, sourceLines) {
  slide.speakerNotes.textFrame.setText(
    `${script.trim()}\n\n[Sources]\n${[...new Set(sourceLines)]
      .map((line) => `- ${line}`)
      .join("\n")}\n[/Sources]`,
  );
}
```

Every call must pass a complete Thai script. Each script should contain 80–140 Thai words plus only the relevant cue markers.

Expected: every notes page begins with facilitator content and ends with one valid `[Sources]` block.

- [ ] **Step 2: Add the reviewer checklist slide**

Implement one shared slide function with role-specific body copy:

```javascript
function addReviewerChecklist(deck, theme, page, supportMode, script) {}
```

Academic checklist:

```text
ตรวจว่าข้อมูลตรงกับเกณฑ์
เปิดหลักฐานและตรวจความสอดคล้อง
ให้คะแนนหรือความเห็นตามสิทธิ์
ส่งต่อเมื่อข้อมูลครบ
```

Support checklist additionally names activity results and the weighted score.

Expected: each deck has a dedicated “เมื่อได้รับงานประเมิน” slide.

- [ ] **Step 3: Add the reviewer handoff slide**

Implement:

```javascript
function addReviewerHandoff(deck, theme, page, script) {}
```

The slide explains:

```text
ผู้ประเมินแต่ละคนตรวจและส่งต่องาน
ผู้ประเมินคนสุดท้ายยืนยัน
สถานะเปลี่ยนเป็น ประเมินเสร็จสิ้น (Completed)
```

Expected: the final-reviewer rule is explicit without implying that a fixed executive role always closes the work.

- [ ] **Step 4: Add facilitator scripts to all slides**

Use the approved opening, demonstration, practice, and submission transitions. Place cues at these transitions:

```text
After slide 5: demonstrate dashboard and opening an assigned item
After academic slide 9: demonstrate one academic entry
After support slide 10: demonstrate activity, score, and evidence entry
Academic slides 12–14: demonstrate reviewer and last-reviewer actions
Support slides 13–14: demonstrate reviewer and last-reviewer actions
Slide 15: start participant practice and stop at Save Draft
Slide 16: debrief and troubleshooting
```

Expected: all 32 slides have usable facilitator scripts and the live-demo transitions match the approved three-hour run-of-show.

- [ ] **Step 5: Verify notes coverage**

Inspect both presentation models.

Expected:

```text
Academic slides: 16
Academic notes pages with [พูด]: 16
Support slides: 16
Support notes pages with [พูด]: 16
Sources blocks: 32
```

### Task 3: Export the Two Facilitator Decks

**Files:**
- Create: `docs/MSU-EVA-อบรมสายวิชาการ-ฉบับวิทยากร.pptx`
- Create: `docs/MSU-EVA-อบรมสายสนับสนุน-ฉบับวิทยากร.pptx`

**Interfaces:**
- Consumes: Task 2 completed presentation models
- Produces: two independent 16-slide PowerPoint files

- [ ] **Step 1: Load the Artifact Tool runtime**

Add the runtime module path:

```text
C:\Users\pisut\.cache\codex-runtimes\codex-primary-runtime\dependencies\node\node_modules
```

Expected: `await import("@oai/artifact-tool")` succeeds.

- [ ] **Step 2: Execute the builder**

Import the local `.mjs` module through the Node REPL with a cache-busting query.

Expected:

```text
Academic: 16 slides
Support: 16 slides
Both output files exist and exceed 500 KB
```

- [ ] **Step 3: Inspect the PowerPoint packages**

Open each `.pptx` as a ZIP package and count:

```text
ppt/slides/slide*.xml = 16
ppt/notesSlides/notesSlide*.xml = 16
ppt/media/* includes faculty seal, company logo, and background
```

Expected: both decks contain 16 slides, 16 notes pages, and embedded media.

- [ ] **Step 4: Commit the facilitator decks**

Run:

```powershell
git add -- 'docs/MSU-EVA-อบรมสายวิชาการ-ฉบับวิทยากร.pptx' 'docs/MSU-EVA-อบรมสายสนับสนุน-ฉบับวิทยากร.pptx'
git commit -m 'docs: add facilitator training decks with speaker notes'
```

Expected: the commit contains only the two new PowerPoint files.

### Task 4: Render and Verify All 32 Slides

**Files:**
- Create: `C:\Users\pisut\AppData\Local\Temp\codex-presentations\training-facilitator-2026-07-30\tmp\qa\academic-ledger.txt`
- Create: `C:\Users\pisut\AppData\Local\Temp\codex-presentations\training-facilitator-2026-07-30\tmp\qa\support-ledger.txt`
- Validate: both facilitator PowerPoint files

**Interfaces:**
- Consumes: Task 3 exported decks
- Produces: full-size renders, final visual ledgers, and verified deliverables

- [ ] **Step 1: Export every slide through Microsoft PowerPoint**

Render both decks to 1600 × 900 PNG files.

Expected: exactly 16 images per deck with correct Thai glyphs and embedded logos.

- [ ] **Step 2: Inspect all slides individually**

Record pass/fail for:

```text
Natural Thai wording
Title fits within two deliberate lines
No clipping or unintended overlap
Readable body copy
Consistent footer and page number
Reviewer slides visually match the deck
```

Expected: both QA ledgers contain 16 passing slide records.

- [ ] **Step 3: Run PowerPoint canvas and text-overflow validation**

For every shape in both decks, verify:

```text
Left >= 0
Top >= 0
Left + Width <= SlideWidth
Top + Height <= SlideHeight
TextFrame2.Overflowing is false
```

Expected: no outside-canvas object and no overflowing text.

- [ ] **Step 4: Verify script and cue content**

Inspect speaker notes and confirm:

```text
32 notes pages contain [พูด]
demonstration cues occur at the approved transitions
participant practice stops at บันทึกร่าง
every notes page contains one [Sources] block
```

Expected: all four checks pass.

- [ ] **Step 5: Run repository tests**

Run:

```powershell
composer test
```

Expected: exit code 0.

- [ ] **Step 6: Inspect final repository state**

Run:

```powershell
git status --short
git diff --check
git show --stat --oneline HEAD
```

Expected: task-owned changes are limited to the approved specification, implementation plan, and two facilitator decks; unrelated existing changes remain untouched.
